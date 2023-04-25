<?php

class Pbc_request_access extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Request Access (pbc_request_access)',
			'description' => 'Request and approve access to multiple Organizations at once.',
			'category' => 'pbc',
			'permissions' => array(
				array(
					'name' => 'create_users',
					'description' => 'Role can create new users'
				),
				array(
					'name' => 'edit_users',
					'description' => 'Role can delete users'
				),
				array(
					'name' => 'delete_users',
					'description' => 'Role can delete users'
				),
				array(
					'name' => 'masquerade_users',
					'description' => 'Role can masquerade as users'
				)
			)
		);
	}
	
	
	/**
	 *
	 */
	public function as_content($each_component, $vce) {



		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js');
		
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');


		$filter_by = array();
	
		foreach ($vce as $key=>$value) {
			if (strpos($key, 'filter_by_') !== FALSE) {
				$filter_by[str_replace('filter_by_', '', $key)] = $value;
			}
		}
		
		// find if "user_id" has been supplied in the query string. 
		if (isset($vce->query_string)) {
			$query_string = json_decode($vce->query_string, true);
			$query_string_user_id = $this->hex2str($query_string['user_id']);
		}
		
		// manage_users_attributes_filter_by
		if (isset($vce->site->hooks['manage_users_attributes_filter_by'])) {
			foreach($vce->site->hooks['manage_users_attributes_filter_by'] as $hook) {
				$filter_by = call_user_func($hook, $filter_by, $vce);
			}
		}

		// get roles
		$roles = json_decode($vce->site->roles, true);
	
		// create var for content
		$content = null;
		

		
		
		// decrypt 
		if (isset($query_string_user_id)) {
		
			// look up active user's vector; this is how the user id was encrypted
		// First we query the user table to get user_id and vector
	// 		$query = "SELECT " . TABLE_PREFIX . "users.* FROM " . TABLE_PREFIX . "users_meta INNER JOIN " . TABLE_PREFIX . "users ON " . TABLE_PREFIX . "users_meta.user_id = " . TABLE_PREFIX . "users.user_id";
			$query = "SELECT user_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id = '" . $vce->user->user_id . "'";
			$current_list = $vce->db->get_data_object($query);
		
			// rekey data into array for user_id and vectors
			foreach ($current_list as $each_list) {
				$users[$each_list->user_id]['vector'] = $each_list->vector;
			}

			$user_of_interest = $vce->userdecryption($query_string_user_id,$users[$vce->user->user_id]['vector']);
			
// 			$user_of_interest = $vce->userdecryption($query_string_user_id,'82/bnLGiE6J2P+mMqCOpfg==');
		} else {
			$user_of_interest = $vce->user->user_id;
		}


		// We get information either about the user visiting the page, or the user who sent a request for access (if there is a "user_id" in the query string)
		// First we query the user table to get user_id and vector
// 		$query = "SELECT " . TABLE_PREFIX . "users.* FROM " . TABLE_PREFIX . "users_meta INNER JOIN " . TABLE_PREFIX . "users ON " . TABLE_PREFIX . "users_meta.user_id = " . TABLE_PREFIX . "users.user_id WHERE " . TABLE_PREFIX . "users.user_id = '" . $user_of_interest . "'";
		$query = "SELECT " . TABLE_PREFIX . "users.* FROM " . TABLE_PREFIX . "users WHERE " . TABLE_PREFIX . "users.user_id = '" . $user_of_interest . "'";
		$current_list = $vce->db->get_data_object($query);
		

		// rekey data into array for user_id and vectors
		foreach ($current_list as $each_list) {
			$users_list[] = $each_list->user_id;
			$users[$each_list->user_id]['user_id'] = $each_list->user_id;
			$users[$each_list->user_id]['role_id'] = $each_list->role_id;
			$users[$each_list->user_id]['role_name'] = $roles[$each_list->role_id]['role_name'];
			$users[$each_list->user_id]['user_vector'] = $each_list->vector;
			$vectors[$each_list->user_id] = $each_list->vector;
		}
		
		// Second we query the user_meta table for user_ids
		
		if (isset($users_list) ) {
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
			
			// add
			$users[$each_meta_data->user_id][$each_meta_data->meta_key] = $vce->user->decryption($each_meta_data->meta_value,$vectors[$each_meta_data->user_id]);
		}
		$this_user = (object) $users[$user_of_interest];


		// If "user_id" has been supplied in the query string, display the "accept" part of the page (with a link to the "request" part)
		if (isset($query_string_user_id)) {
		
		$dossier_for_grant_access = $vce->generate_dossier(array('type' => 'Pbc_request_access','procedure' => 'grant_access'));
		$this_page_url = $vce->site->site_url . '/' . $each_component->url;
// 	$vce->site->dump($user);
		$requester_name = $this_user->first_name . ' '  . $this_user->last_name;
		$requester_email = $this_user->email;
		$requester_role = $roles[$this_user->role_id]['role_name'];
		
$content = <<<EOF
<p>
<div class="clickbar-container">
<div class="clickbar-content clickbar-open">
<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_grant_access">
<input type="hidden" name="first_name" value="$this_user->first_name">
<input type="hidden" name="last_name" value="$this_user->last_name">
<input type="hidden" name="email" value="$this_user->email">
<input type="hidden" name="user_id" value="$this_user->user_id">
<input type="hidden" name="user_vector" value="$this_user->user_vector">
<input type="hidden" name="url" value="$this_page_url">




<label>
<div class="label-message instructions" style="padding:10px; text:medium">
<ul>
EOF;
			if (strlen($requester_email) > 4) {
$content .= <<<EOF
This is a utility which will allow you to grant access to a user who works in multiple organizations.<br>
A user named &quot;$requester_name&quot; ($requester_email) has requested to work in your organization. $requester_name will have the role of $requester_role in your organization.  To accept their request, do the following:<br><br>

<li>Choose the group you want them to be associated with.</li>
<li>Click &quot;Accept Request&quot;. A notification email will be sent to $requester_name, who will then be able to work with members of your organization.</li>
EOF;
		} else {
$content .= <<<EOF
Grant Request: <br>
It seems that you have reached this page with a link intended for a user other than yourself. Please check that you are the intended recipient of the url you used to get here.
<br>
EOF;
		}
$content .= <<<EOF
<br>
<a href="$this_page_url">You can return to the request page here</a>
</ul>
</div>
</label>
EOF;


		/* show list of organizations and groups */
		// load hooks
		// manage_users_attributes_filter_by
		$filter_by['organization'] = $vce->user->organization;
		if (isset($vce->site->hooks['manage_users_attributes_filter_by'])) {
			foreach($vce->site->hooks['manage_users_attributes_filter_by'] as $hook) {
				$filter_by = call_user_func($hook, $filter_by, $vce);
			}
		}
		
		$vce->user->component_name = 'grant_access';
		if (isset($vce->site->hooks['manage_users_attributes2'])) {
			foreach($vce->site->hooks['manage_users_attributes2'] as $hook) {
				$content .= call_user_func($hook, $vce->user);
			}
		}
		$requested_organization_name = $vce->requested_organization_name;
		
		$roles_hierarchical = json_decode($vce->site->site_roles, true);
// $vce->dump($roles_hierarchical);
$content .= <<<EOF
<label>
<select class="filter-form" name="role_id">
<option></option>
EOF;


			foreach ($roles_hierarchical as $roles_each) {
				foreach ($roles_each as $key => $value) {
					if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
						if ($value['role_name'] != 'Coachee') {
							$content .= '<option value="' . $key . '"';
								if ($value['role_name'] == 'Coach') {
									$content .= ' selected';
								}
						
							$content .= '>' . $value['role_name'] . '</option>';
						}
					}
				}
			}


$content .= <<<EOF
</select>
<div class="label-text">
<div class="label-message">Role for $requester_name in your organization.</div>
</div>
</label>



<label>
<input type="text" name="org_admin_message" tag="required" value="Your request to register with the $requested_organization_name organization has been granted." autocomplete="off">
<div class="label-text">
<div class="label-message">Message for $requester_name</div>
<div class="label-error">Enter a Message to $requester_name</div>
</div>
</label>

<input type="submit" value="Accept Request">

</form>
</div>
<div class="clickbar-title clickbar-open"><span>Accept and Grant Access to Your Organization</span></div>
</div>
</p>
EOF;

	
		} else {

// show only the "request" part of the page
		
		$dossier_for_request_access = $vce->generate_dossier(array('type' => 'Pbc_request_access','procedure' => 'request_access'));
		$this_page_url = $vce->site->site_url . '/' . $each_component->url;
// 		$vce->dump($this_page_url);
		$quickstart_link = $vce->site->site_url . '/help/quickstartvideos/admin_quickstart_videos.php';

$content = <<<EOF
<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_request_access">
<input type="hidden" name="first_name" value="$this_user->first_name">
<input type="hidden" name="last_name" value="$this_user->last_name">
<input type="hidden" name="email" value="$this_user->email">
<input type="hidden" name="user_id" value="$this_user->user_id">
<input type="hidden" name="url" value="$this_page_url">

<label>
<div class="label-message instructions" style="padding:10px; text:medium">
<ul>
This utility allows you to request access to more than one organization.<br>
<a href="$quickstart_link">Here is a link to the &quot;Quickstart Video&quot; with instructions for use.</a> <br>
If you will be coaching in an organization which does not currently appear in your &quot;My Account&quot; page, do the following:<br><br>

<li>Choose the additional organization you will be working with from the dropdown.</li>
<li>Write a message to the administrator of that organization in the supplied field about why you are requesting access.</li>
<li>Click &quot;Send Request&quot;. Your message, name and email will be sent to the organization admin, who can then add you to that additional organization.</li>
<li>Once the admin has added you to the organization, you will see this and any other organizations you belong to in the &quot;Organization&quot; field in your &quot;My Account&quot; page.</li>
<li>To switch between organizations in the field, you must go to your &quot;My Account&quot; page, change your organization, and click &quot;Update&quot; </li>
</ul>
</div>
</label>
EOF;


		/* show list of organizations and groups */
		// load hooks
		$this_user->component_name = 'request_access';
		if (isset($vce->site->hooks['manage_users_attributes2'])) {
			foreach($vce->site->hooks['manage_users_attributes2'] as $hook) {
				$content .= call_user_func($hook, $this_user);
			}
		}

		// add message textarea
		$input = array(
			'type' => 'textarea',
			'name' => 'org_admin_message',
			'data' => array(
					'rows' => '3',
					'tag' => 'required',
			)
		);

		$org_admin_message = $vce->content->create_input($input,'Message for Organization Admin');

$content .= <<<EOF
$org_admin_message
<input type="submit" value="Send Request">

</form>
EOF;
	}
		$vce->content->add('main', $content);
	
	}

	
	/**
	 * send request to Organization Admin
	 */
	public function request_access($input) {
	
		global $vce;
// 		$vce->log($input);
		
		//get user_id of Admin for Organization the user wants to join
		$filter_by = array('organization'=>$input['organization'], 'role_id'=>5);
	

		// get roles
		$roles = json_decode($vce->site->roles, true);
	
		// create var for content
		$content = null;

		
		// First we query the user table to get user_ids and vectors for all users, 
		// then get their metadata to get their organizations, then filter by organization,
		// then wirte emails to all the organization admins from the requested organization
		
// 		$query = "SELECT " . TABLE_PREFIX . "users.* FROM " . TABLE_PREFIX . "users_meta INNER JOIN " . TABLE_PREFIX . "users ON " . TABLE_PREFIX . "users_meta.user_id = " . TABLE_PREFIX . "users.user_id";
		$query = "SELECT * FROM " . TABLE_PREFIX . "users";
		$current_list = $vce->db->get_data_object($query);
		
		// rekey data into array for user_id and vectors
		foreach ($current_list as $each_list) {
			$users_list[] = $each_list->user_id;
			$users[$each_list->user_id]['user_id'] = $each_list->user_id;
			$users[$each_list->user_id]['role_id'] = $each_list->role_id;
			$users[$each_list->user_id]['role_name'] = $roles[$each_list->role_id]['role_name'];
			$users[$each_list->user_id]['vector'] = $each_list->vector;
			$vectors[$each_list->user_id] = $each_list->vector;
		}
		
		// Second we query the user_meta table for user_ids
		
		if (isset($users_list) ) {
		
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
			
			// add
			$users[$each_meta_data->user_id][$each_meta_data->meta_key] = $vce->userdecryption($each_meta_data->meta_value,$vectors[$each_meta_data->user_id]);
		}
		foreach ($users as $each_user) {
			$this_user[] = (object) $each_user;
		}
		// $vce->log($this_user);
		$selected_users = array();
			// loop through users
		foreach ($users_list as $each_user) {
		
			// check if filtering is happening
			if (!empty($filter_by)) {
// 			$vce->log($filter_by);
				// loop through filters and check if any user fields are a match
				foreach ($filter_by as $filter_key=>$filter_value) {

					if ($filter_key == "role_id") {
						// make title of role
						//	$filter_value = $roles[$filter_value]['role_name'];
						if ($users[$each_user]['role_id'] != $filter_value) {
							continue 2;
						}
						
						continue;
					}
					// check if $filter_value is an array
					if (is_array($filter_value)) {
						// check that meta_key exists for this user
						if (!isset($users[$each_user][$filter_key])) {
							continue 2;
						}
						// check if not in the array
						if (!in_array($users[$each_user][$filter_key],$filter_value)) {
							// continue foreach before this foreach
							continue 2;
						}
					} else {
						// doesn't match so continue
						if ($users[$each_user][$filter_key] != $filter_value) {
							// continue foreach before this foreach
							continue 2;	
						}
					}
				}
			}
			$selected_users[] = $users[$each_user];
		}

// $vce->log($selected_users);

if (!isset($selected_users[0]['email'])){
		echo json_encode(array('response' => 'success','message' => 'This Organization does not have an assigned administrator; your request cannot be submitted.','form' => 'no_admin','action' => ''));
		return;
} else {

		//send email to each Admin with link to acceptance page
		$vce->log('purge_log');
		foreach($selected_users as $each_user) {
		// $vce->log($each_user);
		// $vce->log('email sent to:');
		// $vce->log($each_user['email']);
	$admin_fname = $each_user['first_name'];
	$admin_lname = $each_user['last_name'];
	$admin_fullname = $each_user['first_name'].' '.$each_user['last_name'];
	$admin_email = $each_user['email'];


	$requester_fullname = $input['first_name'].' '.$input['last_name'];
	$requester_email = $input['email'];
	$encrypted_user_id = $vce->userencryption($input['user_id'],$each_user['vector']);
	$encrypted_user_id = $this->str2hex($encrypted_user_id);
	$href_link = $input['url'] . "?user_id=$encrypted_user_id";

$email_message = <<<EOF
Dear $admin_fullname,<br>
<br>
The Coaching Companion User <b>$requester_fullname</b> is requesting to be included in your organization. <br>
As Organization Administrator of your organization, you can grant access to $requester_fullname, allowing them to work directly with the other members of your organization.<br><br>
To accept this request, please go to this link and follow the instructions posted there:<br>
<a href="$href_link">Access Administration for the OHS Coaching Companion</a> <br>
<br>

Thank you,<br>
Your OHSCC Administrator<br>
EOF;


// log requests to the site log
		// $vce->log('purge_log');
		$vce->log('request access');
		$vce->log('admin:');
		$vce->log($admin_email);
		$vce->log('person requesting:');
		$vce->log($requester_email);
		$vce->log($href_link);
		
	$mail_attributes = array (
	  	'from' => array('coachingcompanion@eclkc.info', 'DoNotReply'),
	  	'to' => array(
	 		 array($admin_email, $admin_fullname)
	   	 ),
		'subject' => 'OHSCC Request for Admission',
	 	'message' => $email_message,
	 	'html' => true,
	 	'SMTPAuth' => false
	 );	

	$vce->mail($mail_attributes);	

		}
// exit;
		echo json_encode(array('response' => 'success','message' => 'Request has been sent.','form' => 'create','action' => ''));
		return;
	}
	}
	





	
	/**
	 *  Organization Admin approves access
	 */
	public function grant_access($input) {
	
		global $vce;	
		
// $vce->log($input);
// exit;
		


		// rekey input
		foreach ($input as $key=>$value) {
			$$key = $value;
		}



		
		//create arrays for groups and organizations to add to user_meta
		// the organization lists and group lists are stored as json objects	
		// the structure of $org_group_list_array = array(<org_id> => array(<group_id> => <role>))
		$org_group_list_array = array();	
		$org_list_exists = false;
		$query = "SELECT meta_value FROM " . TABLE_PREFIX . "users_meta WHERE user_id = $user_id AND meta_key = 'org_group_list'";
		$meta_data = $vce->db->get_data_object($query);

		foreach ($meta_data as $each_meta) {
			if(isset($each_meta->meta_value)) {
				$org_group_list_array = json_decode($vce->user->decryption($each_meta->meta_value,$user_vector), true);
				$org_list_exists = true;
			}
		}
		
		
		// add org and group from request to array
		// this becomes the value which is saved into the user attribute "org_group_list" and grows with each new organization
		// if it exists, it has been decrypted, the new values added, and re-encrypted. 
		// if it does not exist, it has been created and then encrypted
		$org_group_list_array[$organization][$group] = $role_id;
		$org_group_list_value = $vce->user->encryption(json_encode($org_group_list_array),$user_vector);

// $vce->log($org_group_list_array);
// $query1 = "delete from " . TABLE_PREFIX . "users_meta WHERE user_id = 3215 AND meta_key = 'org_group_list'";
// $vce->db->query($query1);
// $query1 = "delete from " . TABLE_PREFIX . "users_meta WHERE user_id = 3215 AND meta_key = 'native_org_group'";
// $vce->db->query($query1);
// exit;
		if ($org_list_exists !== true) {
			$query1 = "INSERT INTO " . TABLE_PREFIX . "users_meta (user_id, meta_key, meta_value) VALUES ('$user_id','org_group_list','$org_group_list_value')";
			$vce->db->query($query1);
			// get requester's original group and org
			$query = "SELECT meta_key, meta_value FROM " . TABLE_PREFIX . "users_meta WHERE user_id = $user_id";
			$meta_data = $vce->db->get_data_object($query);
			foreach ($meta_data as $this_metadata) {
				if ($this_metadata->meta_key == 'organization') {
					$requester_organization = $vce->user->decryption($this_metadata->meta_value,$user_vector);
				}
				if ($this_metadata->meta_key == 'group') {
					$requester_group = $vce->user->decryption($this_metadata->meta_value,$user_vector);
				}
			}
			// get requester's original role_id
			$query = "SELECT role_id FROM " . TABLE_PREFIX . "users WHERE user_id = $user_id";
			$meta_data = $vce->db->get_data_object($query);
			foreach ($meta_data as $this_metadata) {
				if (isset($this_metadata->role_id)) {
					$requester_role_id = $this_metadata->role_id;
				}
			}

			
			$native_org_group = array();
			$native_org_group[$requester_organization][$requester_group] = $requester_role_id;
// 			$vce->log($native_org_group);
			$native_org_group = $vce->user->encryption(json_encode($native_org_group),$user_vector);
			$query2 = "INSERT INTO " . TABLE_PREFIX . "users_meta (user_id, meta_key, meta_value) VALUES ('$user_id','native_org_group','$native_org_group')";
			$vce->db->query($query2);
		} else {
			$query3 = "UPDATE " . TABLE_PREFIX . "users_meta SET meta_value = '$org_group_list_value' WHERE user_id = '$user_id' AND meta_key = 'org_group_list'";
			$vce->db->query($query3);
		}

		

		
// exit;		
	
		//get requesting user's id
// $vce->log($query1);
// $vce->log($query2);
// $vce->log($query3);


		//call up user object for that user and
	
		//send email
	$requester_fname = $input['first_name'];
	$requester_lname = $input['last_name'];
	$requester_fullname = $input['first_name'].' '.$input['last_name'];
	$requester_email = $input['email'];

	$admin_fullname = $vce->user->first_name.' '.$vce->user->last_name;
	$admin_email = $vce->user->email;
	// $href_link = $input['url'] . "?user_id=$encrypted_user_id";

$email_message = <<<EOF
Dear $requester_fullname,<br>
<br>
The Coaching Companion Admin <b>$admin_fullname</b> has granted your request to be a multi-organizational coach in their organization. <br>
You will now see all organizations to which you have multi-organizational coach access in your My Account page.<br>
To work with these additional organizations, select the organization in your My Account page and click &quot;Update&quot;<br>
<br>

Thank you,<br>
Your OHSCC Administrator<br>
EOF;

// log grants to the site log
// $vce->log('grant access: ');
// $vce->log($admin_email);
// $vce->log($requester_email);
// $vce->log($input['url']);

		$vce->log('Grant Access Message: ');	
		$vce->log($email_message);
// 		exit;
	$mail_attributes = array (
	  	'from' => array('coachingcompanion@eclkc.info', 'DoNotReply'),
	  	'to' => array(
	 		 array($requester_email, $requester_fname)
	   	 ),
		'subject' => 'OHSCC Request for Admission',
	 	'message' => $email_message,
	 	'html' => true,
	 	'SMTPAuth' => false
	 );	

	$vce->mail($mail_attributes);	

	// redirect to new url without query string
	 $redirect_url = $input['url'];
		echo json_encode(array('response' => 'success','message' => 'Approval has been sent.','form' => 'create_lose_querystring','action' => ''));
		return;		

	
	}
	
	
	
	
	
	/**
	 * Create a new user
	 */
	public function create($input) {
	
		global $vce;
		
		
		// loop through to look for checkbox type input
		foreach ($input as $input_key=>$input_value) {
			// for checkbox inputs
			if (preg_match('/_\d+$/',$input_key,$matches)) {
				// strip _1 off to find input value for checkbox
				$new_input = str_replace($matches[0],'', $input_key);
				// decode previous json object value for input variable
				$new_value = isset($input[$new_input]) ? json_decode($input[$new_input], true) : array();
				// add new value to array
				$new_value[] = $input_value;
				// remove the _1
				unset($input[$input_key]);
				// reset the input with json object
				$input[$new_input] = json_encode($new_value);
			}
		}
		
		// get user attributes
		$user_attributes = json_decode($vce->site->user_attributes, true);

		// start with default
		$attributes = array('email' => 'text');
		
		// assign values into attributes for order preserving hash in minutia column
		if (isset($user_attributes)) {
			foreach ($user_attributes as $user_attributes_key=>$user_attributes_value) {
				if (isset($user_attributes_value['sortable']) && $user_attributes_value['sortable']) {
					$value = isset($user_attributes_value['type']) ? $user_attributes_value['type'] : null;
					$attributes[$user_attributes_key] = $value;
				}
			}
		}

		// remove type so that it's not created for new user
		unset($input['type']);
	
		// test email address for validity
		$input['email'] = filter_var(strtolower($input['email']), FILTER_SANITIZE_EMAIL);
		if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
			echo json_encode(array('response' => 'error','message' => 'Email is not a valid email address','form' => 'create', 'action' => ''));
			return;
		}
		
		$lookup = $vce->userlookup($input['email']);
		
		// check
		$query = "SELECT id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and meta_value='" . $lookup . "'";
		$lookup_check = $vce->db->get_data_object($query);
		
		if (!empty($lookup_check)) {
			echo json_encode(array('response' => 'error','message' => 'Email is already in use','form' => 'create', 'action' => ''));
			return;
		}
		
		// call to user class to create_hash function
		$hash = $vce->usercreate_hash($input['email'], $input['password']);
		
		// get a new vector for this user
		$vector = $vce->usercreate_vector();

		$user_data = array(
		'vector' => $vector, 
		'hash' => $hash,
		'role_id' => $input['role_id']
		);
		$user_id = $vce->db->insert('users', $user_data);
		
		unset($input['procedure']);
		unset($input['password']);
		unset($input['role_id']);
				
		// now add meta data

		$records = array();
				
		$lookup = $vce->userlookup($input['email']);
		
		$records[] = array(
		'user_id' => $user_id,
		'meta_key' => 'lookup',
		'meta_value' => $lookup,
		'minutia' => 'false'
		);
		
		foreach ($input as $key => $value) {

			// encode user data			
			$encrypted = $vce->userencryption($value, $vector);
			
			$minutia = null;
			
			// if this is a sortable text attribute
			if ($attributes[$key]) {
				// check if this is a text field
				if ($attributes[$key] == 'text') {
					$minutia = $vce->userorder_preserving_hash($value);
				}
				// other option will go here
			}
			
			$records[] = array(
			'user_id' => $user_id,
			'meta_key' => $key,
			'meta_value' => $encrypted,
			'minutia' => $minutia
			);
			
		}		
		
		$vce->db->insert('users_meta', $records);

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
You can view our new user orientation page here:<br>
<a href="https://eclkc.ohs.acf.hhs.gov/cc/help/new_user_orientation.php">New User Orientation</a><br>
<br>
EOF;
		// get roles
		$roles = json_decode($vce->site->roles, true);
		$role_name = $roles[$input['role_id']]['role_name'];
		 if ($roles[$input['role_id']]['role_hierarchy'] <= 4) {
$email_message .= <<<EOF
You have been registered as an administrator with the role of &quot;$role_name&quot;. You will find quickstart videos for administrators here:<br>
<a href="https://eclkc.ohs.acf.hhs.gov/cc/help/quickstartvideos/admin_quickstart_videos.php">Administrator Quickstart Videos</a><br>
<br>
EOF;
		 }		

