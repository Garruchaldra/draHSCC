<?php

class HTMLPurifierComponent extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'HTMLPurifier',
			'description' => 'HTML/XHTML textarea filter for input using HTMLPurifier',
			'category' => 'XSS'
		);
	}

	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
		
		$content_hook = array (
		'input_sanitize_textarea' => 'HTMLPurifierComponent::sanitize_textarea'
		);

		return $content_hook;

	}

	/**
	 * add a user attribute
	 */
	public static function sanitize_textarea($value) {

		// load htmlpurifier
		require_once(dirname(__FILE__) . '/htmlpurifier/HTMLPurifier.auto.php');
		$config = HTMLPurifier_Config::createDefault();
		$purifier = new HTMLPurifier($config);
		
		$filter = $purifier->purify($value);

		return $filter;
		
	}

	
	/**
	 * hide this component from being added to a recipe
	 */
	public function recipe_fields($recipe) {
		return false;
	}

}