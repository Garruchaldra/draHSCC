<?php

class Pbccycles extends Component {


	/**
	 * basic info about the component
	 * This component is based on "Courses" and retains much of its functionality
	 */
	public function component_info() {
		return array(
			'name' => 'PBCCycles',
			'description' => 'Practice Based Coaching Cycles',
			'category' => 'pbc',
			'recipe_fields' => array('title','template','role_access','content_access','content_create','content_edit','content_delete')
		);
	}


	/**
	 * component has been installed, now do the following
	 */
	public function activated() {
	
		global $site;

		// json object with meta_data and sub items
		$items = '[{"name":"OHS","items":[{"name":"PBC"}]}]';

		$attributes = array (
		'datalist' => 'organizations_datalist',
		'aspects' => array('type' => 'select'),
		'hierarchy' => array('organization','group'),
		'items' => json_decode($items, true)
		);
		
		$vce->create_datalist($attributes);
		
	}
	
	
	/**
	 * component has been removed, as in deleted
	 */
	public function removed() {
	
		global $site;
		
		$attributes = array (
		'datalist' => 'organizations_datalist'
		);
		
		$vce->remove_datalist($attributes);
		
		$meta_data = array('organization','group');
		
		foreach ($meta_data as $each_data) {
		
			// delete user from database
			$where = array('meta_key' => $each_data);
			$vce->db->delete('users_meta', $where);
		
		}
	
	}

	
	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
	
		$content_hook = array (

		);

