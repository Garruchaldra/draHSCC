<?php
class Pbc_webdam_migration  extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Webdam Migration',
			'description' => 'A component to allow monitored migration of videos and metadata from WebDam to the Coaching Companion.',
			'category' => 'pbc'
		);
	}
	


    /**
     *
     */
    public function as_content($each_component, $vce) {

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js');
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');

		$content = null;

		$dossier_for_add_video = $vce->user->encryption(json_encode(array('type' => 'Pbc_webdam_migration','procedure' => 'save_video')), $vce->user->session_vector);		
		$dossier_for_delete_video = $vce->user->encryption(json_encode(array('type' => 'Pbc_webdam_migration','procedure' => 'delete_video')), $vce->user->session_vector);		
		$dossier_for_unlink_uploads = $vce->user->encryption(json_encode(array('type' => 'Pbc_webdam_migration','procedure' => 'unlink_uploads')), $vce->user->session_vector);
		$dossier_for_repair_description = $vce->user->encryption(json_encode(array('type' => 'Pbc_webdam_migration','procedure' => 'repair_description')), $vce->user->session_vector);
		$dossier_for_import_external_link = $vce->user->encryption(json_encode(array('type' => 'Pbc_webdam_migration','procedure' => 'import_external_link')), $vce->user->session_vector);


$input = array(
	'type' => 'text',
	'name' => 'asset_id',
	'data' => array (
		'autocapitalize' => 'none',
		'tag' => 'required',
		'class' => 'asset-id'
	)
);

$save_asset_input = $vce->content->create_input($input,'Asset ID');

$input = array(
	'type' => 'text',
	'name' => 'taxonomy',
	'data' => array (
		'autocapitalize' => 'none',
		'tag' => 'required',
		'class' => 'taxonomy'
	)
);

$categories_input = $vce->content->create_input($input,'Taxonomy');

$content .= <<<EOF
<form id="add-id" class="asynchronous-form add-group-form" method="post" action="$vce->input_path">
<h2>Save a video from Webdam to this server</h2>
<input id="dossier-for-add-video" type="hidden" name="dossier" value="$dossier_for_add_video">
$save_asset_input
$categories_input
<input class="button__primary" type="submit" value="Save">
</form>
EOF;




$content .= <<<EOF
<h2>Parse CSV: asset_id,taxonomy_id|taxonomy_id|etc.</h2>
<div class="input-label-style"> 
<textarea id="parse-csv-textarea" name="parse_csv" rows="10" cols="100">
</textarea>
</div>
<div>
<button id="parse-csv-button" class="request_access button__primary" type="reset">Begin Webdam Batch Upload</button>
</div>

<div id="float-right-buttons-div">
<input id="dossier-for-delete-video" type="hidden" name="dossier" value="$dossier_for_delete_video">
<button id="parse-csv-delete-button" class="request_access button__primary" type="reset">Batch Delete</button>

<input id="dossier-for-unlink-uploads" type="hidden" name="dossier" value="$dossier_for_unlink_uploads">
<button id="unlink-uploads-button" class="request_access button__primary" type="reset">Unlink Uploads</button>
</div>
<div id="parse-csv-result" class="input-label-style">
</div>

<div>
<input id="dossier-for-repair-description" type="hidden" name="dossier" value="$dossier_for_repair_description">
<button id="repair-description-button" class="request_access button__primary" type="reset">Repair Webdam Video Descriptions</button>

<br>
<input id="dossier-for-import-external-link" type="hidden" name="dossier" value="$dossier_for_import_external_link">
<button id="import-external-links-button" class="request_access button__primary" type="reset">Import External Links</button>

</div>
EOF;


	
		

		$vce->content->add('main', $content);
	
	}

	/**
	 * instructions for various pbc pages
	 */
	public function example() {
		$content = <<<EOF
		some content here
EOF;
		return $content;
	}


	public static function save_video($input) {
	
		global $vce;

		if (!isset($input['asset_id']) || empty($input['asset_id'])){
			$message = 'No asset id';
			echo json_encode(array('response' => 'error','message' => $message,'action' => ''));
			return;
		}

		// check if video already exists
		$webdam_id = $input['asset_id'];
		$query = "SELECT component_id FROM " . TABLE_PREFIX . "components_meta WHERE meta_key = 'webdam_id' AND meta_value = $webdam_id";
		$result = $vce->db->get_data_object($query);
		if ($result) {
			$message = 'Video exists in glossary : ' . $webdam_id;
			echo json_encode(array('response' => 'success','message' => $message,'action' => ''));
			return;
		}


		// $vce->log($input);
		
        // return;

		//retrieve_attributes for webdam restful api
		$webdam_access_token = $vce->site->retrieve_attributes('webdam_access_token');
		$webdam_token_expires = $vce->site->retrieve_attributes('webdam_token_expires');
		$webdam_refresh_token = $vce->site->retrieve_attributes('webdam_refresh_token');


	
		$_refresh_token = 'b9bbcbc2eafa2fd4461454c8f5c48c621bfedc3e';

		$download = 'http://earlyeducoach.org/webdam/download.php';
		$limit_value = 20;
		$current_limit = isset($input['current_limit']) ? $input['current_limit'] : 0;
		$current_offset = ($current_limit == 0) ? 0 : $current_limit;

		$curlCounter = 0;

		// generate webdam_access_token if it doesn't exist
		if (!isset($webdam_access_token) || (isset($webdam_token_expires) && $webdam_token_expires < time())) {

			if (!isset($webdam_refresh_token)) {

				// Getting the access_token using grant type "password"

				$url = 'https://apiv2.webdamdb.com/oauth2/token';

				$post = array(
				'grant_type' => 'password',
				'client_id' => '44b79782ca114d2898d9d8778fe82030693b1995',
				'client_secret' => '1a05910aa040979a126ed2bbb0ea13b205f354bd',
				'username' => 'eedulib',
				'password' => 'h9$#hKuQ'
				);

				$curlCounter++;
				// http://php.net/manual/en/curl.examples-basic.php
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$results = json_decode(curl_exec($ch));
				curl_close($ch);

				// add to page object
				$webdam_access_token = $results->access_token;
				$vce->site->add_attributes('webdam_access_token', $webdam_access_token, true);

				$webdam_token_expires = time() + $results->expires_in;
				$vce->site->add_attributes('webdam_token_expires', $webdam_token_expires, true);

				$webdam_refresh_token = $results->refresh_token;
				$vce->site->add_attributes('webdam_refresh_token', $webdam_refresh_token, true);
		

			} else {
	
				// Getting the access_token using grant type "refresh_token"

				$url = 'https://apiv2.webdamdb.com/oauth2/token';

				$post = array(
				'grant_type' => 'refresh_token',
				'refresh_token' => $webdam_refresh_token,
				'client_id' => '44b79782ca114d2898d9d8778fe82030693b1995',
				'client_secret' => '1a05910aa040979a126ed2bbb0ea13b205f354bd',
				'redirect_uri' => 'http://earlyeducoach.org/webdam/'
				);

				$curlCounter++;
				// http://php.net/manual/en/curl.examples-basic.php
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$results = json_decode(curl_exec($ch));
				curl_close($ch);
				
				$webdam_access_token = $results->access_token;
				$vce->site->add_attributes('webdam_access_token', $webdam_access_token, true);

				$webdam_token_expires = time() + $results->expires_in;
				$vce->site->add_attributes('webdam_token_expires', $webdam_token_expires, true);
			}

		}   //end of generate webdam_access_token
		
		/**
		 * get one video
		 * 
		 * 
		 */

		

		


		// asset_id has value
		if (isset($input['asset_id'])) {

			$asset_id = $input['asset_id'];

			$headers = array(
			"Authorization: Bearer " . $webdam_access_token
			);


			// get basic info from asset
			$url = 'https://apiv2.webdamdb.com/assets/'.$asset_id;


			$curlCounter++;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$results = json_decode(curl_exec($ch));
			curl_close($ch);
// $vce->dump($results);




			$asset_info = array();
	
			if (isset($results)) {
				$asset_info['webdam_id'] = (isset($results->id))? $results->id : 'none';
				$asset_info['filetype'] = (isset($results->filetype))? $results->filetype : 'none';
				$asset_info['name'] = (isset($results->name))? str_replace('_',' ',preg_replace('/\.\w{3}$/','',$results->name)) : 'none';
				// sanitize name
				$asset_info['name'] = str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $asset_info['name']));
				// sanitize description
				$asset_info['description'] = (isset($results->description))? str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $results->description)) : '';
				$asset_info['url'] = (isset($results->hiResURLRaw))? $results->hiResURLRaw : '';
			}


			// get metadata info from asset
			$url = 'https://apiv2.webdamdb.com/assets/'.$asset_id.'/metadatas/xmp';


			$curlCounter++;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$results = json_decode(curl_exec($ch));
			curl_close($ch);
