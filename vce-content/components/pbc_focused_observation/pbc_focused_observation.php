<?php

class Pbc_focused_observation extends Component {

	/**
	 * basic info about the component
	 * this component was based on the "Reflection_assignment" component
	 */
	public function component_info() {
		return array(
			'name' => 'Focused Observation',
			'description' => 'Action Plan Focused observation',
			'category' => 'pbc',
			'recipe_fields' => array('title','description','potence')
		);
	}

	/**
	 * adding assignment specific mata to page object
	 */
	public function check_access($each_component, $vce) {

		// adding assignment specific mata to page object
		$vce->assignment_id = $each_component->component_id;
		$vce->assignment_type = $each_component->type;
// 		$vce->goal_achievement_evidence =  $each_component->goal_achievement_evidence;
// 		$vce->action_plan_goal = $each_component->action_plan_goal;

	
		return true;
	}

	/**
	 * last component was the requested id, so generate links for this component
	 * by default this is a simple html link
	 */
	public function as_link($each_component, $vce) {
		return false;
	}


	/**
	 *
	 */
	public function as_content($each_component, $vce) {
		// $vce->dump('fo as_content');

		// return false;
			// the instructions to pass through the form
			$dossier = array(
			'type' => $each_component->type,
			'procedure' => 'update',
			'component_id' => $each_component->component_id,
			'created_at' => $each_component->created_at,
			);
			
			
// 			generate dossier
			$dossier_for_update = $vce->generate_dossier($dossier);
// 			$dossier_for_update = $vce->generate_dossier($each_component->dossier);
		
			// get course component info
			$course = $vce->page->components[(count($vce->page->components) - 1)];
		
			$recipe = isset($each_component->recipe) ? (object) $each_component->recipe : null;
		
			// add javascript to page
			$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');

			// add touch-punch jquery to page
			$vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js','jquery-touchpunch-ui');

			// this is the current template, but the recipe will control this.
			$template = isset($vce->page->template) ? $vce->page->template : null;
			
			// for date picker
			$id_key = uniqid();
			
			// required if not admin
			$required = ($vce->user->role_id == 1) ? '' : 'tag="required"';
			
		
		
		
$content = '';


		$vce->content->add('main',$content);
	}
	


