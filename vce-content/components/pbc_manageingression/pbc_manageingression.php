<?php

class Pbc_ManageIngression extends Component {

    public function component_info() {
        return array(
            'name' => 'PBC Manage Ingression',
            'description' => 'Show and Process Ingression Applications',
            'category' => 'pbc',
            'permissions' => array(
                array(
                    'name' => 'create_users',
                    'description' => 'Role can create new users',
                ),
                array(
                    'name' => 'edit_users',
                    'description' => 'Role can delete users',
                ),
                array(
                    'name' => 'delete_users',
                    'description' => 'Role can delete users',
                ),
                array(
                    'name' => 'masquerade_users',
                    'description' => 'Role can masquerade as users',
                ),
            ),
        );
    }

    /**
     *
     */
    public function as_content($each_component, $vce) {

        // add javascript to page
        $vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tabletocard');

        $vce->site->add_style(dirname(__FILE__) . '/css/style.css');

        // preset selections of users
        $user_selections = array(
            0 => "unprocessed",
            1 => "processed",
            2 => "existing_user",
            3 => "new_user",
            4 => "changed_basic_info",
            5 => "registering_new_organization",
        );

        $selection_has_been_made = FALSE;
        foreach ($user_selections as $k=>$v) {
            if (isset($vce->$v)) {
                $selection_has_been_made = TRUE;
            }
        }

        if ($selection_has_been_made == FALSE) {
            $vce->site->add_attributes('unprocessed', 'on', TRUE);
            $vce->site->add_attributes('processed', 'on', TRUE);
            $vce->site->add_attributes('existing_user', 'on', TRUE);
            $vce->site->add_attributes('new_user', 'on', TRUE);
            $vce->site->add_attributes('user_selection_added', 'yes', TRUE);
        }



        // add the "$inputtypes" value for use in all forms
		// This is to guard against asynchronous call errors when JS is not enabled.
		$inputtypes = json_encode(array());

        // minimal user attributers
        $default_attributes = array(
            'user_id' => array(
                'title' => 'Id',
                'sortable' => 1,
            ),
            'role_id' => array(
                'title' => 'Role',
                'sortable' => 1,
            ),
            'email' => array(
                'title' => 'Email',
                'required' => 1,
                'type' => 'text',
                'sortable' => 1,
            ),
        );

        // load hooks for title bar
		if (isset($vce->site->hooks['titleBar'])) {
			foreach ($vce->site->hooks['titleBar'] as $hook) {
				$title = call_user_func($hook, 'Manage Intake Applications ..', 'manage-ingression');
			}
		}

		$vce->content->add('title', $title);

        $user_attributes = json_decode($vce->site->user_attributes, true);

        $attributes = array_merge($default_attributes, $user_attributes);

        $filter_by = array();

        foreach ($vce as $key => $value) {
            if (strpos($key, 'filter_by_') !== FALSE) {
                $filter_by[str_replace('filter_by_', '', $key)] = $value;
            }
        }

        // manage_users_attributes_filter_by
        if (isset($vce->site->hooks['manage_users_attributes_filter_by'])) {
            foreach ($vce->site->hooks['manage_users_attributes_filter_by'] as $hook) {
                $filter_by = call_user_func($hook, $filter_by, $vce);
            }
        }

        // check if edit_user is within the page object, which means we want to edit this user
        $edit_user = isset($vce->edit_user) ? $vce->edit_user : null;

        // get roles
        $roles = json_decode($vce->site->roles, true);

        // get roles in hierarchical order
        $roles_hierarchical = json_decode($vce->site->site_roles, true);

        // create var for content
        $content = null;

        // variables
        $sort_by = isset($vce->sort_by) ? $vce->sort_by : 'user_id';
        $sort_direction = isset($vce->sort_direction) ? $vce->sort_direction : 'DESC';
        $display_users = true;
        $pagination = true;
        $pagination_current = isset($vce->pagination_current) ? $vce->pagination_current : 1;
        $pagination_length = isset($vce->pagination_length) ? $vce->pagination_length : 25;


        //get lists of users who have INGRESSION applications, depending on the saved user_selection attributes
        // array for all id's we want to display
        $ingression_form_user_ids = array();
        //create an array for users to take out of list
        $remove_users_array = array();
        $ingression_forms_processed = array();
        $ingression_forms_unprocessed = array();

        // if (isset($vce->unprocessed) && $vce->unprocessed == 'on') {

            // find all unprocessed users
            $query = "SELECT id, created, user_id FROM " . TABLE_PREFIX . "ingression_forms WHERE processed IS NULL ORDER BY created ASC";
            $vce->db->query($query);
            $results = $vce->db->get_data_object($query);
            if (!empty($results)) {
                foreach ($results as $this_result) {
                    $ingression_form_user_ids[$this_result->user_id] = $this_result->user_id;
                    $ingression_forms_unprocessed[$this_result->user_id]['created'] = $this_result->created;
                }
            }
        // }

        // find all processed users
        // if (isset($vce->processed) && $vce->processed == 'on') {
            $query = "SELECT created, processed, user_id FROM " . TABLE_PREFIX . "ingression_forms WHERE processed IS NOT NULL ORDER BY created ASC";
            $vce->db->query($query);
            $results = $vce->db->get_data_object($query);
            if (!empty($results)) {
                foreach ($results as $this_result) {
                    $ingression_form_user_ids[$this_result->user_id] = $this_result->user_id;
                    $ingression_forms_processed[$this_result->user_id]['created'] = $this_result->created;
                    $ingression_forms_processed[$this_result->user_id]['processed'] = $this_result->processed;
                }
            }

// }

         // remove all processed users if they were processed after an unprocessed form (to remove double-forms)
            foreach ($ingression_form_user_ids as $this_user_id=>$v){
                // remove users from processed or unprocessed lists if the created date on one is higher than the other
                if (isset($ingression_forms_processed[$this_user_id]['created']) && isset($ingression_forms_unprocessed[$this_user_id]['created'])) {
                    if ($ingression_forms_unprocessed[$this_user_id]['created'] > $ingression_forms_processed[$this_user_id]['created']) {
                        unset($ingression_forms_processed[$this_user_id]);
                    } else {
                        unset($ingression_forms_unprocessed[$this_user_id]);
                    }
                }
                // remove unprocessed users if looking at the processed only
                if ((isset($vce->processed) && $vce->processed == 'on') && (!isset($vce->unprocessed) || $vce->unprocessed != 'on')) {
                    if (!isset($ingression_forms_processed[$this_user_id])) {
                        unset($ingression_form_user_ids[$this_user_id]);
                    }
                    // remove processed users if looking at the unprocessed only
                } elseif ((isset($vce->unprocessed) && $vce->unprocessed == 'on') && (!isset($vce->processed) || $vce->processed != 'on')) {
                    if (!isset($ingression_forms_unprocessed[$this_user_id])) {
                        unset($ingression_form_user_ids[$this_user_id]);
 
                    }
                }
            }        


            $unprocessed_user_ids = array();
            $processed_user_ids = array();
            $ingression_forms_users_list = array();
            foreach ($ingression_forms_unprocessed as $k=>$v) {
                $unprocessed_user_ids[] = $k;
                $timestamp = strtotime($v['created']);
                $ingression_forms_users_list[$timestamp] = $k;
            }

            unset($k);
            foreach ($ingression_forms_processed as $k=>$v) {
                $processed_user_ids[] = $k;
                $timestamp = strtotime($v['created']);
                $ingression_forms_users_list[$timestamp] = $k;
            }

            krsort($ingression_forms_users_list);

        // remove all users who are not existing users
        if (!isset($vce->existing_user) || $vce->existing_user != 'on') {
            $query = "SELECT created, user_id FROM " . TABLE_PREFIX . "ingression_forms WHERE current_site_role != 'NewUsers'  AND user_id NOT IN (SELECT user_id FROM " . TABLE_PREFIX . "ingression_forms WHERE current_site_role = 'NewUsers') ORDER BY created ASC";
            $vce->db->query($query);
            $results = $vce->db->get_data_object($query);
            if (!empty($results)) {
                foreach ($results as $this_result) {
                    $remove_users_array[] = $this_result->user_id;
                }
            }
        }

        // remove all users who are not new users
        if (!isset($vce->new_user) || $vce->new_user != 'on') {
            $query = "SELECT created, user_id FROM " . TABLE_PREFIX . "ingression_forms WHERE current_site_role = 'NewUsers' AND user_id NOT IN (SELECT user_id FROM " . TABLE_PREFIX . "ingression_forms WHERE current_site_role != 'NewUsers')";
            $vce->db->query($query);
            $results = $vce->db->get_data_object($query);
            if (!empty($results)) {
                foreach ($results as $this_result) {
                    $remove_users_array[] = $this_result->user_id;
                }
            }
        }

        // remove all users who are not registering a new organization
        if (isset($vce->registering_new_organization) && $vce->registering_new_organization == 'on') {
            $query = "SELECT created, user_id FROM " . TABLE_PREFIX . "ingression_forms WHERE org_registration_question IS NULL AND user_id NOT IN (SELECT user_id FROM " . TABLE_PREFIX . "ingression_forms WHERE org_registration_question IS NOT NULL) ORDER BY created ASC";
            $vce->db->query($query);
            $results = $vce->db->get_data_object($query);
            if (!empty($results)) {
                foreach ($results as $this_result) {
                    $remove_users_array[] = $this_result->user_id;
                }
            }
        }

        // remove all users who have not changed basic info 
        // $remove_unchanged_users = array();
        // if (isset($vce->registering_new_organization) && $vce->registering_new_organization == 'on') {
        //     $query = "SELECT created, user_id FROM " . TABLE_PREFIX . "ingression_forms WHERE org_registration_question IS NULL AND user_id NOT IN (SELECT user_id FROM " . TABLE_PREFIX . "ingression_forms WHERE org_registration_question IS NOT NULL) ORDER BY created ASC";
        //     $vce->db->query($query);
        //     $results = $vce->db->get_data_object($query);
        //     if (!empty($results)) {
        //         foreach ($results as $this_result) {
        //             $remove_users_array[] = $this_result->user_id;
        //         }
        //     }
        // }


        // remove users from removal list
        $ingression_form_user_ids = array_diff($ingression_form_user_ids, $remove_users_array);
        //combine all relevant user_ids and create select list separated by commas
        $ingression_form_user_ids = array_unique($ingression_form_user_ids);
        //sort
        $temp_ingression_form_user_ids = array();
        foreach($ingression_forms_users_list as $k=>$v) {
            if (isset($ingression_form_user_ids[$v])) {
                $temp_ingression_form_user_ids[] = $ingression_form_user_ids[$v];
            }
        }
        $ingression_form_user_ids = implode(',', $temp_ingression_form_user_ids);
        // $ingression_form_user_ids = implode(',', $ingression_form_user_ids);


        // create search in values
        $role_id_in = array();
        foreach ($roles_hierarchical as $roles_each) {
            foreach ($roles_each as $key => $value) {
                if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
                    // add to role array
                    $role_id_in[] = $key;
                }
            }
        }