// $vce->dump($results);
// return;

	
			if (isset($results)) {
				// $asset_info['caption'] = (isset($results->caption))? $results->caption : 'none';
				$asset_info['keywords'] = (isset($results->keyword))? $results->keyword : '';		
				$asset_info['keywords'] = ($asset_info['keywords'] != 'none') ? explode(',', $asset_info['keywords']) : array();
				// add language to keywords
				$asset_info['keywords'][] = ($asset_info['language'] != '') ? $asset_info['language'] : null;
				// add age group to keywords
				$asset_info['keywords'][] = ($asset_info['age_group'] != '') ? $asset_info['age_group'] : null;
				// add teaching practices to keywords
				if (isset($results->customfield15) && $results->customfield15 != '') {
					$results->customfield15 = explode(',', $results->customfield15);
					foreach ($results->customfield15 as $this_teaching_practice) {
						$asset_info['keywords'][] = $this_teaching_practice;
					}
				}
				// sanitize keywords
				$sanitized_keywords = array();
				foreach ($asset_info['keywords'] as $this_keyword) {
					$this_keyword = str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $this_keyword));
					$sanitized_keywords[] = $this_keyword;
				}

				$asset_info['keywords'] = implode('|', $sanitized_keywords);


			}
// $vce->dump($asset_info);
// return;




			// 
			$err_msg = ''; 
			$newfilename = $asset_info['name'].'.'.$asset_info['filetype'];
			$localpath = '';



 
			// download file
			$url = 'https://apiv2.webdamdb.com/assets/'.$asset_id.'/download';
		
			set_time_limit(0);
			//This is the file where we save the    information
			// check if this is a using a common install
			$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
			// construct full path to video
			$path =  $vce->user->user_id.'_' . time() . '.' . $asset_info['filetype'];
			$uploaded_video_file = $basepath . PATH_TO_UPLOADS . '/' . $vce->user->user_id . '/' . $path;
			$fp = fopen ($uploaded_video_file, 'w+');
			//Here is the file we are downloading, replace spaces with %20
			$ch = curl_init($url);
			// make sure to set timeout to a high enough value
			// if this is too low the download will be interrupted
			curl_setopt($ch, CURLOPT_TIMEOUT, 600);
			// write curl response to file
			curl_setopt($ch, CURLOPT_FILE, $fp); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			// get curl response
			$curl_result = curl_exec($ch); 
			// $vce->log($curl_result);
			curl_close($ch);
			fclose($fp);
			if ($curl_result == true) {
				// upload to Vimeo
				//needed inputs: created_by, path, title, type, mediatype, taxonomy, keywords, parent_id (resource library), webdam_asset_id, 

		// get resource library component_id
		$query = "SELECT component_id FROM " . TABLE_PREFIX . "components WHERE url = 'resource_library'";
		$data = $vce->db->get_data_object($query);
		foreach($data as $this_data) {
			$resource_library_component_id = $this_data->component_id;
		}

		// $vce->dump($resource_library_component_id);

		$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
		// construct full path to Media
		$media_component_path = $basepath . '/vce-application/components/Media/Media.php';

		// require_once($media_component_path);
		$Media = new Media();

		// format $taxonomy correctly; it needs opening and ending pipelines
		$taxonomy = (isset($input['taxonomy'])) ? $input['taxonomy'] : '';
		$taxonomy = '|' . trim($taxonomy, '|') . '|';
		if ($taxonomy == '||') {
			$taxonomy = '';
		}

				$media_input = array (
					"type" => "Media",
					"parent_id" => $resource_library_component_id,
					"sequence" => 1,
					"name" => $asset_info['name'],
					'created_by' => $vce->user->user_id,
					"title" => $asset_info['name'],
					"description" => $asset_info['description'],
					"media_type" => "VimeoVideo",
					'path' => $path,
					'keywords' => $asset_info['keywords'],
					'webdam_id' => $asset_info['webdam_id'],
					'resource_library_component_id' => $resource_library_component_id,
					'taxonomy' => $taxonomy
				);

				// {"type":"Media","parent_id":"17858","sequence":1,"name":"smallvid.mov","inputtypes":"[]","created_by":"13","title":"smallvid","media_type":"VimeoVideo","path":"13_1673633671.mov"}

				$new_component_id = $Media->create($media_input);

			}

		}


	
		// $message = 'Done saving '. $newfilename . ': ' . $asset_info['webdam_id'] . ' to component_id: ' . $new_component_id;
		// echo json_encode(array('response' => 'success','message' => $message,'action' => ''));
		return;
	
	}