	/**
	 * close div for tab
	 */
	public function as_content_finish($each_component, $vce) {
		// $vce->dump('fo as_content_finish');
$content = <<<EOF
EOF;
	
		$vce->content->add('postmain',$content);
	
	}

	
	/**
	 *
	 */
	public function edit_component($each_component, $vce) {
		// return false;
		// $vce->dump('edit fo');

		if ($vce->page->can_edit($each_component) || $vce->user->sub_role == 1) {

			if (isset($vce->page->query_string)) {
				$query_string = json_decode($vce->page->query_string);
				if (isset($query_string->ap_step_id)){
					// $vce->site->add_attributes('ap_step_id', $query_string->ap_step_id);
					$vce->ap_step_id = $query_string->ap_step_id;
				}
			}
		

			// the instructions to pass through the form
			$parent_cycle = $vce->page->components[(count($vce->page->components) - 3)];
			$redirect_url = $vce->site->site_url . '/' . $parent_cycle->url . '?pbcsteps_view=fo&ap_step_id='.$vce->ap_step_id;

			//get cycle id
			$pbc_cycles_id = pbc_utilities::find_component_id($each_component->parent_id, "Pbccycles");
			
			$dossier = array(
			'type' => $each_component->type,
			'procedure' => 'update',
			'component_id' => $each_component->component_id,
			'created_at' => $each_component->created_at,
			'redirect_url' => $redirect_url,
			'pbc_cycles_id' => $pbc_cycles_id,
			'ap_step_id' => $each_component->ap_step_id
			);

// 			generate dossier
			$dossier_for_update = $vce->generate_dossier($dossier);
// 			$dossier_for_update = $vce->generate_dossier($each_component->dossier);
		
			// get course component info
			$course = $vce->page->components[(count($vce->page->components) - 1)];
		
			$recipe = isset($each_component->recipe) ? (object) $each_component->recipe : null;
		
			// add javascript to page
			$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');

			// add touch-punch jquery to page
			$vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js','jquery-touchpunch-ui');

			// this is the current template, but the recipe will control this.
			$template = isset($vce->page->template) ? $vce->page->template : null;
			
			// for date picker
			$id_key = uniqid();
			
			// required if not admin
			$required = ($vce->user->role_id == 1) ? '' : 'tag="required"';
			
			
			



$content = <<<EOF
<form id="update_$each_component->component_id" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
EOF;


		$input = array(
			'type' => 'textarea',
			'name' => 'title',
			'value' => $each_component->title,
			'data' => array(
					'rows' => '4',
					'tag' => 'required',
			)
		);

		$fo_title_input = $vce->content->create_input($input,'Title');

	
		// add focused observation inputs
		// select users in same group
		$filter_by = array(
			'group' => $vce->user->group
		);
	
		$users_in_same_group = pbc_utilities::filter_users($filter_by, $vce);
	
		// Observer multi-select menu			
		$dd_array = array(
		'type' => 'select',
		'required' => 'true',
		'dl' => 'observers', 
		'dl_name' => 'Observers', 
		'dl_id' => '', 
		'selected_users' => $each_component->observers,
		'component_id' => $each_component->component_id, 
		'component_method' => __FUNCTION__,
		'get_user_array' => array('user_ids' => $users_in_same_group)
		);
	
		$input = pbc_utilities::datalist_add_multiple_select($dd_array, $vce);
		$fo_multiple_observer_input = $vce->content->create_input($input,'Observers');

		// Observed Participants multi-select menu			
		$dd_array = array(
			'type' => 'select',
			'required' => 'true',
			'dl' => 'observed', 
			'dl_name' => 'Observed Participants', 
			'dl_id' => '', 
			'selected_users' => $each_component->observed,
			'component_id' => $each_component->component_id,  
			'component_method' => __FUNCTION__,
			'get_user_array' => array('user_ids' => $users_in_same_group)
		);
	
		$input = pbc_utilities::datalist_add_multiple_select($dd_array, $vce);
		$fo_multiple_observed_input = $vce->content->create_input($input,'Observed Participants');
		
		$input = array(
			'type' => 'textarea',
			'name' => 'focus',
			'value' => $each_component->focus,
			'data' => array(
					'rows' => '4',
					'tag' => 'required',
			)
		);

		$fo_focus_input = $vce->content->create_input($input,'Focus');

		$input = array(
			'type' => 'text',
			'name' => 'date',
			'class' => 'datepicker',
			'value' => $each_component->date,
			'data' => array (
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$fo_date_input = $vce->content->create_input($input,'Observation Date');

		$input = array(
			'type' => 'textarea',
			'name' => 'preparation_notes',
			'value' => $each_component->preparation_notes,
			'data' => array(
					'rows' => '4',
			)
		);

		$fo_prep_notes_input = $vce->content->create_input($input,'Preparation Notes');

		$content .= <<<EOF
$fo_title_input
$fo_multiple_observer_input
$fo_multiple_observed_input
$fo_focus_input
$fo_date_input
$fo_prep_notes_input
<input class="button__primary" type="submit" value="Update" >
</form>
EOF;



			if ($vce->page->can_delete($each_component) || $vce->user->sub_role == 1) {


				// the instructions to pass through the form
				$dossier = array(
				'type' => $each_component->type,
				'procedure' => 'delete',
				'component_id' => $each_component->component_id,
				'created_at' => $each_component->created_at,
				'parent_url' => $vce->requested_url,
				'rf_id' => $each_component->rf_id
				);

				// generate dossier
				$dossier_for_delete = $vce->generate_dossier($dossier);
			
$content .= <<<EOF
<form id="delete_$each_component->component_id" class="delete-form float-right-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<input class="delete-btn button__primary" type="submit" value="Delete">
</form>
EOF;

			}

		$vce->content->add('edit_fo',$content);

		}

	}




		 /**
     * add the event to pass on to notify
     */
	public static function notification($component_id = null, $action = null) {

		global $vce;
		
		// get actor name
		$actor = $vce->user->email;
		if (isset($vce->user->first_name) || isset($vce->user->last_name)) {
			$actor = '';
			if (isset($vce->user->first_name)) {
				$actor .= $vce->user->first_name;
			}
			if (isset($vce->user->last_name)) {
				$actor .= ' ' . $vce->user->last_name;
			}
			$actor = trim($actor);
		}
		
		if ($action == 'create') {
		
			$event = array(
				'actor' => $actor,
				'action' => 'create',
				'verb' => 'added',
				'object' => 'a cycle'
			);
		
		}
		
		if ($action == 'update') {
		
			$event = array(
				'actor' => $actor,
				'action' => 'update',
				'verb' => 'updated',
				'object' => 'a cycle'
			);
		
		}
		
		if ($action == 'delete') {
		
			$event = array(
				'actor' => $actor,
				'action' => 'delete',
				'verb' => 'deleted',
				'object' => 'a cycle'
			);
		
		}
		
		/**
		 * The following block of code will always be needed in any notification to trigger the calls to parents of this component
		 **/
		
		// get parents
		$parents = $vce->page->get_parents($component_id);

		// call to notify method on parents in reverse order
		if (!empty($parents) && !empty($event)) {
		
			end($parents)->notification_event = $event;
		
			// work backwards
			for ($x = (count($parents) - 1);$x > -1;$x--) {
				// notify($parents)
				$result = $parents[$x]->notify($parents);
		
				// if boolean and true is returned by one of the parent components, then we end our search
				if (is_bool($result) && $result) {
					// end notify calls
					break;
				}
			}
		}
	
		return true;
	}
	
	
	/**
	 * This function is called to from notification
	 * @param array $parents
	 */
	public function notify($parents = null) {
	
		global $vce;
		// added because this was producing error messages in MyAccount
		if ($parents === null) {
			return FALSE;
		}
		// find the parent 2 levels back (Pbc_home_locations->Pbc_steps->Pbc_goal)
		// add one to to steps to subtract because the array is 0 based
		$index = count($parents) - 3;
		$parent_cycle_attributes = $parents[$index];
// $vce->dump($parents[$index]);
// exit;
		// get component configuration information from this component
		$component_name = get_class();
		$value = $vce->site->$component_name;
		$minutia = $component_name . '_minutia';
		$vector = $vce->site->$minutia;
		// $vce->dump($vector);
		// if $component_config exists, set it, otherwise set it to an empty array
		if (!empty($value) && !empty($vector)) {
			$component_config = json_decode($vce->site->decryption($value, $vector), true);
		} else {
			$component_config = array();
		}

		// get info about parent cycle
		$cycle_title = $parent_cycle_attributes->pbccycle_name;
		$cycle_url = $vce->site->site_url . '/' . $parent_cycle_attributes->url;

		// get included participants
		$user_ids_cycle_participants = json_decode(html_entity_decode($parent_cycle_attributes->user_ids_cycle_participants), TRUE);
		$cycle_participants = (isset($user_ids_cycle_participants['user_ids'])) ? $user_ids_cycle_participants['user_ids'] : NULL;
		$cycle_participants = explode('|', $cycle_participants);
		$cycle_participants = implode(',', $cycle_participants);


		// $vce->dump($cycle_participants);
		// exit;
		// need a way to know what options are available for pick-and-choose
		// use a subtractive approach to minimize what is stored in user_meta
		$announcements = array(
		'notify_on_create' => 'Notify me when someone has created a Focused Observation for a Cycle in which I am included.'
		);
		
		if (!$parents || !is_array($parents)) {
			// this may be where the notification in my account is
			if ((isset($component_config['create_email_notifier_toggle']) && $component_config['create_email_notifier_toggle'] == 'on')||(isset($component_config['create_site_notifier_toggle']) && $component_config['create_site_notifier_toggle'] == 'on')) {
				return $announcements;
			}
		}

		// is the parent of this event correct?
		if ($parents[count($parents) - 4]->type != 'Pbc_home_location') {
			return false;
		}
		
		$event = end($parents)->notification_event;
		
		$link = null;
		
		// work backwards to find link
		for ($x = (count($parents) - 1);$x > -1;$x--) {
			if (isset($parents[$x]->url)) {
				$link = $vce->site->site_url . '/' . $parents[$x]->url;
				break;
			}
		}
		
		// remember to provide the specific announcement
		if ($event['action'] == 'create') {
			// check to see if the component configuration wants this notification to be sent
			if (class_exists('Notifier') && isset($component_config['create_email_notifier_toggle']) && $component_config['create_email_notifier_toggle'] == 'on') {

				$proclamation = array();	
			
				$event['verb'] = 'created';
				$event['object'] = 'a cycle';
			
				// convert to array of array in order to add addtional events
				$event_array = array($event);

				$notification_content = html_entity_decode(stripcslashes($component_config['create_email_notifier_content']), ENT_QUOTES);
				$notification_content = str_replace("\r\n", '<br>', $notification_content);
				$notification_content = str_replace("\t", '&nbsp;&nbsp;', $notification_content);

				// replace any wildcards
				$notification_content = str_replace('{cycle_title}', $cycle_title, $notification_content);
				$notification_content = str_replace('{cycle_url}', $cycle_url, $notification_content);

				
				// allow for multipe proclamations by placing in a sub array
				$proclamation[] = array(
					// component type is needed if you are allowing for notifications options
					'component' => get_class($this),
					// add NotifierType if you want to only trigger that type of notification
					// 'type' => 'SiteNotifier',
					// announcement is needed if you are allowing for notifications options
					'announcement' => 'notify_on_reply',
					'recipient' => $cycle_participants,
					'event' => $event_array,
					'link' => $link,
					'subject' => 'OHS Coaching Companion Account Update',
					// content will override everything in the event array
					'content' => $notification_content
				);

				// this fires off the notification(s), then exits the method. I left the rest for future reference.
				Notifier::components_notify_method($proclamation);
				return TRUE;

		} else {
			// don't send a notification
			return FALSE;
		}
	}
                        $each_proclamation = array(
                        // component type is needed if you are allowing for notifications options
                        'component' => get_class($this),
                        // add NotifierType if you want to only trigger that type of notification
                        // 'type' => 'SiteNotifier',
                        // announcement is needed if you are allowing for notifications options
                        'announcement' => 'notify_on_reply',
                        //recipents can be provided as array if email address are provided or comma delinated user_id
                        'recipient' => $user_id,
                        //event is required, but you can supply an empty arrray()
                        // // $event = array(
                        // // 'actor' => 'name of user that did action',
                        // // 'action' => 'delete',
                        // // 'verb' => 'deleted',
                        // // 'object' => 'a comment'
                        // // );
                        'event' => $event_array,
                        //the url to view where the event took place
                        'link' => $link,
                        //a subject can be supplied
                        'subject' => 'Our Notification',
                        //content will override everything in the event array
                        'content' => 'testing'
                        );
		
		// this is a pattern that should be used to prevent any errors
		if (!empty($proclamation)) {
		
			// hook for allowing multiple types of notifications
			if (isset($vce->site->hooks['components_notify_method'])) {
				foreach($vce->site->hooks['components_notify_method'] as $hook) {
					call_user_func($hook, $proclamation);
				}
			}

			return true;
		
		}
		
		return false;
	}






	/**
	 *
	 */
	public function add_component($recipe_component, $vce) {
		// return false;
	// $vce->dump('pbc focused ob add_component');
	
		// get id of cycle to which this step belogs		
		$pbc_cycles_id = pbc_utilities::find_component_id($recipe_component->parent_id, "Pbccycles");
		//add url
		$specific_url = $vce->requested_url . '/' . 'fo' . time() . '-' . $vce->user->user_id;

		if (isset($vce->ap_step_id)) {
			$ap_step_id = $vce->ap_step_id;
		} else {
			$ap_step_id = '';
		}

		// create dossier
		// $recipe_component->dossier['title'] = $recipe_component->title; (because this was overwriting the given title)
		$recipe_component->dossier['assignment_category'] = $recipe_component->title;
		$recipe_component->dossier['list_order'] = 2;
		$recipe_component->dossier['pbc_cycles_id'] = $pbc_cycles_id;
		$recipe_component->dossier['url'] = $specific_url;
		$recipe_component->dossier['ap_step_id'] = $ap_step_id;

		$dossier_for_create = $vce->generate_dossier($recipe_component->dossier);
		
		// create dossier for checkurl functionality
		$dossier = array(
		'type' => $recipe_component->type,
		'procedure' => 'checkurl'
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_for_checkurl = $vce->generate_dossier($dossier);

		// get course component info
		$course = $vce->page->components[(count($vce->page->components) - 1)];
		
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		
		// add touch-punch jquery to page
		$vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js','jquery-touchpunch-ui');
		
		// for date picker
		$id_key = uniqid();

		// required if not admin
		$required = ($vce->user->role_id == 1) ? '' : 'tag="required"';
		
		
		


			$image_path = $vce->site->path_to_url(dirname(__FILE__));
							
			// get parent_parent
			$parent_component = $vce->page->components[(count($vce->page->components) - 1)];

			// get cycle component info
			$cycle = $vce->page->components[(count($vce->page->components) - 1)];
						
			$recipe = isset($each_component->recipe) ? (object) $each_component->recipe : null;

			$user_access = json_decode($parent_component->user_access, true);
			

			// add javascript to page
			$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter');
			
			// add touch-punch jquery to page
			$vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js','jquery-touchpunch-ui');
			
			// add stylesheet to page
			$vce->site->add_style(dirname(__FILE__) . '/css/style.css','courses-style');
			



$content = '';
$content .= <<<EOF
<form id="create_items" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_create">
EOF;


		$input = array(
			'type' => 'textarea',
			'name' => 'title',
			'data' => array(
					'rows' => '4',
					'tag' => 'required',
			)
		);

		$fo_title_input = $vce->content->create_input($input,'Title');

	
		// add focused observation inputs
		// select users in same group
		$filter_by = array(
			'group' => $vce->user->group
		);
	
		$users_in_same_group = pbc_utilities::filter_users($filter_by, $vce);
	
		// Observer multi-select menu			
		$dd_array = array(
		'type' => 'select',
		'required' => 'true',
		'dl' => 'observers', 
		'dl_name' => 'Observers', 
		'dl_id' => '', 
		'component_id' => null, 
		'selected_users' => $vce->user->user_id,
		'component_method' => __FUNCTION__,
		'required' => true,
		'get_user_array' => array('user_ids' => $users_in_same_group)
		);
	
		$input = pbc_utilities::datalist_add_multiple_select($dd_array, $vce);
		$fo_multiple_observer_input = $vce->content->create_input($input,'Observers');

		// Observed Participants multi-select menu			
		$dd_array = array(
			'type' => 'select',
			'required' => 'true',
			'dl' => 'observed', 
			'dl_name' => 'Observed Participants', 
			'dl_id' => '', 
			'component_id' => null, 
			'selected_users' => trim($cycle->cycle_participants, '|'),
			'component_method' => __FUNCTION__,
			'required' => true,
			'get_user_array' => array('user_ids' => $users_in_same_group)
		);
	
		$input = pbc_utilities::datalist_add_multiple_select($dd_array, $vce);
		$fo_multiple_observed_input = $vce->content->create_input($input,'Observed Participants');
		
		$input = array(
			'type' => 'textarea',
			'name' => 'focus',
			'data' => array(
					'rows' => '4',
					'tag' => 'required',
			)
		);

		$fo_focus_input = $vce->content->create_input($input,'Focus');

		$input = array(
			'type' => 'text',
			'name' => 'date',
			'class' => 'datepicker',
			'data' => array (
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$fo_date_input = $vce->content->create_input($input,'Observation Date');

		$input = array(
			'type' => 'textarea',
			'name' => 'preparation_notes',
			'data' => array(
					'rows' => '4',
			)
		);

		$fo_prep_notes_input = $vce->content->create_input($input,'Preparation Notes');

		$content .= <<<EOF
$fo_title_input
$fo_multiple_observer_input
$fo_multiple_observed_input
$fo_focus_input
$fo_date_input
$fo_prep_notes_input
<input class="button__primary" type="submit" value="Create" >
</form>
EOF;


		$vce->content->add('create_fo',$content);

	}
	




		/**
		 * This takes any filter critereon contained in $filter_by and returns a pipeline delineated list of users which fit that filter
		 */


		public static function filter_users($filter_by, $vce) {
	
			
			
			// get roles
			$roles = json_decode($vce->site->roles, true);
	
			// get roles in hierarchical order
			$roles_hierarchical = json_decode($vce->site->site_roles, true);
	
			$query = "SELECT " . TABLE_PREFIX . "users.* FROM " . TABLE_PREFIX . "users_meta INNER JOIN " . TABLE_PREFIX . "users ON " . TABLE_PREFIX . "users_meta.user_id = " . TABLE_PREFIX . "users.user_id";

			$current_list = $vce->db->get_data_object($query);
	
			// rekey data into array for user_id and vectors
			foreach ($current_list as $each_list) {
				$users_list[] = $each_list->user_id;
				$users[$each_list->user_id]['user_id'] = $each_list->user_id;
				$users[$each_list->user_id]['role_id'] = $roles[$each_list->role_id]['role_name'];
				$vectors[$each_list->user_id] = $each_list->vector;
			}
	
			// Second we query the user_meta table for user_ids
	
			// if (empty($filter_by)) {
			if (isset($users_list)) {
				// get meta data for the list of user_ids
				$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id IN (" . implode($users_list,',') . ")";

			} else {

				// get all meta data for all users because of filtering
				$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta";

			}
	
			$meta_data = $vce->db->get_data_object($query);
	
			
			// rekey data
			foreach ($meta_data as $each_meta_data) {
	
				// skip lookup
				if ($each_meta_data->meta_key == 'lookup') {
					continue;
				}
				// decrypt meta_data and add to list of users to run through the filter
				$users[$each_meta_data->user_id][$each_meta_data->meta_key] = User::decryption($each_meta_data->meta_value,$vectors[$each_meta_data->user_id]);
			}
		


			// prepare for filtering of roles limited by hierarchy
			if (!empty($filter_by)) {
				$role_name = array();
				// create a lookup array from role_name to role_hierarchy
				foreach ($roles as $roles_key=>$roles_value) {
					$role_name[$roles_value['role_name']] = $roles_value['role_hierarchy'];
				}
			}

			// loop through users, adding them to a list if they fit the search criteria
			$users_filtered = array();
			foreach ($users_list as $each_user) {
				// check if for filtering and scip any user which doesn't pass the filter
				if (!empty($filter_by)) {
					// loop through filters and check if any user fields are a match
					foreach ($filter_by as $filter_key=>$filter_value) {
						if ($filter_key == "role_id") {
							// make title of role
							$filter_value = $roles[$filter_value]['role_name'];
						} else {
							// if the hierarchy of this user role is less than the current user, skip this user
							if (!isset($role_name[$users[$each_user]['role_id']])) {

							}
					
							if (isset($roles[$vce->user->role_id]['role_hierarchy']) && $role_name[$users[$each_user]['role_id']] < $roles[$vce->user->role_id]['role_hierarchy']) {
								continue 2;
							}
						}
						// check if $filter_value is an array
						if (is_array($filter_value)) {
							// check if not in the array
							if (!isset($users[$each_user][$filter_key])) {
								continue 2;
							}
							if (!in_array($users[$each_user][$filter_key],$filter_value)) {
								// continue foreach before this foreach
								continue 2;
							}
						} else {
							// doesn't match so continue
							if (isset($users[$each_user][$filter_key]) && $users[$each_user][$filter_key] != $filter_value) {
								// continue foreach before this foreach
								continue 2;	
							}
						}
					}
				}
				// create array of filtered users
				$users_filtered_array[] = $users[$each_user]['user_id'];
			}
			
			//return pipeline delineated list of filtered users
			return implode('|', $users_filtered_array);
	}




        
        
	
	/**
	 * Creates component
	 * this is customized from the parent method to create datalists
	 * @param array $input
	 * @return calls component's procedure or echos an error message
	 */
	public function create($input) {
	
		global $vce;
		// $vce->log($input);

		$basepath = defined('INSTANCE_BASEPATH') ? INSTANCE_BASEPATH : BASEPATH;
$file = "log.txt";
$s = print_r($_POST, 1);
file_put_contents($basepath . $file, $s . PHP_EOL, FILE_APPEND);
		
		// takes care of glitch from mobile app and re-encodes the input saved in JSON format
		foreach ($input as $k=>$v) {
			if (is_object($v)) {
				$input[$k] = json_encode($v);
			}
		}

		// clean up url
		if (isset($input['url'])) {
			
			$input['url'] = $vce->site->url_checker($input['url']);
		}

		//save multi-select userlist to component	
		foreach($input as $key => $value) {
			if (strpos($key, 'user_ids') !== false) {
				$value = stripslashes($value);
				$value = html_entity_decode($value, ENT_QUOTES);
				$input[$key] = $value;
				$dl_input = json_decode($value);
				$dl_user_ids = trim($dl_input->user_ids, '|');
				$input[$dl_input->dl] = '|' . $dl_user_ids . '|';
			}
		}
	
		// call to create_component, which returns the newly created component_id
		$component_id = $this->create_component($input);
		

		
		
		


			
		
		


//update user_access list:

	// add users from drag and drop to user_access array of a cycle
	// (Makes an array of users from any and all drag-and-drops on page, gets array from 
	// cycle component, combines and stores them)	
	
	// get all user id's from drag-and-drops in form
		$user_ids_array = array();
		foreach($input as $key => $value) {
			if (strpos($key, 'user_ids') !== false) {
				$dl_input = json_decode(html_entity_decode($value));
				$uia = explode("|", $dl_input->user_ids);
				foreach ($uia as $id) {
					$user_ids_array[] = $id;
				}
			}
		}
		
		//get the user_ids_aps_assignee and convert to an array
		$drag_and_drop_datalist = implode('|', $user_ids_array);
		
	//explode into array
		$user_ids = $user_ids_array;
		$user_access_array = array();
		foreach($user_ids as $id) {
			$user_access_array[$id] = array("sub_role" => 2);
		}
	//get exiting user-access list from the cycle
		$query = 'SELECT meta_value FROM ' . TABLE_PREFIX . 'components_meta WHERE component_id =' . $input['pbc_cycles_id'] . ' AND meta_key = "user_access"' ;
		$result = $vce->db->get_data_object($query);
		$existing_users = NULL;
		if (isset($result[0])) {
			$existing_users = json_decode($result[0]->meta_value, true);
		}
// 		$site->log(json_decode($result[0]->meta_value));
		
	//add existing users to new user array
		foreach ($existing_users as $key => $value) {
			$user_access_array[$key] = $value;
		}


	//convert array to json encoded datastructure
		$user_access_array = $vce->db->mysqli_escape(json_encode($user_access_array));
		
	//add selected id's to cycle's user_access
		$query = 'UPDATE ' . TABLE_PREFIX . 'components_meta SET meta_value = "' . $user_access_array . '" WHERE component_id =' . $input['pbc_cycles_id'] . ' AND meta_key = "user_access"' ;
		$result = $vce->db->query($query);


	
		if ($component_id && $dl_user_ids) {
		
			
			$vce->site->add_attributes('message',$this->component_info()['name'] .$component_id. ' Created');
			
					//redirect to as_content after creation
			$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components WHERE component_id ="' . $component_id . '"' ;
			$component = $vce->db->get_data_object($query);
			$requested_url = $vce->site->site_url . '/' . $component[0]->url;
				

			
			//put together list to send emails to
			$user_list = trim($drag_and_drop_datalist, '|');
			$users_info = array('user_ids' => $user_list);
			$user_list = $vce->user->get_users($users_info);
	
		//get get cycle url
			$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components WHERE component_id ="' . $input['pbc_cycles_id'] . '"' ;
			$component = $vce->db->get_data_object($query);
			$cycle_url = $vce->site->site_url . '/' . $component[0]->url;
			$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components_meta WHERE component_id ="' . $input['pbc_cycles_id'] . '" AND meta_key = "title"' ;
			$component_meta = $vce->db->get_data_object($query);
			$cycle_title = $component_meta[0]->meta_value;

		//send email to each in the list
		foreach ($user_list as $a) {
			$email = $a->email;
			$fullname = $a->first_name . ' ' . $a->last_name;

			//get user data
// 			$pbc_cycles_id = pbc_utilities::get_user_data();

			//send email	
			$email_message = <<<EOF
Dear $a->first_name $a->last_name,<br>
<br>
Your OHS Coaching Companion user account has been included as a participant in a Focused Observation from the coaching cycle "$cycle_title" at this link:<br>
$cycle_url <br>
When you log in to the OHS Coaching Companion, this cycle will appear on your home page.<br>
<br>
If you have any questions, please contact your group administrator.<br>
<br>
Once you are registered with the ECLKC, you can access the OHS Coaching Companion here:<br>
<a href="https://eclkc.ohs.acf.hhs.gov/cc/">Coaching Companion Home</a><br>
<br>
Thank you,<br>
Your OHSCC Administrator<br>
EOF;

// $vce->log($email_message);

		$mail_attributes = array (
			'from' => array('coachingcompanion@eclkc.info', 'DoNotReply'),
			'to' => array(
				 array($email, $fullname)
			 ),
			'subject' => 'OHS Coaching Companion Account Update',
			'message' => $email_message,
			'html' => true,
			'SMTPAuth' => false
		 );	
		
		$vce->mail($mail_attributes);
	}
		
	
			echo json_encode(array('response' => 'success','procedure' => 'create','action' => 'reload','message' => 'Created','component_id' => $component_id));
			return;
		
		}
		
		echo json_encode(array('response' => 'error','procedure' => 'create','message' => "Error"));
		return;

	}






	
	
	//custom delete, also deletes reflection and feedback
   public function delete($input) {
		
	global $vce;
		$parent_url = $this->delete_component($input);
		
// 		$vce->site->log($input);

		if (isset($parent_url)) {
		
				$vce->site->add_attributes('message',$this->component_info()['name'] . " Deleted");

				echo json_encode(array('response' => 'success','procedure' => 'delete','action' => 'reload','url' => $parent_url, 'message' => "Deleted"));
				return;
			
		}

		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Errors"));
		return;
	
	}



	/**
	 * Updates data
	 * Customized from class.component so it will also update corp_groups datalist
	 * @param array $input
	 * @return calls component's procedure or echos an error message
	 */
	public function update($input) {
			
		global $vce;
		
		// takes care of glitch from mobile app and re-encodes the input saved in JSON format
		foreach ($input as $k=>$v) {
			if (is_object($v)) {
				$input[$k] = json_encode($v);
			}
		}
		
		
		// exit;
		//save multi-select userlist to component	
		foreach($input as $key => $value) {
			if (strpos($key, 'user_ids') !== false) {
				$value = stripslashes($value);
				$value = html_entity_decode($value, ENT_QUOTES);
				$input[$key] = $value;
				$dl_input = json_decode($value);
				$dl_user_ids = trim($dl_input->user_ids, '|');
				$input[$dl_input->dl] = '|' . $dl_user_ids . '|';
			}
		}

		if (parent::update_component($input)) {

			
//update user_access list:

	// add users from drag and drop to user_access array of a cycle
	// (Makes an array of users from any and all drag-and-drops on page, gets array from 
	// cycle component, combines and stores them)	
	
	// get all user id's from drag-and-drops in form (after editing)
		$user_ids = '';
		foreach($input as $key => $value) {
			if (strpos($key, 'user_ids') !== false) {
				$dl_input = json_decode(html_entity_decode($value));
				$user_ids .= '|'.$dl_input->user_ids;
			}
		}
				

		
	// get all user id's from drag-and-drops in form (before editing)
		$old_user_ids = '';
		foreach($input as $key => $value) {
			if (strpos($key, 'user_oldids') !== false) {
// 				$old_dl_input = json_decode(html_entity_decode($value));
				$old_user_ids .= '|'.$value;
			}
		}
		
		
	//explode into arrays
		$user_ids = trim($user_ids, '|');
		$user_ids = explode("|", $user_ids);
		$user_access_array = array();
		foreach($user_ids as $id) {
			$user_access_array[$id] = array("sub_role" => 2);
		}

		
		$old_user_ids = trim($old_user_ids, '|');
		$old_user_ids = explode("|", $old_user_ids);
		$old_user_access_array = array();
		foreach($old_user_ids as $id) {
			$old_user_access_array[$id] = array("sub_role" => 2);
		}
		

	//get existing user-access list from the cycle
		$query = 'SELECT meta_value FROM ' . TABLE_PREFIX . 'components_meta WHERE component_id =' . $input['pbc_cycles_id'] . ' AND meta_key = "user_access"' ;
		$result = $vce->db->get_data_object($query);
		$existing_users = json_decode($result[0]->meta_value, true);
// 		$vce->site->log($existing_users);
// 		$vce->site->log('<br>');
		
	//remove old users from array
		foreach ($old_user_access_array as $key => $value) {
			unset($existing_users[$key]);
		}
// 		$vce->site->log($existing_users);
// 				$vce->site->log('<br>');
// 		foreach ($existing_users as $key => $value) {
// 			if($key == "") {
// 				unset($existing_users[$key]);
// 			}
// 				
// 		}
		
	//add existing users to new user array
		foreach ($existing_users as $key => $value) {
			$user_access_array[$key] = $value;
		}
// 		$vce->site->log($user_access_array);
// 		$vce->site->log('<br>');
// 		$vce->site->log('<br>');


	//convert array to json encoded datastructure
		$user_access_array = $vce->db->mysqli_escape(json_encode($user_access_array));
		
	//add selected id's to cycle's user_access
		$query = 'UPDATE ' . TABLE_PREFIX . 'components_meta SET meta_value = "' . $user_access_array . '" WHERE component_id =' . $input['pbc_cycles_id'] . ' AND meta_key = "user_access"' ;
		$result = $vce->db->query($query);





	//get exiting user-access list from the cycle
// 		$query = 'SELECT meta_value FROM ' . TABLE_PREFIX . 'components_meta WHERE component_id =' . $input['pbc_cycles_id'] . ' AND meta_key = "user_access"' ;
// 		$result = $db->get_data_object($query);
// 		$existing_users = json_decode($result[0]->meta_value, true);
// 		$vce->site->log(json_decode($result[0]->meta_value));

// exit;

			
			$vce->site->add_attributes('message',$this->component_info()['name'] . " Updated");

			if (isset($input['redirect_url'])) {
				$redirect_url = $input['redirect_url'];
				unset($input['redirect_url']);
			} else {
				$redirect_url = NULL;
			}
		
			echo json_encode(array('response' => 'success','procedure' => 'update', 'url' => $redirect_url, 'action' => 'reload','message' => "Updated"));
			return;
		}
		
		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Permission Error"));
		return;
	
	}

	
	
	
/*
 add config info for this component
*/
public function component_configuration() {
	global $vce;
	$content = NULL;

		/**  Standard CRUD+Track for all site roles */
		$class = get_class($this);

		if (isset($vce->site->$class)) {
			$value = $vce->site->$class;
			$class_minutia = $class . '_minutia';
			$vector = $vce->site->$class_minutia;
			$config = json_decode($vce->site->decryption($value,$vector), true);
		}


		$permission_categories = array (
			'create' => '<div>These roles can create ' . $class . ' components:</div>',
			'read' => '<div>These roles can view ' . $class . ' components:</div>',
			'update' => '<div>These roles can edit' . $class . ' components:</div>',
			'delete' => '<div>These roles can delete ' . $class . ' components:</div>',
			'track' => '<div>These roles can track ' . $class . ' components:</div>',
		);
	
		foreach ($permission_categories as $k=>$v) {
			$elements = null;
			$elements .= $v;
			$input = array(
				'type' => 'checkbox',
				'name' => $k,
				'selected' => (isset($this->configuration[$k]) ? explode('|', $this->configuration[$k]) : null),
				'flags' => array(
					'label_tag_wrap' => true
				)
			);
			// add site roles as options
			foreach (json_decode($vce->site->site_roles) as $each_role) {
				foreach ($each_role as $key=>$value) {
					$input['options'][] = array(
						'value' => $key,
						'label' => $value->role_name
					);
				}
			}
			$elements .= $vce->content->input_element($input);
			$content .= $elements . '</br>';
		}
		/**  END: Standard CRUD+Track for all site roles */

	$configuration_input = array(
		'type' => 'checkbox',
		'name' => 'create_email_notifier_toggle',
		'options' => array(
			'value' => 'on',
			'selected' => ((isset($config['create_email_notifier_toggle']) && $config['create_email_notifier_toggle'] == 'on') ? true :  false),
			'label' => 'Send email notification on component creation?'
			)
		);
	$content .= $vce->content->create_input($configuration_input,'Email Notifier On: Create');


	$default_create_email_notifier_content = <<<EOF
A Focused Observation has been created for the Practice Based Coaching Cycle entitled "{cycle_title}" at this link:
{cycle_url}
Thank you,
Your OHSCC Administrator
EOF;

$default_create_email_notifier_content = (isset($config['create_email_notifier_content']) && $config['create_email_notifier_content'] != '') ? stripcslashes($config['create_email_notifier_content']) :  $default_create_email_notifier_content;

$configuration_input = array(
	'type' => 'textarea',
	'name' => 'create_email_notifier_content',
	'rows'=> 40,
	'options' => array(
		'value' =>	$default_create_email_notifier_content,
		'label' => 'Enter email notification content.',
		)
	);
$content .= $vce->content->create_textarea_input('create_email_notifier_content', $default_create_email_notifier_content, 'Email Notification Content On: Create');


$configuration_input = array(
	'type' => 'checkbox',
	'name' => 'create_site_notifier_toggle',
	'options' => array(
		'value' => 'on',
		'selected' => ((isset($config['create_site_notifier_toggle']) && $config['create_site_notifier_toggle'] == 'on') ? true :  false),
		'label' => 'Send site notification on component creation?'
		)
	);
$content .= $vce->content->create_input($configuration_input,'Email Notifier On: Create');



	$default_create_site_notifier_content = <<<EOF
A Focused Observation has been created for the Practice Based Coaching Cycle entitled "{cycle_title}" at this link:
{cycle_url}
Thank you,
Your OHSCC Administrator
EOF;
$default_create_site_notifier_content = (isset($config['create_site_notifier_content']) && $config['create_site_notifier_content'] != '') ? stripcslashes($config['create_site_notifier_content']) :  $default_create_site_notifier_content;


$configuration_input = array(
	'type' => 'textarea',
	'name' => 'create_site_notifier_content',
	'rows'=> 40,
	'options' => array(
		'value' =>	$default_create_site_notifier_content,
		'label' => 'Enter email notification content.',
		)
	);
	$content .= $vce->content->create_textarea_input('create_site_notifier_content', $default_create_site_notifier_content, 'Site Notification Content On: Create');

	return $content;
}
	
	


}