<?php

class Pbc_goal extends Component {

	/**
	 * basic info about the component
	 * this component was based on the "Reflection_assignment" component
	 */
	public function component_info() {
		return array(
			'name' => 'Action Plan Goal',
			'description' => 'Sets an Action Plan Goal',
			'category' => 'pbc'
		);
	}

	/**
	 * adding assignment specific mata to page object
	 */
	public function check_access($each_component, $vce) {

		// adding assignment specific mata to page object
		$vce->assignment_id = $each_component->component_id;
		$vce->assignment_type = $each_component->type;
		$vce->goal_achievement_evidence =  $each_component->goal_achievement_evidence;
		$vce->action_plan_goal = $each_component->action_plan_goal;

	
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

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');

		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','assignment-style');
		
		// goal input
		$input = array(
			'type' => 'textarea',
			'name' => 'goal',
			'required' => 'true',
			'value' => $each_component->action_plan_goal,
			'data' => array(
					'rows' => '3',
					'tag' => 'required',
			)
		);

		$goal_input = $vce->content->create_input($input,'What is your goal for this coaching cycle?');

		// goal achievement input
		$input = array(
			'type' => 'textarea',
			'name' => 'goal_achievement',
			'required' => 'true',
			'value' => $each_component->goal_achievement_evidence,
			'data' => array(
					'rows' => '3',
					'tag' => 'required',
			)
		);

		$goal_achievement_input = $vce->content->create_input($input,'How will you know when you have achieved this goal?');

		$tab_content = <<<EOF
$goal_input
$goal_achievement_input
EOF;

		$tab_input = array (
			'tabs__container1' => array(
				'tabs' => array(
					'tab1' => array(
						'id' => 'add-goal',
						'label' => 'Add Goal',
						'content' => $tab_content
					),
				),
			),
		);

		$tab_content = Pbc_utilities::create_tab($tab_input);

		$content = <<<EOF
$tab_content
EOF;

		
		// page content (remove; we are only editing now
// 		$vce->content->add('main', $content);
		
		$sub_roles = json_decode($vce->sub_roles, true);
		
		$user_access = json_decode($vce->user_access, true);
		
		// check that sub_role is student
		if ($vce->user->sub_role == "3") {
		
			// test that the assignment open_date is in the past
			if (strtotime($each_component->open_date) > time()) {
				$vce->content->add('main', '<div class="clickbar-container"><div class="clickbar-title disabled clickbar-closed"><span>This assignment is not open yet.</span></div></div>');
				// return false to stop build_content in page class from building sub components
				return false;
			}
			// end open_date test

			// check to see if a StudentSubmissions component has been created already.
			if (isset($each_component->components)) {
				$component_exists = false;
				foreach ($each_component->components as $each_sub_component) {
		
					if ($each_sub_component->type == "StudentSubmissions" && $each_sub_component->created_by == $vce->user->user_id) {

						$component_exists = true;
					}
		
				}

				// create StudentSubmissions component
				if (!$component_exists) {
		
					$input['parent_id'] = $each_component->component_id;
					$input['type'] = "StudentSubmissions";
					$input['title'] = $each_component->title;
					$input['sequence'] = "101";
			
					$this->create_component($input);
					
					// reload page					
					header('location: ' . $vce->site->site_url . '/'. $vce->requested_url);

				}
			}
		
		}

	}

