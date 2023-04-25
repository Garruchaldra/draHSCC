<?php

class Utility extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Utility',
			'description' => 'A test of utility class',
			'category' => 'utility'
		);
	}
	
	public function activated() {
	
	/*
		global $vce;

		$attributes = array (
		'parent_id' => '0',
		'item_id' => '0',
		'component_id' => '0',
		'user_id' => '0',
		'sequence' => '1',
		'datalist' => 'States_datalist',
		'hierarchy' => array ('State', 'City'),
		'items' => array (
		array (
		'name' => 'WA',
		'state_flower' => 'rhododendron'
		),
		array (
		'name' => 'CA',
		'state_flower' => 'golden poppy',
		'items' => array (
		array (
		'name' => 'Sacramento',
		'mayor' => 'Darrell Steinberg'
		),
		array (
		'name' => 'Los Angeles',
		'mayor' => 'Eric Garcetti')
		)
		)
		)
		);
 
		$vce->create_datalist($attributes);
		
	*/
	
	}

	/**
	 * things to do when this component is preloaded
	 */
	public function _preload_component() {
		
		$content_hook = array (
		'site_hook_initiation' => 'Utility::create_object'
		);

		return $content_hook;

	}

	/**
	 * 
	 */
	public static function _create_object() {
	}

	
	/**
	 * hide this component from being added to a recipe
	 */
	public function recipe_fields($recipe) {
		return false;
	}

}