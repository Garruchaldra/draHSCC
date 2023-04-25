<?php
class Pbc_Manageorganizations2 extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Pbc Manage Organizations 2',
			'description' => 'Add, edit and delete datalists regarding organizations. V2 adds pagination and filtering',
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

		$roles = json_decode($vce->site->roles, true);
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

		// set up filtering
		$filter_by = array();

        foreach ($vce as $key => $value) {
            if (strpos($key, 'filter_by_') !== FALSE) {
				// $vce->dump($key);
				// $vce->dump($value);
                $filter_by[str_replace('filter_by_', '', $key)] = $value;
            }
        }


		
		$content = NULL;

			//treat as if the organizations datalist was already selected
			// set datalist_id to the original "organization" datalist by querying it
			// all orgs are items of that datalist
			$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_key = 'name' AND meta_value='organization'";

			$org_info = $vce->db->get_data_object($query);

			if (isset($org_info[0]->datalist_id)) {
				$organization_list_datalist_id = $org_info[0]->datalist_id;
				if (!isset($vce->datalist_id)) {		
					$vce->datalist_id = $org_info[0]->datalist_id;
				}
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



		// pagination dossier
		$dossier = array(
			'type' => 'Pbc_Manageorganizations2',
			'procedure' => 'pagination',
		);
		if (isset($vce->active_filter)) {
			$dossier['active_filter'] = $vce->active_filter; 
		}

		$dossier_for_pagination = $vce->generate_dossier($dossier);

		$pagination_current = (isset($vce->pagination_current))? $vce->pagination_current : 1;
		$number_of_pages = (isset($vce->number_of_pages))? $vce->number_of_pages : 1;
		$pagination_length = isset($vce->pagination_length) ? $vce->pagination_length : 25;


		        // get total count of organizations
				$query = "SELECT count(a.item_id) AS count FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id WHERE datalist_id='" . $vce->datalist_id . "' AND b.meta_key = 'name'";

				$count_data = $vce->db->get_data_object($query);

				// $vce->dump($count_data);
				// exit;
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
		
	

		// these defaults are not currently used, but might be in the future:
		$sort_by = (isset($vce->sort_by))? $vce->sort_by : "name";
		$sort_direction = (isset($vce->sort_direction))? $vce->sort_direction : "asc";
		$inputtypes = (isset($vce->inputtypes))? $vce->inputtypes : "";


		$pagination_previous = ($pagination_current > 1) ? $pagination_current - 1 : 1;
		$pagination_next = ($pagination_current < $number_of_pages) ? $pagination_current + 1 : $number_of_pages;


		$pagination_markup = NULL;
		// tell page to show pagination
		$pagination = TRUE;
			
		if ($pagination) {
		
		$pagination_markup = <<<EOF
	<div class="pagination">
		<div class="pagination-controls">
		Total: $pagination_count &nbsp; &nbsp; &nbsp; 
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

		$query = "SELECT meta_key, meta_value FROM " . TABLE_PREFIX . "datalists_meta WHERE datalist_id='"  . $vce->datalist_id . "'";
		$meta_data = $vce->db->get_data_object($query);

		// create datalist object with meta_data 
		$datalist = new StdClass();
		foreach ($meta_data as $each_meta_data) {		
			$key = $each_meta_data->meta_key;
			$datalist->$key = $each_meta_data->meta_value;
		}

		        // search results
				if (isset($vce->search_term) && !empty($vce->search_term)) {

					$pagination = false;
					$sort_by = null;		
		
					$org_search_term  = $vce->search_term;
							
					$query = "SELECT a.item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id WHERE datalist_id='" . $vce->datalist_id . "' AND b.meta_key = 'name' AND b.meta_value LIKE '%" . $org_search_term . "%' ORDER BY name";
					// $vce->dump($query);
					// exit;
		
				}  else {
					// towards the standard way
					// with role_id filter
					if (!empty($filter_by)) {
						// $vce->dump($filter_by['item_id_list']);
						$filter_by_item_id_list = $filter_by['item_id_list'];
						$query = "SELECT a.item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id AND a.item_id IN (" . $filter_by['item_id_list'] . ") WHERE datalist_id='" . $vce->datalist_id . "' AND b.meta_key = 'name' ORDER BY name";
						$pagination = false;
						$sort_by = null;
					} else {
						// the standard way
						$query = "SELECT a.item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id WHERE datalist_id='" . $vce->datalist_id . "' AND b.meta_key = 'name' ORDER BY name LIMIT " . $pagination_length . " OFFSET " . $pagination_offset;
						//    $vce->dump($query);
					}
		
				}

		// $query = "SELECT a.item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id WHERE datalist_id='" . $vce->datalist_id . "' AND b.meta_key = 'name' ORDER BY name LIMIT " . $pagination_length . " OFFSET " . $pagination_offset;
		$options = $vce->db->get_data_object($query);



		}





			$input = array(
				'type' => 'text',
				'name' => 'name',
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);

			$manageOrgsInput = $vce->content->create_input($input,'Name of New Organization');

		// load hooks
		if (isset($vce->site->hooks['titleBar'])) {
			foreach ($vce->site->hooks['titleBar'] as $hook) {
				$title = call_user_func($hook, 'Manage Organizations & Groups, V2', 'manage-organizations');
			}
		}

		$vce->content->add('title', $title);

		
		$content .= <<<EOF
<span class="manage-orgs"></span>
<div class="label-message instructions">This utility allows for the administration of registered organizations and groups. Each administrator sees the organizations or groups
for which they are personally responsible.</div>
EOF;

$content .= <<<EOF
	$pagination_markup
EOF;


if ($vce->datalist_id == $organization_list_datalist_id) {
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
			
			$dossier_for_add = $vce->user->encryption(json_encode(array('type' => 'Pbc_Manageorganizations2','procedure' => 'add','datalist_id' => $vce->datalist_id)),$vce->user->session_vector);		

			if (isset($parent_name)) {
			
				$datalist_name .= ' (' . $parent_name[0]->meta_value . ')';
			
			}

$value = (isset($value)?$value:0);



	/* start filtering */

	// the instructions to pass through the form
	$dossier = array(
		'type' => 'Pbc_Manageorganizations2',
		'procedure' => 'filter',
	);

	// add dossier, which is an encrypted json object of details uses in the form
	$dossier_for_filter = $vce->generate_dossier($dossier);

	$accordion_content = !empty($filter_by) ? 'true' : 'false';
	// $clickbar_title = !empty($filter_by) ? 'clickbar-title' : 'clickbar-title clickbar-closed';



// Filter  input

 $options_array = array(
	0 => array(
		'name' => '',
		'value' => '',
	),
	1 => array(
		'name' => 'Orgs without users',
		'value' => 'no_users_in_org',
	),
	2 => array(
		'name' => 'Orgs without active users',
		'value' => 'no_active_users_in_org',
	),
	3 => array(
		'name' => 'Orgs with duplicate names',
		'value' => 'duplicate_names',
	),
	4 => array(
		'name' => 'Show Batch Delete Form',
		'value' => 'batch_delete_form',
	),

 );

$input = array(
 'type' => 'select',
 'name' => 'filter_term',
 'required' => 'false',
 'data' => array(
	 'class' => 'filter-form',
	 'tag' => 'required',
 ),
 'options' => $options_array
);

$filter_input = $vce->content->create_input($input,'Choose a filter','Enter a Filter Option');
$filter_content = <<<EOF
 $filter_input
 <button id="manage-users__filter-btn" class="button__secondary filter-form-submit link-button" dossier="$dossier_for_filter" inputtypes="$inputtypes" action="$vce->input_path" pagination="1">Filter</button>
 <button id="manage-users__clear-filter-btn" class="button__secondary link-button cancel-button">Clear Filter</button>
EOF;

 $filterAccordion = (isset($filterAccordion) ? $filterAccordion : '');
// create accordion box
$filterAccordion .= $vce->content->accordion('Filter', $filter_content, false);


		 /* end filtering */


//add form for search
$dossier_for_search = $vce->user->encryption(json_encode(array('type' => 'Pbc_Manageorganizations2','procedure' => 'search','datalist_id' => $vce->datalist_id)),$vce->user->session_vector);		

$input = array(
	'type' => 'text',
	'name' => 'search',
	'data' => array (
		'autocapitalize' => 'none',
		'tag' => 'required',
	)
);

$searchOrgsInput = $vce->content->create_input($input,'Search Term');


	$active_filter = (isset($vce->active_filter)) ? 'Active Filter: ' . $vce->active_filter : NULL;


$content .= <<<EOF
<h2>Search for an Organization</h2>
<form id="search-id" class="asynchronous-form search-org-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_search">
$searchOrgsInput
<input class="button__primary" type="submit" value="Search">
</form>
<div>$filterAccordion $active_filter</div>
<hr>
EOF;



// add form for adding org
$dossier_for_add = $vce->user->encryption(json_encode(array('type' => 'Pbc_Manageorganizations2','procedure' => 'add','datalist_id' => $vce->datalist_id)),$vce->user->session_vector);		
$value = 0;
$number_of_orgs_shown = count($options);


$content .= <<<EOF
<h2>Create a new organization</h2>
<form id="add-id" class="asynchronous-form add-org-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_add">
$manageOrgsInput
<input type="hidden" name="sequence" value="$value" >
<input class="button__primary" type="submit" value="Create">
</form>
<hr>
Organizations ($number_of_orgs_shown shown):
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
				$dossier_for_delete_sub = $vce->user->encryption(json_encode(array('type' => 'Pbc_Manageorganizations2','procedure' => 'delete','item_id' => $each_option->item_id,'datalist_id' => $each_option->datalist_id)),$vce->user->session_vector);		

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

				if ($vce->datalist_id == $organization_list_datalist_id) {
					$filter_by = array('organization' => $each_option->item_id);
				} else {
					$filter_by = array('group' => $each_option->item_id);
				}
 
				$users_in_org = (!empty($filter_by) ? pbc_utilities::filter_users($filter_by, $vce) : '');
				$users_in_org = trim($users_in_org, '|');
				$users_in_org = array('user_ids'=>$users_in_org);
				$site_users_per_org = $vce->user->get_users($users_in_org);

				// $vce->dump($site_users_per_org);
				$users_to_display = NULL;
				if (is_array($site_users_per_org)) {
					foreach ($site_users_per_org as $u) {
						if (isset($u->first_name) && isset($u->last_name)) {
							$users_to_display.= '<option value="'. $u->email .'">' . $u->first_name . ' ' . $u->last_name  . ' (' . $u->role_name . ', id: ' . $u->user_id . ')</option>';
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
						$dossier_for_edit_children = $vce->user->encryption(json_encode(array('type' => 'Pbc_Manageorganizations2','procedure' => 'edit','item_id' => $each_option->item_id, 'datalist_id' => $child->datalist_id, 'org_name' => $each_option->name)),$vce->user->session_vector);		

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

	if (isset($vce->active_filter) && $vce->active_filter != 'duplicate_names') {
		//add form for batch delete
		$dossier_for_batch_delete = $vce->user->encryption(json_encode(array('type' => 'Pbc_Manageorganizations2','procedure' => 'batch_delete','datalist_id' => $vce->datalist_id)),$vce->user->session_vector);		

		$input = array(
			'type' => 'text',
			'name' => 'batch_delete',
			'data' => array (
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$batchDeleteOrgsInput = $vce->content->create_input($input,'Batch Delete');


		$content .= <<<EOF
		<div>
		<h2>Batch Delete Organizations</h2>
		<div class="wordwrap">$filter_by_item_id_list</div>
		<form id="batch-delete-id" class="delete-form asynchronous-form batch-delete-org-form" method="post" action="$vce->input_path">
		<input type="hidden" name="dossier" value="$dossier_for_batch_delete">
		$batchDeleteOrgsInput
		<input class="button__primary" type="submit" value="Batch Delete">
		</form>
		<hr>
		</div>
EOF;
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
		$vce->log($input);
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




	// get all users in jurisdiction 
	public function find_users_in_jurisdiction($user, $vce, $get_user_metadata = false,  $users_filter = NULL) {
		// $vce->dump($user->role_hierarchy);
		// $user->role_hierarchy = 3;
		if (isset($users_filter)) {
			$users_info = array('roles' => $users_filter);
		} else {
			$users_info = array('roles' => 'all');
		}
		switch ($user->role_hierarchy) {
			case 1:
			case 2:
				// get all users
				$all_users = $vce->user->get_users($users_info);
				// foreach ($all_users as $key=>$value) {
				// 	if (!isset($value->organization) || $value->organization != $vce->user->organization) {
				// 		unset($all_users[$key]);
				// 	}
				// }
				break;
			case 3:
			//get users in same organization
				$users_info = array('roles' => 'all');
				$all_users = $vce->user->get_users($users_info);
				foreach ($all_users as $key=>$value) {

					if (!isset($value->organization) || $value->organization != $vce->user->organization) {
						unset($all_users[$key]);
					}
				}
				break;
			case 4:
			// get users in same group
				$users_info = array('roles' => 'all');
				$all_users = $vce->user->get_users($users_info);
				foreach ($all_users as $key=>$value) {
					if (!isset($value->group) || $value->group != $vce->user->group) {
						unset($all_users[$key]);
					}
				}
				break;
			// no other users
			case 5:
				$all_users = array();
				break;
			// no other users
			case 6:
				$all_users = array();
				break;
			// no other users
			default:
				$all_users = array();
		}

		$test_users = array();
		if (!isset($vce->site->testusers) || $vce->site->testusers != 'on') {
			$query = "SELECT user_id FROM " . TABLE_PREFIX . "users_meta where meta_key='tester' and meta_value='TRUE'";
			// $vce->log($query);
			$data = $vce->db->get_data_object($query);
			foreach($data as $this_data) {
				// $vce->log($this_data);
				$test_users[] = $this_data->user_id;
			}
		}
		foreach ($all_users as $k=>$v) {
			if (in_array($v->user_id, $test_users)) {
				unset($all_users[$k]);
			}
		}

		// return user object array
		if ($get_user_metadata == true) {
			return $all_users;
		}

		// create comma-delineated list of users
		$user_list = array();
		foreach ($all_users as $this_user) {
			$user_list[] = $this_user->user_id;
		}
		if (empty($user_list)) {
			$user_list[] = $user->user_id;
		}
		$user_list = implode(',', $user_list);

		return $user_list;
	}

	/**
	* pagination of organizations
	*/
	public function pagination($input) {

		// add attributes to page object for next page load using session
		global $vce;
		// $vce->log($input);
		
		$pagination_current = filter_var($input['pagination_current'], FILTER_SANITIZE_NUMBER_INT);
		
		if ($pagination_current < 1) {
			$pagination_current = 1;
		}
		
		if (!empty($input['user_search_results'])) {
			$vce->site->add_attributes('user_search_results',$input['user_search_results']);
		}
		if (!empty($input['cycle_search_results'])) {
			$vce->site->add_attributes('cycle_search_results',$input['cycle_search_results']);
		}
		if (!empty($input['search_value'])) {
			$vce->site->add_attributes('search_value',$input['search_value']);
		}
		if (!empty($input['active_filter'])) {
			$vce->site->add_attributes('active_filter',$input['active_filter']);
		}
		$vce->site->add_attributes('sort_by',$input['sort_by']);
		$vce->site->add_attributes('sort_direction',$input['sort_direction']);
		$vce->site->add_attributes('pagination_current',$pagination_current);

		
		echo json_encode(array('response' => 'success','message' => 'pagination'));
		return;
	
	}
		


    /**
     * Filter
     */
    public function filter($input) {

        global $vce;

		// take search terms and find organizations with those attributes,
		// create distinct array of org item id's
		// return array as comma-delineated list
		$list_of_org_ids = array();
        foreach ($input as $key => $value) {
            if (strpos($key, 'filter_by_') !== FALSE) {
				$vce->site->add_attributes('active_filter', $value);
				//get datalist id
				if (!isset($vce->datalist_id)) {
					$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_key = 'name' AND meta_value='organization'";
					$org_info = $vce->db->get_data_object($query);	
					$vce->datalist_id = $org_info[0]->datalist_id;
				}

				$filters = explode(',', $value);
				foreach ($filters as $k => $v) {

					if ($v == 'no_users_in_org') {
						$active_orgs_list = array();
						$all_users = $this->find_users_in_jurisdiction($vce->user, $vce, $get_user_metadata = true);
						foreach ($all_users as $kk => $vv) {
							if ($vv->organization != NULL) {
								$active_orgs_list[] = $vv->organization;
							}
						}
						$active_orgs_list = implode(',', $active_orgs_list);
// $vce->log($active_orgs_list);
						$query = "SELECT a.item_id AS item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id AND a.item_id NOT IN (" . $active_orgs_list . ") WHERE datalist_id='" . $vce->datalist_id . "' AND b.meta_key = 'name' ORDER BY name";
// $vce->log($query);						
						$orgs = $vce->db->get_data_object($query);
						$org_id_list = array();
						foreach ($orgs as $this_org){
							$org_id_list[] = $this_org->item_id;
						}
						$org_id_list = implode(',', $org_id_list);
						$vce->site->add_attributes('filter_by_item_id_list', $org_id_list);
					}


					if ($v == 'no_active_users_in_org') {

						
						$query = "SELECT DISTINCT user_id FROM " . TABLE_PREFIX . "analytics";
						$users = $vce->db->get_data_object($query);
						$users_id_list = array();
						foreach ($users as $this_user_id){
							$users_id_list[] = $this_user_id->user_id;
						}
// $vce->log($users_id_list);
						$query = "SELECT DISTINCT b.meta_value FROM vce_components_meta AS a JOIN vce_components_meta AS b ON a.component_id = b.component_id AND b.meta_key = 'created_by' JOIN vce_components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_at'";
						$users = $vce->db->get_data_object($query);
						// $vce->log($users);
						foreach ($users as $this_user_id){
							$users_id_list[] = $this_user_id->meta_value;
						}

						$users_id_list = array_unique($users_id_list);
// $vce->log($users_id_list);
// exit;
						$all_users = $this->find_users_in_jurisdiction($vce->user, $vce, $get_user_metadata = true);
						foreach ($all_users as $kk => $vv) {
							if (($vv->organization != NULL && in_array($vv->user_id, $users_id_list)) || $vv->created_at > 1644889172 ) {
// $vce->log($vv->organization);
								$active_orgs_list[] = $vv->organization;
							}
						}
						$active_orgs_list = array_unique($active_orgs_list);
// $vce->log(count($active_orgs_list));		
// exit;			
						$active_orgs_list = implode(',', $active_orgs_list);

						$query = "SELECT a.item_id AS item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id AND a.item_id NOT IN (" . $active_orgs_list . ") WHERE datalist_id='" . $vce->datalist_id . "' AND b.meta_key = 'name' ORDER BY name";
						$orgs = $vce->db->get_data_object($query);
						$org_id_list = array();
						foreach ($orgs as $this_org){
							$org_id_list[] = $this_org->item_id;
						}
						$org_id_list = implode(',', $org_id_list);
						$vce->site->add_attributes('filter_by_item_id_list', $org_id_list);
						
					}



					if ($v == 'duplicate_names') {
						$query = "SELECT a.item_id AS item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id WHERE datalist_id='" . $vce->datalist_id . "' AND b.meta_key = 'name' AND b.meta_value LIKE '%" . $org_search_term . "%' ORDER BY name";
						$orgs = $vce->db->get_data_object($query);
						$org_name_list = array();
						foreach ($orgs as $this_org){
							$org_name_list[] = $this_org->name;
						}
						// $vce->log($org_array);
						$org_name_list = array_diff_assoc($org_name_list, array_unique($org_name_list));
						
						$org_name_list = "'" . implode("','", $org_name_list). "'";

						$query = "SELECT a.item_id AS item_id, a.datalist_id, a.sequence, b.meta_value AS name FROM " . TABLE_PREFIX . "datalists_items AS a LEFT JOIN  " . TABLE_PREFIX . "datalists_items_meta AS b ON a.item_id = b.item_id WHERE datalist_id='" . $vce->datalist_id . "' AND b.meta_key = 'name' AND b.meta_value IN (" . $org_name_list . ") ORDER BY name";
						$orgs = $vce->db->get_data_object($query);
						$org_id_list = array();
						foreach ($orgs as $this_org){
							$org_id_list[] = $this_org->item_id;
						}
						$org_id_list = implode(',', $org_id_list);
						$vce->site->add_attributes('filter_by_item_id_list', $org_id_list);
					}
				}
            }
        }

        $vce->site->add_attributes('pagination_current', $input['pagination_current']);

        echo json_encode(array('response' => 'success', 'message' => 'Filter'));
        return;

    }


       
	/**
     * search for an organization
     */
    public  function search($input) {
    
    	// not an object at this location
        global $vce;
        
        if (!isset($input['search']) || strlen($input['search']) < 3) {
            // return a response, but without any results
            echo json_encode(array('response' => 'success', 'results' => null));
            return;
        }


        $vce->site->add_attributes('search_term', $input['search']);
	
        echo json_encode(array('response' => 'success', 'form' => 'edit'));
        return;

    }

	/**
	 * Delete single datalist
	 */
	public function delete($input) {
	
		global $vce;
		$dossier_for_delete_sub = $vce->user->encryption(json_encode(array('type' => 'ManageDatalists','procedure' => 'delete','item_id' => $each_option->item_id,'datalist_id' => $each_option->datalist_id)),$vce->user->session_vector);		

		$input['batch_delete'] = $input['item_id'];
		unset($input['item_id']);

		// call batch delete to delete org as well as to put people in that org in InactiveUsers
		$this->batch_delete($input);

		echo json_encode(array('response' => 'success','procedure' => 'delete','action' => 'reload','message' => 'Deleted'));
		return;
	
	}

	/**
     * delete list of organizations
     */
    public  function batch_delete($input) {
    
    	// not an object at this location
        global $vce;
        
        if (isset($input['batch_delete'])) {
			// set datalist_id to the original "organization" datalist by querying it
			if (!isset($vce->datalist_id)) {	
				// all orgs are items of that datalist
				$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_key = 'name' AND meta_value='organization'";
				$org_info = $vce->db->get_data_object($query);	
				$vce->datalist_id = $org_info[0]->datalist_id;
			}

			$list_to_delete = trim($input['batch_delete'], ',');
			$list_to_delete = explode(',', $list_to_delete);

			// loop through all orgs in batch_delete list
			foreach ($list_to_delete as $this_org_id) {
				// $vce->log($this_org_id);
				//set filter
				$filter_by = array('organization' => $this_org_id);
				// get list of users in org
				$users_in_org = (!empty($filter_by) ? pbc_utilities::filter_users($filter_by, $vce) : '');
				$users_in_org = trim($users_in_org, '|');
				$users_in_org = explode('|', $users_in_org);
				// $vce->log($this_org_id . ' ' . $users_in_org);

				// update user into InactiveUsers org, group and role
				// this allows users to continue to exist but remove them from the deleted org
				foreach ($users_in_org as $this_user_id) {
					$this->update_user($vce, $this_user_id);
				}


				// delete organization (done through ManageDatalists)

				// create array of installed components
				$activated_components = json_decode($vce->site->activated_components, true);

				if (isset($activated_components['ManageDatalists'])) {
					
					$meta_data = array();
					$meta_data['type'] = 'ManageDatalists';
					
					// create an instance of the class
					$this_component = $vce->page->instantiate_component($meta_data, $vce);
		
					// adding vce object as component property
					$this_component->vce = $vce;
					
					$input = array(
						'procedure' => 'delete',
						'item_id' => $this_org_id,
						'datalist_id' => $vce->datalist_id
					);

					// call to procedure method on type class
					$this_component->form_input($input);
				}
			}
			echo json_encode(array('response' => 'success', 'results' => null));
            return;
        }
	
        echo json_encode(array('response' => 'success', 'form' => 'batch-delete'));
        return;

    }

	/**
     * update user to inactive
     */
    public function update_user($vce, $user_id) {

		if (!empty($user_id) && $user_id !='') {
			//find default organization and group id's based on name
			$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'Inactive Users'";
			$result = $vce->db->get_data_object($query);
			$organization_id = $result[0]->item_id;
			
			$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'Inactive Users Default Group'";
			$result = $vce->db->get_data_object($query);
			$group_id = $result[0]->item_id;

			$input = array(
				'organization' => $organization_id,
				'group' => $group_id
			);

			// get role id of InactiveUsers
			$roles = json_decode($vce->site->roles, true);
			foreach($roles as $k => $v) {
				if ($v['role_name'] == 'InactiveUsers') {
					$role_id = $k;
				}
			}

			// change user to inactive org, group, and role
			if (isset($user_id, $role_id, $organization_id, $group_id)) {
				// $vce->log('user: ' . $user_id . ' ' . $role_id . ' ' . $organization_id . ' ' . $group_id);
				$vce->user->update_user($user_id, $input, $role_id);
			}

		}
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