        // get total count of users
        $query = "SELECT count(*) as count FROM " . TABLE_PREFIX . "users WHERE role_id IN (" . implode(',', $role_id_in) . ") AND user_id IN ($ingression_form_user_ids)";

        $count_data = $vce->db->get_data_object($query);
        // set variable
        $pagination_count = $count_data[0]->count;

        $number_of_pages = ceil($pagination_count / $pagination_length);

        // prevent errors if input number is bad
        if ($pagination_current > $number_of_pages) {
            $pagination_current = $number_of_pages;
        } else if ($pagination_current < 1) {
            $pagination_current = 1;
        }

        $pagination_offset = ($pagination_current != 1) ? ($pagination_length * ($pagination_current - 1)) : 0;

        // First we query the user table to get user_id and vector

        // search results
        if (isset($vce->user_search_results) && !empty($vce->user_search_results)) {

            $pagination = false;
            $sort_by = null;

            $query = "SELECT * FROM " . TABLE_PREFIX . "users WHERE user_id IN (" . implode(json_decode($vce->user_search_results, true), ',') . ") AND user_id IN ($ingression_form_user_ids)";

        }  else {
            // towards the standard way
            // with role_id filter
            if (!empty($filter_by)) {
                $query = "SELECT * FROM " . TABLE_PREFIX . "users WHERE user_id IN ($ingression_form_user_ids)";
                $pagination = false;
                $sort_by = null;
            } else if ($sort_by == 'user_id' || $sort_by == 'role_id') {
                // if user_id or role_id is the sort
                $query = "SELECT * FROM " . TABLE_PREFIX . "users WHERE role_id IN (" . implode(',', $role_id_in) . ") AND user_id IN ($ingression_form_user_ids)  ORDER BY FIELD (user_id,$ingression_form_user_ids) LIMIT " . $pagination_length . " OFFSET " . $pagination_offset;
            } else {
                // the standard way
                $query = "SELECT " . TABLE_PREFIX . "users.* FROM " . TABLE_PREFIX . "users_meta INNER JOIN " . TABLE_PREFIX . "users ON " . TABLE_PREFIX . "users_meta.user_id = " . TABLE_PREFIX . "users.user_id AND users.user_id IN ($ingression_form_user_ids) WHERE " . TABLE_PREFIX . "users.role_id IN (" . implode(',', $role_id_in) . ") AND " . TABLE_PREFIX . "users_meta.meta_key='" . $sort_by . "' GROUP BY " . TABLE_PREFIX . "users_meta.user_id ORDER BY " . TABLE_PREFIX . "users_meta.minutia " . $sort_direction . " LIMIT " . $pagination_length . " OFFSET " . $pagination_offset;
            }

        }


        // get info for display_users
        $current_list = $vce->db->get_data_object($query);

        // rekey data into array for user_id and vectors
        foreach ($current_list as $each_list) {
            $users_list[] = $each_list->user_id;
            $users[$each_list->user_id]['user_id'] = $each_list->user_id;
            $users[$each_list->user_id]['role_id'] = $each_list->role_id;
            $users[$each_list->user_id]['role_name'] = $roles[$each_list->role_id]['role_name'];
            $vectors[$each_list->user_id] = $each_list->vector;
        }

        // Second we query the user_meta table for user_ids

        if (isset($users_list)) {

            // get meta data for the list of user_ids
            $query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id IN (" . implode(',', $users_list) . ")";

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
            $users[$each_meta_data->user_id][$each_meta_data->meta_key] = $vce->user->decryption($each_meta_data->meta_value, $vectors[$each_meta_data->user_id]);
        }

        /* start user edit */

        // we want to edit this user
        // check permissions for edit users
        if (isset($edit_user) && $vce->check_permissions('edit_users')) {

            // edit user and display users exist simultaneously
            $sort_by = null;
            $edit_user_query = "SELECT * FROM " . TABLE_PREFIX . "users WHERE user_id = '" . $edit_user . "'";

        

        //get info for edit user
        $edit_user_list = $vce->db->get_data_object($edit_user_query);

        // rekey data into array for user_id and vectors
        foreach ($edit_user_list as $each_edit_user_list) {
            $edit_list[] = $each_edit_user_list->user_id;
            $edit_users[$each_edit_user_list->user_id]['user_id'] = $each_edit_user_list->user_id;
            $edit_users[$each_edit_user_list->user_id]['role_id'] = $each_edit_user_list->role_id;
            $edit_users[$each_edit_user_list->user_id]['role_name'] = $roles[$each_edit_user_list->role_id]['role_name'];
            $vectors[$each_edit_user_list->user_id] = $each_edit_user_list->vector;
        }

        if (isset($edit_list)) {

            $edit_list = (!is_array($edit_list)) ? array($edit_list) : $edit_list;
            // get meta data for the list of user_ids
            $query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id IN (" . implode(',', $edit_list) . ")";

        }

        $meta_data = $vce->db->get_data_object($query);

        // rekey data
        foreach ($meta_data as $each_meta_data) {

            // skip lookup
            if ($each_meta_data->meta_key == 'lookup') {
                continue;
            }

            // add
            $edit_users[$each_meta_data->user_id][$each_meta_data->meta_key] = $vce->user->decryption($each_meta_data->meta_value, $vectors[$each_meta_data->user_id]);
        }

            // get user info and cast as an object (the variable $edit_user (singular) is the user passed to the $vce object for editing)
            $user = (object) $edit_users[$edit_user];

            // create the dossier
            $dossier_for_update = $vce->generate_dossier(array('type' => 'Pbc_ManageIngression', 'procedure' => 'update', 'user_id' => $edit_user));
            $edit_user_content = NULL;

            $ingression_form_info_content = $this->ingression_form_info($vce, $edit_user);
            $ingression_form_info_accordion = $vce->content->accordion('Intake Form Information', $ingression_form_info_content, $accordion_expanded = false, $accordion_disabled = false, $accordion_class = 'ingression-form-info-accordion');
            
            $edit_user_content .= <<<EOF

$ingression_form_info_accordion

EOF;
$edit_user_content .= <<<EOF
<div>
<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
EOF;
    
    $email_input = $vce->content->create_input($user->email,'Email (Cannot be edited)','Enter Email');
    $edit_user_content .= <<<EOF
     $email_input
EOF;




foreach ($user_attributes as $user_attributes_key => $user_attributes_value) {

    // nice title for this user attribute
    $title = isset($user_attributes_value['title']) ? ucwords(str_replace('_', ' ', $user_attributes_value['title'])) : ucwords(str_replace('_', ' ', $user_attributes_key));

    // check if required
    $tag = (isset($user_attributes_value['required']) && $user_attributes_value['required'] == '1') ? 'required' : null;

    // attribute value
    $attribute_value = isset($user->$user_attributes_key) ? $user->$user_attributes_key : null;

    // if a datalist has been assigned
    if (isset($user_attributes_value['datalist'])) {

        if (!is_array($user_attributes_value['datalist'])) {
            $datalist_field = 'datalist';
            $datalist_value = $user_attributes_value['datalist'];
        } else {
            $datalist_field = array_keys($user_attributes_value['datalist'])[0];
            $datalist_value = $user_attributes_value['datalist'][$datalist_field];
        }

        $options_data = $vce->get_datalist_items(array($datalist_field => $datalist_value));

        $options = array();

        if (!empty($options_data)) {
            foreach ($options_data['items'] as $option_key => $option_value) {
                $options[$option_key] = $option_value['name'];
            }
        }
    }

    // if options is set
    if (isset($user_attributes_value['options'])) {
        $options = $user_attributes_value['options'];
    }

    if (isset($user_attributes_value['type'])) {
        $attribute_type = $user_attributes_value['type'];

        // skip if conceal
        if ($user_attributes_value['type'] == 'conceal') {
            continue;
        }

        // attributes input (this creates form fields depending on user attribute settings)
        $input = array(
            'type' => $attribute_type,
            'name' => $user_attributes_key,
            'value' => $attribute_value,
            'data' => array(
                'autocapitalize' => 'none',
                'tag' => 'required',
            )
        );

        $attribute_input = $vce->content->create_input($input,$title,'Enter a '.$title);
        $edit_user_content .= <<<EOF
        $attribute_input
EOF;

    }
}

            // load hooks
            if (isset($vce->site->hooks['manage_users_attributes'])) {
                foreach ($vce->site->hooks['manage_users_attributes'] as $hook) {
                    $edit_user_content .= call_user_func($hook, $user);
                }
            }

            // role input
            $options_array = array();
            $options_array[] = array(
                'name' => '',
                'value' => ''
            );


            foreach ($roles_hierarchical as $roles_each) {
                foreach ($roles_each as $key => $value) {
                    if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
                            $selected = $key == $user->role_id ? true : false;
                            $options_array[] = array(
                            'name' => $value['role_name'],
                            'value' => $key,
                            'selected' => $selected
                            );
                        }
                    }
                }

