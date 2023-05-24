<?php

class Pbcsteps extends Component {

	/**
	 * basic info about the component
	 * this component is based on "Assignments" and retains much of the same naming and functionality
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Cycle Steps',
			'description' => 'A container for steps within PBC Cycles',
			'category' => 'pbc',
			'recipe_fields' => array('title','template','role_access','content_access','content_create','content_edit','content_delete')

		);
	}


	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
		
		$content_hook = array (
		'get_sub_components' => 'Pbcsteps::sort_pbcsteps'
		);

		return $content_hook;

	}


	/**
	 * sort pbcsteps by pre-defined order
	 */
	public static function sort_pbcsteps($requested_components,$sub_components,$vce) {

		if (isset($sub_components)) {
	
			foreach ($sub_components as $component_key=>$component_info) {
		
				if ($component_info->type == "Pbcsteps") {
			
					foreach ($requested_components as $requested_key=>$requested_info) {
						if ($requested_info->parent_id == $component_info->component_id) {
						
							$meta_key = 'list_order';
			
							usort($requested_components, function($a, $b) use ($meta_key) {
								if (isset($a->$meta_key) && isset($b->$meta_key)) {
									return $a->$meta_key > $b->$meta_key ? 1 : -1;
								} else {
									return 1;
								}
							});
							
							return $requested_components;
							
						}

					}
			
				}
		
			}
		
		}
	
		return $requested_components;
		
	}


	/**
	 * 
	 */
	public function as_link($each_component, $vce) {
		return false;
	}
	
	
	/**
	 *
	 */
	public function as_content($each_component, $vce) {
		// return false;
		// $vce->dump('pbcsteps as_content');
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/tinymce/tinymce.min.js', 'jquery');
	
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter');
		
		$vce->site->add_script(dirname(__FILE__) . '/js/jquery.magnific-popup.js', 'tablesorter');	
		$vce->site->add_script(dirname(__FILE__) . '/js/jquery.magnific-popup.min.js', 'tablesorter');	
		$vce->site->add_style(dirname(__FILE__) . '/js/magnific-popup.css', 'tablesorter');	

		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','pbcstep-style');

		
		if (isset($vce->page->query_string)) {
			$query_string = json_decode($vce->page->query_string);
			if (isset($query_string->ap_step_id)){
				// $vce->site->add_attributes('ap_step_id', $query_string->ap_step_id);
				$vce->ap_step_id = $query_string->ap_step_id;
			}
		}
       	// Set storage to capture the subcomponents of Pbcsteps.
		$current_storage_key = $vce->content->set_storage("subcomponents_of_pbcsteps");
		// save the current key
		$vce->site->add_attributes('default_storage_key', $current_storage_key);
		// $vce->dump($each_component);
	}
	

