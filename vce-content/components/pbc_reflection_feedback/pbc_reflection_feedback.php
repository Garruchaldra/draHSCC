<?php

class Pbc_reflection_feedback extends Component {

	/**
	 * basic info about the component
	 * this component was based on the "Reflection_assignment" component
	 */
	public function component_info() {
		return array(
			'name' => 'Reflection and Feedback',
			'description' => 'Reflection and Feedback on Action Plan Steps',
			'category' => 'pbc'
		);
	}
	
	
	/**
	 * 
	 */
	public function preload_component() {
		
		$content_hook = array (
		'resource_library_view' => 'Pbc_reflection_feedback::resource_library_view'
		);

		return $content_hook;

	}
	
		
	/**
	 * 
	 */
	public static function resource_library_view($each_component, $page) {
		if (isset($page->rf_resource_requester_id)) {
			global $db;
			$rf_resource_requester_id = $page->rf_resource_requester_id;
			$query = "SELECT meta_key, meta_value, minutia FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $page->rf_resource_requester_id . "' ORDER BY meta_key";
			$components_meta = $db->get_data_object($query);
	// 		global $site;
	// 		$site->dump($components_meta);
			foreach ($components_meta as $each_meta) {
				// add title from requested id to page object base
				if ($each_meta->meta_key == "title") {
					$resource_requester_title = $each_meta->meta_value;
				}
			}
		
		

			$test = $page->rf_resource_requester_id;
		
	// 		$test = $each_component->component_id;

	
			$dossier = array(
			'type' => 'Pbc_reflection_feedback',
			'procedure' => 'create_alias',
			'parent_id' => $page->rf_resource_requester_id,
			'alias_id' => $each_component->component_id,
			'created_by' => $page->user->user_id,
			);
		
		
			// generate dossier
			$dossier_copy_resource = $page->generate_dossier($dossier);

$content = <<<EOF
<br><br>
<form class="asynchronous-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_copy_resource">
<input type="submit" value="Associate This Resource &quot;$each_component->title&quot; to: $resource_requester_title">
</form>
EOF;

			$page->content->add('postmain',$content);
		}
	}

	/**
	 * adding assignment specific mata to page object
	 */
	public function check_access($each_component, $page) {

		// adding assignment specific mata to page object
		$page->assignment_id = $each_component->component_id;
		$page->assignment_type = $each_component->type;
	
		return true;
	}

	/**
	 * last component was the requested id, so generate links for this component
	 * by default this is a simple html link
	 */
	public function as_link($each_component, $page) {
		return false;
	}