            $input = array(
            'type' => 'select',
            'name' => 'role_id',
            'data' => array(
                'tag' => 'required',
            ),
            'options' => $options_array
            );

            $role_input = $vce->content->create_input($input,'Role');
            $delete_users = $vce->check_permissions('delete_users') ? true : false;
            $edit_user_content .= <<<EOF
                $role_input
                <input type="submit" value="Update User">
                <div class="link-button cancel-button">Cancel</div>
                </form>
                </div>
EOF;
if ($delete_users) {
    $dossier_for_delete = $vce->generate_dossier(array('type' => 'Pbc_ManageIngression', 'procedure' => 'delete', 'user_id' => $edit_user));
    $edit_user_content .= <<<EOF
        <div>
        <form class="delete-form inline-form asynchronous-form" method="post" action="$vce->input_path">
        <input type="hidden" name="dossier" value="$dossier_for_delete">
        <button type="submit" class="btn-reset delete-button" title="Delete" value="Delete">Delete User</button>
        </form>
        </div>
EOF;
}

            // create accordion box
            // $edit_user_content .= $vce->content->accordion('Edit User', $edit_user_content, true, false);
            /* end user edit */


        } 
   /* start of new user */

            // check permissions for create users
            if ($vce->check_permissions('create_users')) {

                // create the dossier
                $dossier_for_create = $vce->generate_dossier(array('type' => 'Pbc_ManageIngression', 'procedure' => 'create'));

                $new_user_content = <<<EOF
<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_create">
EOF;

    // email input
    $input = array(
        'type' => 'text',
        'name' => 'email',
        'data' => array(
            'autocapitalize' => 'none',
            'tag' => 'required',
        )
    );
    
    $email_input = $vce->content->create_input($input,'Email','Enter Your Email');
    $new_user_content .= <<<EOF
        $email_input
EOF;


//                 if (isset($user_attributes['password']['type']) && $user_attributes['password']['type'] == 'conceal') {

//                     $password = $vce->user->generate_password();

//                     $new_user_content .= <<<EOF
// <input type="hidden" name="password" value="$password">
// EOF;

//                 } else {

//                     // the standard user create form with password input

//    // password input
//    $input = array(
//     'type' => 'text',
//     'name' => 'password',
//     'data' => array(
//         'autocapitalize' => 'none',
//         'tag' => 'required',
//     )
// );

// $pw_input = $vce->content->create_input($input,'Password','Enter a Password');
// $new_user_content .= <<<EOF
//     $pw_input
// EOF;

//                 }

                foreach ($user_attributes as $user_attributes_key => $user_attributes_value) {

                    // nice title for this user attribute
                    $title = isset($user_attributes_value['title']) ? ucwords(str_replace('_', ' ', $user_attributes_value['title'])) : ucwords(str_replace('_', ' ', $user_attributes_key));

                    // check if required
                    $tag = (isset($user_attributes_value['required']) && $user_attributes_value['required'] == '1') ? 'required' : null;

                    // if a datalist has been assigned
                    if (isset($user_attributes_value['datalist'])) {
 
                        if (!is_array($user_attributes_value['datalist'])) {
                            $datalist_field = 'datalist';
                            $datalist_value = $user_attributes_value['datalist'];
                        } else {
                            $datalist_field = array_keys($user_attributes_value['datalist'])[0];
                            $datalist_value = $user_attributes_value['datalist'][$datalist_field];
                        }

                        $options_data = $vce->get_datalist_items(array($datalist_field => $datalist_value));

                        $options = array();

                        if (!empty($options_data)) {
                            foreach ($options_data['items'] as $option_key => $option_value) {
                                $options[$option_key] = $option_value['name'];
                            }
                        }
                    }

                    // if options is set
                    if (isset($user_attributes_value['options'])) {
                        $options = $user_attributes_value['options'];
                    }

                    if (isset($user_attributes_value['type'])) {
                        $attribute_type = $user_attributes_value['type'];

                        // skip if conceal
                        if ($user_attributes_value['type'] == 'conceal') {
                            continue;
                        }

   // attributes input (this creates form fields depending on user attribute settings)
   $input = array(
    'type' => $attribute_type,
    'name' => $user_attributes_key,
    'data' => array(
        'autocapitalize' => 'none',
        'tag' => 'required',
    )
);

$attribute_input = $vce->content->create_input($input,$title,'Enter a '.$title);
$new_user_content .= <<<EOF
    $attribute_input
EOF;

                    }
                }

                // load hooks
                if (isset($vce->site->hooks['manage_users_attributes'])) {
                    foreach ($vce->site->hooks['manage_users_attributes'] as $hook) {
                        $new_user_content .= call_user_func($hook, $new_user_content);
                    }
                }


   // role input
   $options_array = array();
   $options_array[] = array(
        'name' => '',
        'value' => ''
        // 'selected' => true
    );
   foreach ($roles_hierarchical as $roles_each) {
        foreach ($roles_each as $key => $value) {
            if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
                $options_array[] = array(
                    'name' => $value['role_name'],
                    'value' => $key
                    // 'selected' => true
                );
            }
        }
    }

   $input = array(
    'type' => 'select',
    'name' => 'role_id',
    'data' => array(
        'tag' => 'required',
    ),
    'options' => $options_array
);

$role_input = $vce->content->create_input($input,'Role');
$new_user_content .= <<<EOF
    $role_input
    <input type="submit" value="Create User" class="button__primary"></input>
EOF;

//                 if (!isset($user_attributes['password']) || !isset($user_attributes['password']['type']) || $user_attributes['password']['type'] != 'conceal') {

//                     $new_user_content .= <<<EOF
// <div id="generate-password" class="link-button">Generate Password</div>
// EOF;

//                 }

                $new_user_content .= <<<EOF
</form>
EOF;

///// NEW USER this negates the above and puts a link to the user ingression form
$vce->site->add_attributes('form_to_show', 'ingression_form');
$ingression_form_url = $vce->site->site_url . '/user_application';
$new_user_content = <<<EOF
<div class="tab-content">
<br>
<br>
<a href="$ingression_form_url">&nbsp;&nbsp;&nbsp;Fill out a new user in the ingression form here.</a>
<br>
<br>
</div>
EOF;


// create accordion box
// $content .= $vce->content->accordion('Create New User', $new_user_content, false, false);

            }

            /* end of new user */

            /* start search */

            // dossier for search
            $dossier = array(
                'type' => 'Pbc_ManageIngression',
                'procedure' => 'search',
            );

            // generate dossier
            $dossier_for_search = $vce->generate_dossier($dossier);

            $input_value = isset($vce->search_value) ? $vce->search_value : null;

            $search_content = <<<EOF
EOF;

            if (isset($vce->user_search_results) && empty($vce->user_search_results)) {

                $search_content = <<<EOF
<div class="form-message form-error">No Matches Found</div>
EOF;

            }

    // search input
    $input = array(
        'type' => 'text',
        'name' => 'search',
        'value' => $input_value,
        'data' => array(
            'autocapitalize' => 'none',
            'tag' => 'required',
        )
    );
    
    $search_input = $vce->content->create_input($input,'Search','Looking for someone?');

            $search_content .= <<<EOF