	/**
	 *
	 */
	public function as_content_finish($each_component, $vce) {
		// $vce->plog('start', 0);
// $vce->plog($vce->page, 1);
// return false;
		$content = NULL;
		$site_url = $vce->site->site_url;
		$pbcsteps_url = $vce->page->find_url($each_component->component_id);
		foreach ($vce->page->components as $component) {
				if ($component->url == $vce->site->requested_url) {
						$sidebar_view = $component->type;
				}
		}
		// if there is a query string for the page view, use it and convert it to an object
		if (isset($vce->page->query_string) && !empty(json_decode($vce->page->query_string))) {
			$query_string = json_decode($vce->page->query_string);
		}
		

		// add information which we can glean from the component meta-data, and redirect to fo if that is what the url is for
		$query = "SELECT a.component_id, b.meta_value AS 'type', c.meta_value AS 'ap_step_id' FROM  " . TABLE_PREFIX . "components AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND b.meta_key = 'type'   JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'ap_step_id'  WHERE a.url='" . $vce->site->requested_url . "'";
		$result = $vce->db->get_data_object($query);
		foreach ($result as $this_result) {
				$type = $this_result->type;
				$ap_step_id = $this_result->ap_step_id;
		}
		// $vce->dump($type);
		// $vce->dump($ap_step_id);
		if (!isset($query_string->ap_step_id) && isset($type) && isset($ap_step_id) && $type == 'Pbc_focused_observation') {
			$redirect_to = $site_url . '/' . $vce->site->requested_url . '?pbcsteps_view=fo&ap_step_id=' . $ap_step_id;
			header('location: ' . $redirect_to);
		}

		// Output subcomponent content to a variable
		// $vce->dump('subcomponents_of_pbcsteps');
		$subcomponents_of_pbcsteps = $vce->content->output(array('admin', 'premain', 'main', 'postmain'), true);
		// $vce->dump(substr_count($subcomponents_of_pbcsteps, 'safd_01'));
		$create_goal = $vce->content->output(array('create_goal'), true);
		$create_pbcstep = $vce->content->output(array('create_pbcstep'), true);
		$edit_pbcstep = $vce->content->output(array('edit_pbcstep'), true);
		$create_fo = $vce->content->output(array('create_fo'), true);
		$edit_fo = $vce->content->output(array('edit_fo'), true);
		$edit_goal = $vce->content->output(array('edit_goal'), true);
		// reset the storage to default
		$vce->content->current_storage_key = $vce->default_storage_key;

		$edit_cycle = $vce->content->output(array('edit_cycle'), true);

		//set href for editing this cycle

		

		// condition for edit cycle page
		if (isset($query_string->action) && $query_string->action == 'edit_cycle'){
			$tab1_content = '<a href="' . $site_url . '"><div class="back-icon"></div>Return to list of all cycles</a>';
			$tab1_visibility = false;
			$tab3_visibility = true;
			$tab3_id = 'edit-cycle';
			$tab3_label = 'Edit Cycle';
			$tab3_content = $edit_cycle;


		// create tabs
		$tabs_input = array (
			'tabs__container1' => array(
				'tabs' => array(),
			),
		);

		if (isset($tab1_content)) {
			$tabs_input['tabs__container1']['tabs']['tab1'] = array(
				'id' => 'tab1',
				'label' => 'View Cycles',
				'content' => $tab1_content,
				'visibility' => $tab1_visibility
			);
		}

		if (isset($tab3_content)) {
			$tabs_input['tabs__container1']['tabs']['tab2'] = array(
				'id' => 'tab2',
				'label' => 'Edit Cycle',
				'content' => $tab3_content,
				'visibility' => $tab3_visibility
			);
		}
		$edit_cycle_tabs_input = $tabs_input;
	}

	// condition for edit goal page
		if (isset($query_string->action) && $query_string->action == 'edit_goal'){
			$tab1_content = null;
			$tab1_visibility = false;
			$tab3_visibility = true;
			$tab3_id = 'edit-goal';
			$tab3_label = 'Edit Goal';
			$tab3_content = $edit_goal;


		// create tabs
		$tabs_input = array (
			'tabs__container1' => array(
				'tabs' => array(),
			),
		);

		if (isset($tab1_content)) {
			$tabs_input['tabs__container1']['tabs']['tab1'] = array(
				'id' => 'tab1',
				'label' => 'View Cycles',
				'content' => $tab1_content,
				'visibility' => $tab1_visibility
			);
		}

		if (isset($tab3_content)) {
			$tabs_input['tabs__container1']['tabs']['tab2'] = array(
				'id' => 'tab2',
				'label' => 'Edit Goal',
				'content' => $tab3_content,
				'visibility' => $tab3_visibility
			);
		}

		$edit_goal_tabs_input = $tabs_input;
		// $content = Pbc_utilities::create_tab($tabs_input);

		}
	




		// find requested url 
		$requested_url = $vce->site->requested_url;

		// $vce->dump($vce->page->components);

		//this gets the subcomponents of the pbcsteps page for building the list.
		// it is based on the Pbcsteps component id, so it stays the same regardless of the requested url
		$child_id = $vce->page->get_children($each_component->component_id);
		$pbc_steps_subcomponents = isset($child_id) ? $vce->page->get_children($each_component->component_id) : NULL;

		// set type of requested url for use with sidebar
		$goal_exists = false;
		foreach ($pbc_steps_subcomponents as $component) {
			// $vce->dump($component->type);
			if ($component->url == $requested_url) {
				$sidebar_view = $component->type;
			}
			// check if goal has been created
			if ($component->type == 'Pbc_goal') {
				$goal_exists = true;
				$goal_url = $site_url . '/' . $component->url . '?action=edit_goal';
			}
		}

		if (!isset($vce->pbcsteps_view)) {
			// create default variable for which info to view when pbcsteps loads
			$vce->pbcsteps_view = 'ap_step';
			// $vce->site->add_attributes('pbcsteps_view', 'ap_step');
		}

		// if there is a query string for the page view, use it
		if (isset($query_string)) {
			if (isset($query_string->pbcsteps_view)){
				// $vce->site->add_attributes('pbcsteps_view', $query_string->pbcsteps_view);
				$vce->pbcsteps_view = $query_string->pbcsteps_view;
			}
			if (isset($query_string->ap_step_id)){
				// $vce->site->add_attributes('ap_step_id', $query_string->ap_step_id);
				$vce->ap_step_id = $query_string->ap_step_id;
			}
			if (isset($query_string->action)){
				// $vce->site->add_attributes('action', $query_string->action);
				
				$vce->action = $query_string->action;
			}
		}

		if ($goal_exists === false) {
			// $vce->site->add_attributes('pbcsteps_view', 'goal');
			$vce->pbcsteps_view = 'goal';
		} elseif ($goal_exists === true && $vce->pbcsteps_view == 'goal') {
			$vce->pbcsteps_view = 'ap_step';
		}

		if (isset($edit_goal_tabs_input)) {
			$vce->pbcsteps_view = 'goal';
		}
		if (isset($edit_cycle_tabs_input)) {
			$vce->pbcsteps_view = 'cycle';
		}
		// $vce->dump($vce->page);


		$pbc_step_components = array();
		if (count($pbc_steps_subcomponents) > 0) {
			foreach($pbc_steps_subcomponents as $subcomponent) {
				// count subcomponents of type 'Pbc_step'
				if ($subcomponent->type == 'Pbc_step') {
					$pbc_step_components[] = $subcomponent->component_id;
				}
			}
		}
		if (count($pbc_step_components) < 1) {

		}

		// Set titleBar, sidebar, and progressArrows info for hooks below
		if ($vce->pbcsteps_view == 'goal') {
			$title = 'Action Plan Goal';
			$icon = 'goal';
			$boxCount = 1;

			$sidebarContent = array(
				'instructionsTitle' => '&#x25C0; NEXT',
				'instructionsText' => 'Cycle participants assess needs and work together to set a goal for this coaching cycle. You will be able to update this goal throughout the cycle.',
				'infoboxTitle' => 'Shared Goals & Action Planning',
				'infoboxIcon' => 'goal',
				'infoboxText' => 'Is your goal Specific, Measurable, Achievable, Realistic and Time bound? Write your goal so that it speaks directly to improving a specific practice. Articulate the Who, What, and When that you will you be able to identify when improvement has been made.'
			);
			
			$pageLinks = array(
				'homeLink' => 'href="' . $site_url . '" class="progress-arrow-text__active-link progress-arrows progress-arrow-text progress-arrows__one""',
				'goalLink' => 'class="progress-arrows__active progress-arrows progress-arrow-text progress-arrows__two"',
				'stepsLink' => 'class="progress-arrows progress-arrow-text progress-arrows__three"',
				'foLink' => 'class="progress-arrows progress-arrow-text progress-arrows__four"'
			);
		}

		// Set titleBar, sidebar, and progressArrows info for hooks below
		if ($vce->pbcsteps_view == 'cycle') {
			$title = 'Practice-Based Coaching (PBC) Cycles';
			$icon = 'cycles';
			$boxCount = 1;

			$sidebarContent = array(
				'instructionsTitle' => '&#x25C0; FIRST',
				'instructionsText' => 'Select a PBC title or add a new PBC Cycle to get started.',
			);
			
			$pageLinks = array(
				'homeLink' => 'class="progress-arrows progress-arrows__active progress-arrow-text progress-arrows__one"',
				'goalLink' => 'class="progress-arrows progress-arrow-text progress-arrows__two"',
				'stepsLink' => 'class="progress-arrows progress-arrow-text progress-arrows__three"',
				'foLink' => 'class="progress-arrows progress-arrow-text progress-arrows__four"'
			);
		}

	// if subcomponents exist, loop through them and generate content
	if (count($pbc_steps_subcomponents) > 0) {
		$pbcsteps = $pbc_steps_subcomponents;

		$content = "";
		$pbcsteps1 = $pbcsteps;
		$vce->goal_created = false;

		// create the sidebar box views
		if ($vce->pbcsteps_view == 'ap_step') {
			$title = 'Action Plan Steps';
			$icon = 'steps';
			$boxCount = 2;
			if($sidebar_view === 'Pbc_step') {
				$sidebarContent = array(
					'instructionsTitle' => '',
					'instructionsText' => '&#x25C0; These are the Action Plan Steps associated with the selected PBC cycle and goal.',
					'infoboxTitle' => 'Shared Goals & Action Planning',
					'infoboxIcon' => 'goal',
					'infoboxText' => 'Shared Resources should support your Action Plan Step. These are concrete resources and activities that support the improvement of practices. Coach and coachee should use Shared Resources to support the “Know and See” components. Use the comment features to respond to the resources and reinforce the knowledge.'
				);
			} else {
				$sidebarContent = array(
					'instructionsTitle' => '',
					'instructionsText' => '&#x25C0; These are the Action Plan Steps associated with the selected PBC cycle.',
					'infoboxTitle' => 'Shared Goals & Action Planning',
					'infoboxIcon' => 'goal',
					'infoboxText' => 'What are the smaller steps that support the overall goal? Breaking down a goal into smaller steps will help embed improved practices into every day activities. Action Plan Steps incorporate the Know, See and Do components of the coaching cycle.'
				);
			}
			$pageLinks = array(
				'homeLink' => 'href="' . $site_url . '" class="progress-arrow-text__active-link progress-arrows progress-arrow-text progress-arrows__one"',
				'goalLink' => 'href="' . $goal_url . '" class="progress-arrow-text__active-link progress-arrows progress-arrow-text progress-arrows__two"',
				'stepsLink' => 'class="progress-arrows__active progress-arrows progress-arrow-text progress-arrows__three"',
				'foLink' => 'class="progress-arrows progress-arrow-text progress-arrows__four"'
			);
		} elseif ($vce->pbcsteps_view == 'fo') {
			$title = 'Focused Observations';
			$icon = 'fo';
			$boxCount = 3;
			$instructionsTitle = 'Title';
			$instructionsText = 'instructions';
			$goal_link = '';

			if ($sidebar_view === 'Pbc_focused_observation') {
				$sidebarContent = array(
					'instructionsTitle' => '',
					'instructionsText' => '&#x25C0; These are the Focused Observations for the selected Action Plan Step.',
					'infoboxTitle' => 'Reflection and Feedback',
					'infoboxIcon' => 'reflection',
					'infoboxText' => 'Reflection and Feedback supports the implementation of new practices by encouraging the coachee to reflect on their progress towards accomplishing their goal and by the coach providing both supportive and constructive feedback.'
				);
			} else {
				$sidebarContent = array(
					'instructionsTitle' => '',
					'instructionsText' => '&#x25C0; These are the Focused Observations for the selected Action Plan Step.',
					'infoboxTitle' => 'Focused Observation',
					'infoboxIcon' => 'fo',
					'infoboxText' => 'Focused Observation should support the demonstration of mastery of the goal. This documentation (picture, video, or other uploaded documentation) is the evidence around high quality practice. The Focused Observation supports the “Do” component.'
				);
			}
			$pageLinks = array(
				'homeLink' => 'href="' . $site_url . '" class="progress-arrow-text__active-link progress-arrows progress-arrow-text progress-arrows__one"',
				'goalLink' => 'href="' . $goal_url . '" class="progress-arrow-text__active-link progress-arrows progress-arrow-text progress-arrows__two"',
				'stepsLink' => 'href="' . $pbcsteps_url . '?pbcsteps_view=ap_step' . '" class="progress-arrow-text__active-link progress-arrows progress-arrow-text progress-arrows__three"',
				'foLink' => 'class="progress-arrows__active progress-arrows progress-arrow-text progress-arrows__four"'
			);
		}

		$fos_per_ap_step = array();
		foreach ($pbcsteps1 as $k => $each_pbcstep1) {
			if ($each_pbcstep1->type == 'Pbc_focused_observation' && isset($each_pbcstep1->ap_step_id)) {
				$fos_per_ap_step[$each_pbcstep1->ap_step_id][] = array('title'=>$each_pbcstep1->title, 'url'=>$each_pbcstep1->url);
			}
		}



		$goal_content = '';
		$tab1_content = '';
		$related_ap_step_title = '';
		$ap_steps_array = array();
		$fo_array = array();
		// $vce->dump($pbcsteps1);
	/*** loop through all subcomponents which will be shown on the page    ***/
		foreach ($pbcsteps1 as $k => $each_pbcstep1) {

			if (isset($each_pbcstep1->components)) {
				$number_of_subcomponents = count($each_pbcstep1->components);
			} else {
				$number_of_subcomponents = '0';
			}
			// $vce->dump($each_pbcstep1->title);
			// $vce->dump($each_pbcstep1);

			// create pbcstep url
			$pbcstep_url = $site_url . '/' . $each_pbcstep1->url;

			// create pbcstep url
			$pbcstep_link = $pbcstep_url;

			// set content divs to display none unless changed below
			$div_visibility = '';
			$embedded_display = '';
				
			
			if ($each_pbcstep1->type == 'Pbc_goal') {
				$vce->goal_created = true;
				$goal = $each_pbcstep1->action_plan_goal;
				$goal_achievement = $each_pbcstep1->goal_achievement_evidence;
				$goal_edit_href = $site_url . '/' . $each_pbcstep1->url . '?action=edit_goal';

			// $vce->dump($each_pbcstep1);

			$goal_content .= <<<EOF
<div class="progress-info-box__text">$goal</div>
<div><span class="pbc-cycles__bold-text progress-info-box-headers">Achieved when: </span><div class="progress-info-box__text">$goal_achievement</div></div>
EOF;
			}

			
			
			// content for Action Plan Steps
			if ($each_pbcstep1->type == 'Pbc_step' && $vce->pbcsteps_view == 'ap_step') {

				$media_link = "";
				if (isset($each_pbcstep1->components[0])) {
					if ($each_pbcstep1->components[0]->type == "Media") {
						$media_link = '<a class="popup-vimeo link-button" href="https://vimeo.com/'.$each_pbcstep1->components[0]->guid.'">View Video</a><br>';
					}
				}
	
				$check_complete = "";
				if (isset($each_pbcstep1->pbccycle_status) && $each_pbcstep1->pbccycle_status == 'Complete') {
					$completed_steps_exist = true;
					if (!isset($query_string->tab_target) || $query_string->tab_target != 'view-completed-steps') {
						continue;
					}
					$link_container_class = "pbccycle-link-complete";
					$check_complete = "checked='checked'";
				} else {
					if (isset($query_string->tab_target) && $query_string->tab_target == 'view-completed-steps') {
						continue;
					}
					$link_container_class = "pbccycle-link";
				}

				unset($notes);
				if ($each_pbcstep1->comments !== '') {
					$notes = $each_pbcstep1->comments;
				}

				$aps_notes = (isset($notes)) ? $notes : 'none';
				$accordionArrow = 'arrow';
				$added_by = $vce->user->get_users($each_pbcstep1->created_by);
				$added_by = $added_by[0]->first_name . " " . $added_by[0]->last_name;
				$created_at = date("m/d/Y", $each_pbcstep1->created_at);
				$fo_href = $pbcsteps_url . '?pbcsteps_view=fo&ap_step_id='.$each_pbcstep1->component_id;
				$edit_href = $site_url . '/' . $each_pbcstep1->url . '?pbcsteps_view=ap_step&action=edit';
				$ap_step_url = $site_url . '/' . $each_pbcstep1->url . '?pbcsteps_view=ap_step';
				$cycle_url = $pbcsteps_url . '?pbcsteps_view=ap_step';
// $vce->dump('c'.$cycle_url);
// $vce->dump('s'.$ap_step_url);
				if ($requested_url == $each_pbcstep1->url && (!isset($query_string->tab_target) || $query_string->tab_target != 'view-completed-steps')) {
					$div_visibility = 'style="display:block;"';
					$accordionArrow = 'arrow-open';
					$resources_link = $cycle_url;
				} else {
					$resources_link = $ap_step_url;
				}

				$aps_assignee = Pbc_utilities::userlist_to_names($each_pbcstep1->aps_assignee, $vce);
				//links to related focused observations
				if (isset($fos_per_ap_step[$each_pbcstep1->component_id])) {
					$number_of_related_focused_observations = count($fos_per_ap_step[$each_pbcstep1->component_id]);
				} else {
					$number_of_related_focused_observations = 0;
				}

				$redirect_url = $site_url . '/' . $each_pbcstep1->url;
				// $vce->dump($redirect_url);
				// the instructions to pass through the form with specifics
				$dossier = array(
				'type' => 'Pbc_utilities',
				'procedure' => 'add_as_resource_requester_id',
				'url_of_resource_library' => $vce->site->site_url . '/resource_library',
				// 'url_of_resource_library' => $vce->site->site_url . '/pbc_resource_library_01',
				'component_id' => $each_pbcstep1->component_id,
				'redirect_url' => $redirect_url,
					'component_title' => $each_pbcstep1->title
				);
		
				// add dossier for requesting a resource
				$dossier_for_add_resource_requester_id = $vce->generate_dossier($dossier);

				$dossier = array(
					'type' => 'Pbc_utilities',
					'procedure' => 'add_as_resource_requester_id',
					'url_of_resource_library' => $vce->site->site_url . '/usermedia',
					'component_id' => $each_pbcstep1->component_id,
					'redirect_url' => $redirect_url,
					'component_title' => $each_pbcstep1->title
					);
			
				// add dossier for requesting a resource
				$dossier_for_add_my_library_resource_requester_id = $vce->generate_dossier($dossier);

				$dossier = array(
					'type' => 'Pbc_utilities',
					'procedure' => 'add_as_resource_requester_id',
					'url_of_resource_library' => $vce->site->site_url . '/org_library',
					'component_id' => $each_pbcstep1->component_id,
					'redirect_url' => $redirect_url,
					'component_title' => $each_pbcstep1->title
					);
			
				// add dossier for requesting a resource
				$dossier_for_add_org_library_resource_requester_id = $vce->generate_dossier($dossier);
				

				// the instructions to pass through the form	
				$dossier = array(
					'type' => $each_pbcstep1->type,
					'procedure' => 'update',
					'component_id' => $each_pbcstep1->component_id,
					'form_location' => __FUNCTION__,
					'created_at' => $each_pbcstep1->created_at,
					'redirect_url' => $redirect_url
				);

				// generate dossier
				$dossier_for_update = $vce->generate_dossier($dossier);				
				// create accordion box
				// $completedAccordion = $vce->content->accordion('Completed', ' ', false, false, 'completed-accordion');

				//find out if the logged in user should see the edit button
				// (In this case, the user must either be a participant of the step or the creator)
				// $vce->dump($each_component);
			// $vce->dump($each_component->can_edit($vce));
			//use this $can_edit if it is decided that everyone can edit (following the permissions set in pbccycles)
				$can_edit = $vce->page->can_edit($each_component);
				// $can_edit = (in_array($vce->user->user_id, explode('|', trim($each_pbcstep1->aps_assignee, '|'))) ? true : false);
				$edit_button = '';

				// show edit button if user is creator, has permissions, and it is not checked as complete
				if (($can_edit || $each_pbcstep1->created_by == $vce->user->user_id) && $check_complete != "checked='checked'") {
					$edit_button = '<button id="edit-btn" class="admin-toggle edit-toggle link-button align-right align-center"><span class="edit-btn-text">Edit</span></button>';
				}	

				// hide resource link if checked as complete. 
				if ($check_complete != "checked='checked'") {
					$resources_and_comments_link = <<<EOF
					<button class="resources_comments_accordion_btn" link_location="$resources_link">Resources & Comments ($number_of_subcomponents)<span class="$accordionArrow"></span></button>
EOF;
				} else {
					$resources_and_comments_link = NULL;
				}

				/**
				 * This condition empties the variable "$subcomponents_of_pbcsteps" if the component being looped through is not the requested url
				 * This is necessary because the mobile app searches for media.
				 * The child media is added to $subcomponents_of_pbcsteps for the requested url, and needs to be emptied for all other components in the list.
				 */
				// $urls = $vce->site->requested_url  . '____' . $each_pbcstep1->url;
				// $vce->plog($urls, 1);

				if ($vce->site->requested_url != $each_pbcstep1->url) {
					$subcomponents_to_show = NULL;
					// $vce->plog('1st', 1);
				} else {
					$subcomponents_to_show = $subcomponents_of_pbcsteps;
					// $vce->plog('2nd', 1);
				}
				// $vce->plog($subcomponents_to_show, 1);
				// $subcomponents_to_show = $subcomponents_of_pbcsteps;

				$tab1_id = 'view-steps';
				$tab1_label = 'View Steps';
				$ap_steps_array[$each_pbcstep1->created_at] = <<<EOF
<div class="pbc-cycles__table-item aps-link $link_container_class">
	<div class="pbc-cycles__step-name table-item__margin">$each_pbcstep1->title</div>
	<div class="pbc-cycles__assigned-to table-item__margin"><span class="pbc-cycles__bold-text">Assigned to: </span>$aps_assignee</div>
	<div class="pbc-cycles__step-notes table-item__margin"><span class="pbc-cycles__bold-text">Notes: </span>$aps_notes</div>
	<div class="pbc-cycles__step-added-date">Added by $added_by on $created_at</div>

	<div class="number-of-fo">
		<span class="pbc-cycles__bold-text">Focused Observations on this step: </span>$number_of_related_focused_observations
		<a class="fo-for-step-link" href="$fo_href">Go to focused observations</a>
	</div>

	<form id="form" class="asynchronous-form completed-checkbox-form" method="post" action="$vce->input_path" autocomplete="off">
		<input type="hidden" name="dossier" value="$dossier_for_update">
		<label class="pbc-cycles__completed-checkbox completed-checkbox">Complete
			<input class="pbc_cycles__completed-checkbox-input completed-checkbox-input" value="Complete" type="checkbox"  name="pbccycle_status" $check_complete>
			<span class="checkmark"></span>
		</label>
	</form>

	<a class="aps-edit-link" href="$edit_href" tabindex="-1">
		$edit_button
	</a>

	<div class="accordion-container">
		$resources_and_comments_link
		<div class="resources_comments_accordion_content" $div_visibility>
			<div class="resources_comments_accordion-heading">Resources</div>
			<br>
			<form id="action_plan_step_resource_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
			<input type="hidden" name="dossier" value="$dossier_for_add_resource_requester_id">
			<a href="" tabindex="-1"><button class="resource-library-btn button__primary" >Add Resource from Resource Library</button></a>
			</form>

			<form id="action_plan_step_my_library_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
			<input type="hidden" name="dossier" value="$dossier_for_add_my_library_resource_requester_id">
			<a href="" tabindex="-1"><button class="resource-library-btn button__primary">Add Resource from My Library</button></a>
			</form>

			<form id="action_plan_step_my_library_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
			<input type="hidden" name="dossier" value="$dossier_for_add_org_library_resource_requester_id">
			<a href="" tabindex="-1"><button class="resource-library-btn button__primary">Add Resource from Org Library</button></a>
			</form>
			$subcomponents_to_show
		</div>
		
	</div>
</div>
EOF;
			}


		// content for focused observation

		if ($each_pbcstep1->type == 'Pbc_focused_observation' && $vce->pbcsteps_view == 'fo' && $each_pbcstep1->ap_step_id == $vce->ap_step_id) {

				// $media_link = '';
				// if (isset($each_pbcstep1->components[0])) {
				// 	if ($each_pbcstep1->components[0]->type == "Media") {
				// 		$media_link = '<a class="popup-vimeo link-button" href="https://vimeo.com/'.$each_pbcstep1->components[0]->guid.'">View Video</a><br>';
				// 	}
				// }
	

				$link_container_class = "pbccycle-link";
				

				if ($each_pbcstep1->preparation_notes !== '') {
					$notes = $each_pbcstep1->preparation_notes;
				}

				$fo_notes = (isset($notes)) ? $notes : 'none';
				$accordionArrow = 'arrow';
				$added_by = $vce->user->get_users($each_pbcstep1->created_by);
				$added_by = $added_by[0]->first_name . " " . $added_by[0]->last_name;
				$created_at = date("m/d/Y", $each_pbcstep1->created_at);
				$related_ap_step = (isset($vce->ap_step_id) ? $vce->page->get_requested_component($vce->ap_step_id) : $vce->page->get_requested_component($each_pbcstep1->ap_step_id));
				$related_ap_step_edit_link = $site_url . '/' . $related_ap_step->url . '?action=edit';
				$related_ap_step_title = $related_ap_step->title;
				$related_ap_step_assignees = NULL;
				if (isset($related_ap_step->aps_assignee)) {
					$related_ap_step_assignees = $related_ap_step->aps_assignee;
				}
				$apstep_href = $pbcsteps_url . '?pbcsteps_view=ap_step';
				$edit_href = $site_url . '/' . $each_pbcstep1->url . '?pbcsteps_view=fo&action=edit&ap_step_id='.$vce->ap_step_id;
				$fo_url = $site_url . '/' . $each_pbcstep1->url . '?pbcsteps_view=fo&ap_step_id='.$vce->ap_step_id;
				$cycle_url = $pbcsteps_url . '?pbcsteps_view=fo&ap_step_id='.$vce->ap_step_id;

				if ($requested_url == $each_pbcstep1->url) {
					$div_visibility = 'style="display:block;"';
					$accordionArrow = 'arrow-open';
					$resources_link = $cycle_url;
				} else {
					$resources_link = $fo_url;
				}
				
				if (isset($related_ap_step_assignees)) {
					$related_ap_step_assignees = Pbc_utilities::userlist_to_names($related_ap_step_assignees, $vce);
				}
				$observers = Pbc_utilities::userlist_to_names($each_pbcstep1->observers, $vce);
				$observed = Pbc_utilities::userlist_to_names($each_pbcstep1->observed, $vce);


				$redirect_url = $site_url . '/' . $each_pbcstep1->url . '?pbcsteps_view=fo&ap_step_id=' .$vce->ap_step_id;
				// $vce->dump($redirect_url);
				// the instructions to pass through the form with specifics
				$dossier = array(
					'type' => 'Pbc_utilities',
					'procedure' => 'add_as_resource_requester_id',
					'url_of_resource_library' => $vce->site->site_url . '/resource_library',
					'component_id' => $each_pbcstep1->component_id,
					'redirect_url' => $redirect_url,
					'component_title' => $each_pbcstep1->title
					);
			
				// add dossier for requesting a resource
				$dossier_for_add_resource_requester_id = $vce->generate_dossier($dossier);

				$dossier = array(
					'type' => 'Pbc_utilities',
					'procedure' => 'add_as_resource_requester_id',
					'url_of_resource_library' => $vce->site->site_url . '/usermedia',
					'component_id' => $each_pbcstep1->component_id,
					'redirect_url' => $redirect_url,
					'component_title' => $each_pbcstep1->title
					);
			
				// add dossier for requesting a resource
				$dossier_for_add_my_library_resource_requester_id = $vce->generate_dossier($dossier);

				$dossier = array(
					'type' => 'Pbc_utilities',
					'procedure' => 'add_as_resource_requester_id',
					'url_of_resource_library' => $vce->site->site_url . '/org_library',
					'component_id' => $each_pbcstep1->component_id,
					'redirect_url' => $redirect_url,
					'component_title' => $each_pbcstep1->title
					);
			
				// add dossier for requesting a resource
				$dossier_for_add_org_library_resource_requester_id = $vce->generate_dossier($dossier);
				
				/**
				 * This condition empties the variable "$subcomponents_of_pbcsteps" if the component being looped through is not the requested url
				 * This is necessary because the mobile app searches for media.
				 * The child media is added to $subcomponents_of_pbcsteps for the requested url, and needs to be emptied for all other components in the list.
				 */

				if ($vce->site->requested_url != $each_pbcstep1->url) {
					$subcomponents_to_show = NULL;
				} else {
					$subcomponents_to_show = $subcomponents_of_pbcsteps;
				}

				$tab1_id = 'view-fo';
				$tab1_label = 'View Observations';
				$fo_array[$each_pbcstep1->created_at] = <<<EOF
				<div class="pbc-cycles__table-item $link_container_class">
					<div class="pbc-cycles__fo-name table-item__margin">$each_pbcstep1->title</div>
					<div class="pbc-cycles__fo-observers table-item__margin"><span class="pbc-cycles__bold-text">Observers:</span> $observers</div>
					<div class="pbc-cycles__fo-observed table-item__margin"><span class="pbc-cycles__bold-text">Observed:</span> $observed</div>
					<div class="pbc-cycles__fo-focus table-item__margin"><span class="pbc-cycles__bold-text">Focus:</span> $each_pbcstep1->focus</div>
					<div class="pbc-cycles__fo-notes table-item__margin"><span class="pbc-cycles__bold-text">Preparation Notes: </span>$fo_notes</div>


					<a class="fo-edit-link" href="$edit_href" tabindex="-1">
					<button id="edit-btn" class="admin-toggle edit-toggle link-button align-right align-center"><span class="edit-btn-text">Edit</span></button>
					</a>
					<div class="pbc-cycles__fo-added-date">Added by $added_by on $created_at</div>
					<a class="fo-link" href="$apstep_href"><div class="back-icon"></div>Return to Action Plan Steps</a>

					<div class="accordion-container">
						<button class="resources_comments_accordion_btn" link_location="$resources_link">Resources & Comments ($number_of_subcomponents)<span class="$accordionArrow"></span></button>
						<div class="resources_comments_accordion_content" $div_visibility><div class="resources_comments_accordion-heading">Resources</div>
						<br>
						<form id="action_plan_step_resource_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
						<input type="hidden" name="dossier" value="$dossier_for_add_resource_requester_id">				
						<a href="" tabindex="-1"><button class="resource-library-btn button__primary">Add Resource from Resource Library</button></a>
						</form>

						<form id="action_plan_step_my_library_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
						<input type="hidden" name="dossier" value="$dossier_for_add_my_library_resource_requester_id">
						<a href="" tabindex="-1"><button class="resource-library-btn button__primary">Add Resource from My Library</button></a>
						</form>

						<form id="action_plan_step_my_library_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
						<input type="hidden" name="dossier" value="$dossier_for_add_org_library_resource_requester_id">
						<a href="" tabindex="-1"><button class="resource-library-btn button__primary">Add Resource from Org Library</button></a>
						</form>
						$subcomponents_to_show
					</div>
				</div>
			</div> <!-- solution div -->
			
EOF;
			
			}

		} // end of loop through child components (foreach ($pbcsteps1 as $k => $each_pbcstep1) {)

		// convert content arrays into tab content
		ksort($ap_steps_array);
		
		foreach ($ap_steps_array as $this_content) {
			$tab1_content .= $this_content;
		}
		ksort($fo_array);
		foreach ($fo_array as $this_content) {
			$tab1_content .= $this_content;
		}
	}

			if ($vce->pbcsteps_view == 'ap_step') {
				$tab1_visibility = true; 
				$tab3_visibility = false;

// 				$tab1_content .= <<<EOF
// 				$completedAccordion
// EOF;

				if (!isset($tab1_id )){
					$tab1_visibility = false;
					$tab3_visibility = true;
					$tab1_id = 'view-steps';
					$tab1_label = 'View Step';
					$tab1_content = <<<EOF
					<div class="empty"></div>
EOF;
				}
				// $vce->dump($vce->action);
				if (isset($vce->action) && $vce->action == 'edit' && isset($edit_pbcstep)) {
					// $vce->dump('in edit');
					$tab1_visibility = false;
					$tab3_visibility = true;
					$tab3_id = 'edit-steps';
					$tab3_label = 'Edit Step';
					$tab3_content = $edit_pbcstep;
				} else {
					$tab3_id = 'add-steps';
					$tab3_label = 'Add Step';
					$tab3_content = $create_pbcstep;
				}
			}

			if ($vce->pbcsteps_view == 'fo') {
				$tab1_visibility = true;
				$tab3_visibility = false;

				if (!isset($tab1_id )){
					$tab1_visibility = false;
					$tab3_visibility = true;
					$tab1_id = 'view-fo';
					$tab1_label = 'View Observations';
					$tab1_content = <<<EOF
					<div class="empty"></div>
EOF;
				}
				if (isset($vce->action) && $vce->action == 'edit' && isset($edit_fo)) {

					$tab1_visibility = false;
					$tab3_visibility = true;
					$tab3_id = 'edit-fo';
					$tab3_label = 'Edit Observation';
					$tab3_content = $edit_fo;
				} else {
					$related_ap_step = (isset($vce->ap_step_id) ? $vce->page->get_requested_component($vce->ap_step_id) : $vce->page->get_requested_component($each_pbcstep1->ap_step_id));
					$related_ap_step_edit_link = $site_url . '/' . $related_ap_step->url . '?action=edit';
					$related_ap_step_title = $related_ap_step->title;
					
					$tab3_id = 'add-fo';
					$tab3_label = 'Add Observation';
					$tab3_content = $create_fo;

				}
			}



			if ($vce->pbcsteps_view == 'goal') {
				$tab1_visibility = true;

				$tab1_id = 'add-goal';
				$tab1_label = 'Add Goal';
				$tab1_content = $create_goal;
			}

			// condition for completed steps page
			if (isset($query_string->tab_target) && $query_string->tab_target == 'view-completed-steps') {
				$completed_tab_visibility = TRUE;
				$uncompleted_tab_visibility = FALSE;
				$tab1_visibility = FALSE;
				$tab3_visibility = FALSE;
			} else {
				$completed_tab_visibility = FALSE;
				$uncompleted_tab_visibility = TRUE;
			}


			// create tabs
			$tabs_input = array (
				'tabs__container1' => array(
					'tabs' => array(),
				),
			);

			$existing_query_string = NULL;
			if (isset($query_string)) {
				$existing_query_string = $query_string;
			}

			if (isset($tab1_content)) {
				$reload = FALSE;
				if (isset($completed_steps_exist) && $completed_steps_exist == true) {
					$reload = TRUE;
				}

				$tab1_label = (isset($tab1_label)) ? $tab1_label : NULL;
				$tab1_id = (isset($tab1_id)) ? $tab1_id : NULL;
				$tab1_id = (isset($tab1_label)) ? $tab1_label : NULL;
				$tabs_input['tabs__container1']['tabs']['tab1'] = array(
					'id' => $tab1_id,
					'label' => $tab1_label,
					'content' => $tab1_content,
					// 'visibility' => true
					'visibility' => $tab1_visibility,
					'reload' => $reload,
					'tab_target' => 'default',
					'existing_query_string' => $existing_query_string
				);
			}

			if (isset($completed_steps_exist) && $completed_steps_exist == true) {
				$tabs_input['tabs__container1']['tabs']['tab2'] = array(
					'id' => $tab1_id,
					'label' => 'View Completed Steps',
					'content' => $tab1_content,
					'visibility' => $completed_tab_visibility,
					'reload' => TRUE,
					'tab_target' => 'view-completed-steps',
					'existing_query_string' => $existing_query_string
				);
			}

			if (isset($tab3_content)) {
				// $vce->dump($tab3_id);
				$tabs_input['tabs__container1']['tabs']['tab3'] = array(
					'id' => $tab3_id,
					'label' => $tab3_label,
					'content' => $tab3_content,
					'visibility' => $tab3_visibility
				);
			}

			$tabs_input = (isset($edit_cycle_tabs_input)) ? $edit_cycle_tabs_input : $tabs_input;
			$tabs_input = (isset($edit_goal_tabs_input)) ? $edit_goal_tabs_input : $tabs_input;
			
			$content .= Pbc_utilities::create_tab($tabs_input);

		// add content for the main tabs (either ap_step or fo)
		$vce->content->add('main',$content);
		
// $vce->dump($vce->pbcsteps_view);
		// load hooks for progress arrows
		if (isset($vce->site->hooks['arrows'])) {
			foreach ($vce->site->hooks['arrows'] as $hook) {
				$progressArrows = call_user_func($hook, $pageLinks);
			}
		}

		$vce->content->add('progress_arrows', $progressArrows);

		// load hooks
		if (isset($vce->site->hooks['titleBar'])) {
			foreach ($vce->site->hooks['titleBar'] as $hook) {
				$title = call_user_func($hook, $title, $icon);
			}
		}

		$vce->content->add('title', $title);

		// load hooks
		if (isset($vce->site->hooks['sidebar'])) {
			foreach ($vce->site->hooks['sidebar'] as $hook) {
				$sidebarContainer = call_user_func($hook, $sidebarContent);
			}
		}

		$vce->content->add('sidebar', $sidebarContainer);

		// Create progress info boxes
		if ($boxCount > 0) {
			
			// $vce->dump($each_component);
			if (!isset($vce->content->store['default']->cycle_link_info)) {
				$pbc_home_location = new Pbc_home_location;
				$parent_cycle_component = $vce->page->get_requested_component($each_component->parent_id);
				$cycle_link_info = $pbc_home_location->generate_cycle_info($parent_cycle_component, $vce);
				$vce->content->add('cycle_link_info', $cycle_link_info);
				// $vce->dump($vce->content->store['default']->cycle_link_info);
			}

			$cycle_link_info = $vce->content->output('cycle_link_info', true);

			$boxContents = <<<EOF
<div class="progress-info-box progress-info-box__one">
	<span class="pbc-cycles__bold-text progress-info-box-headers">Selected Cycle:</span>$cycle_link_info
</div>
EOF;
		}

		if ($boxCount > 1) {
			$boxContents .= <<<EOF
<div class="progress-info-box progress-info-box__two">
	<span class="pbc-cycles__bold-text progress-info-box-headers">Goal:</span>$goal_content
	<a href="$goal_edit_href">
		<button id="edit-btn" class="admin-toggle edit-toggle link-button align-right align-center">
			<span class="edit-btn-text">Edit</span>
		</button>
	</a>
</div>
EOF;
		}

		if ($boxCount > 2) {
			$boxContents .= <<<EOF
<div class="progress-info-box progress-info-box__three">
	<span class="pbc-cycles__bold-text progress-info-box-headers">Selected Step:</span>
	<div class="progress-info-box__text">$related_ap_step_title </div>
	<p><span class="pbc-cycles__bold-text progress-info-box-headers">Assigned To:</span> $related_ap_step_assignees</p>
	<a class="progress-info-box__link" href="$pbcsteps_url?pbcsteps_view=ap_step">
		<div class="back-icon"></div>Return to Action Plan Steps
	</a>
	<a href="$related_ap_step_edit_link">
		<button id="edit-btn" class="admin-toggle edit-toggle link-button align-right align-center">
			<span class="edit-btn-text">Edit</span>
		</button>
	</a>
</div>
EOF;
		}

		$vce->content->add('progress_arrows', '<div class="progress-info-box-container">' . $boxContents . '<button class="progress-info-box-expand-btn button__secondary" aria-label="expand/collapse"><span class="arrow"></span></button></div>');

		


}


	/**
	 * fields for ManageRecipes
	 */
	public function recipe_fields($recipe) {
	
		global $site;
		
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
		
$elements = <<<EOF
<input type="hidden" name="auto_create" value="forward">
<label>
<input type="text" name="title" value="$title" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Title</div>
<div class="label-error">Enter a Title</div>
</div>
</label>
EOF;

		return $elements;
		
	}

}