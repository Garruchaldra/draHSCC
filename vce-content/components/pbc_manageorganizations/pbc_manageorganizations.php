<?php
class Pbc_Manageorganizations extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Pbc Manage Organizations',
			'description' => 'Add, edit and delete datalists regarding organizations',
			'category' => 'pbc'
		);
	}
	

	/**
	 *
	 */
	public function as_content($each_component, $vce) {

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter');
		
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css');
		


		// see only what you have permission to see 
		// (org admins see only their org, group admins only their group)
		if ($vce->user->role_id == 6) {
			$group_admin_item_id = $vce->user->group;	
			$query = "SELECT datalist_id FROM vce_datalists WHERE item_id = ".$vce->user->organization;
			$org_id = $vce->db->get_data_object($query);
			if (isset($org_id)) {		
				$vce->datalist_id = $org_id[0]->datalist_id;
			}
		}

		if ($vce->user->role_id == 5	) {
			$query = "SELECT datalist_id FROM vce_datalists WHERE item_id = ".$vce->user->organization;
			$org_id = $vce->db->get_data_object($query);
			if (isset($org_id)) {		
				$vce->datalist_id = $org_id[0]->datalist_id;
			}
		}
		
		$content = NULL;

			//treat as if the organizations datalist was already selected
			$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_key = 'name' AND meta_value='organization'";

			$org_info = $vce->db->get_data_object($query);
			if (!isset($vce->datalist_id)) {		
				$vce->datalist_id = $org_info[0]->datalist_id;
			}


	
		// datalist_id found in vce object
		if (isset($vce->datalist_id)) {
		
			if (isset($vce->item_id)) {
			
				// get the name of the parent
				
				$query = "SELECT * FROM " . TABLE_PREFIX . "datalists WHERE item_id='"  . $vce->item_id . "'";

				$parent_info = $vce->db->get_data_object($query);
			
				// get the name of the parent
				$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_items_meta WHERE item_id='"  . $vce->item_id . "' AND meta_key='name'";
				$parent_name = $vce->db->get_data_object($query);
				
			}
		
			$query = "SELECT meta_key, meta_value FROM " . TABLE_PREFIX . "datalists_meta WHERE datalist_id='"  . $vce->datalist_id . "'";
			$meta_data = $vce->db->get_data_object($query);
			
			// create datalist object with meta_data 
			$datalist = new StdClass();
			foreach ($meta_data as $each_meta_data) {		
				$key = $each_meta_data->meta_key;
				$datalist->$key = $each_meta_data->meta_value;
			}

			$query = "SELECT a.item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id WHERE datalist_id='" . $vce->datalist_id . "' AND b.meta_key = 'name' ORDER BY name";
			$options = $vce->db->get_data_object($query);

			$input = array(
				'type' => 'text',
				'name' => 'name',
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);

			$manageOrgsInput = $vce->content->create_input($input,'Name of Organization');

		// load hooks
		if (isset($vce->site->hooks['titleBar'])) {
			foreach ($vce->site->hooks['titleBar'] as $hook) {
				$title = call_user_func($hook, 'Manage Organizations & Groups', 'manage-organizations');
			}
		}

		$vce->content->add('title', $title);

		
		$content .= <<<EOF
<span class="manage-orgs"></span>
<div class="label-message instructions">This utility allows for the administration of registered organizations and groups. Each administrator sees the organizations or groups
for which they are personally responsible.</div>
EOF;
if ($vce->datalist_id == 1) {
//put an extra "add organization" at the top
			// make a nice name for the title
			// $datalist_name = isset($parent_name->meta_value) ? $parent_name->meta_value . ' / ' . $datalist->name : $datalist->name;

			$datalist_name = !empty($datalist->name) ? $datalist->name : "";
			
			$datalist_name = ucfirst($datalist_name);
			if ($datalist_name == 'Group') {
				$datalist_name_article = 'a';
			} else {
				$datalist_name_article = 'an';
			}
			
			$dossier_for_add = $vce->user->encryption(json_encode(array('type' => 'Pbc_Manageorganizations','procedure' => 'add','datalist_id' => $vce->datalist_id)),$vce->user->session_vector);		

			if (isset($parent_name)) {
			
				$datalist_name .= ' (' . $parent_name[0]->meta_value . ')';
			
			}

$value = (isset($value)?$value:0);
$content .= <<<EOF
<h2>Create a new organization</h2>
<form id="add-id" class="asynchronous-form add-org-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_add">
$manageOrgsInput
<input type="hidden" name="sequence" value="$value" >
<input class="button__primary" type="submit" value="Create">
</form>
<hr>
EOF;
}
			
			// starting value to prevent errrors
			$value = 1;

// 
// $content .= <<<EOF
// 
// <label>
// <div class="label-message instructions">This utility allows for the administration of registered organizations and groups. Each administrator sees the organizations or groups
// for which they are personally responsible. Select the name of the organization or group you want to update to see the edit fields.</div>
// </label>
// 
// 
// </p>
// 			
// EOF;	
$dossier_for_add = $vce->user->encryption(json_encode(array('type' => 'ManageDatalists','procedure' => 'add','datalist_id' => $vce->datalist_id)),$vce->user->session_vector);		


$input = array(
	'type' => 'text',
	'name' => 'name',
	'data' => array (
		'autocapitalize' => 'none',
		'tag' => 'required',
	)
);

$manageGroupsInput = $vce->content->create_input($input,'Name of Group');

if (!isset($vce->org_name)) {
	$user_data = NULL;
	if (class_exists('Pbc_utilities')) { 
		$user_data = Pbc_utilities::get_user_data($vce->user->user_id);
	}
	$org_name = $user_data->organization_name . ' (id:' . $vce->datalist_id . ')';

} else {
	$org_name = $vce->org_name . ' (id:' . $vce->datalist_id . ')';
}

$content .= <<<EOF
<form id="add-id" class="asynchronous-form add-group-form" method="post" action="$vce->input_path">
<h2>Create a new group for the $org_name organization</h2>
<input type="hidden" name="dossier" value="$dossier_for_add">
$manageGroupsInput
<input type="hidden" name="sequence" value="$value" >
<input class="button__primary" type="submit" value="Create">
<hr>
<h2>Your Groups:</h2>
</form>

EOF;

$i = 0;
$y = 0;
			foreach ($options as $value=>$each_option) {
				
				// limit list for development
				// $i++;
				// if ($i > 20) {
				// 	break;
				// }
				// limit list for development


				if (isset($group_admin_item_id) && $each_option->item_id != $group_admin_item_id) {
					continue;
				}
				$i++;

				$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_items_meta WHERE item_id='" . $each_option->item_id . "'";
				$meta_info = $vce->db->get_data_object($query);
				
					$meta_data = array();
					foreach ($meta_info as $meta_data_key=>$meta_data_value) {
						// get name of meta_key
						$this_key = $meta_data_value->meta_key;
						// add meta_value to this option
						$each_option->$this_key = $meta_data_value->meta_value;
					}
					
				// for sequence add one more to value
				$value = $value + 2;
				
				//
				$each_option->name = isset($each_option->name) ? $each_option->name : $each_option->datalist_id;
				// create dossier values
				$dossier_for_update = $vce->user->encryption(json_encode(array('type' => 'ManageDatalists','procedure' => 'update','item_id' => $each_option->item_id,'datalist_id' => $each_option->datalist_id)),$vce->user->session_vector);		
				$dossier_for_delete_sub = $vce->user->encryption(json_encode(array('type' => 'ManageDatalists','procedure' => 'delete','item_id' => $each_option->item_id,'datalist_id' => $each_option->datalist_id)),$vce->user->session_vector);		

				$input = array(
					'type' => 'text',
					'name' => 'name',
					'value' => $each_option->name,
					'data' => array (
						'autocapitalize' => 'none',
						'tag' => 'required',
					)
				);
	
				$manageOrgsInputwithVals = $vce->content->create_input($input,'Name');

				if ($vce->datalist_id == 1) {
					$filter_by = array('organization' => $each_option->item_id);
				} else {
					$filter_by = array('group' => $each_option->item_id);
				}
 
				$users_in_org = (!empty($filter_by) ? pbc_utilities::filter_users($filter_by, $vce) : '');
				$users_in_org = trim($users_in_org, '|');
				// $users_in_org = explode('|', $users_in_org);
				$users_in_org = array('user_ids'=>$users_in_org);
				// $users_in_org = implode(',', $users_in_org);
				// $users_in_org = trim($users_in_org, ',');
// $vce->dump($users_in_org);
				$site_users_per_org = $vce->user->get_users($users_in_org);
				// $vce->dump($site_users_per_org);
				$users_to_display = NULL;
				if (is_array($site_users_per_org)) {
					foreach ($site_users_per_org as $u) {
						if (isset($u->first_name) && isset($u->last_name)) {
							$users_to_display.= '<option value="'. $u->email .'">' . $u->first_name . ' ' . $u->last_name . '</option>';
						}
					}
				} else {
					// $vce->dump('nope: '.$y);
					$y++;
				}
				

				$user_list = NULL;
				$user_list = <<<EOF
				<label for="users-in-org-$each_option->item_id">Users:</label>
				<select name="users-in-org" id="users-in-org-$each_option->item_id" size="3.5">
				$users_to_display
				</select>
EOF;

$manageOrgs_accordion_content = <<<EOF
$user_list
<form id="update-$each_option->item_id" class="asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_update">
$manageOrgsInputwithVals
<input type="hidden" name="sequence" value="$each_option->sequence">
<input type="submit" value="Update">
</form>
<form id="delete-$each_option->item_id" class="delete-form float-right-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete_sub">
<input type="submit" value="Delete">
</form>
EOF;

// $vce->dump($datalist->hierarchy);
				if (isset($datalist->hierarchy)) {
	
					$query = "SELECT datalist_id FROM " . TABLE_PREFIX . "datalists WHERE item_id='"  . $each_option->item_id . "'";
					$child = $vce->db->get_data_object($query)[0];

					if (isset($child->datalist_id)) {
				
						// get name of first child
						$children_name = ucfirst(json_decode($datalist->hierarchy, true)[0]);
						$dossier_for_edit_children = $vce->user->encryption(json_encode(array('type' => 'Pbc_Manageorganizations','procedure' => 'edit','item_id' => $each_option->item_id, 'datalist_id' => $child->datalist_id, 'org_name' => $each_option->name)),$vce->user->session_vector);		

$manageOrgs_accordion_content .= <<<EOF
<p>
<form id="children-$each_option->datalist_id" class="asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_edit_children">
<input type="submit" value="View/Edit Groups">
</form>
</p>
EOF;

					// end if
					}

					$accordionTitle = <<<EOF
$each_option->name &nbsp;&nbsp;&nbsp;(id: $each_option->item_id)
EOF;

					// create accordion box
					$content .= $vce->content->accordion($accordionTitle, $manageOrgs_accordion_content);
				// end if
				} else {
// this section covers the end of the datalist chain: those items which don't have a heirarchy


	
$query = "SELECT datalist_id FROM " . TABLE_PREFIX . "datalists WHERE item_id='"  . $each_option->item_id . "'";
$result = $vce->db->get_data_object($query);

if (isset($result[0])) {
	$child = $result[0]->datalist_id;

	if (isset($child->datalist_id)) {

		// get name of first child
		$children_name = ucfirst(json_decode($datalist->hierarchy, true)[0]);
		$dossier_for_edit_children = $vce->user->encryption(json_encode(array('type' => 'ManageDatalists','procedure' => 'edit','item_id' => $each_option->item_id, 'datalist_id' => $child->datalist_id)),$vce->user->session_vector);		

		$manageOrgs_accordion_content .= <<<EOF
		<p>
		<form id="children-$each_option->datalist_id" class="asynchronous-form" method="post" action="$vce->input_path">
		<input type="hidden" name="dossier" value="$dossier_for_edit_children">
		<input type="submit" value="Edit $children_name">
		</form>
		</p>
		EOF;

	}
}
$accordionTitle = <<<EOF
$each_option->name
EOF;

// create accordion box
$content .= $vce->content->accordion($accordionTitle, $manageOrgs_accordion_content);
// end if

				}
				
				
				if (isset($parent_info[0]->parent_id) && $vce->user->role_hierarchy < 3) {

					$dossier_for_edit_parent = $vce->user->encryption(json_encode(array('type' => 'ManageDatalists','procedure' => 'edit','datalist_id' => $parent_info[0]->parent_id)),$vce->user->session_vector);		

$content .= <<<EOF
<p>
<form class="asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_edit_parent">
<input type="submit" value="Back to Organizations List">
</form>
</p>
EOF;

				}

$content .= <<<EOF
</p>
EOF;
			// end foreach
			}

			// make a nice name for the title
			// $datalist_name = isset($parent_name->meta_value) ? $parent_name->meta_value . ' / ' . $datalist->name : $datalist->name;

			$datalist_name = !empty($datalist->name) ? $datalist->name : "";
			$datalist_name = ucfirst($datalist_name);
			if ($datalist_name == 'Group') {
				$datalist_name_article = 'a';
			} else {
				$datalist_name_article = 'an';
			}
			$dossier_for_add = $vce->user->encryption(json_encode(array('type' => 'ManageDatalists','procedure' => 'add','datalist_id' => $vce->datalist_id)),$vce->user->session_vector);		

			if (isset($parent_name)) {
			
			$datalist_name .= ' (' . $parent_name[0]->meta_value . ')';
			
			} elseif (!isset($parent_name)) {
			
					$query = "SELECT b.meta_value FROM " . TABLE_PREFIX . "datalists AS a LEFT JOIN " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id WHERE a.datalist_id="  . $vce->datalist_id . " AND b.meta_key = 'name'";
					$organization_name = isset($vce->db->get_data_object($query)[0]) ? $vce->db->get_data_object($query)[0] : NULL;

					if (isset($organization_name)) {
				
						// get name of first child
						$datalist_name .= ' (' . $organization_name->meta_value . ')';
					}
			
			}

	$vce->content->add('main', $content);
	return;
			
		}
		
		$query = "SELECT * FROM " . TABLE_PREFIX . "datalists WHERE parent_id='0'";
		$datalists = $vce->db->get_data_object($query);
		
$content .= <<<EOF
<p>
<table id="datalist" class="tablesorter">
<thead>
<tr>
<th></th>
<th>Name</th>
<th>Datalist</th>
<th>Type</th>
<th>Hierarchy</th>
<th>User Id</th>
<th>Component Id</th>
<th></th>
</tr>
</thead>
EOF;

		
		foreach ($datalists as $each_datalist) {
		
			$query = "SELECT meta_key, meta_value FROM " . TABLE_PREFIX . "datalists_meta WHERE datalist_id='" . $each_datalist->datalist_id . "'";
			$datalist_meta = $vce->db->get_data_object($query);
			
			$listinfo = array();	
		
			foreach ($datalist_meta as $each_meta) {
			
				$listinfo[$each_meta->meta_key ] = $each_meta->meta_value;
		
			}


			$dossier_for_edit = $vce->user->encryption(json_encode(array('type' => 'ManageDatalists','procedure' => 'edit','datalist_id' => $each_datalist->datalist_id)),$vce->user->session_vector);		

// edit
$content .= <<<EOF
<tr>
<td class="align-center">
<form class="inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_edit">
<input type="submit" value="Edit">
</form>
</td>
EOF;

			$list_name = isset($listinfo['name']) ? $listinfo['name'] : null;
			$list_datalist = isset($listinfo['datalist']) ? $listinfo['datalist'] : null;
			$list_type = isset($listinfo['type']) ? $listinfo['type'] : null;

			$content .= '<td>' . $list_name . '</td>';
			$content .= '<td>' . $list_datalist . '</td>';
			$content .= '<td>' . $list_type . '</td>';
			
			if (isset($listinfo['hierarchy'])) {

				$content .= '<td>' . $listinfo['hierarchy'] . '</td>';
			
			} else {
			
				$content .= '<td></td>';
			
			}
			
			if ($each_datalist->user_id != 0) {

				$content .= '<td>' . $each_datalist->user_id . '</td>';
			
			} else {
				
				$content .= '<td></td>';
			
			}
			
			if ($each_datalist->component_id != 0) {
			
				$content .= '<td>' . $each_datalist->component_id . '</td>';
				
			} else {

				$content .= '<td></td>';	
			
			}
	
			$dossier_for_delete = $vce->user->encryption(json_encode(array('type' => 'ManageDatalists','procedure' => 'delete','item_id' => 'all','datalist_id' => $each_datalist->datalist_id)),$vce->user->session_vector);
// delete
$content .= <<<EOF
<td class="align-center">
<form class="delete-form inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<input type="submit" value="Delete">
</form>
</td>
</tr>
EOF;

		}
	
		
$content .= <<<EOF
</table>
</p>
EOF;

		$vce->content->add('main', $content);
	}


	/**
	 * Edit a datalist
	 */

	public function edit($input) {

		$vce = $this->vce;
		
		// add key value to page object on next load
		$vce->site->add_attributes('datalist_id',$input['datalist_id']);
		
		if (isset($input['item_id'])) {
			// add key value to page object on next load
			$vce->site->add_attributes('item_id',$input['item_id']);
		}

		if (isset($input['org_name'])) {
			// add key value to page object on next load
			$vce->site->add_attributes('org_name',$input['org_name']);
		}
		
		echo json_encode(array('response' => 'success','procedure' => 'edit','action' => 'reload','delay' => '0', 'message' => 'session data saved'));
		return;
		
	}

	
	/**
	 * Create a new
	 */
	public function add($input) {

		global $vce;
		// add key value to page object on next load
		$vce->site->add_attributes('datalist_id',$input['datalist_id']);
		
		$new_id = $vce->add_datalist_item($input);
		if ($input['datalist_id'] == 1) {
			$group_input = array(
				'datalist_id' => $new_id,
				'name' => 'Default Group',
				'sequence' => 0,
			);
			$this->add_default_group($group_input);
		}

		echo json_encode(array('response' => 'success','procedure' => 'create','action' => 'reload','message' => 'Added'));
		return;
	}


	/**
	 * Create a new Organization, a default group, and add the user to that new organization (this is called from the manage_ingression component) 
	 */
	public function auto_add($input) {

		global $vce;

		if (!isset($input['datalist_id'])) {
			return FALSE;
		}

		$datalist = $vce->get_datalist_items(array('datalist' => 'organizations_datalist'));
        foreach ($datalist['items'] as $k=>$v) {
			if ($v['name'] == $input['name']) {
				echo json_encode(array('response' => 'error', 'form' => 'auto_add', 'procedure' => 'create','action' => 'reload','message' => 'This organization already exists.'));
				return;
			}
		}

		$new_id = $vce->add_datalist_item($input);
		// the id stored for org and group are item_id's, not datalist_id's
		$datalist = $vce->get_datalist_items(array('datalist_id' => $new_id));

		// $vce->log($new_id);
		if ($input['datalist_id'] == 1) {
			$group_input = array(
				'datalist_id' => $new_id,
				'name' => 'Default Group',
				'sequence' => 0,
			);
			$new_group_id = $this->add_default_group($group_input);
		}

		$user_input = array(
			'user_id' => $input['user_id'],
			'organization' => $datalist['item_id'],
			'group' => $new_group_id
		);

		$vce->user->update($user_input);

		$vce->site->add_attributes('newly_created_org_id', $datalist['item_id']);
		$vce->site->add_attributes('newly_created_group_id', $new_group_id);

		echo json_encode(array('response' => 'success', 'form' => 'auto_add', 'procedure' => 'create','action' => 'reload','message' => 'Added New Organization'));
		return;
	}

	public function add_default_group($input) {

		global $vce;

		$new_group_id = $vce->add_datalist_item($input);

		return $new_group_id;
	}

	


	/**
	 * update datalist_item
	 */
	public function update($input) {
	
		global $vce;
		
		// add key value to page object on next load
		$vce->site->add_attributes('datalist_id',$input['datalist_id']);
		
		$attributes = array (
		'item_id' => $input['item_id'],
		'relational_data' => array('sequence' => $input['sequence']),
		'meta_data' => array ('name' => $input['name'])
		);	
		
		// update item
		$vce->update_datalist_item($attributes);
				
		echo json_encode(array('response' => 'success','procedure' => 'update','action' => 'reload','message' => 'Updated'));
		return;
	
	}	


	/**
	 * Delete datalist
	 */
	public function delete($input) {
	
		global $vce;
		
		// if item_id is set to delete all, then don't add attribute for reload
		if ($input['item_id'] != "all") {
			// add key value to page object on next load
			$vce->site->add_attributes('datalist_id',$input['datalist_id']);
		}
		
		$attributes = array (
		'item_id' => $input['item_id'], 
		'datalist_id' => $input['datalist_id']
		);
		
		// send to remove_datalist function
		$vce->remove_datalist($attributes);

		echo json_encode(array('response' => 'success','procedure' => 'delete','action' => 'reload','message' => 'Deleted'));
		return;
	
	}
		

	/**
	 * fileds to display when this is created
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