<form id="search-users" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_search">

$search_input

<button class="submit-button button__primary" type="submit" value="Search">Search</button>
<button class="link-button button__secondary cancel-button">Clear Search</button>
</form>
EOF;

            // create accordion box
            // $open = isset($vce->search_value) ? true : false;
            // $content .= $vce->content->accordion('Search', $search_content, $open, false);


            /* end search */

            /* start filtering */

            // the instructions to pass through the form
            $dossier = array(
                'type' => 'Pbc_ManageIngression',
                'procedure' => 'filter',
            );

            // add dossier, which is an encrypted json object of details uses in the form
            $dossier_for_filter = $vce->generate_dossier($dossier);

            $accordion_content = !empty($filter_by) ? 'true' : 'false';
            // $clickbar_title = !empty($filter_by) ? 'clickbar-title' : 'clickbar-title clickbar-closed';



    // load hooks
    if (isset($vce->site->hooks['manage_users_attributes_filter'])) {
        foreach ($vce->site->hooks['manage_users_attributes_filter'] as $hook) {
            $filter_content = call_user_func($hook, $filter_by, $content, $vce);
        }
    }


   // Filter  input
   $options_array = array();
   $options_array[] = array(
    'name' => '',
    'value' => '',
    );

   foreach ($roles_hierarchical as $roles_each) {
        foreach ($roles_each as $key => $value) {
            if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
                $this_option = array(
                    'name' => $value['role_name'],
                    'value' => $key,
                );
                if (isset($vce->filter_by_role_id) && $key == $vce->filter_by_role_id) {
                    $this_option['selected'] = true; 
                }
                $options_array[] = $this_option;
            }
        }
    }

   $input = array(
    'type' => 'select',
    'name' => 'role_id',
    'required' => 'false',
    'data' => array(
        'class' => 'filter-form',
        'tag' => 'required',
    ),
    'options' => $options_array
);

$filter_input = $vce->content->create_input($input,'Filter By Site Roles','Enter a Filter Option');
$filter_content .= <<<EOF
    $filter_input
    <button id="manage-users__filter-btn" class="button__secondary filter-form-submit link-button" dossier="$dossier_for_filter" inputtypes="$inputtypes" action="$vce->input_path" pagination="1">Filter</button>
    <button id="manage-users__clear-filter-btn" class="button__secondary link-button cancel-button">Clear Filter</button>
