<?php

class Pbcbreadcrumbs extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Pbc Breadcrumbs',
			'description' => 'A custom breadcrumb trail for navigation.',
			'category' => 'pbc'
		);
	}

	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
		
		$content_hook = array (
		'build_content_loop' => 'Pbcbreadcrumbs::add_breadcrumbs'
		);

		return $content_hook;

	}

	/**
	 * add a user attribute
	 */
	public static function add_breadcrumbs($each_component, $linked) {
	
		global $site;
		global $content;

		if (isset($each_component->url) && !empty($each_component->url) && $linked === false) {
			if ($each_component->url == "/") {
// 				$content->breadcrumb .= '<a href="' . $site->site_url . '" class="breadcrumb-item breadcrumb-item-home"></a>';
			} else {
				if (!isset($content->breadcrumb)) {
					$content->breadcrumb .= '<a href="' . $site->site_url . '" class="breadcrumb-item breadcrumb-item-home"></a>';
				}
				$content->breadcrumb .= '<a href="' . $site->site_url . '/' . $each_component->url . '" class="breadcrumb-item">';
				if (isset($each_component->title)) {
					$content->breadcrumb .= $each_component->title;
				} else {
					$content->breadcrumb .= $each_component->url;
				}
				$content->breadcrumb .= '</a>';
			}
		}
		
	}

	
	/**
	 * hide this component from being added to a recipe
	 */
	public function recipe_fields($recipe) {
		return false;
	}

}