<?php

class Pbc_usermanagement extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC User Management',
			'description' => 'Add, edit and delete site users. You can also masquerade as them using this component. Like the site Manage Users but customized.',
			'category' => 'pbc'
		);
	}
	
	/**
	 *
	 */
	public function as_content($each_component, $vce) {
	
		global $db;
		
		// need to add user_attributes
		
		// check if value is in page object
		$user_id = isset($vce->page->user_id) ? $vce->page->user_id : null;
		
		$roles = json_decode($vce->site->roles, true);	

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter');	
		
		// initialize array to store users
		$site_users = array();

		$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users";
		$all_users = $db->get_data_object($query);

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
				$value = $vce->user->decryption($each_metadata->meta_value, $each_user->vector);

				// add the values into the user object	
				$user_object[$each_metadata->meta_key] = $value;		
			}
			
			// save into site_users array
			$site_users[$each_user->user_id] = (object) $user_object;

		}

		if (isset($user_id)) {
			// update an exisiting user

			$user_info = $site_users[$user_id];
			
			$dossier_for_update = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'update','user_id' => $user_id)),$vce->user->session_vector);
			
			$first_name = isset($user_info->first_name) ? $user_info->first_name : null;
			$last_name = isset($user_info->last_name) ? $user_info->last_name : null;

$content = <<<EOF
<p>
<div class="clickbar-container">
<div class="clickbar-content clickbar-open">
<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
<input type="hidden" name="email" value="$vce->user->email">
<label>
<div class="input-padding">
$user_info->email
</div>
<div class="label-text">
<div class="label-message">Email</div>
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

			// load hooks
			if (isset($vce->site->hooks['user_attributes'])) {
				foreach($vce->site->hooks['user_attributes'] as $hook) {
					$content .= call_user_func($hook, $user_info);
				}
			}

$content .= <<<EOF
<label>
<select name="role_id" tag="required">
<option value=""></option>
EOF;

			foreach (json_decode($vce->site->roles, true) as $key => $value) {
				if ($vce->user->user_role <= $key) {
					$content .= '<label for=""><option value="' . $key . '"';
					if ($key == $user_info->role_id) {
						$content .= ' selected';
					}
					$content .= '>' . $value . '</option>';
				}
			}
		
$content .= <<<EOF
</select>
<div class="label-text">
<div class="label-message">Role</div>
<div class="label-error">Enter your Role</div>
</div>
</label>
<input type="submit" value="Update User">
</form>
</div>
<div class="clickbar-title disabled"><span>Update An Existing User</span></div>
</div>
</p>
EOF;


		} else {
		// to create a new user
		
			$dossier_for_create = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'create')),$vce->user->session_vector);

$content = <<<EOF
<p>
<div class="clickbar-container">
<div class="clickbar-content">
<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_create">

<label>
<input type="text" name="email" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Email</div>
<div class="label-error">Enter Email</div>
</div>
</label>

<label>
<input type="text" name="password" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Password</div>
<div class="label-error">Enter your Password</div>
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
			if (isset($vce->site->hooks['user_attributes'])) {
				foreach($vce->site->hooks['user_attributes'] as $hook) {
					$content .= call_user_func($hook, $content);
				}
			}

$content .= <<<EOF
<label>
<select name="role_id" tag="required">
<option value=""></option>
EOF;

			foreach ($roles as $key => $value) {
				// allow both simple and complex role definitions
				$role_name = is_array($value) ? $value['role_name'] : $value;
				$content .= '<label for=""><option value="' . $key . '">' . $role_name . '</option>';
				
			}
		
$content .= <<<EOF
</select>
<div class="label-text">
<div class="label-message">Role</div>
<div class="label-error">Enter your Role</div>
</div>
</label>
<input type="submit" value="Create User">
<div id="generate-password" class="link-button">Generate Password</div>
</form>
</div>
<div class="clickbar-title clickbar-closed"><span>Create A New User!</span></div>
</div>
</p>
EOF;

		}
		
		$user_attributes_list = array('user_id','last_name','first_name','email');

		// load hooks
		if (isset($vce->site->hooks['user_attributes_list'])) {
			foreach($vce->site->hooks['user_attributes_list'] as $hook) {
				$user_attributes_list = call_user_func($hook, $user_attributes_list);
			}
		}

// list site users
$content .= <<<EOF
<p>
<div class="clickbar-container">
<div class="clickbar-content no-padding clickbar-open">

<table id="users" class="tablesorter">
<thead>
<tr>
<th></th>
<th></th>
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


		foreach ($site_users as $each_site_user) {
		
			// allow both simple and complex role definitions
			$user_role = is_array($roles[$each_site_user->role_id]) ? $roles[$each_site_user->role_id]['role_name'] : $roles[$each_site_user->role_id];
			
			if ($each_site_user->user_id == "1") {

$content .= <<<EOF
<tr>
<td></td>
<td></td>
<td></td>
<td>$user_role</td>
EOF;

			} else {
			
				$dossier_for_edit = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'edit','user_id' => $each_site_user->user_id)),$vce->user->session_vector);
				$dossier_for_masquerade = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'masquerade','user_id' => $each_site_user->user_id)),$vce->user->session_vector);
				$dossier_for_delete = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'delete','user_id' => $each_site_user->user_id)),$vce->user->session_vector);