EOF;

    $filterAccordion = (isset($filterAccordion) ? $filterAccordion : '');
            // create accordion box
            // $open = (isset($filter_by['organization']) || isset($filter_by['role_id'])) ? true : false;
            $filterAccordion .= $vce->content->accordion('Filter', $filter_content, false);


            /* end filtering */

        

        // check if display_users is true
        if ($display_users) {

            // the instructions to pass through the form
            $dossier = array(
                'type' => 'Pbc_ManageIngression',
                'procedure' => 'pagination',
            );

            // add dossier, which is an encrypted json object of details uses in the form
            $dossier_for_pagination = $vce->generate_dossier($dossier);

            $pagination_previous = ($pagination_current > 1) ? $pagination_current - 1 : 1;
            $pagination_next = ($pagination_current < $number_of_pages) ? $pagination_current + 1 : $number_of_pages;


            $pagination_markup = NULL;
            if ($pagination) {

                $pagination_markup = <<<EOF
<div class="pagination">
    <div class="pagination-controls">
        <button class="pagination-button link-button" aria-label="first page" pagination="1" sort="$sort_by" direction="$sort_direction" dossier="$dossier_for_pagination" inputtypes="$inputtypes" action="$vce->input_path">&#124;&#65124;</button>
        <button class="pagination-button link-button" aria-label="previous page" pagination="$pagination_previous" sort="$sort_by" direction="$sort_direction" dossier="$dossier_for_pagination" inputtypes="$inputtypes" action="$vce->input_path">&#65124;</button>
        <div class="pagination-tracker">
            <label for="page-input">Page</label> 
            <input id="page-input" class="pagination-input no-label" type="text" name="pagination" value="$pagination_current" sort="$sort_by" direction="$sort_direction" dossier="$dossier_for_pagination" inputtypes="$inputtypes" action="$vce->input_path"> of $number_of_pages
        </div>
        <button class="pagination-button link-button" aria-label="next page" pagination="$pagination_next" sort="$sort_by" direction="$sort_direction" dossier="$dossier_for_pagination" inputtypes="$inputtypes" action="$vce->input_path">&#65125;</button>
        <button class="pagination-button link-button" aria-label="last page" pagination="$number_of_pages" sort="$sort_by" direction="$sort_direction" dossier="$dossier_for_pagination" inputtypes="$inputtypes" action="$vce->input_path">&#65125;&#124;</button>
    </div>
</div>
EOF;

            }


            $these_options = array(
                0 => array(
                    'name' => 'unprocessed',
                    'label' => 'Unprocessed'
                ),
                1 => array(
                    'name' => 'processed',
                    'label' => 'Processed'
                ),
                2 => array(
                    'name' => 'existing_user',
                    'label' => 'Existing&nbsp;User'
                ),
                3 => array(
                    'name' => 'new_user',
                    'label' => 'New&nbsp;User'
                ),
                4 => array(
                    'name' => 'changed_basic_info',
                    'label' => 'Changed&nbsp;Basic&nbsp;Info'
                ),
                5 => array(
                    'name' => 'registering_new_organization',
                    'label' => 'Registering&nbsp;New&nbsp;Organization'
                ),
            );


                //process checkbox input
                $input = array(
                    'type' => 'checkbox',
                    'name' => 'process_user',
                    'class' => '',
                    'options' => array(),
                    'data' => array(
                            'autocapitalize' => 'none',
                    ),
                    'flags' => Array
                    (
                       
                    )
                );

                $i = 0;
                foreach ($these_options as $k=>$v) {
                    $selected = false;
                    if (isset($vce->{$v['name']}) && $vce->{$v['name']} == 'on') {
                        $selected = true;
                    }
                    $input['options'][$i] = array(
                        'name' => $v['name'],
                        'label' => $v['label'],
                        'selected' => $selected
                    );
                    $i++;
                }
                $process_user_input = $vce->content->create_input($input,'Show the following intake forms:');


            // the instructions to pass through the form
            $dossier = array(
                'type' => 'Pbc_ManageIngression',
                'procedure' => 'user_selection',
            );

            // add dossier, which is an encrypted json object of details uses in the form
            $dossier_for_selections = $vce->generate_dossier($dossier);

            $dossier_for_process_selected_users = NULL;

            $select_content = NULL;

                $select_content .= <<<EOF
<div class="selections_div">
<form class="asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_selections">

$process_user_input

<button type="submit">Apply Selections</button>
</form>
<input type="checkbox" id="select-all-visible-users">Select All Visible Users</input>&nbsp;&nbsp;
<div>
<button id="process-selected-users" class="link-button"  dossier="$dossier_for_process_selected_users" action="$vce->input_path">Process Selected Users</button>
</div>
<br>
</div>
<br>
EOF;

  
            $user_list_content = NULL;

            $user_list_content .= <<<EOF
<table class="table-style">
<thead>
<tr>
<th></th>
EOF;

            // load hooks
            if (isset($vce->site->hooks['manage_users_attributes_list'])) {
                $user_attributes_list = array();
                foreach ($vce->site->hooks['manage_users_attributes_list'] as $hook) {
                    $user_attributes_list = call_user_func($hook, $user_attributes_list);
                }

                foreach ($user_attributes_list as $each_attribute_key => $each_attribute_value) {
                    if (!is_array($each_attribute_value)) {
                        $attributes[$each_attribute_value] = array(
                            'title' => $each_attribute_value,
                            'sortable' => 1,
                        );
                    } else {
                        $attributes[$each_attribute_key] = $each_attribute_value;
                    }
                }
            }

            $user_list_content .= <<<EOF
            <th class="">
            <span class="">Process</span>
            </th>
EOF;

            $user_list_content .= <<<EOF
            <th class="">
            <span class="">Submission<br>Date</span>
            </th>
EOF;

            $user_list_content .= <<<EOF
            <th class="">
            <span class="">Process<br>Date</span>
            </th>
EOF;
            foreach ($attributes as $each_attribute_key => $each_attribute_value) {

                // attribute is "Tester", continue
                if ($each_attribute_key == 'tester') {
                    continue;
                }

                // if conceal is set, as in the case of password, skip to next
                if (isset($each_attribute_value['type']) && $each_attribute_value['type'] == 'conceal') {
                    continue;
                }

                $nice_attribute_title = $each_attribute_value['title'];

                if ($each_attribute_key == $sort_by) {
                    if ($sort_direction == 'ASC') {
                        $sort_class = 'sort-icon sort-active sort-asc';
                        $direction = 'DESC';
                    } else {
                        $sort_class = 'sort-icon sort-active sort-desc';
                        $direction = 'ASC';
                    }
                    $th_class = 'current-sort';
                } else {
                    $sort_class = 'sort-icon sort-inactive';
                    $direction = 'ASC';
                    $th_class = '';
                }

                // dossier for sort
                $dossier = array(
                    'type' => 'Pbc_ManageIngression',
                    'procedure' => 'pagination',
                );

                // generate dossier
                $dossier_for_sort = $vce->generate_dossier($dossier);
                $user_list_content .= <<<EOF
<th class="$th_class">

EOF;

                // check if this is a sortable attribute
                if (isset($each_attribute_value['sortable']) && $each_attribute_value['sortable']) {
                    $nice_attribute_title = ($nice_attribute_title == 'Organization') ? 'Org and Group' : $nice_attribute_title;
                    $nice_attribute_title = ($nice_attribute_title == 'Group') ? 'New Org' : $nice_attribute_title;

                    $user_list_content .= <<<EOF
<span class="$sort_class" dossier="$dossier_for_sort" sort="$each_attribute_key" direction="$direction" action="$vce->input_path" title="Sort By $nice_attribute_title">$nice_attribute_title</span>
EOF;

                } else {

                    $user_list_content .= <<<EOF
<div class="sort-icon"></div>
EOF;

                }

                $user_list_content .= <<<EOF
</th>
EOF;


            }

            $user_list_content .= <<<EOF

</tr>
</thead>
<tbody>
EOF;

            // check permissions and assign values
            $edit_users = $vce->check_permissions('edit_users') ? true : false;
            $masquerade_users = $vce->check_permissions('masquerade_users') ? true : false;
            $delete_users = $vce->check_permissions('delete_users') ? true : false;


            // prepare for filtering of roles limited by hierarchy
            if (!empty($filter_by)) {
                $role_hierarchy = array();
                // create a lookup array from role_name to role_hierarchy
                foreach ($roles as $roles_key => $roles_value) {
                    $role_hierarchy[$roles_key] = $roles_value['role_hierarchy'];
                }
            }


        // get roles
        $roles = json_decode($vce->site->roles, true);
        // get roles in hierarchical order
        $roles_hierarchical = json_decode($vce->site->site_roles, true);


            // USER LIST loop through users in to-show list.

            foreach ($users_list as $each_user) {

                // check if filtering is happening
                if (!empty($filter_by)) {
                    // loop through filters and check if any user fields are a match
                    foreach ($filter_by as $filter_key => $filter_value) {
                        // prevent roles hierarchy above this from displaying
                        if ($role_hierarchy[$users[$each_user]['role_id']] < $role_hierarchy[$vce->user->role_id]) {
                            continue 2;
                        }

                        if ($filter_key == "role_id") {
                            // make title of role
                            //    $filter_value = $roles[$filter_value]['role_name'];
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
                            if (!in_array($users[$each_user][$filter_key], $filter_value)) {
                                // continue foreach before this foreach
                                continue 2;
                            }
                        } else {
                            // doesn't match so continue
                            if (isset($users[$each_user][$filter_key])) {
                                if ($users[$each_user][$filter_key] != $filter_value) {
                                    // continue foreach before this foreach
                                    continue 2;
                                }
                            } else {
                                continue 2;
                            }
                        }
                    }
                }
                if (in_array($each_user, $unprocessed_user_ids)) {
                    $row_class = 'unprocessed_user';
                }
                if (in_array($each_user, $processed_user_ids)) {
                    $row_class = 'processed_user';
                }
                $user_list_content .= '<tr class="' . $row_class . '">';

                //$dossier_for_edit = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'edit','user_id' => $each_user)),$vce->user->session_vector);
                $dossier_for_edit = $vce->generate_dossier(array('type' => 'Pbc_ManageIngression', 'procedure' => 'edit', 'user_id' => $each_user));

                //$dossier_for_masquerade = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'masquerade','user_id' => $each_user)),$vce->user->session_vector);
                $dossier_for_masquerade = $vce->generate_dossier(array('type' => 'Pbc_ManageIngression', 'procedure' => 'masquerade', 'user_id' => $each_user));

                // //$dossier_for_delete = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'delete','user_id' => $each_user)),$vce->user->session_vector);
                // $dossier_for_delete = $vce->generate_dossier(array('type' => 'Pbc_ManageIngression', 'procedure' => 'delete', 'user_id' => $each_user));

                $user_list_content .= <<<EOF
<td class="align-center">
EOF;

                if ($edit_users) {

                    $user_list_content .= <<<EOF
<form class="inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_edit">
<input type="hidden" name="sort_by" value="$sort_by">
<input type="hidden" name="sort_direction" value="$sort_direction">
<input type="hidden" name="pagination_current" value="$pagination_current">
<button id="edit-btn" type="submit" title="Edit"></button>
</form>
EOF;

                }

                if ($masquerade_users) {

                    $user_list_content .= <<<EOF
<form class="inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_masquerade">
<button id="masquerade-btn" type="submit" title="Masquerade" value="Masquerade"></button>
</form>
EOF;

                }

                $dossier_for_notes= $vce->generate_dossier(array('type' => 'Pbc_ManageIngression', 'procedure' => 'view_notes', 'user_id' => $each_user));

                $user_list_content .= <<<EOF
<form class="inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_notes">
<button id="notes-btn" type="submit" title="View Notes" value="View_notes"></button>
</form>
EOF;

                $user_list_content .= <<<EOF
</td>
EOF;

            $preset_fields = $this->ingression_form_info_single_user($vce, $each_user);
            $dossier = array(
                'type' => 'Pbc_ManageIngression',
                'procedure' => 'process_user',
                'ingression_form_id' => $preset_fields['id'],
                'email' => $users[$each_user]['email'],
                'user_id' => $each_user,
                );

                $dossier_for_individual_user = $vce->generate_dossier($dossier);


                $form_id = $each_user . "-form";
                $form_class = "process-user";
                $dossier_input_id = $form_id . "-dossier";
                $process_user_input_id = $form_id . "-process-user";
                $role_id_input_id = $form_id . "-role_id";
                $first_name_input_id = $form_id . "-first-name";
                $last_name_input_id = $form_id . "-last-name";
                $group_input_id = $form_id . "-group";
                $org_input_id = $form_id . "-org";
                $org_class = $form_id . "_org";
                $user_list_content .= <<<EOF
                <form id="$form_id" class="$form_class asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
                <input id="$dossier_input_id" type="hidden" name="dossier" value="$dossier_for_individual_user"></form>
                <td><span><input form="$form_id" type="checkbox" id="$process_user_input_id" class="batch-process" name="process_user"></span></td>
EOF;
                $display_date = NULL;
                $form_created = (isset($ingression_forms_processed[$each_user]['created']))? $ingression_forms_processed[$each_user]['created'] : $ingression_forms_unprocessed[$each_user]['created'];
                $date = new DateTime($form_created);
                $display_date = $date->format('Y-m-d');
                $user_list_content .= <<<EOF
                <td><span>$display_date</span></td>
EOF;

                $display_date = 'pending';

                if (isset($ingression_forms_processed[$each_user]['processed'])) {
                    $date = new DateTime($ingression_forms_processed[$each_user]['processed']);
                    $display_date = $date->format('Y-m-d');
                }
                $user_list_content .= <<<EOF
                <td><span>$display_date</span></td>
EOF;


                foreach ($attributes as $each_attribute_key => $each_attribute_value) {

                    // attribute is "Tester", continue
                    if ($each_attribute_key == 'tester') {
                        continue;
                    }
                    

                    // exception for role_id, change to role_name
                    if ($each_attribute_key == 'role_id') {
                        $each_attribute_key = 'role_name';
                    }

                    // if conceal is set, as in the case of password, skip to next
                    if (isset($each_attribute_value['type']) && $each_attribute_value['type'] == 'conceal') {
                        continue;
                    }

                    // prevent error if not set
                    $attribute_value = isset($users[$each_user][$each_attribute_key]) ? $users[$each_user][$each_attribute_key] : null;

                    if (isset($each_attribute_value['datalist'])) {

                        if (isset($datalist_cache[$attribute_value])) {

                            // user saved value
                            $attribute_name = $datalist_cache[$attribute_value];

                        } else {

                            $datalist = $vce->get_datalist_items(array('item_id' => $attribute_value));

                            $attribute_name = isset($datalist['items'][$attribute_value]['name']) ? $datalist['items'][$attribute_value]['name'] : null;

                            // save it so we dont need to look up again
                            $datalist_cache[$attribute_value] = $attribute_name;

                        }

                        $attribute_value = $attribute_name;

                    }


                    if ($each_attribute_key == 'first_name') {
                        $new_name = $preset_fields[$each_attribute_key];
                        $old_name = $attribute_value;
                        $user_list_content .= '<td><input id="' . $form_id . '-first-name"  form="' . $form_id . '" type="text" name="'.$each_attribute_key.'" value="'.$new_name.'">Current value:<br>' . $attribute_value . '</input></td>';
                    } elseif ($each_attribute_key == 'last_name') {
                        $new_name = $preset_fields[$each_attribute_key];
                        $old_name = $attribute_value;
                        $user_list_content .= '<td><input id="' . $form_id . '-last-name" form="' . $form_id . '" type="text" name="'.$each_attribute_key.'" value="'.$new_name.'">Current value:<br>' . $attribute_value . '</input></td>';

                    } elseif ($each_attribute_key == 'role_name' ) {
                        // get the desired role id from the closest role field
                        switch ($preset_fields['closest_role']) {
                            case 'Coach':
                                $apparent_role_id = 2;
                                break;
                            case 'Teacher/Teacher Assistant':
                                $apparent_role_id = 2;
                                break;
                            case 'Manager/Specialist (Education, Disabilities, Health, etc.)':
                                $apparent_role_id = 6;
                                break;
                            case 'Director/Program Manager':
                                $apparent_role_id = 5;
                                break;
                            case 'Family Child Care Provider':
                                $apparent_role_id = 3;
                                break;
                            case 'Home Visitor':
                                $apparent_role_id = 3;
                                break;
                            case 'Regional TTA Specialist Consultant':
                                $apparent_role_id = 5;
                                break;
                            case 'Other':
                                $apparent_role_id = 3;
                                break;
                            default:
                                $apparent_role_id = 3;
                                break;
                        }
                        // if user has answered yes to org admin question
                        if (isset($preset_fields['org_registration_question']) && $preset_fields['org_registration_question'] == 1) {
                            $apparent_role_id  = 5;
                        }

                        // create role input for individual users
                        $options_array = array();
                        foreach ($roles_hierarchical as $roles_each) {
                            foreach ($roles_each as $key => $value) {
                                if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
                                    $selected = ($key == $apparent_role_id) ? true : false;
                                    $options_array[] = array(
                                    'name' => $value['role_name'],
                                    'value' => $key,
                                    'selected' => $selected
                                    );
                                }
                            }
                        }

                        $input = array(
                        'type' => 'select',
                        'name' => 'role_id',
                        'id' => $form_id . '-role_id',
                        'data' => array(
                            'form' => $form_id,
                            'tag' => 'required',
                            
                        ),
                        'options' => $options_array,

                        );

                        $role_input = $vce->content->create_input($input,'Role');
                        $user_list_content .= '<td class="role-select">' . $role_input . 'Current value:<br>' . $users[$each_user]['role_name'] . '</td>';

                    } elseif ($each_attribute_key == 'organization' ) {
                        $user_info = new stdClass();
                        $user_info->user_id = $each_user;
                        $user_info->role_id = $users[$each_user]['role_id'];
                        // $user_info->organization = $users[$each_user]['organization'];
                        // $user_info->group = $users[$each_user]['group'];
                        $user_info->organization = $preset_fields['org_id'];
                        $user_info->group = $preset_fields['group_id'];
                        $user_info->form_id = $form_id;
                        $user_info->action=$vce->input_path;
                        $user_info->group_class = $form_id . '_group';
                        $user_info->org_class = $form_id . '_org';

                        
                        
                        // get current organization and group names
                        $input = array(
                            'org_id' => $users[$each_user]['organization'],
                            'group_id' => $users[$each_user]['group']
                        );

                        $current_org_group_names = $this->get_org_group_names($vce, $input);

                        // if user has requested creation of new org, and that request has been carried out, show new org and group in row to be processed
                        $org_create_class = 'registering-new-org';
                        if ($preset_fields['org_new_organization']== $current_org_group_names['org_name']) {
                            $user_info->organization = $input['org_id'];
                            $user_info->group = $input['group_id'];
                            $org_create_class = 'org-has-been-registered';
                        }
                        
                        $user_list_content .= '<td>' . $this->organizations($vce, $user_info) . '<br>Current values:<br>' . $current_org_group_names['org_name'] . ',&nbsp;' . $current_org_group_names['group_name'] . '</td>';

                    } elseif ($each_attribute_key == 'group' ) {
                        if (isset($preset_fields['org_new_organization']) && $preset_fields['org_new_organization'] != '') {

                            //get org datalist id
                            $query = "SELECT * FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_key = 'name' AND meta_value='organization'";
                            $org_info = $vce->db->get_data_object($query);
                            if (isset($org_info[0]->datalist_id)) {		
                                $org_datalist_id = $org_info[0]->datalist_id;
                            } else {
                                $org_datalist_id = NULL;
                            }

                            $dossier = array(
                                'type' => 'Pbc_Manageorganizations',
                                'procedure' => 'auto_add',
                                'datalist_id' => $org_datalist_id,
                                'ingression_form_id' => $preset_fields['id'],
                                'new_org_name' => $preset_fields['org_new_organization'],
                                'user_id' => $each_user,
                                );

                            $dossier_for_create_org = $vce->generate_dossier($dossier);

                            $user_list_content .= <<<EOF
                            <form  class=" asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
                            <input type="hidden" name="dossier" value="$dossier_for_create_org">
EOF;
                            if (isset($preset_fields['org_registration_question']) && isset($preset_fields['org_new_organization']) && $preset_fields['org_new_organization'] != '' && $preset_fields['org_registration_question'] == 1) {
                                $user_list_content .= '<td class="' . $org_create_class . '">Entered new organization, and requested to register new org and to be org admin. <br><button class="submit-button button__primary registering-new-org-button" type="submit" value="Submit">Create Organization</button><br>New Org Name:<br><input type="text" name="name" value="' . $preset_fields['org_new_organization'] . '" tag="required" autocomplete="off"></form></td>'; 
                            } elseif (isset($preset_fields['org_new_organization']) && $preset_fields['org_new_organization'] != '' && $preset_fields['org_registration_question'] != 1) {
                                // if user entered new org but didn't want to register the new org:
                                $user_list_content .= '<td class="' . $org_create_class . '">Entered new organization, but did not request to register new org or to be org admin.<br><button  class="submit-button button__primary  registering-new-org-button" type="submit" value="Submit">Create Organization</button><br>New Org Name:<br><input type="text" name="name" value="' . $preset_fields['org_new_organization'] . '" tag="required" autocomplete="off"></form></td>'; 
                            }
                        } else {
                                // no new org and no registration of new org:
                                $user_list_content .= '<td><span></span></td>'; 
                            
                        }
                    } else {

                         $user_list_content .= '<td><span>' . $attribute_value . '</span></td>';
                    }

                }

                $user_list_content .= '</tr>';

            }


            $dossier_for_save_notes= $vce->generate_dossier(array('type' => 'Pbc_ManageIngression', 'procedure' => 'save_notes'));

            $user_list_content .= <<<EOF
