<?php

	/**
	 * This is used in a hook to put form fields into any place which needs them. 
	 * The first use of this is to add Organization and Group fields into Manage Users and My Account
	 * This has become complicated due to differing needs in various components and a complicated structure of permissions based on roles.
	 *
	 */
		 
	global $page;
	global $vce;


// get role hierarchy
$roles = json_decode($vce->site->roles, true);

// start content
$insert = NULL;

// get roles in hierarchical order
$roles_hierarchical = json_decode($vce->site->site_roles, true);

// preserve the info sent to this method				
$original_user_info = $user_info;


//if no user_info is specified, make the user the current logged in user	
// $vce->dump($user_info);
if (!isset($user_info->user_id)) {
	$user_info = $vce->user;
 }
 // set user's role hierarchy
$user_info->role_hierarchy = $roles[$user_info->role_id]['role_hierarchy'];


// This is to remove the user's role restrictions for when every user should see the same thing
// In this case, the Pbc_Ingression class is sending a property called "class_of_origin" to avoid the organization from being
// shown as both a dropdown and as an info field. This property is sent in the generic "user_info" argument sent
// when this hook is called.

// $vce->log($original_user_info);
if (isset($original_user_info->class_of_origin) && $original_user_info->class_of_origin == 'Pbc_Ingression') {
	$user_info->role_hierarchy = 1;
}

// $vce->dump($user_info);
//find out if this call is coming from the request_access component
if (isset($original_user_info->request_access) && !isset($original_user_info->grant_access)) {
	$vce->user->role_id = 4;
}

// find id of New User organization
$new_user_id = self::new_user_id();

// add javascript to page 
$vce->site->add_script(dirname(__FILE__) . '/../js/groups.js');	
//  organization and group of user being edited
// $vce->dump($user_info);
$user_organization = isset($user_info->organization) ? $user_info->organization : $vce->user->organization;
$user_group = isset($user_info->group) ? $user_info->group : null;
// $vce->dump($user_info);

$attributes = array(
	'name' => 'organization'
);

$organizations = $vce->get_datalist_items($attributes);


// set datalist var
$datalist_id = $organizations['datalist_id'];

$dossier_for_organization = $vce->user->encryption(json_encode(array('type' => 'Pbc_utilities','procedure' => 'groups')),$vce->user->session_vector);

//order the Organizations alphabetically
$options_items = isset($organizations['items']) ? $organizations['items'] : null;
$meta_key = 'name';
$order = 'asc';
usort($options_items, function($a, $b) use ($meta_key, $order, $page) {
		$a = (object) $a;
		$b = (object) $b;
		if (isset($a->$meta_key) && isset($b->$meta_key)) {
				if ($order == "desc") {
						return (strcmp(strtolower($a->$meta_key), strtolower($b->$meta_key)) > 0) ? -1 : 1;
				} else {
						return (strcmp(strtolower($a->$meta_key), strtolower($b->$meta_key)) > 0) ? 1 : -1;
				}
		} else {
				return 1;
		}
});  
// this is an ordered list of organizations
$organizations['items'] =	$options_items ;
// $vce->dump($user_info);
// $vce->dump($user_organization);
//get the org. name of the user being edited
if (isset($organizations['items'])) {
	foreach ($organizations['items'] as $each_organization) {
		if ($each_organization['item_id'] == $user_organization) {
			$organization_name = $each_organization['name'];
		}
	}
}

//get groups which belong to this specific user's organization
$attributes = array(
'parent_id' => $datalist_id,
'item_id' => $user_organization
);

$groups = $vce->get_datalist_items($attributes);
// find group name of the group that the edited user belongs to
if (isset($groups['items'])) {
	foreach ($groups['items'] as $each_group) {
		if ($each_group['item_id'] == $user_group) {
			$group_name = $each_group['name'];
		}
	}
}



// decide who sees what	
/**
 * 
 *  THIS IS THE ONLY CURRENT DEPARTURE FROM "add_user_attributes"
 * 
 */
// $vce->dump($vce->user->role_hierarchy);
// $vce->user->role_hierarchy = 3;
// $vce->dump($vce->user->role_hierarchy);
// unset($user_info->org_group_list);
// unset($user_info->group_list);

$permissions = array();
// site admin, global admin
// if ($vce->user->role_hierarchy <= 2) {
$permissions['show_organizations'] = $organizations['items'];
$permissions['show_groups'] = $groups['items'];
// }



