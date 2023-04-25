<?php

class Sandbox_login extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'HSCC Sandbox Login',
			'description' => 'Login for Sandbox with access keyss and automatic account creation.',
			'category' => 'site',
			'recipe_fields' => array('auto_create',
			'title',
			'template',
			'login_disabled' => array(
				'label' => array('message' => 'Login Disabled'),
				'type' => 'checkbox',
				'name' => 'login_disabled',
				'selected' => isset($recipe['login_disabled']) ? $recipe['login_disabled'] : null,
				'flags' => array (
				'label_tag_wrap' => 'true'
				),
				'options' => array(
				'label' => 'Login Disabled', 'value' => 'true'
				)
			)
			)
		);
	}


	
	


	/**
	 * check if get_sub_components should be called.
	 * @return bool
	 */
	public function find_sub_componets($requested_component, $vce) {
	
		// if user has not logged in, return false
		if (!isset($vce->user->user_id)) {
		
			return false;
		
		}
	
		// return true if user has logged in
		return true;
	}
	
	

	

	
		
	/**
	 * This takes $_SERVER['REMOTE_USER'] which is set by the system login 
	 */
	public function check_access($each_component, $vce) {





		
		// allows for a way to disable a login component while keeping it in the recipe
		if (isset($each_component->recipe['login_disabled'])) {
			return true;
		}

		if (isset($_REQUEST['logout'])) {
			session_destroy();
			return;
		}





// $vce->dump('sandbox login');

		//if there is no session with user_id
		if (!isset($vce->user->user_id)) {

			// hook that can be used to extend this method
			// login_check_access_false
			if (isset($vce->site->hooks['login_check_access_false'])) {
				foreach($vce->site->hooks['login_check_access_false'] as $hook) {
					call_user_func($hook, $each_component, $vce);
				}
			}
			
			//add javascript
			$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery');
			
			// the instructions to pass through the form with specifics
			$dossier = array(
			'type' => 'Sandbox_login',
			'procedure' => 'form_input',
			'component_id' => $each_component->component_id,
			'requested_url' => rtrim($vce->requested_url,'/')
			);

			// add dossier, which is an encrypted json object of details uses in the form
			$each_component->dossier = $vce->user->encryption(json_encode($dossier),$vce->user->session_vector);


			// email input
			$input = array(
				'type' => 'text',
				'name' => 'access_key',
				'data' => array(
					'autocapitalize' => 'none',
					'tag' => 'required',
					'placeholder' => 'Enter Your Access Key'
				)
				);
			
			$access_key_input = $vce->content->create_input($input,'Access Key','Enter Your Access Key');


			// email input
			$input = array(
			'type' => 'text',
			'name' => 'email',
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
				'placeholder' => 'Enter Your Email Address'
			)
			);
		
			$email_input = $vce->content->create_input($input,'Email','Enter Your Email');

			// // password input
			// $input = array(
			// 'type' => 'password',
			// 'name' => 'password',
			// 'class' => 'password-input',
			// 'data' => array(
			// 	'tag' => 'required',
			// 	'placeholder' => 'Enter Your Password'
			// )
			// );

			
		
			// $password_input = $vce->content->create_input($input,'Password','Enter Your Password');
// $vce->dump($this->configuration['access_key_coachee']);
// $roles = json_decode($vce->site->roles, true);

// $vce->dump($roles);
			$content = <<<EOF
<div>
Please enter the access key you received. <br><br>
If you already have an account on this site, you can log in using your email and access key.<br>
If you do not yet have an account, enter the access key and the email you would like to use and an account will be created for you.
</div>
<form id="login_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$each_component->dossier">
$access_key_input
$email_input
<input type="submit" value="Click here to Login">
</form>
EOF;

			$add_content = $vce->content->accordion('Login', $content, true, true);

			// add content
			$vce->content->add('main', $add_content);
	
			return false;
	

				
				
				} else {
					// login_check_access_true
					// method should return true of false
					// if (isset($vce->site->hooks['login_check_access_true'])) {
					// 	foreach($vce->site->hooks['login_check_access_true'] as $hook) {
					// 		return call_user_func($hook, $each_component, $vce);
					// 	}
					// }
				
					return true;			
				}
			


	}
	

	/**
	 * Instead of going all the way through form_input in class.component.php, we just do everything here in the child.
	 */
	public function form_input($input) {
	
		$vce = $this->vce;
		
		$input['email'] = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
		if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
			echo json_encode(array('response' => 'error','message' => 'Not a valid email address','action' => 'clear'));
			return;
		}


		$email = $input['email'];
		$parts = explode('@', $email);
		$first_name  = $parts[0] . '-Sandbox';
		$last_name = 'User';
		
		//$vce->page->requested_id = $input['component_id'];
		//$vce->page->requested_url = $input['requested_url'];
		
		//unset($vce->page->post_variables);
		
		// send array to user login
		// $success = $vce->user->login($input);

		$roles = json_decode($vce->site->roles, true);
		$role_name_array = array();
		foreach ($roles as $k=>$v){
			$role_name_array[$v['role_name']] = $k;
		}

		$access_keys = array(
			$this->configuration['access_key_coachee'] => $role_name_array['Coachee'],
			$this->configuration['access_key_coach'] => $role_name_array['Coach'],
			$this->configuration['access_key_group_admin'] => $role_name_array['GroupAdmin'],
			$this->configuration['access_key_org_admin'] => $role_name_array['OrganizationAdmin']
		);



		if (array_key_exists($input['access_key'], $access_keys)) {

			$user_id = $vce->user->email_to_id($email);
				
			//if there is such a user, create user object and continue
			if (!empty($user_id)) {

				$vce->user->make_user_object($user_id);

				//add javascript
				$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery');

				echo json_encode(array('response' => 'success','message' => 'Welcome Back!','action' => 'reload','url' => $vce->site->site_url . '/' . $input['requested_url']));
				return;
			}

		
		

		
	//if  there is no such user, create one.

	$role_id = $access_keys[$input['access_key']];
	// $password = $input['password'];

	//add new user to default group and organization
	//find default organization and group id's based on name
	$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'Sandbox'";
	$result = $vce->db->get_data_object($query);
	$organization_id = $result[0]->item_id;
	
	$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'Default Sandbox Group'";
	$result = $vce->db->get_data_object($query);
	$group_id = $result[0]->item_id;

	$roles = json_decode($vce->site->roles, true);


	$attributes = array(
		'first_name' => $first_name,
		'last_name' => $last_name,
		'email' => $email,
		'organization' => $organization_id,
		'group' => $group_id,
		'password' => time() . 'temp_pw!',
		'role_id' => $role_id
	);

	// echo json_encode(array('response' => 'success','message' => 'data '. var_dump($attributes),'action' => 'reload','url' => $vce->site->site_url . '/' . $input['requested_url']));
	// return;
		$create_user_request = $vce->user->create($attributes);
		$vce->log($create_user_request);
		$vce->log($create_user_request['user_id']);

				// this adds all new users to any cycle with "do not edit" in the title
				$query = "SELECT id, meta_value FROM vce_components_meta WHERE meta_key = 'cycle_participants' AND component_id IN (SELECT component_id FROM vce_components_meta WHERE meta_key = 'pbccycle_name' AND meta_value LIKE '%do not edit%')";
				$result = $vce->db->get_data_object($query);
				// $vce->dump($result);
				$new_user_id = $create_user_request['user_id'];
				foreach ($result as $k=>$v) {
					foreach ($v as $kk=>$vv) {
						// $vce->dump($kk);
						if ($kk == 'id'){
							$row_id = $vv;
						}
						if ($kk == 'meta_value'){
							$user_array = explode('|', trim($vv, '|'));
							// $vce->dump($user_array);
							if (!in_array($new_user_id, $user_array)){
								$user_array[] = $new_user_id;
							}
							$user_list = implode('|', $user_array);
						}
					}
					// $vce->dump($user_list);
					$query = "UPDATE vce_components_meta SET meta_value = '$user_list'  WHERE id = $row_id";
					$result = $vce->db->query($query);
				}


		$vce->user->make_user_object($create_user_request['user_id']);

		//add javascript
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery');


		echo json_encode(array('response' => 'success','message' => 'An account for user '. $email . ' has been created.','action' => 'reload','url' => $vce->site->site_url . '/' . $input['requested_url']));
		return;
	}

	echo json_encode(array('response' => 'error', 'message' => 'Your access key is incorrect','action' => 'reload','url' => $vce->site->site_url . '/' . $input['requested_url']));
	return;

}


	/*
 add config info for this component
*/
public function component_configuration() {
	global $vce;
	$content = NULL;
	$content .= <<<EOF
	<div>
		Sandbox users will be required to use an access key to log in.<br>
		If they don't already have an account, one will be created using the provided email.<br>
		These access keys govern the role an automatically-created user will have.<br>
		<br>
	</div>
EOF;

	$encrypted_data_input = array(
	'type' => 'text',
	'name' => 'access_key_coachee',
	'options' => array(
		'value' => ((isset($this->configuration['access_key_coachee']) && $this->configuration['access_key_coachee'] != '') ? $this->configuration['access_key_coachee'] :  'defaultaccesskey_coachee'),
		)
	);
	$content .= $vce->content->create_input($encrypted_data_input,'Access Key for Coachee');

	$encrypted_data_input = array(
		'type' => 'text',
		'name' => 'access_key_coach',
		'options' => array(
			'value' => ((isset($this->configuration['access_key_coach']) && $this->configuration['access_key_coach'] != '') ? $this->configuration['access_key_coach'] :  'defaultaccesskey_coach'),
			)
		);
		$content .= $vce->content->create_input($encrypted_data_input,'Access Key for Coach');

		$encrypted_data_input = array(
			'type' => 'text',
			'name' => 'access_key_group_admin',
			'options' => array(
				'value' => ((isset($this->configuration['access_key_group_admin']) && $this->configuration['access_key_group_admin'] != '') ? $this->configuration['access_key_group_admin'] :  'defaultaccesskey_groupadmin'),
				)
			);
		$content .= $vce->content->create_input($encrypted_data_input,'Access Key for Group Admin');

		$encrypted_data_input = array(
			'type' => 'text',
			'name' => 'access_key_org_admin',
			'options' => array(
				'value' => ((isset($this->configuration['access_key_org_admin']) && $this->configuration['access_key_org_admin'] != '') ? $this->configuration['access_key_org_admin'] :  'defaultaccesskey_orgadmin'),
				)
			);
		$content .= $vce->content->create_input($encrypted_data_input,'Access Key for Org Admin');

		$encrypted_data_input = array(
			'type' => 'text',
			'name' => 'access_key_main',
			'options' => array(
				'value' => ((isset($this->configuration['access_key_main']) && $this->configuration['access_key_main'] != '') ? $this->configuration['access_key_main'] :  'Admin_cde3'),
				)
			);
		$content .= $vce->content->create_input($encrypted_data_input,'Access Key Which Does Not Change for Admins');

	return $content;
}


	

}