	/**
	 * close div for tab
	 */
	public function as_content_finish($each_component, $vce) {	
// 		$content = <<<EOF
// 		EOF;
// 
// 	
// 		$vce->content->add('postmain',$content);
	
	}

	
	/**
	 *
	 */
	public function edit_component($each_component, $vce) {
		// $vce->dump('in goal');
		// $vce->content->add('edit_goal','<div>yep</div>');
		// return;
		if ($vce->page->can_edit($each_component) || $vce->user->sub_role == 1) {
		
			// prevent assignments from being edited
			if (isset($vce->lock_assignments) && $vce->user->role_id != 1) {
				return false;
			}

			$siblings = $vce->page->get_children($each_component->parent_id);

			//get related review id's
			$review_ids = '';
			foreach ($siblings as $each_sibling) {
				if ($each_sibling->type == "Pbc_review") {
					$review_component = $each_sibling;
					$review_ids .= '|'.$each_sibling->component_id;
				}
			}
			$review_ids = trim($review_ids, '|');
		
			// the instructions to pass through the form
			$parent_cycle = $vce->page->components[(count($vce->page->components) - 3)];
			$redirect_url = $vce->site->site_url . '/' . $parent_cycle->url . '?pbcsteps_view=ap_step';
			$dossier = array(
			'type' => $each_component->type,
			'procedure' => 'update',
			'component_id' => $each_component->component_id,
			'created_at' => $each_component->created_at,
			'redirect_url' => $redirect_url,
			);

			// generate dossier
			$dossier_for_update = $vce->generate_dossier($dossier);
		
			// get course component info
			$course = $vce->page->components[(count($vce->page->components) - 1)];
		
			$recipe = isset($each_component->recipe) ? (object) $each_component->recipe : null;
		
			// add javascript to page
			$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		
			// this is the current template, but the recipe will control this.
			$template = isset($vce->page->template) ? $vce->page->template : null;
			
			// for date picker
			$id_key = uniqid();

			$action_plan_goal = str_replace(array("\r", "\n"), '', $each_component->action_plan_goal);

			$goal_achievement_evidence = str_replace(array("\r", "\n"), '', $each_component->goal_achievement_evidence);
			
			// required if not admin
			$required = ($vce->user->role_id == 1) ? '' : 'tag="required"';
			
			






			$edit_tab_content = <<<EOF
<form id="update_$each_component->component_id" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
EOF;


		if ($template) {
			$content .= '<input type="hidden" name="template" value="' . $template . '">';
		}

		// goal input
		$input = array(
			'type' => 'textarea',
			'name' => 'action_plan_goal',
			'required' => 'true',
			'value' => $each_component->action_plan_goal,
			'data' => array(
					'rows' => '3',
					'tag' => 'required',
			)
		);

		$goal_input = $vce->content->create_input($input,'What is your goal for this coaching cycle?');

		// goal achievement input
		$input = array(
			'type' => 'textarea',
			'name' => 'goal_achievement_evidence',
			'required' => 'true',
			'value' => $each_component->goal_achievement_evidence,
			'data' => array(
					'rows' => '3',
					'tag' => 'required',
			)
		);

		$goal_achievement_input = $vce->content->create_input($input,'How will you know when you have achieved this goal?');

		$edit_tab_content .= <<<EOF
$goal_input
$goal_achievement_input
<button type="submit" class="button__primary" >Update</button>
</form>
EOF;

			
			

		// the instructions to pass through the form with specifics
		$dossier = array(
		'type' => 'Pbc_step',
		'procedure' => 'add_as_resource_requester_id',
		'url_of_resource_library' => $vce->site->site_url . '/resource_library',
		'component_id' => $each_component->component_id
		);

		// add dossier for requesting a resource
		$dossier_for_add_resource_requester_id = $vce->generate_dossier($dossier);


		// the instructions to pass through the form with specifics
		$dossier = array(
		'type' => 'Pbc_step',
		'procedure' => 'add_as_resource_requester_id',
		'url_of_resource_library' => $vce->site->site_url . '/usermedia',
		'component_id' => $each_component->component_id
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_for_add_resource_requester_id = $vce->generate_dossier($dossier);



$content = <<<EOF
$edit_tab_content
EOF;

			$vce->content->add('edit_goal',$content);

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

		if ($parents === null) {
			return FALSE;
		}

		// find the parent 2 levels back (Pbc_home_locations->Pbc_steps->Pbc_goal)
		// add one to to steps to subtract because the array is 0 based
		$index = count($parents) - 3;
		$parent_cycle_attributes = $parents[$index];
// $vce->log($parents[$index]);

		// get component configuration information from this component
		$component_name = get_class();
		$value = $vce->site->$component_name;
		$minutia = $component_name . '_minutia';
		$vector = $vce->site->$minutia;
		if (!empty($value) && !empty($vector)) {
			$component_config = json_decode($vce->site->decryption($value, $vector), true);
		} else {
			$component_config = NULL;
		}

		// get info about parent cycle
		$cycle_title = $parent_cycle_attributes->pbccycle_name;
		$cycle_url = $vce->site->site_url . '/' . $parent_cycle_attributes->url;

		// get included participants
		$user_ids_cycle_participants = json_decode(html_entity_decode($parent_cycle_attributes->user_ids_cycle_participants), TRUE);
		$cycle_participants = $user_ids_cycle_participants['user_ids'];
		$cycle_participants = explode('|', $cycle_participants);
		$cycle_participants = implode(',', $cycle_participants);


		// $vce->dump($cycle_participants);
		// exit;
		// need a way to know what options are available for pick-and-choose
		// use a subtractive approach to minimize what is stored in user_meta
		$announcements = array(
		'notify_on_create' => 'Notify me when someone has created a Goal for a Cycle in which I am included.'
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
						$event_array = array();
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
	

		//skip if already defined
		$titles = '';
		foreach ($vce->page->components as $each_component) {
		$titles .= $each_component->title;
			if ($each_component->title == $recipe_component->title) {
				return;
			}
		}

		//create specific url
		$specific_url = $vce->requested_url . '/' . time() . '-' . $vce->user->user_id;
		
		$recipe_component->dossier['assignment_category'] = $recipe_component->title;
		$recipe_component->dossier['title'] = $recipe_component->title;
		$recipe_component->dossier['list_order'] = 0;
		$recipe_component->dossier['url'] = $specific_url;
		// create dossier
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
		
		// for date picker
		$id_key = uniqid();

		// required if not admin
		$required = ($vce->user->role_id == 1) ? '' : 'tag="required"';
		

// 		
// 		global $site;
// 		$site->dump($specific_url);

$content = <<<EOF
<form id="create_items" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_create">
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

// $content .= <<<EOF
// 
// 
// <input class="check-url" type="hidden" name="url" value="" parent_url="$course->url/" dossier="$dossier_for_checkurl" tag="required" autocomplete="off">
// EOF;

		if (isset($recipe_component->template)) {
			$content .= '<input type="hidden" name="template" value="' . $recipe_component->template . '">';
		}

		// goal input
		$input = array(
			'type' => 'textarea',
			'name' => 'action_plan_goal',
			'required' => 'true',
			'value' => $each_component->action_plan_goal,
			'data' => array(
					'rows' => '3',
					'tag' => 'required',
			)
		);

		$goal_input = $vce->content->create_input($input,'What is your goal for this coaching cycle?');

		// goal achievement input
		$input = array(
			'type' => 'textarea',
			'name' => 'goal_achievement_evidence',
			'required' => 'true',
			'value' => $each_component->goal_achievement_evidence,
			'data' => array(
					'rows' => '3',
					'tag' => 'required',
			)
		);

		$goal_achievement_input = $vce->content->create_input($input,'How will you know when you have achieved this goal?');

		$content .= <<<EOF
$goal_input
$goal_achievement_input

<button type="submit" class="button__primary">Create</button>
</form>
EOF;


		$vce->content->add('create_goal',$content);

	}



        /**
         * Creates component
         * @param array $input
         * @return calls component's procedure or echos an error message
         */
        public function create($input) {

                global $vce;
            //  $vce->log($input);
// 				exit();

                // clean up url
                if (isset($input['url'])) {

//                         $site->log($input['url']);
                        $input['url'] = $vce->site->url_checker($input['url']);
//                         $site->log($input['url']);
                }

                // call to create_component, which returns the newly created component_id
                $component_id = $this->create_component($input);
                
                
			

                // // call to create_component with the new $next_input array
                // $next_component_id = self::create_component($next_input);

                if ($component_id) {

					$component_name = $this->component_info()['name'];

                    $vce->site->add_attributes('message', $component_name . ' Created');

                    echo json_encode(array('response' => 'success','procedure' => 'create','action' => 'reload','message' => 'Created','component_id' => $component_id));
                    return;

                }

                echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Error"));
                return;

        }	
        
   
   
   public function delete($input) {

		$parent_url = self::delete_component($input);
		global $site;
// 		$site->log($input);

		if (isset($parent_url)) {
		global $site;
		
			//delete review as well (a review is created automatically when a goal is created, so all reviews should be extirpated)
			if (isset($input['review_ids'])) {
				$review_ids =explode('|', $input['review_ids']);
				foreach($review_ids as $id){
					$input2 = array(
						'component_id' => $id,
						'created_at' => $input['created_at']
					);
// 									$site->log($input2);
						$review_deleted = self::delete_component($input2);
				}
			}
			
			//success, if both goal and review are deleted
			if (isset($review_deleted)) {
				global $site;
				$site->add_attributes('message',self::component_info()['name'] . " Deleted");

				echo json_encode(array('response' => 'success','procedure' => 'delete','action' => 'reload','url' => $parent_url, 'message' => "Deleted"));
				return;
			}
		}

		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Errors"));
		return;
	
	}
        

	/**
	 * Updates data
	 * @param array $input
	 * @return calls component's procedure or echos an error message
	 */
	public function update($input) {
	global $vce;

		// get redirect url
		if (isset($input['redirect_url'])) {
			$redirect_url = $input['redirect_url'];
			unset($input['redirect_url']);
		} else {
			$redirect_url = NULL;
		}
	
              // update sibling component (Review)
                // parent_id from $input[‘parent_id’]
                // type
                // title
                // $next_input = array();
				// $next_input['component_id'] = $input['review_sibling_id'];
				// $next_input['type'] = "Pbc_review";
				// $next_input['created_at'] = $input['created_at'];
				// $next_input['action_plan_goal'] = $input['action_plan_goal'];
				// $next_input['goal_achievement_evidence'] = $input['goal_achievement_evidence'];
				// self::update_component($next_input);
				
		if ($this->update_component($input)) {
		
			
			$vce->site->add_attributes('message', $this->component_info()['name'] . " Updated");
		
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
A Goal has been created for the Practice Based Coaching Cycle entitled "{cycle_title}" at this link:
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
A Goal has been created for the Practice Based Coaching Cycle entitled "{cycle_title}" at this link:
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



	
	/**
	 * 
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