/**
 * emports single external link as component 
 */

	public static function import_external_link($input) {
	
		global $vce;
// $vce->log($input);
		// get resource library component_id
		$query = "SELECT component_id FROM " . TABLE_PREFIX . "components WHERE url = 'resource_library'";
		$data = $vce->db->get_data_object($query);
		foreach($data as $this_data) {
			$resource_library_component_id = $this_data->component_id;
		}

		// check if external link already exists in resource library
		$import_url = $input['import_url'];
		$query = "SELECT a.component_id, c.meta_value AS taxonomy, c.id AS taxonomy_id FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id=b.component_id AND b.meta_key = 'resource_library_component_id' AND b.meta_value=$resource_library_component_id AND a.meta_value = '$import_url' JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id=c.component_id AND c.meta_key='taxonomy'";
		// $vce->log($query);
		$result = $vce->db->get_data_object($query);

		// if the resource exists, it may have new taxonomy to add, so we'll check and correct if necessary
		if ($result) {
			// format $taxonomy correctly; it needs opening and ending pipelines
			$taxonomy = (isset($input['taxonomy'])) ? $input['taxonomy'] : '';
			$taxonomy = '|' . trim($taxonomy, '|') . '|';
			if ($taxonomy == '||') {
				$taxonomy = '';
			}

			if (isset($result->taxonomy) && $taxonomy && $result->taxonomy != $taxonomy) {
				$result_taxonomy = $result->taxonomy;
				$previous_taxonomy = explode('|', trim($result_taxonomy, '|'));
				$taxonomy = explode('|', trim($taxonomy, '|'));
				$complete_taxonomy = array_merge($previous_taxonomy, $taxonomy);
				$complete_taxonomy = '|' . implode('|', $complete_taxonomy) . '|';

				$taxonomy_id = $result->taxonomy_id;
				$query = "UPDATE " . TABLE_PREFIX . "components_meta SET meta_value='$complete_taxonomy' WHERE id='$taxonomy_id'";
				$vce->db->query($query);

				$message = 'Added new taxonomy to : ' . $import_url;
				echo json_encode(array('response' => 'success','message' => $message,'action' => ''));
				return;


			}



			$message = 'External link exists in glossary : ' . $import_url;
			echo json_encode(array('response' => 'error','message' => $message,'action' => ''));
			return;
		}



		// import_url has value, so continue
		if (isset($input['import_url'])) {

			// gather all info needed to create media item
			$asset_info = array();
	
			$asset_info['import_url'] = (isset($input['import_url']))? $input['import_url']: 'none';
			$asset_info['media_type'] = 'ExternalLink';
			$asset_info['name'] = (isset($input['name']))? str_replace('_',' ',preg_replace('/\.\w{3}$/','',$input['name'])) : 'none';
			// sanitize name
			$asset_info['name'] = str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $asset_info['name']));
			$asset_info['title'] = $asset_info['name'];
			// sanitize description
			$asset_info['description'] = (isset($input['description']))? str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $input['description'])) : '';
		
			