// organization admin
if ($vce->user->role_hierarchy == 3) {
if (isset($organizations['items'])) {
	$organizations_subset = array();
		// if user has a list of additional organizations
	if (isset($user_info->org_group_list)) {
		$list_of_additional_groups = 'restrict_groups="' . $user_info->group_list . '"';
		$native_organization = 'native_org="' . $user_info->organization . '"';

		$organizations_in_dropdown = explode('|', $user_info->org_group_list);
		$organizations_in_dropdown[] = $user_info->organization;
		foreach ($organizations['items'] as $each_organization) {
			if (in_array($each_organization['item_id'] , $organizations_in_dropdown)) {
				$organizations_subset[] = $each_organization;
			}
		}
		$permissions['show_organizations'] = $organizations_subset;
	} elseif ($user_info->organization != $vce->user->organization) {
		$organizations_in_dropdown = array();
		$organizations_in_dropdown[] = $vce->user->organization;
		$organizations_in_dropdown[] = $user_info->organization;
		foreach ($organizations['items'] as $each_organization) {
			if (in_array($each_organization['item_id'] , $organizations_in_dropdown)) {
				$organizations_subset[] = $each_organization;
			}
		}
		$permissions['show_organizations'] = $organizations_subset;
	
	} else {
		$permissions['show_one_organization'] = $user_info->organization;
	}
}



$permissions['show_groups'] = $groups['items'];	
}

// group admin
if ($vce->user->role_hierarchy == 4) {
if (isset($organizations['items'])) {
	$organization_subset = array();
		// if user has a list of additional organizations
	if (isset($user_info->org_group_list)) {
		$list_of_additional_groups = 'restrict_groups="' . $user_info->group_list . '"';
		$native_organization = 'native_org="' . $user_info->organization . '"';

		$organizations_in_dropdown = explode('|', $user_info->org_group_list);
		$organizations_in_dropdown[] = $user_info->organization;
		foreach ($organizations['items'] as $each_organization) {
			if (in_array($each_organization['item_id'] , $organizations_in_dropdown)) {
				$organizations_subset[] = $each_organization;
			}
		}
		$permissions['show_organizations'] = $organizations_subset;

	} elseif ($user_info->organization != $vce->user->organization) {
		$organizations_in_dropdown = array();
		$organizations_in_dropdown[] = $vce->user->organization;
		$organizations_in_dropdown[] = $user_info->organization;
		foreach ($organizations['items'] as $each_organization) {
			if (in_array($each_organization['item_id'] , $organizations_in_dropdown)) {
				$organizations_subset[] = $each_organization;
			}
		}
		$permissions['show_organizations'] = $organizations_subset;
	
	} else {
		$permissions['show_one_organization'] = $user_info->organization;
	}
}


if (isset($groups['items'])) {
	$groups_subset = array();
	if (isset($user_info->group_list)) {
		$groups_in_dropdown = explode('|', $user_info->group_list);
		$groups_in_dropdown[] = $user_info->group;
		foreach ($groups['items'] as $each_group) {
			if ($each_group['item_id'] == $user_info->group) {
				$groups_subset[] = $each_group;
			}
		}
		$permissions['show_groups'] = $groups_subset;

	} elseif ($user_info->organization != $vce->user->organization) {
		$groups_in_dropdown = array();
		$groups_in_dropdown[] = $vce->user->group;
		$groups_in_dropdown[] = $user_info->group;
		foreach ($groups['items'] as $each_group) {
			if (in_array($each_group['item_id'] , $groups_in_dropdown)) {
				$groups_subset[] = $each_group;
			}
		}
		$permissions['show_groups'] = $groups_subset;
	
	} else {
		$permissions['show_one_group'] = $user_info->group;
	}

}

}

$organization = (isset($organization) ? $organization : '');
$list_of_additional_groups = (isset($list_of_additional_groups) ? $list_of_additional_groups : '');
$native_organization = (isset($native_organization) ? $native_organization : '');
$role_hierarchy = (isset($role_hierarchy) ? $role_hierarchy : '');





// What to see
// Get Organizations 

// Show one Organization; no edit possibilities			
if (key_exists('show_one_organization', $permissions)) {
		$input = array(
			'type' => 'hidden',
			'name' => 'organization',
			'value' => $user_info->organization,
			'flags' => array(
				'append' => $organization_name,
			),
		);
			$org_inputs = $vce->content->create_input($input,'Organization','Enter Organization', 'add-padding hidden-input');
			$insert .= <<<EOF
			$org_inputs
EOF;

}

