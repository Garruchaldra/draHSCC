<?php

class Pbc_adjust extends Site {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Adjust',
			'description' => 'A set of basic utilities which can be expanded as needed.',
			'category' => 'pbc'
		);
	}

	public function __construct() {
	}

		/**
	 * add a hook that fires at initiation of site hooks
	 */
	public function preload_component() {
		$content_hook = array (
		'site_hook_initiation' => 'Pbc_utilities::instantiate_self'
		);
		return $content_hook;
	}
	
	public static function instantiate_self() {

	}
	
		/**
	 * Dumps array in a pre tag with a yellow background
	 * Outputs dump of whatever object is specified to the top of the browser window. 
	 * 
	 * @param string $var
	 * @param string $color
	 * @return string of print_r(object)
	 */
	public function dump($var, $color = 'ffc') {
		if (SITE_DUMP === true) {
			echo '<pre style="background:#' . $color  . ';">Heyah!' . print_r($var, true) . '</pre>';
		}
	}	
	

	/**
	 * fileds to display when this is created
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