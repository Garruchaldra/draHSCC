<?php

class MediaConsent extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Media Consent',
			'description' => 'Add a consent checkbox to the file upload form',
			'category' => 'media',
			'recipe_fields' => false
		);
	}

	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
		
		$content_hook = array (
		'media_file_uploader' => 'MediaConsent::add_consent_checkbox'
		);

		return $content_hook;

	}

	/**
	 * add consent checkbox to file upload form
	 */
	public static function add_consent_checkbox($recipe_component, $vce) {
		
		// get the parent component of this add media call
		$parent_of_media = end($vce->page->components)->type;
		
		// self::language('media_consent_text', static::class)
		
		$input = array(
		'type' => 'checkbox',
		'name' => 'consent',
		'data' => array('tag' => 'required','class' => 'ignore-input'),
		'options' => array('label' => MediaConsent::language('media_consent_text'), 'value' => 'true')
		// 'flags' => array('label_tag_wrap' => true)
		);
		
		return $vce->content->create_input($input, MediaConsent::language('Permissions'),'Affirm necessary licenses, rights, consents, and permissions.');
		
	}

}