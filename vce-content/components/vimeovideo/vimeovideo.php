<?php

class VimeoVideo extends MediaType {

	//these are the current defaults for the Vimeo API, but are overridden by the settings in the $site object. 
	const VIMEO_CLIENT_ID = 'client_id';
	const VIMEO_CLIENT_SECRET = 'client_secret';
	const VIMEO_ACCESS_TOKEN = 'access_token';
	const VIMEO_DOMAIN_PRIVACY_SETTING = 'yoursite.com';


    public $_curl_opts = array();
    public $CURL_DEFAULTS = array();

	/**
	 * basic info about the component
	 * This component uses the Vimeo PHP API library.
	 * The Vimeo PHP API library has some dependencies, and all of the necessary files are in the src/Vimeo and vendor directories.
	 * The Vimeo PHP API library is updated using Composer, which must be installed on the dev machine.
	 * The composer.json and composer.lock files in this component's root directory are all that is needed to keep the Vimeo API up to date.
	 * The composer.json file has an entry for our server's PHP version. This must be there, otherwise it defaults to the highest version possible, which is incompatible.
	 * To update the Vimeo PHP API library, open the VimeoVideo root directory and use the command "composer update".
	 * There are 6 calls from VimeoVideo.php to the Vimeo PHP API library. One is $vimeo->upload(), and the other 5 are $vimeo->request(), which covers 
	 * delete, read, and update functions. These all need to be validated on each Vimeo PHP API library update.
	 */
	public function component_info() {
		return array(
			'name' => 'VimeoVideo (Media Type)',
			'description' => 'Adds Vimeo Video to Media',
			'category' => 'media',
			'typename' => 'Video'
		);
	}

	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
	
	$content_hook = null;
		
		$content_hook = array (
			'media_create_component' => 'VimeoVideo::custom_create',
			'media_update_component' => 'VimeoVideo::custom_update',
			'media_delete_component' => 'VimeoVideo::custom_delete'
		);

