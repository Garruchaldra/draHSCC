<?php

require_once __DIR__ . '/../aws_support.php';

class AWSTusUpload extends Component {

    use AWSSupport;

    /**
     * basic info about the component
     */
    public function component_info() {

		$upload_max_filesize = ini_get("upload_max_filesize");
		$post_max_size =ini_get("post_max_size");
		$max_execution_time = ini_get("max_execution_time");
		$max_input_time = ini_get("max_input_time");
		$memory_limit = ini_get("memory_limit");
		$max_file_uploads = ini_get("max_file_uploads");
		
		$phpinfo = <<<EOF
<div>
<p>php.ini values</p>
upload_max_filesize: $upload_max_filesize (30M is recommended)<br>
post_max_size: $post_max_size (30M is recommended)<br>
max_execution_time: $max_execution_time (900 is recommended)<br>
max_input_time: $max_input_time (-1 is recommended)<br>
memory_limit: $memory_limit (256M is recommended)<br>
max_file_uploads: $max_file_uploads (100 is recommended)<br>
</div>
EOF;
    
        return array(
            'name' => 'AWSTusUpload',
            'description' => 'Asynchronous Amazon s3 upload portal using Tus' . $phpinfo,
            'category' => 'uploaders',
        );
    }
    
	// display build calls
	// 10 MB
	static $chunk_size = '10000000';
	// 1 minute (1 * 60 * 1000);
	static $restart_time = (2 * 60 * 1000);
	// 12 hours
	static $clean_up = (12 * 60 * 60);

	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
		
		$content_hook = array (
			'media_add_file_uploader' => 'AWSTusUpload::add_file_uploader',
            'file_file_path_method' => 'AWSTusUpload::file_file_path_method',
            'media_delete_component' => 'AWSTusUpload::media_delete_component',
            'media_file_uploader' => 'AWSTusUpload::add_transcript_checkbox'
		);