	/**
	 *
	 */
	public function as_content($each_component, $page) {
	
		global $site;
		
		$site->remove_attributes('rf_resource_requester_id');

		
		// add javascript to page
		$page->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');

		// add stylesheet to page
		$page->site->add_style(dirname(__FILE__) . '/css/style.css','assignment-style');
		
global $db;
$component_id = $each_component->fo_id;
// get children of current_id
$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components WHERE component_id ="' . $component_id . '"' ;
$component = $db->get_data_object($query);

 $requested_components = new stdClass();

 $requested_components->component_id = $component[0]->component_id;
 $requested_components->url = $page->site->site_url . '/' . $component[0]->url;


// get level one components meta data
$query = "SELECT meta_key, meta_value, minutia FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $component_id . "' ORDER BY meta_key";
$components_meta = $db->get_data_object($query);

// cycle through results and add meta_key / meta_value pairs to component object
foreach ($components_meta as $each_meta) {

        $key = $each_meta->meta_key;

        $requested_components->$key  = $db->clean($each_meta->meta_value);

        // adding minutia if it exists within database table
        if (!empty($each_meta->minutia)) {
                $key .= "_minutia";
                $requested_components[$each_key]->$key = $each_meta->minutia;
        }

}
if (strlen($requested_components->focus) > 31) {
	$fo_name = substr($requested_components->focus, 0, 32) . '...';
} else {
	$fo_name = $requested_components->focus;
}



$component_id = $each_component->ap_id;
// get children of current_id
$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components WHERE component_id ="' . $component_id . '"' ;
$component = $db->get_data_object($query);

 $requested_components = new stdClass();

 $requested_components->component_id = $component[0]->component_id;
 $requested_components->url = $page->site->site_url . '/' . $component[0]->url;


// get level one components meta data
$query = "SELECT meta_key, meta_value, minutia FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $component_id . "' ORDER BY meta_key";
$components_meta = $db->get_data_object($query);

// cycle through results and add meta_key / meta_value pairs to component object
foreach ($components_meta as $each_meta) {

        $key = $each_meta->meta_key;

        $requested_components->$key  = $db->clean($each_meta->meta_value);

        // adding minutia if it exists within database table
        if (!empty($each_meta->minutia)) {
                $key .= "_minutia";
                $requested_components[$each_key]->$key = $each_meta->minutia;
        }

}
if (strlen($requested_components->title) > 31) {
	$ap_name = substr($requested_components->title, 0, 32) . '...';
} else {
	$ap_name = $requested_components->title;
}

//find and display the video which was uploaded to the corresponding action plan step

$siblings = $page->get_children($each_component->parent_id);

$step_media = array();
foreach($siblings as $component){
	if (isset($component) && isset($component->components)) {
		if($component->type == 'Pbc_step') {
			foreach($component->components as $subcomponent){
				if($subcomponent->type == 'Media') {
					$step_media[] = $subcomponent;
				}
			}
		}
	}

}








if(count($step_media) > 0) {
	$vid_caption = 'Video from Action Plan Step: &quot; '.$ap_name.' &quot;';
} else {
	$vid_caption = <<<EOF

<label class="selectable warning">
<div class="data-display warning">
No video has been uploaded for the related Action Plan Step: &quot; $ap_name &quot;
</div>
</label>
EOF;
}

$content = <<<EOF
<div class="data-display">$each_component->title for &quot; $fo_name &quot;</div>

<div class="data-display">
$vid_caption
</div>

EOF;

		global $site;
		$site->remove_attributes('as_resource_requester_id');
//clickbar to add from resource library
		// the instructions to pass through the form with specifics
		$dossier = array(
		'type' => 'Pbc_step',
		'procedure' => 'add_as_resource_requester_id',
		'url_of_resource_library' => $site->site_url . '/resource_library',
		'component_id' => $each_component->component_id
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_for_add_resource_requester_id = $page->generate_dossier($dossier);



$content .= <<<EOF
<div class="clickbar-container admin-container add-container ignore-admin-toggle">
<div class="clickbar-content">
<div class="link-button  add-requester" dossier="$dossier_for_add_resource_requester_id" action="$page->input_path">Add a resource from the Resource Library</div>
</div>
<div class="clickbar-title clickbar-closed"><span>Associate Media From Resource Library</span></div>
</div>

EOF;


//clickbar to add from personal media library
		// the instructions to pass through the form with specifics
		$dossier = array(
		'type' => 'Pbc_step',
		'procedure' => 'add_as_resource_requester_id',
		'url_of_resource_library' => $site->site_url . '/usermedia',
		'component_id' => $each_component->component_id
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_for_add_resource_requester_id = $page->generate_dossier($dossier);



$content .= <<<EOF
<div class="clickbar-container admin-container add-container ignore-admin-toggle">
<div class="clickbar-content">
<div class="link-button  add-requester" dossier="$dossier_for_add_resource_requester_id" action="$page->input_path">Add one of your previously uploaded resources.</div>
</div>
<div class="clickbar-title clickbar-closed"><span>Associate Media From Your User Media Library</span></div>
</div>

EOF;



		// page content
		$page->content->add('main', $content);
		
		$page->display_components($step_media);
		
		$sub_roles = json_decode($page->sub_roles, true);
		
		$user_access = json_decode($page->user_access, true);
		


	}

	/**
	 * close div for tab
	 */
	public function as_content_finish($each_component, $page) {
	
	
// 		$page->content->add('postmain',$content);
	
	}

	
	/**
	 *
	 */
	public function edit_component($each_component, $page) {
		return false;
		if ($page->can_edit($each_component) || $page->user->sub_role == 1) {
		
			// prevent assignments from being edited
			if (isset($page->lock_assignments) && $page->user->role_id != 1) {
				return false;
			}
		
			// the instructions to pass through the form
			$dossier = array(
			'type' => $each_component->type,
			'procedure' => 'update',
			'component_id' => $each_component->component_id,
			'created_at' => $each_component->created_at
			);

			// generate dossier
			$dossier_for_update = $page->generate_dossier($dossier);
		
			// get course component info
			$course = $page->components[(count($page->components) - 1)];
		
			$recipe = isset($each_component->recipe) ? (object) $each_component->recipe : null;
		
			// add javascript to page
			$page->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		
			// this is the current template, but the recipe will control this.
			$template = isset($page->template) ? $page->template : null;
			
			// for date picker
			$id_key = uniqid();
			
			// required if not admin
			$required = ($page->user->role_id == 1) ? '' : 'tag="required"';

$content = <<<EOF
<div class="clickbar-container admin-container edit-container">
<div class="clickbar-content">
<form id="update_$each_component->component_id" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
EOF;

		if ($template) {
			$content .= '<input type="hidden" name="template" value="' . $template . '">';
		}

$content .= <<<EOF
<div class="data-display">
<div>$each_component->title</div>
</div>

EOF;

		if (isset($recipe_component->template)) {
			$content .= '<input type="hidden" name="template" value="' . $recipe_component->template . '">';
		}
		


			foreach ($page->components as $each_page_component) {
				if ($each_page_component->type == 'Pbcsteps') {
						$Pbcsteps_id = $each_page_component->component_id;
				}
			}
			
global $db;
$component_id = $Pbcsteps_id;
// get children of current_id
$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components WHERE parent_id ="' . $component_id . '"' ;
$components = $db->get_data_object($query);
// 			global $site;
// 			$site->dump($component);


$requested_components = array();
$i = 1;
foreach ($components as $sibling) {
	$component_id = $sibling->component_id;
	// get children of current_id
	$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components WHERE component_id ="' . $component_id . '"' ;
	$component = $db->get_data_object($query);
	$requested_components[$i] = array();
	 $requested_components[$i]["component_id"] = $component[0]->component_id;
	 $requested_components[$i]["url"] = $page->site->site_url . '/' . $component[0]->url;


	// get level one components meta data
	$query = "SELECT meta_key, meta_value, minutia FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $component_id . "' ORDER BY meta_key";
	$components_meta = $db->get_data_object($query);

	// cycle through results and add meta_key / meta_value pairs to component object
	foreach ($components_meta as $each_meta) {

			$key = $each_meta->meta_key;

			$requested_components[$i]["$key"]  = $db->clean($each_meta->meta_value);

			// adding minutia if it exists within database table
			if (!empty($each_meta->minutia)) {
					$key .= "_minutia";
					$requested_components[$i][$each_key]->$key = $each_meta->minutia;
			}

	}

	$i++;
}

			

			//build arrays for collections of components
			$fo_feedback_content = '';

			foreach ($requested_components as $each_pbcstep) {
			// create pbcstep url
// 				$pbcstep_url = $page->site->site_url . '/' . $each_pbcstep->url;

				if ($each_pbcstep['list_order'] == 2) {
				$selected_status = '';
				if ($each_component->fo_id == $each_pbcstep['component_id']) {
					$selected_status = 'selected="selected"';
				}
				$title = $each_pbcstep['title'];
				$component_id = $each_pbcstep['component_id'];
				$focus = $each_pbcstep['focus'];
				$date = $each_pbcstep['date'];
				
$fo_feedback_content .= <<<EOF
<option value="$component_id" $selected_status >$title : $focus, $date</option>
EOF;
				}
			}
		

$content .= <<<EOF
<label class="selectable">
<select name="fo_id" tag="required">
<option value="">Please choose a focused observation to give feedback.</option>
$fo_feedback_content
</select>
<div class="label-text">
<div class="label-message">Which Focused Observation are you commenting on?

<div class="input-tooltip-icon">
<div class="general-tooltip" style="display: none;">
<span class="tooltip-text">These are the focused observations which belong to this cycle.</span>
<div class="arrow-down"></div>
</div>
</div>

</div>
<div class="label-error">Enter which Focused Observation you are commenting on</div>
</div>
</label>


<input type="submit" value="Update">
</form>
EOF;

			if ($page->can_delete($each_component) || $page->user->sub_role == 1) {


				// the instructions to pass through the form
				$dossier = array(
				'type' => $each_component->type,
				'procedure' => 'delete',
				'component_id' => $each_component->component_id,
				'created_at' => $each_component->created_at,
				'parent_url' => $page->requested_url
				);

				// generate dossier
				$dossier_for_delete = $page->generate_dossier($dossier);
			
$content .= <<<EOF
<form id="delete_$each_component->component_id" class="delete-form float-right-form asynchronous-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<input type="submit" value="Delete">
</form>
EOF;

			}

$content .= <<<EOF
</div>
<div class="clickbar-title clickbar-closed"><span>Edit Reflection and Feedback</span></div>
</div>
EOF;

			$page->content->add('admin',$content);

		}

	
	}


	/**
	 *
	 */
	public function add_component($recipe_component, $page) {
	//this is disabled by returning false; the reflection and feedback step is created automatically when a focused observation
	//is created, and both are linked to a single action plan step.
		return false;
		global $user;
		
		// create dossier
		$dossier_for_create = $page->generate_dossier($recipe_component->dossier);
		
		// create dossier for checkurl functionality
		$dossier = array(
		'type' => $recipe_component->type,
		'procedure' => 'checkurl'
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_for_checkurl = $page->generate_dossier($dossier);

		// get course component info
		$course = $page->components[(count($page->components) - 1)];
		
		// add javascript to page
		$page->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		
		// for date picker
		$id_key = uniqid();

		// required if not admin
		$required = ($page->user->role_id == 1) ? '' : 'tag="required"';

$content = <<<EOF
<div class="clickbar-container admin-container add-container ignore-admin-toggle">
<div class="clickbar-content">
<form id="create_items" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_create">
<input type="hidden" name="assignment_category" value="$recipe_component->title">
<input id="create-title" type="hidden" name="title" value="$recipe_component->title">
<input type="hidden" name="list_order" value="3">
<input type="hidden" name="fo_user_id" value="$user->user_id">
EOF;

		if (isset($recipe_component->description)) {
		
			$description = nl2br($recipe_component->description);

$content .= <<<EOF
<label>
<div class="input-padding">$description</div>
<div class="label-text">
<div class="label-message">$recipe_component->title</div>
</div>
</label>
EOF;

		}

$content .= <<<EOF
<div class="link-button disabled clickbar-top-close" style="visibility: visible; float: right;" >X</div>
<div class="data-display">
<div>$recipe_component->title</div>
</div>

<input class="check-url" type="hidden" name="url" value="" parent_url="$course->url/" dossier="$dossier_for_checkurl" tag="required" autocomplete="off">
EOF;

		if (isset($recipe_component->template)) {
			$content .= '<input type="hidden" name="template" value="' . $recipe_component->template . '">';
		}
		
		$fo_feedback_content = '';
		if (isset($page->components[(count($page->components)-1)]->components[0]->components)) {
			$pbcsteps = $page->components[(count($page->components)-1)]->components[0]->components;
		
			//build arrays for collections of components
			
			$pbcsteps1 = $pbcsteps;

			foreach ($pbcsteps as $each_pbcstep) {
			// create pbcstep url
				$pbcstep_url = $page->site->site_url . '/' . $each_pbcstep->url;

				if ($each_pbcstep->list_order == 2) {
$fo_feedback_content .= <<<EOF
<option value="$each_pbcstep->component_id">$each_pbcstep->title : $each_pbcstep->focus, $each_pbcstep->date</option>
EOF;
				}
			}
		}	
			

$content .= <<<EOF
<label class="selectable">
<select name="fo_id" tag="required">
<option value="">Please choose a focused observation to give feedback.</option>
$fo_feedback_content
</select>
<div class="label-text">
<div class="label-message">Which Focused Observation are you commenting on?

<div class="input-tooltip-icon">
<div class="general-tooltip" style="display: none;">
<span class="tooltip-text">These are the focused observations which belong to this cycle.</span>
<div class="arrow-down"></div>
</div>
</div>

</div>
<div class="label-error">Enter which Focused Observation you are commenting on</div>
</div>
</label>

<label class="selectable">
<div class="data-display">
Create this Reflection and Feedback step by choosing a Focused Observation and clicking &#34;Create&#34;.<br>
Then you will be able to give feedback to these questions:<br><br>
What went well in this observation?<br>
What didn't go well in this observation?<br>
What would you do differently?<br><br>
</div>
<div class="label-text">
<div class="label-message">Action Plan Goal</div>
</div>
</label>

<input type="submit" value="Create"> <div class="link-button clickbar-group-close">Cancel</div>
</form>
</div>
<div class="clickbar-title clickbar-closed clickbar-group"><span>Add a $recipe_component->title Response</span></div>
</div>
EOF;

		$page->content->add('create_pbcstep',$content);

	}
	
	
	
	
	/**
	 * procedure that is called via javascript by clicking on the "Add Resource" button within Focused Observations
	 */
	public function add_rf_resource_requester_id($input) {

			global $site;
// 			$site->log($input);
			// set a value and forward
			$site->add_attributes('rf_resource_requester_id', $input['component_id'], true);


			if (!empty($input['component_id'])) {
					$url = $input['url_of_resource_library'];

					echo json_encode(array('response' => 'success','url' => $url));
					return;
			}

			echo json_encode(array('response' => 'error','procedure' => 'add','message' => "Error"));
			return;

	}
	
	
	
	
	/**
	 * Creates component
	 * this is customized from the parent method to create datalists
	 * @param array $input
	 * @return calls component's procedure or echos an error message
	 */
	public function create($input) {
// global $site;
// $site->log($input);
		// clean up url
		if (isset($input['url'])) {
			global $site;
			$input['url'] = $site->url_checker($input['url']);
		}
	
		// call to create_component, which returns the newly created component_id
		$component_id = $this->create_component($input);
		
		
	
	
		if ($component_id) {
		
			global $site;
			$site->add_attributes('message',self::component_info()['name'] .$component_id. ' Created');
			
			
			//redirect to as_content after creation
			global $db;

			$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components WHERE component_id ="' . $component_id . '"' ;
			$component = $db->get_data_object($query);
			
			$requested_url = $site->site_url . '/' . $component[0]->url;

			echo json_encode(array('response' => 'success','procedure' => 'create','action' => 'reload','url' => $requested_url, 'message' => 'Created','component_id' => $component_id));
			return;
		
		}
		
		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Error"));
		return;

	}


	public function create_alias($input) {
	
		global $db;
		global $site;
	
		$input['type'] = 'Alias';
	
		// call to create_component, which returns the newly created component_id
		$component_id = $this->create_component($input);
	
		if ($component_id) {
		
			// find the component_id for component type of FocusedObservations 
			$query = "SELECT * FROM " . TABLE_PREFIX . "components WHERE component_id IN (SELECT parent_id FROM " . TABLE_PREFIX . "components WHERE component_id='" . $component_id . "')";
			$parent_component = $db->get_data_object($query);
	
			$url = $site->site_url . '/' . current($parent_component)->url;
		
			$site->add_attributes('message','Resource Associated with Reflection and Feedback');
			
			$site->add_attributes('observation_id',$component_id);
			
	
			echo json_encode(array('response' => 'success','procedure' => 'create','action' => 'reload','url' => $url,'message' => 'Created','component_id' => $component_id));
			return;
		
		}
		
		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Error"));
		return;

	
	}


	
	/**
	 * fields for ManageRecipes
	 */
	public function recipe_fields($recipe) {
	
		global $site;
		
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
		$description = isset($recipe['description']) ? $recipe['description'] : null;
				
$elements = <<<EOF
<label>
<input type="text" name="title" value="$title" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Title</div>
<div class="label-error">Enter a Title</div>
</div>
</label>
<label>
<textarea name="description">$description</textarea>
<div class="label-text">
<div class="label-message">Description</div>
<div class="label-error">Enter a Description</div>
</div>
</label>
EOF;

		return $elements;
		
	}

}