// $vce->log($asset_info);
// return;


			$err_msg = ''; 
			$localpath = '';


			// get basepath to use in getting location of Media component so we can instantiate it
			$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
			// construct full path to Media
			$media_component_path = $basepath . '/vce-application/components/Media/Media.php';

			// Media seems to be already included
			// require_once($media_component_path);
			$Media = new Media();

			// format $taxonomy correctly; it needs opening and ending pipelines
			$taxonomy = (isset($input['taxonomy'])) ? $input['taxonomy'] : '';
			$taxonomy = '|' . trim($taxonomy, '|') . '|';
			if ($taxonomy == '||') {
				$taxonomy = '';
			}

			$media_input = array (
				"type" => "Media",
				"parent_id" => $resource_library_component_id,
				"sequence" => 1,
				"name" => $asset_info['name'],
				'created_by' => $vce->user->user_id,
				"title" => $asset_info['title'],
				"description" => $asset_info['description'],
				"media_type" => $asset_info['media_type'],
				'link' => $asset_info['import_url'],
				'resource_library_component_id' => $resource_library_component_id,
				'taxonomy' => $taxonomy
			);

			// {"type":"Media","parent_id":"17858","sequence":1,"name":"smallvid.mov","inputtypes":"[]","created_by":"13","title":"smallvid","media_type":"VimeoVideo","path":"13_1673633671.mov"}

			if (!empty($media_input ['link']) && !empty($media_input ['title'])) {
				$new_component_id = $Media->create($media_input);
			} else {
				$message = 'Entry was missing information, skipping.';
				echo json_encode(array('response' => 'error','message' => $message,'action' => ''));
				return;
			}

		}


	//no direct messaging because Component class creates message
		// $message = 'Done saving '. $asset_info['title'] . ': ' . $asset_info['import_url'] . ' to component_id: ' . $new_component_id;
		// echo json_encode(array('message2' => $message,'action' => ''));
		return;
	
	}

	 /**
	 * deletes file from uploads after it has been uploaded to Vimeo
	 */

	public static function unlink_uploads($input) {
		global $vce;


		// check if this is a using a common install
		$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;

		$uploads_directory = $basepath . PATH_TO_UPLOADS . DIRECTORY_SEPARATOR . $vce->user->user_id;

		$files_in_uploads_dir = $scanned_directory = array_diff(scandir($uploads_directory), array('..', '.'));
		
		$vids_in_uploads_dir = array();
		foreach ($files_in_uploads_dir as $this_file) {

			$this_file = $uploads_directory . DIRECTORY_SEPARATOR . $this_file;
			$path_parts = pathinfo($this_file);
			if ($path_parts['extension'] == 'mp4' || $path_parts['extension'] == 'mov') {
				if (file_exists($this_file)) {
					unlink($this_file);
				}
			}

		}


		// if (file_exists($file_path)) {
		// 	//delete file
		// 	unlink($file_path);
		// }		

			return true;

	}





	public static function repair_description($input) {
	
		global $vce;

		if (!isset($input['asset_id']) || empty($input['asset_id'])){
			$message = 'No asset id';
			echo json_encode(array('response' => 'error','message' => $message,'action' => ''));
			return;
		}




		// $vce->log($input);
		
        // return;

		//retrieve_attributes for webdam restful api
		$webdam_access_token = $vce->site->retrieve_attributes('webdam_access_token');
		$webdam_token_expires = $vce->site->retrieve_attributes('webdam_token_expires');
		$webdam_refresh_token = $vce->site->retrieve_attributes('webdam_refresh_token');


	
		$_refresh_token = 'b9bbcbc2eafa2fd4461454c8f5c48c621bfedc3e';

		$download = 'http://earlyeducoach.org/webdam/download.php';
		$limit_value = 20;
		$current_limit = isset($input['current_limit']) ? $input['current_limit'] : 0;
		$current_offset = ($current_limit == 0) ? 0 : $current_limit;

		$curlCounter = 0;

		// generate webdam_access_token if it doesn't exist
		if (!isset($webdam_access_token) || (isset($webdam_token_expires) && $webdam_token_expires < time())) {

			if (!isset($webdam_refresh_token)) {

				// Getting the access_token using grant type "password"

				$url = 'https://apiv2.webdamdb.com/oauth2/token';

				$post = array(
				'grant_type' => 'password',
				'client_id' => '44b79782ca114d2898d9d8778fe82030693b1995',
				'client_secret' => '1a05910aa040979a126ed2bbb0ea13b205f354bd',
				'username' => 'eedulib',
				'password' => 'h9$#hKuQ'
				);

				$curlCounter++;
				// http://php.net/manual/en/curl.examples-basic.php
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$results = json_decode(curl_exec($ch));
				curl_close($ch);

				// add to page object
				$webdam_access_token = $results->access_token;
				$vce->site->add_attributes('webdam_access_token', $webdam_access_token, true);

				$webdam_token_expires = time() + $results->expires_in;
				$vce->site->add_attributes('webdam_token_expires', $webdam_token_expires, true);

				$webdam_refresh_token = $results->refresh_token;
				$vce->site->add_attributes('webdam_refresh_token', $webdam_refresh_token, true);
		

			} else {
	
				// Getting the access_token using grant type "refresh_token"

				$url = 'https://apiv2.webdamdb.com/oauth2/token';

				$post = array(
				'grant_type' => 'refresh_token',
				'refresh_token' => $webdam_refresh_token,
				'client_id' => '44b79782ca114d2898d9d8778fe82030693b1995',
				'client_secret' => '1a05910aa040979a126ed2bbb0ea13b205f354bd',
				'redirect_uri' => 'http://earlyeducoach.org/webdam/'
				);

				$curlCounter++;
				// http://php.net/manual/en/curl.examples-basic.php
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$results = json_decode(curl_exec($ch));
				curl_close($ch);
				
				$webdam_access_token = $results->access_token;
				$vce->site->add_attributes('webdam_access_token', $webdam_access_token, true);

				$webdam_token_expires = time() + $results->expires_in;
				$vce->site->add_attributes('webdam_token_expires', $webdam_token_expires, true);
			}

		}   //end of generate webdam_access_token
		


		

		


		// asset_id has value
		if (isset($input['asset_id'])) {

			$asset_id = $input['asset_id'];

			$headers = array(
			"Authorization: Bearer " . $webdam_access_token
			);


			// get basic info from asset
			$url = 'https://apiv2.webdamdb.com/assets/'.$asset_id;


			$curlCounter++;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$results = json_decode(curl_exec($ch));
			curl_close($ch);
// $vce->log($results);




			$asset_info = array();
	
			if (isset($results)) {
				$asset_info['webdam_id'] = (isset($results->id))? $results->id : 'none';
				$asset_info['filetype'] = (isset($results->filetype))? $results->filetype : 'none';
				$asset_info['name'] = (isset($results->name))? str_replace('_',' ',preg_replace('/\.\w{3}$/','',$results->name)) : 'none';
				// sanitize name
				$asset_info['name'] = str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $asset_info['name']));
				// sanitize description
				$asset_info['description'] = (isset($results->description))? str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $results->description)) : '';
				$asset_info['url'] = (isset($results->hiResURLRaw))? $results->hiResURLRaw : '';
				$description =  (isset($results->description))? str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $results->description)) : '';
			}


			// get metadata info from asset
			$url = 'https://apiv2.webdamdb.com/assets/'.$asset_id.'/metadatas/xmp';


			$curlCounter++;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$results = json_decode(curl_exec($ch));
			curl_close($ch);