		return $content_hook;

	}



	
	
	
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
				$users[$each_meta_data->user_id][$each_meta_data->meta_key] = $vce->user->decryption($each_meta_data->meta_value,$vectors[$each_meta_data->user_id]);
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
			
			$users_filtered_array = array_unique($users_filtered_array);
		
			//return pipeline delineated list of filtered users
			return implode('|', $users_filtered_array);
	}





	

	


	
	/**	
	 * This is a utility which creates an array of groups and the users which belong to them.
     * Then that list is concatenated into a delineated list of users per group and added to
     * datalists_items_meta
	 * 
	 * It is called when a user is created, updated or deleted
	 */
	public  function create_user_list_for_one_group($input) {	

// 			return;
			$user_id = isset($input['user_id']) ? $input['user_id'] : null;
			if (is_null($user_id)) {
				return;
			}
			// get old group list, remove user 
			//get userdata to know what group they are in currently (using semantics from manage_users

			// initialize array to store users
			$site_users = array();

			$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "'";

			$all_users = $vce->db->get_data_object($query);

			foreach ($all_users as $each_user) {

					// create array
					$user_object = array();

					// add the values into the user object
					$user_object['user_id'] = $each_user->user_id;
					$user_object['role_id'] = $each_user->role_id;

					$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id='" . $each_user->user_id . "'  AND minutia=''";
					$metadata = $vce->db->get_data_object($query);

					// look through metadata
					foreach ($metadata as $each_metadata) {

							//decrypt the values
							$value = $vce->user->decryption($each_metadata->meta_value, $each_user->vector);

							// add the values into the user object
							$user_object[$each_metadata->meta_key] = $vce->db->clean($value);
					}

					// save into site_users array
					$site_users[$each_user->user_id] = (object) $user_object;
			}
                        
			$user_info = $site_users[$user_id];
			$old_group = isset($user_info->group) ? $user_info->group : null;
// 			$vce->log('old group: '.$old_group);

			
			if (!is_null($old_group)) {
				$query = "SELECT item_id, meta_value FROM  " . TABLE_PREFIX . "datalists_items_meta WHERE item_id = $old_group  AND meta_key = 'user_list'";
				$result = $vce->db->get_data_object($query);
				foreach ($result as $user_list) {
					$user_list = $user_list->meta_value;
				}
				
				if (isset($user_list)) {
					$user_list_array = explode('|', $user_list);
					if (($key = array_search($user_id, $user_list_array)) !== false) {
						 unset($user_list_array[$key]);
						 $user_list = implode('|', $user_list_array);
						$query = "UPDATE " . TABLE_PREFIX . "datalists_items_meta SET meta_value =  '".$user_list."' WHERE item_id = $old_group AND meta_key = 'user_list'";
						$vce->db->query($query);	
				
					}
				}
			
				if (isset($user_list)) {
					unset($user_list);
				}
			}

			
		//add id to group list, if it is present in the input	
			$new_group = isset($input['group']) ? $input['group'] : null;
		

			if (!is_null($new_group)) {
// 				$vce->log('new group: '.$new_group);
				// update new group list
				
				//if this group still does not have a user_list, create one
				$query = "SELECT item_id, meta_value FROM  " . TABLE_PREFIX . "datalists_items_meta WHERE item_id = $new_group AND meta_key = 'user_list'";
				foreach ($vce->db->get_data_object($query) as $user_list) {
					$user_list = $user_list->meta_value;
				}

				if (!isset($user_list)) {
					$user_list = $user_id;
					$query = "INSERT INTO " . TABLE_PREFIX . "datalists_items_meta (item_id, meta_key, meta_value) VALUES ($new_group, 'user_list', '".$user_list."') ";
					$vce->db->query($query);
					return;
				}
			

				if (isset($user_list) && $user_list != '') {
					$user_list_array = explode('|', $user_list);
					if (($key = array_search($user_id, $user_list_array)) == false) {
						$user_list_array[] = $user_id;
						$user_list = implode('|', $user_list_array);

						$query = "UPDATE " . TABLE_PREFIX . "datalists_items_meta SET meta_value =  '".$user_list."' WHERE item_id = $new_group AND meta_key = 'user_list'";
						$vce->db->query($query);
					}
				}
			}
		}		



	
	/**	
	 * This is a utility which creates an array of groups and the users which belong to them.
     * Then that list is concatenated into a delineated list of users per group and added to
     * datalists_items_meta
	 * 
	 */
	public function create_user_lists_per_group() {	

			// initialize array to store users
			$site_users = array();

			$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users";

			$all_users = $vce->db->get_data_object($query);
			
			$all_users_total = $all_users;
		
			$user_organization_group = array();

			foreach ($all_users as $each_user) {
		
				// create array
				$user_object = array();
		
				// add the values into the user object	
				$user_object['user_id'] = $each_user->user_id;
				$user_object['role_id'] = $each_user->role_id;
			
				$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id='" . $each_user->user_id . "'  AND minutia=''";
				$metadata = $vce->db->get_data_object($query);
			
				// look through metadata
				foreach ($metadata as $each_metadata) {

					//decrypt the values
					$value = $vce->user->decryption($each_metadata->meta_value, $each_user->vector);

					// add the values into the user object	
					$user_object[$each_metadata->meta_key] = $vce->db->clean($value);		
				}
				//Create array of organization, group, user_id
				if (!array_key_exists($user_object['organization'], $user_organization_group)) {
					$user_organization_group[$user_object['organization']] = array();
				}

				if (!array_key_exists($user_object['group'], $user_organization_group[$user_object['organization']])) {
					$user_organization_group[$user_object['organization']][$user_object['group']] = array('user_list' => '');
				}
				if (!array_key_exists($user_object['user_id'], $user_organization_group[$user_object['organization']][$user_object['group']])) {
// 					$user_organization_group[$user_object['organization']][$user_object['group']][] = $user_object['user_id'];
					if ($user_organization_group[$user_object['organization']][$user_object['group']]['user_list'] != '') {
						$delimiter = '|';
					} else {
						$delimiter = '';
					}
					$user_organization_group[$user_object['organization']][$user_object['group']]['user_list'] .= $delimiter.$user_object['user_id'];
				}


			}
	
	
	
			


	/* This process loops through the $user_organization_group variable and gets the ids of the datalists associated to the groups */
	foreach ($user_organization_group as $orgkey => $orgvalue) {
		
			$organization = $orgkey;
			$attributes = array(
			'name' => 'organization'
			);
			$options = $vce->get_datalist_items($attributes);
			$datalist_id = $options['datalist_id'];
		
			if (isset($options['items'])) {
				foreach ($options['items'] as $each_option) {
					if ($each_option['item_id'] == $organization) {
						$orgname = $each_option['name'];
					}
				}
			}
		
		foreach ($user_organization_group[$orgkey] as $groupkey => $groupvalue) {
	
			$group = $groupkey;

			$attributes = array(
			'parent_id' => $datalist_id,
			'item_id' => $organization
			);
		
			$options = $vce->get_datalist_items($attributes);

			if (isset($options['items'])) {
				foreach ($options['items'] as $each_option) {
					if ($each_option['item_id'] == $group) {
						//this is where the group has been related to its user_id list
						$user_list = $groupvalue['user_list'];
						
						
						//check to see if list exists or needs to be created
						$query = "SELECT item_id FROM  " . TABLE_PREFIX . "datalists_items_meta WHERE item_id = $group";
						foreach ($vce->db->get_data_object($query) as $group_id) {
							$user_list_id = $group_id->item_id;
						}

						
						if (isset($user_list_id) && $user_list_id > 0) {
							$query = "UPDATE " . TABLE_PREFIX . "datalists_items_meta SET meta_value =  '".$user_list."' WHERE item_id = $user_list_id";
							$all_users = $vce->db->query($query);		
						} else {
							$query = "INSERT INTO " . TABLE_PREFIX . "datalists_items_meta (item_id, meta_key, meta_value) VALUES ($group, 'user_list', '".$user_list."') ";
							$all_users = $vce->db->query($query);
						}
// 						$vce->dump($group.' '.$each_option['name'].' '.$orgkey.' '.$orgname.' '.$groupvalue['user_list']);
					}
				}
			}
		}
	}	
}		
		

	

	/**
	 * sort pbccycles by pbccycle_begins
	 */
	public static function sort_sub_components($requested_components,$sub_components,$vce) {

		if ($requested_components[0]->type == "Pbccycles") {
		
			$meta_key = 'pbccycle_begins';
			$order = 'asc';
	
 			usort($requested_components, function($a, $b) use ($meta_key, $order) {
				if (isset($a->$meta_key) && isset($b->$meta_key)) {
 					if ($order == "desc") {
 						return strtotime($a->$meta_key) > strtotime($b->$meta_key) ? -1 : 1;
 					} else {
 						return strtotime($a->$meta_key) < strtotime($b->$meta_key) ? 1 : -1;
 					}
 				} else {
 					return 1;
 				}
 			});
		}		

		return $requested_components;
		
	}


	/**
	 * This is a ghostly apparation of a sub recipe item.
	 */
	public static function recipe_item_manifestation($each_recipe_component, $vce) {
		$vce->content->add('main','<h1>PBC Cycles</h1>');
		
		// check to see if there should be a no pbccycles message
		if (isset($vce->components[(count($vce->components)-1)]->components)) {
			$pbccycle_count = null;
			foreach ($vce->components[(count($vce->components)-1)]->components as $key=>$pbccycle) {
				if (isset($pbccycle->user_access)) {
					$user_access = json_decode($pbccycle->user_access, true);
					if (isset($user_access[$vce->user->user_id]) || $pbccycle->created_by == $vce->user->user_id || $vce->user->role_id == "1") {
						$pbccycle_count = 1;
						break;
					}
				}
			}
			// display no pbccycles message
			if (!isset($pbccycle_count)) {
				$vce->content->add('main','<p>No PBC Cycles Found</p>');
			}
		}
		
	}




	//as_link

	//generate_link_container
	
	
	/**
	 *
	 */
	public function check_access($each_component, $vce) {

	 	// $vce->dump(json_decode($each_component->user_access, true));
// $vce->dump($vce->assignees);
		$users_in_jurisdiction = explode(',', $vce->users_in_jurisdiction);
		$component_id = $each_component->component_id;
		$user_id = $vce->user->user_id;

		if (isset($vce->user->user_id)) {		
			if (in_array($each_component->created_by, $users_in_jurisdiction) || $each_component->created_by == $vce->user->user_id || $vce->user->role_id == "1" || in_array($user_id, $vce->assignees[$component_id])) {
					
				// $vce->sub_roles = $each_component->sub_roles;
				// $vce->user_access = $each_component->user_access;
				// $vce->instructor_id = $each_component->created_by;
				$vce->pbccycle_name = $each_component->title;
				$vce->pbccycle_id = $each_component->component_id;
				$vce->pbccycle_url = $vce->site->site_url . '/' . $each_component->url;
								
				return true;
			}
		}
		
		return false;
	
	}




	/**
	 *
	 */
	public function as_content($each_component, $vce) {
		// a test:
		// $_SESSION['user']->user_id = 2222;
		// $vce->dump($_SESSION);
// 	global $site;
// return false;
// add current user sub_role
// $vce->dump('pbc cycles');
// $vce->dump($each_component->configuration);
// if(isset($vce->send_email_notification_on_cycle_creation)) {
	// $vce->dump('send_email_notification_on_cycle_creation');
// }

// $vce->dump($each_component->configuration);
// foreach ($each_component->configuration as $k=>$v) {
	// $vce->site->add_attributes('testing', 'yep', TRUE);
	// $vce->dump($k . ' ' . $v);
// }

		// $user_access = json_decode($each_component->user_access, true);
		
		// if ($each_component->created_by == $vce->user->user_id) {
		// 	$vce->user->sub_role = '1';
		// }
		
		
		// if (!empty($each_component->user_access) && array_key_exists($vce->user->user_id, json_decode($each_component->user_access, true)) || $each_component->created_by == $vce->user->user_id || $vce->user->role_id == "1") {
		

		// } else {
		
		// 	header('location: ' . $vce->site->site_url);
		
		// }
		
	}
	
	
	/**
	 *
	 */
	public function edit_component($each_component, $vce) {
// return false;
// $vce->dump($each_component);
		$pbc_home_location = new Pbc_home_location;

		$cycle_link_info = $pbc_home_location->generate_cycle_info($each_component, $vce);
		$vce->content->add('cycle_link_info', $cycle_link_info);
	
	
		$can_edit = $vce->page->can_edit($each_component);
// $can_edit = true;
		if ($can_edit) {
		
			// the instructions to pass through the form
			$parent_cycle = $vce->page->components[(count($vce->page->components) - 2)];
			$redirect_url = $vce->site->site_url . '/' . $parent_cycle->url;
			$dossier = array(
				'type' => $each_component->type,
				'procedure' => 'update',
				'component_id' => $each_component->component_id,
				'created_at' => $each_component->created_at,
				'redirect_url' => $redirect_url
			);

			// generate dossier
			$dossier_for_update = $vce->generate_dossier($dossier);
			
			$recipe = isset($each_component->recipe) ? (object) $each_component->recipe : null;
		
			// add javascript to page
			$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui select2');
			
			// add stylesheet to page
			$vce->site->add_style(dirname(__FILE__) . '/css/style.css','pbccycles-style');

			$pbccycle_url = $vce->site->site_url . '/' . $each_component->url;
						

			
			// and nice pbccycle date
			$pbccycle_date = date('F jS, Y', strtotime($each_component->pbccycle_begins));

			$user = $vce->user;

			$user_info =  $vce->user->get_users(array('user_ids' => $each_component->originator_id));

			$originator_name = $user_info[0]->first_name . ' ' . $user_info[0]->last_name;

			

// remove link container from pbcsteps
$content = '';

$user = $vce->user;

// add javascript to page
$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui select2');


$user_organization = $vce->user->organization;
$user_group = $vce->user->group;
$user_id = $vce->user->user_id;
$originator_id = $each_component->originator_id;



	$new_cycle_content = <<<EOF
	<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
	<input type="hidden" name="dossier" value="$dossier_for_update">
	<input type="hidden" name="organization" value="$user_organization">
	<input type="hidden" name="group" value="$user_group">
	<input type="hidden" name="originator_id" value="$originator_id">

EOF;

		// cycle name input
		$input = array(
				'type' => 'text',
				'name' => 'pbccycle_name',
				'value' => $each_component->pbccycle_name,
				'data' => array(
						'autocapitalize' => 'none',
						'tag' => 'required',
				)
		);
		
		$cycleName_input = $vce->content->create_input($input,'Cycle Name');

		// cycle begin date input
		$input = array(
			'type' => 'text',
			'name' => 'pbccycle_begins',
			'class' => 'datepicker',
			'value' => $each_component->pbccycle_begins,
			'data' => array(
					'autocapitalize' => 'none',
					'tag' => 'required',
			)
		);
		
		$cycleStartDate_input = $vce->content->create_input($input,'Cycle Start Date');



		$filter_by = array(
			'group' => $vce->user->group
		);
		$users_in_same_group = pbc_utilities::filter_users($filter_by, $vce);
		//start multiple select			
		$dd_array = array(
		'type' => 'select',
		'required' => 'true',
		'dl' => 'cycle_participants', 
		'dl_name' => 'Cycle Participants', 
		'dl_id' => '', 
		'selected_users' => $each_component->cycle_participants,
		'component_method' => __FUNCTION__,
		'get_user_array' => array('user_ids' => $users_in_same_group),
		'data' => array(
			'tag' => 'required',
		)
		);
	
		$input = pbc_utilities::datalist_add_multiple_select($dd_array, $vce);
		$multiple_member_input = $vce->content->create_input($input,'Cycle Participants');




		$new_cycle_content .= <<<EOF
				$cycleName_input
				$cycleStartDate_input
				$multiple_member_input
EOF;

		$content_tab2 = <<<EOF
		<div class="form-container"
		<p>
EOF;

		$content_tab2 .= <<<EOF
		$new_cycle_content
EOF;


		$content_tab2 .= <<<EOF
<br>
<input type="submit" value="Update Cycle" class="button__primary">
</form>
EOF;

if ($vce->page->can_delete($each_component) || $vce->user->sub_role == 1) {

	// the instructions to pass through the form
	$dossier = array(
	'type' => $each_component->type,
	'procedure' => 'delete',
	'component_id' => $each_component->component_id,
	'created_at' => $each_component->created_at,
	'parent_url' => $vce->requested_url
	);

	// generate dossier
	$dossier_for_delete = $vce->generate_dossier($dossier);

	$content_tab2.= <<<EOF
<form id="delete_$each_component->component_id" class="delete-form float-right-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<button type="submit" class="button__primary" >Delete</button>
</form>
EOF;

}

		$content .= <<<EOF
		$content_tab2
EOF;





			// $vce->dump('edit_cycle');
			$vce->content->add('edit_cycle', $content);
		
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

		$class = get_class($this);

		if (isset($vce->site->$class)) {
			$value = $vce->site->$class;
			$class_minutia = $class . '_minutia';
			$vector = $vce->site->$class_minutia;
			$component_config = json_decode($vce->site->decryption($value,$vector), true);
		}

		// get component configuration information from this component
		$component_name = get_class();
		$value = $vce->site->$component_name;
		$minutia = $component_name . '_minutia';
		$vector = $vce->site->$minutia;
		// $component_config = json_decode($vce->site->decryption($value, $vector), true);
		// get form input for this new cycle by looking at the page class
		$post_vars = json_decode($vce->page->post_variables, TRUE);
		// $vce->dump($post_vars);
		$cycle_title = (isset($post_vars['pbccycle_name'])) ? $post_vars['pbccycle_name'] : NULL;
		$pbccycle_begins = (isset($post_vars['pbccycle_begins'])) ? $post_vars['pbccycle_begins'] : NULL;
		// get component URL
		$query = "SELECT a.url FROM " . TABLE_PREFIX . "components AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id WHERE b.meta_key='pbccycle_name' AND b.meta_value='$cycle_title' AND c.meta_key='pbccycle_begins' AND c.meta_value='$pbccycle_begins'";
		$data = $vce->db->get_data_object($query);
		foreach($data as $this_data) {
			$cycle_url = $vce->site->site_url . '/' . $this_data->url;
		}
		// get included participants
		$post_vars['user_ids_cycle_participants'] = (isset($post_vars['user_ids_cycle_participants'])) ? $post_vars['user_ids_cycle_participants'] : '';
		$user_ids_cycle_participants = json_decode(html_entity_decode($post_vars['user_ids_cycle_participants']), TRUE);
		
		$cycle_participants = NULL;
		if (isset($user_ids_cycle_participants['user_ids'])) {
			$cycle_participants = $user_ids_cycle_participants['user_ids'];
			$cycle_participants = explode('|', $cycle_participants);
			$cycle_participants = implode(',', $cycle_participants);
		}


		// $vce->dump($cycle_url);
		// exit;
		// need a way to know what options are available for pick-and-choose
		// use a subtractive approach to minimize what is stored in user_meta
		$announcements = array(
		'notify_on_create' => 'Notify me when someone has created a Cycle in which I am included.'
		);
		
		if (!$parents || !is_array($parents)) {
			// this may be where the notification in my account is
			if ((isset($component_config['create_email_notifier_toggle']) && $component_config['create_email_notifier_toggle'] == 'on')||(isset($component_config['create_site_notifier_toggle']) && $component_config['create_site_notifier_toggle'] == 'on')) {
				return $announcements;
			}
		}

		
		// is the parent of this event correct?
		if ($parents[count($parents) - 2]->type != 'Pbc_home_location') {
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
			// if (isset($component_config['create_email_notifier_toggle']) && $component_config['create_email_notifier_toggle'] == 'on') {
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
				$vce->log($proclamation);
				Notifier::components_notify_method($proclamation);
				return TRUE;
				

		} else {
			// don't send a notification
			return FALSE;
		}
	}

						$event_array = (isset($event_array)) ? $event_array : array();
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
	 * sub_roles = {"1":"Instructor","2":"Teaching Assistant","3":"Student"}
	 */
	public function add_component($recipe_component, $vce) {

		// create dossier
		$recipe_component->dossier['user_organization'] = $vce->user->organization;
		$recipe_component->dossier['user_group'] = $vce->user->group;
		$recipe_component->dossier['originator_id'] = $vce->user->user_id;
		$dossier_for_create = $vce->generate_dossier($recipe_component->dossier);
// $vce->dump($recipe_component->dossier);
	
		$user = $vce->user;

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		
		// // add touch-punch jquery to page
		// $vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js','jquery-touchpunch-ui');

		


$content = <<<EOF
EOF;

$user = $vce->user;

// add javascript to page
$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui select2');



// $parent_id = $recipe_component->component_id;
// <input type="hidden" name="parent_id" value="$parent_id">
// $vce->dump($each_component);



	$new_cycle_content = <<<EOF
	<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
	<input type="hidden" name="dossier" value="$dossier_for_create">

EOF;

		// cycle name input
		$input = array(
				'type' => 'text',
				'name' => 'pbccycle_name',
				'data' => array(
						'autocapitalize' => 'none',
						'tag' => 'required',
				)
		);
		
		$cycleName_input = $vce->content->create_input($input,'Cycle Name');

		// cycle begin date input
		$input = array(
			'type' => 'text',
			'name' => 'pbccycle_begins',
			'class' => 'datepicker',
			'data' => array(
					'autocapitalize' => 'none',
					'tag' => 'required',
			)
		);
		
		$cycleStartDate_input = $vce->content->create_input($input,'Cycle Start Date');



		$filter_by = array(
			'group' => $vce->user->group
		);
	
		$users_in_same_group = pbc_utilities::filter_users($filter_by, $vce);
	
		//start multiple select			
		$dd_array = array(
		'type' => 'select',
		'required' => 'true',
		'dl' => 'cycle_participants', 
		'dl_name' => 'Cycle Participants', 
		'dl_id' => '', 
		'component_id' => null, 
		'component_method' => 'add',
		'get_user_array' => array('user_ids' => $users_in_same_group)
		);
	
		$input = pbc_utilities::datalist_add_multiple_select($dd_array, $vce);
		// $vce->dump($input);
		$multiple_member_input = $vce->content->create_input($input,'Cycle Participants');




		$new_cycle_content .= <<<EOF
				$cycleName_input
				$cycleStartDate_input
				$multiple_member_input
EOF;

		$content_tab2 = <<<EOF
		<div class="form-container"
		<p>
EOF;

		$content_tab2 .= <<<EOF
		$new_cycle_content
EOF;


		$content_tab2 .= <<<EOF
<br>
<input type="submit" value="Create Cycle" class="button__primary">
</form>
</p>
</div>
</div>

EOF;

		$content .= <<<EOF
		$content_tab2
EOF;

		$vce->content->add('add_cycle', $content);

		
	}







	/**
	 * create
	 */
	public function create($input) {
	
		global $vce;

		// takes care of glitch from mobile app and re-encodes the input saved in JSON format
		foreach ($input as $k=>$v) {
			if (is_object($v)) {
				$input[$k] = json_encode($v);
			}
		}

		// get redirect url
		if (isset($input['redirect_url'])) {
			$redirect_url = $input['redirect_url'];
			unset($input['redirect_url']);
		} else {
			$redirect_url = NULL;
		}

		// create title
		$input['title'] = $input['pbccycle_name'];

		
		// create url path for this pbccycle
		// $input['url'] = 'courses/' . $input['organization'] . '/'  . $input['group'] . '/'  . $input['year'] . '/' . strtolower($input['term']) . '/' . $vce->site->create_path($input['course_number']);
		if (!isset($input['organization'])) {
		
			$input['organization'] = 1;
		}
		// create url path for this pbccycle
		$input['url'] = 'pbccycles/' . $input['organization'] . '/' . time();
		// create empty user_access
		$input['user_access'] = '';

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


		
		$component_id = $this->create_component($input);
		
			//put newly created component id into input array
		$input['component_id'] = $component_id;





//update user_access list:

	// add users from drag and drop to user_access array of a cycle
	// (Makes an array of users from any and all drag-and-drops on page, gets array from 
	// cycle component, combines and stores them)	
	
	// get all user id's from drag-and-drops in form
		foreach($input as $key => $value) {
			if (strpos($key, 'user_ids') !== false) {
				$dl_input = json_decode(html_entity_decode($value, ENT_QUOTES));
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
	//get exiting user-access list from the cycle
		$query = 'SELECT meta_value FROM ' . TABLE_PREFIX . 'components_meta WHERE component_id =' . $input['component_id'] . ' AND meta_key = "user_access"' ;
		$result = $vce->db->get_data_object($query);
		if (isset($result[0]->meta_value)) {
			$existing_users = json_decode($result[0]->meta_value, true);
		}
// 		$vce->log(json_decode($result[0]->meta_value));
		
	//add existing users to new user array
	if (isset($existing_users)) {
		foreach ($existing_users as $key => $value) {
			$user_access_array[$key] = $value;
		}
	}


	//convert array to json encoded datastructure
		$user_access_array = $vce->db->mysqli_escape(json_encode($user_access_array));
		
	//add selected id's to cycle's user_access
		$query = 'UPDATE ' . TABLE_PREFIX . 'components_meta SET meta_value = "' . $user_access_array . '" WHERE component_id =' . $input['component_id'] . ' AND meta_key = "user_access"' ;
		$result = $vce->db->query($query);
	



	
	//if the component has been created and the related datalist has been created, send out emails to participants and return success/redirect to component
	if ($component_id && $dl_user_ids) {   
		
			$vce->site->add_attributes('message',$this->component_info()['name'] . ' Created');
		//redirect to as_content after creation
			$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components WHERE component_id ="' . $component_id . '"' ;
			$component = $vce->db->get_data_object($query);
			$requested_url = $vce->site->site_url . '/' . $component[0]->url;
				

			
			//put together list to send emails to
			$user_list = trim($drag_and_drop_datalist, '|');
			$users_info = array('user_ids' => $user_list);
			$user_list = $vce->user->get_users($users_info);
	
		//get get cycle url
			$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components WHERE component_id ="' . $input['component_id'] . '"' ;
			$component = $vce->db->get_data_object($query);
			$cycle_url = $vce->site->site_url . '/' . $component[0]->url;
			$query = 'SELECT * FROM  ' . TABLE_PREFIX . 'components_meta WHERE component_id ="' . $input['component_id'] . '" AND meta_key = "title"' ;
			$component_meta = $vce->db->get_data_object($query);
			$cycle_title = $component_meta[0]->meta_value;
// 		$vce->log($user_list);

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
Your OHS Coaching Companion user account has been included as a participant in a Practice Based Coaching Cycle entitled "$cycle_title" at this link:<br>
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

		//  //test for mail
		// $mail_attributes = array (
		// 	'from' => array('coachingcompanion@eclkc.info', 'DoNotReply'),
		// 	'to' => array(
		// 		 array('daytonra@uw.edu', 'Dayton Allemann')
		// 	 ),
		// 	'subject' => 'sent with $vce->mail: OHS Coaching Companion Account Update',
		// 	'message' => $email_message,
		// 	'html' => true,
		// 	'SMTPAuth' => false
		//  );	


		// $vce->mail($mail_attributes);
		// mail('daytonra@uw.edu', 'test: sent with mail:', '$email_message');
	}
		
		
		

		
	
		if (!empty($component_id)) {
			echo json_encode(array('response' => 'success','procedure' => 'create', 'url' => $redirect_url, 'action' => 'reload','message' => 'Created'));
			return;
		
		}
	}	
		
		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Error"));
		return;

	}
	



	/**
	 * update
	 */
	public function update($input) {

		global $vce;

		// takes care of glitch from mobile app and re-encodes the input saved in JSON format
		foreach ($input as $k=>$v) {
			if (is_object($v)) {
				$input[$k] = json_encode($v);
			}
		}

		if (isset($input['redirect_url'])) {
			$redirect_url = $input['redirect_url'];
			unset($input['redirect_url']);
		} else {
			$redirect_url = NULL;
		}
	
		if (isset($input['pbccycle_name'])) {
			$input['title'] =  $input['pbccycle_name'];
		}

		if (isset($input["form_location"]) && $input["form_location"] == 'generate_link_container') {
			// $vce->log($input["form_location"]);
			if (!isset($input['pbccycle_status']) || $input['pbccycle_status'] != 'Complete') {
				$input['pbccycle_status'] = 'in_progress';
				// $vce->log($input['pbccycle_status']);
			}
			unset($input["form_location"]);
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
		// 	$vce->log($input);
		// exit;
		if ($this->update_component($input)) {
			echo json_encode(array('response' => 'success','procedure' => 'update', 'url' => $redirect_url, 'action' => 'reload','message' => "Updated"));
			return;
		}
		
			echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Error"));
			return;
	
	}


	
		/**
	 * fields for ManageRecipes
	 */
	public function go_to_add_ap_step($input) {
			//get meta_data (created_at) from db
			$query = "SELECT url FROM " . TABLE_PREFIX . "components WHERE component_id='" . $input['component_id'] . "'";
			$components_data = $vce->db->get_data_object($query);
		
			// for components_meta key => values
			$data = array();	
	
			// get url
			foreach ($components_data as $each_data) {
				$requested_url = $each_data->url;
			}	
	
// 	$input['component_id']
	
// 	global $site;
// 	$vce->log($url);
// 		exit;
		if ($input['go_to_new_page'] == true) {
		
			
			$requested_url = '';

			echo json_encode(array('response' => 'success','procedure' => 'go_to_add_ap_step','action' => 'reload','url' => $requested_url, 'message' => "Add a new action plan step here."));
			return;
		}
		
			echo json_encode(array('response' => 'error','procedure' => 'go_to_add_ap_step','message' => "Error"));
			return;
		
	
	}


	/**
	* pagination of cycles
	*/
	public function pagination($input) {

		// add attributes to page object for next page load using session
		global $site;
		global $vce;
		
		
		$pagination_current = filter_var($input['pagination_current'], FILTER_SANITIZE_NUMBER_INT);
		
		if ($pagination_current < 1) {
			$pagination_current = 1;
		}
		
		$vce->site->add_attributes('sort_by',$input['sort_by']);
		$vce->site->add_attributes('sort_direction',$input['sort_direction']);
		$vce->site->add_attributes('pagination_current',$pagination_current);

		
		echo json_encode(array('response' => 'success','message' => 'pagination'));
		return;
	
	}



/*
 add config info for this component
*/
public function component_configuration() {
	global $vce;
	$content = NULL;

	$class = get_class($this);

	if (isset($vce->site->$class)) {
		$value = $vce->site->$class;
		$class_minutia = $class . '_minutia';
		$vector = $vce->site->$class_minutia;
		$config = json_decode($vce->site->decryption($value,$vector), true);
	}

	/**  Standard CRUD+Track for all site roles */
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
			'selected' => (isset($config[$k]) ? explode('|', $config[$k]) : null),
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
			'label' => 'Send email notification on Cycle creation?'
			)
		);
	$content .= $vce->content->create_input($configuration_input,'Email Notifier On: Create');


	$default_create_email_notifier_content = <<<EOF
Your OHS Coaching Companion user account has been included as a participant in a Practice Based Coaching Cycle entitled "{cycle_title}" at this link:
	{cycle_url}
When you log in to the OHS Coaching Companion, this cycle will appear on your home page.
If you have any questions, please contact your group administrator.
Once you are registered with the ECLKC, you can access the OHS Coaching Companion here:
<a href="https://eclkc.ohs.acf.hhs.gov/cc/">Coaching Companion Home</a>
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
			'label' => 'Send site notification on Cycle creation?'
			)
		);
	$content .= $vce->content->create_input($configuration_input,'Email Notifier On: Create');


	$default_create_site_notifier_content = <<<EOF