		return $content_hook;

	}

	/**
	 * create component specific database table when installed
	 */
	public function activated() {
		global $vce;
		$sql = "CREATE TABLE " . TABLE_PREFIX . "uploads (`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,`start_time` int(11) UNSIGNED NOT NULL,`end_time` int(11) UNSIGNED NOT NULL,`user_agent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,`location` varchar(255) COLLATE utf8_unicode_ci NOT NULL,`user_id` int(11) UNSIGNED NOT NULL,`file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,`file_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,`file_size` bigint(20) UNSIGNED NOT NULL,`chunks_total` int(11) UNSIGNED NOT NULL,`chunks_resumed` int(11) UNSIGNED NOT NULL,`chunks_completed` int(11) UNSIGNED NOT NULL,`uploaded_size` bigint(20) UNSIGNED NOT NULL,`upload_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL, `etags` text COLLATE utf8_unicode_ci NOT NULL,`destination` varchar(255) COLLATE utf8_unicode_ci NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$vce->db->query($sql);
	}
	
	/**
	 * clear component specific database table when disabled
	 */
	public function disabled() {
		// global $vce;
		// $sql = "DROP TABLE IF EXISTS " . TABLE_PREFIX . "uploads;";
		// $vce->db->query($sql);
	}
	
	/**
	 * This method can be used to route a url path to a specific component method. 
	 */
	public function path_routing() {
	
		// add the path to upload
		$media_upload_path = defined('MEDIA_UPLOAD_PATH') ? MEDIA_UPLOAD_PATH : 'upload';

		$path_routing = array(
			$media_upload_path => array('AWSTusUpload','upload_creation_request'),
			$media_upload_path . '/{location}' => array('AWSTusUpload','upload_content'),
		);
		 
		return $path_routing;

	}

	public function upload_creation_request() {

		/*
		At first, the client sends a POST request to the server to initiate the upload. 
		This upload creation request tells the server basic information about the upload, 
		such as its size or additional metadata. If the server accepts this upload creation request, 
		it will return a successfully response with the Location header set to the upload URL. 
		The upload URL is used to unique identify and reference the newly created upload resource.
		*/
	
		global $vce;
		
		// add the path to upload
		$media_upload_path = defined('MEDIA_UPLOAD_PATH') ? $vce->site->site_url . '/' . MEDIA_UPLOAD_PATH : $vce->site->site_url . '/upload';

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			// add hooks, specifically for db sessions
			$vce->site->hooks = $vce->site->get_hooks($vce);
		
			// create user object
			require_once(BASEPATH . 'vce-application/class.user.php');
			$user = new User($vce);
			
			if (isset($_SERVER['HTTP_UPLOAD_METADATA'])) {
			
				$upload_metadata = explode(',', $_SERVER['HTTP_UPLOAD_METADATA']);
			
				$metadata = array();
			
				// rekey upload_metadata and decode base64
				foreach ($upload_metadata as $each) {
			
					list($key,$value) = explode(' ', $each);
			
					$metadata[$key] = base64_decode($value);
			
				}
				
			}
			
			$filetype = $metadata['filetype'];
			
			$mimename = null;
		
			// get mimetype supplied by plupload
			// if one is not supplied, then create a special one for verification
			$mimetype = !empty($metadata['mediatypes']) ? $metadata['mediatypes'] : json_encode($metadata['filetype']);
			
			// cycle through mediatypes that were passed through from functions media_type()
			foreach (json_decode($mimetype, true) as $each_mediatype) {
			
				file_put_contents(BASEPATH . 'log.txt', $each_mediatype['mimetype'] . PHP_EOL, FILE_APPEND);

				// check for subtype wildcard
				if (preg_match('/\.\*$/', $each_mediatype['mimetype'])) {

					// match primaray type
					if (explode('/', $each_mediatype['mimetype'])[0] == explode('/', $filetype)[0]) {

						// class name of media player
						$mimename = $each_mediatype['mimename'];
			
						break;
	
					}

				} else {

					// match full
					if ($each_mediatype['mimetype'] == $filetype) {

						$mimename = $each_mediatype['mimename'];
			
						break;
	
					}
	
				}

			}

			// no mimename name match was found.
			if (!isset($mimename)) {
				// should delete file, but for now leave it for error detection
				die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: File type not allowed / Mimename not found. <div class="link-button cancel-button">Try Again</div>')));
			}

			// decryption of dossier
			$dossier = json_decode($vce->user->decryption($metadata['dossier'], $vce->user->session_vector));
			
			// check that component is a property of $dossier, json object test
			if (!isset($dossier->type) || !isset($dossier->procedure)) {
				// echo json_encode(array('response' => 'error','message' => 'File Uploader Error: Dossier is not valid <div class="link-button cancel-button">Try Again</div>','action' => ''));
				header('HTTP/1.1 200 OK');
				exit();
			}
			
			// anonymous function to generate password
			$random_name = function ($name = null) use (&$random_name) {

				$charset = array('0123456789','abcdefghijklmnopqrstuxyvwz','ABCDEFGHIJKLMNOPQRSTUXYVWZ');
			
				$key = mt_rand(0,(count($charset) - 1));
			
				$newchar = substr($charset[$key], mt_rand(0, (strlen($charset[$key]) - 1)), 1);

				if (strlen($name) >= 16) {
						return $name . $newchar;
				}
			
				return $random_name($name . $newchar);
			
			};

			// get a new random password
			$file = $vce->user->user_id . '_' . $random_name() . '.' . strtolower(pathinfo($metadata['filename'])['extension']);
			
			// title
			$records[] = array(
			'location' => $file,
			'start_time' => time(),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'user_id' => $vce->user->user_id, 
			'file_name' => $metadata['filename'],
			'file_type' => $metadata['filetype'],
			'file_size' => $_SERVER['HTTP_UPLOAD_LENGTH'],
			'chunks_total' => ceil($_SERVER['HTTP_UPLOAD_LENGTH'] / self::$chunk_size)
			);

			$vce->db->insert('uploads', $records);
			
			// file_put_contents(BASEPATH . 'log.txt', $file . PHP_EOL, FILE_APPEND);

			header('HTTP/1.1 201 Created');
			header('Location: ' . $media_upload_path . '/' . $file);
			header('Tus-Resumable: 1.0.0');
			header('Tus-Max-Size: ' . self::$chunk_size);
			
			echo 'success';
	
			exit();
		
		}
		
		header('HTTP/1.1 200 OK');
		
		exit();
	
	}
	
	
	public function upload_content() {
	
		global $vce;
	
		if (empty($this->location)) {
			exit();
		}
		
		$query = "SELECT * FROM " . TABLE_PREFIX . "uploads WHERE location='" . $this->location . "'";
		$upload_data = $vce->db->get_data_object($query);
		
		// get body content
		$input = file_get_contents('php://input');

		/*
		Once the upload has been create, the client can start to transmit the actual upload content 
		by sending a PATCH request to the upload URL, as returned in the previous POST request. 
		Idealy, this PATCH request should contain as much upload content as possible to minimize 
		the upload duration. The PATCH request must also contain the Upload-Offset header 
		which tells the server at which byte-offset the server should write the uploaded data. 
		If the PATCH request successfully transfers the entire upload content, then your upload is done!
		*/

		if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
		
			$offset = $upload_data[0]->uploaded_size + $_SERVER['CONTENT_LENGTH'];
		
			$chunk = ceil($offset / self::$chunk_size);

			try {
		
				AWSTusUpload::check_aws_objects($vce);
				AWSTusUpload::set_header();
				
				// put file on s3 source bucket
            	$config = AWSTusUpload::get_config($vce);
            	$sdk = AWSTusUpload::get_sdk($config); 
            	$s3 = $sdk->createS3();
			
				// first chunk?
				if ($upload_data[0]->chunks_completed == '0') {
			
					$result = $s3->createMultipartUpload([
						'Bucket' => AWSTusUpload::source_bucket($config),
						'Key' => $this->location,
					]);
					$etags = array();
					$upload_id = $result['UploadId'];
					$part_number = $upload_data[0]->chunks_completed;
		   
				} else {
			
					// get from session
					$etags = unserialize($upload_data[0]->etags);
					$upload_id = $upload_data[0]->upload_id;
					$part_number = $upload_data[0]->chunks_completed;			
				}
            
            	$part_number++;
    
                $result = $s3->uploadPart([
                'Bucket' => AWSTusUpload::source_bucket($config),
                'Key' => $this->location,
                'UploadId' => $upload_id,
                'PartNumber' => $part_number,
                'Body' => $input,
				]);

				// keep track of etags
				$etags[$part_number] = str_replace('"', '', $result['ETag']);
				
  		 		// file_put_contents(BASEPATH . 'log.txt', $result['ETag'] . PHP_EOL, FILE_APPEND);

			} catch (Exception $e) {
				// $vce->log($e);
				// file_put_contents(BASEPATH . 'log.txt', $e . PHP_EOL, FILE_APPEND);
				die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: AWS problem: ' . $e->getMessage() . ' <div class="link-button cancel-button">Try Again</div>')));
			}

			$update = array('chunks_completed' => $chunk,'uploaded_size' => $offset,'upload_id' => $upload_id, 'etags' => serialize($etags));
			$update_where = array('location' => $this->location);
			$vce->db->update('uploads', $update, $update_where);

			// file_put_contents(BASEPATH . 'log.txt', 'PATCH' . PHP_EOL, FILE_APPEND);
			// file_put_contents(BASEPATH . 'log.txt', 'filesize: ' . $offset . PHP_EOL, FILE_APPEND);
			
			header('HTTP/1.1 201 Content');
			header('Tus-Resumable: 1.0.0');
			header('Upload-Offset: ' . $offset);
			
			echo 'PATCH Success';
			
			exit();
		
		}
		
		/*
		If the PATCH request got interrupted or failed for another reason, 
		the client can attempt to resume the upload. In order to resume, 
		the client must know how much data the server has received. 
		This information is obtained by sending a HEAD request to the upload URL 
		and inspecting the returned Upload-Offset header. 
		Once the client knows the upload offset, it can send another PATCH request 
		until the upload is completely down.
		*/
		
		if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
		
			/*
			Request:

			HEAD /files/24e533e02ec3bc40c387f1a0e460e216 HTTP/1.1
			Host: tus.example.org
			Tus-Resumable: 1.0.0

			Response:

			HTTP/1.1 200 OK
			Upload-Offset: 70
			Tus-Resumable: 1.0.0
			*/
		
			// Cache-Control: no-store
			
			$offset = $upload_data[0]->uploaded_size;
			
			$chunks_resumed = $upload_data[0]->chunks_resumed + 1;
			
			$update = array('chunks_resumed' => $chunks_resumed);
			$update_where = array('location' => $this->location);
			$vce->db->update('uploads', $update, $update_where);
			
			// file_put_contents(BASEPATH . 'log.txt', 'HEAD' . PHP_EOL, FILE_APPEND);
			
			// file_put_contents(BASEPATH . 'log.txt', $offset . PHP_EOL, FILE_APPEND);
			
			// file_put_contents(BASEPATH . 'log.txt', json_encode($_SERVER) . PHP_EOL, FILE_APPEND);
			
			header('HTTP/1.1 200 OK');
			header('Tus-Resumable: 1.0.0');
			header('Upload-Offset: ' . $offset);
			header('Upload-Length: ' . ($offset + 1));
			// header('Upload-Length: ' . $upload_data[0]->file_size);
			header('Cache-Control: no-store');
	
			echo 'HEAD resume';
			
		}
		
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
			// add hooks, specifically for db sessions
			$vce->site->hooks = $vce->site->get_hooks($vce);
		
			// create user object
			require_once(BASEPATH . 'vce-application/class.user.php');
			$user = new User($vce);
		
			// file_put_contents(BASEPATH . 'log.txt', 'SUCCESS' . PHP_EOL, FILE_APPEND);
		
			$filetype = $_POST['filetype'];
		
			// get mimetype supplied by plupload
			// if one is not supplied, then create a special one for verification
			$mimetype = !empty($_POST['mediatypes']) ? $_POST['mediatypes'] : json_encode($_POST['filetype']);
			
			// // file_put_contents(BASEPATH . 'log.txt', $mimetype . PHP_EOL, FILE_APPEND);
			
			// cycle through mediatypes that were passed through from functions media_type()
			foreach (json_decode($mimetype, true) as $each_mediatype) {

				// check for subtype wildcard
				if (preg_match('/\.\*$/', $each_mediatype['mimetype'])) {

					// match primaray type
					if (explode('/', $each_mediatype['mimetype'])[0] == explode('/', $filetype)[0]) {

						// class name of media player
						$mimename = $each_mediatype['mimename'];
			
						break;
	
					}

				} else {

					// match full
					if ($each_mediatype['mimetype'] == $filetype) {

						$mimename = $each_mediatype['mimename'];
			
						break;
	
					}
	
				}

			}

			// no mimename name match was found.
			if (!isset($mimename)) {
				// should delete file, but for now leave it for error detection
				die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: File type not allowed / Mimename not found. <div class="link-button cancel-button">Try Again</div>')));
			}
			
			// // file_put_contents(BASEPATH . 'log.txt', $mimename . PHP_EOL, FILE_APPEND);
		
			$created_by = $upload_data[0]->user_id;
			$created_by = $vce->user->user_id;
		
			AWSTusUpload::check_aws_objects($vce);
			AWSTusUpload::set_header();
			
			// put file on s3 source bucket
			$config = AWSTusUpload::get_config($vce);
			$sdk = AWSTusUpload::get_sdk($config); 
			$s3 = $sdk->createS3();

			$filetype = $_POST['filetype'];
		
			// get mimetype supplied by plupload
			// if one is not supplied, then create a special one for verification
			$mimetype = !empty($_POST['mediatypes']) ? $_POST['mediatypes'] : json_encode($_POST['filetype']);
			
			// // file_put_contents(BASEPATH . 'log.txt', $mimetype . PHP_EOL, FILE_APPEND);
			
			// cycle through mediatypes that were passed through from functions media_type()
			foreach (json_decode($mimetype, true) as $each_mediatype) {

				// check for subtype wildcard
				if (preg_match('/\.\*$/', $each_mediatype['mimetype'])) {

					// match primaray type
					if (explode('/', $each_mediatype['mimetype'])[0] == explode('/', $filetype)[0]) {

						// class name of media player
						$mimename = $each_mediatype['mimename'];
			
						break;
	
					}

				} else {

					// match full
					if ($each_mediatype['mimetype'] == $filetype) {

						$mimename = $each_mediatype['mimename'];
			
						break;
	
					}
	
				}

			}

			// no mimename name match was found.
			if (!isset($mimename)) {
				// should delete file, but for now leave it for error detection
				unlink($filepath);
				die(json_encode(array('status' => 'error', 'message' => 'File Uploader Error: File type not allowed / Mimename not found. <div class="link-button cancel-button">Try Again</div>')));
			}

			$etags = unserialize($upload_data[0]->etags);
			$upload_id = $upload_data[0]->upload_id;
			
			for ($i = 1; $i <= count($etags); $i++) {
				$parts['Parts'][$i] = ['PartNumber' => $i, 'ETag' => '"' . $etags[$i] . '"'];
			}

			$result = $s3->completeMultipartUpload([
				'Bucket' => AWSTusUpload::source_bucket($config),
				'Key' => $this->location,
				'UploadId' => $upload_id,
				'MultipartUpload' => $parts,
			]);
			
			
			$update = array('end_time' => time(),'etags' => 'success','destination' => $this->location);
			$update_where = array('location' => $this->location);
			$vce->db->update('uploads', $update, $update_where);
			
			// // file_put_contents(BASEPATH . 'log.txt', 'SUCCESS' . PHP_EOL, FILE_APPEND);
		
			// // file_put_contents(BASEPATH . 'log.txt', json_encode($_POST) . PHP_EOL, FILE_APPEND);
			
			// create input
			$post = array();
			
			$post['dossier'] =  $_POST['dossier'];
			$post['name'] =  $_POST['filename'];
			$post['title'] =  $_POST['title'];
			$post['media_type'] = $mimename;
			$post['path'] = $this->location;
			$post['created_by'] = $_POST['created_by'];
			
			// $input['action'] = $_POST['action'];
			
			$url = $_POST['action'];
			
			echo json_encode(array('status' => 'success', 'message' => 'Upload Complete!', 'media_type' => $mimename, 'path' => $this->location));
	
			exit();
		
		}	
	

		exit();
		
	}


	/**
	 * File uploader method
	 */
	public static function add_file_uploader($recipe_component, $vce) {
	
		// path to image
		$path = $vce->site->path_to_url(dirname(__FILE__));

		// add a property to page to indicate the the uploader has been added
		$vce->file_uploader = true;
		
		// add javascript for fileupload
		$vce->site->add_script(dirname(__FILE__) . '/js/tus.js');
	
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery jquery-ui');
		
		// add style
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css', 'media-style');
		
		$media_upload_path = $vce->site->site_url . '/upload';

		$chunk_size = self::$chunk_size;
		$restart_time = self::$restart_time;

$content_media = <<<EOF
<mauploader>
<div class="uploader-container">

	<div class="progressbar-container">
		<div class="progressbar-title">Upload In Progress</div>
		<div class="progressbar-block">
			<div class="progressbar-block-left">
				<div class="progressbar">
					<div class="progress-chunks" style="position:absolute;padding-left:5px;"></div>
				</div>
			</div>
			<div class="progressbar-block-right">
				<a class="cancel-upload link-button" href="">Cancel</a>
			</div>
		</div>
		<div class="progress-label" timestamp=0>0%</div>
		<div class="verify-chunks"></div>
	</div>


	<div class="upload-browse">
		<input class="fileupload" path="$media_upload_path" type="file" accept="" chunk_size="$chunk_size" restart_time="$restart_time">
		<button class="file-upload-cancel cancel-button">Cancel</button>
	</div>

EOF;

$content_media .= <<<EOF
	<div class="upload-form">
EOF;

		// the upload file form

$upload_file = <<<EOF
		<input class="action" type="hidden" name="action" value="$vce->input_path">
		<input class="dossier" type="hidden" name="dossier" value="$recipe_component->dossier_for_create">
		<input class="inputtypes" type="hidden" name="inputtypes" value="[]">
		<input class="created_by" type="hidden" name="created_by" value="{$vce->user->user_id}">
		<input class="parent_id" type="hidden" name="parent_id" value="{$recipe_component->parent_id}">
		<input class="mediatypes" type="hidden" name="mediatypes" value="">
EOF;

		// add title input
		$input = array(
		'type' => 'text',
		'name' => 'title',
		'data' => array('tag' => 'required','class' => 'resource-name')
		);
		
		$upload_file .= $vce->content->create_input($input,'Title','Enter a Title');

		// load hooks
		// media_file_uploader
		if (isset($vce->site->hooks['media_file_uploader'])) {
			foreach($vce->site->hooks['media_file_uploader'] as $hook) {
				$upload_file .= call_user_func($hook, $recipe_component, $vce);
			}
		}

$upload_file .= <<<EOF
		<button class="start-upload link-button"">Upload</button> <button class="cancel-upload link-button cancel-button">Cancel</button>
EOF;

		$content_media .= $vce->content->accordion('Upload File',$upload_file,true,true);

$content_media .= <<<EOF
	</div>
	<div class="progressbar-message"></div>
</div>
</mauploader>
EOF;

		return $content_media;
	
	}

    /**
     * add create transcript checkbox to file upload form
     */
    public static function add_transcript_checkbox($recipe_component, $vce) {

        $config = AWSTusUpload::get_config($vce);

        if ($config['create_captions_activated']) {
            return $vce->content->create_checkbox_input('disable_captions', false, 'Disable captions for this file.') . '<br>';
        }

    }

    /**
     * Delete file on s3
     *
     * @param [type] $input
     * @return void
     */
    public static function media_delete_component($input) {

        global $vce;

        if (isset($input['media_type']) && $input['media_type'] != "AWSVideo") {
            $query = "SELECT meta_value FROM " . TABLE_PREFIX . "components_meta WHERE component_id='" . $input['component_id'] . "' AND meta_key='path'";
            $path_data = $vce->db->get_data_object($query);
            if (isset($path_data[0]->meta_value)) {

                $path = $path_data[0]->meta_value;

                $config = AWSTusUpload::get_config($vce);
                $sdk = AWSTusUpload::get_sdk($config);
                $s3 = $sdk->createS3();
                $result = $s3->deleteObject([
                    'Key' => $path,
                    'Bucket' => AWSTusUpload::source_bucket($config),
                ]);
            }

        }

        return $input;
    }

    /**
     * alter file_path for s3.  Note this is deprecated in favor of the new aws_file.php component.
     *
     * @param [string] $requested_url
     * @param [string] $file_path
     * @param [VCE] $vce
     * @return file_path
     */
    public static function file_file_path_method($requested_url, $file_path, $vce) {

        $config = AWSTusUpload::get_config($vce);
        $sdk = AWSTusUpload::get_sdk($config);
        $bucket = AWSTusUpload::source_bucket($config);
        $file_path = basename($file_path);
        return AWSTusUpload::get_file_stream($bucket, $file_path, $sdk);

    }

    public static function set_header() {

        header('Vary: Accept');
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }

        // No cache
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        header("Access-Control-Allow-Headers: Content-Type,Content-Range,Content-Disposition");
    }

    public static function get_mime_name() {

        $mimetype = !empty($_REQUEST['mimetype']) ? $_REQUEST['mimetype'] : 'application/' . $_REQUEST['extention'];

        // cycle through mediatypes that were passed through from functions media_type()
        foreach (json_decode($_REQUEST['mediatypes']) as $each_mediatype) {

            // check for subtype wildcard
            if (preg_match('/\.\*$/', $each_mediatype->mimetype)) {
                // match primaray type
                if (explode('/', $each_mediatype->mimetype)[0] == explode('/', $mimetype)[0]) {
                    return $each_mediatype->mimename;
                }
            } else {
                // match full
                if ($each_mediatype->mimetype == $mimetype) {
                    return $each_mediatype->mimename;
                }
            }
        }

        return ''; // not found
    }

    /**
     * hide this component from being added to a recipe
     */
    public function recipe_fields($recipe) {
        return false;
    }

}