</tbody>
</table>


<div id="notes-modal" class="modal hide">
<div class="modal-content">
  <button class="close close-notes-modal">×</button>
  <h1 id="notes-title" class="notes-title">Notes</h1>
  <hr>
  <p>
  <form class="inline-form asynchronous-form" method="post" action="$vce->input_path">
    <input type="hidden" name="dossier" value="$dossier_for_save_notes">    
    <input id="this-user-id" type="hidden" name="this_user_id" value="">  
    <textarea id="notes-textarea" name="user_notes" rows="10" cols="50">
     Placeholder
    </textarea></p>
     <button type="submit" class="btn button__primary complete-btn" value="Save Notes">Save Notes</button>
  </form>
  <button type="button" class="btn button__primary cancel cancel-notes">Cancel</button>
</div>
</div>


$pagination_markup
EOF;


        }


    // Set up tabs
    $tab_input = array (
        'tabs__container1' => array(
            'tabs' => array(
                'tab1' => array(
                    'id' => 'view-ingression-applications',
                    'label' => 'View Intake Applications',
                    'content' => $search_content . $filterAccordion . $select_content . $user_list_content
                ),
                'tab2' => array(
                    'id' => 'add-ingression-application',
                    'label' => 'Add Intake Application',
                    'content' => $new_user_content
                ),
            ),
        ),
    );

    if (isset($edit_user_content)) {
        $tab_input['tabs__container1']['tabs']['tab3'] = array(
            'id' => 'edit-users',
            'label' => 'Edit User',
            'content' => $edit_user_content,
            'visibility' => true
        );
        $tab_input['tabs__container1']['tabs']['tab1']['visibility'] = false;
    }
    $user_list_content = Pbc_utilities::create_tab($tab_input);
    
    $content = <<<EOF
<div id="manage-users-page"></div>
$user_list_content
EOF;

