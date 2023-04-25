<?php

class Pbc_ManageUsers2 extends Component {

    /**
     * basic info about the component
     */
    public function component_info() {
        return array(
            'name' => 'PBC Manage Users 2',
            'description' => 'Custom Manage Users for HSCC. Add, edit, merge, masquerade as, and delete site users.',
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
                    'name' => 'merge_users',
                    'description' => 'Role can merge users',
                ),
                array(
                    'name' => 'masquerade_users',
                    'description' => 'Role can masquerade as users',
                ),
                array(
                    'name' => 'suspend_users',
                    'description' => 'Role can suspend users',
                )
            ),
            'recipe_fields' => array('auto_create','title',array('url' => 'required'))
        );
    }

    /**
     *
     */
    public function as_content($each_component, $vce) {

        // add javascript to page
        $vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tabletocard');

        $vce->site->add_style(dirname(__FILE__) . '/css/style.css');

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
				$title = call_user_func($hook, 'Manage Users', 'manage-users');
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
        $sort_by = (isset($vce->sort_by) && !empty($vce->sort_by)) ? $vce->sort_by : 'user_id';
        $sort_direction = isset($vce->sort_direction) ? $vce->sort_direction : 'DESC';
        $display_users = true;
        $pagination = true;
        $pagination_current = isset($vce->pagination_current) ? $vce->pagination_current : 1;
        $pagination_length = isset($vce->pagination_length) ? $vce->pagination_length : 25;

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
        $query = "SELECT count(*) as count FROM " . TABLE_PREFIX . "users WHERE role_id IN (" . implode(',', $role_id_in) . ")";

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
            // $vce->dump($vce->user_search_results);
            // $this_user_search_result = json_decode(base64_decode($vce->user_search_results),true);


            $this_user_search_result  = $vce->user_search_results;
            
            // $vce->user_search_results = implode( ',', $this_user_search_result);

            $query = "SELECT * FROM " . TABLE_PREFIX . "users WHERE user_id IN (" . $vce->user_search_results  . ")";
            // $vce->dump($query);
            // exit;

        }  else {
            // towards the standard way
            // with role_id filter
            if (!empty($filter_by)) {
                $query = "SELECT * FROM " . TABLE_PREFIX . "users";
                $pagination = false;
                $sort_by = null;
            } else if ($sort_by == 'user_id' || $sort_by == 'role_id') {
                // if user_id or role_id is the sort
                $query = "SELECT * FROM " . TABLE_PREFIX . "users WHERE role_id IN (" . implode(',', $role_id_in) . ") ORDER BY $sort_by " . $sort_direction . " LIMIT " . $pagination_length . " OFFSET " . $pagination_offset;
            } else {
                // the standard way
                $query = "SELECT " . TABLE_PREFIX . "users.* FROM " . TABLE_PREFIX . "users_meta INNER JOIN " . TABLE_PREFIX . "users ON " . TABLE_PREFIX . "users_meta.user_id = " . TABLE_PREFIX . "users.user_id WHERE " . TABLE_PREFIX . "users.role_id IN (" . implode(',', $role_id_in) . ") AND " . TABLE_PREFIX . "users_meta.meta_key='" . $sort_by . "' GROUP BY " . TABLE_PREFIX . "users_meta.user_id ORDER BY " . TABLE_PREFIX . "users_meta.minutia " . $sort_direction . " LIMIT " . $pagination_length . " OFFSET " . $pagination_offset;

            }

        }


        // get info for display_users
        $current_list = $vce->db->get_data_object($query);

        // rekey data into array for user_id and vectors
        $vectors = array();

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
//  $vce->dump($users); 
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
            // $vce->dump($edit_users[$each_meta_data->user_id][$each_meta_data->meta_key]);
            // $vce->dump($each_meta_data->meta_key);
        }

            // get user info and cast as an object (the variable $edit_user (singular) is the user passed to the $vce object for editing)
            $user = (object) $edit_users[$edit_user];

            // create the dossier
            $dossier_for_update = $vce->generate_dossier(array('type' => 'Pbc_ManageUsers2', 'procedure' => 'update', 'user_id' => $edit_user));
            $edit_user_content = NULL;

            $ingression_form_info_content = $this->ingression_form_info($vce, $edit_user);
            $ingression_form_info_accordion = $vce->content->accordion('Ingression Form Information', $ingression_form_info_content, $accordion_expanded = false, $accordion_disabled = false, $accordion_class = 'ingression-form-info-accordion');
            
    if ($vce->user->role_hierarchy < 3) {
            $edit_user_content .= <<<EOF
$ingression_form_info_accordion
EOF;
    }