if (key_exists('show_organizations', $permissions)) {
	// Show Organization dropdown
	// 	$vce->dump('org_group_list');
	//get the org. name of the edited user	               
	if (isset($permissions['show_organizations'])) {
		foreach ($permissions['show_organizations'] as $each_option) {
			if ($each_option['item_id'] == $user_info->organization) {
				$organization_name = $each_option['name'];
			}
		}
	}




// Org input
$options_array = array();
$options_array[] = array(
	'name' => '',
	'value' => ''
);
if (isset($permissions['show_organizations'])) {
	foreach ($permissions['show_organizations'] as $each_option) {
		$this_option = array(
			'name' => $each_option['name'],
			'value' => $each_option['item_id'],
		);
		if ($each_option['item_id'] == $user_info->organization) {
			$this_option['selected'] = true; 
		}
		$options_array[] = $this_option;
	}
}
// $vce->dump($options_array);
$input = array(
	'type' => 'select',
	'name' => 'organization',
	'data' => array(
		'tag' => 'required',
		'datalist_id' => $datalist_id,
		'dossier' => $dossier_for_organization,
		'role_hierarchy' => $vce->user->$role_hierarchy
	),
	'options' => $options_array
	);
	if (isset($user_info->form_id)) {
		$input['data']['form_id'] = $user_info->form_id;
	}
	if (isset($user_info->action)) {
		$input['data']['action'] = $user_info->action;
	}
	// if (isset($user_info->group_class)) {
	// 	$input['data']['group_class'] = $user_info->group_class;
	// }
	if (isset($user_info->org_class)) {
		$input['class'] = $user_info->org_class;
	}
	// $vce->dump($input);
	// add additional data to the select tag for JS use
	if (isset($list_of_additional_groups) && strpos($list_of_additional_groups, '=') != false) {
		$data_item = explode('=', $list_of_additional_groups);
		$input['data'][trim($data_item[0])] = trim($data_item[1]);
	}
	if (isset($native_organization) && strpos($native_organization, '=') != false) {
		$data_item = explode('=', $native_organization);
		$input['data'][trim($data_item[0])] = trim($data_item[1]);
	}

	$org_inputs = $vce->content->create_input($input,'Organization');
	$insert .= <<<EOF
	$org_inputs
EOF;

}


if (key_exists('show_one_group', $permissions)) {
		// Show one Group; no edit possibilities		

	$input = array(
		'type' => 'hidden',
		'name' => 'group',
		'value' => $user_group,
		'flags' => array(
			'prepend' => $group_name,
		),
	);
		$group_inputs = $vce->content->create_input($input,'Group','Enter your Group', 'add-padding hidden-input');
		$insert .= <<<EOF
		$group_inputs
EOF;

}



// Show Group dropdown
// Group input
$options_array = array();
$options_array[] = array(
	'name' => '',
	'value' => ''
);
if (isset($permissions['show_groups'])) {
	foreach ($permissions['show_groups'] as $each_group) {
		$this_option = array(
			'name' => $each_group['name'],
			'value' => $each_group['item_id'],
		);
		if ($each_group['item_id'] == $user_group) {
			$this_option['selected'] = true; 
		}
		$options_array[] = $this_option;
	}

// $vce->dump($options_array);
$input = array(
	'type' => 'select',
	'name' => 'group',
	'data' => array(
		'tag' => 'required',
		'datalist_id' => $datalist_id,
	),
	'options' => $options_array
	);

	if (isset($user_info->group_class)) {
		$input['class'] = $user_info->group_class;
	}

	if (isset($user_info->form_id)) {
		$input['data']['form_id'] = $user_info->form_id;
	}

	// $vce->dump($input);
	$group_inputs = $vce->content->create_input($input,'Group');

	$hidden_input = NULL;
	if ($vce->user->role_hierarchy == 4) { 
		$admin_group_id = $vce->user->group;
		$hidden_input = <<<EOF
<input type="hidden" class="admin_group_id" name="no_name" value="no_value" admin_group_id="$admin_group_id"><br>
EOF;
	}
	$insert .= <<<EOF
	$hidden_input
	$group_inputs
EOF;


}
