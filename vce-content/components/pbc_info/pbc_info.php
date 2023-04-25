<?php
class Pbc_info  extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Info',
			'description' => 'Environment Variables Viewer',
			'category' => 'pbc'
		);
	}
	
	/**
	 * add a hook that fires at initiation of site hooks
	 */
	// public function preload_component() {
		// $content_hook = array (
		// 	'instructions' => 'Pbc_testing::example',
		// );
		// return $content_hook;
	// }


    /**
     *
     */
    public function as_content($each_component, $vce) {

		$vce->dump('PBC Info Component');
		$content = '<div>PBC Info Component</div>';
		$content .= '<pre>';
		$filepath = BASEPATH . 'vce-config.php';
		ob_start();
		print_r(file($filepath));
		$content .=  ob_get_clean();
		
		$content .= '</pre>';


		
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