Your OHS Coaching Companion user account has been included as a participant in a Practice Based Coaching Cycle entitled "{cycle_title}" at this link:{cycle_url}
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
		
// 	/**
// 	 * fields for ManageRecipes
// 	 */
// 	public function recipe_fields($recipe) {
	
// 		global $vce;
		
// 		$title = isset($recipe['title']) ? $recipe['title'] : $this->component_info()['name'];
// 		$template = isset($recipe['template']) ? $recipe['template'] : null;
// 		$role_select = isset($recipe['role_select']) ? $recipe['role_select'] : null;
		
// $elements = <<<EOF
// <label>
// <input type="text" name="title" value="$title" tag="required" autocomplete="off">
// <div class="label-text">
// <div class="label-message">Title</div>
// <div class="label-error">Enter a Title</div>
// </div>
// </label>
// <label>
// <select name="template">
// <option value=""></option>
// EOF;

// 		foreach($vce->site->get_template_names() as $key=>$value) {
// 			$elements .= '<option value="' . $value . '"';
// 			if ($value == $template) {
// 				$elements .= ' selected';
// 			}
// 			$elements .= '>' . $key . '</option>';
// 		}

// $elements .= <<<EOF
// </select>
// <div class="label-text">
// <div class="label-message">Template (optional)</div>
// <div class="label-error">Enter a Template</div>
// </div>
// </label>
// EOF;
// 		return $elements;
		
// 	}


}