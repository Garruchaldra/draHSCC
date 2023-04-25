<?php


/**
 * This is the hook method which is used to set the $filter_by variable by adding ->filter_by_ attributes to the page
 */	
	
$new_user_org = self::new_user_id();
		
if (!isset($filter_by['group'])) {
    $filter_by['group'] = array();
    $previous_filter_by = false;
} else {
    $filter_by['group'] = array($filter_by['group']);
    $previous_filter_by = true;
}

if (!isset($filter_by['organization'])) {
    $filter_by['organization'] = array();
    $previous_filter_by = false;
} else {
    $filter_by['organization'] = array($filter_by['organization']);
    $previous_filter_by = true;
}

// $vce->dump($vce->user_search_results);

// if a user search has been initiated, find out if the user searched for is in "New Users" and change the filtered by to that group
if (isset($vce->user_search_results) && !empty($vce->user_search_results)) {

    // get roles
    $roles = json_decode($vce->site->roles, true);

    // get roles in hierarchical order
    $roles_hierarchical = json_decode($vce->site->site_roles, true);
// $vce->dump($vce->user_search_results);
// exit;
    $this_user_search_result = json_decode($vce->user_search_results,true);
    $this_user_search_result  = (!is_array($this_user_search_result)) ? array($this_user_search_result) : $this_user_search_result;

    $vce->user_search_results = implode( ',', $this_user_search_result);

    //get users from search results
    $query = "SELECT * FROM " . TABLE_PREFIX . "users WHERE user_id IN (" . $vce->user_search_results . ")";
    $current_list = $vce->db->get_data_object($query);

    // rekey data into array for user_id and vectors
    foreach ($current_list as $each_list) {
        $users_list[] = $each_list->user_id;
        $users[$each_list->user_id]['user_id'] = $each_list->user_id;
        $users[$each_list->user_id]['role_id'] = $roles[$each_list->role_id]['role_name'];
        $vectors[$each_list->user_id] = $each_list->vector;
    }

    // Second we query the user_meta table for user_ids



    // get all meta data for all users because of filtering
    $query = "SELECT * FROM " . TABLE_PREFIX . "users_meta";


    $meta_data = $vce->db->get_data_object($query);

    // rekey data
    foreach ($meta_data as $each_meta_data) {

        // skip lookup
        if ($each_meta_data->meta_key == 'lookup') {
            continue;
        }
        // skip if not in the search results
        if (!in_array($each_meta_data->user_id, $users_list)) {
            continue;
        }
    
        // add
        $users[$each_meta_data->user_id][$each_meta_data->meta_key] = User::decryption($each_meta_data->meta_value,$vectors[$each_meta_data->user_id]);
    }
    
    // check to see if any search results users are in "New Users" or in the right organization
    foreach ($users as $this_user) {
        if (isset($this_user['organization']) && ($this_user['organization'] == $new_user_org['organization'] || $this_user['organization'] == $vce->user->organization)) {
            $filter_by['organization'][] = $this_user['organization'];
            $filter_by['group'][] = $this_user['group'];
        }
    }
}

if ($vce->user->role_hierarchy > 2) {
    if (!in_array($vce->user->organization,$filter_by['organization']) && !in_array($new_user_org['organization'],$filter_by['organization'])) {
        $filter_by['organization'][] = $vce->user->organization;
    }
    if ($vce->user->role_hierarchy > 3) {
        if (!in_array($vce->user->group, $filter_by['group']) && !in_array($new_user_org['group'],$filter_by['group'])) {
            $filter_by['group'][] = $vce->user->group;
        }	
    }
}

if ($vce->user->role_hierarchy < 3 && $previous_filter_by == false) {
    unset($filter_by['organization']);
    unset($filter_by['group']);
}
if (empty($filter_by['organization'])) {
    unset($filter_by['organization']);
}
if (empty($filter_by['group'])) {
    unset($filter_by['group']);
}
// $filter_by['group']
			// $vce->dump($filter_by);
// return $filter_by;

