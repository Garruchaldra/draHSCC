<?php

class PbcManageUsers extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Manage Users',
			'description' => 'Add, edit, masquerade as, and delete site users.',
			'category' => 'pbc'
		);
	}
	
	/**
	 *
	 */
	public function as_content($each_component, $page) {
	
		global $db;
		global $site;
		
// 		$site->dump($page->user->role_id);
		
		// add javascript to page
		$page->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter');
		
		$page->site->add_style(dirname(__FILE__) . '/css/style.css');
		
		// check if value is in page object
		$user_id = isset($page->user_id) ? $page->user_id : null;
		
		//establish role heirarchy numerically (correct the json object in the db)
			$corrected_site_roles = array();
			foreach (json_decode($page->site->roles, true) as $key => $value) {
				$role_name = is_array($value) ? $value['role_name'] : $value;
				switch ($role_name) {
				 case 'Admin':
					$permissions_key = 0;
					break;
				 case 'SiteAdmin':
					$permissions_key = 1;
					break;
				 case 'OrganizationAdmin':
					$permissions_key = 2;
					break;
				 case 'GroupAdmin':
					$permissions_key = 3;
					break;
				 case 'Coach':
					$permissions_key = 4;
					break;
				 case 'Coachee':
					$permissions_key = 5;
					break;    			
				 default:
					 $permissions_key = 100;
			}
			$corrected_site_roles[$key] = $permissions_key;
		}
		
		$roles = json_decode($page->site->roles, true);	
	
		$pagination_current = isset($page->pagination_current) ? $page->pagination_current : 1;
		$pagination_length = isset($page->pagination_length) ? $page->pagination_length : 50;
		$pagination_offset = ($pagination_current != 1) ? ($pagination_length * ($pagination_current - 1)) : 0;
		
		
		
		// establish a filter, if criteria are present
		$filter_by = array();
		$paginate = true;

		// if value is set, disable pagination
		if (isset($user_id)) {
				$paginate = false;
		}
		
		// see only what you have permission to see 
		// (org admins see only their org, group admins only their group)
		if ($page->user->role_id == 6) {
			$page->filter_by_group = $page->user->group;
		}
		if ($page->user->role_id == 5) {
			$page->filter_by_organization = $page->user->organization;
		}
		
		
	// search for an existing filter definition in the page object
		foreach ($page as $key=>$value) {
			if (strpos($key, 'filter_by_') !== FALSE) {
				$filter_by[str_replace('filter_by_', '', $key)] = $value;
				if ($key != 'filter_by_role_id') {
					$paginate = false;
				}
			}
		}


		if (isset($page->user_search_results)) {
		
			$site_users = json_decode($page->user_search_results);
			
			// set value to hide pagination next time around
			$page->site->add_attributes('search_results_edit',true);
		
		} else {
			// initialize array to store users
			$site_users = array();

			//basic query, looks for all users
			$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users";
			
            if (isset($user_id)) {
            //if we have an id that we are looking for
                    $query .= " WHERE user_id='" . $user_id . "'";
                    
        	} else if (isset($filter_by['organization']) && !isset($filter_by['group'])) {
            // if we are looking for all users in an organization

            	$organization = $filter_by['organization'];

            	$query_for_datalist = "SELECT * FROM  " . TABLE_PREFIX . "datalists WHERE datalist_id = $organization";

				$result = $db->get_data_object($query_for_datalist);
				foreach ($result as $dl) {
					$item_id = $dl->item_id;
					$parent_id = $dl->parent_id;
				}

				$attributes = array(
					'parent_id' => 1,
					'item_id' => $organization
				);
		
				$options = $site->get_datalist_items($attributes);

				$user_list_array = array();
				foreach ($options['items'] as $group) {
					$this_list_array = explode('|', $group['user_list']);
					$user_list_array = array_merge($user_list_array, $this_list_array);
				}
				//if user list array is empty, give it a default value
				foreach ($user_list_array as $key => $value) {
					if (empty($value)) {
					   unset($user_list_array[$key]);
					}
				}
				if (empty($user_list_array)) {
				   $user_list_array[] = 0;
				}

				$user_list = implode(',', $user_list_array);
            	$query .= " WHERE user_id IN (" . $user_list . ")";

       		} else if (isset($filter_by['group'])) {
            // if we are looking for users in a particular group
            
            	$group = $filter_by['group'];
            	$query_for_user_list = "SELECT meta_value FROM  " . TABLE_PREFIX . "datalists_items_meta WHERE item_id = $group  AND meta_key = 'user_list'";
				$result = $db->get_data_object($query_for_user_list);
				foreach ($result as $user_list) {
					$user_list = $user_list->meta_value;
					$user_list_array = explode('|', $user_list);
				}

				//if user list array is empty, give it a default value
				foreach ($user_list_array as $key => $value) {
					if (empty($value)) {
					   unset($user_list_array[$key]);
					}
				}
				if (empty($user_list_array)) {
				   $user_list_array[] = 0;
				}
				
				$user_list = implode(',', $user_list_array);
				
            	$query .= " WHERE user_id IN (" . $user_list . ")";
            } 
            
            if (isset($filter_by['role_id'])) {
            // if we are filtering by role_id
            	if (strpos($query, 'WHERE') !== FALSE) {
            		$query .= " AND role_id='" . $filter_by['role_id'] . "'";
            	} else {
					$query .= " WHERE role_id='" . $filter_by['role_id'] . "'";
				}
			}


			// get users which fit this criteria
			$all_users = $db->get_data_object($query);
			
			$all_users_total = $all_users;
			
			// only paginate for role_id

			if ($paginate === true) {
				// use array_slice to limit users
				$all_users = array_slice($all_users, $pagination_offset, $pagination_length);
			}

// global $site;
// $site->dump($all_users);
// $site->dump($filter_by);

			foreach ($all_users as $each_user) {
		
				// create array
				$user_object = array();
		
				// add the values into the user object	
				$user_object['user_id'] = $each_user->user_id;
				$user_object['role_id'] = $each_user->role_id;
			
				$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id='" . $each_user->user_id . "'  AND minutia=''";
				$metadata = $db->get_data_object($query);
			
				// look through metadata
				foreach ($metadata as $each_metadata) {

					//decrypt the values
					$value = user::decryption($each_metadata->meta_value, $each_user->vector);

					// add the values into the user object	
					$user_object[$each_metadata->meta_key] = $db->clean($value);		
				}
				// $site->dump($user_object);
				// filter users by anything that is in filter_by, unless the user is in the New User organization
				//get the New User id numbers
				$new_user_id = self::new_user_id();
			if ($user_object['organization'] != $new_user_id['organization']) {
				foreach ($filter_by as $each_filte_key=>$each_filter_value) {
				
					if ($user_object[$each_filte_key] != $each_filter_value && $each_filte_key != 'role_id') {
						// skip to next,. one level up
						continue 2;
					}
				
				}
			}
				// save into site_users array
				$site_users[$each_user->user_id] = (object) $user_object;
// $site->dump($site_users);
			}
		
		}
		
		// total number of users
		$pagination_total = isset($all_users_total) ? count($all_users_total) : count($site_users);
		$pagination_count = ceil($pagination_total / $pagination_length);
		
		// update an exisiting user, if $page->user_id (which is used to set $user_id) is present
		if (isset($user_id)) {
// $site->dump($user_id);
			// get specific user object from the $site_users array
			$user_info = $site_users[$user_id];
			// set dossier
			$dossier_for_update = $page->user->encryption(json_encode(array('type' => 'PbcManageUsers','procedure' => 'update','user_id' => $user_id)),$page->user->session_vector);
			
			$first_name = isset($user_info->first_name) ? $user_info->first_name : null;
			$last_name = isset($user_info->last_name) ? $user_info->last_name : null;
			// get id of the person doing this edit
			$admin_id = isset($page->user->user_id) ? $page->user->user_id : null;

$content = <<<EOF
<p>
<div class="clickbar-container">
<div class="clickbar-content clickbar-open">
<form id="form" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
<input type="hidden" name="admin_id" value="$admin_id">
<label>
<div class="input-padding">
$user_info->email
</div>
<div class="label-text">
<div class="label-message">Email</div>
<div class="label-error">Enter Email</div>
</div>
</label>
<label>
<input type="text" name="first_name" value="$first_name" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">First Name</div>
<div class="label-error">Enter a First Name</div>
</div>
</label>
<label>
<input type="text" name="last_name" value="$last_name" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Last Name</div>
<div class="label-error">Enter a Last Name</div>
</div>
</label>
EOF;

			// load hooks  These hooks are in pbccycles and pertain to displaying or editing Organization and Group
			if (isset($page->site->hooks['user_attributes'])) {
				foreach($page->site->hooks['user_attributes'] as $hook) {
					$content .= call_user_func($hook, $user_info);
				}
			}
			
if ($user_id == $page->user->user_id || $corrected_site_roles[$page->user->role_id] > $corrected_site_roles[$user_info->role_id]) {
//don't allow admins to change their own roles; display but don't edit
		$site_roles = json_decode($page->site->roles, true);
		
		// allow both simple and complex role definitions  [?]
		$user_role = is_array($site_roles[$page->user->role_id]) ? $site_roles[$page->user->role_id]['role_name'] : $site_roles[$page->user->role_id];
		$user_role_id = $page->user->role_id;

$content .= <<<EOF
<label>
<input type="hidden" name="role_id" value="$user_role_id">
<div class="input-padding">
$user_role
</div>
<div class="label-text">
<div class="label-message">Role</div>
<div class="label-error">Enter your Role</div>
</div>
</label>
EOF;


} else {
// if editing someone other than themselves, an admin can change roles for someone if they are equal to or lesser than the admin role
$content .= <<<EOF
<label>
<select name="role_id" tag="required">
<option value=""></option>
EOF;

			// show only roles which are equal to or greater than the user's role
			foreach (json_decode($page->site->roles, true) as $key => $value) {
				if ($corrected_site_roles[$page->user->role_id] <= $corrected_site_roles[$key]) {
					$role_name = is_array($value) ? $value['role_name'] : $value;
					$content .= '<label for=""><option value="' . $key . '"';
					if ($key == $user_info->role_id) {
						$content .= ' selected';
					}
					$content .= '>' . $role_name . '</option>';
				}
			}

$content .= <<<EOF
</select>
<div class="label-text">
<div class="label-message">Role</div>
<div class="label-error">Enter your Role</div>
</div>
</label>
EOF;
}

		
$content .= <<<EOF
<input type="submit" value="Update User">
<div class="link-button cancel-button">Cancel</div>
</form>
</div>
<div class="clickbar-title disabled"><span>Update An Existing User</span></div>
</div>
</p>
EOF;


		} else {
		// to create a new user
		
			$dossier_for_create = $page->user->encryption(json_encode(array('type' => 'PbcManageUsers','procedure' => 'create')),$page->user->session_vector);
			
			
			//create random 7-character password 
			 $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			 $random_password = '';
			 $max = strlen($characters) - 1;
			 for ($i = 0; $i < 7; $i++) {
				  $random_password .= $characters[mt_rand(0, $max)];
			 }


$content = <<<EOF
<p>
<div class="clickbar-container">
<div class="clickbar-content">
<form id="form" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_create">
<input type="hidden" name="password" value="$random_password">

<label>
<input type="text" name="email" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Email</div>
<div class="label-error">Enter Email</div>
</div>
</label>




<label>
<input type="text" name="first_name" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">First Name</div>
<div class="label-error">Enter a First Name</div>
</div>
</label>

<label>
<input type="text" name="last_name" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Last Name</div>
<div class="label-error">Enter a Last Name</div>
</div>
</label>
EOF;

			// load hooks
			if (isset($page->site->hooks['user_attributes'])) {
				foreach($page->site->hooks['user_attributes'] as $hook) {
					$content .= call_user_func($hook, $content);
				}
			}

$content .= <<<EOF
<label>
<select name="role_id" tag="required">
<option value=""></option>
EOF;


			foreach (json_decode($page->site->roles, true) as $key => $value) {
				// allow both simple and complex role definitions
				if ($corrected_site_roles[$page->user->role_id] <= $corrected_site_roles[$key]) {
					$role_name = is_array($value) ? $value['role_name'] : $value;
					$content .= '<label for=""><option value="' . $key . '">' . $role_name . '</option>';
				}
			}
			

		
$content .= <<<EOF
</select>
<div class="label-text">
<div class="label-message">Role</div>
<div class="label-error">Enter your Role</div>
</div>
</label>
<input type="submit" value="Create User">
</form>
</div>
<div class="clickbar-title clickbar-closed"><span>Create a New User</span></div>
</div>
</p>
EOF;

		}

		// dossier for search
		$dossier = array(
		'type' => 'PbcManageUsers',
		'procedure' => 'search'
		);

		// generate dossier
		$dossier_for_search = $page->generate_dossier($dossier);
		
		$clickbar_content = isset($page->search_value) ? 'clickbar-content clickbar-open' : 'clickbar-content';
		$clickbar_title = isset($page->search_value) ? 'clickbar-title' : 'clickbar-title clickbar-closed';
		$input_value = isset($page->search_value) ? $page->search_value : null;
	
$content .= <<<EOF
<div class="clickbar-container">
<div class="$clickbar_content">

<form id="search-users" class="asynchronous-form" method="post" action="$page->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_search">

<label>
<input type="text" name="search" value="$input_value" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Search for Users (three character minimum)		<div class="tooltip-icon">
			<div class="tooltip-content">
				You can search for any new user who has not yet been assigned to an organization, or any of the users in your organization or group.
			</div>
		</div>

</div>
<div class="label-error">Searching For Someone?</div>
</div>
</label>

<input type="submit" value="Search">
<div class="link-button cancel-button">Cancel</div>
</form>

</div>
<div class="$clickbar_title"><span>Search for Users</span>
		<div class="tooltip-icon">
			<div class="tooltip-content">
				You can search for any new user who has not yet been assigned to an organization, or any of the users in your organization or group.
			</div>
		</div>

</div>
</div>

EOF;


	// only show if we are not editing search results
	if (!isset($page->user_id)) {

		$user_attributes_list = array('user_id','last_name','first_name','email');

		// load hooks
		if (isset($page->site->hooks['user_attributes_list'])) {
			foreach($page->site->hooks['user_attributes_list'] as $hook) {
				$user_attributes_list = call_user_func($hook, $user_attributes_list);
			}
		}

// list site users
$content .= <<<EOF
<p>
<div class="clickbar-container">
<div class="clickbar-content no-padding clickbar-open">
<div class="pagination">
EOF;

		// the instructions to pass through the form
		$dossier = array(
		'type' => 'PbcManageUsers',
		'procedure' => 'filter'
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_for_filter = $page->generate_dossier($dossier);

$content .= <<<EOF
<label>
<select class="filter-form" name="role_id">
<option></option>
EOF;

		foreach ($roles as $role_id=>$each_role) {

			$content .= '<option value="' . $role_id . '"';
			// $filter_by_role_id = 3;
			if (isset($page->filter_by_role_id) && $role_id == $page->filter_by_role_id) {
				$content .= ' selected';
			}
			$content .= '>' . $each_role['role_name'] . '</option>';

		}

$content .= <<<EOF
</select>
<div class="label-text">
<div class="label-message">Filter By Site Roles</div>
</div>
</label>
EOF;

		// load hooks
		if (isset($page->site->hooks['user_attributes_filter'])) {
			foreach($page->site->hooks['user_attributes_filter'] as $hook) {
				$content .= call_user_func($hook, $filter_by, $content, $page);
			}
		}

$content .= <<<EOF
<div class="filter-form-submit link-button" dossier="$dossier_for_filter" action="$page->input_path" pagination="1">Filter</div>
EOF;

		if ($paginate === true) {

			// the instructions to pass through the form
			$dossier = array(
			'type' => 'PbcManageUsers',
			'procedure' => 'filter'
			);

			// add dossier, which is an encrypted json object of details uses in the form
			$dossier_for_filter = $page->generate_dossier($dossier);
		
			for ($x = 1;$x <= $pagination_count; $x++) {

				$class = ($x == $pagination_current) ? 'class="highlighted"': '';


		
$content .= <<<EOF
<form class="pagination-form inline-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_for_filter">
EOF;

			foreach ($filter_by as $key=>$value) {
				$content .= '<input type="hidden" name="filter_by_' . $key . '" value="' . $value . '">';
			}

$content .= <<<EOF
<input type="hidden" name="pagination_current" value="$x">
<input $class type="submit" value="$x">
</form>
EOF;
		
			}
		
			$start = $pagination_offset + 1;
			$end = ($pagination_offset + $pagination_length) < $pagination_total ? $pagination_offset + $pagination_length : $pagination_total;
			$label_text = $start . ' - ' . $end . ' of ' . $pagination_total . ' total';

$content .= <<<EOF
$label_text
EOF;

			} else {

$content .= count($site_users) . ' total';

			}

$content .= <<<EOF
</div>
<table id="users" class="tablesorter">
<thead>
<tr>
<th></th>
<!--
<th></th>
-->
<th></th>
<th>Site Role</th>
EOF;


		foreach ($user_attributes_list as $each_user_attribute) {

			$content .= '<th>' . ucwords(str_replace('_', ' ', $each_user_attribute)) . '</th>';

		}

$content .= <<<EOF
</tr>
</thead>
EOF;

	if (!empty($site_users)) {
		foreach ($site_users as $each_site_user) {
			if ($each_site_user->role_id == 1) {
               continue;
            }
		
			// allow both simple and complex role definitions
			$user_role = is_array($roles[$each_site_user->role_id]) ? $roles[$each_site_user->role_id]['role_name'] : $roles[$each_site_user->role_id];
			
			if ($each_site_user->user_id == "1") {

$content .= <<<EOF
<tr>
<td></td>
<!--
<td></td>
-->
<td></td>
<td>$user_role</td>
EOF;

			} else {
			
				$dossier_for_edit = $page->user->encryption(json_encode(array('type' => 'PbcManageUsers','procedure' => 'edit','user_id' => $each_site_user->user_id)),$page->user->session_vector);
				$dossier_for_masquerade = $page->user->encryption(json_encode(array('type' => 'PbcManageUsers','procedure' => 'masquerade','user_id' => $each_site_user->user_id)),$page->user->session_vector);
				$dossier_for_delete = $page->user->encryption(json_encode(array('type' => 'PbcManageUsers','procedure' => 'delete','user_id' => $each_site_user->user_id)),$page->user->session_vector);


$content .= <<<EOF
<tr>
<td class="align-center">
<form class="inline-form asynchronous-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_for_edit">
<input type="hidden" name="pagination_current" value="$pagination_current">
<input type="submit" value="Edit">
</form>
</td>
<!--
<td class="align-center">
<form class="inline-form asynchronous-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_for_masquerade">
<input type="submit" value="Masquerade">
</form>
</td>
-->
<td class="align-center">
<form class="delete-form inline-form asynchronous-form" method="post" action="$page->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<input type="submit" value="Delete">
</form>
</td>
<td>$user_role</td>
EOF;

			}
		global $db;
			foreach ($user_attributes_list as $each_user_attribute) {

				$content .= '<td>';
				if ($each_user_attribute == 'organization') {
					if (isset($each_site_user->$each_user_attribute) && $each_site_user->$each_user_attribute != '') {
// 										$site->dump('org_id: '.$each_site_user->$each_user_attribute);
						$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_key = 'name' AND item_id = ".$each_site_user->$each_user_attribute;
						$org_info = $db->get_data_object($query);
						$content .= $org_info[0]->meta_value;
					}
				}	elseif ($each_user_attribute == 'group') {
// 					$site->dump('org_id: '.$each_site_user->$each_user_attribute);
					if (isset($each_site_user->$each_user_attribute) && $each_site_user->$each_user_attribute != '') {
						$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_key = 'name' AND item_id = ".$each_site_user->$each_user_attribute;
						$org_info = $db->get_data_object($query);
						$content .= $org_info[0]->meta_value;
					}
				} else {
					if (isset($each_site_user->$each_user_attribute)) {
						$content .= $each_site_user->$each_user_attribute;
					}
				}
				$content .= '</td>';

			}			
			
			
			

// 			foreach ($user_attributes_list as $each_user_attribute) {
// 
// 				$content .= '<td>';

// 					if (isset($each_site_user->$each_user_attribute)) {
// 						$content .= $each_site_user->$each_user_attribute;
// 					}

// 				$content .= '</td>';
// 
// 			}

$content .= <<<EOF
</tr>
EOF;

		}
	} else {
$content .= <<<EOF
<tr>
<td class="align-center"></td>
<td class="align-center"></td>
<td class="align-center"></td>
<td class="align-center"></td>
<td class="align-center"></td>
<td class="align-center"></td>
<td class="align-center"></td>
<td class="align-center"></td>
<td class="align-center"></td>
</tr>
EOF;
	}
	
$content .= <<<EOF
</table>
</div>
<div class="clickbar-title disabled"><span>Users</span></div>
</div>
</p>
EOF;


}

		$page->content->add('main', $content);
	
	
	}

public function send_email($input) {
	
	$now = date("Y/m/d-h:i:sa");
	$mail_attributes = array (
	  'from' => array('coachingcompanion@eclkc.info', 'DoNotReply'),
	  'to' => array(
	  array($input['address'], $input['name'])
	    ),
	'subject' => $input['subject'],
	 'message' => $input['message'].' at '.$now,
	 'SMTPAuth' => false
	 );
	
	global $site;
	$site->mail($mail_attributes);	
	echo json_encode(array('response' => 'success','message' => 'Sent Test Email ','form' => 'create','action' => ''));
		return;
}

	
	/**
	 * Create a new user
	 */
	public function create($input) {
	
		global $db;

	
		// remove type so that it's not created for new user
		unset($input['type']);
	
		// test email address for validity
		$input['email'] = filter_var(strtolower($input['email']), FILTER_SANITIZE_EMAIL);
		if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
			echo json_encode(array('response' => 'error','message' => 'Email is not a valid email address','form' => 'create', 'action' => ''));
			return;
		}
		
		$lookup = user::lookup($input['email']);
		
		// check
		$query = "SELECT id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and meta_value='" . $lookup . "'";
		$lookup_check = $db->get_data_object($query);
		
		if (!empty($lookup_check)) {
			echo json_encode(array('response' => 'error','message' => 'Email is already in use','form' => 'create', 'action' => ''));
			return;
		}
		
		// call to user class to create_hash function
		$hash = user::create_hash($input['email'], $input['password']);
		
		// get a new vector for this user
		$vector = user::create_vector();

		$user_data = array(
		'vector' => $vector, 
		'hash' => $hash,
		'role_id' => $input['role_id']
		);
		$user_id = $db->insert('users', $user_data);
		
		unset($input['procedure']);
// 		unset($input['password']);
		unset($input['role_id']);
				
		// now add meta data

		$records = array();
				
		$lookup = user::lookup($input['email']);
		
		$records[] = array(
		'user_id' => $user_id,
		'meta_key' => 'lookup',
		'meta_value' => $lookup,
		'minutia' => 'false'
		);
		
		foreach ($input as $key => $value) {

			// encode user data			
			$encrypted = user::encryption($value, $vector);
			
			$records[] = array(
			'user_id' => $user_id,
			'meta_key' => $key,
			'meta_value' => $encrypted,
			'minutia' => null
			);
			
		}		
		
		$db->insert('users_meta', $records);
		
		$input['user_id'] = $user_id;    
					

		// load hooks (this hook adds the updated user group to the list of users in that group in the datalist
		if (isset($site->hooks['manage_user_create'])) {
				foreach($site->hooks['manage_user_create'] as $hook) {
						call_user_func($hook, $input);
				}
		}

		echo json_encode(array('response' => 'success','message' => 'User has been created','form' => 'create','action' => ''));


	// send email
	$fname = $input['first_name'];
	$lname = $input['last_name'];
	$fullname = $input['first_name'].' '.$input['last_name'];

$email_message = <<<EOF
Dear $fname $lname,<br>
<br>
An OHS Coaching Companion account has been created for you. <br>
To access the site, you need to register for an ECLKC login, using the email address to which this message has been sent. If you have not already done so, please follow the instructions located here: <a href="https://eclkc.ohs.acf.hhs.gov/sites/default/files/pdf/no-search/how-to-access-coaching-companion.pdf">How to access the Coaching Companion</a> <br>
<br>
Once you are registered with the ECLKC, you can access the OHS Coaching Companion here:<br>
<a href="https://eclkc.ohs.acf.hhs.gov/cc/">Coaching Companion Home</a><br>
<br>
Thank you,<br>
Your OHSCC Administrator<br>
EOF;
	$mail_attributes = array (
	  	'from' => array('coachingcompanion@eclkc.info', 'DoNotReply'),
	  	'to' => array(
	 		 array($input['email'], $fullname)
	   	 ),
		'subject' => 'Welcome to the OHS Coaching Companion',
	 	'message' => $email_message,
	 	'html' => true,
	 	'SMTPAuth' => false
	 );	
	global $site;
	$site->mail($mail_attributes);	


		return;
	}

	/**
	 * edit user
	 */
	public function edit($input) {

		// add attributes to page object for next page load using session
		global $site;
		
		$site->add_attributes('user_id',$input['user_id']);
				
		$site->add_attributes('pagination_current',$input['pagination_current']);
	
		
		echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
		return;
		
	}

	/**
	 * update user
	 */
	public function update($input) {
	
		global $db;
		global $site;
		global $page;


// $site->log($input);


		// load hooks (this hook adds the updated user group to the list of users in that group in the datalist
		if (isset($site->hooks['manage_user_update'])) {
				foreach($site->hooks['manage_user_update'] as $hook) {
// 						$site->log('hook');
						call_user_func($hook, $input);
				}
		}	
		
		$user_id = $input['user_id'];

	
		$query = "SELECT role_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "'";
		$user_info = $db->get_data_object($query);
		
		$role_id = $user_info[0]->role_id;
		$vector = $user_info[0]->vector;
		
		// has role_id been updated?
		if ($input['role_id'] != $role_id) {

			$update = array('role_id' => $input['role_id']);
			$update_where = array('user_id' => $user_id);
			$db->update('users', $update, $update_where );

		}
		
		// clean up
		unset($input['type'],$input['procedure'],$input['role_id'],$input['user_id']);
		
		// delete old meta data
		foreach ($input as $key => $value) {
				
			// delete user meta from database
			$where = array('user_id' => $user_id, 'meta_key' => $key);
			$db->delete('users_meta', $where);
		
		}
		
		// now add meta data
		
		$records = array();
		
		foreach ($input as $key => $value) {

			// encode user data			
			$encrypted = user::encryption($value, $vector);
			
			$records[] = array(
			'user_id' => $user_id,
			'meta_key' => $key,
			'meta_value' => $encrypted,
			'minutia' => null
			);
			
		}
		
		$db->insert('users_meta', $records);
				
	

		
	// send email
			// initialize array to store users
			global $site;
			$site_users = array();

			//get info about the user just updated
			$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "'";
			$updated_user = $db->get_data_object($query);
			foreach ($updated_user as $each_user) {
		
				// create array
				$user_object = array();
		
				// add the values into the user object	
				$user_object['user_id'] = $each_user->user_id;
				$user_object['role_id'] = $each_user->role_id;
			
				$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id='" . $each_user->user_id . "'  AND minutia=''";
				$metadata = $db->get_data_object($query);
			
				// look through metadata
				foreach ($metadata as $each_metadata) {

					//decrypt the values
					$value = user::decryption($each_metadata->meta_value, $each_user->vector);

					// add the values into the user object	
					$user_object[$each_metadata->meta_key] = $db->clean($value);		
				}
			
				// save into site_users array
				$site_users[$each_user->user_id] = (object) $user_object;
				
				//get role name
				$site_roles = json_decode($site->roles, true);
				$site_users[$each_user->user_id]->role_name = is_array($site_roles[$each_user->role_id]) ? $site_roles[$each_user->role_id]['role_name'] : $site_roles[$each_user->role_id];
				
				
				//get organization name
				$organization = isset($site_users[$each_user->user_id]->organization) ? $site_users[$each_user->user_id]->organization : 0;
		
				$attributes = array(
				'name' => 'organization'
				);
		
				$options = $site->get_datalist_items($attributes);

				// set datalist var
				$datalist_id = $options['datalist_id'];
		
				if (isset($options['items'])) {
					foreach ($options['items'] as $each_option) {
						if ($each_option['item_id'] == $organization) {
							$organization_name = $each_option['name'];
						}
					}
				}
				$site_users[$each_user->user_id]->organization_name = $organization_name;
				
				//get group name
				$group = isset($site_users[$each_user->user_id]->group) ? $site_users[$each_user->user_id]->group : 0;

				$attributes = array(
				'parent_id' => $datalist_id,
				'item_id' => $organization
				);
		
				$options = $site->get_datalist_items($attributes);
		
				if (isset($options['items'])) {
					foreach ($options['items'] as $each_option) {
						if ($each_option['item_id'] == $group) {
							$group_name = $each_option['name'];
						}
					}
				}
				$site_users[$each_user->user_id]->group_name = $group_name;

			}
			$user_info = $site_users[$user_id];
			
			
		foreach ($user_info as $key => $value) {
			$$key = $value;
		}
			
	$fullname = $first_name.' '.$last_name;

$email_message = <<<EOF
Dear $first_name $last_name,<br>
<br>
Your OHS Coaching Companion account information has been changed.<br>
Your account now has the following information:<br>
<br>
Email: $email<br>
First Name: $first_name<br>
Last Name: $last_name<br>
Organization: $organization_name<br>
Group: $group_name<br>
Role: $role_name<br>
<br>
If you are logged in to the Coaching Companion, please log out and log back in to see these changes take effect.
<br>
If you have any questions, please contact your administrator.<br>
<br>
Once you are registered with the ECLKC, you can access the OHS Coaching Companion here:<br>
<a href="https://eclkc.ohs.acf.hhs.gov/cc/">Coaching Companion Home</a><br>
<br>
Thank you,<br>
Your OHSCC Administrator<br>
EOF;
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
	global $site;
	$site->mail($mail_attributes);
	

		echo json_encode(array('response' => 'success','message' => 'User Updated','form' => 'create','action' => ''));

	
		return;
	
	}	

	
	/**
	 * Masquerade as user
	 */
	public function masquerade($input) {
	
		global $user;
			
		// pass user id to masquerade as
		$user->make_user_object($input['user_id']);
		
		global $site;
		
		echo json_encode(array('response' => 'success','message' => 'User masquerade','form' => 'masquerade','action' => $site->site_url));
		return;
	
	}	
	
	
	/**
	 * Delete a user
	 */
	public function delete($input) {
	
		global $db;
		global $site;

		// load hooks (this takes the user OUT of the list of users per-group
		if (isset($site->hooks['manage_user_delete'])) {
				foreach($site->hooks['manage_user_delete'] as $hook) {
						call_user_func($hook, $input);
				}
		}	
		// delete user from database
		$where = array('user_id' => $input['user_id']);
		$db->delete('users', $where);
		
		// delete user from database
		$where = array('user_id' => $input['user_id']);
		$db->delete('users_meta', $where);
		
		echo json_encode(array('response' => 'success','message' => 'User has been deleted','form' => 'delete','user_id' => $input['user_id'] ,'action' => ''));
		return;
	
	}
	
	/**
	 * Filter
	 */
	public function filter($input) {
	
		global $site;
// 		$site->log($input.'asdfasdf');
		foreach ($input as $key=>$value) {
			if (strpos($key, 'filter_by_') !== FALSE) {
				$site->add_attributes($key,$value);
			}
		}
		
		$site->add_attributes('pagination_current',$input['pagination_current']);
	
		echo json_encode(array('response' => 'success','message' =>'Filter'));
		return;
	
	}

	/**
	 * get the id numbers of organization and group named New Users
	 */
	public function new_user_id() {
	
		global $db;

		//find default organization and group id's based on name
		$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'New Users'";
		$result = $db->get_data_object($query);
		$new_user_id['organization'] = $result[0]->item_id;
		
		$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'New Users Default'";
		$result = $db->get_data_object($query);
		$new_user_id['group'] = $result[0]->item_id;
		
		return $new_user_id;
	}
	
	
	/**
	 * search for a user
	 */
	public static function search($input) {
		
		global $db;
		global $site;
		global $user;
		
		
		//if search input is less than 3 characters
		if (!isset($input['search']) || strlen($input['search']) < 3) {
			// return a response, but without any results
			echo json_encode(array('response' => 'success','results' => null));
			return;
		}
		
		
		// break into array based on spaces
		$search_values = explode('|',preg_replace('/\s+/','|',$input['search']));
		global $site;

		// create the IN
// 		$role_id_in = "";
// 		foreach (json_decode($site->roles, true) as $key=>$value) {
// 			if ($key >= $user->role_id) {
// 				$role_id_in .= $key . ',';
// 			}
// 		}
// 		$role_id_in = rtrim($role_id_in,',');		
		
		switch ($user->role_id) {
		case "1":
			$role_id_in = '1, 2, 3, 4, 5, 6, 7, 8';
			break;
		case "4":
			$role_id_in = '2, 3, 4, 5, 6, 7, 8';
			break;
		case "5":
			$role_id_in = '2, 3, 5, 6, 7, 8';
			break;
		case "6":
			$role_id_in = '2, 3, 6, 7, 8';
			break;
		default:
			$role_id_in = '3';
		}


		// get all users of specific roles as an array
		$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users WHERE role_id IN (" . $role_id_in . ")";
		$find_users_by_role = $db->get_data_object($query, 0);
		

		// cycle through users
		foreach ($find_users_by_role as $key=>$value) {
			// add user_id to array for the IN contained within database call
			$users_id_in[] = $value['user_id'];
			// and these other values
			$all_users[$value['user_id']]['user_id'] = $value['user_id'];
			$all_users[$value['user_id']]['role_id'] = $value['role_id'];
			$all_users[$value['user_id']]['vector'] = $value['vector'];
			// set for search
			$match[$value['user_id']] = 0;
		}
		
		if (!isset($users_id_in)) {
			echo json_encode(array('response' => 'success','results' => null));
			return;
		}

		$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE minutia='' AND user_id IN (" . implode(",",$users_id_in) . ")";
		$users_meta_data = $db->get_data_object($query, 0);
		
		foreach ($users_meta_data as $key=>$value) {
			// decrypt the values
			$all_users[$value['user_id']][$value['meta_key']] = user::decryption($value['meta_value'], $all_users[$value['user_id']]['vector']);
			// test multiples
			for ($i = 0; $i < count($search_values); $i++) {
				// create a search
				$search = '/^' . $search_values[$i] . '/i';
    			if (preg_match($search, $all_users[$value['user_id']][$value['meta_key']]) && !isset($counter[$value['user_id']][$i])) {
        			// add to specific match
        			$match[$value['user_id']]++;
        			// set a counter to prevent repeats
        			$counter[$value['user_id']][$i] = true;
        			// break so it only counts once for this value
        			break;
    			}
			}
		}
		
		// cycle through match to see if the number is equal to count
		foreach ($match as $match_user_id=>$match_user_value) {
			// unset vector
			unset($all_users[$match_user_id]['vector']);
			// if there are fewer than count, then unset
			if ($match_user_value < count($search_values)) {
				// unset user info if the count is less than the total
				unset($all_users[$match_user_id]);
			}
		}


		$new_user_id = self::new_user_id();

		

// 		$site->log($user->role_id);
		//remove users who do not belong to the same organization, if user is organizationAdmin
		//retain users in new_user
		if ($user->role_id == 5) {
			foreach ($all_users as $key => $value) {
// 			$site->log($value);
				if ($value['organization'] != $user->organization && $value['organization'] != $new_user_id['organization']) {
					unset($all_users[$key]);
				}
			
			}
		}
		
		if ($user->role_id == 6) {
			foreach ($all_users as $key => $value) {
// 			$site->log($value);
				if ($value['group'] != $user->group && $value['group'] != $new_user_id['group']) {
					unset($all_users[$key]);
				}
			
			}
		}

		
		if (count($all_users)) {
			
			$site->add_attributes('search_value',$input['search']);
			$site->add_attributes('user_search_results',json_encode($all_users));
		
			echo json_encode(array('response' => 'success', 'form' => 'edit'));
			return;
		}
		
		$site->add_attributes('search_value',$input['search']);
		$site->add_attributes('user_search_results',null);
		
		echo json_encode(array('response' => 'success','form' => 'edit'));
		return;
	
	}
	
	
		public static function sandbox_search($input = array('search'=>'SANDBOX')) {
		
		global $db;
		global $site;
		global $user;
		
		// hook here
		// manage_users_search
		
		// return whatever
		
		if (!isset($input['search']) || strlen($input['search']) < 3) {
			// return a response, but without any results
			echo json_encode(array('response' => 'success','results' => null));
			return;
		}
		
		// break into array based on spaces
		$search_values = explode('|',preg_replace('/\s+/','|',$input['search']));

		// create the IN
		$role_id_in = "";
		foreach (json_decode($site->roles, true) as $key=>$value) {
			if ($key >= $user->role_id) {
				$role_id_in .= $key . ',';
			}
		}
		$role_id_in = rtrim($role_id_in,',');		
		
		// get all users of specific roles as an array
		$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users WHERE role_id IN (" . $role_id_in . ")";
		$find_users_by_role = $db->get_data_object($query, 0);
		

		// cycle through users
		foreach ($find_users_by_role as $key=>$value) {
			// add user_id to array for the IN contained within database call
			$users_id_in[] = $value['user_id'];
			// and these other values
			$all_users[$value['user_id']]['user_id'] = $value['user_id'];
			$all_users[$value['user_id']]['role_id'] = $value['role_id'];
			$all_users[$value['user_id']]['vector'] = $value['vector'];
			// set for search
			$match[$value['user_id']] = 0;
		}
		
		if (!isset($users_id_in)) {
			echo json_encode(array('response' => 'success','results' => null));
			return;
		}

		$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE minutia='' AND user_id IN (" . implode(",",$users_id_in) . ")";
		$users_meta_data = $db->get_data_object($query, 0);
		
		foreach ($users_meta_data as $key=>$value) {
			// decrypt the values
			$all_users[$value['user_id']][$value['meta_key']] = user::decryption($value['meta_value'], $all_users[$value['user_id']]['vector']);
			// test multiples
			for ($i = 0; $i < count($search_values); $i++) {
				// create a search
				$search = '/^' . $search_values[$i] . '/i';
    			if (preg_match($search, $all_users[$value['user_id']][$value['meta_key']]) && !isset($counter[$value['user_id']][$i])) {
        			// add to specific match
        			$match[$value['user_id']]++;
        			// set a counter to prevent repeats
        			$counter[$value['user_id']][$i] = true;
        			// break so it only counts once for this value
        			break;
    			}
			}
		}
		
		// cycle through match to see if the number is equal to count
		foreach ($match as $match_user_id=>$match_user_value) {
			// unset vector
			unset($all_users[$match_user_id]['vector']);
			// if there are fewer than count, then unset
			if ($match_user_value < count($search_values)) {
				// unset user info if the count is less than the total
				unset($all_users[$match_user_id]);
			}
		}
		
		if (count($all_users)) {
			
			$site->add_attributes('search_value',$input['search']);
			$site->add_attributes('user_search_results',json_encode($all_users));
		
		
// 			echo json_encode(array('response' => 'success', 'form' => 'edit'));
			return json_encode($all_users);
		}
		
		$site->add_attributes('search_value',$input['search']);
		$site->add_attributes('user_search_results',null);
		
		echo json_encode(array('response' => 'success','form' => 'edit'));
		return;
	
	}


	/**
	 * fileds to display when this is created
	 */
	public function recipe_fields($recipe) {
	
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
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