// $vce->dump($results);
// $vce->log($results);
// return;

	
			if (isset($results)) {
				$caption =  (isset($results->caption))? str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $results->caption)) : '';
				// $asset_info['caption'] = (isset($results->caption))? $results->caption : 'none';
				$asset_info['keywords'] = (isset($results->keyword))? $results->keyword : '';		
				$asset_info['keywords'] = ($asset_info['keywords'] != 'none') ? explode(',', $asset_info['keywords']) : array();
				// add language to keywords
				$asset_info['keywords'][] = ($asset_info['language'] != '') ? $asset_info['language'] : null;
				// add age group to keywords
				$asset_info['keywords'][] = ($asset_info['age_group'] != '') ? $asset_info['age_group'] : null;
				// add teaching practices to keywords
				if (isset($results->customfield15) && $results->customfield15 != '') {
					$results->customfield15 = explode(',', $results->customfield15);
					foreach ($results->customfield15 as $this_teaching_practice) {
						$asset_info['keywords'][] = $this_teaching_practice;
					}
				}
				// sanitize keywords
				$sanitized_keywords = array();
				foreach ($asset_info['keywords'] as $this_keyword) {
					$this_keyword = str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $this_keyword));
					$sanitized_keywords[] = $this_keyword;
				}

				$asset_info['keywords'] = implode('|', $sanitized_keywords);


			}

