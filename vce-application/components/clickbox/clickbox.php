<?php

class Clickbox extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Clickbox',
			'description' => 'Places sub components into a clickbox',
			'category' => 'accessibility',
			'recipe_fields' => array('auto_create','title')
		);
	}
	
	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
		
		$content_hook = array (
		'page_requested_url' => 'Clickbox::page_requested_url'
		);

		return $content_hook;

	}
	
	/**
	 * method for page_requested_url hook
	 */
	public static function page_requested_url($requested_url, $vce) {
	
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui');
		
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css', 'clickbox-style');

	}
	
	
	/**
	 * start of clickbox
	 */
	public function as_content($each_component, $vce) {
	
$content = <<<EOF
<div class="clickbar-container">
<div class="clickbar-content">	
EOF;

		$vce->content->add('main',$content);

	}
	
	/**
	 * end of clickbox
	 */
	public function as_content_finish($each_component, $vce) {
	
$content = <<<EOF
</div>
<div class="clickbar-title clickbar-closed"><span>$each_component->title</span></div>
</div>
EOF;
	
		$vce->content->add('main',$content);
	
	}

}