<?php

class Text extends MediaType {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Text (Media Type)',
			'description' => 'Adds Text to Media',
			'category' => 'media'
		);
	}    
	/**
	 * Display the text block
	 */
	public function display($each_component, $vce) {
	
		// adding repeat of input component hook for xss sanitization
		if (isset($vce->site->hooks['input_sanitize_textarea'])) {
			foreach($vce->site->hooks['input_sanitize_textarea'] as $hook) {
				$each_component->text = call_user_func($hook, $each_component->text);
			}
		}
    		
    	$vce->content->add('main','<div class="media-text-block">' . nl2br($each_component->text) . '</div>');

    }
    
	/**
	 * Add form for text block
	 */    
    public static function add($recipe_component, $vce) {
    
    	$input = array(
    	'type' => 'textarea',
    	'name' => 'text',
    	'data' => array(
    	'tag' => 'required',
    	'rows' => '10'
    	)
    	);
    	
    	$textarea_input = $vce->content->create_input($input,'Text Block Content','Enter Text Block Content');

		$accordion = <<<EOF
<form id="create_media" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$recipe_component->dossier_for_create">
<input type="hidden" name="media_type" value="Text">
<input type="hidden" name="title" value="Text Block">
$textarea_input
<input type="hidden" name="sequence" value="$recipe_component->sequence">
<input type="submit" value="Create">
</form>
EOF;

		return '<div id="text-block-container add-container">' . $vce->content->accordion('Add A Text Block', $accordion) . '</div>';
    
    }
    
    
	/**
	 * Edit form for text block
	 */    
    public static function edit($each_component, $vce) {
    
    	$input = array(
    	'type' => 'textarea',
    	'name' => 'text',
    	'value' => $each_component->text,
    	'data' => array(
    	'tag' => 'required',
    	'rows' => 10
    	)
    	);
    	
    	$textarea_input = $vce->content->create_input($input,'Text Block Content','Enter Text Block Content');

     	$input = array(
    	'type' => 'text',
    	'name' => 'sequence',
    	'value' => $each_component->sequence,
    	'data' => array(
    	'tag' => 'required'
    	)
    	);
    	
    	$select_input = $vce->content->create_input($input,'Order Number','Enter an Order Number');
   
		$content_mediatype = <<<EOF
<mamediatext>
	<div class="media-edit-container">
		<button class="no-style media-edit-open" title="edit">Edit</button>
		<div class="media-edit-form">
			<form id="update_$each_component->component_id" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
			<input type="hidden" name="dossier" value="$each_component->dossier_for_edit">
			<input type="hidden" name="title" value="$each_component->title">
			$textarea_input
			$select_input
			<input type="submit" value="Update">
			<button class="media-edit-cancel">Cancel</button>
			</form>
EOF;

		if ($each_component->can_delete($vce)) {
			$content_mediatype .= <<<EOF
			<form id="delete_$each_component->component_id" class="float-right-form delete-form asynchronous-form" method="post" action="$vce->input_path">
				<input type="hidden" name="dossier" value="$each_component->dossier_for_delete">
				<input type="submit" value="Delete">
			</form>
EOF;
		}


		$content_mediatype .= <<<EOF
		</div>
	</div>
</mamediatext>
EOF;

		return $content_mediatype;
        
    }

}