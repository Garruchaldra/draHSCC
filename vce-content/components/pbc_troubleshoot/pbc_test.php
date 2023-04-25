<?php

class Pbc_test extends Component {

	/**
	 * basic info about the component
	 * this component was based on the "Reflection_assignment" component
	 */
	public function component_info() {
		return array(
			'name' => 'Test',
			'description' => 'For testing integrated code',
			'category' => 'pbc'
		);
	}

	
	/**
	 * add a hook that fires at initiation of site hooks
	 */
// 	public function preload_component() {
// 		$content_hook = array (
// 			'site_hook_initiation' => 'Pbc_ManageVideos::require_once_mediatype'
// 		);
// 		return $content_hook;
// 	}



	
	/**
	 *
	 */
	public function as_content($each_component, $page) {
		return false;
	
	}



	/**
	 *
	 */
	public static function add_component($recipe_component, $page) {
global $site;
global $user;

$site->log(date("d-m-Y h:i:s"));


// $site->dump($user);
		$recipe_component->dossier_for_create = $page->generate_dossier($recipe_component->dossier);

$content = <<<EOF
<div class="clickbar-container admin-container add-container ignore-admin-toggle">
<div class="clickbar-content">
EOF;

$dossier_for_clear_log = $page->user->encryption(json_encode(array('type' => 'Pbc_test ','procedure' => 'clear_log','user_id' => $user->user_id)),$user->session_vector);
$dossier_for_datalist = $page->user->encryption(json_encode(array('type' => 'Pbc_test ','procedure' => 'datalist','user_id' => $user->user_id)),$user->session_vector);


$content .= <<<EOF
hi!<br>
<form class="inline-form asynchronous-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_for_clear_log">
<input type="submit" value="Clear Log">
</form>
<br>

<form class="inline-form asynchronous-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_for_datalist">
<input type="submit" value="Datalist">
</form>

</div>
<div class="clickbar-title clickbar-closed"><span>Test Content</span></div>
</div>
EOF;
		// add to content object
		$page->content->add('main',$content);

	

	}
	
	
public function clear_log() {
// global $site;
// $site->log('clearlog');
	file_put_contents(BASEPATH . 'log.txt', 'cleared' . date("d-m-Y h:i:s") . PHP_EOL);
}		


public function datalist() {
	global $site;

		// json object with meta_data and sub items
		$items = '[{"name":"name1","items":[{"name":"name2"}]}]';

		$attributes = array (
		'datalist' => 'test01',
		'aspects' => array('type' => 'select'),
		'hierarchy' => array('h1','h2'),
		'items' => json_decode($items, true)
		);
		
		$site->create_datalist($attributes);
}


	
	
	/**
	 * for ManageRecipes class
	 */
	public function recipe_fields($recipe) {
	
		global $site;
	
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
		$media_types = isset($recipe['media_types']) ? $recipe['media_types'] : null;

		
$elements = <<<EOF
<label>
<input type="text" name="title" value="$title" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Title</div>
<div class="label-error">Enter a Title</div>
</div>
</label>
<label for="">
<div class="input-padding">
EOF;

		if (isset($site->enabled_mediatype)) {
			foreach (json_decode($site->enabled_mediatype, true) as $key=>$each_media) {
				$elements .= '<label class="ignore"><input type="checkbox" name="media_types" value="' . $key . '"';
				if (in_array($key,explode('|',$media_types))) {
					$elements .= ' checked="checked"';
				}
				$elements .= '>  ' . $key . '</label> ';
			}
		}

$elements .= <<<EOF
</div>
<div class="label-text">
<div class="label-message">Media Types</div>
<div class="label-error">Must have a Media Type</div>
</div>
</label>
EOF;
		return $elements;
		
	}
	

}