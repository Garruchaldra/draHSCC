<?php

class ManageGroups extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Manage Groups',
			'description' => 'Manage Groups',
			'category' => 'state'
		);
	}

	/**
	 * component has been installed, now do the following
	 */
	public function installed() {
	
		global $site;
		
		// add hierarchiacal roles for state version
		// 
		// Agency Lead
		// Coach Support Lead
		// Coach
		// Teacher/Provider
		
		
		$attributes = array (
		array (
		'role_name' => 'Agency Lead'
		),
		array (
		'role_name' => 'Coach Support Lead'
		),
		array (
		'role_name' => 'Coach'
		),
		array (
		'role_name' => 'Teacher/Provider'
		)
		);
	 
	 	// add new site roles
		$site->add_site_roles($attributes);
		
	}

	/**
	 * Component has been activated.
	 */
	public function activated() {
	}
	
	/**
	 * Component has been disabled.
	 */
	public function disabled() {
	}

	/**
	 * component has been removed, as in deleted
	 */
	public function removed() {
		// not removing roles since it would cause issues with user accounts.
	}


	/**
	 * 
	 */
	public function as_content($each_component, $vce) {
	
$content = <<<EOF
<div class="pbc-item-container main-container">
<div class="pbc-item-header manage-groups"><h1>Manage Groups</h1></div>
<div class="pbc-item-body">
<div class="pbc-item-each add-padding">
EOF;

		$vce->content->add('main',$content);
		
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui tablesorter');
		
		$vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js', 'jquery-ui');

		// add stylesheet to page
		// $vce->site->add_style(dirname(__FILE__) . '/css/style.css','manage-groups-style');

		// get meta_data associated with datalist_id
		$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_key='members' AND datalist_id IN (SELECT datalist_id FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_value='groups_datalist' AND datalist_id IN (SELECT datalist_id FROM " . TABLE_PREFIX . "datalists WHERE user_id='" . $vce->user->user_id . "'))";
		$members = $vce->db->get_data_object($query);
		
		// the datalist that belongs to this user
		if (isset($members[0])) {
			$user_datalist = $members[0]->datalist_id;
		} else {
			// create datalist for this user
			$attributes = array (
			'user_id' => $vce->user->user_id,
			'datalist' => 'groups_datalist',
			'aspects' => array ('name' => 'Groups','members'=> '')
			);
			$user_datalist = $vce->create_datalist($attributes);
		}
		
		// get group members
		$group_members = (!empty($members)) ? $vce->user->get_users(array('user_ids' => trim($members[0]->meta_value,'|'))) : null;

$content = <<<EOF
<div class="clickbar-container">
<div class="clickbar-content no-padding clickbar-open">
EOF;

		if (isset($group_members) && !empty($group_members)) {

$content .= <<<EOF
<table id="default-group" class="tablesorter">
<thead>
<tr>
<th></th>
<th>First Name</th>
<th>Last Name</th>
<th>Email</th>
<th>Site Role</th>
</tr>
</thead>
EOF;

			foreach ($group_members as $each_member) {

				// dossier for invite
				$dossier = array(
				'type' => 'ManageGroups',
				'procedure' => 'remove_member',
				'member_id' => $each_member->user_id
				);

				// generate dossier
				$dossier_for_remove = $vce->generate_dossier($dossier);

				// get role name for this group memeber
				$roles = json_decode($vce->site->roles, true);
				
				$role_name = isset($roles[$each_member->role_id]['role_name']) ? $roles[$each_member->role_id]['role_name'] : $roles[$each_member->role_id];
		
$content .= <<<EOF
<tr class="each-member">
<td class="align-center">
<form class="remove-member" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_remove">
<input type="submit" value="Remove">
</form>
</td>
<td>
$each_member->first_name
</td>
<td>
$each_member->last_name
</td>
<td>
$each_member->email
</td>
<td>
$role_name
</td>
</tr>
EOF;

			}

$content .= <<<EOF
</table>
EOF;

		} else {

$content .= <<<EOF
<div class="no-users-message">You have no users in your default group.</div>
EOF;

		}

$content .= <<<EOF
</div>
<div class="clickbar-title"><span>My Default Group</span></div>
</div>
EOF;



		$vce->content->add('main',$content);

		// load hooks
		// manage_groups_content
		if (isset($vce->site->hooks['manage_groups_content'])) {
			foreach($vce->site->hooks['manage_groups_content'] as $hook) {
				call_user_func($hook, $each_component, $vce);
			}
		}


// hide groups for now
return;

// sub groups
				
			$image_path = $vce->site->path_to_url(dirname(__FILE__));

$content = <<<EOF
<div class="clickbar-container">
<div class="clickbar-content clickbar-open clickbar-trigger">
EOF;

			if (isset($vce->group_id)) {

				$dossier = array(
				'type' => 'ManageGroups',
				'procedure' => 'update_group',
				'datalist_id' => $user_datalist,
				'item_id' => $vce->group_id
				);

				$dossier_for_update_group = $vce->generate_dossier($dossier);
			
				$attributes = array (
				'item_id' => $vce->group_id
				);
		
				$datalist = $vce->get_datalist_items($attributes);
			
				$items = $datalist['items'];
				
				$item = $datalist['items'][key($items)];
				
				if ($item['user_id'] == $vce->user->user_id) {
				
					$item_name = $item['name'];
					$item_members = $item['members'];
					$users_array = explode('|', $item_members);
					
					// get group members
					$item_users = (!empty($item['members'])) ? $vce->user->get_users(array('user_ids' => trim($item_members,'|'))) : null;

$content .= <<<EOF
<div class="clickbar-container">
<div class="clickbar-content clickbar-open">

<form id="edit-group" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update_group">
<label>
<ul id="default-members" class="connected-sortable">
EOF;


					if (!empty($group_members)) {
						foreach ($group_members as $each_member) {
							if (!in_array($each_member->user_id ,$users_array)) {
								if (isset($each_member->first_name)) {
									$name = $each_member->first_name . ' ' . $each_member->last_name;
								} else {
									$name = $each_member->email;
								}
								$content .= '<li class="ui-state-default" user_id="' .  $each_member->user_id . '">' . $name . '</li>';
							}
						}
					}
			
$content .= <<<EOF
</ul>
<div class="label-text">
<div class="label-message">Available Users</div>
<div class="label-error">Add</div>
</div>
</label>
<label>
<ul id="group-members" class="connected-sortable">
EOF;


					if (!empty($item_users)) {	
						foreach ($item_users as $each_member) {
							if (isset($each_member->first_name)) {
								$name = $each_member->first_name . ' ' . $each_member->last_name;
							} else {
								$name = $each_member->email;
							}
							$content .= '<li class="ui-state-default accepted-members" user_id="' .  $each_member->user_id . '"><span class="remove-current-members" title="remove">x</span>' . $name . '</li>';
						}
					}

$content .= <<<EOF
</ul>
<div class="label-text">
<div class="label-message">Selected Users</div>
<div class="label-error">Add</div>
</div>
</label>
<input id="selected-users" type="hidden" name="user_ids" value="$item_members">
<label>
<input id="corps-create" type="text" name="name" value="$item_name" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Name</div>
<div class="label-error">Enter a name</div>
</div>
</label>
<input type="submit" value="Update">
<div class="link-button cancel-button">Cancel</div>
</form>

</div>
<div class="clickbar-title"><span>Edit Group</span></div>
</div>
EOF;

					}

				} else {

					$dossier = array(		
					'type' => 'ManageGroups',
					'procedure' => 'create_group',
					'datalist_id' => $user_datalist
					);

					$dossier_for_create_group = $vce->generate_dossier($dossier);

$content .= <<<EOF
<div class="clickbar-container">
<div class="clickbar-content">

<form id="create-group" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_create_group">
<label>
<ul id="default-members" class="connected-sortable">
EOF;

					if (!empty($group_members)) {
						foreach ($group_members as $each_member) {
							if (isset($each_member->first_name)) {
								$name = $each_member->first_name . ' ' . $each_member->last_name;
							} else {
								$name = $each_member->email;
							}
							$content .= '<li class="ui-state-default" user_id="' .  $each_member->user_id . '">' . $name . '</li>';
						}
					}
			
$content .= <<<EOF
</ul>
<div class="label-text">
<div class="label-message">Available Users</div>
<div class="label-error">Add</div>
</div>
</label>
<label>
<ul id="group-members" class="connected-sortable">
</ul>
<div class="label-text">
<div class="label-message">Selected Users</div>
<div class="label-error">Add</div>
</div>
</label>
<input id="selected-users" type="hidden" name="user_ids" value="">
<label>
<input id="corps-create" type="text" name="name" value="" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Name</div>
<div class="label-error">Enter a name</div>
</div>
</label>
<input type="submit" value="Create">
</form>

</div>
<div class="clickbar-title clickbar-closed"><span>Create A New Group</span></div>
</div>
EOF;

			}

			$groups = $vce->get_datalist_items(array('datalist_id' => $user_datalist));

			if (isset($groups['items'])) {
			
$content .= <<<EOF
<table class="my-groups table-to-card">
<thead>
<tr>
<td>Name</td>
<td icon="$image_path/images/info.png">Members</td>
<td icon="$image_path/images/edit.png">Edit</td>
<td icon="$image_path/images/delete.png">Delete</td>
</tr>
</thead>
<tbody>
EOF;

				foreach ($groups['items'] as $each_group) {

					$name = $each_group['name'];

					$members = trim($each_group['members'],'|');
					
					// get group members
					$item_users = (!empty($members)) ? $vce->user->get_users(array('user_ids' => $members)) : null;

					$dossier = array(		
					'type' => 'ManageGroups',
					'procedure' => 'edit_group',
					'datalist_id' => $user_datalist,
					'item_id' => $each_group['item_id']
					);

					$dossier_for_edit_group = $vce->generate_dossier($dossier);	

					$dossier = array(		
					'type' => 'ManageGroups',
					'procedure' => 'delete_group',
					'datalist_id' => $user_datalist,
					'item_id' => $each_group['item_id']
					);

					$dossier_for_delete_group = $vce->generate_dossier($dossier);
					
					$user_info = $vce->user;
					
					$user_image = $vce->site->site_url . '/vce-application/images/user_' . ($user_info->user_id % 5) . '.png';


$content .= <<<EOF
<tr name="$name">
<td>$name</td>
<td>
<label>
<div class="control-height">
<div class="user-block" title="">
<img src="$user_image" class="user-image">$user_info->first_name $user_info->last_name
</div>
</div>
<div class="label-text">
<div class="label-message">Created By</div>
</div>
</label>
<label>
<div class="control-height">
EOF;

					// get count in a nice way
					$group_count = (count($item_users) == 1) ? count($item_users) . ' Member' : count($item_users) . ' Members';


					if (!empty($item_users)) {
						foreach ($item_users as $user_key=>$user_info) {
					
							$user_image = $vce->site->site_url . '/vce-application/images/user_' . ($user_info->user_id % 5) . '.png';

$content .= <<<EOF
<div class="user-block" title="$user_info->email">
<img src="$user_image" class="user-image">$user_info->first_name $user_info->last_name
</div>
EOF;

						}
					}

$content .= <<<EOF
</div>
<div class="label-text">
<div class="label-message">$group_count</div>
</div>
</label>
</td>
<td>
<form class="edit-group" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_edit_group">
<input type="submit" value="Edit">
</form>
</td>
<td>
<form class="remove-group" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete_group">
<input type="submit" value="Delete">
</form>
</td>
</tr>
EOF;

				}

$content .= <<<EOF
</tbody>
</table>
EOF;
	
			}

$content .= <<<EOF
</div>
<div class="clickbar-title"><span>My Groups</span></div>
</div>
EOF;


		// find all groups that this user belongs to.

		$search = '%|' . $vce->user->user_id . '|%';

		// get meta_data associated with any sub groups this user has been added to
		$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_items_meta WHERE item_id IN (SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_key='members' AND meta_value LIKE '" . $search . "')";
		$sub_groups = $vce->db->get_data_object($query);
		
		if (!empty($sub_groups)) {
		
			$sub_groups_list = array();
			$all_users = array();

			foreach ($sub_groups as $meta_info) {
		
				if (!isset($sub_groups_list[$meta_info->item_id]['item_id'])) {
					$sub_groups_list[$meta_info->item_id]['item_id'] = $meta_info->item_id;
				}
		
				$sub_groups_list[$meta_info->item_id][$meta_info->meta_key] = $meta_info->meta_value;
		
				// add group owner
				if ($meta_info->meta_key == "user_id") {
					$all_users[] = $meta_info->meta_value;
				}
		
				// add group members
				if ($meta_info->meta_key == "members") {
					$all_users = array_merge($all_users, explode('|', trim($meta_info->meta_value,'|')));
				}
		
			}
			
			// remove duplicates
			$all_users = array_unique($all_users);
			
			$group_members = (!empty($all_users)) ? $vce->user->get_users(array('user_ids' => implode(',', $all_users)), true) : null;

			// $vce->site->dump($group_members);

	
$content .= <<<EOF
<div class="clickbar-container">
<div class="clickbar-content clickbar-open no-padding">

<table class="tablesorter table-to-card">
<thead>
<th>Name</th>
<th>Created By</th>
<th>Members</th>
<th>Drop</th>
</thead>
EOF;

			foreach ($sub_groups_list as $each_sub_group) {
			
				$each_item_id = $each_sub_group['item_id'];
				
				// this is returning null
				$each_user_id = $each_sub_group['user_id'];
				
				// temp fix
				$each_user_id = $vce->user->user_id;
				$each_name = $each_sub_group['name'];
				$each_members = trim($each_sub_group['members'],'|');

				$owner_name = $group_members[$each_user_id]->first_name . ' ' . $group_members[$each_user_id]->last_name; 
				$owner_image = $vce->site->site_url . '/vce-application/images/user_' . ($group_members[$each_user_id]->user_id % 5) . '.png';


$content .= <<<EOF
<tr>
<td>
$each_name
</td>
<td>
<div class="user-block">
<img src="$owner_image" class="user-image">$owner_name
</div>
</td>
<td>
EOF;

				foreach (explode('|', $each_members) as $member_id) {
		
					$member_name = $group_members[$member_id]->first_name . ' ' . $group_members[$member_id]->last_name; 
					$member_image = $vce->site->site_url . '/vce-application/images/user_' . ($group_members[$member_id]->user_id % 5) . '.png';

$content .= <<<EOF
<div class="user-block">
<img src="$member_image" class="user-image">$member_name
</div>
EOF;

				}

$content .= <<<EOF
</td>
<td>
EOF;

				$dossier = array(		
				'type' => 'ManageGroups',
				'procedure' => 'leave_group',
				'item_id' => $each_item_id,
				'name' => $each_name
				);

				$dossier_for_leave_group = $vce->generate_dossier($dossier);

$content .= <<<EOF
<form class="asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_leave_group">
<input type="submit" value="Leave Group">
</form>
</td>
</tr>
EOF;
			
			}
			
$content .= <<<EOF
</table>
</div>
<div class="clickbar-title"><span>Other Groups</span></div>
</div>
EOF;

		}

		$vce->content->add('main',$content);
	
	}
	
	
	
	/**
	 * 
	 */
	public function as_content_finish($each_component, $vce) {
	
$content = <<<EOF
</div>
</div>
</div>
EOF;

		$vce->content->add('main',$content);
		
	}


	/**
	 * remove a member from the default group
	 */
	public static function remove_member($input) {
	
		global $vce;
		
		// create array that switches
		$owners = array (
		$vce->user->user_id => $input['member_id'],
		$input['member_id'] => $vce->user->user_id
		);
		
		// remove from both user groups
		foreach ($owners as $group_owner=>$remove_user) {
		
			// get meta_data associated with datalist_id
			$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_key='members' AND datalist_id IN (SELECT datalist_id FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_value='groups_datalist' AND datalist_id IN (SELECT datalist_id FROM " . TABLE_PREFIX . "datalists WHERE user_id='" . $group_owner . "'))";
			$members = $vce->db->get_data_object($query);

			$user_list = explode('|',$members[0]->meta_value);
	
			// remove key
			if (($key = array_search($remove_user, $user_list)) !== false) {
	
				unset($user_list[$key]);
		
				$update_list = implode('|', array_unique($user_list));
			
				$update = array('meta_value' => $update_list);
				$update_where = array('id' => $members[0]->id);
				$vce->db->update('datalists_meta', $update, $update_where);


				$attributes = array (
				'datalist_id' => $members[0]->datalist_id
				);
		
				$items = $vce->get_datalist_items($attributes);
				
				if (isset($items['items'])) {
				
					foreach ($items['items'] as $each_item) {

						// resort and clean up
						$member_ids = explode('|', $each_item['members']);

						if (($key = array_search($remove_user, $member_ids)) !== false) {
							 unset($member_ids[$key]);
						}
					
						sort($member_ids);
						// $members = implode('|', array_unique($member_ids));
						
						// adding for LIKE search
						$members = '|' . implode('|', array_unique($member_ids)) . '|';

						$update = array('meta_value' => $members);
						$update_where = array('item_id' => $each_item['item_id'], 'meta_key' => 'members');
						$vce->db->update('datalists_items_meta', $update, $update_where);
					
				
					}
				
				}
				
			} else {
			
				echo json_encode(array('response' => 'error','message' => 'Member not found in group'));
				return;
				
			}
		
		}
		
		$vce->site->add_attributes('message','You have removed a user from your group');

		echo json_encode(array('response' => 'success','message' => 'deleted'));
		return;

	}


	/**
	 * create a new group
	 */
	public static function create_group($input) {
	
		global $vce;
		
		// adding for LIKE search
		$members = '|' . $input['user_ids'] . '|';
	
		$attributes = array (
		'datalist_id' => $input['datalist_id'],
		'items' => array ( array ('user_id' => $user->user_id, 'name' => $input['name'], 'members' => $members ) )
		);

		$vce->insert_datalist_items($attributes);
		
		$vce->site->add_attributes('message','You have created a new group.');
	
		echo json_encode(array('response' => 'success','message' => json_encode($input)));
		return;
	
	}


	/**
	 * edit group
	 */
	public static function edit_group($input) {
	
		global $vce;
		
		$attributes = array (
		'datalist_id' => $input['datalist_id']
		);
		
		$datalist = $vce->get_datalist($attributes);
		
		if ($vce->user->user_id == $datalist[key($datalist)]['user_id']) {
		
			$vce->site->add_attributes('group_id',$input['item_id']);
		
			echo json_encode(array('response' => 'success','message' => 'edit'));
			return;	
		
		}
	
	}


	/**
	 * update group
	 */
	public static function update_group($input) {
	
		global $vce;
		
		$attributes = array (
		'datalist_id' => $input['datalist_id']
		);
		
		$datalist = $vce->get_datalist($attributes);
		
		if ($vce->user->user_id == $datalist[key($datalist)]['user_id']) {
		
			// resort and clean up
			$member_ids = explode('|', $input['user_ids']);
			sort($member_ids);
			$members = '|' . implode('|', array_unique($member_ids)) . '|';
		
			$update = array('meta_value' => $members);
			$update_where = array('item_id' => $input['item_id'], 'meta_key' => 'members');
			$db->update('datalists_items_meta', $update, $update_where);
			
			$update = array('meta_value' => $input['name']);
			$update_where = array('item_id' => $input['item_id'], 'meta_key' => 'name');
			$db->update('datalists_items_meta', $update, $update_where);
		
			$vce->site->add_attributes('message','Updated');
		
			echo json_encode(array('response' => 'success','message' => 'deleted'));
			return;	
		
		}
		
		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Error"));
		return;
	
	}

	/**
	 * delete group
	 */
	public static function delete_group($input) {
	
		global $vce;
		
		$attributes = array (
		'datalist_id' => $input['datalist_id']
		);
		
		$datalist = $vce->get_datalist($attributes);
		
		if ($vce->user->user_id == $datalist[key($datalist)]['user_id']) {
		
			$attributes = array (
			'item_id' => $input['item_id']
			);
		
			$vce->remove_datalist($attributes);
		
			$vce->site->add_attributes('message','Group Deleted');

			echo json_encode(array('response' => 'success','message' => 'deleted'));
			return;	
		
		}

		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Error"));
		return;

	}


	/**
	 * leave a sub group
	 */
	public static function leave_group($input) {
	
		global $vce;

		// get meta_data associated with datalist_id
		$query = "SELECT meta_value AS 'members' FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_key='members' AND item_id='" . $input['item_id'] . "'";
		$members_data = $vce->db->get_data_object($query);
		
		// make members into an array
		$members = explode('|', trim($members_data[0]->members,'|'));
		
		// remove current user from all_users array
		if (!empty($members) && ($key = array_search($user->user_id, $members)) !== false) {
			unset($members[$key]);
		}
		
		// same updated members
		$update = array('meta_value' => '|' . implode('|', $members) . '|');
		$update_where = array('item_id' => $input['item_id'], 'meta_key' => 'members');
		$vce->db->update('datalists_items_meta', $update, $update_where);	

		$vce->site->add_attributes('message','You have left the group named ' . $input['name']);
	
		echo json_encode(array('response' => 'success','message' => 'You have left a group'));
		return;

	}


	/**
	 * fields for ManageRecipes
	 */
	public function recipe_fields($recipe) {
	
		global $site;
		
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
		$template = isset($recipe['template']) ? $recipe['template'] : null;
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
<select name="template">
<option value=""></option>
EOF;

		foreach($site->get_template_names() as $key=>$value) {
			$elements .= '<option value="' . $value . '"';
			if ($value == $template) {
				$elements .= ' selected';
			}
			$elements .= '>' . $key . '</option>';
		}

$elements .= <<<EOF
</select>
<div class="label-text">
<div class="label-message">Template (optional)</div>
<div class="label-error">Enter a Template</div>
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