		return $content_hook;

	}
	
	
	/**
	 * instantiate Vimeo API class
	 * necessary for most video functions, including upload, renaming, viewing, etc.
	 */
	public static function instantiate_vimeo() {

		global $vce;
		
		// Get stored values from database
		if (isset($vce->site->VimeoVideo)) {
			$value = $vce->site->VimeoVideo;
			$vector = $vce->site->VimeoVideo_minutia;
			$config = json_decode($vce->site->decryption($value,$vector), true);
			
			$error = false;
			$vimeo_client_id = isset($config['vimeo_client_id']) ? $config['vimeo_client_id'] : $error = true;
			$vimeo_client_secret = isset($config['vimeo_client_secret']) ? $config['vimeo_client_secret'] : $error = true;
			$vimeo_access_token = isset($config['vimeo_access_token']) ? $config['vimeo_access_token'] : $error = true;
		
			if ($error) {
				echo json_encode(array('response' => 'error','procedure' => 'create','message' => "Error: VimeoVideo has not been configured"));
				return;
			}

		} else {
			echo json_encode(array('response' => 'error','procedure' => 'create','message' => "Error: VimeoVideo has not been configured"));
			return;	
		}

		//All API methods are within the Vimeo.php file
		require dirname(__FILE__) . '/vendor/autoload.php';
        require_once(dirname(__FILE__) . '/src/Vimeo/Vimeo.php');
        $vimeo = new Vimeo\Vimeo($vimeo_client_id, $vimeo_client_secret, $vimeo_access_token);
		return $vimeo;
	}
	

	/**
	 * called by the function create() in Media Component.
	 * allows for an alternative method to create componenet specific to media type.
	 */
	public static function custom_create($input) {

		global $vce;
		if ($input['media_type'] == "VimeoVideo") {
			
			//instantiate API class
			$vimeo = self::instantiate_vimeo();
			
			// check if this is a using a common install
			$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
			
		 	// construct full path to video
			$uploaded_video_file = $basepath . PATH_TO_UPLOADS . '/' . $input['created_by'] . '/' . $input['path'];		

			//  Send the files to the upload script.
			try {
				//  Send this to the API library.
				$uri = $vimeo->upload($uploaded_video_file);
				//  Now that we know where it is in the API, let's get the info about it so we can find the link.
				$video_data = $vimeo->request($uri);
				//  The script will pause until the upload is complete
				$link = '';
				if ($video_data['status'] == 200) {
					$link = $video_data['body']['link'];
					$videoID = str_replace('/videos/', '', $video_data['body']['uri']);

					// change name of video to title from input
					$vimeo->request('/videos/'.$videoID, array('name' => $input['title'], 'privacy' => array('download' => 'false', 'view' => 'unlisted'), 'embed' => array('buttons' => array('share' => 'false', 'watchlater' => 'false', 'like' => 'false', 'embed' => 'false'))), 'PATCH');
				}
			}

			catch (Exception $e) {
			
				if (!isset($uri)){
					echo json_encode(array('response' => 'error','procedure' => 'create','message' => "Error: VimeoVideo did not return a URI"));
					return;
				}
			
				//  We may have had an error.  We can't resolve it here necessarily, so report it to the user.
				echo json_encode(array('response' => 'error','procedure' => 'create','message' => "Error: There has been an upload error. The server reports: ".$e->getMessage()));
				return;	
			}
		
			if (isset($videoID)) {

				$input['guid'] = $videoID;
				
			} else {
			
				echo json_encode(array('response' => 'error','procedure' => 'create','message' => "Error: Vimeo did not return a GUID"));
				return;	
			
			}		
		
		}
		
		return $input;

	}



	/**
	 * called by the function update() in Media Component.
	 * allows for an alternative method to update componenet specific to media type.
	 */
	public static function custom_update($input) {

		global $vce;
		
		if  (isset($input['media_type']) && $input['media_type'] == "VimeoVideo") {
		
			$query = "SELECT meta_value FROM " . TABLE_PREFIX . "components_meta WHERE component_id='" . $input['component_id'] . "' AND meta_key='guid'";
			$guid_data = $vce->db->get_data_object($query);
			
			if (isset($guid_data[0]->meta_value)) {
				$guid = $guid_data[0]->meta_value;
				$title = $input['title'];
			}

			//instantiate API class
			$vimeo = self::instantiate_vimeo();
			$vimeo->request('/videos/'.$guid, array('name' => $input['title']), 'PATCH');

		}

	 	return $input;
	}
	
	/**
	 * called by the function update() in Media Component.
	 * allows for an alternative method to update componenet specific to media type.
	 */
	public static function custom_delete($input) {
	
		if  (isset($input['media_type']) && $input['media_type'] == "VimeoVideo") {
		
			global $vce;	

			$vimeo = self::instantiate_vimeo();
			
			$query = "SELECT meta_value FROM " . TABLE_PREFIX . "components_meta WHERE component_id='" . $input['component_id'] . "' AND meta_key='guid'";
			
			$guid_data = $vce->db->get_data_object($query);

			if (isset($guid_data)) {
				$guid = $guid_data[0]->meta_value;
				$vimeo->request('/videos/' . $guid, array(), 'DELETE');
			}
		}
		return $input;
	}
	
	/**
	 * 
	 */
	public function display($each_component, $vce) {

		// add the "$inputtypes" value for use in all forms
		// This is to guard against asynchronous call errors when JS is not enabled.
		$inputtypes = json_encode(array());
	
		if (isset($vce->site->VimeoVideo)) {
		
			$vimeo = self::instantiate_vimeo();

			if (isset($each_component->guid) && $each_component->guid != "") {
			 	$video_data = $vimeo->request('/videos/' . $each_component->guid, array('embed' => array('logos' => array('vimeo' => 'false'))), 'PATCH');
		
			} else {

				// No GUID was found, so diplay an error message
				
				// path to image
				$path = $vce->site->path_to_url(dirname(__FILE__));
				
$content = <<<EOF
<div class="video-processing red">
<div class="video-processing-icon">
<img src="$path/images/clock.png">
<div class="video-processing-time">-</div>
</div>
<div class="video-processing-message">An error has occured while processing this video. No GUID was assigned.</div>
</div>
EOF;
				
			}
			
			// add javascript library for VimeoVideo
			$vce->site->add_script(dirname(__FILE__) . '/js/player.min.js');
			
			// add javascript to page
			$vce->site->add_script(dirname(__FILE__) . '/js/script.js');
		
			// add javascript to page
			$vce->site->add_style(dirname(__FILE__) . '/css/style.css');

			if (isset($video_data['status']) && $video_data['status'] == 200) {

				// check if video is still being processed by vimeo
				// if (!empty($video_data['body']['files']) && $video_data['body']['files'][0]['size'] != 0 ) {
				if (!empty($video_data['body']['transcode']['status']) && $video_data['body']['transcode']['status'] == 'complete' ) {

					// check if a meta_key has been set, if not write and delete original file
					if (!isset($each_component->published)) {
						
						// set duration from video data
						$records[] = array(
						'component_id' => $each_component->component_id,
						'meta_key' => 'duration',
						'meta_value' => ($video_data['body']['duration'] * 1000),
						'minutia' => null
						);
						
						$each_component->duration = $video_data['body']['duration'] * 1000;

						// thumbnail
						$records[] = array(
						'component_id' => $each_component->component_id,
						'meta_key' => 'thumbnail_url',
						'meta_value' => $video_data['body']['pictures']['sizes'][1]['link'],
						'minutia' => null
						);
				

						// published
						$records[] = array(
						'component_id' => $each_component->component_id,
						'meta_key' => 'published',
						'meta_value' => strtotime($video_data['body']['created_time']),
						'minutia' => null
						);
				
				
						$vce->db->insert('components_meta', $records);
						
						// check if this is a using a common install
						$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
						
						$file_path = $basepath . PATH_TO_UPLOADS . DIRECTORY_SEPARATOR . $each_component->created_by . DIRECTORY_SEPARATOR . $each_component->path;

						if (file_exists($file_path)) {
							//delete file
							unlink($file_path);
						}					
					}
					
					$duration = isset($each_component->duration) ? $each_component->duration : 0;
					
					// this needs to have a non-null value so that count() in the next line can process it (changed in PHP 8.1)
					$video_data['body']['files'] = (!isset($video_data['body']['files'])) ? array() : $video_data['body']['files'];

					// get the highest quality video
					$this_count = count($video_data['body']['files']) - 1;
					$video_link = $video_data['body']['files'][$this_count]['link_secure'];
					
	
					// iframe code
					$iframe_html = html_entity_decode($video_data['body']['embed']['html']);
					
					// extract src from iframe_html
					preg_match('/.*src="([^%"]+)"/', $iframe_html, $matches);
					$video_link = $matches[1];
					
					//replace actual width and height with display width and height
					//$iframe_html = preg_replace('/width="([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9])"/', 'width="640"' , $iframe_html);
					//$iframe_html = preg_replace('/height="([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|[1-9][0-9][0-9][0-9])"/', 'height="360"' , $iframe_html);

					// the old, constructed iframe
					// <iframe class="player" id="player-$each_component->component_id" src="https://player.vimeo.com/video/$each_component->guid?badge=0&autopause=0&player_id=0&byline=0&portrait=0&owner=0" scrolling="no" frameBorder="0" seamless="seamless" allowFullScreen="true" style="width:100%;height:550px" timestamp="0" duration="$duration"></iframe>

$content = <<<EOF
<div class="vidbox" player="player-$each_component->component_id">
	<button class="vidbox-click-control"></button>
	<div class="vidbox-content">
		<button class="vidbox-content-close">X</button>
		<div class="vidbox-content-area"></div>
	</div>
	<iframe class="player" id="player-$each_component->component_id" src="$video_link" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="{$each_component->title}" timestamp="0" duration="$duration"></iframe>
</div>
EOF;

	// display title
	if (isset($each_component->recipe['display_title']) && $each_component->recipe['display_title'] == 'on') {
		$vce->content->add('main','<div class="media-title">' . $each_component->title . '</div>');
	}  
					
// <button id="rotate-player-$each_component->component_id">Rotate</button>

				} else {
				
					// video processing message
				
					// path to image
					$path = $vce->site->path_to_url(dirname(__FILE__));

					$class = "green";
					$how_log_ago = '';
					$seconds = time() - $each_component->created_at;
					$minutes = (int)($seconds / 60);
					$hours = (int)($minutes / 60);
					$days = (int)($hours / 24);
					if ($hours >= 6) {
						$class = "red";
						$how_log_ago = $hours . ' hour' . ($hours != 1 ? 's' : '');
					
						if ($hours >= 24) {
							$how_log_ago = $days . ' day' . ($days != 1 ? 's' : '');
						}

						if ($vce->page->can_delete($each_component)) {
		
							// the instructions to pass through the form
							$dossier = array(
							'type' => 'Media',
							'procedure' => 'delete',
							'component_id' => $each_component->component_id,
							'created_at' => $each_component->created_at,
							'media_type' => $each_component->media_type,
							'parent_url' => $vce->page->requested_url
							);

							// generate dossier
							$dossier_for_delete = $vce->generate_dossier($dossier);
			
$message = <<<EOF
An error has occured while encoding this video. Please delete and upload again.
<div style="float:right;margin-left:20px;">
<form id="delete_$each_component->component_id" class="delete-form asynchronous-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<input type="hidden" name="inputtypes" value="$inputtypes">
<input type="submit" value="Delete">
</form>
</div>
EOF;
			
						} else {
					
$message = <<<EOF
An error has occured while encoding this video.
EOF;
						}
				
					} else if ($hours >= 1) {
						$class = "yellow";
						$how_log_ago = $hours . ' hour' . ($hours != 1 ? 's' : '');
						$message = "This video has been successfully uploaded and is now being encoded, which may take several hours.";
					} else if ($minutes >= 1) {
						$how_log_ago = $minutes . ' minute' . ($minutes != 1 ? 's' : '');
						$message = "This video has been successfully uploaded and is now being encoded, which may take an hour.";
					} else {
						$how_log_ago = $seconds . ' second' . ($seconds != 1 ? 's' : '');
						$message = "This video has been successfully uploaded and is now being encoded, which may take several minutes.";
					}
	
	
$content = <<<EOF
<div class="video-processing $class">
<div class="video-processing-icon">
<img src="$path/images/clock.png">
<div class="video-processing-time">$how_log_ago</div>
</div>
<div class="video-processing-message">$message</div>
<button class='reload-button'>Refresh</button>
</div>
EOF;
				
				}
		
			} else {
			
				// path to image
				$path = $vce->site->path_to_url(dirname(__FILE__));
			
				// video can't find video at URI
				if (isset($video_data['status']) && $video_data['status'] == 405) {

$content = <<<EOF
<div class="video-processing red">
<div class="video-processing-icon">
<img src="$path/images/clock.png">
<div class="video-processing-time">-</div>
</div>
<div class="video-processing-message">An error has occured. Vimeo unable to find video URI.</div>
</div>
EOF;
						
				} else {
					
$content = <<<EOF
<div class="video-processing red">
<div class="video-processing-icon">
<img src="$path/images/clock.png">
<div class="video-processing-time">-</div>
</div>
<div class="video-processing-message">An error has occured. End of method reached.</div>
</div>
EOF;
					
				}
			
			}
    	
    		$vce->content->add('main',$content);
    	
		}
    }
    
    /**
     * file uploader needed
     */
    public static function file_upload() {
	 	return true;
	}
	
	
	/**
	 * a way to pass file extensions to the plupload to limit file selection
	 */
	 public static function file_extensions() {
	 	//{title:'Image files',extensions:'gif,png,jpg,jpeg'};
	 	return array('title' => 'video files','extensions' => 'mpg,mpeg,mov,mp4,m4v,wmv,avi,asx,asf');
	 }
	 
	 /**
	  * a way to pass the mimetype and mimename to vce-upload.php
	  * the minename is the class name of the mediaplayer.
	  * mimetype can have a wildcard for subtype, included after slash by adding .*
	  */
	 public static function mime_info() {
	 	return array(
	 	'video/.*' => get_class()
	 	);
	 }



	/**
	 * return form fields
	 */
	public function component_configuration() {
	
		global $vce;
		
		$vimeo_client_id = self::VIMEO_CLIENT_ID;
		$vimeo_client_secret = self::VIMEO_CLIENT_SECRET;
		$vimeo_access_token = self::VIMEO_ACCESS_TOKEN;
		$vimeo_domain_privacy_setting = self::VIMEO_DOMAIN_PRIVACY_SETTING;

		if (isset($vce->site->VimeoVideo)) {
			
			$value = $vce->site->VimeoVideo;
			$vector = $vce->site->VimeoVideo_minutia;
			$config = json_decode($vce->site->decryption($value,$vector), true);
			
			$vimeo_client_id = isset($config['vimeo_client_id']) ? $config['vimeo_client_id'] : '';
			$vimeo_client_secret = isset($config['vimeo_client_secret']) ? $config['vimeo_client_secret'] : '';
			$vimeo_access_token = isset($config['vimeo_access_token']) ? $config['vimeo_access_token'] : '';
			$vimeo_domain_privacy_setting = isset($config['vimeo_domain_privacy_setting']) ? $config['vimeo_domain_privacy_setting'] : $_SERVER['HTTP_HOST'];

		}
		
$elements = <<<EOF
<label>
<input type="text" name="vimeo_client_id" value="$vimeo_client_id" autocomplete="off">
<div class="label-text">
<div class="label-message">Vimeo Client Id</div>
<div class="label-error">Enter Vimeo Client Id</div>
</div>
</label>
<label>
<input type="text" name="vimeo_client_secret" value="$vimeo_client_secret" autocomplete="off">
<div class="label-text">
<div class="label-message">Vimeo Client Secret</div>
<div class="label-error">Enter Vimeo Client Secret</div>
</div>
</label>
<label>
<input type="text" name="vimeo_access_token" value="$vimeo_access_token" autocomplete="off">
<div class="label-text">
<div class="label-message">Vimeo Access Token</div>
<div class="label-error">Enter Vimeo Access Token</div>
</div>
</label>
<label>
<input type="text" name="vimeo_domain_privacy_setting" value="$vimeo_domain_privacy_setting" autocomplete="off">
<div class="label-text">
<div class="label-message">Vimeo Domain Privacy Setting</div>
<div class="label-error">Enter the Domain in Which Videos are Viewable</div>
</div>
</label>
EOF;

		return $elements;

	}

}