$email_message .= <<<EOF
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

	 $vce->mail($mail_attributes);	



		
		return;
	}

	/**
	 * edit user
	 */
	public function edit($input) {

		// add attributes to page object for next page load using session
		global $vce;
		
		$vce->site->add_attributes('edit_user',$input['user_id']);
		
		$pagination_current = filter_var($input['pagination_current'], FILTER_SANITIZE_NUMBER_INT);
		
		if ($pagination_current < 1) {
			$pagination_current = 1;
		}
		
		$vce->site->add_attributes('sort_by',$input['sort_by']);
		$vce->site->add_attributes('sort_direction',$input['sort_direction']);
		$vce->site->add_attributes('pagination_current',$pagination_current);
		
		echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
		return;
		
	}


	/**
	 * update user
	 */
	public function update($input) {
	
		global $vce;
		
		// loop through to look for checkbox type input
		foreach ($input as $input_key=>$input_value) {
			// for checkbox inputs
			if (preg_match('/_\d+$/',$input_key,$matches)) {
				// strip _1 off to find input value for checkbox
				$new_input = str_replace($matches[0],'', $input_key);
				// decode previous json object value for input variable
				$new_value = isset($input[$new_input]) ? json_decode($input[$new_input], true) : array();
				// add new value to array
				$new_value[] = $input_value;
				// remove the _1
				unset($input[$input_key]);
				// reset the input with json object
				$input[$new_input] = json_encode($new_value);
			}
		}

		// get user attributes
		$user_attributes = json_decode($vce->site->user_attributes, true);

		// start with default
		$attributes = array('email' => 'text');
		
		// assign values into attributes for order preserving hash in minutia column
		if (isset($user_attributes)) {
			foreach ($user_attributes as $user_attributes_key=>$user_attributes_value) {
				if (isset($user_attributes_value['sortable']) && $user_attributes_value['sortable']) {
					$value = isset($user_attributes_value['type']) ? $user_attributes_value['type'] : null;
					$attributes[$user_attributes_key] = $value;
				}
			}
		}
	
		$user_id = $input['user_id'];
	
		$query = "SELECT role_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "'";
		$user_info = $vce->db->get_data_object($query);
		
		$role_id = $user_info[0]->role_id;
		$vector = $user_info[0]->vector;
		
		// has role_id been updated?
		if (isset($input['role_id']) && $input['role_id'] != $role_id) {

			$update = array('role_id' => $input['role_id']);
			$update_where = array('user_id' => $user_id);
			$vce->db->update('users', $update, $update_where );

		}
		
		// clean up
		unset($input['type'],$input['procedure'],$input['role_id'],$input['user_id']);
		
		// delete old meta data
		foreach ($input as $key => $value) {
				
			// delete user meta from database
			$where = array('user_id' => $user_id, 'meta_key' => $key);
			$vce->db->delete('users_meta', $where);
		
		}
		
		// now add meta data
		
		$records = array();
		
		foreach ($input as $key => $value) {

			// encode user data			
			$encrypted = $vce->userencryption($value, $vector);
			
			$minutia = null;
			
			// if this is a sortable text attribute
			if (isset($attributes[$key])) {
				// check if this is a text field
				if ($attributes[$key] == 'text') {
					$minutia = $vce->userorder_preserving_hash($value);
				}
				// other option will go here
			}
			
			$records[] = array(
			'user_id' => $user_id,
			'meta_key' => $key,
			'meta_value' => $encrypted,
			'minutia' => $minutia
			);
			
		}
		
		// check that $records is not empty
		if (!empty($records)) {
			$vce->db->insert('users_meta', $records);
		}
				
		echo json_encode(array('response' => 'success','message' => 'User Updated','form' => 'create','action' => ''));
		return;
	
	}

	
	/**
	 * Masquerade as user
	 */
	public function masquerade($input) {
	
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
	
		global $vce;
	
		// delete user from database
		$where = array('user_id' => $input['user_id']);
		$vce->db->delete('users', $where);
		
		// delete user from database
		$where = array('user_id' => $input['user_id']);
		$vce->db->delete('users_meta', $where);
		
		echo json_encode(array('response' => 'success','message' => 'User has been deleted','form' => 'delete','user_id' => $input['user_id'] ,'action' => ''));
		return;
	
	}


	/**
	 * Filter
	 */
	public function filter($input) {
	
		global $vce;
		
		foreach ($input as $key=>$value) {
			if (strpos($key, 'filter_by_') !== FALSE) {
				$vce->site->add_attributes($key,$value);
			}
		}
		
		$vce->site->add_attributes('pagination_current',$input['pagination_current']);
	
		echo json_encode(array('response' => 'success','message' =>'Filter'));
		return;
	
	}
	
	/**
	 * pagination users
	 */
	public function pagination($input) {

		// add attributes to page object for next page load using session
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
	
	
	/**
	 * search for a user
	 */
	public static function search($input) {
		
		global $vce;
		
		if (!isset($input['search']) || strlen($input['search']) < 3) {
			// return a response, but without any results
			echo json_encode(array('response' => 'success','results' => null));
			return;
		}
		
		// break into array based on spaces
		$search_values = explode('|',preg_replace('/\s+/','|',$input['search']));



		// get all users of specific roles as an array
		$query = "SELECT * FROM " . TABLE_PREFIX . "users";
		$find_users_by_role = $vce->db->get_data_object($query, 0);
		
		// get roles
		$roles = json_decode($vce->site->roles, true);

		$roles_list = array();
		foreach ($roles as $key=>$value) {
			if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
				// add to role_id to array
				$roles_list[] = $key;
			}
		}
	
		// cycle through users
		foreach ($find_users_by_role as $key=>$value) {
			// filter out higher role_id
			if (in_array($value['role_id'],$roles_list)) {			
				// add user_id to array for the IN contained within database call
				$users_id_in[] = $value['user_id'];
				// and these other values
				$all_users[$value['user_id']]['user_id'] = $value['user_id'];
				$all_users[$value['user_id']]['role_id'] = $value['role_id'];
				$all_users[$value['user_id']]['vector'] = $value['vector'];
				// set for search
				$match[$value['user_id']] = 0;
			}
		}
		

		if (!isset($users_id_in)) {
			echo json_encode(array('response' => 'success','results' => null));
			return;
		}

		$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id IN (" . implode(",",$users_id_in) . ")";
		$users_meta_data = $vce->db->get_data_object($query, 0);

		foreach ($users_meta_data as $key=>$value) {
			
			// skip a few meta_key that we don't want to allow searching in
			if ($value['meta_key'] == 'lookup' || $value['meta_key'] == 'persistant_login') {
				continue;
			}
				
			// decrypt the values
			$all_users[$value['user_id']][$value['meta_key']] = $vce->userdecryption($value['meta_value'], $all_users[$value['user_id']]['vector']);
						
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

		// hook to work with search results
		if (isset($vce->site->hooks['manage_users_attributes_search'])) {
			foreach($vce->site->hooks['manage_users_attributes_search'] as $hook) {
				$all_users = call_user_func($hook, $all_users);
			}
		}

		if (count($all_users)) {
		
			$user_keys = array_keys($all_users);
			
			$vce->site->add_attributes('search_value',$input['search']);
			$vce->site->add_attributes('user_search_results',json_encode($user_keys));
		
			echo json_encode(array('response' => 'success', 'form' => 'edit'));
			return;
		}
		
		$vce->site->add_attributes('search_value',$input['search']);
		$vce->site->add_attributes('user_search_results', null);
		
		echo json_encode(array('response' => 'success','form' => 'edit'));
		return;
	
	}
	
	/**
	 * functions for converting to hexidecimal and back
	 */
	
	public function hex2str($hex) {
  		return pack('H*', $hex);
	}

	public function str2hex($str) {
  		return array_shift(unpack('H*', $str));
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