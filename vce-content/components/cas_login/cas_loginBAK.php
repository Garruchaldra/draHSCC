<?php

class Cas_Login extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'OHS CAS Login',
			'description' => 'Login with external OHS CAS authentication.',
			'category' => 'site'
		);
	}


	
	public function external_cas_login() {

		return true;
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
	
	

	
	private function caslogout () {
// 				$content = <<<EOF
// You don't currently have permission to view this content.<br>
// Please contact your site administrator to ask about access.<br>
// EOF;
// 
// echo $content;
// return;

				require_once BASEPATH .'/vce-content/components/cas_login/CAS/cas_config.php';
				// Load the CAS lib
				require_once $phpcas_path . '/CAS.php';

				// Enable debugging
				// phpCAS::setDebug('debug.txt');
				// Enable verbose error messages. Disable in production!
				phpCAS::setVerbose(false);
				// Initialize phpCAS
				phpCAS::client(CAS_VERSION_3_0, $cas_host, $cas_port, $cas_context, false);
				phpCAS::setNoCasServerValidation();
				//phpCAS::logout();
				phpCAS::logoutWithRedirectService('https://eclkc.ohs.acf.hhs.gov');
				return;	
	}
	
		
	/**
	 * This takes $_SERVER['REMOTE_USER'] which is set by the system login 
	 */
	public function check_access($each_component, $vce) {

	// // get user_id which has been saved for masquerade
	// 	$query = "SELECT a.meta_value AS masquerade_id, b.meta_value AS default_id FROM " . TABLE_PREFIX . "site_meta AS a JOIN " . TABLE_PREFIX . "site_meta AS b ON a.minutia = b.minutia WHERE a.meta_key='masquerade_id' AND a.meta_key='default_id'";
	// 	$masquerade_user_id = $vce->db->get_data_object($query);
	
	// 	// user_id has already been created, but no other user meta information has been created
	// 	if (isset($masquerade_user_id[0]->masquerade_id) && $masquerade_user_id[0]->masquerade_id != '') {
	// 		$vce->user->make_user_object($masquerade_user_id[0]->masquerade_id);
	// 	} else {
	// 		// echo 'not there';
	// 	}
		
		

// 	$vce->dump($_SESSION['phpCAS']);
// // 	$vce->dump($each_component);
// 	exit;
	


		
		if (isset($_REQUEST['logout'])) {
			session_destroy();
			$this::caslogout();
			return;
		}

		//check to see if maintenance page is set.
		//if so, show maintenance page and exit.

		if (isset($vce->user->user_id) && $vce->user->role_heirarchy = 1) {
			// keep going no matter what
		} else {
			$query = "SELECT meta_value FROM " . TABLE_PREFIX . "site_meta WHERE meta_key = 'maintenance_status'";
			$result = $vce->db->get_data_object($query);
				// $vce->dump($result[0]->meta_value);
			if (isset($result[0]->meta_value) && $result[0]->meta_value == 'in_maintenance') {
				require(dirname(__FILE__).'/includes/maintenance.php');
			}
		}


		                // //TEST
						// $casid_name = 'D ayton ra@uw.edu';
						// $casid_name = strtolower($casid_name);
						// $casid_name = filter_var($casid_name, FILTER_SANITIZE_STRING);
						// $vce->dump($casid_name);
						// //if user is CAS logged in, check for vce credentials using email
						// $user_id = $vce->user->email_to_id($casid_name);
						// $vce->dump($user_id);

						// 	$first_name = ucwords(strtolower('Dayton'));
						// 	$first_name = filter_var($first_name, FILTER_SANITIZE_STRING);
						// 	$vce->dump($first_name);
						// 	$last_name = ucwords(strtolower("O'malley"));
							
						// 	$last_name = filter_var($last_name, FILTER_SANITIZE_STRING);
						// 	$vce->dump($last_name);
						// 	$UID = 'asdfai8d*';
						// 	$UID = filter_var($UID, FILTER_SANITIZE_STRING);
						// 	$vce->dump($UID);


		//if there is no session with user_id
		if (!isset($vce->user->user_id)) {
		
			//Check CAS only once
			if (!isset($_SESSION['phpCAS'])) {
				//if there is no registered user, invoke phpCAS and the user will be redirected to the central CAS login site
				//Invoke CAS library to find status of CAS Login:
				// Load the settings from the central config file
				require_once  BASEPATH .'/vce-content/components/cas_login/CAS/cas_config.php';
				// Load the CAS lib
				require_once $phpcas_path . '/CAS.php';

				// Enable debugging
				// phpCAS::setDebug('debug.txt');
				// Enable verbose error messages. Disable in production!
				phpCAS::setVerbose(true);
				// Initialize phpCAS 3.0
				phpCAS::client(CAS_VERSION_3_0, $cas_host, $cas_port, $cas_context, false);
				phpCAS::setNoCasServerValidation();
				phpCAS::forceAuthentication();
// 				if (isset($_REQUEST['ticket'])) {
// 					$_SESSION['cas'] = phpCAS::getAttributes();
// 					$_SESSION['cas_login_status'] = 'logged_in';
// 				}
			}

			// $vce->dump($_SESSION['phpCAS']);
			// 	$vce->dump($each_component);
				// exit;

			//If CAS gives us the user attributes, that means CAS worked and is sending an auth. ticket
			// $vce->dump($_SESSION);
			if (isset($_SESSION['phpCAS']['attributes']['mail'])) {

				//get email from CAS user attributes
				$casid_name = $_SESSION['phpCAS']['attributes']['mail'];
				$casid_name = strtolower($casid_name);
				$casid_name = str_replace(["'", '"'], ['&#39;', '&#34;'], preg_replace('/\x00|<[^>]*>?/', '', $casid_name));
				// $casid_name = filter_var($casid_name, FILTER_SANITIZE_STRING);
				//if user is CAS logged in, check for vce credentials using email
				$user_id = $vce->user->email_to_id($casid_name);
				
				//if there is such a user, create user object and continue
				if (!empty($user_id)) {

					$vce->user->make_user_object($user_id);

					// add CAS UID (which is the registered username rather than the email) if it doesn't exist)
					if (isset($user_id) && isset($_SESSION['phpCAS']['attributes']['uid'])) {

							//find out if it already exists
							$query = "SELECT meta_value FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='UID'";
							$uid_lookup = $vce->db->get_data_object($query);
							if (!isset($uid_lookup[0]->meta_value)) {
								// insert if it doesn't exist
								$UID = $_SESSION['phpCAS']['attributes']['uid'];
								$UID = filter_var($UID, FILTER_SANITIZE_STRING);

								$records[] = array(
									'user_id' => $user_id,
									'meta_key' => 'UID',
									'meta_value' => $vce->user->encryption($UID,$vector),
									'minutia' => null
									);	
	
								$vce->db->insert('users_meta', $records);

							}

						}

					//add javascript
					$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery');

					return;
				
				
				} else {

					//if the CAS authentication worked but there is no such user, create one.
					// set values for CAS user
					$first_name = ucwords(strtolower($_SESSION['phpCAS']['attributes']['givenName']));
					$first_name = filter_var($first_name, FILTER_SANITIZE_STRING);
					$last_name = ucwords(strtolower($_SESSION['phpCAS']['attributes']['sn']));
					$last_name = filter_var($last_name, FILTER_SANITIZE_STRING);
					$UID = $_SESSION['phpCAS']['attributes']['uid'];
					$UID = filter_var($UID, FILTER_SANITIZE_STRING);
					$email = $casid_name;
				
					// creating a simple 14 digit unique id from the email address
					// this is an "ilkyo" id and is from a previous non-uw project I worked on.
					// the crc32 collision is worked around by reversing the string and and adding that onto the id.
				
					// the argument is treated as an integer, and presented as an unsigned decimal number.
					sscanf(crc32($email), "%u", $front);
					sscanf(crc32(strrev($email)), "%u", $back);
					// ilkyo id
					$ilkyo_id = $front . substr($back, 0, (14-strlen($front)));
				
		
 					// get meta data for component
					$query = "SELECT user_id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and minutia='" . $ilkyo_id . "'";
					$user_id = $vce->db->get_data_object($query);
				
					// if user_id has already been created, but no other user meta information has been created
					if (isset($user_id[0]->user_id)) {
					
						$new_user_id = $user_id[0]->user_id;
						
						 // get meta data for component
						$query = "SELECT vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $new_user_id . "'";
						$user_info = $vce->db->get_data_object($query);
						
						$vector = $user_info[0]->vector;
						
						// update
						$update = array('meta_value' => $vce->user->lookup($email));
						$update_where = array('user_id' => $new_user_id, 'meta_key' => 'lookup');
						$vce->db->update('users_meta', $update, $update_where);
						
						$records = array();
						
					} else {
						
						$vector = $vce->user->create_vector();

						$lowest_role_id = key(end(json_decode($vce->site->site_roles, true)));
						// $vce->dump($lowest_role_id);
			
						$user_data = array(
						'vector' => $vector, 
						'hash' => '',
						'role_id' => $lowest_role_id
						);
			
						$new_user_id = $vce->db->insert('users', $user_data);
						
						$records = array();
			
						// add a lookup
						$records[] = array(
						'user_id' => $new_user_id,
						'meta_key' => 'lookup',
						'meta_value' => $vce->user->lookup($email),
						'minutia' => $ilkyo_id
						);
						
						// add email
						$records[] = array(
						'user_id' => $new_user_id,
						'meta_key' => 'email',
						'meta_value' => $vce->user->encryption($email,$vector),
						'minutia' => null
						);
						
					}
					
					// finish up the remaining user meta data
						
					// first name
					$records[] = array(
					'user_id' => $new_user_id,
					'meta_key' => 'first_name',
					'meta_value' => $vce->user->encryption($first_name,$vector),
					'minutia' => null
					);
						
					// last name
					$records[] = array(
					'user_id' => $new_user_id,
					'meta_key' => 'last_name',
					'meta_value' => $vce->user->encryption($last_name,$vector),
					'minutia' => null
					);
						
					// UID
					$records[] = array(
					'user_id' => $new_user_id,
					'meta_key' => 'UID',
					'meta_value' => $vce->user->encryption($UID,$vector),
					'minutia' => null
					);	

					// created_at
					$records[] = array(
						'user_id' => $new_user_id,
						'meta_key' => 'created_at',
						'meta_value' => time(),
						'minutia' => null
					);
					
					
					//add new user to default group and organization
					
					//find default organization and group id's based on name
					$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'New Users'";
					$result = $vce->db->get_data_object($query);
					$organization_id = $result[0]->item_id;
					
					$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'New Users Default'";
					$result = $vce->db->get_data_object($query);
					$group_id = $result[0]->item_id;
					
					//add to user records
					//Organization
					$records[] = array(
					'user_id' => $new_user_id,
					'meta_key' => 'organization',
					'meta_value' => $vce->user->encryption($organization_id,$vector),
					'minutia' => null
					);
					
					//Group
					$records[] = array(
					'user_id' => $new_user_id,
					'meta_key' => 'group',
					'meta_value' => $vce->user->encryption($group_id,$vector),
					'minutia' => null
					);

					$vce->db->insert('users_meta', $records);
					

					
					//add javascript
					$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery');

					return;
				
				}
			}

			//if there is no registered CAS, user, post an error
			// no remote_user error message
			$vce->content->add('main', 'A CAS account is required to access this site. <a href="https://eclkc.ohs.acf.hhs.gov/cas/login" class="link-button">Click here for information</a>');
			return false;

		} else {


            // add hook
            if (isset($vce->site->hooks['login_check_access_true'])) {
				foreach($vce->site->hooks['login_check_access_true'] as $hook) {
					return call_user_func($hook, $each_component, $vce);
				}
			}
		
			return true;
		
		}
	}
	
	
	
	/**
	 *
	 */
	public function recipe_fields($recipe) {
		global $vce;
	
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
		$template = isset($recipe['template']) ? $recipe['template'] : null;
	
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

	foreach($vce->site->get_template_names() as $key=>$value) {
	
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
EOF;

		return $elements;
		
	}

}