$vce->content->add('main',$content);

}


    public function ingression_form_info($vce, $user_id){
        
        $content = NULL;

        //array for all preset fields (with these fields first)
		$preset_fields = array(
            'processed' => NULL,
            'email' => NULL,
            'first_name' => NULL,
            'last_name' => NULL,
            'organization' => NULL,
            'group' => NULL,
            'created' => NULL,
            'user_id' => NULL,
        );
        $hidden_preset_fields = array();

        $original_preset_fields_length = count($preset_fields);

		if (isset($user_id)) {
            // $user_id = 13;
			//set all available fields from last submission by this user
			$query = "SELECT * FROM " . TABLE_PREFIX . "ingression_forms WHERE user_id='$user_id' ORDER BY created DESC";
			$vce->db->query($query);
			$results = $vce->db->get_data_object($query);

            // get user vector
            $query = "SELECT vector FROM " . TABLE_PREFIX . "users WHERE user_id='$user_id' LIMIT 1";
            $user_vector = $vce->db->get_data_object($query);
            
            // set vector of user filling out the form
            $vector = $user_vector[0]->vector;


			if (!empty($results)) {

                //get org and group names
                $datalist = $vce->get_datalist_items(array('datalist' => 'organizations_datalist'));
                $org_name = isset($datalist['items'][$results[0]->organization]['name']) ? $datalist['items'][$results[0]->organization]['name'] : null;

                $datalist = $vce->get_datalist_items(array('item_id' => $results[0]->group));
                $group_name = isset($datalist['items'][$results[0]->group]['name']) ? $datalist['items'][$results[0]->group]['name'] : null;
                
                $i = 0;
				foreach ($results[0] as $k=>$v) {
                    if ($k == 'email' || $k == 'first_name'|| $k == 'last_name'|| $k == 'current_first_name'|| $k == 'current_last_name') {
						// decode user data			
						$v = $vce->user->decryption($v, $vector);
					}

                    if ($k == 'organization') {
                        $preset_fields['org_id'] = $v;
                        $v = $org_name;
                    }
                    if ($k == 'group') {
                        $preset_fields['group_id'] = $v;
                        $v = $group_name;
                    }
					// set each field from the query to a member of $preset_fields
					$preset_fields[$k] = $v;
                }
			} else {
				// if there are no records for this user, then say so
                $content .= <<<EOF
                <br>
                This user has no recorded intake forms.
                <br>
EOF;
                return $content;
			}
		}
        $email = $preset_fields['email'];
 
        $content .= <<<EOF
            Recorded intake raw form data for $email.
            <br>
            This exists to allow view of raw DB data. Any data being used for processing should be moved to the main table.
            <br><br>
EOF;



        $hidden_content = NULL;
        $i = 0;
    foreach ($preset_fields as $k=>$v) {
        if ($i < $original_preset_fields_length) {
            $content .= <<<EOF
            $k : &nbsp; &nbsp; &nbsp; $v <br>
EOF;
            $i++;
        } else {
            $hidden_content .= <<<EOF
                $k : &nbsp; &nbsp; &nbsp; $v <br>
EOF;
        }
    }

            $hidden_preset_fields_accordion = $vce->content->accordion('Other Intake Data', $hidden_content, $accordion_expanded = false, $accordion_disabled = false, $accordion_class = 'other-intake-data-accordion');
            $content .= <<<EOF
            $hidden_preset_fields_accordion
EOF;
        return $content;
    }


    public function get_org_group_names($vce, $input){
        $output = array();
        //get org and group names
        $datalist = $vce->get_datalist_items(array('datalist' => 'organizations_datalist'));
        $output['org_name'] = isset($datalist['items'][$input['org_id']]['name']) ? $datalist['items'][$input['org_id']]['name'] : null;

        $datalist = $vce->get_datalist_items(array('item_id' => $input['group_id']));
        $output['group_name']= isset($datalist['items'][$input['group_id']]['name']) ? $datalist['items'][$input['group_id']]['name'] : null;

        return $output;

    }

    public function ingression_form_info_single_user($vce, $user_id){
        
        $content = NULL;

        //array for all preset fields (with these fields first)
        $preset_fields = array();
		$requested_preset_fields = array(
            'processed' => NULL,
            'email' => NULL,
            'first_name' => NULL,
            'last_name' => NULL,
            'organization' => NULL,
            'group' => NULL,
            'user_id' => NULL,
            'org_registration_question' => NULL,
            'org_new_organization' => NULL,
            'id' => NULL,
            'closest_role' => NULL,
        );

        $original_preset_fields_length = count($preset_fields);

		if (isset($user_id)) {
            // $user_id = 13;
			//set all available fields from last submission by this user
			$query = "SELECT * FROM " . TABLE_PREFIX . "ingression_forms WHERE user_id='$user_id' ORDER BY created DESC LIMIT 1";
			$vce->db->query($query);
			$results = $vce->db->get_data_object($query);

            // get user vector
            $query = "SELECT vector FROM " . TABLE_PREFIX . "users WHERE user_id='$user_id' LIMIT 1";
            $user_vector = $vce->db->get_data_object($query);
            
            // set vector of user filling out the form
            $vector = $user_vector[0]->vector;


			if (!empty($results)) {

                //get org and group names
                $datalist = $vce->get_datalist_items(array('datalist' => 'organizations_datalist'));
                $org_name = isset($datalist['items'][$results[0]->organization]['name']) ? $datalist['items'][$results[0]->organization]['name'] : null;

                $datalist = $vce->get_datalist_items(array('item_id' => $results[0]->group));
                $group_name = isset($datalist['items'][$results[0]->group]['name']) ? $datalist['items'][$results[0]->group]['name'] : null;
                
                $i = 0;
				foreach ($results[0] as $k=>$v) {
                    if ($k == 'email' || $k == 'first_name'|| $k == 'last_name'|| $k == 'current_first_name'|| $k == 'current_last_name') {
						// decode user data			
						$v = $vce->user->decryption($v, $vector);
					}

                    if ($k == 'organization') {
                        $preset_fields['org_id'] = $v;
                        $v = $org_name;
                    }
                    if ($k == 'group') {
                        $preset_fields['group_id'] = $v;
                        $v = $group_name;
                    }
					// set each field from the query to a member of $preset_fields
                    if (array_key_exists($k, $requested_preset_fields)) {
					    $preset_fields[$k] = $v;
                    }
                }
			}
		}

        return $preset_fields;
    }

    /**
     * Create a new user
     */
    public function create($input) {
    
    	$vce = $this->vce;
    	
    	$response = $vce->user->create($input);
    	
    	$response['form'] = 'create';

        if ($response['response'] == 'success') {
            $this->send_new_user_email($input);
        }

    	echo json_encode($response);
    	
    	return;
    }



	public function organizations($vce, $user_info) {
		$organizations = array();

                // load hooks
                if (isset($vce->site->hooks['get_organizations_and_groups'])) {
                    foreach ($vce->site->hooks['get_organizations_and_groups'] as $hook) {
                        $organizations = call_user_func($hook, $user_info);
                    }
                }
		return $organizations;
	}

    /**
     * Send a welcome email to the newly created user.
     */
    public function send_new_user_email($input) {
        
        $vce = $this->vce;

    // send email
	$fname = $input['first_name'];
	$lname = $input['last_name'];
	$fullname = $input['first_name'].' '.$input['last_name'];

$email_message = <<<EOF
Dear $fname $lname,<br>
<br>
A Head Start Coaching Companion account has been created for you. <br>
To access the site, you need to register for an ECLKC login, using the email address to which this message has been sent. If you have not already done so, please follow the instructions located here: <a href="https://eclkc.ohs.acf.hhs.gov/sites/default/files/pdf/no-search/how-to-access-coaching-companion.pdf">How to access the Coaching Companion</a> <br>
<br>
Once you are registered with the ECLKC, you can access the OHS Coaching Companion here:<br>
<a href="https://eclkc.ohs.acf.hhs.gov/cc/">Coaching Companion Home</a><br>
<br>
Thank you,<br>
Your HSCC Administrator<br>
EOF;
	$mail_attributes = array (
	  	'from' => array('coachingcompanion@eclkc.info', 'DoNotReply'),
	  	'to' => array(
	 		 array($input['email'], $fullname)
	   	 ),
		'subject' => 'Welcome to the Head Start Coaching Companion',
	 	'message' => $email_message,
	 	'html' => true,
	 	'SMTPAuth' => false
	 );	

     // send invitation email
     $vce->mail($mail_attributes);

    return;


    }

    /**
     * edit user
     */
    public function edit($input) {

        // add attributes to page object for next page load using session
        global $vce;

        $vce->site->add_attributes('edit_user', $input['user_id']);

        $pagination_current = filter_var($input['pagination_current'], FILTER_SANITIZE_NUMBER_INT);

        if ($pagination_current < 1) {
            $pagination_current = 1;
        }

        $vce->site->add_attributes('sort_by', $input['sort_by']);
        $vce->site->add_attributes('sort_direction', $input['sort_direction']);
        $vce->site->add_attributes('pagination_current', $pagination_current);

        echo json_encode(array('response' => 'success', 'message' => 'session data saved', 'form' => 'edit'));
        return;

    }

        /**
     * process user
     */
    public function process_user($input) {
        global $vce;
        // $vce->log('process_user');
        // $vce->log($input);
        // extract($input); 

        // {"type":"Pbc_ManageIngression","email":"testuser@test.com","user_id":"6771","role_id":"2","first_name":"asdf","last_name":"fdsa","organization":"237","group":"1731"}
        $this->update($input);

        $unix_timestamp = time();
        $format = 'Y-m-d H:i:s';
        $this_date = date($format, $unix_timestamp);

        $ingression_form_id = $input['ingression_form_id'];

        $query = "UPDATE vce_ingression_forms SET processed ='$this_date' WHERE id='$ingression_form_id'";
        $vce->db->query($query);


        echo json_encode(array('response' => 'success', 'message' => 'Users Processed', 'form' => 'process-user', 'action' => ''));
        return;
    }
    /**
     * update user
     */
    public function update($input) {
        global $vce;
        // $vce->log($input);
		$user_id = $input['user_id'];

        // make sure that someone can't change to a role higher than their own
        $role_id_to_change = empty($input['role_id']) ? '' : $input['role_id'];

        if ($role_id_to_change != '') {
            $role_id_hierarchy = json_decode($vce->site->site_roles, true);

            foreach ($role_id_hierarchy as $k=>$v) {
                foreach ($v as $this_role) {
                    if ($vce->user->role_id == $this_role['role_id']) {
                        $my_hierarchy = $k;
                    }
                    if ($role_id_to_change == $this_role['role_id']) {
                        $role_to_change_hierarchy = $k;
                    }
                }
            }
            if ($my_hierarchy > $role_to_change_hierarchy) {
                $role_id_to_change = $vce->user->role_id;
            }
        }

		

        unset($input['type'], $input['procedure'], $input['role_id'], $input['user_id']);
		$vce->user->update_user($user_id, $input, $role_id_to_change);

        echo json_encode(array('response' => 'success', 'message' => 'User Updated', 'form' => 'create', 'action' => ''));
        return;

    }

    /**
     * Masquerade as user
     */
    public function masquerade($input) {

        global $vce;

        // pass user id to masquerade as
        $vce->user->make_user_object($input['user_id']);

        echo json_encode(array('response' => 'success', 'message' => 'User masquerade', 'form' => 'masquerade', 'action' => $vce->site->site_url));
        return;

    }

    /**
     * Delete a user
     */
    public function delete($input) {

        global $vce;

		$vce->user->delete_user($input['user_id']);

        echo json_encode(array('response' => 'success', 'message' => 'User has been deleted', 'form' => 'delete', 'user_id' => $input['user_id'], 'action' => ''));
        return;

    }


    /**
     * View Notes
     */
    public function view_notes($input) {

        global $vce;

        //creates an indexed list of users with available attributes
        $user = $vce->user->find_users($input['user_id']);
        $user_notes = (isset($user[0]->user_notes)) ? html_entity_decode($user[0]->user_notes, ENT_QUOTES) : 'No notes recorded.';
        echo json_encode(array('response' => 'success', 'message' => 'View Notes', 'form' => 'view_notes', 'this_user_id' => $input['user_id'], 'first_name'=>$user[0]->first_name, 'last_name'=>$user[0]->last_name, 'email'=>$user[0]->email, 'user_notes'=>$user_notes, 'action' => ''));
        return;

    }

        /**
     * Save Notes
     */
    public function save_notes($input) {

        global $vce;

        $user_data = array(
            'user_id' => $input['this_user_id'],
            'user_notes' => $input['user_notes']

        );

    	$response = $vce->user->update($user_data);
    	
    	
        echo json_encode(array('response' => 'success', 'message' => 'Notes saved', 'form' => 'save_notes', 'action' => ''));
        return;

    }

 

    /**
     * Filter
     */
    public function filter($input) {

        global $vce;

        foreach ($input as $key => $value) {
            if (strpos($key, 'filter_by_') !== FALSE) {
                $vce->site->add_attributes($key, $value);
            }
        }

        $vce->site->add_attributes('pagination_current', $input['pagination_current']);

        echo json_encode(array('response' => 'success', 'message' => 'Filter'));
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

        $vce->site->add_attributes('sort_by', $input['sort_by']);
        $vce->site->add_attributes('sort_direction', $input['sort_direction']);
        $vce->site->add_attributes('pagination_current', $pagination_current);

        echo json_encode(array('response' => 'success', 'message' => 'pagination'));
        return;

    }

      /**
     * pagination users
     */
    public function user_selection($input) {

        // add attributes to page object for next page load using session
        global $vce;

        // keep track of which selections are on or off
        // {"type":"Pbc_ManageIngression","all":"on","unprocessed":"on","processed":"on","existing_user":"on","new_user":"on","registering_new_organization":"on"}
        $user_selections = array(
            0 => "unprocessed",
            1 => "processed",
            2 => "existing_user",
            3 => "new_user",
            4 => "changed_basic_info",
            5 => "registering_new_organization",
        );
        foreach ($user_selections as $k=>$v) {
            $vce->site->remove_attributes($v);
        }
        foreach ($input as $k=>$v) {
            if (in_array($k, $user_selections)) {
                $vce->site->add_attributes($k, $v, TRUE);
                $vce->site->add_attributes('user_selection_added', 'yes', TRUE);
                // $vce->log($k);
            }
        }

        echo json_encode(array('response' => 'success', 'message' => 'List has been reconfigured.', 'form' => 'user_selection','action' => ''));

        // echo json_encode(array('response' => 'success', 'message' => 'pagination'));
        return;

    }

        /**
     * search for a user
     */
    public  function search($input) {
    
    	// not an object at this location
        global $vce;
        
        if (!isset($input['search']) || strlen($input['search']) < 3) {
            // return a response, but without any results
            echo json_encode(array('response' => 'success', 'results' => null));
            return;
        }
        
       $all_users = $vce->user->search($input['search']);
        
        // hook to work with search results
        if (isset($vce->site->hooks['manage_users_attributes_search'])) {
            foreach ($vce->site->hooks['manage_users_attributes_search'] as $hook) {
                $all_users = call_user_func($hook, $all_users);
            }
        }

        if (count($all_users)) {

            $user_keys = array_keys($all_users);

            $vce->site->add_attributes('search_value', $input['search']);
            $vce->site->add_attributes('user_search_results', json_encode($user_keys));

            echo json_encode(array('response' => 'success', 'form' => 'edit'));
            return;
        }

        $vce->site->add_attributes('search_value', $input['search']);
        $vce->site->add_attributes('user_search_results', null);
	
        echo json_encode(array('response' => 'success', 'form' => 'edit'));
        return;

    }

    /**
     * search for a user
     */
    public function searchBAK($input) {
    
    	// not an object at this location
        global $vce;

        if (!isset($input['search']) || strlen($input['search']) < 3) {
            // return a response, but without any results
            echo json_encode(array('response' => 'success', 'results' => null));
            return;
        }

        // break into array based on spaces
        $search_values = explode('|', preg_replace('/\s+/', '|', $input['search']));
        
        // get all users of specific roles as an array
        $query = "SELECT * FROM " . TABLE_PREFIX . "users";
        $find_users_by_role = $vce->db->get_data_object($query, 0);

        // get roles
        $roles = json_decode($vce->site->roles, true);

        $roles_list = array();
        foreach ($roles as $key => $value) {
            if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
                // add to role_id to array
                $roles_list[] = $key;
            }
        }

        // cycle through users
        foreach ($find_users_by_role as $key => $value) {
            // filter out higher role_id
            if (in_array($value['role_id'], $roles_list)) {
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
            echo json_encode(array('response' => 'success', 'results' => null));
            return;
        }

        $query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id IN (" . implode(",", $users_id_in) . ")";
        $users_meta_data = $vce->db->get_data_object($query, 0);

        foreach ($users_meta_data as $key=>$value) {

            // skip a few meta_key that we don't want to allow searching in
            if ($value['meta_key'] == 'lookup' || $value['meta_key'] == 'persistent_login') {
                continue;
            }

            // decrypt the values
            $all_users[$value['user_id']][$value['meta_key']] = $vce->user->decryption($value['meta_value'], $all_users[$value['user_id']]['vector']);

            // test multiples
            for ($i = 0; $i < count($search_values); $i++) {

                $pos = strrpos(strtolower($all_users[$value['user_id']][$value['meta_key']]), strtolower($search_values[$i]));
                // if ($all_users[$value['user_id']][$value['meta_key']] == 'DTL') {
                // }
                if ($pos === false) {
                	continue;
                } else {
					if (!isset($counter[$value['user_id']][$i])) {
						// add to specific match
						$match[$value['user_id']]++;
						// set a counter to prevent repeats
						$counter[$value['user_id']][$i] = true;
						// break so it only counts once for this value
						break;
					}
                }
            }
        }

        // cycle through match to see if the number is equal to count
        foreach ($match as $match_user_id => $match_user_value) {
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
            foreach ($vce->site->hooks['manage_users_attributes_search'] as $hook) {
                $all_users = call_user_func($hook, $all_users);
            }
        }

        if (count($all_users)) {

            
            $user_keys = array_keys($all_users);

            $vce->site->add_attributes('search_value', $input['search']);
            $vce->site->add_attributes('user_search_results', json_encode($user_keys));

            echo json_encode(array('response' => 'success', 'form' => 'edit'));
            return;
        }

        $vce->site->add_attributes('search_value', $input['search']);
        $vce->site->add_attributes('user_search_results', null);
	
        echo json_encode(array('response' => 'success', 'form' => 'edit'));
        return;

    }


    /**
     * fields to display when this is created
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