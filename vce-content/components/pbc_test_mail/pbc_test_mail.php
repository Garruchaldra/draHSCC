<?php

class Pbc_test_mail extends Component {

	/**
	 * basic info about the component
	 * this component was based on the "Reflection_assignment" component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Test Mail',
			'description' => 'Step to achieve the Action Plan Step',
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

	public  function test_output() {
		return 'this is the test output';
	}


	/**
	 *
	 */
	public function as_content($each_component, $vce) {
		// return false;

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui select2');
		// add touch-punch jquery to page
		$vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js','jquery-touchpunch-ui');
		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','assignment-style');
		
		
		
		$vce->site->remove_attributes('as_resource_requester_id');



		$content = NULL;

		$vce->content->add('main',$content);


	}

	/**
	 * close div for tab
	 */
	public function as_content_finish($each_component, $vce) {
	
		$comments = str_replace(array("\r", "\n"), '', $each_component->comments);


$content = <<<EOF
EOF;
	
// 		$vce->content->add('postmain',$content);
	
	}

	
	/**
	 *
	 */
	public function edit_component($each_component, $vce) {
// return false;
// $vce->dump($each_component);

		$can_edit = (in_array($vce->user->user_id, explode('|', trim($each_component->aps_assignee, '|'))) ? true : false);
		// $vce->dump($can_edit);
		if ($can_edit || $each_component->created_by == $vce->user->user_id) {
			

			// the instructions to pass through the form
			$parent_cycle = $vce->page->components[(count($vce->page->components) - 3)];
			$redirect_url = $vce->site->site_url . '/' . $parent_cycle->url . '?pbcsteps_view=ap_step';
			$dossier = array(
			'type' => $each_component->type,
			'procedure' => 'update',
			'component_id' => $each_component->component_id,
			'created_at' => $each_component->created_at,
			'redirect_url' => $redirect_url
			);

			// generate dossier
			$dossier_for_update = $vce->generate_dossier($dossier);
		
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

			$comments = str_replace(array("\r", "\n"), '', $each_component->comments);
			
			// required if not admin
			$required = ($vce->user->role_id == 1) ? '' : 'tag="required"';
			
			//get cycle id
			$pbc_cycles_id = pbc_utilities::find_component_id($each_component->parent_id, "Pbccycles");



$edit_tab_content = <<<EOF
<form id="update_$each_component->component_id" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
<input type="hidden" name="pbc_cycles_id" value="$pbc_cycles_id">
EOF;






				// add action plan step inputs
				$input = array(
					'type' => 'textarea',
					'name' => 'title',
					'value' => $each_component->title,
					'data' => array(
							'rows' => '2',
							'tag' => 'required',
					)
				);
		
				$ap_step_name_input = $vce->content->create_input($input,'Action Plan Step');
		
				$input = array(
					'type' => 'text',
					'name' => 'start_date',
					'class' => 'datepicker',
					'value' => $each_component->start_date,
					'data' => array (
						'autocapitalize' => 'none',
						'tag' => 'required',
					)
				);
		
				$ap_step_start_date_input = $vce->content->create_input($input,'Start Date');

			//start drag and drop
				//select users in same group
				$filter_by = array(
					'group' => $vce->user->group
				);
				$users_in_same_group = pbc_utilities::filter_users($filter_by, $vce);
			
				$dd_array = array(
				'type' => 'select',
				'required' => 'true',
				'dl' => 'aps_assignee', 
				'dl_name' => 'Action Plan Step Assignee', 
				'dl_id' => '', 
				'selected_users' => $each_component->aps_assignee,
 				'component_id' => $each_component->component_id, 
				'component_method' => __FUNCTION__,
				'get_user_array' => array('user_ids' => $users_in_same_group)
				);
			
				$input = pbc_utilities::datalist_add_multiple_select($dd_array, $vce);
				$ap_multiple_member_input = $vce->content->create_input($input,'Step assigned to');

		
			// comments input
			$input = array(
				'type' => 'textarea',
				'name' => 'step_comments',
				'value' => $each_component->comments,
				'data' => array(
					'rows' => '3',
				)
			);
		
			$ap_step_comments_input = $vce->content->create_input($input,'Notes (optional)');



				// $parent_object->components[$k]->add_component();
				$edit_tab_content .= <<<EOF
$ap_step_name_input
$ap_step_start_date_input
$ap_multiple_member_input
$ap_step_comments_input
<button type="submit" class="button__primary">Update</button>
</form>
EOF;





		if ($template) {
			$edit_tab_content .= '<input type="hidden" name="template" value="' . $template . '">';
		}

		
		$vce->site->remove_attributes('as_resource_requester_id');


			if ($vce->page->can_delete($each_component)) {

				// the instructions to pass through the form
				$dossier = array(
				'type' => $each_component->type,
				'procedure' => 'delete',
				'component_id' => $each_component->component_id,
				'created_at' => $each_component->created_at,
				'parent_url' => $vce->pbccycle_url
				);
// $vce->dump($vce);
				// generate dossier
				$dossier_for_delete = $vce->generate_dossier($dossier);
	$can_edit = ($each_component->created_by == $vce->user->user_id ? true : false);
	if ($can_edit) {	
		$edit_tab_content .= <<<EOF
		<form id="delete_$each_component->component_id" class="delete-form float-right-form asynchronous-form" method="post" action="$vce->input_path">
		<input type="hidden" name="dossier" value="$dossier_for_delete">
		<button type="submit" class="button__primary" >Delete</button>
		</form>
EOF;
				}

			}


$content = <<<EOF
$edit_tab_content
EOF;

			$vce->content->add('edit_pbcstep',$content);

		}

	}




