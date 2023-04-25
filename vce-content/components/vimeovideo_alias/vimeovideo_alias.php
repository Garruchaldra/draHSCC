<?php

class VimeoVideoAlias extends MediaType {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'VimeoVideo Alias (Media Type)',
			'description' => 'An Alias of VimeoVideo',
			'category' => 'media'
		);
	}
	
	/**
	 * get all VimeoVideo vidoes for this user
	 */
    public static function add($recipe_component, $vce) {
    
		// add javascript
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js');
		
		// add style
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');


		// add the "$inputtypes" value for use in all forms
		// This is to guard against asynchronous call errors when JS is not enabled.
		$inputtypes = json_encode(array());
	
$content_mediatype = <<<EOF
<div class="video-alias-container">
EOF;


//old nested query
// $query = "SELECT * FROM  " . TABLE_PREFIX . "components_meta WHERE component_id IN (SELECT component_id FROM  " . TABLE_PREFIX . "components_meta WHERE meta_key='media_type' AND meta_value='VimeoVideo' and component_id IN (SELECT component_id FROM  " . TABLE_PREFIX . "components_meta WHERE meta_key='created_by' AND meta_value='" . $vce->user->user_id . "'))";
//all components created by user
$component_ids = array();
$query = "SELECT component_id FROM  " . TABLE_PREFIX . "components_meta WHERE meta_key='created_by' AND meta_value='" . $vce->user->user_id . "'";
foreach ($vce->db->get_data_object($query) as $component_id) {
	$component_ids[] = $component_id->component_id;
}

$component_ids = implode(',', $component_ids);

//add a null set of videos if none exist
if(!isset($component_ids)){
	$component_ids = 0;
}

//all videos from those components
$query = "SELECT component_id FROM  " . TABLE_PREFIX . "components_meta WHERE meta_key='media_type' AND meta_value='VimeoVideo' and component_id IN ($component_ids)";
$user_video_ids = array();
foreach ($vce->db->get_data_object($query) as $user_video_id) {
	$user_video_ids[] = $user_video_id->component_id;
}

$user_video_ids = implode(',', $user_video_ids);

if (empty($user_video_ids)) {
	$user_video_ids = 0;
}

//all the meta-data from those videos
$query = "SELECT * FROM  " . TABLE_PREFIX . "components_meta WHERE component_id IN ($user_video_ids)";
$meta_data = $vce->db->get_data_object($query);



$videos = array();
foreach ($meta_data as $meta_value) {

	if (!isset($video[$meta_value->component_id]['component_id'])) {
		$videos[$meta_value->component_id]['component_id'] = $meta_value->component_id;
	}

	$videos[$meta_value->component_id][$meta_value->meta_key] = $meta_value->meta_value;

}
if(count($videos) < 1){
$content_mediatype .= <<<EOF
You don't have any previously uploaded videos.
EOF;
	
} 
foreach ($videos as $each_video) {

	if (!isset($each_video['thumbnail_url'])) {
		continue;
	}

	$component_id = $each_video['component_id'];
	$thumbnail_url = $each_video['thumbnail_url'];
	$title = $each_video['title'];

	$query_string = json_decode($vce->page->query_string);
	$qs = '?';
	if (isset($query_string)) {
		foreach ($query_string as $k => $v) {
			$qs .= $k .'=' . $v;
		}
	}
	$redirect_url = $vce->site->site_url . '/' . $recipe_component->parent_url . $qs;
	// $vce->dump($redirect_url);

	// the instructions to pass through the form
	$dossier = array(
	'type' => 'VimeoVideoAlias',
	'procedure' => 'create',
	'alias_id' => $component_id,
	'parent_id' => $recipe_component->parent_id,
	'redirect_url' => $redirect_url
	);

	// generate dossier
	$dossier_for_alias = $vce->generate_dossier($dossier);


$content_mediatype .= <<<EOF
<div class="video-thumbnail-border">
<div class="video-thumbnail" dossier="$dossier_for_alias">
<img src="$thumbnail_url">
<div class="video-title">$title</div>
</div>
</div>
EOF;

}


$content_mediatype .= <<<EOF

</div>

<form id="vimeovideo-alias" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_alias">
<input type="hidden" name="inputtypes" value="$inputtypes">
<input type="submit" value="Submit Selected Video">
</form>
EOF;

	// create accordion box
	$prevUploads_accordion = $vce->content->accordion('Select Previously Uploaded Video', $content_mediatype);

	return $prevUploads_accordion;
	
	}
	
	/**
	 * custom form_input
	 */
	public function form_input($input) {

		// save these two, so we can unset to clean up $input before sending it onward
		$type = trim($input['type']);
		$procedure = trim($input['procedure']);
		
		// unset component and procedure
		unset($input['procedure']);
		
		// check that protected function exists
		if (method_exists($type, $procedure)) {
			// call to class and function
			$type::$procedure($input);	
			return;
		}
		
		echo json_encode(array('response' => 'error','message' => 'Unknown Procedure'));
		return;
	}
	

	protected function create($input) {
	
		global $vce;
		
		// add created by and created at time_stamp
		$input['type'] = 'Alias';
		$input['created_by'] = $vce->user->user_id;
		$input['created_at'] = time();
		
		// create component data
		$parent_id = isset($input['parent_id']) ? $input['parent_id'] : 0;
		$sequence = isset($input['sequence']) ? $input['sequence'] : 1;
		$url = isset($input['url']) ? stripslashes($input['url']) : '';
			
		unset($input['parent_id'], $input['sequence'], $input['url'], $input['current_url']);
	
		$data = array(
		'parent_id' => $parent_id, 
		'sequence' => $sequence,
		'url' => $url
		);
		
		// insert into components table, which returns new component id
		$component_id = $vce->db->insert('components', $data);

		// now add meta data
		$records = array();

		// loop through other meta data
		foreach ($input as $key=>$value) {
		
			// title
			$records[] = array(
			'component_id' => $component_id,
			'meta_key' => $key, 
			'meta_value' => $value,
			'minutia' => null
			);
		
		}

		$vce->db->insert('components_meta', $records);

		if (isset($input['redirect_url'])) {
			$redirect_url = $input['redirect_url'];
			unset($input['redirect_url']);
		} else {
			$redirect_url = NULL;
		}
		
	
		echo json_encode(array('response' => 'success', 'url' => $redirect_url, 'action' => 'reload', 'procedure' => 'create','message' => 'Alias Created'));
		return;

	}


}