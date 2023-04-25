<?php

class PbcUserSettings extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Pbc User Settings',
			'description' => 'Allows users to update their account',
			'category' => 'pbc'
		);
	}
	
	
	
	
public	function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}





	
	/**
	 *
	 */
	public function as_content($each_component, $vce) {
// $vce->dump($vce->user);
		
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js');
		
		$first_name = isset($vce->user->first_name) ? $vce->user->first_name : null;
		$last_name = isset($vce->user->last_name) ? $vce->user->last_name : null;
		
		$site_roles = json_decode($vce->site->roles, true);
		
		// allow both simple and complex role definitions
		$user_role = is_array($site_roles[$vce->user->role_id]) ? $site_roles[$vce->user->role_id]['role_name'] : $site_roles[$vce->user->role_id];
		
		// create a special dossier
		$dossier_for_password = $vce->user->encryption(json_encode(array('type' => 'PbcUserSettings','procedure' => 'password')),$vce->user->session_vector);		
		$dossier_for_update = $vce->user->encryption(json_encode(array('type' => 'PbcUserSettings','procedure' => 'update')),$vce->user->session_vector);		
		// $vce->dump($vce->user->native_org_group);
		// $vce->dump($vce->user->org_group_list);
		// $vce->dump($vce->user->user_id);
		//use or create $vce->user->org_group_list and $vce->user->native_org_group
		$vce->user->org_group_list = isset($vce->user->org_group_list) ? $vce->user->org_group_list : json_encode(array($vce->user->organization=>array($vce->user->group=>$vce->user->role_hierarchy)));
		$vce->user->native_org_group = isset($vce->user->native_org_group) ? $vce->user->native_org_group : json_encode(array($vce->user->organization=>array($vce->user->group=>$vce->user->role_hierarchy)));
		// make compound list of all available organizations, groups and roles
		// $vce->dump($vce->user);
		$org_group_role_list = json_encode(json_decode($vce->user->org_group_list, true) + json_decode($vce->user->native_org_group, true));
		// $vce->dump($org_group_role_list);

		

		// $vce->dump($org_group_role_list);
		// $vce->dump($user);
		// 	$vce->dump(json_decode($org_group_role_list, true));	
		$page_site_roles = $vce->site->roles;
		// $vce->dump($vce->site->roles);
		$user_email = $vce->user->email;


				// update password
				if ($vce->user->role_hierarchy < 3 && (!isset($user_attributes['password']) || !isset($user_attributes['password']['type']) || $user_attributes['password']['type'] != 'conceal')) {

					// password input
					$input = array(
					'type' => 'password',
					'name' => 'password',
					'class' => 'password-input',
					'data' => array(
						'tag' => 'required',
						'placeholder' => 'Enter Password'
					)
					);
				
					$password_input = $vce->content->create_input($input,'Enter A New Password','Enter Your Password');
		
					// password input
					$input = array(
					'type' => 'password',
					'name' => 'password2',
					'class' => 'password-input',
					'data' => array(
						'tag' => 'required',
						'placeholder' => 'Repeat Password'
					)
					);
				
					$password2_input = $vce->content->create_input($input,'Repeat New Password','Repeat Password');
		
					$password_update = <<<EOF
		<form id="password" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
		<input type="hidden" name="dossier" value="$dossier_for_password">
		$password_input
		$password2_input
		<input type="submit" value="Update">
		<label class="ignore" style="color:#666;"><input class="show-password-input" type="checkbox" name="show-password"> Show Password</label>
		</form>
EOF;
		
					$password_content = $vce->content->accordion('Update Your Password', $password_update);
		
				}

				

		// first name input
		$input = array(
			'type' => 'text',
			'name' => 'first_name',
			'value' => $first_name,
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$first_name_input = $vce->content->create_input($input,'First Name');

		// last name input
		$input = array(
			'type' => 'text',
			'name' => 'last_name',
			'value' => $last_name,
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$last_name_input = $vce->content->create_input($input,'Last Name');

		// e-mail input
		$input = array(
			'type' => 'hidden',
			'name' => 'email',
			'value' => $user_email,
			'flags' => array(
				'prepend' => $user_email,
			),
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$email_input = $vce->content->create_input($input,'Email (Cannot be edited)', 'Email is required', 'add-padding hidden-input');


		// role input
		$input = array(
			'type' => 'hidden',
			'name' => 'role',
			'value' => $user_role,
			'flags' => array(
				'prepend' => $user_role,
			),
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$role_input = $vce->content->create_input($input,'Role', 'Role is required', 'add-padding hidden-input');

		$content = <<<EOF
<div>Review information about your personal data, organization, group, and role.

 You can edit your name, but any other changes must be made by your administrator.</div>
 $password_content
<form id="update" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
<input type="hidden" name="email" value="$user_email">
<script>
org_group_role_list = $org_group_role_list;
pagesiteroles = $page_site_roles;
</script>


$first_name_input
$last_name_input
$email_input
EOF;

$vce->user->component_name = 'usersettings';
// add hooks
if (isset($vce->site->hooks['manage_users_attributes2'])) {
	foreach($vce->site->hooks['manage_users_attributes2'] as $hook) {
		$content .= call_user_func($hook, $vce->user);
	}
}

$content .= <<<EOF
$role_input
EOF;




		$content .=<<<EOF
<button type="submit" class="button__primary">Update</button>
<button type="reset" class="button__primary">Reset</button>
EOF;
// $vce->dump($vce->user->role_hierarchy);
if($vce->user->role_hierarchy < 6) {
// $vce->dump($site);
// 	$url =  $vce->site->url.'request_access';
// $content .= <<<EOF
// <button class="request_access button__primary" type="reset" url="$url">Request Access</button>
// EOF;

}

$content .= <<<EOF
</form>
EOF;


$notifications_link = $vce->site->site_url . '/notifications';
$content .= <<<EOF
<a href="$notifications_link"><button type="link" class="button__primary">See Notifications</button></a>
<br>
<span>Email Notifications have been suspended because server-created emails often don't arrive in the recipients' inbox.<br>
Please use personal email to communicate with Coaching Companion participants and Site Messages which partipants can read in their My Account page.</span>
EOF;

		$vce->content->add('main', $content);

		// hook for allowing multiple types of notifications
		if (isset($vce->site->hooks['user_settings_as_content'])) {
			foreach($vce->site->hooks['user_settings_as_content'] as $hook) {
				call_user_func($hook);
			}
		}
	
	}


	
	/**
	 *
	 */
	public function check_access($each_component, $vce) {

	
		if (isset($vce->user->user_id)) {
		
			return true;
		
		}
		
		// in the event that a user is not logged in, redirect to top of site
		
		global $site;
		// to front of site
		header('location: ' . $vce->site->site_url);

	}
	
	
	/**
	 *
	 */
	public function password($input) {
	
		global $vce;
	
		if ($input['password'] != $input['password2']) {
			echo json_encode(array('response' => 'error','message' => 'Passwords do not match','action' => ''));
			return;
		}
		
		// call to user class to create_hash function
		$hash = $vce->user->create_hash($vce->user->email, $input['password']);
			
		// update hash
		$update = array('hash' => $hash);
		$update_where = array( 'user_id' => $vce->user->user_id);
		$vce->db->update('users', $update, $update_where);

		echo json_encode(array('response' => 'success','message' => 'Password Updated','action' => ''));
		return;
		
	}


	public function update($input) {
	
    	$vce = $this->vce;

    	unset($input['role_id']);
		
    	$response = $vce->user->update($input);

    	$response['form'] = 'create';
    	
    	echo json_encode($response);
    	
    	return;
    	
    }
	
	/**
	 *
	 */
	public function updateBAK($input) {
	
		global $vce;


		$user_id = $vce->user->user_id;
		
		// we don't need to store the type, so unset this
		unset($input['type']);
		
		// get user vector
		$query = "SELECT vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "' LIMIT 1";
		$user_vector = $vce->db->get_data_object($query);
		
		// set
		$vector = $user_vector[0]->vector;
		
		// check if email has been changed
		if ($input['email'] != $vce->user->email) {
			$input['email'] = filter_var(strtolower($input['email']), FILTER_SANITIZE_EMAIL);
			if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
				echo json_encode(array('response' => 'error','message' => 'Not a valid email address','form' => 'create', 'action' => ''));
				return;
			}
			
			// create lookup
			$lookup = $vce->user->lookup($input['email']);
			
			// get user vector
			$query = "SELECT id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and meta_value='" . $lookup . "' LIMIT 1";
			$lookup_check = $vce->db->get_data_object($query);
			
			if (!empty($lookup_check)) {
				echo json_encode(array('response' => 'error','message' => 'Email is already in use','form' => 'create', 'action' => ''));
				return;
			}
				
			// call to user class to create_hash function
			$hash = $vce->user->create_hash($vce->user->email, $input['password']);
			
			$query = "SELECT user_id FROM  " . TABLE_PREFIX . "users WHERE hash='" . $hash . "' LIMIT 1";
			$password_check = $vce->db->get_data_object($query);
			
			// check that password is correct
			if (empty($password_check)) {
				echo json_encode(array('response' => 'error','message' => 'Password is not correct','form' => 'create', 'action' => ''));
				return;
			}
			
			// call to user class to create_hash function
			$hash = $vce->user->create_hash($input['email'], $input['password']);
			
			// update hash
			$update = array( 'hash' => $hash);
			$update_where = array( 'user_id' => $vce->user->user_id);
			$vce->db->update( 'users', $update, $update_where );
			
			// update hash
			$update = array( 'meta_value' => $lookup);
			$update_where = array('user_id' => $vce->user->user_id, 'meta_key' => 'lookup');
			$vce->db->update( 'users_meta', $update, $update_where );
			
		}
		
		unset($input['password']);
		
		// delete old meta data
		// don't add role_id to meta_data; write it into the users table
		if(isset($input['role_id'])) {
			$update = array( 'role_id' => $input['role_id']);
			$update_where = array( 'user_id' => $vce->user->user_id);
			$vce->db->update( 'users', $update, $update_where );

			unset($input['role_id']);
		}
		
		foreach ($input as $key => $value) {
				
			// delete user meta from database
			$where = array( 'user_id' => $user_id, 'meta_key' => $key);
			$vce->db->delete('users_meta', $where);
		
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
		
		$vce->db->insert('users_meta', $records);


// test for how to preserve object properties
/*
$current_user = $vce->user->user_id;
$vce->log('purge_log');

$vce->log('user');
$vce->log($vce->user->session_vector);

$before = $user;
// $vce->log('before');
// $vce->log($before);
$clone = clone $user;

$beforearray = (array) $user;
// $vce->log('beforearray');
// $vce->log($beforearray);

$govarray = get_object_vars($user);
$vce->log('govarray');
$vce->log($govarray['session_vector']);


$vce->user->make_user_object($current_user);
$after = $user;
$vce->log('after');
$vce->log($after->session_vector);
/////////////////
$vce->log('user');
$vce->log($vce->user->session_vector);

$vce->log('before');
$vce->log($before->session_vector);

$vce->log('clone');
$vce->log($clone->session_vector);

$vce->log('govarray');
$vce->log($govarray['session_vector']);

$vce->log('beforearray');
$vce->log($beforearray['session_vector']);

*/


		// reload user object (changes session data)
		$vce->user->make_user_object($user_id);
		// $vce->log($_SESSION['user']);
		echo json_encode(array('response' => 'success','message' => 'User Settings Updated','action' => ''));
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
<div class="label-message">URL (optional)</div>
<div class="label-error">Enter a URL</div>
</div>
</label>
EOF;

		return $elements;
		
	}

}