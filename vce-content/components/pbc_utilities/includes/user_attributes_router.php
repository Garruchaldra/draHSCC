<?php 
		/**
		 * this is a routing utility for the hook "manage_users_attributes"
		 * There are a few different Components using this hook, and this is essentially a Switch to deliver different content depending on
		 * what the component needs.
		 */

		global $vce;
		global $page;  
	
	
		// add org_group_list and native_org_group to session var.
		// these two user attributes are what allows multi-organizational users
		// get user vector
		$query = "SELECT vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_info->user_id . "' LIMIT 1";
		$user_vector = $vce->db->get_data_object($query);
		$user_info->user_vector = $user_vector[0]->vector;
	
		// get user_id
		$user_id = $user_info->user_id;
		
		// get org_group_list and native_group_list from db
		$query = "SELECT a.meta_value as ogl, b.meta_value as nog FROM " . TABLE_PREFIX . "users_meta as a INNER JOIN " . TABLE_PREFIX . "users_meta as b ON a.user_id = b.user_id WHERE a.user_id = $user_id AND a.meta_key = 'org_group_list' AND b.meta_key = 'native_org_group'";
		$meta_data = $vce->db->get_data_object($query);
	
		// either org_group_list and native_group_list exist, or are set to NULL
		foreach ($meta_data as $each_meta) {
			if (isset($each_meta->ogl)) {
				$user_info->org_group_list = $vce->user->decryption($each_meta->ogl,$user_info->user_vector);
				$user_info->native_org_group = $vce->user->decryption($each_meta->nog,$user_info->user_vector);
			} else {
				$org_group_list = null;
				$native_org_group = null;
			}
		}
	// $vce->log($user_info->org_group_list);
	// $vce->log($native_org_group);	 
	
	
	 // All common requirements:
	 
		// get role hierarchy
		$roles = json_decode($vce->site->roles, true);
	
		// get roles in hierarchical order
		$roles_hierarchical = json_decode($vce->site->site_roles, true);
		
		// preserve the info sent to this method				
		$original_user_info = $user_info;
		//if no user_info is specified, make the user the current logged in user
		// (either an admin needs info about another user, or a user needs info about themselves)	
		if (!isset($user_info->user_id)) {
			$user_info = $vce->user;
		 }
		 
		 // set user's role hierarchy
		$user_info->role_hierarchy = $roles[$user_info->role_id]['role_hierarchy'];
	// 		$vce->log($user_info->role_hierarchy);
		
		// find id of New User organization
		$new_user_id = self::new_user_id();
	
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/../js/groups.js');	
	
	
		//  organization and group of user being edited. If no one is beeing edited, active user is used
		$user_organization = isset($user_info->organization) ? $user_info->organization : $vce->user->organization;
		$user_group = isset($user_info->group) ? $user_info->group : null;
	 
	
		 // Get organization of user, and all groups assigned to that organization
	
		// Get datalist of organizations
		$attributes = array(
		'name' => 'organization'
		);
		$organizations = $vce->get_datalist_items($attributes);
	
		// set datalist id to get groups, which is a sub-datalist
		$datalist_id = $organizations['datalist_id'];
		
		// set dossier to send when requesting groups 
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
								return (strcmp($a->$meta_key, $b->$meta_key) > 0) ? -1 : 1;
						} else {
								return (strcmp($a->$meta_key, $b->$meta_key) > 0) ? 1 : -1;
						}
				} else {
						return 1;
				}
		});  
		// this is an ordered list of organizations
		$organizations['items'] =	$options_items ;
		
		//get the org. name of the user being edited by comparing the list of organization names to the id listed with the org.
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
		
		// get all groups which belong to the user's organization in an array
		$groups = $vce->get_datalist_items($attributes);
		// find group name of the group that the edited user belongs to by comparing each group name 
		if (isset($groups['items'])) {
			foreach ($groups['items'] as $each_group) {
				if ($each_group['item_id'] == $user_group) {
					$group_name = $each_group['name'];
				}
			}
		}
		
	 
	 // ROUTE request for form fields to appropriate method. Depends on the requesting component as well as the
	// role_id 
	// the permissions array determines which subset of the Organizations datalist you can see
		 $permissions = array();
		 
	 // switch between requesting components
		 switch ($user_info->component_name) {
			 case 'request_access':
				 //show all the organizations, no subset
				 $permissions['show_these_organizations'] = $organizations['items'];
				 $route = 0;
				 break;
			 case 'grant_access':
	// 	 		 		$vce->dump($user_info);
	
				 //show only the organization of the organization admin
				 if (isset($organizations['items'])) {
					 $organizations_to_show = array();
					foreach($organizations['items'] as $this_organization) {
						if ($this_organization['item_id'] == $user_info->organization) {
							$organizations_to_show[] = $this_organization;
						}
					}
				}
				 $permissions['show_one_organization'] = $organizations_to_show;
				 $permissions['show_groups'] = $groups['items'];
				$route = 1;
				 break;
			 // usersettings is "My Account"
			 case 'usersettings':
	// $user_info->role_hierarchy = 3;
				 // filter what to show depending on role, show both org and group
				//Admin, Site Admin
				 if ($user_info->role_hierarchy < 3) {
					 //show all the organizations, no subset
					$permissions['show_these_organizations'] = $organizations['items'];
					$permissions['show_groups'] = $groups['items'];
					$route = 1;
				 }
				// Org Admin
				/*
					 Usersettings is the page where a user can navigate between the Organizations and Groups that 
					 they have access to. A user which is not a Multi-Organizational user is governed by their role 
					 as to which are available. A Multi-Organizational user has other access. The rules are: 
					 - an Admin or Site Admin has access to all organizations and groups
					 - an Organization Admin is rooted to one Organization, but has access to all groups
					 - a Group Admin, Coach and Coachee is rooted to one Organization 
					 - a Multi-Organizational Coach is not a role, but the state of having a list of other organizations
					 - to work within other Organizations and Groups, a user must switch these groups in Usersettings (myAccout)
					 - a Multi-Organizational Coach has access to their native Org/Group as well as any other Organizations to which they were accepted
					 - a Multi-Organizational Coach assumes the access allowed by the record of each Organization, which includes the Org, Group, and Role
					 - when a Multi-Organizational Coach is in a non-native Org, they won't show up in other userlists. This needs to be altered.
				 */
				 if ($user_info->role_hierarchy == 3) {
	
				//show only the organization of the user, unless they have an org_group_list metadata (are a floating coach)
				// if we have our list of organizations
				if (isset($organizations['items'])) {
					// $vce->log($user_info);
						// variable for all organizations that show up in the dropdown (the actual org objects)
						$organizations_to_show = array();
						// variable for all org id's which get converted into organizations_to_show
	
					// if an org_group_list exists, get it, and add native_org_group to list
					if (isset($user_info->org_group_list)) {
						$org_group_list = json_decode($user_info->org_group_list, true);
						// add native organization and group to org_group_list
						$native_org_group = json_decode($user_info->native_org_group, true);
						foreach ($native_org_group as $key=>$value) {
							$org_group_list[$key] = $value;
						}
						// loop through all organizations and add org_group_list orgs to the display list
						foreach($organizations['items'] as $this_organization) {
							// if there is an org_group_list, use it to get the organization data
							if (isset($org_group_list)) {
								// loop through the org_group_list to see if it matches an organization
								foreach ($org_group_list as $key=>$value) {
									// put matching organizations in organizations_to_show
									if ($this_organization['item_id'] == $key) {
										$organizations_to_show[] = $this_organization;
									}
								}
							} 
						}
					} else {
						// if this is not a multi-org coach, only add the native org to the list
						foreach($organizations['items'] as $this_organization) {
							// put matching organizations in organizations_to_show
							if ($this_organization['item_id'] == $user_info->organization) {
								$organizations_to_show[] = $this_organization;
							} 
						}
					}
	
				}
				
					if (count($organizations_to_show) > 1) {
						$permissions['show_these_organizations'] = $organizations_to_show;
					} else {
						$permissions['show_one_organization'] = $organizations_to_show;
					}
	
					//get the group(s) related to the current organization
	
					if (isset($user_info->native_org_group)) {
						$native_org_group = json_decode($user_info->native_org_group, true);
						// $vce->dump($user_info->organization);
						foreach ($native_org_group as $native_org) {
							foreach ($native_org as $key => $value) {
								if ($user_info->group == $key) {
									$permissions['show_groups'] = $groups['items'];
								}
							}
						}
					} else {
						$permissions['show_groups'] = $groups['items'];		
					}
	
					// $vce->dump($user_info);
					// $vce->dump($permissions);
					$route = 1;
				 }
			 
				 //Group Admin, Coach
				 if ($user_info->role_hierarchy == 4 || $user_info->role_hierarchy == 5) {
	
					//show only the organization of the user, unless they have an org_group_list metadata (are a floating coach)
					// if we have our list of organizations
					if (isset($organizations['items'])) {
						// variable for all organizations that show up in the dropdown (the actual org objects)
						$organizations_to_show = array();
						// variable for all org id's which get converted into organizations_to_show
	
						// if an org_group_list exists, get it. If it doesn't, create it
						if (isset($user_info->org_group_list)) {
							$org_group_list = json_decode($user_info->org_group_list, true);
						} else {
							$org_group_list = array($user_info->organization => array($user_info->group => $user_info->role_id));
						}
	
						
						// add native organization and group to org_group_list
						$native_org_group = json_decode($user_info->native_org_group, true);
						foreach ($native_org_group as $key=>$value) {
							$org_group_list[$key] = $value;
						}
						// loop through all organizations
						foreach($organizations['items'] as $this_organization) {
							// if there is an org_group_list, use it to get the organization data
							if (isset($org_group_list)) {
								// loop through the org_group_list to see if it matches an organization
								foreach ($org_group_list as $key=>$value) {
									// put matching organizations in organizations_to_show
									if ($this_organization['item_id'] == $key) {
										$organizations_to_show[] = $this_organization;
									}
								}
							} 
						}
					}
	
					if (count($organizations_to_show) > 1) {
						$permissions['show_these_organizations'] = $organizations_to_show;
					} else {
						$permissions['show_one_organization'] = $organizations_to_show;
					}
	
					// $vce->dump($user);
					// foreach ($groups['items'] as $key1 => $val1) {
					// 	foreach ($org_group_list as $key2 => $val2) {
					// 		foreach ($val2 as $key3 => $val3) {
					// 			if ($key1 == $key3) {
					// 				$permissions['show_groups'] = array($key1 => $val1);
					// 			}
					// 		}
					// 	}
					// }
					$permissions['show_one_group'] = $user_info->group;
					$route = 1;
				 }
				 // Coachee
				 if ($user_info->role_hierarchy == 6) {
					 $native_org_group = json_decode($user_info->native_org_group, true);
	
					//show only the organization of the user
					// if we have our list of organizations
					if (isset($organizations['items'])) {
						// variable for all organizations that show up in the dropdown (the actual org objects)
						$organizations_to_show = array();
						// variable for all org id's which get converted into organizations_to_show
						$org_group_list = array();
						// if an org_group_list exists, get it
						if (isset($user_info->org_group_list)) {
							$org_group_list = json_decode($user_info->org_group_list, true);
						}
						// add native organization and group to org_group_list
						$native_org_group = json_decode($user_info->native_org_group, true);
						$org_group_list[] = array(0=>$native_org_group[0], 1=>$native_org_group[1]);
						// loop through all organizations
						foreach($organizations['items'] as $this_organization) {
							if ($this_organization['item_id'] == $user_info->organization) {
								$organizations_to_show[] = $this_organization;
							} 
						}
					}
	
					$permissions['show_one_organization'] = $organizations_to_show;
					$permissions['show_one_group'] = $user_info->group;
					
					$route = 1;
				 }
				 
				 break; 			
	
			 default:
				 
				 break;
		 }
		 
	// 	 	if (!isset($user_info->component)) {
	// 	 		$route = 0;
	// 	 	}
	// 	 	if ($user_info->component == 'request_access') {
	// 	 		$route = 1;
	// 	 	}
		 
		 // now that we have a route number, we do what needs doing
		switch ($route) {
			case 0:  // for request access
				//get the org. name of the user being edited
				if (isset($organizations['items'])) {
					foreach ($organizations['items'] as $each_organization) {
						if ($each_organization['item_id'] == $user_organization) {
							$organization_name = $each_organization['name'];
						}
					}
				}
				
				
				if (key_exists('show_these_organizations', $permissions)) {
	// Show Organization dropdown
	// 	$vce->dump('org_group_list');
		//get the org. name of the edited user	               
		if (isset($permissions['show_these_organizations'])) {
			foreach ($permissions['show_these_organizations'] as $each_option) {
				if ($each_option['item_id'] == $user_info->organization) {
					$organization_name = $each_option['name'];
				}
			}
		}
		
		if (isset($user_info->native_org_group)) {
			$native_org = json_decode($user_info->native_org_group, true);
			$native_org = $native_org[0];
		}
		$role_hierarchy = $vce->user->role_hierarchy;
		
	$insert = <<<EOF
	<label>
	<select name="organization" native_org="$native_org"  datalist_id="$datalist_id" dossier="$dossier_for_organization" role_hierarchy="$role_hierarchy" tag="required">
	<option value=""></option>
EOF;
	
		if (isset($permissions['show_these_organizations'])) {
			foreach ($permissions['show_these_organizations'] as $each_option) {
				$insert .= '<option value="' . $each_option['item_id'] . '"';
				if ($each_option['item_id'] == $user_info->organization) {
					$insert .= ' selected';
				}
				$insert .= '>' . $each_option['name'] . '</option>';
				$vce->requested_organization_name = $each_option['name'];
			}
		}
		
	$insert .= <<<EOF
	</select>
	<div class="label-text">
	<div class="label-message">Organization
		<div class="tooltip-icon">
			<div class="tooltip-content">
				If a user&apos;s group or organization is changed, they must log in anew before the change will take effect. This includes Admins!
			</div>
		</div>
	</div>
	<div class="label-error">Select An Organization</div>
	</div>
	</label>
EOF;
	
	}
				
				break;
			case 1:  // for usersettings (My Account)
				
	// What to see
	// Get Organizations 
	
	// Show one Organization; no dropdown		
	if (key_exists('show_one_organization', $permissions)) {
		$input = array(
			'type' => 'hidden',
			'name' => 'organization',
			'value' => $user_info->organization,
			'flags' => array(
				'append' => $organization_name,
			),
		);
			$org_inputs = $vce->content->create_input($input,'Organization','Organization is required', 'add-padding hidden-input');
			$insert .= <<<EOF
			$org_inputs
EOF;
	
	}
	
	if (key_exists('show_these_organizations', $permissions)) {
	// Show Organization dropdown
	// $vce->dump('org_group_list');
		//get the org. name of the edited user	               
		if (isset($permissions['show_organizations'])) {
			foreach ($permissions['show_organizations'] as $each_option) {
				if ($each_option['item_id'] == $user_info->organization) {
					$organization_name = $each_option['name'];
				}
			}
		}
		// $vce->dump($native_org_group);
		$native_org = (isset($native_org_group[0]) ? $native_org_group[0] : null);
		$org_list = $user_info->org_group_list;
		$role_hierarchy = $vce->user->role_hierarchy;
	$insert = <<<EOF
	<label>
	<select name="organization" org_list="$org_list" native_org="$native_org" datalist_id="$datalist_id" dossier="$dossier_for_organization" role_hierarchy="$role_hierarchy" tag="required">
	<!-- <option value=""></option> -->
EOF;
	
		if (isset($permissions['show_these_organizations'])) {
			foreach ($permissions['show_these_organizations'] as $each_option) {
				$insert .= '<option value="' . $each_option['item_id'] . '"';
				if ($each_option['item_id'] == $user_info->organization) {
					$insert .= ' selected';
				}
				$insert .= '>' . $each_option['name'] . '</option>';
			}
		}

		if (isset($permissions['show_these_organizations'])) {
			$options_array = array();
			$options_array[] = array(
				'name' => '',
				'value' => ''
			);
			foreach ($permissions['show_these_organizations'] as $each_option) {
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
			$insert = <<<EOF
			$org_inputs
EOF;
	
	}
	
	
	// for Groups:
	if (key_exists('show_one_group', $permissions)) {
		// Show one Group; no edit possibilities		

		$input = array(
			'type' => 'hidden',
			'name' => 'group',
			'value' => $user_group,
			'data' => array(
				'tag' => 'required'
			),
			'flags' => array(
				'prepend' => $group_name,
			),
		);
			$group_inputs = $vce->content->create_input($input,'Group', '', 'add-padding hidden-input');
			$insert .= <<<EOF
			$group_inputs
EOF;
	
	}
	
	
	
	// Show Group dropdown
	if (key_exists('show_groups', $permissions)) {

		
		$options_array = array();
		$options_array[] = array(
			'name' => '',
			'value' => ''
		);

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
			// $vce->dump($input);
		
			$group_inputs = $vce->content->create_input($input,'Group');
			$insert .= <<<EOF
			$group_inputs
EOF;
		
	
	}
				
				break;
			case 2:
				
				break;
		}
	 
		//return all the html to insert into content 
		// return $insert;