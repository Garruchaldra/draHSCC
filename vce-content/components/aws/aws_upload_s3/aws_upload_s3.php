<?php

require_once __DIR__ . '/../aws_support.php';

class AWSUploadS3 extends Component
{

    use AWSSupport;

    /**
     * basic info about the component
     */
    public function component_info()
    {
        return array(
            'name' => 'AWSUploadS3',
            'description' => 'Asynchronous Amazon s3 upload portal using AWS javascript',
            'category' => 'uploaders',
        );
    }

    /**
	 * create component specific database table when installed
	 */
	public function activated() {
	
		global $vce;
		$sql = "CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "uploads_s3 (`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,`start_time` int(11) UNSIGNED NOT NULL,`end_time` int(11) UNSIGNED NOT NULL,`user_agent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,`location` varchar(255) COLLATE utf8_unicode_ci NOT NULL,`user_id` int(11) UNSIGNED NOT NULL,`file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,`file_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,`file_size` int(11) UNSIGNED NOT NULL,`chunks_total` int(11) UNSIGNED NOT NULL,`chunks_resumed` int(11) UNSIGNED NOT NULL,`chunks_completed` int(11) UNSIGNED NOT NULL,`uploaded_size` int(11) UNSIGNED NOT NULL,`destination` varchar(255),`status` varchar(255) COLLATE utf8_unicode_ci NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$vce->db->query($sql);
	}
	
	/**
	 * clear component specific database table when disabled
     * NOTE: Currently we are not deleting the table.
	 */
	public function disabled() {
//		global $vce;
//		$sql = "DROP TABLE IF EXISTS " . TABLE_PREFIX . "uploads_s3;";
//		$vce->db->query($sql);
	}

    /**
	 * This method can be used to route a url path to a specific component method. 
	 */
	public function path_routing() {
	
		$path_routing = array(
			'begin_log_activity' => array('AWSUploadS3','begin_log_activity'),
			'log_activity' => array('AWSUploadS3','log_activity')
		);
		 
		return $path_routing;

    }

    public function begin_log_activity() {

        global $vce;

        // title
        $records[] = array(
        'location' => $this->post_variables['path'],
        'start_time' => time(),
        'end_time' => time(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'user_id' => $this->post_variables['created_by'], 
        'file_name' => $this->post_variables['filename'],
        'file_type' => $this->post_variables['filetype'],
        'file_size' => '0',
        'chunks_total' => '0',
        'chunks_resumed' => '0',
        'chunks_completed' => '0',
        'uploaded_size' => '0',
        'status' => 'starting',
        'destination' => $this->post_variables['path']
        );

        $vce->db->insert('uploads_s3', $records);

        echo json_encode(array('status' => 'success', 'message' => 'begin activity logged'));
        exit();

    }

    public function log_activity() {
	
		global $vce;
        
        $update = array(
            'end_time' => time(),
            'file_size' => $this->post_variables['total'],
            'chunks_total' => $this->post_variables['chunk'],
            'chunks_completed' => $this->post_variables['chunk'],
            'uploaded_size' => $this->post_variables['loaded'],
            'status' => $this->post_variables['status']
        );

		$update_where = array('location' => $this->post_variables['path']);
        $vce->db->update('uploads_s3', $update, $update_where);
        
        echo json_encode(array('status' => 'success', 'message' => 'activity logged'));
        exit();

    }

    public function preload_component()
    {

        $content_hook = array(
            'media_delete_component' => 'AWSUploadS3::media_delete_component',
            'media_file_uploader' => 'AWSUploadS3::add_transcript_checkbox',
            'media_add_file_uploader' => 'AWSUploadS3::add_file_uploader'
        );

        return $content_hook;
    }

    /**
     * add create transcript checkbox to file upload form
     */
    public static function add_transcript_checkbox($recipe_component, $vce)
    {

        $config = AWSUploadS3::get_config($vce);

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
    public static function media_delete_component($input)
    {

        global $vce;

        if (isset($input['media_type']) && $input['media_type'] != "AWSVideo") {
            $query = "SELECT meta_value FROM " . TABLE_PREFIX . "components_meta WHERE component_id='" . $input['component_id'] . "' AND meta_key='path'";
            $path_data = $vce->db->get_data_object($query);
            if (isset($path_data[0]->meta_value)) {

                $path = $path_data[0]->meta_value;

                $config = AWSUploadS3::get_config($vce);
                $sdk = AWSUploadS3::get_sdk($config);
                $s3 = $sdk->createS3();
                $result = $s3->deleteObject([
                    'Key' => $path,
                    'Bucket' => AWSUploadS3::source_bucket($config),
                ]);
            }
        }

        return $input;
    }


	/**
	 * File uploader method
	 */
	public static function add_file_uploader($recipe_component, $vce) {
	
		// add a property to page to indicate the the uploader has been added
		$vce->file_uploader = true;
		
        // add javascript to page
        $vce->site->add_script(dirname(__FILE__) . '/js/aws-sdk.min.js');
        
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery jquery-ui');
		
		// add style
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css', 'media-style');
		
		$media_upload_path = $vce->site->site_url . '/upload';

        $config = AWSUploadS3::get_config($vce);
        $bucket = AWSUploadS3::source_bucket($config);
        
        // TODO
        $cancel = AWSUploadS3::language('Cancel');

		// <div class="uploader-container">
$content_media = <<<EOF
<mauploader>
	<div class="progressbar-container">
		<div class="progressbar-title">Upload In Progress</div>
		<div class="progressbar-block">
			<div class="progressbar-block-left">
				<div class="progressbar">
					<div class="progress-chunks" style="position:absolute;padding-left:5px;"></div>
				</div>
			</div>
			<div class="progressbar-block-right">
				<a class="cancel-upload link-button" href="">{$cancel}</a>
			</div>
		</div>
		<div class="progress-label" timestamp=0>0%</div>
		<div class="verify-chunks"></div>
	</div>
	<div class="upload-browse">
		<input class="fileupload" path="$media_upload_path" type="file" accept="">
		<button class="file-upload-cancel cancel-button">{$cancel}</button>
	</div>
EOF;

$content_media .= <<<EOF
	<div class="upload-form">
EOF;

		// the upload file form

        $begin_log_activity = $vce->site_url . '/begin_log_activity';
        $log_activity = $vce->site_url . '/log_activity';

$upload_file = <<<EOF
		<input class="action" type="hidden" name="action" value="$vce->input_path">
		<input class="begin_log_activity" type="hidden" name="begin_log_activity" value="$begin_log_activity">
		<input class="log_activity" type="hidden" name="log_activity" value="$log_activity">
		<input class="dossier" type="hidden" name="dossier" value="$recipe_component->dossier_for_create">
		<input class="inputtypes" type="hidden" name="inputtypes" value="[]">
		<input class="created_by" type="hidden" name="created_by" value="{$vce->user->user_id}">
		<input class="parent_id" type="hidden" name="parent_id" value="{$recipe_component->parent_id}">
		<input class="region" type="hidden" name="region" value="{$config['region']}">
		<input class="identity_pool_id" type="hidden" name="identity_pool_id" value="{$config['credentials']['identity_pool_id']}">
		<input class="version" type="hidden" name="version" value="{$config['version']}">
		<input class="bucket" type="hidden" name="bucket" value="{$bucket}">
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
		<button class="start-upload link-button">Upload</button> <button class="cancel-button cancel-upload link-button">Cancel</button>
EOF;

		$content_media .= $vce->content->accordion('Upload File',$upload_file,true,true);
		
$content_media .= <<<EOF
	</div>
	<div class="progressbar-message"></div>
</mauploader>	
EOF;
// </div> of <div class="uploader-container">

		return $content_media;
	
	}



    /**
     * hide this component from being added to a recipe
     */
    public function recipe_fields($recipe)
    {
        return false;
    }
}
