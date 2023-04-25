<?php

class Pbc_Ingression extends Component {
	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Ingression',
			'description' => 'Forms for Ingression of new members',
			'category' => 'pbc'
		);
	}

	public function to_do() {
		/** 
		 * Access: Is this open to the web? If so, do we need a capcha?
		 * Requirement: Should everyone in the system be required to fill this out Yes? Should only people in New User role be required?
		 * Workflow:
		 * Create ECLKC account first, Fill out Individual form, fill out Organization form (Org form should not be available until individual form is complete)
		 * Redirect to home-page (ECLKC sign-in) ?
		 * 
		 * 
		 * 
		*/

		return false;
	}
	

	/**
	 * create component specific database table when installed
	 */
	public function installed() {
		global $vce;

		$sql = "INSERT INTO `" . TABLE_PREFIX . "site_meta` (meta_key, meta_value) VALUES ('intake_form', 'FALSE')";
		$vce->db->query($sql);
		
		$sql = "DROP TABLE IF EXISTS `" . TABLE_PREFIX . "ingression_forms`";
		$vce->db->query($sql);

		$sql = "CREATE TABLE `" . TABLE_PREFIX . "ingression_forms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `processed` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `current_created_at_date` timestamp NULL DEFAULT NULL,
  `current_first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `current_last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `current_org` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `current_group` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `current_site_role` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `organization` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `group` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ind_new_organization` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ind_city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ind_state` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ind_region` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `closest_role` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_closest_role` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coach_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coachee_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `admin_question` smallint(6) DEFAULT NULL,
  `org_registration_question` smallint(6) DEFAULT NULL,
  `org_new_organization` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `org_city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `org_state` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `org_region` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_size` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_type_head_start` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_type_early_head_start` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_type_family_child_care` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_type_home_based_home_visiting` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_type_ehs_child_care_partnership` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_type_aian` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_type_mshs` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_type_new` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_option_center_based` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_option_home_based_home_visiting` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_option_family_child_care` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `program_option_new` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_model` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_model_new` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_delivery_virtual` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_delivery_in_person` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_delivery_peer` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_delivery_group` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_delivery_expert` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_delivery_tlc` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_delivery_new` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `org_coaches_number` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `org_coachees_number` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coachee_roles_teachers` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coachee_roles_teacher_assistants` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coachee_roles_visitors` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coachee_roles_family_child_care_providers` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coachee_roles_family_service_workers` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coachee_role_new` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coaching_companion_use` text COLLATE utf8_unicode_ci,
  `notes` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		
		$vce->db->query($sql);
	}


	public function check_access($each_component, $vce) {
			return true;
	}

	/**
	 *
	 */
	public function as_content($each_component, $vce) {

		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','vidbox-hover-style');
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui tablesorter');

// 		$unix_timestamp = 1647377351;
// 		$format = 'Y-m-d H:i:s';
// 		$mysql_timestamp = date($format, $unix_timestamp);
		
// // $date = DateTime::createFromFormat($format, '2009-02-15 15:16:17');
// // echo "Format: $format; " . $date->format('Y-m-d H:i:s') . "\n";

// // 		$mysql_timestamp = DateTime::createFromFormat( $format, $unix_timestamp );
// 		// $formatted_created_at = $vce->user->created_at;
// $vce->dump($mysql_timestamp);
// 		// convert query_sting
// 		// if (isset($vce->query_string)) {
// 		// 	$vce->query_string = json_decode($vce->query_string);
// 		// }

			
		$content = NULL;

					//temporary
					// $vce->form_to_show = 'ingression_form';
					//temporary

		// load appropriate form
		if (!isset($vce->form_to_show)) {
			$ingression_form = $this->ingression_form_not_chosen($vce, $each_component->url);
		} else {

			$this_form = $vce->form_to_show;
			$ingression_form = $this->$this_form($vce, $each_component->url, $each_component);
			// $ingression_form = $this->ingression_form($vce);
			// $ingression_form = $this->organization_ingression_form($vce);
		}

		$content .= <<<EOF
<div>
EOF;

		$content .= <<<EOF
$ingression_form
EOF;

		$content .= <<<EOF
		</div>
EOF;

		$vce->content->add('main', $content);

	}
	


	public function ingression_form_not_chosen($vce, $this_url) {
		$dossier = array(
			'type' => 'Pbc_Ingression',
			'procedure' => 'process_choose_form',
			'url' => $this_url,
			);
	
			$dossier_for_choose_form = $vce->generate_dossier($dossier);

		$content = NULL;
// $content .= <<<EOF
// <div>
// <h2>Coaching Companion Application</h2>
// <h2>What is the Head Start Coaching Companion?</h2>
// The Head Start Coaching Companion is a video-sharing and coaching-feedback application for early care and education staff and providers. It helps coaches, coachees, and peer-coaching teams work together, even between coach visits or from a distance. Share video files, ask questions, exchange feedback, and develop individualized coaching plans that support quality teaching and positive outcomes for young children. The HSCC is a useful tool for a variety of staff within early childhood programs, including teachers, home visitors, family childcare providers, education managers, coaches, and others who are supporting coaching.

// <h2>How does the Head Start Coaching Companion support coach’s implementation of Practice-Based Coaching (PBC)?</h2>
// The HSCC supports the three components of the PBC model: Shared Goals and Action Planning, Focused Observation, and Reflection and Feedback. The HSCC guides users through the PBC components in a cyclical process.

// <h2>Head Start Coaching Companion Account Applications</h2>

// <b>Organizations:</b> If you are the first person from your organization to request access to Coaching Companion, you can apply on behalf of your organization to be an organization administrator. The HSCC Organization Administrator will organize and manage the HSCC for your organization. This is not necessarily someone with a title/role of administrator in your program. When you apply as an organizational administrator, we will create your personal/individual account along with the organization's account. To fill out this application, click the organization button below to get started.
// <br><br>
// <b>Individuals:</b> Once organizations gain access, individuals can request access to the HSCC to support their own coaching and/or professional development efforts. To fast-track individual applications, please have the following information ready:

// <ul>
// <li>What role you are requesting</li>
// <li>Your organization name</li>
// <li>Your organization administrator's name and email</li>
// </ul>
// <br><br>
// Your responses to this application will help us better understand coaching within your organization. After we receive your completed questionnaire, a member of our team may contact you to discuss how the Head Start Coaching Companion can best support your coaching needs and plans. To fill out this application, click the individual button below to get started.
// <br>
// <br>
// <b>Thank you for your interest in the Head Start Coaching Companion!</b>

// </div>
// <div>
// <form id="choose_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
// <input type="hidden" name="dossier" value="$dossier_for_choose_form">
// <input type="hidden" name="choose_form" value="ingression_form">
// EOF;

$new_user_content = <<<EOF
<div>
<h2>Head Start Coaching Companion Registration form</h2>
<p>
Thank you for your interest in the Head Start Coaching Companion! Please complete the
registration form to create an account.
</p>
<p>
<b>Individuals:</b> Individuals can request access to an existing organization within the Head Start
Coaching Companion by filling out the registration form.
<br><br>
<b>Organizations:</b> If you are the first person from your organization to request access to the Head
Start Coaching Companion (HSCC), you can apply on behalf of your organization to be an
organization administrator. The HSCC organization administrator will organize and manage the
HSCC for your organization. This is not necessarily someone with a title/role of administrator in
your program. When you apply as an organizational administrator, we will create your
personal/individual account along with the organization&#39;s account.
</p>
<p>
Your responses to this registration will help us better understand coaching within your
organization. After we receive your completed application form, a member of our team may
contact you to discuss how the Head Start Coaching Companion can best support your coaching
needs and plans.
</p>
</div>
<div>
<form id="choose_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_choose_form">
<input type="hidden" name="choose_form" value="ingression_form">
EOF;

$existing_user_content = <<<EOF
<div>
<h2>Welcome back to the Head Start Coaching Companion!</h2>
<p>
Please confirm your information is correct and fill in any missing fields. If there is any
information that is incorrect, please note this at the bottom of the form or contact
<a href="mailto:coachingcompanion@eclkc.info">coachingcompanion@eclkc.info</a>.
<p>
You can view the <a href="https://eclkc.ohs.acf.hhs.gov/privacy">ECLKC Privacy Statement here</a>.
</p>
<p>
Please note that we cannot merge accounts with different email addresses. If you would like a
Head Start Coaching Companion with a different email address, please create a new ECLKC
account.
</p>

</div>
<div>
<form id="choose_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_choose_form">
<input type="hidden" name="choose_form" value="ingression_form">
EOF;

		//set content depending on use
		if ($vce->user->role_name != 'NewUsers') {
			$content .= $existing_user_content;
			// $content .= $new_user_content;

			$submit_text = 'Proceed to your Information';
		} elseif ($vce->user->role_name == 'NewUsers') {
			$content .= $new_user_content;
			$submit_text = 'Proceed to Registration Form';
		}




//choose_form input 
$input = array(
	'type' => 'radio',
	'name' => 'choose_form',
	'options' => array(
		0 => array(
			'label' => 'Organization',
			'value' => 'organization_ingression_form',
		),
		1 => array(
			'label' => 'Individual',
			'value' => 'ingression_form',
		),
	),
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
);
$choose_form_input = $vce->content->create_input($input,'Are you registering your <b>organization</b> or registering as an <b>individual</b>?');

// $choose_form_input
// <h2>Are you registering your <b>organization</b> or registering as an <b>individual</b>?</h2>
$content .= <<<EOF
<button class="submit-button button__primary" type="submit" value="Submit">$submit_text</button>

</form>
</div>
EOF;

		return $content;

	}

	/**
	 * content of individual ingression form
	 */
	public function ingression_form ($vce, $this_url, $each_component) {

		//array for all preset fields
		$preset_fields = array();

		if (isset($vce->user)) {


			//set all available fields from last submission by this user
			$this_user_id = $vce->user->user_id;
			$query = "SELECT * FROM " . TABLE_PREFIX . "ingression_forms WHERE user_id='" . $this_user_id . "' ORDER BY created DESC";
			$vce->db->query($query);
			$results = $vce->db->get_data_object($query);

			$your_information_explanation = NULL;
			if (!empty($results)) {
				// $vce->dump($results[0]);
				$your_information_explanation = ':  the form has been pre-populated with the records of your last use of this form. Please edit any that have changed.';
				
				// get user vector
				$query = "SELECT vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $this_user_id . "' LIMIT 1";
				$user_vector = $vce->db->get_data_object($query);

				// set vector of user filling out the form
				$vector = $user_vector[0]->vector;
				// $vce->dump($vce->user);
				foreach ($results[0] as $k=>$v) {
					if ($k == 'email' || $k == 'first_name'|| $k == 'last_name') {
						// encode user data			
						$v_decrypted = $vce->user->decryption($v, $vector);
						$v = (empty($v_decrypted)) ? $v : $vce->user->decryption($v, $vector);

					}
					// set each field from the query to a member of $preset_fields
					$preset_fields[$k] = $v;
				}
			} else {
				// if there are no records for this user, then create a default empty variable with the column names from the table
				$query = "SHOW COLUMNS FROM " . TABLE_PREFIX . "ingression_forms";
				$results = $vce->db->get_data_object($query);
				// $vce->dump($results);
				if (!empty($results)) {
					foreach ($results as $this_result) {
						// set each field from the query as a variable
						$var_name = $this_result->Field;
						// don't overwrite variables supplied by user object
						if (!isset($$var_name)) {
							$preset_fields[$var_name] = NULL;
						}
					}
				}
				//set all available fields for existing users
				foreach ($vce->user as $k => $v){
					$preset_fields[$k] = $v;
				}
			}
		}

		//set title depending on use
		if ($vce->user->role_name != 'NewUsers') {
			$form_title = 'User Information Update';
		} elseif ($vce->user->role_name == 'NewUsers') {
			$form_title = 'New User Application';
		}


		if (isset($vce->user->created_at)) {
			$unix_timestamp = $vce->user->created_at;
			$format = 'Y-m-d H:i:s';
			$mysql_timestamp = date($format, $unix_timestamp);
		} else {
			$mysql_timestamp = NULL;

		}

		$dossier = array(
			'type' => 'Pbc_Ingression',
			'procedure' => 'process_ingression_request',
			'this_url' => $this_url,
			'user_id' => $vce->user->user_id,
			'email' => $vce->user->email,
			'current_created_at_date' => $mysql_timestamp,
			'current_first_name' => $vce->user->first_name,
			'current_last_name' => $vce->user->last_name,
			'current_org' => $vce->user->organization,
			'current_group' => $vce->user->group,
			'current_site_role' => $vce->user->role_name,
			);
	
			$dossier_for_process_ingression_request = $vce->generate_dossier($dossier);

		$content = NULL;



$content .= <<<EOF
<div>
<h2>$form_title</h2>
</div>
<div>
<form id="pbc_ingression_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_process_ingression_request">
EOF;

// first name input
$input = array(
		'type' => 'text',
		'name' => 'first_name',
		'value' => $preset_fields['first_name'],
		'disabled' => 'disabled',
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
	'value' => $preset_fields['last_name'],
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	)
);
$last_name_input = $vce->content->create_input($input,'Last Name');

//job title input
$input = array(
	'type' => 'text',
	'name' => 'job_title',
	'value' => $preset_fields['job_title'],
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	)
);
$job_title_input = $vce->content->create_input($input,'Job Title');

$content .= <<<EOF
<h2>Your Information $your_information_explanation</h2><br>
$first_name_input
$last_name_input
$job_title_input
EOF;

	if (isset($vce->user->email)) {
		$user_email = $vce->user->email;
	}

	$role_hierarchy = $preset_fields['role_hierarchy'];
	if ($role_hierarchy > 2) {
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
	
	} else {
	
		//email input
		$input = array(
			'type' => 'text',
			'name' => 'email',
			'class' => 'email',
			'value' => $preset_fields['email'],
			'data' => array(
					'autocapitalize' => 'none',
					'tag' => 'required',
			)
		);

		$email_instructions = ': (Leave as-is if you are updating this registration. If you are an Admin registering a new user, change the email.)';
		$email_input = $vce->content->create_input($input,'Email Address' . $email_instructions);
	}

//email2 input
$input = array(
	'type' => 'text',
	'name' => 'email2',
	'class' => 'email2',
	'value' => $preset_fields['email'],
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	)
);
$email2_input = $vce->content->create_input($input,'Confirm Email Address');

$content .= <<<EOF
<h2>Your Contact Information</h2><br>
$email_input
$email2_input
EOF;

$organization_name_input = $this->organizations($vce);

//new_organization_input
$input = array(
	'type' => 'text',
	'name' => 'ind_new_organization',
	'class' => 'ind-new-organization',
	'value' => $preset_fields['ind_new_organization'],
	'data' => array(
			'autocapitalize' => 'none',
	)
);
$new_organization_input = $vce->content->create_input($input,"I don't see my organization in the dropdown list, my organization name is:");

$content .= <<<EOF
<h2>Your Organization Name</h2><br>
$organization_name_input
$new_organization_input
EOF;

//city input
$input = array(
	'type' => 'text',
	'name' => 'ind_city',
	'class' => 'ind-city',
	'value' => $preset_fields['ind_city'],
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	)
);
$city_input = $vce->content->create_input($input,'City');

//state input
$input = array(
	'type' => 'select',
	'name' => 'ind_state',
	'class' => 'ind-state',
	'value' => '',
	'options' => array(
		0 => array(
			'value' => '',
			'name' => ''
		)
	),
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
);

	$i = 1;
	foreach ($this->states($vce) as $k=>$v) {
		$selected = false;
		if ($k == $preset_fields['ind_state']) {
			$selected = true;
		}
		$input['options'][$i] = array(
			'value' => $k,
			'name' => $k,
			'selected' => $selected
		);
		$i++;
	}

$state_input = $vce->content->create_input($input,'Organization State');

//region input
	$these_options = array(
		0 => array(
			'value' => '',
			'name' => ''
		),
		1 => array(
			'value' => 'I',
			'name' => 'Region I (CT, ME, MA, NH, RI, VT)'
		),
		2 => array(
			'value' => 'II',
			'name' => 'Region II (NJ, NY, PR, VI)'
		),
		3 => array(
			'value' => 'III',
			'name' => 'Region III (DE, MD, PA, VA, WV, DC)'
		),
		4 => array(
			'value' => 'IV',
			'name' => 'Region IV (AL, FL, GA, KY, MS, NC, SC, TN)'
		),
		5 => array(
			'value' => 'V',
			'name' => 'Region V (IL, IN, MI, MN, OH, WI)'
		),
		6 => array(
			'value' => 'VI',
			'name' => 'Region VI (AR, LA, NM, OK, TX)'
		),
		7 => array(
			'value' => 'VII',
			'name' => 'Region VII (IA, KS, MO, NE)'
		),
		8 => array(
			'value' => 'VIII',
			'name' => 'Region VIII (CO, MT, ND, SD, UT, WY)'
		),
		9 => array(
			'value' => 'IX',
			'name' => 'Region IX (AZ, CA, HI, NV, AS, FM, GU, MH, PW, MP)'
		),
		10 => array(
			'value' => 'X',
			'name' => 'Region X (AK, ID, OR, WA)'
		),
		11 => array(
			'value' => 'XI',
			'name' => 'Region XI (American Indian and Alaska Native)'
		),
		12 => array(
			'value' => 'XII',
			'name' => 'Region XII (Migrant and Seasonal Head Start)'
		),
		13 => array(
			'value' => 'Not Applicable to Me',
			'name' => 'Not Applicable to Me'
		),
		14 => array(
			'value' => 'I&#39;m Not Sure of My Region',
			'name' => 'I&#39;m Not Sure of My Region'
		)
	);

$input = array(
	'type' => 'select',
	'name' => 'ind_region',
	'class' => 'ind-region',
	'value' => '',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
);

$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if ($v['value'] == $preset_fields['ind_region']) {
		$selected = true;
	}
	$input['options'][$i] = array(
		'value' => $v['value'],
		'name' => $v['name'],
		'selected' => $selected
	);
	$i++;
}

$region_input = $vce->content->create_input($input,'Organization Region');

$content .= <<<EOF
<h2>City, State and Region of Your Organization</h2><br>
$city_input
$state_input
$region_input
EOF;



//closest_role to primary position input 
$these_options = array(
	1 => array(
		'value' => '',
		'label' => ''
	),
	2 => array(
		'value' => 'Coach',
		'label' => 'Coach'
	),
	3 => array(
		'value' => 'Teacher/Teacher Assistant',
		'label' => 'Teacher/Teacher Assistant'
	),
	4 => array(
		'value' => 'Manager/Specialist (Education, Disabilities, Health, etc.)',
		'label' => 'Manager/Specialist (Education, Disabilities, Health, etc.)'
	),
	5 => array(
		'value' => 'Director/Program Manager',
		'label' => 'Director/Program Manager'
	),			
	6 => array(
		'value' => 'Family Child Care Provider',
		'label' => 'Family Child Care Provider'
	),
	7 => array(
		'value' => 'Home Visitor',
		'label' => 'Home Visitor'
	),	
	8 => array(
		'value' => 'Regional TTA Specialist Consultant',
		'label' => 'Regional TTA Specialist Consultant'
	),		
	9 => array(
		'value' => 'Other',
		'label' => 'Other'
	),	
);


$input = array(
	'type' => 'select',
	'name' => 'closest_role',
	'class' => 'closest-role',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
	// 'flags' => Array
	// (
	// 	'options_listed' => 1
	// )
);


$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if ($v['value'] == $preset_fields['closest_role']) {
		$selected = true;
	}
	$input['options'][$i] = array(
		'value' => $v['value'],
		'name' => $v['label'],
		'selected' => $selected
	);
	$i++;
}

$closest_role_input = $vce->content->create_input($input,'Please select the role that is closest to your primary position.');

//new_closest_role_input
$input = array(
	'type' => 'text',
	'name' => 'new_closest_role',
	'value' => $preset_fields['new_closest_role'],
	'class' => 'new-closest-role',
	'data' => array(
			'autocapitalize' => 'none',
	)
);
$new_closest_role_input = $vce->content->create_input($input,'Other (please specify)');


$content .= <<<EOF
<h2>Please select the role that is closest to your primary position:</h2><br>
$closest_role_input
$new_closest_role_input
EOF;

$these_options = array(
	0 => array(
		'value' => '',
		'label' => ''
	),
	1 => array(
		'value' => 'I am a coach full time.',
		'label' => 'I am a coach full time.'
	),
	2 => array(
		'value' => 'I serve in multiple roles.',
		'label' => 'I serve in multiple roles.'
	),
	3 => array(
		'value' => 'N/A (I am not a coach.)',
		'label' => 'N/A (I am not a coach.)'
	),		
	
);

//coach_type_input input 
$input = array(
	'type' => 'select',
	'name' => 'coach_type',
	'class' => 'coach-type-question',
	'value' => '',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
);

$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if ($v['value'] == $preset_fields['coach_type']) {
		$selected = true;
	}
	$input['options'][$i] = array(
		'value' => $v['value'],
		'name' => $v['label'],
		'selected' => $selected
	);
	$i++;
}

$coach_type_input = $vce->content->create_input($input,'If you are a coach, do you serve as a full-time coach or do you serve in multiple roles?');

$content .= <<<EOF
<h2>If you are a coach, do you serve as a full-time coach or do you serve in multiple roles?</h2><br>
$coach_type_input
EOF;

//coachee_number_input input 
$input = array(
	'type' => 'text',
	'name' => 'coachee_number',
	'class' => 'coachee-number integer-input',
	'value' => $preset_fields['coachee_number'],
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
);
$coachee_number_input = $vce->content->create_input($input,'If you are a coach, how many coachees are in your caseload?');

$content .= <<<EOF
<div class="coachee-number1"><h2>If you are a coach, how many coachees are in your caseload?</h2><br>
$coachee_number_input
</div>
EOF;

	$these_options = array(
		0 => array(
			'value' => 1,
			'label' => 'yes'
		),
		1 => array(
			'value' => 0,
			'label' => 'No'
		),
	);
//organization input 
$input = array(
	'type' => 'radio',
	'name' => 'admin_question',
	'class' => 'admin-question',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
);

$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if (!empty($preset_fields['admin_question']) && $v['value'] == $preset_fields['admin_question']) {
		$selected = true;
	}
	$input['options'][$i] = array(
		'value' => $v['value'],
		'label' => $v['label'],
		'selected' => $selected
	);
	$i++;
}
$admin_question_input = $vce->content->create_input($input,'Will you be an HSCC Organization Administrator?');

$these_options = array(
	0 => array(
		'value' => 1,
		'label' => 'Yes'
	),
	1 => array(
		'value' => 0,
		'label' => 'No'
	),
);

$input = array(
	'type' => 'radio',
	'name' => 'org_registration_question',
	'class' => 'org-registration-question',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',
	),
);


$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if (!empty($preset_fields['org_registration_question']) && $v['value'] == $preset_fields['org_registration_question']) {
		$selected = true;
	}
	$input['options'][$i] = array(
		'value' => $v['value'],
		'label' => $v['label'],
		'selected' => $selected
	);
	$i++;
}

$org_registration_question_input = $vce->content->create_input($input,'Are you also registering a new organization?');

$new_org_registration_registration_content = $this->organization_ingression_form($vce, $each_component->url, $preset_fields);
$new_org_registration_accordion = $vce->content->accordion('Organization Registration', $new_org_registration_registration_content, $accordion_expanded = true, $accordion_disabled = true, $accordion_class = 'new-org-registration-accordion');

$content .= <<<EOF
<h2>Will you be an HSCC Organization Administrator?</h2>
The organization administrator organizes and manages the Head Start Coaching Companion account but does not need to be in an administrator role in the program.
<br> (Click &quot;yes&quot; to register a new organization.)<br>
$admin_question_input
$org_registration_question_input
$new_org_registration_accordion
EOF;

//notes input 
$input = array(
	'type' => 'textarea',
	'name' => 'notes',
	'value' => $preset_fields['notes'],
	'data' => array(
			'autocapitalize' => 'none',
	),
);
$notes_input = $vce->content->create_input($input,'Additional Notes (optional)');

$content .= <<<EOF
<h2>Additional Notes</h2><br>
$notes_input
EOF;




$content .= <<<EOF
<button class="submit-button button__primary" type="submit" value="Submit">Submit</button>

EOF;

$content .= <<<EOF
</form>
</div>
EOF;

		return $content;
	}



		/**
	 * content of individual ingression form
	 */
	public function organization_ingression_form ($vce, $this_url, $preset_fields) {

		// $dossier = array(
		// 	'type' => 'Pbc_Ingression',
		// 	'procedure' => 'process_org_ingression_request',
		// 	'this_url' => $this_url,
		// 	);
	
		// 	$dossier_for_process_ingression_request = $vce->generate_dossier($dossier);





		$content = NULL;
$content .= <<<EOF
<div>
EOF;

// $content .= <<<EOF
// <form id="pbc_org_ingression_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
// <input type="hidden" name="dossier" value="$dossier_for_process_ingression_request">
// EOF;

//new_organization_input
$input = array(
	'type' => 'text',
	'name' => 'org_new_organization',
	'class' => 'org-new-organization',
	'value' => $preset_fields['org_new_organization'],
	'data' => array(
			'autocapitalize' => 'none',
	)
);
$new_organization_input = $vce->content->create_input($input,"New Organization:");

$content .= <<<EOF
<h2>Your Organization Name</h2><br>
$new_organization_input
EOF;

//city input
$input = array(
	'type' => 'text',
	'name' => 'org_city',
	'class' => 'org-city',
	'value' => $preset_fields['org_city'],
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	)
);
$city_input = $vce->content->create_input($input,'City of Organization:');

//organization input 
$input = array(
	'type' => 'select',
	'name' => 'org_state',
	'class' => 'org-state',
	'value' => '',
	'options' => array(
		0 => array(
			'value' => '',
			'name' => ''
		)
	),
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
);

	$i = 1;
	foreach ($this->states($vce) as $k=>$v) {
		$selected = false;
		if ($k == $preset_fields['org_state']) {
			$selected = true;
		}
		$input['options'][$i] = array(
			'value' => $k,
			'name' => $k,
			'selected' => $selected
		);
		$i++;
	}

$state_input = $vce->content->create_input($input,'State of Organization:');

//region input
$these_options = array(
	0 => array(
		'value' => '',
		'name' => ''
	),
	1 => array(
		'value' => 'I',
		'name' => 'Region I (CT, ME, MA, NH, RI, VT)'
	),
	2 => array(
		'value' => 'II',
		'name' => 'Region II (NJ, NY, PR, VI)'
	),
	3 => array(
		'value' => 'III',
		'name' => 'Region III (DE, MD, PA, VA, WV, DC)'
	),
	4 => array(
		'value' => 'IV',
		'name' => 'Region IV (AL, FL, GA, KY, MS, NC, SC, TN)'
	),
	5 => array(
		'value' => 'V',
		'name' => 'Region V (IL, IN, MI, MN, OH, WI)'
	),
	6 => array(
		'value' => 'VI',
		'name' => 'Region VI (AR, LA, NM, OK, TX)'
	),
	7 => array(
		'value' => 'VII',
		'name' => 'Region VII (IA, KS, MO, NE)'
	),
	8 => array(
		'value' => 'VIII',
		'name' => 'Region VIII (CO, MT, ND, SD, UT, WY)'
	),
	9 => array(
		'value' => 'IX',
		'name' => 'Region IX (AZ, CA, HI, NV, AS, FM, GU, MH, PW, MP)'
	),
	10 => array(
		'value' => 'X',
		'name' => 'Region X (AK, ID, OR, WA)'
	),
	11 => array(
		'value' => 'XI',
		'name' => 'Region XI (American Indian and Alaska Native)'
	),
	12 => array(
		'value' => 'XII',
		'name' => 'Region XII (Migrant and Seasonal Head Start)'
	),
	13 => array(
		'value' => 'Not Applicable to Me',
		'name' => 'Not Applicable to Me'
	),
	14 => array(
		'value' => 'I&#39;m Not Sure of My Region',
		'name' => 'I&#39;m Not Sure of My Region'
	)
);

$input = array(
	'type' => 'select',
	'name' => 'org_region',
	'class' => 'org-region',
	'value' => '',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
);

$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if ($v['value'] == $preset_fields['org_region']) {
		$selected = true;
	}
	$input['options'][$i] = array(
		'value' => $v['value'],
		'name' => $v['name'],
		'selected' => $selected
	);
	$i++;
}
$region_input = $vce->content->create_input($input,'Organization Region');

$content .= <<<EOF
<h2>City, State and Region of Your Organization</h2><br>
$city_input
$state_input
$region_input
EOF;

//program size
$input = array(
'type' => 'text',
'name' => 'program_size',
'class' => 'integer-input',
'value' => $preset_fields['program_size'],
'data' => array(
		'autocapitalize' => 'none',
		'tag' => 'required',
)
);
$program_size_input = $vce->content->create_input($input,'Number of People Served by Program');

$content .= <<<EOF
<h2>Program Size/ Funded Enrollment</h2><br>
$program_size_input
EOF;

$these_options = array(
	0 => array(
		'name' => 'program_type_head_start',
		'label' => 'Head Start'
	),
	1 => array(
		'name' => 'program_type_early_head_start',
		'label' => 'Early Head Start'
	),
	2 => array(
		'name' => 'program_type_family_child_care',
		'label' => 'Family Child Care'
	),
	3 => array(
		'name' => 'program_type_home_based_home_visiting',
		'label' => 'Home-Based/Home Visiting'
	),
	4 => array(
		'name' => 'program_type_ehs_child_care_partnership',
		'label' => 'EHS-Child Care Partnership'
	),
	5 => array(
		'name' => 'program_type_aian',
		'label' => 'American Indian Alaska Native (AIAN)'
	),
	6 => array(
		'name' => 'program_type_mshs',
		'label' => 'Migrant and Seasonal Head Start (MSHS)'
	),
);

//program_type input
$input = array(
	'type' => 'checkbox',
	'name' => 'program_type',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',
	),
	'flags' => Array
	(
		'options_listed' => 1
	)
);

$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if (isset($preset_fields[$v['name']]) && $preset_fields[$v['name']] == 'on') {
		$selected = true;
	}
	$input['options'][$i] = array(
		'name' => $v['name'],
		'label' => $v['label'],
		'selected' => $selected
	);
	$i++;
}

$program_type_input = $vce->content->create_input($input,'Program Type (check all that apply)');

$input = array(
	'type' => 'text',
	'name' => 'program_type_new',
	'value' => $preset_fields['program_type_new'],
	'data' => array(
			'autocapitalize' => 'none',
	),
);
$new_program_type_input = $vce->content->create_input($input,'New Program Type (does not appear above)');

$content .= <<<EOF
<h2>Program Type (check all that apply)</h2><br>
$program_type_input
$new_program_type_input
EOF;


	$these_options = array(
			0 => array(
				'name' => 'program_option_center_based',
				'label' => 'Center-Based'
			),
			1 => array(
				'name' => 'program_option_home_based_home_visiting',
				'label' => 'Home-Based/ Home Visiting'
			),
			2 => array(
				'name' => 'program_option_family_child_care',
				'label' => 'Family Child Care'
			),
		);
//program_option input
$input = array(
	'type' => 'checkbox',
	'name' => 'program_option',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',
	),
	'flags' => Array
	(
		'options_listed' => 1
	)
);

$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if (isset($preset_fields[$v['name']]) && $preset_fields[$v['name']] == 'on') {
		$selected = true;
	}
	$input['options'][$i] = array(
		'name' => $v['name'],
		'label' => $v['label'],
		'selected' => $selected
	);
	$i++;
}

$program_option_input = $vce->content->create_input($input,'Program Option (check all that apply)');

$input = array(
	'type' => 'text',
	'name' => 'program_option_new',
	'value' => $preset_fields['program_option_new'],
	'data' => array(
			'autocapitalize' => 'none',
	),
);
$new_program_option_input = $vce->content->create_input($input,'Other (please specify)');

$content .= <<<EOF
<h2>Program Option (check all that apply)</h2><br>
$program_option_input
$new_program_option_input
EOF;


$these_options =array(
	0 => array(
		'value' => '',
		'label' => ''
	),
	1 => array(
		'value' => 'coaching_model_practice_based_coaching',
		'label' => 'Practice-Based Coaching'
	),
	2 => array(
		'value' => 'coaching_model_hybrid',
		'label' => 'Hybrid (PBC and Another Model)'
	),
	3 => array(
		'value' => 'coaching_model_other',
		'label' => 'Other Coaching Model'
	),
	4 => array(
		'value' => 'coaching_model_none',
		'label' => 'We do not currently have a coaching model'
	),
);

//coaching_model input
$input = array(
	'type' => 'select',
	'name' => 'coaching_model',
	'class' => 'coaching-model-question',
	'value' => '',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',

	),
	'flags' => Array
	(
		'options_listed' => 1
	)
);


$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if ($v['value'] == $preset_fields['coaching_model']) {
		$selected = true;
	}
	$input['options'][$i] = array(
		'value' => $v['value'],
		'name' => $v['label'],
		'selected' => $selected
	);
	$i++;
}
$coaching_model_input = $vce->content->create_input($input,'What is your current coaching model?');

$input = array(
	'type' => 'text',
	'name' => 'coaching_model_new',
	'class' => 'coaching-model-new',
	'value' => $preset_fields['coaching_model_new'],
	'data' => array(
			'autocapitalize' => 'none',
	),
);
$new_coaching_model_input = $vce->content->create_input($input,'Other (please specify)');

$content .= <<<EOF
<h2>What is your current coaching model?</h2><br>
$coaching_model_input
<div class="other-coaching-model-field"><h2>Other coaching model:</h2><br>
$new_coaching_model_input
</div>
EOF;

$these_options = array(
	0 => array(
		'name' => 'coaching_delivery_virtual',
		'label' => 'Virtual'
	),
	1 => array(
		'name' => 'coaching_delivery_in_person',
		'label' => 'In-Person'
	),
	2 => array(
		'name' => 'coaching_delivery_peer',
		'label' => 'Peer'
	),
	3 => array(
		'name' => 'coaching_delivery_group',
		'label' => 'Group'
	),
	4 => array(
		'name' => 'coaching_delivery_expert',
		'label' => 'Expert'
	),
	5 => array(
		'name' => 'coaching_delivery_tlc',
		'label' => 'Together Learning and Collaborating (TLC)'
	),
);
//coaching_delivery input
$input = array(
	'type' => 'checkbox',
	'name' => 'coaching_delivery',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',
	),
	'flags' => Array
	(
		'options_listed' => 1
	)
);
$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if (isset($preset_fields[$v['name']]) && $preset_fields[$v['name']] == 'on') {
		$selected = true;
	}
	$input['options'][$i] = array(
		'name' => $v['name'],
		'label' => $v['label'],
		'selected' => $selected
	);
	$i++;
}
$coaching_delivery_input = $vce->content->create_input($input,'What is your coaching delivery format? (check all that apply)');

$input = array(
	'type' => 'text',
	'name' => 'coaching_delivery_new',
	'value' => $preset_fields['coaching_delivery_new'],
	'data' => array(
			'autocapitalize' => 'none',
	),
);
$new_coaching_delivery_input = $vce->content->create_input($input,'Other (please specify)');

$content .= <<<EOF
<h2>What is your coaching delivery format? (Check all that apply. Consider all options within/beyond the pandemic.)</h2><br>
$coaching_delivery_input
$new_coaching_delivery_input
EOF;

// org_coaches_number input
$input = array(
	'type' => 'text',
	'name' => 'org_coaches_number',
	'class' => 'integer-input',
	'value' => $preset_fields['org_coaches_number'],
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
);
$coaches_number_input = $vce->content->create_input($input,'Number of Coaches in Your Organization');

$content .= <<<EOF
<h2>Number of Coaches in Your Organization</h2><br>
$coaches_number_input
EOF;

// org_coachees_number input
$input = array(
	'type' => 'text',
	'name' => 'org_coachees_number',
	'class' => 'integer-input',
	'value' => $preset_fields['org_coachees_number'],
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	),
);
$coachees_number_input = $vce->content->create_input($input,'Number of Coachees in Your Organization');

$content .= <<<EOF
<h2>Number of Coachees in Your Organization</h2><br>
$coachees_number_input
EOF;

$these_options = array(
	0 => array(
		'name' => 'coachee_roles_teachers',
		'label' => 'Teachers'
	),
	1 => array(
		'name' => 'coachee_roles_teacher_assistants',
		'label' => 'Teacher Assistants'
	),
	2 => array(
		'name' => 'coachee_roles_visitors',
		'label' => 'Home Visitors'
	),
	3 => array(
		'name' => 'coachee_roles_family_child_care_providers',
		'label' => 'Family Child Care Providers '
	),
	4 => array(
		'name' => 'coachee_roles_family_service_workers',
		'label' => 'Family Service Workers'
	),
);
//coachee_roles input
$input = array(
	'type' => 'checkbox',
	'name' => 'coachee_roles',
	'options' => array(),
	'data' => array(
			'autocapitalize' => 'none',
	),
	'flags' => Array
	(
		'options_listed' => 1
	)
);
$i = 0;
foreach ($these_options as $k=>$v) {
	$selected = false;
	if (isset($preset_fields[$v['name']]) && $preset_fields[$v['name']] == 'on') {
		$selected = true;
	}
	$input['options'][$i] = array(
		'name' => $v['name'],
		'label' => $v['label'],
		'selected' => $selected
	);
	$i++;
}
$coachee_roles_input = $vce->content->create_input($input,'What are the roles of coachees in your organization? (Check all that apply. Consider all options within/beyond the pandemic.)');

$input = array(
	'type' => 'text',
	'name' => 'coachee_role_new',
	'value' => $preset_fields['coachee_role_new'],
	'data' => array(
			'autocapitalize' => 'none',
	),
);
$new_coachee_role_input = $vce->content->create_input($input,'Other (please specify)');

$content .= <<<EOF
<h2>What are the roles of coachees in your organization? (Check all that apply. Consider all options within/beyond the pandemic.)</h2><br>
$coachee_roles_input
$new_coachee_role_input
EOF;

//coaching_companion_use input
$input = array(
	'type' => 'textarea',
	'name' => 'coaching_companion_use',
	'value' => $preset_fields['coaching_companion_use'],
	'data' => array(
			'autocapitalize' => 'none',
			'tag' => 'required',
	)
	);
	$coaching_companion_use_input = $vce->content->create_input($input,'Description');
	
	$content .= <<<EOF
	<h2>How would you like to use Coaching Companion for your organization?</h2><br>
	$coaching_companion_use_input
	EOF;


// $content .= <<<EOF
// <button class="submit-button button__primary" type="submit" value="Submit">Submit</button>
// </form>
// EOF;

$content .= <<<EOF
</div>
EOF;

		return $content;
	}


	public function process_choose_form($input) {
		global $vce;

		
		// exit;
		$vce->site->add_attributes('form_to_show', $input['choose_form']);
		$redirect_url = $vce->site->site_url . '/' . $input['url'];
		echo json_encode(array('response' => 'success','procedure' => 'choose_form', 'url' => $redirect_url, 'action' => 'reload','message' => 'Loading application form...'));
		return;
	}

	public function process_ingression_request($input) {


		global $vce;

		// $vce->log($input);
		// $vce->plog($input);

		// require a wait time of a few minutes before a user can submit a new form
		$query = "SELECT created FROM " . TABLE_PREFIX . "ingression_forms WHERE user_id='" . $input['user_id'] . "' ORDER BY created DESC LIMIT 1";
		$last_form = $vce->db->get_data_object($query);

		if (isset($last_form[0]->created)) {
			$last_form = $last_form[0]->created;

			$minutes_to_add = 5;
			$time = new DateTime($last_form);
			$time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
			$stamp = $time->format('Y-m-d H:i:s');

			$right_now = new DateTime('NOW');
			$right_now = $right_now->format('Y-m-d H:i:s');


			if ($right_now < $stamp) {
				$redirect_url = '';
				echo json_encode(array('response' => 'error', 'form' => 'process_ingression_form', 'procedure' => 'process_ingression_request', 'url' => $redirect_url, 'action' => 'reload','message' => 'You have already submitted this form less than five minutes ago. Redirecting to home page.'));
				return;
			}
		}

		// get user vector
		$query = "SELECT vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $input['user_id'] . "' LIMIT 1";
		$user_vector = $vce->db->get_data_object($query);
		
		// set vector of user filling out the form
		$vector = $user_vector[0]->vector;

		//remove current_created_at field if the value is NULL
		if (!isset($input['current_created_at_date'])) {
			unset($input['current_created_at_date']);
		}


		// get all input form fields and convert into list for query
		$insert_fields = array();
		$insert_values = array();
		foreach ($input as $k=>$v) {
			if ($k == 'type' || $k == 'this_url'|| $k == 'email2') {
				continue;
			}

			if ($k == 'email' || $k == 'first_name'|| $k == 'last_name' || $k == 'current_first_name'|| $k == 'current_last_name') {
				// encode user data			
				$v = $vce->user->encryption($v, $vector);
			}

			$v = ($v != '')? $v:NULL;
			$insert_fields[] = "`$k`";
			$insert_values[] = "'$v'";
		}
		$insert_fields = implode(',', $insert_fields);
		$insert_values = implode(',', $insert_values);
		$query = "INSERT INTO `" . TABLE_PREFIX . "ingression_forms` ($insert_fields) VALUES ($insert_values)";
		// $vce->log($query);
		$results = $vce->db->query($query);


		if (!empty($results) && $results == TRUE) {

			//store "done" flag in user attributes so that filling out the form is only required once
			$this_user_id = $vce->user->user_id;
			$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id='" . $this_user_id . "' AND meta_key='intake_form'";
			$results = $vce->db->get_data_object($query);

			if (empty($results)) {
				$input = array(
					'user_id' => $this_user_id,
					'notification_name' => 'intake_form',
					'notification_metadata' => 'recorded on ' . date(DATE_RFC2822),
				);
				$this->acknowledge_notifications($input);
			}

			$redirect_url = $vce->site->site_url;
			echo json_encode(array('response' => 'success','procedure' => 'process_ingression_request', 'url' => $redirect_url, 'action' => 'reload','message' => 'Intake form recorded. Redirecting to home page.'));
			return;
		}

		$redirect_url = $vce->site->site_url;
		echo json_encode(array('response' => 'error','procedure' => 'process_ingression_request',
		
		'message' => 'Intake form was not recorded. Please check fields and re-submit.'));
		return;

	}

	public function process_org_ingression_request($input) {
		// Array
// (
//     [type] => Pbc_Ingression
//     [this_url] => 
//     [new_organization] => safd
//     [city] => asfd
//     [state] => OREGON
//     [first_name] => sdf
//     [last_name] => asfd
//     [admin_title] => fd
//     [email] => sfa
//     [email2] => df
//     [empty] => f
//     [Early_Head_Start] => on
//     [new_program_type] => asfsafd
//     [Center-Based] => on
//     [new_program_option] => df
//     [Hybrid_(PBC_and_Another_Model)] => on
//     [new_coaching_model] => safadsf
//     [Peer] => on
//     [new_coaching_delivery] => dfdf
//     [coaches_number] => sfsfs
//     [coachees_number] => dfd
//     [Teacher_Assistants] => on
//     [new_coachee_roles] => sadf
// )
		global $vce;

		// $vce->log($input);
		// exit;
		// $redirect_url = $vce->site->site_url . '/' . $input['this_url'];
		$redirect_url = $vce->site->site_url . '/home';
		echo json_encode(array('response' => 'success','procedure' => 'process_org_ingression_request', 'url' => $redirect_url, 'action' => 'reload','message' => 'Organization application recorded.'));
		return;
	}

	/**
	 * record acknowledgement of mandatory form 
	 */
	public function acknowledge_notifications($input) {

		global $vce;


		// rekey input
		foreach ($input as $key=>$value) {
			$$key = $value;
		}
		
		$notification_title = ucwords(preg_replace('/_/', ' ', $notification_name));
		// $notification_metadata = $user->encryption($notification_metadata, $user->vector);
		//set new user attribute, using the name of the notification
		$query = "INSERT INTO " . TABLE_PREFIX . "users_meta (user_id, meta_key, meta_value, minutia) VALUES ('$user_id','$notification_name','$notification_metadata', '" . time() . "')";
		// $site->log($query);
		$vce->db->query($query);

		return;

	}


	public function organizations($vce) {
		$organizations = array();
                // load hooks
                if (isset($vce->site->hooks['get_organizations_and_groups'])) {
                    foreach ($vce->site->hooks['get_organizations_and_groups'] as $hook) {
						$user_info = new stdClass;
						$user_info->class_of_origin = 'Pbc_Ingression';
                        $organizations = call_user_func($hook, $user_info);
                    }
                }
		return $organizations;
	}

	public function states($vce) {

		$us_state_abbrevs_names = Array(
			'ALABAMA' => 'AL',
			'ALASKA' => 'AK',
			'AMERICAN SAMOA' => 'AS',
			'ARIZONA' => 'AZ',
			'ARKANSAS' => 'AR',
			'CALIFORNIA' => 'CA',
			'COLORADO' => 'CO',
			'CONNECTICUT' => 'CT',
			'DELAWARE' => 'DE',
			'DISTRICT OF COLUMBIA' => 'DC',
			'FEDERATED STATES OF MICRONESIA' => 'FM',
			'FLORIDA' => 'FL',
			'GEORGIA' => 'GA',
			'GUAM' => 'GU',
			'HAWAII' => 'HI',
			'IDAHO' => 'ID',
			'ILLINOIS' => 'IL',
			'INDIANA' => 'IN',
			'IOWA' => 'IA',
			'KANSAS' => 'KS',
			'KENTUCKY' => 'KY',
			'LOUISIANA' => 'LA',
			'MAINE' => 'ME',
			'MARSHALL ISLANDS' => 'MH',
			'MARYLAND' => 'MD',
			'MASSACHUSETTS' => 'MA',
			'MICHIGAN' => 'MI',
			'MINNESOTA' => 'MN',
			'MISSISSIPPI' => 'MS',
			'MISSOURI' => 'MO',
			'MONTANA' => 'MT',
			'NEBRASKA' => 'NE',
			'NEVADA' => 'NV',
			'NEW HAMPSHIRE' => 'NH',
			'NEW JERSEY' => 'NJ',
			'NEW MEXICO' => 'NM',
			'NEW YORK' => 'NY',
			'NORTH CAROLINA' => 'NC',
			'NORTH DAKOTA' => 'ND',
			'NORTHERN MARIANA ISLANDS' => 'MP',
			'OHIO' => 'OH',
			'OKLAHOMA' => 'OK',
			'OREGON' => 'OR',
			'PALAU' => 'PW',
			'PENNSYLVANIA' => 'PA',
			'PUERTO RICO' => 'PR',
			'RHODE ISLAND' => 'RI',
			'SOUTH CAROLINA' => 'SC',
			'SOUTH DAKOTA' => 'SD',
			'TENNESSEE' => 'TN',
			'TEXAS' => 'TX',
			'UTAH' => 'UT',
			'VERMONT' => 'VT',
			'VIRGIN ISLANDS' => 'VI',
			'VIRGINIA' => 'VA',
			'WASHINGTON' => 'WA',
			'WEST VIRGINIA' => 'WV',
			'WISCONSIN' => 'WI',
			'WYOMING' => 'WY',
			'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST' => 'AE',
			'ARMED FORCES AFRICA' => 'AE',
			'ARMED FORCES CANADA' => 'AE',
			'ARMED FORCES EUROPE' => 'AE',
			'ARMED FORCES MIDDLE EAST' => 'AE',
			'ARMED FORCES AMERICA (EXCEPT CANADA)' => 'AA',
			'ARMED FORCES PACIFIC' => 'AP',
		);

		$states_array = $us_state_abbrevs_names;

		return $states_array;
	}
	

	/**
	 * fields for ManageRecipe
	 */
	public function recipe_fields($recipe) {
	
		global $vce;
	
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