// 			if ($description != $caption) {
// // $vce->log($description);
// $vce->log($caption);
// 			}
// return;








 
			if ($description != $caption) {

			



				// check if video already exists, if so update it
				$webdam_id = $input['asset_id'];
				$query = "SELECT component_id FROM " . TABLE_PREFIX . "components_meta WHERE meta_key = 'webdam_id' AND meta_value = $webdam_id";
				$result = $vce->db->get_data_object($query);
				if ($result) {
					
					foreach ($result AS $k => $v) {
						$component_id = $v->component_id;
						// $vce->log($component_id);
					}
					$query = "UPDATE " . TABLE_PREFIX . "components_meta SET meta_value = '$caption' WHERE meta_key = 'description' AND component_id = $component_id";
					$result = $vce->db->query($query);
					
					$message = 'Updated existing video : ' . $webdam_id;
					echo json_encode(array('response' => 'success','message' => $message, 'component_id' => $component_id, 'action' => ''));
					return;
				}



			}

			$message = $asset_info['webdam_id'] . ' is already up to date';
			echo json_encode(array('response' => 'success','message' => $message,'action' => ''));
			return;
		}

		


				$message = 'no asset id';
			echo json_encode(array('response' => 'error','message' => $message,'action' => ''));
			return;

	
	}

	

	 /**
	 * deletes list of videos
	 */

	public static function delete_video($input) {
	
		global $vce;

		if (!isset($input['asset_id']) || empty($input['asset_id'])){
			$message = 'No asset id';
			echo json_encode(array('response' => 'success','message' => $message,'action' => ''));
			return;
		}

		// check if video already exists
		$webdam_id = $input['asset_id'];
		$query = "SELECT a.component_id AS component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'webdam_id' AND a.meta_value = $webdam_id AND b.meta_key = 'created_at'";
		$result = $vce->db->get_data_object($query);
		
		if ($result) {

			$component_ids_to_delete = array();
			$created_ats_to_delete = array();
			$i = 0;
			foreach($result as $this_result) {
				$component_ids_to_delete[$i] = $this_result->component_id;
				$created_ats_to_delete[$i] = $this_result->created_at;
				$i++;
			}

			$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
			// construct full path to Media
			$media_component_path = $basepath . '/vce-application/components/Media/Media.php';
	
			// require_once($media_component_path);
			$Media = new Media();

			foreach ($component_ids_to_delete AS $k=>$v) {
				if (!isset($component_ids_to_delete[$k]) || !isset($created_ats_to_delete[$k])){
					continue;
				}
				
				$media_input = array (
					'type' => 'Media',
					'procedure' => 'delete',
					'component_id' => $component_ids_to_delete[$k] ,
					'created_by' => $vce->user->user_id,
					'created_at' => $created_ats_to_delete[$k],
					'media_type' => 'VimeoVideo',
				);
					
				$Media->delete($media_input);

			}

			// $message = 'Deleted '. $input['asset_id'];
			// echo json_encode(array('response' => 'success','message' => $message,'action' => ''));
			return;
		} else {
			$message = 'Not found '. $input['asset_id'];
			echo json_encode(array('response' => 'success','message' => $message,'action' => ''));
			return;
		}



	}

	 /**
	 * hide this component from being added to a recipe
	 */
	public function recipe_fields($recipe) {
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
        $url = isset($recipe['url']) ? $recipe['url'] : null;

        $elements = <<<EOF
<input type="hidden" name="auto_create" value="forward">
<label>
<input type="text" name="title" value="$title" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Title</div>
<div class="label-error">Enter a Title</div>
</div>
</label>
<label>
<input type="text" name="url" value="$url" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">URL</div>
<div class="label-error">Enter a URL</div>
</div>
</label>
EOF;

        return $elements;
	}

}