$content .= <<<EOF
<tr>
<td class="align-center">
<form class="inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_edit">
<input type="submit" value="Edit">
</form>
</td>
<td class="align-center">
<form class="inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_masquerade">
<input type="submit" value="Masquerade">
</form>
</td>
<td class="align-center">
<form class="delete-form inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<input type="submit" value="Delete">
</form>
</td>
<td>$user_role</td>
EOF;

			}
			
			
			foreach ($user_attributes_list as $each_user_attribute) {

				$content .= '<td>' . $each_site_user->$each_user_attribute . '</td>';

			}

$content .= <<<EOF
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

		$vce->page->content->add('main', $content);
	
	
	}

	
	/**
	 * Create a new user
	 */
	public function create($input) {
	
		global $db;
		global $site;
		global $vce;
	
		// remove type so that it's not created for new user
		unset($input['type']);
	
		// test email address for validity
		$input['email'] = filter_var(strtolower($input['email']), FILTER_SANITIZE_EMAIL);
		if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
			echo json_encode(array('response' => 'error','message' => 'Email is not a valid email address','form' => 'create', 'action' => ''));
			return;
		}
		
		$lookup = $vce->user->lookup($input['email']);
		
		// check
		$query = "SELECT id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and meta_value='" . $lookup . "'";
		$lookup_check = $db->get_data_object($query);
		
		if (!empty($lookup_check)) {
			echo json_encode(array('response' => 'error','message' => 'Email is already in use','form' => 'create', 'action' => ''));
			return;
		}
		
		// call to user class to create_hash function
		$hash = $vce->user->create_hash($input['email'], $input['password']);
		
		// get a new vector for this user
		$vector = $vce->user->create_vector();

		$user_data = array(
		'vector' => $vector, 
		'hash' => $hash,
		'role_id' => $input['role_id']
		);
		$user_id = $db->insert('users', $user_data);
		
		unset($input['procedure']);
		unset($input['password']);
		unset($input['role_id']);
				
		// now add meta data

		$records = array();
				
		$lookup = $vce->user->lookup($input['email']);
		
		$records[] = array(
		'user_id' => $user_id,
		'meta_key' => 'lookup',
		'meta_value' => $lookup,
		'minutia' => 'false'
		);
		
		foreach ($input as $key => $value) {

			// encode user data			
			$encrypted = $vce->user->encryption($value, $vector);
			
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
		if (isset($vce->site->hooks['manage_user_create'])) {
				foreach($vce->site->hooks['manage_user_create'] as $hook) {
						call_user_func($hook, $hook_input);
				}
		}
                
		echo json_encode(array('response' => 'success','message' => 'User has been created','form' => 'create','action' => ''));
		return;
	}

	/**
	 * edit user
	 */
	public function edit($input) {

		// add attributes to page object for next page load using session
		global $site;
		global $vce;
		
		$vce->site->add_attributes('user_id',$input['user_id']);
	
		
		echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
		return;
		
	}

	/**
	 * update user
	 */
	public function update($input) {
	
		global $db;
		global $site;
		global $vce;
							
		// load hooks (this hook adds the updated user group to the list of users in that group in the datalist
		if (isset($vce->site->hooks['manage_user_update'])) {
				foreach($vce->site->hooks['manage_user_update'] as $hook) {

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
			$encrypted = $vce->user->encryption($value, $vector);
			
			$records[] = array(
			'user_id' => $user_id,
			'meta_key' => $key,
			'meta_value' => $encrypted,
			'minutia' => null
			);
			
		}
		
		$db->insert('users_meta', $records);
				
		echo json_encode(array('response' => 'success','message' => 'User Updated','form' => 'create','action' => ''));
		return;
	
	}	

	
	/**
	 * Masquerade as user
	 */
	public function masquerade($input) {
	
		global $user;
		global $vce;
			
		// pass user id to masquerade as
		$vce->user->make_user_object($input['user_id']);
		
		global $site;
		
		echo json_encode(array('response' => 'success','message' => 'User masquerade','form' => 'masquerade','action' => $vce->site->site_url));
		return;
	
	}	
	
	
	/**
	 * Delete a user
	 */
	public function delete($input) {
	
		global $db;
		global $site;
		global $vce;

		// load hooks (this takes the user OUT of the list of users per-group
		if (isset($vce->site->hooks['manage_user_delete'])) {
				foreach($vce->site->hooks['manage_user_delete'] as $hook) {
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
	 * fileds to display when this is created
	 */
	public function recipe_fields($recipe) {
	
		$title = isset($recipe['title']) ? $recipe['title'] : $this->component_info()['name'];
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