	/**
	 *
	 */
	public function add_component($recipe_component, $vce) {
		// $vce->dump('add step');
		// return false;
		
		$pbc_cycles_id = pbc_utilities::find_component_id($recipe_component->parent_id, "Pbccycles");
		// $vce->dump($pbc_cycles_id);
		// $vce->dump($vce->page);
		
		
// 		$recipe = (object) $recipe_component->recipe;
		// create dossier
		$dossier_for_create = $vce->generate_dossier($recipe_component->dossier);
		
		// create dossier for checkurl functionality
		$dossier = array(
		'type' => $recipe_component->type,
		'procedure' => 'checkurl'
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_for_checkurl = $vce->generate_dossier($dossier);

		// get cycle component info
		$cycle = $vce->page->components[(count($vce->page->components) - 1)];
		// $vce->dump($cycle );
		
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		
		// add touch-punch jquery to page
		$vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js','jquery-touchpunch-ui');

		
		// for date picker
		$id_key = uniqid();
		
		$url = 'apstep'.time();

		// required if not admin
		$required = ($vce->user->role_id == 1) ? '' : 'tag="required"';

$content = <<<EOF
<form id="action_plan_step_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_create">
<input type="hidden" name="pbc_cycles_id" value="$pbc_cycles_id">
EOF;






				// add action plan step inputs
				$input = array(
					'type' => 'textarea',
					'name' => 'title',
					'data' => array(
							'rows' => '2',
							'tag' => 'required',
					)
				);
		
				$ap_step_name_input = $vce->content->create_input($input,'Action Plan Step');
		
				$input = array(
					'type' => 'text',
					'name' => 'start_date',
					'class' => 'datepicker',
					'data' => array (
						'autocapitalize' => 'none',
						'tag' => 'required',
					)
				);
		
				$ap_step_start_date_input = $vce->content->create_input($input,'Start Date');


				//select users in same group
				$filter_by = array(
					'group' => $vce->user->group
				);
			
				$users_in_same_group = pbc_utilities::filter_users($filter_by, $vce);
				//start drag and drop			
				$dd_array = array(
				'type' => 'select',
				'required' => 'true',
				'dl' => 'aps_assignee', 
				'dl_name' => 'Action Plan Step Assignee', 
				'dl_id' => NULL, 
				'selected_users' => trim($cycle->cycle_participants, '|'),
				'component_id' => null, 
				'component_method' => __FUNCTION__,
				'get_user_array' => array('user_ids' => $users_in_same_group)
				);
			
				$input = pbc_utilities::datalist_add_multiple_select($dd_array, $vce);
				$ap_multiple_member_input = $vce->content->create_input($input,'Step assigned to');

		
				$input = array(
					'type' => 'textarea',
					'name' => 'comments',
					'data' => array(
						'rows' => '3',
					)
				);
		
				$ap_step_comments_input = $vce->content->create_input($input,'Notes (optional)');

				// $parent_object->components[$k]->add_component();
				$content .= <<<EOF
$ap_step_name_input
$ap_step_start_date_input
$ap_multiple_member_input
$ap_step_comments_input
<div>Note: To add media resources to this Action Plan Step, please create the step first.</div>
<br>
<input class="button__primary" type="submit" value="Create Step" >
EOF;






		$content .= <<<EOF
<input class="check-url" type="hidden" name="url" value="$url" parent_url="$cycle->url/" dossier="$dossier_for_checkurl" tag="required" autocomplete="off">
EOF;


		if (isset($recipe_component->template)) {
			$content .= '<input type="hidden" name="template" value="' . $recipe_component->template . '">';
		}

		$content .= <<<EOF
</form>
EOF;

		$vce->content->add('create_pbcstep', $content);
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

				// check if for filtering and skip any user which doesn't pass the filter
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
							if ($users[$each_user]['role_id'] != 5) {
// 							$vce->site->dump($users[$each_user]);
							}
							if ($role_name[$users[$each_user]['role_id']] < $roles[$vce->user->role_id]['role_hierarchy']) {
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





	
	

	public function create_alias($input) {

		global $vce;
		$vce->log($input);
		$input['type'] = 'Alias';
	
		// call to create_component, which returns the newly created component_id
		$component_id = self::create_component($input);
	
		if ($component_id) {
		
			// find the component_id for component type of Pbc_step 
			$query = "SELECT * FROM " . TABLE_PREFIX . "components WHERE component_id IN (SELECT parent_id FROM " . TABLE_PREFIX . "components WHERE component_id='" . $component_id . "')";
			$parent_component = $vce->db->get_data_object($query);
	
			// $url = $vce->site->site_url . '/' . current($parent_component)->url;
			$url = $input['redirect_url'];
		
			$vce->site->add_attributes('message','Resource Associated with Action Plan Step');
			
			$vce->site->add_attributes('observation_id',$component_id);
			
	
			echo json_encode(array('response' => 'success','procedure' => 'create','action' => 'reload','url' => $url, 'message' => 'Created','component_id' => $component_id));
			return;
		
		}
		
		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Error"));
		return;

	
	}
	
	
	


	
	/**
	 * Creates component
	 * this is customized from the parent method to create datalists
	 * @param array $input
	 * @return calls component's procedure or echos an error message
	 */
	public function create($input) {
		
		global $vce;
		
		

		// clean up url
		if (!isset($input['url']) || $input['url'] == '') {
			$input['url'] = 'apstep'.time();
		}
		if (isset($input['url'])) {
			$input['url'] = $vce->site->url_checker($input['url']);
		}

		//save multi-select userlist to component	
		foreach($input as $key => $value) {
			if (strpos($key, 'user_ids') !== false) {
				$dl_input = json_decode(html_entity_decode($value));				
				$dl_user_ids = trim($dl_input->user_ids, '|');
				$input[$dl_input->dl] = '|' . $dl_user_ids . '|';
			}
		}

	// call to create_component, which returns the newly created component_id
		$component_id = self::create_component($input);
	//put newly created component id into input array
		$input['component_id'] = $component_id;




//update user_access list:

	// add users from drag and drop to user_access array of a cycle
	// (Makes an array of users from any and all drag-and-drops on page, gets array from 
	// cycle component, combines and stores them)	
	
	// get all user id's from drag-and-drops in form
		foreach($input as $key => $value) {
			if (strpos($key, 'user_ids') !== false) {
				$dl_input = json_decode(html_entity_decode($value));
				$user_ids = $dl_input->user_ids;
			}
		}
		
	//get the user_ids_aps_assignee and convert to an array
		$drag_and_drop_datalist = $user_ids;
		
	//explode into array
		$user_ids = explode("|", $user_ids);
		$user_access_array = array();
		foreach($user_ids as $id) {
			$user_access_array[$id] = array("sub_role" => 2);
		}
	//get existing user-access list from the cycle
		$query = 'SELECT meta_value FROM ' . TABLE_PREFIX . 'components_meta WHERE component_id =' . $input['pbc_cycles_id'] . ' AND meta_key = "user_access"' ;
		$result = $vce->db->get_data_object($query);
		$existing_users = json_decode($result[0]->meta_value, true);
// 		$vce->site->log(json_decode($result[0]->meta_value));
		
	//add existing users to new user array
	if (isset($existing_users)) {
		foreach ($existing_users as $key => $value) {
			$user_access_array[$key] = $value;
		}
	}


	//convert array to json encoded datastructure
		$user_access_array = $vce->db->mysqli_escape(json_encode($user_access_array));
		
	//add selected id's to cycle's user_access
		$query = 'UPDATE ' . TABLE_PREFIX . 'components_meta SET meta_value = "' . $user_access_array . '" WHERE component_id =' . $input['pbc_cycles_id'] . ' AND meta_key = "user_access"' ;
		$result = $vce->db->query($query);
	



	
	//if the component has been created and the related datalist has been created, send out emails to participants and return success/redirect to component
	if ($component_id && $dl_user_ids) {   
					
			$vce->site->add_attributes('message',self::component_info()['name'] . ' Created');
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
// 		$vce->site->log($user_list);

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
Your OHS Coaching Companion user account has been included as a participant in an Action Plan from the coaching cycle "$cycle_title" at this link:<br>
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

// $vce->site->log($email_message);

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
			
	
			
			echo json_encode(array('response' => 'success','procedure' => 'create','action' => 'reload','url' => $cycle_url, 'message' => 'Create','component_id' => $component_id));
			return;
		
		}
		
		
		
	
		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Error"));
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
		// get redirect url
		if (isset($input['redirect_url'])) {
			$redirect_url = $input['redirect_url'];
			unset($input['redirect_url']);
		} else {
			$redirect_url = NULL;
		}
		//save multi-select userlist to component	
		foreach($input as $key => $value) {
			if (strpos($key, 'user_ids') !== false) {
				$dl_input = json_decode(html_entity_decode($value));				
				$dl_user_ids = trim($dl_input->user_ids, '|');
				$input[$dl_input->dl] = '|' . $dl_user_ids . '|';
			}
		}

		if (isset($input["form_location"]) && $input["form_location"] == 'as_content_finish') {
			if (!isset($input['pbccycle_status']) || $input['pbccycle_status'] != 'Complete') {
				$input['pbccycle_status'] = 'in_progress';
			}
			unset($input["form_location"]);
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
// 		$result = $vce->db->get_data_object($query);
// 		$existing_users = json_decode($result[0]->meta_value, true);
// 		$vce->site->log(json_decode($result[0]->meta_value));

// exit;		
		
		

			
			$vce->site->add_attributes('message',self::component_info()['name'] . " Updated");
		
			echo json_encode(array('response' => 'success','procedure' => 'update', 'url' => $redirect_url, 'action' => 'reload','message' => "Updated"));
			return;
		}
		
		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Permission Error"));
		return;
	
	}
	

}