$edit_user_content .= <<<EOF
<div>
<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
EOF;
    
    $email_input = $vce->content->create_input($user->email,'Email (Cannot be edited)','Enter Email');
    $edit_user_content .= <<<EOF
     $email_input
EOF;

// additional attributes content for edit user (doesn't include email, org, group, and role)
foreach ($user_attributes as $user_attributes_key => $user_attributes_value) {

    if ($user_attributes_key == 'tester' && $vce->user->role_hierarchy > 2) {
        continue;
    }
    if ($user_attributes_key == 'uid' && $vce->user->role_hierarchy > 2) {
        continue;
    }

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
                'tag' => $tag,
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

            $role_description = array(
                'Admin' => ':&nbsp Global site owner',
                'SiteAdmin' => ':&nbsp Can see all content and edit all users',
                'OrganizationAdmin' => ':&nbsp Can create and edit all users in their organization and create groups',
                'GroupAdmin' => ':&nbsp Can create and edit all users in their group',
                'Coach' => ':&nbsp Can create and edit Cycles',
                'Coachee' => ':&nbsp Can create and edit Cycles',
                'InactiveUsers' => ':&nbsp Suspended, can not see or create content',
                'NewUsers' => ':&nbsp Can not see or create content',
            );

            foreach ($roles_hierarchical as $roles_each) {
                foreach ($roles_each as $key => $value) {
                    if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
                            $selected = $key == $user->role_id ? true : false;
                            $options_array[] = array(
                            'name' => $value['role_name'].$role_description[$value['role_name']],
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
                'tag' => $tag,
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
    $dossier_for_delete = $vce->generate_dossier(array('type' => 'Pbc_ManageUsers2', 'procedure' => 'delete', 'user_id' => $edit_user));
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
                $dossier_for_create = $vce->generate_dossier(array('type' => 'Pbc_ManageUsers2', 'procedure' => 'create'));

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

                    if ($user_attributes_key == 'tester' && $vce->user->role_hierarchy > 2) {
                        continue;
                    }
                    if ($user_attributes_key == 'uid' && $vce->user->role_hierarchy > 2) {
                        continue;
                    }

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


                $role_description = array(
                    'Admin' => ':&nbsp Global site owner',
                    'SiteAdmin' => ':&nbsp Can see all content and edit all users',
                    'OrganizationAdmin' => ':&nbsp Can create and edit all users in their organization and create groups',
                    'GroupAdmin' => ':&nbsp Can create and edit all users in their group',
                    'Coach' => ':&nbsp Can create and edit Cycles',
                    'Coachee' => ':&nbsp Can create and edit Cycles',
                    'InactiveUsers' => ':&nbsp Suspended, can not see or create content',
                    'NewUsers' => ':&nbsp Can not see or create content',
                );


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
                    'name' => $value['role_name'].$role_description[$value['role_name']],
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

// create accordion box
// $content .= $vce->content->accordion('Create New User', $new_user_content, false, false);

            }

            /* end of new user */




            /* start merge user */
            
            // check permissions for create users
            if ($this->check_permissions('merge_users')) {
            
				// the instructions to pass through the form
				$dossier = array(
				'type' => $each_component->type,
				'procedure' => 'merge'
				);

				// generate dossier
				$dossier_for_merge = $vce->generate_dossier($dossier);
			
				$form_elements = null;
			
				$input = array(
				'type' => 'text',
				'name' => 'merge_from',
				'data' => array(  
					'tag' => 'required',
				),
				'placeholder' => 'From'
				);
			
				$form_elements .= $vce->content->create_input($input,'Merge From User_id','Enter Merge From User_id');

			
				$input = array(
				'type' => 'text',
				'name' => 'merge_to',
				'data' => array(  
					'tag' => 'required',
				),
				'placeholder' => 'To'
				);
	
				$form_elements .= $vce->content->create_input($input,'Merge To User_id','Enter Merge To User_id');

				$form = <<<EOF
<form id="create_items" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_merge">
$form_elements
<input type="submit" value="Merge">
<button class="link-button cancel-button">Cancel</button>
</form>
EOF;
            
            	$merge_user_content = $vce->content->accordion('Merge User Accounts', $form);
             
            }
             
            /* end of merge user */ 






            /* start search */

            // dossier for search
            $dossier = array(
                'type' => 'Pbc_ManageUsers2',
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
                'type' => 'Pbc_ManageUsers2',
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
                'type' => 'Pbc_ManageUsers2',
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

            $user_list_content = <<<EOF
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
// $vce->dump($attributes);
            // list through attributes for head on main page
            foreach ($attributes as $each_attribute_key => $each_attribute_value) {

                if ($each_attribute_key == 'tester' && $vce->user->role_hierarchy > 2) {
                    continue;
                }
                if ($each_attribute_key == 'uid' && $vce->user->role_hierarchy > 2) {
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
                    'type' => 'Pbc_ManageUsers2',
                    'procedure' => 'pagination',
                );

                // generate dossier
                $dossier_for_sort = $vce->generate_dossier($dossier);
                $user_list_content .= <<<EOF
<th class="$th_class">

EOF;

                // check if this is a sortable attribute
                if (isset($each_attribute_value['sortable']) && $each_attribute_value['sortable']) {

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
            // $vce->dump($users);
            // loop through users
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

                $user_list_content .= '<tr>';

                //$dossier_for_edit = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'edit','user_id' => $each_user)),$vce->user->session_vector);
                $dossier_for_edit = $vce->generate_dossier(array('type' => 'Pbc_ManageUsers2', 'procedure' => 'edit', 'user_id' => $each_user));

                //$dossier_for_masquerade = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'masquerade','user_id' => $each_user)),$vce->user->session_vector);
                $dossier_for_masquerade = $vce->generate_dossier(array('type' => 'Pbc_ManageUsers2', 'procedure' => 'masquerade', 'user_id' => $each_user));

                // //$dossier_for_delete = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'delete','user_id' => $each_user)),$vce->user->session_vector);
                // $dossier_for_delete = $vce->generate_dossier(array('type' => 'Pbc_ManageUsers2', 'procedure' => 'delete', 'user_id' => $each_user));

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

//                 if ($delete_users) {
//                 //    $vce->dump('this');
//                     $user_list_content .= <<<EOF
// <form class="delete-form inline-form asynchronous-form" method="post" action="$vce->input_path">
// <input type="hidden" name="dossier" value="$dossier_for_delete">
// <button type="submit" class="btn-reset" title="Delete" value="Delete">Delete User</button>
// </form>
// EOF;

                // }

                $user_list_content .= <<<EOF
</td>
EOF;


                // list through attributes for content on main page
                foreach ($attributes as $each_attribute_key => $each_attribute_value) {

                    // The UID attribute is uc in the DB, but appears lc in this array.
                    // It needs to be uc to function in this context.
                    if ($each_attribute_key == 'uid') {
                        if ($vce->user->role_hierarchy > 2) {
                            continue;
                        }
                        $each_attribute_key = 'UID';
                    }

                    if ($each_attribute_key == 'tester' && $vce->user->role_hierarchy > 2) {
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

                    $user_list_content .= '<td><span>' . $attribute_value . '</span></td>';

                }

                $user_list_content .= '</tr>';

            }

            $user_list_content .= <<<EOF
</tbody>
</table>
$pagination_markup
EOF;

        }


    // Set up tabs
    $tab_input = array (
        'tabs__container1' => array(
            'tabs' => array(
                'tab1' => array(
                    'id' => 'view-users',
                    'label' => 'View Users',
                    'content' => $search_content . $filterAccordion . $user_list_content
                ),
                'tab2' => array(
                    'id' => 'add-users',
                    'label' => 'Add Users',
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
$merge_user_content
$user_list_content
EOF;

$vce->content->add('main',$content);

}


    public function ingression_form_info($vce, $user_id){
        
        $content = NULL;
        //array for all preset fields
		$preset_fields = array();

		if (isset($user_id)) {
            // $user_id = 13;
			//set all available fields from last submission by this user
			$query = "SELECT * FROM " . TABLE_PREFIX . "ingression_forms WHERE user_id='$user_id' ORDER BY created DESC";
			$vce->db->query($query);
			$results = $vce->db->get_data_object($query);

			if (!empty($results)) {
				// $vce->dump($results[0]);
				foreach ($results[0] as $k=>$v) {
					// set each field from the query to a member of $preset_fields
					$preset_fields[$k] = $v;
				}
			} else {
				// if there are no records for this user, then say so
                $content .= <<<EOF
                <br>
                This user has no recorded ingression forms.
                <br>
EOF;
                $no_content = TRUE;
			}
		}

        if (empty($no_content)) {

        $email = $preset_fields['email'];
        //get org and group names
        $datalist = $vce->get_datalist_items(array('datalist' => 'organizations_datalist'));
        $org_name = isset($datalist['items'][$preset_fields['organization']]['name']) ? $datalist['items'][$preset_fields['organization']]['name'] : null;
// $vce->dump($org_name);

$datalist = $vce->get_datalist_items(array('name' => 'group'));
// $vce->dump($datalist);
$group_name = isset($datalist['items'][$preset_fields['group']]['name']) ? $datalist['items'][$preset_fields['group']]['name'] : null;
// $vce->dump($group_name);

        $content .= <<<EOF
            Recorded ingression form data for $email <br><br>
EOF;
        foreach ($preset_fields as $k=>$v) {
            if ($k == 'organization') {
                $v == $org_name;
            }
            if ($k == 'group') {
                $v == $group_name;
            }

        $content .= <<<EOF
            $k : &nbsp; &nbsp; &nbsp; $v <br>
EOF;
        }
    }
        $content .= '<br><br>User object info:<br><br>';
        foreach ($vce->user->load_user_object($user_id) as $k=>$v) {

            $content .= <<<EOF
                $k : &nbsp; &nbsp; &nbsp; $v <br>
EOF;
        }

        return $content;
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
     * merge users
     */
    public function merge($input) {
    
    	global $vce;
    	
    	$invalid_users = false;
    	
    	// if (!empty($input['merge_from'])) {
    	 
    	// 	$from_user = $vce->user->find_users(array('user_ids' => $input['merge_from']), false, true, true);
    		
    	// 	if (empty($from_user) || count($from_user) > 1) {
    	// 		$invalid_users = true;
    	// 	}
    	
    	// } else {
    	// 	$invalid_users = true;
    	// }

    	// if (!empty($input['merge_to'])) {
    	 
    	// 	$to_user = $vce->user->find_users(array('user_ids' => $input['merge_to']), false, true, true);
    		
    	// 	if (empty($to_user)|| count($to_user) > 1) {
    	// 		$invalid_users = true;
    	// 	}
    	
    	// } else {
    	// 	$invalid_users = true;
    	// }
    	
    	// // if not valid users 
    	// if ($invalid_users) {
		// 	echo json_encode(array('response' => 'error','procedure' => 'merge','message' => "Invalid Users", 'form' => 'create'));
		// 	return;
    	// }
    	
		// $attributes = null;    	

		// foreach ($from_user[0] as $key=>$value) {
		// 	if (!in_array($key, array('user_id','role_id','role_hierarchy','role_name','vector','hash','email','created_at','updated_at'))) {
		// 		if (!empty($value) && empty($to_user[0]->$key)) {
		// 			$attributes[$key] = $value;
		// 		}
		// 	}		
		// }
	
		// // merge user attributes
		// if (!empty($attributes)) {
		// 	$vce->user->add_attributes($attributes, true, $input['merge_to']);
		// }
    	
		// $query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id=" . $input['merge_from'] . " AND meta_key='lookup'";
		// $pseudonym = $vce->db->get_data_object($query);
		
		// if (!empty($pseudonym)) {
		
		// 	$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id='" . $input['merge_to'] . "' AND meta_key='pseudonym' AND minutia='" . $pseudonym[0]->meta_value . "'";
		// 	$pseudonym_check = $vce->db->get_data_object($query);

		// 	if (empty($pseudonym_check)) {
		
		// 		// create encryption for from user email of UID, if present
        //         if (isset($from_user[0]->UID)) {
        //             $pseudonym_email = $vce->site->encryption($from_user[0]->UID, $to_user[0]->vector);
        //         } else {
		// 		    $pseudonym_email = $vce->site->encryption($from_user[0]->email, $to_user[0]->vector);
        //         }
		
		// 		$records[] = array(
		// 		'user_id' => $input['merge_to'],
		// 		'meta_key' => 'pseudonym',
		// 		'meta_value' => $pseudonym_email,
		// 		'minutia' => $pseudonym[0]->meta_value
		// 		);
			
		// 		$vce->db->insert('users_meta', $records);
			
		// 	}

		// }

        // change path of media items and move file to correct directory
        $query = "SELECT b.id, b.meta_value FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'created_by' AND a.meta_value = '" . $input['merge_from'] . "' AND b.meta_key='path'";
        $path = $vce->db->get_data_object($query);
        // $vce->log($query);      
        if (!empty($path)) {
            foreach ($path as $key=>$value) {
                // $vce->log($key);
                // $vce->log($value);
                if (isset($value->meta_value)) {
                    $media_path_parts = explode('_', $value->meta_value);
                    if (count($media_path_parts) > 1 && $media_path_parts[0] != $input['merge_to']) {
                        // find or create uploads directory for merge_to user
                        $target_dir = BASEPATH . 'vce-content/uploads/' . $input['merge_to'];
                        // $vce->log($target_dir);
                        if (!is_dir($target_dir)) {
                            if (!mkdir($target_dir, 0775, false)) {
                                die('Failed to create new directories...');
                            }
                        }
                        // create new path and name for file
                        $new_path = $input['merge_to'] . '_' . $media_path_parts[1];
                        $full_old_path = BASEPATH . 'vce-content/uploads/' . $media_path_parts[0] . '/' . $value->meta_value;
                        $full_new_path = BASEPATH . 'vce-content/uploads/' . $input['merge_to'] . '/' . $new_path;
                        rename($full_old_path, $full_new_path);

                        // change path metadata in components_meta
                        $query = "UPDATE vce_components_meta SET meta_value='" . $new_path . "' WHERE id='" . $value->id . "'";
                        $vce->db->query($query);
                    }
                }
            }
        }
		

		// merge all the components created_by attributes
		$query = "SELECT id FROM " . TABLE_PREFIX . "components_meta WHERE meta_key='created_by' AND meta_value='" . $input['merge_from'] . "'";
		$created_by = $vce->db->get_data_object($query);
		
		if (!empty($created_by)) {
			foreach ($created_by as $key=>$value) {
				$query = "UPDATE vce_components_meta SET meta_value='" . $input['merge_to'] . "' WHERE id='" . $value->id . "'";
				$vce->db->query($query);
			}
		}

        // merge all the components originator_id attributes
		$query = "SELECT id FROM " . TABLE_PREFIX . "components_meta WHERE meta_key='originator_id' AND meta_value='" . $input['merge_from'] . "'";
		$originator_id = $vce->db->get_data_object($query);
		
		if (!empty($originator_id)) {
			foreach ($originator_id as $key=>$value) {
				$query = "UPDATE vce_components_meta SET meta_value='" . $input['merge_to'] . "' WHERE id='" . $value->id . "'";
				$vce->db->query($query);
			}
		}
		

        // change user_id in pipline delineated lists

		$search = '%|' . $input['merge_from'] . '|%';
		$query = "SELECT * FROM " . TABLE_PREFIX . "components_meta WHERE meta_value LIKE '" . $search . "'";
		$members = $vce->db->get_data_object($query);
		
		if (!empty($members)) {
			foreach ($members as $key=>$value) {
		
				$members_list = $value->meta_value;
				$members_list = str_replace('|' . $input['merge_from'] . '|','|' . $input['merge_to'] . '|', $members_list);

				// merging members lists
				$update = array('meta_value' => $members_list);
				$update_where = array('id' => $value->id);
				$vce->db->update('components_meta', $update, $update_where);
				
				$query = "SELECT * FROM " . TABLE_PREFIX . "components_meta WHERE component_id='" . $value->component_id . "' AND meta_key='pbc_roles'";
				$pbc_roles = $vce->db->get_data_object($query);
				
				if (!empty($pbc_roles)) {
					
					$pbc = json_decode($pbc_roles[0]->meta_value, true);
					
					if (isset($pbc[$input['merge_from']])) {
					
						$pbc[$input['merge_to']] = $pbc[$input['merge_from']];
						unset($pbc[$input['merge_from']]);
						
						// merging members lists
						$update = array('meta_value' => json_encode($pbc));
						$update_where = array('id' => $pbc_roles[0]->id);
						$vce->db->update('components_meta', $update, $update_where);
						
					}
				
				
				}

			}
		}
		
		$query = "SELECT * FROM " . TABLE_PREFIX . "datalists WHERE user_id=" . $input['merge_from'];
		$datalists = $vce->db->get_data_object($query);
		
		$members_from = array();
		$members_datalist_from = array();
		
		if (!empty($datalists)) {
			foreach ($datalists as $key=>$value) {
		
				$datalist_value = $vce->get_datalist(array('datalist_id' => $value->datalist_id));
				
				//$vce->dump('merge_from');
				//$vce->dump($datalist_value);
			
				if (!empty($datalist_value)) {
					foreach ($datalist_value as $each) {
						if (!empty($each['members'])) {
							// make members into an array
							$members_from[$each['datalist']] = explode('|', trim($each['members'],'|'));
						}
						$members_datalist_from[$each['datalist']] = $each['datalist_id'];
					}
				}
		
			}
		}
		
		$query = "SELECT * FROM " . TABLE_PREFIX . "datalists WHERE user_id=" . $input['merge_to'];
		$datalists = $vce->db->get_data_object($query);
		
		$members_to = array();
		$members_datalist_to = array();
		
		if (!empty($datalists)) {
			foreach ($datalists as $key=>$value) {
		
				$datalist_value = $vce->get_datalist(array('datalist_id' => $value->datalist_id));

				//$vce->dump('merge_to');
				//$vce->dump($datalist_value);
			
				if (!empty($datalist_value)) {
					foreach ($datalist_value as $each) {
						if (!empty($each['members'])) {
							// make members into an array
							$members_to[$each['datalist']] = explode('|', trim($each['members'],'|'));
						}
						$members_datalist_to[$each['datalist']] = $each['datalist_id'];
					}
				}
			
			}
		
		}
		
		if (!empty($members_datalist_from)) {
			foreach ($members_datalist_from as $key=>$value) {
			
				//check for members value in from record
				if (isset($members_from[$key])) {
		
					// does it exist in the to list?
					if (isset($members_datalist_to[$key])) {
					
						if (!empty($members_from[$key])) {
					
							if (!empty($members_to[$key])) {

								$member_ids = array_merge($members_from[$key], $members_to[$key]);
						
							} else {
						
								$member_ids = $members_from[$key];
						
							}
							
							
							sort($member_ids);
							$members = '|' . implode('|', array_unique($member_ids)) . '|';

							$attributes = array(
							'datalist_id' => $members_datalist_to[$key],
							'meta_data' => array('members' => $members)
							);
						
							$vce->update_datalist($attributes);
						
						}

					} 
		
				} else {
					// non-members records
					
					// if it doesn't exist, then change user_id in datalist
					if (!isset($members_datalist_to[$key])) {
					
						// $vce->dump($members_datalist_from[$key]);
						
						$attributes = array(
						'datalist_id' => $members_datalist_from[$key],
						'relational_data' => array('user_id' => $input['merge_to'])
						);
						
						//$vce->dump($attributes);
						
						$vce->update_datalist($attributes);
						
					}
					
				}
	
			}
		}




        // HSCC addition:
        // Edit user_list in vce_datalists_items_meta
        $search = '%|' . $input['merge_from'] . '|%';
		$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value LIKE '" . $search . "'";
		$members = $vce->db->get_data_object($query);
		
		if (!empty($members)) {
			foreach ($members as $key=>$value) {
		
				$members_list = $value->meta_value;
				$members_list = str_replace('|' . $input['merge_from'] . '|','|' . $input['merge_to'] . '|', $members_list);

				// merging members lists
				$update = array('meta_value' => $members_list);
				$update_where = array('id' => $value->id);
				$vce->db->update('components_meta', $update, $update_where);

			}
		}






		
		// finally, delete the from user
		user::delete_user($input['merge_from']);
		
        // echo json_encode(array('response' => 'success', 'message' =>'Merge complete from ' . $from_user[0]->email . ' to ' . $to_user[0]->email, 'form' => 'create'));
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
            // $vce->dump($user_keys);
            // exit;
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
            //  if ($value['meta_key'] == 'organization') {
            //         $vce->dump($all_users[$value['user_id']][$value['meta_key']]);
            //     }

            // test multiples
            for ($i = 0; $i < count($search_values); $i++) {

                // $vce->dump($value['meta_key']);
                $pos = strrpos(strtolower($all_users[$value['user_id']][$value['meta_key']]), strtolower($search_values[$i]));
                // if ($all_users[$value['user_id']][$value['meta_key']] == 'DTL') {
                //     $vce->dump('yesss');
                // }
                if ($pos === false) {
                	continue;
                } else {
                    // $vce->dump($all_users[$value['user_id']][$value['meta_key']]);
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
        // $vce->dump(count($all_users));

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


//     /**
//      * fields to display when this is created
//      */
//     public function recipe_fields($recipe) {

//         $title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
//         $url = isset($recipe['url']) ? $recipe['url'] : null;

//         $elements = <<<EOF
// <input type="hidden" name="auto_create" value="forward">
// <label>
// <input type="text" name="title" value="$title" tag="required" autocomplete="off">
// <div class="label-text">
// <div class="label-message">Title</div>
// <div class="label-error">Enter a Title</div>
// </div>
// </label>
// <label>
// <input type="text" name="url" value="$url" tag="required" autocomplete="off">
// <div class="label-text">
// <div class="label-message">URL</div>
// <div class="label-error">Enter a URL</div>
// </div>
// </label>
// EOF;

//         return $elements;

//     }

}