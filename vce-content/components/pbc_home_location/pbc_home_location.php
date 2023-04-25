<?php

class Pbc_home_location extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Home Location',
			'description' => 'Custom version of Location, adding functionalities to Cycles list',
			'category' => 'pbc'
		);
	}	


	/**
	 * 
	 */
	public function as_content($each_component, $vce) {

		// $vce->dump(getallheaders());
		// $vce->log('home');
		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','pbccycles-style');
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui select2');


		// $lowest_role_id = key(end(json_decode($vce->site->site_roles, true)));
		$lowest_role_id = json_decode($vce->site->site_roles, true);
		$lowest_role_id = end($lowest_role_id);
		$lowest_role_id = key($lowest_role_id);
// $vce->dump($lowest_role_id);
		if ($vce->user->role_id == $lowest_role_id){
			
			$contents = <<<EOF
			Welcome! This is the Head Start Coaching Companion site. <br><br>
			You are currently registered as a New User and do not have access to these pages. <br>
			Your intake form will be processed soon, and you will then be able to access the whole site.<br>
EOF;

			$vce->content->add('main',$contents);
			$this->hide_content = true;
			return TRUE;
		}

		// get the whole assignees list (for the whole site; not just this user)
		$component_id = $each_component->component_id;
		$assignees = pbc_utilities::get_assignees($component_id, $vce);
		pbc_utilities::remove_as_resource_requester_id();

		// add the "$inputtypes" value for use in all forms
		// This is to guard against asynchronous call errors when JS is not enabled.
		$inputtypes = json_encode(array());


		// keep a json encoded version of the search results for use in forms
		$user_search_results_json = isset($vce->user_search_results) ? $vce->user_search_results : NULL;
		$cycle_search_results_json = isset($vce->cycle_search_results) ? $vce->cycle_search_results : NULL;
		$search_value = isset($vce->search_value) ? $vce->search_value : NULL;

	
// If the component we are trying to reach is a child of pbc_home_location, output only the 
// progress arrows and block the rest of the output build for this component
		foreach ($vce->page->components as $component){
			if ($component->type == "Pbccycles") {
				$contents = NULL;
				// $vce->content->add('progress_arrows', $progressArrows);
				// $users_in_jurisdiction = self::find_users_in_jurisdiction($vce->user, $vce);
				// $vce->site->add_attributes('users_in_jurisdiction',$users_in_jurisdiction);
				$vce->users_in_jurisdiction = self::find_users_in_jurisdiction($vce->user, $vce);
				return FALSE;
			} elseif ($component->type == "Pbc_home_location") {
				// $vce->content->add('progress_arrows', $progressArrows);
			}
		}

		// if this component is our requested url
		if ($each_component->component_id == $vce->requested_id) {
			$vce->title = $each_component->title;
		}

		// find out if a search has been performed
		
		$user_search_results = isset($vce->user_search_results) ? json_decode($vce->user_search_results, TRUE) : NULL;
		if (isset($user_search_results)) {
			$user_search_results_string = implode(',', $user_search_results);
		}
		$cycle_search_results = isset($vce->cycle_search_results) ? json_decode($vce->cycle_search_results) : array();
		if (isset($cycle_search_results)) {
			if (isset($user_search_results) && count($user_search_results)) {
				foreach ($assignees as $k => $v) {
					foreach ($user_search_results as $k2 => $v2) {
						if (in_array($v2, $v)) {
							// $cycle_search_results_string .= ',' . $k;
							$cycle_search_results[] = $k;
						}
					}
				}
			}
			$cycle_search_results_string = implode(',', $cycle_search_results);
		}


		// if there is a query string for the page view, use it and convert it to an object
		if (isset($vce->page->query_string) && !empty(json_decode($vce->page->query_string))) {
			$query_string = json_decode($vce->page->query_string);
		}


		// condition for completed cycle page
		if (isset($query_string->tab_target) && $query_string->tab_target == 'view-completed-cycles') {
			$cycle_status_condition = 'IN';
		} else {
			$cycle_status_condition = 'NOT IN';
		}


		//add Pagination form
		// find number of cycles
		// get all the cycles which are either created by the user, include the user, or are created by users under the administration of the user
		$user_id = $vce->user->user_id;

		$users_in_jurisdiction = self::find_users_in_jurisdiction($vce->user, $vce);

	
		$roles = json_decode($vce->site->roles, true);
// $vce->dump($vce->user->role_id);
// $vce->dump($roles[$vce->user->role_id]['role_hierarchy']);
// $vce->dump($roles[1]['role_hierarchy']);	
// $vce->dump($roles[2]['role_hierarchy']);	
// $vce->dump($roles[3]['role_hierarchy']);	
// $vce->dump($roles[4]['role_hierarchy']);	
// $vce->dump($roles[5]['role_hierarchy']);	
// $vce->dump($roles[6]['role_hierarchy']);	
// $vce->dump($roles[7]['role_hierarchy']);	



// recipe_fields access
// this refers to role-based permissions set in the recipe
// toggle visibility of Add Cycle on home page
$role_for_cycle_visibility = 5;
// $vce->dump($vce->site->site_contact_email);
if ($vce->site->site_contact_email == 'daytonra@uw.edua') {
	if (isset($each_component->sub_recipe[0]['content_access']) && in_array($vce->user->role_id, explode('|', $each_component->sub_recipe[0]['content_access']))) {
		$role_for_cycle_visibility = $roles[$vce->user->role_id]['role_hierarchy'];
	}
}

// based on the users role, which can be changed based on recipe_fields access
switch($role_for_cycle_visibility){
	case 1: 
		// filtering by user_id=0 returns all users
		$filter_by = array('user_id' => '00');
		break;
	case 2: 
		$filter_by = array('user_id' => '00');
		break;
	case 3: 
		$filter_by = array('organization' => $vce->user->organization);
		break;
	case 4: 
		$filter_by = array('group' => $vce->user->group);
		break;
	default:
		$filter_by = array();
}



		// find users in the same organization, and filter by group if not an org admin
		$users_in_org = (!empty($filter_by) ? pbc_utilities::filter_users($filter_by, $vce) : '');
		// $vce->dump($users_in_org);
		// exit;
		$users_in_org = trim($users_in_org, '|');
		$users_in_org = explode('|', $users_in_org);

		// make comma-delineated list of user-ids from this org
		if (!isset($created_by_ids)) {
			$created_by_ids = array($vce->user->user_id);
			$created_by_ids = array_merge($created_by_ids, $users_in_org);
			$created_by_ids = implode(',', $created_by_ids);
			$created_by_ids = trim($created_by_ids, ',');
			$created_by_ids_for_search = $created_by_ids;
		}
		// include search results
		$search_condition = NULL;
		if (isset($user_search_results)) {
			// $search_condition = isset($vce->search_condition) ? $vce->search_condition : NULL;
			$created_by_ids = $user_search_results_string;
			// $vce->dump($user_search_results_string);
			// $search_condition = " AND " . TABLE_PREFIX . "components.component_id IN ($user_search_results) ";
		} elseif (!isset($user_search_results) && isset($search_value)) {
			$created_by_ids = 0;
		}


		// $vce->dump($assignees);
		$cycles_with_this_user = array();
		foreach ($assignees as $k => $v) {
			if (in_array($vce->user->user_id, $v)) {
				$cycles_with_this_user[] = $k;
			}	
		}
		$cycles_with_this_user = empty($cycle_search_results) ? $cycles_with_this_user : $cycle_search_results;
		$cycles_with_this_user = implode(',', $cycles_with_this_user);
		$cycles_with_this_user = ($cycles_with_this_user != '' ? $cycles_with_this_user : '0');
		// $vce->dump($cycles_with_this_user);





//pagination
	// if a search has limited the number of cycles, create a condition which limits the number in the search for pagination count
	$cycle_search_condition = NULL;
	if (isset($cycle_search_results) && !empty($cycle_search_results)) {
		$cycle_search_condition = " AND a.component_id IN ($cycle_search_results_string) ";
	} 

	// $cycles_this_user_is_in = self::find_cycles_user_is_in($vce->user, $vce);
	
	// this query gets the total count of cycles which a user can see
	$query = "SELECT COUNT(a.component_id) AS cycles FROM vce_components AS a 
	JOIN vce_components_meta  AS b ON a.component_id = b.component_id AND b.meta_key = 'created_by' 
	WHERE a.component_id $cycle_status_condition (SELECT aa.component_id FROM " . TABLE_PREFIX . "components AS aa 
	JOIN " . TABLE_PREFIX . "components_meta AS bb ON aa.component_id = bb.component_id AND bb.meta_key = 'pbccycle_status' AND bb.meta_value = 'Complete') 
	AND (a.parent_id='" . $each_component->component_id . "') 
	AND (b.component_id in (" . $cycles_with_this_user . ") 
	OR b.meta_value IN (" . $created_by_ids . ")) 
	$search_condition
	";


	// $vce->dump($query);
	$result = $vce->db->get_data_object($query);
	$total_cycles = $result[0]->cycles;
	// $vce->dump($total_cycles);

	// this query gets the total count of completed cycles which a user can see
	$query = "SELECT COUNT(a.component_id) AS cycles FROM vce_components AS a 
	JOIN vce_components_meta  AS b ON a.component_id = b.component_id AND b.meta_key = 'created_by' 
	WHERE a.component_id IN (SELECT aa.component_id FROM " . TABLE_PREFIX . "components AS aa 
	JOIN " . TABLE_PREFIX . "components_meta AS bb ON aa.component_id = bb.component_id AND bb.meta_key = 'pbccycle_status' AND bb.meta_value = 'Complete') 
	AND (a.parent_id='" . $each_component->component_id . "') 
	AND (b.component_id in (" . $cycles_with_this_user . ") 
	OR b.meta_value IN (" . $created_by_ids . ")) 
	$search_condition
	";
	// $vce->dump($query);
	$result = $vce->db->get_data_object($query);
	$vce->total_completed_cycles = $result[0]->cycles;
	// $vce->dump($vce->total_completed_cycles);

	// show 5 cycles on the front page
	$pagination_length = 5;
	// find number of pages
	$pagination_count = isset($total_cycles) ? $total_cycles : 100;
	$number_of_pages = ceil($pagination_count / $pagination_length);
	
	$pagination_current = isset($vce->pagination_current) ? $vce->pagination_current : 1;
	$sort_by = isset($vce->sort_by) ? $vce->sort_by : 'created_at';
	$sort_direction = isset($vce->sort_direction) ? $vce->sort_direction : 'DESC';

	// prevent errors if input number is bad
	if ($pagination_current > $number_of_pages) {
		$pagination_current = $number_of_pages;
	} else if ($pagination_current < 1) {
		$pagination_current = 1;
	}


	// make sure that pagination_current is not 0
	$pagination_current = ($pagination_current < 1) ? 1 : $pagination_current;
	// find out how many cycles to offset 
	$pagination_offset = ($pagination_current != 1) ? ($pagination_length * ($pagination_current - 1)) : 0;


	// the instructions to pass through the form
	$dossier = array(
		'type' => 'Pbc_home_location',
		'procedure' => 'pagination'
	);

	// add dossier, which is an encrypted json object of details uses in the form
	$dossier_for_pagination = $vce->generate_dossier($dossier);
	
	$pagination_previous = ($pagination_current > 1) ? $pagination_current - 1 : 1;
	$pagination_next = ($pagination_current < $number_of_pages) ? $pagination_current + 1 : $number_of_pages;

	$pagination_markup = '';

// $vce->dump($sort_direction);
// $vce->dump($sort_by);
// $vce->dump($pagination_current);
// $vce->dump($pagination_previous);
// $vce->dump($pagination_next);
	// Don't show pagination if it is not needed
	if ($total_cycles > $pagination_length) {
		$pagination_markup = <<<EOF
<div class="pagination">
<div class="pagination-controls">
<button class="pagination-button-home link-button" aria-label="first page" pagination="1" sort="$sort_by" direction="$sort_direction"  search_value="$search_value" user_search_results='$user_search_results_json' cycle_search_results='$cycle_search_results_json' dossier="$dossier_for_pagination" inputtypes="$inputtypes" action="$vce->input_path">&#124;&#65124;</button>
<button class="pagination-button-home  link-button" aria-label="previous page" pagination="$pagination_previous" sort="$sort_by" direction="$sort_direction" search_value="$search_value" user_search_results="$user_search_results" cycle_search_results='$cycle_search_results_json' dossier="$dossier_for_pagination" inputtypes="$inputtypes" action="$vce->input_path">&#65124;</button>
<div class="pagination-tracker">
<label class="clear-style" for="page-input-home ">Page</label>
<input id="page-input" class="pagination-input-home no-label" type="text" name="pagination" value="$pagination_current" sort="$sort_by" direction="$sort_direction" dossier="$dossier_for_pagination" inputtypes="$inputtypes" action="$vce->input_path"> of $number_of_pages
</div>
<button class="pagination-button-home  link-button" aria-label="next page" pagination="$pagination_next" sort="$sort_by" direction="$sort_direction" search_value="$search_value" user_search_results='$user_search_results_json' cycle_search_results='$cycle_search_results_json' dossier="$dossier_for_pagination" inputtypes="$inputtypes" action="$vce->input_path">&#65125;</button>
<button class="pagination-button-home  link-button" aria-label="last page" pagination="$number_of_pages" sort="$sort_by" direction="$sort_direction" search_value="$search_value" user_search_results='$user_search_results_json' cycle_search_results='$cycle_search_results_json' dossier="$dossier_for_pagination" inputtypes="$inputtypes" action="$vce->input_path">&#65125;&#124;</button>
</div>
</div>
<p>
EOF;
	}



		// Get the id's of the cycles to be shown, limited by number and offset
		// ordering by date would be possible only if str_to_date() were used or dates would be converted table-wide into timestamps
		$offset = ' LIMIT ' . $pagination_offset .', ' . $pagination_length;
		if (isset($sort_by) && $sort_by != '') {
			$order_by = " ORDER BY a.component_id $sort_direction ";
			$order_by2 = " ORDER BY " . TABLE_PREFIX . "components.component_id $sort_direction ";
		} else {
			$order_by = " ORDER BY a.component_id DESC ";
			$order_by2 = " ORDER BY " . TABLE_PREFIX . "components.component_id DESC ";
		}

		// " ORDER BY " . TABLE_PREFIX . "components.component_id DESC ";







		// this query gets the actual component id's of the components a user can see
		// this first query only works if the $sort_by is a direct reference to the table, such as " . TABLE_PREFIX . "components.component_id
		// $query = "SELECT " . TABLE_PREFIX . "components.component_id  FROM " . TABLE_PREFIX . "components JOIN " . TABLE_PREFIX . "components_meta  ON " . TABLE_PREFIX . "components.component_id = " . TABLE_PREFIX . "components_meta.component_id AND " . TABLE_PREFIX . "components_meta.meta_key = 'created_by' WHERE (" . TABLE_PREFIX . "components.parent_id='" . $each_component->component_id . "') AND (" . TABLE_PREFIX . "components_meta.component_id in (" . $cycles_with_this_user . ") OR " . TABLE_PREFIX . "components_meta.meta_value IN (" . $created_by_ids . ")) $search_condition" . $order_by . $offset;
		// this second query introduces a second join of the components_meta table in order to allow sorting by another attribute. It did not work on "created_at" because apparently not all the cycles have the same data structure
		// $query = "SELECT a.component_id  FROM " . TABLE_PREFIX . "components AS a JOIN " . TABLE_PREFIX . "components_meta  AS b ON a.component_id = b.component_id AND b.meta_key = 'created_by' JOIN " . TABLE_PREFIX . "components_meta  AS c ON a.component_id = c.component_id AND c.meta_key = '$sort_by' WHERE (a.parent_id='" . $each_component->component_id . "') AND (b.component_id in (" . $cycles_with_this_user . ") OR b.meta_value IN (" . $created_by_ids . ")) $search_condition" . $order_by . $offset;
		$query = "SELECT a.component_id  FROM vce_components AS a 
		JOIN vce_components_meta  AS b ON a.component_id = b.component_id AND b.meta_key = 'created_by' 
		WHERE a.component_id $cycle_status_condition (SELECT aa.component_id FROM " . TABLE_PREFIX . "components AS aa 
		JOIN " . TABLE_PREFIX . "components_meta AS bb ON aa.component_id = bb.component_id AND bb.meta_key = 'pbccycle_status' AND bb.meta_value = 'Complete') 
		AND (a.parent_id='" . $each_component->component_id . "') 
		AND (b.component_id in (" . $cycles_with_this_user . ") 
		OR b.meta_value IN (" . $created_by_ids . ")) 
		$search_condition
		" . $order_by . $offset;
		// $vce->dump($query);

		$result = $vce->db->get_data_object($query);
		$select_cycles = array();
		foreach ($result as $r) {
			$select_cycles[] = $r->component_id;
		}
		$select_cycles = implode(',', $select_cycles);
		
		// GET CYCLES TO DISPLAY
		// use id's found to get all children (Cycles) of this component 
		if (empty($select_cycles)) {
			$select_cycles = "''";
		}
		$query = "SELECT " . TABLE_PREFIX . "components.*, " . TABLE_PREFIX . "components_meta.*  FROM " . TABLE_PREFIX . "components INNER JOIN " . TABLE_PREFIX . "components_meta ON " . TABLE_PREFIX . "components.component_id =  " . TABLE_PREFIX . "components_meta.component_id  WHERE " . TABLE_PREFIX . "components.parent_id='" . $each_component->component_id . "' AND " . TABLE_PREFIX . "components.component_id IN (".$select_cycles.")" . $order_by2;
		// $vce->dump($query);
		$result = $vce->db->get_data_object($query);

		// convert from vertical (key->value) to horizontal array
		$children = array();
		if (!empty($result)) {
			foreach ($result as $r){
				$children[$r->component_id]['component_id'] = $r->component_id;
				$children[$r->component_id]['url'] = $r->url;
				$children[$r->component_id][$r->meta_key] = $r->meta_value;
			}
		}
		
		// turn cycle array into object containing objects
		// Simply casting the array as an object doesn't seem to work; it converts the first dimension
		// into an object but leaves the rest as arrays.
		$children = json_decode(json_encode($children), FALSE);

		$contents = <<<EOF
<div class="main-wrapper pbc-home-wrapper">
EOF;

		$pageLinks = array(
			'homeLink' => 'class="progress-arrows progress-arrows__active progress-arrow-text progress-arrows__one"',
			'goalLink' => 'class="progress-arrows progress-arrow-text progress-arrows__two"',
			'stepsLink' => 'class="progress-arrows progress-arrow-text progress-arrows__three"',
			'foLink' => 'class="progress-arrows progress-arrow-text progress-arrows__four"'
		);

		// load hooks for progress arrows
		if (isset($vce->site->hooks['arrows'])) {
			foreach ($vce->site->hooks['arrows'] as $hook) {
				$progressArrows = call_user_func($hook, $pageLinks);
			}
		}

		$vce->content->add('progress_arrows', $progressArrows);

		// load hooks for title bar
		if (isset($vce->site->hooks['titleBar'])) {
			foreach ($vce->site->hooks['titleBar'] as $hook) {
				$title = call_user_func($hook, 'Practice-Based Coaching (PBC) Cycles', 'cycles');
			}
		}

		$vce->content->add('title', $title);

		$sidebarContent = array(
			'instructionsTitle' => '&#x25C0; FIRST',
			'instructionsText' => 'Select a PBC title or add a new PBC Cycle to get started.',
		);

		// load hooks for sidebar
		if (isset($vce->site->hooks['sidebar'])) {
			foreach ($vce->site->hooks['sidebar'] as $hook) {
				$sidebarContainer = call_user_func($hook, $sidebarContent);
			}
		}

		$vce->content->add('sidebar', $sidebarContainer);

		// the instructions to pass through the form
		$dossier = array(
			'type' => 'Pbc_home_location',
			'procedure' => 'pagination'
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_for_sort = $vce->generate_dossier($dossier);
				// although we are choosing "start_date" in this form, it actually uses ID because of the format of start_date
		$asc_selected = '';
		$desc_selected = '';
		if ($sort_direction == 'ASC') {
			$asc_selected = 'selected';
		}
		if ($sort_direction == 'DESC') {
			$desc_selected = 'selected';
		}

		// the instructions to pass through the form
		$dossier = array(
			'type' => 'Pbc_home_location',
			'procedure' => 'search'
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_search_resource = $vce->generate_dossier($dossier);



		$content_tab1 = <<<EOF
<form results="search-results" id="cycle-search" class="search-form asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
	<input type="hidden" name="dossier" value="$dossier_search_resource">
	<input type="hidden" name="created_by_ids_for_search" value="$created_by_ids_for_search">
	<input type="hidden" name="cycles_with_this_user" value="$cycles_with_this_user">
EOF;

    // search input
    $input = array(
			'type' => 'text',
			'name' => 'search',
			'value' => $search_value,
			'data' => array(
					'autocapitalize' => 'none',
					'tag' => 'required',
			)
		);
	
	$search_input = $vce->content->create_input($input,'Search Cycles');

	$content_tab1 .= <<<EOF
	$search_input
	<button class="submit-button button__primary" type="submit" value="Search">Search</button>
	<button class="link-button button__secondary cancel-button">Clear Search</button>
</form>

<form class="sort-form">
	<label class="sort-by__label" for="sort-cycles">Sort cycles by:</label>
	<select id="sort-cycles" sort_by="created_at"  class="sort-by" name="sort"  user_search_results='$user_search_results_json' cycle_search_results='$cycle_search_results_json' dossier="$dossier_for_sort" inputtypes="$inputtypes" action="$vce->input_path" pagination_current="$pagination_current">
		<option value="DESC" $desc_selected>Date Created (new to old)</option>
		<option value="ASC" $asc_selected>Date Created (old to new)</option>
	</select>
</form>
EOF;

		// check for pipeline delineated fields for member lists.
		// if there are no surrounding pipelines, call method to fix this
		$query = "SELECT * FROM " . TABLE_PREFIX . "datalists_meta WHERE meta_key = 'members' AND meta_value != '' AND meta_value NOT LIKE '|%|'";
		$result = $vce->db->get_data_object($query);
		if (!empty($result)){
			$this->correct_members_datalists($result);
		}

		// build links for list of cycles
		if (isset($children) && $children != NULL) {
			// $vce->log($children);
			// $vce->dump($children);
			if (isset($children) && count((array)$children) < 1) {
				$content_tab1 .= '<p class="no-cycles">There are no cycles to display.</p>';
			}
			foreach ($children as $child){
				// $vce->dump($child);
				$assignees[$child->component_id] = (isset($assignees[$child->component_id])) ? $assignees[$child->component_id] : array();
				$content_tab1 .= $this->generate_link_container($child, $vce, $assignees[$child->component_id],$each_component->sub_recipe[0]);
			}
		}
// $vce->dump($pagination_markup);
		$content_tab1 .= <<<EOF
$pagination_markup
EOF;

		$this->content_tab1 = $content_tab1;
		$vce->content->add('main',$contents);

		
	}


public function as_content_finish($each_component, $vce) {

	$lowest_role_id = json_decode($vce->site->site_roles, true);
	$lowest_role_id = end($lowest_role_id);
	$lowest_role_id = key($lowest_role_id);
	
	if ($vce->user->role_id == $lowest_role_id){
		return TRUE;
	}

	$contents = '';

	$content_tab1 = $this->content_tab1;

	// if there is a query string for the page view, use it and convert it to an object
	if (isset($vce->page->query_string) && !empty(json_decode($vce->page->query_string))) {
		$query_string = json_decode($vce->page->query_string);
	}

	// condition for completed cycle page
	if (isset($query_string->tab_target) && $query_string->tab_target == 'view-completed-cycles') {
		$completed_tab_visibility = TRUE;
		$uncompleted_tab_visibility = FALSE;
	} else {
		$completed_tab_visibility = FALSE;
		$uncompleted_tab_visibility = TRUE;
	}

// Create Cycle Form

$content_tab2 = $vce->content->output('add_cycle', true);

$tab_input = array (
	'tabs__container1' => array(
		'tabs' => array(
			'tab1' => array(
				'id' => 'view-cycles',
				'label' => 'View Cycles',
				'content' => $content_tab1,
				'visibility' => $uncompleted_tab_visibility,
				'reload' => TRUE,
				'tab_target' => 'view-cycles'
			)
		),
	),
);
if ($vce->total_completed_cycles > 0) {
	$tab_input['tabs__container1']['tabs']['tab2'] = array(
		'id' => 'view-cycles-completed-cycles',
		'label' => 'View Completed Cycles',
		'content' => $content_tab1,
		'visibility' => $completed_tab_visibility,
		'reload' => TRUE,
		'tab_target' => 'view-completed-cycles'
	);
}



// recipe_fields access
// this refers to role-based permissions set in the recipe
// toggle visibility of Add Cycle on home page
if (in_array($vce->user->role_id, explode('|', $each_component->sub_recipe[0]['content_create']))) {
	$tab_input['tabs__container1']['tabs']['tab3'] = array(
		'id' => 'add-cycles',
		'label' => 'Add Cycle',
		'content' => $content_tab2,
		'visibility' => '',
	);
}


$tab_content1 = Pbc_utilities::create_tab($tab_input);

if ($vce->requested_url == $each_component->url) {
	
	$contents .= <<<EOF
	$tab_content1
EOF;
}

$vce->content->add('main',$contents);

}

/**
 *
 */
public function generate_link_container($each_component, $vce, $assignees = array(), $recipe_fields_array = array()) {

	$user_ids = implode('|', $assignees);

	$participants = Pbc_utilities::userlist_to_names($user_ids, $vce);

		// for very long participant lists, put in a scrolling textarea
		if (strlen($participants) > 180) {
			$participants = '<textarea class="participants-textarea"  rows="2" cols="50">' . $participants . '</textarea>';
		}
	

		if (isset($each_component->user_access)) {	
			$user_access = json_decode($each_component->user_access);
		}
		
		if (isset($user_access)) {
			foreach (json_decode($each_component->user_access) as $key=>$value) {
// 					$vce->dump($value);
				// if another has the instructor sub_role, add them to this list
				if ($value->sub_role == 1) {
					$instructor_ids .= '|' . $key;
				}
			}
		}

		
		$pbccycle_status = isset($each_component->pbccycle_status) ? $each_component->pbccycle_status : 'In Progress';
		
		$user = $vce->user;
		// get originator info
		if (isset($each_component->originator_id)) {
			$user_info =  user::get_users(array('user_ids' => $each_component->originator_id));
		}

		if (isset($user_info[0])) {
			$originator_name = $user_info[0]->first_name . ' ' . $user_info[0]->last_name;
			// $vce->dump($originator_name);
		} else {
			$originator_name = '';
		}

		$check_complete = "";
		if (isset($each_component->pbccycle_status) && $each_component->pbccycle_status == 'Complete') {
			$link_container_class = "pbccycle-link-complete";
			$check_complete = "checked='checked'";
		} else {
			$link_container_class = "pbccycle-link";
		}

		//get cycle structure (find out what children it has to find participants in those children)
		// $cycle = $vce->page->get_children($each_component->component_id);
		// if (isset($cycle[0]->components)) {
		// 	$cycle_steps = $cycle[0]->components;
		// } else {
		// 	$cycle_steps = array();
		// }

		//add the cycle itself to the array of children, so that any participants defined in the cycle will be listed in the banner
		$cycle_steps[] = $each_component;



	$site_url = $vce->site->site_url ;
	$edit_href = $site_url . '/' . $each_component->url . '?action=edit_cycle';

	
		// the instructions to pass through the form
		$redirect_url = $vce->site->site_url;
		$dossier = array(
			'type' => $each_component->type,
			'procedure' => 'update',
			'component_id' => $each_component->component_id,
			'form_location' => __FUNCTION__,
			'created_at' => $each_component->created_at,
			'redirect_url' => $redirect_url
		);

		// generate dossier
		$dossier_for_update = $vce->generate_dossier($dossier);

	// recipe_fields access
	// this refers to role-based permissions set in the recipe
	// toggle visibility of Edit (per cycle) on home page
	$can_edit = false;
	if (in_array($vce->user->role_id, explode('|', $recipe_fields_array['content_edit'])) || $each_component->created_by == $vce->user->user_id) {
		$can_edit = true;
	}

	$form_for_complete_checkbox = null;
	if ($can_edit) {
		$form_for_complete_checkbox = <<<EOF
		<form id="form" class="asynchronous-form completed-checkbox-form" method="post" action="$vce->input_path" autocomplete="off">
		<input type="hidden" name="dossier" value="$dossier_for_update">
		<label class="pbc-cycles__completed-checkbox completed-checkbox">Complete
			<input class="pbc_cycles__completed-checkbox-input completed-checkbox-input" value="Complete" type="checkbox" name="pbccycle_status" $check_complete>
			<span class="checkmark"></span>
		</label>
	</form>
EOF;
	}
	
	$each_component->pbccycle_begins = (isset($each_component->pbccycle_begins))? $each_component->pbccycle_begins : NULL;
	$each_component->pbccycle_name = (isset($each_component->pbccycle_name))? $each_component->pbccycle_name : NULL;

	$content = <<<EOF
<div class="pbc-cycles__table-item">

<a class="$link_container_class" href="$site_url/$each_component->url" tabindex="0">
	<div class="pbc-cycles__cycle-name">$each_component->pbccycle_name</div>
	<div class="pbc-cycles__originator"><span class="pbc-cycles__bold-text">Originator: </span>$originator_name</div>
	<div class="pbc-cycles__participants"><span class="pbc-cycles__bold-text">Participants: </span>$participants</div>
	<div class="pbc-cycles__cycle-dates pbc-cycles__start-date"><span class="pbc-cycles__bold-text">Start Date: </span>$each_component->pbccycle_begins</div>
	$form_for_complete_checkbox
</a>
EOF;



	if ($can_edit) {
		$content .= <<<EOF
		<a class="cycle-edit-link" href="$edit_href" tabindex="-1">
			<button id="edit-btn" class="admin-toggle edit-toggle link-button align-right align-center"><span class="edit-btn-text">Edit</span></button>
		</a>
EOF;
}

$content .= <<<EOF
</div>
EOF;

	return $content;
}


/**
 *
 */
public function generate_cycle_info($each_component, $vce) {
	// return false;
	// $all_users = pbc_utilities::get_all_users($vce);
	// $vce->dump($all_users);
	$all_assignees = pbc_utilities::get_assignees($each_component->parent_id, $vce);
	$site_users_for_title = $vce->user->get_users($all_assignees[$each_component->component_id]);
	// $vce->dump($site_users_for_title);
	// return;
			$user_access = json_decode($each_component->user_access);
			
			$pbccycle_status = isset($each_component->pbccycle_status) ? $each_component->pbccycle_status : 'In Progress';
			
			// get originator info
			$user_info =  $vce->user->get_users(array('user_ids' => $each_component->originator_id));
	
			if (isset($user_info[0])) {
				$originator_name = $user_info[0]->first_name . ' ' . $user_info[0]->last_name;
				// $vce->dump($originator_name);
			} else {
				$originator_name = '';
			}
	
			$check_complete = "";
			if (isset($each_component->pbccycle_status) && $each_component->pbccycle_status == 'Complete') {
				$link_container_class = "pbccycle-link-complete";
				$check_complete = "checked='checked'";
			} else {
				$link_container_class = "pbccycle-link";
			}
			
		// 	//get cycle structure (find out what children it has to find participants in those children)
		// 	$cycle = $vce->page->get_children($each_component->component_id);
		// 	if (isset($cycle[0]->components)) {
		// 		$cycle_steps = $cycle[0]->components;
		// 	} else {
		// 		$cycle_steps = array();
		// 	}
			
		// 	//add the cycle itself to the array of children, so that any participants defined in the cycle will be listed in the banner
		// 	$cycle_steps[] = $each_component;
	
		// 	foreach($cycle_steps as $component) {
		// 		if ($component->type == 'Pbc_step' || $component->type == 'Pbc_focused_observation' || $component->type == 'Pbccycles') {
		// 			foreach($component as $key => $value) {
		// 				if (strpos($key, 'user_ids_') !== false) {
		// 					if(isset($dl_input)) {
		// 						$dl_input = json_decode(html_entity_decode($value));
		// 						$ids =	explode('|', $dl_input->user_ids);
		// 						foreach($ids as $id) {
		// 							if ($id !== '') {
		// 								$user_ids[] = $id;
		// 							}
		// 						}
		// 					}
		// 				}
		// 			}
		// 		}
		// 	}
		// 	if (isset($user_ids)) {
		// 		$user_ids = array_unique($user_ids);
		// 		$user_ids_formatted = implode($user_ids, ',');
		// 	}
				
				
		// //create clean participant list from user ids which are listed in all the cycle's components						
		// $participants = '';		
		// if (isset($user_ids_formatted) && $user_ids_formatted !== '') {
		// 	$user_ids_formatted = trim($user_ids_formatted, ',');
		// 	// initialize array to store users
		// 	$site_users_for_title = array();
	
		// 	$user_list = array('user_ids' => $user_ids_formatted);
		// 	$user_data = $vce->user->get_users($user_list);
	
		// 	if (isset($user_data)) {
		// 		$site_users_for_title = $user_data;
		// 	}
	
	
			$participants = null;
				
			foreach ($site_users_for_title as $u) {
				if (isset($u->first_name) && isset($u->last_name)) {
					$participants .= ', '. $u->first_name . ' ' . $u->last_name;
				}
			}
			$cycle_participants = trim($participants, ', ');
		
		$dossier_for_update = isset($dossier_for_update) ? $dossier_for_update : '';
		$site_url = $vce->site->site_url ;
		$cycle_edit_href = $site_url . '/' . $each_component->url . '?action=edit_cycle';
		// $cycle_participants = Pbc_utilities::userlist_to_names($each_component->cycle_participants, $vce);

		$cycle_content = <<<EOF
	<div class="progress-info-box__table-item">
	
		<div class="progress-info-box__cycle-name table-item__margin"><div class="progress-info-box__text">$each_component->pbccycle_name</div></div>
		<div class="progress-info-box__participants table-item__margin"><span class="pbc-cycles__bold-text">Participants:</span> $cycle_participants</div>
		<div class="progress-info-box__originator table-item__margin"><span class="pbc-cycles__bold-text">Originator:</span> $originator_name</div>
		
		<div class="progress-info-box__cycle-dates table-item__margin">
			<div class="progress-info-box__start-date"><span class="pbc-cycles__bold-text">Start Date:</span> $each_component->pbccycle_begins</div>
		</div>

		<div class="progress-info-box__edit-container">
EOF;

	$can_edit = ($each_component->created_by == $vce->user->user_id ? true : false);
	if ($can_edit) {
		$cycle_content .= <<<EOF
			<a href="$cycle_edit_href">
				<button id="edit-btn" style="display:inline" class="admin-toggle edit-toggle link-button align-right align-center"><span class="edit-btn-text">Edit</span></button>
			</a>
EOF;
	}

	$cycle_content .= <<<EOF
		</div>

		<div><a class="progress-info-box__link" href="$site_url"><div class="back-icon"></div>Return to all cycles</a></div>
	</div>
EOF;
	
		return $cycle_content;
	}

		

	// get all users in jurisdiction 
	public function find_users_in_jurisdiction($user, $vce, $get_user_metadata = false) {


		//Get user permissions from Component Configuration:
		$roles_see_all_users = explode('|',$this->configuration['see_all_users']);
		// $vce->dump($roles_see_all_users);
		$show_users_in_jurisdiction = FALSE;
		if (in_array($vce->user->role_id, $roles_see_all_users)) {
			$show_users_in_jurisdiction = TRUE;
		}
		$show_users_in_jurisdiction = TRUE;
		// $vce->dump($user->role_id);

		//set default users list to empty:
		$all_users = array();
		
		// if the component configuration allows, change default list:
		if ($show_users_in_jurisdiction == TRUE) {
			switch ($user->role_hierarchy) {
				case 1:
				case 2:
					// get all users
					$users_info = array('roles' => 'all');
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
						// $vce->dump($value->group);
						if (!isset($value->group) || $value->group != $vce->user->group) {
							unset($all_users[$key]);
						}
					}
					break;
				case 5:
					$all_users = array();
					break;
				case 6:
					$all_users = array();
					break;
				default:
					$all_users = array();
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

	


		// get all cycles a user is in
		public function find_cycles_user_is_in($user, $vce, $get_user_metadata = false) {
			

		}


	// should we get the sub component to make the page object?
	public function find_sub_components($requested_component, $vce, $components, $sub_components) {

		
		return false; 
		// return true;
		
	}


	/**
	* correct the pipeline-delineation in the db
	*/
	public function correct_members_datalists($input) {
		global $vce;

		foreach ($input as $r){
			$row_id = $r->id;
			$list = '|'.$r->meta_value.'|';
			$query = "UPDATE " . TABLE_PREFIX . "datalists_meta SET meta_value = '$list' WHERE id = '$row_id'";
			$result = $vce->db->query($query);
			// $vce->dump($r->meta_value);
		}
	}

		/**
	* pagination of cycles
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
		$vce->site->add_attributes('sort_by',$input['sort_by']);
		$vce->site->add_attributes('sort_direction',$input['sort_direction']);
		$vce->site->add_attributes('pagination_current',$pagination_current);

		
		echo json_encode(array('response' => 'success','message' => 'pagination'));
		return;
	
	}
		
	/**
	* search for users and cycle names
	*/
	public function search($input) {

		global $vce;

		$vce->log($input);

		$created_by_ids_for_search = $input['created_by_ids_for_search'];
		$cycles_with_this_user = $input['cycles_with_this_user'];

        // break search input into array based on spaces
        $search_values = explode('|', preg_replace('/\s+/', '|', $input['search']));

		if (!isset($input['search']) || strlen($input['search']) < 3) {
			// return a response, but without any results
            echo json_encode(array('response' => 'success', 'results' => null));
            return;
		}

		
		
		$query = "SELECT * FROM " . TABLE_PREFIX . "users WHERE user_id IN ($created_by_ids_for_search)";
		$users_list = $vce->db->get_data_object($query, 0);
		$all_users = array();

		foreach ($users_list as $this_user) {
			$all_users[$this_user['user_id']]['user_id'] = isset($this_user['user_id']) ? $this_user['user_id'] : NULL;
			$all_users[$this_user['user_id']]['role_id'] = isset($this_user['role_id']) ? $this_user['role_id'] : NULL;
			$all_users[$this_user['user_id']]['vector'] = isset($this_user['vector']) ? $this_user['vector'] : NULL;
			// set for search
			$match[$this_user['user_id']] = 0;
		}
		// $vce->log($all_users);
		// exit;


		$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id IN ($created_by_ids_for_search)";
        $users_meta_data = $vce->db->get_data_object($query, 0);

        foreach ($users_meta_data as $key => $value) {

            // skip a few meta_key that we don't want to allow searching in
            if ($value['meta_key'] == 'lookup' || $value['meta_key'] == 'persistent_login' || $value['meta_key'] == 'organization' || $value['meta_key'] == 'group') {
                continue;
            }

            // decrypt the values
            $all_users[$value['user_id']][$value['meta_key']] = user::decryption($value['meta_value'], $all_users[$value['user_id']]['vector']);

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
				foreach ($match as $match_user_id => $match_user_value) {
					// unset vector
					unset($all_users[$match_user_id]['vector']);
					// if there are fewer than count, then unset
					if ($match_user_value < count($search_values)) {
						// unset user info if the count is less than the total
						unset($all_users[$match_user_id]);
					}
				}
			
				// $vce->log($all_users);
				// exit;

				// test multiples
				$conditions = array();
				for ($i = 0; $i < count($search_values); $i++) {
					// create conditions for searching the titles of components
					$searchterm = $search_values[$i];
					$conditions[] = "a.meta_value LIKE '%$searchterm%'";

				}
				$conditions = implode(' AND ',$conditions);
				
				$query =  "SELECT a.component_id FROM vce_components_meta AS a 
				LEFT JOIN vce_components_meta AS b 
				ON a.component_id=b.component_id 
				JOIN vce_components_meta AS c 
				ON b.component_id=c.component_id 
				WHERE a.meta_key = 'title' 
				AND b.meta_key='type'  
				AND c.meta_key = 'created_by'
				AND a.component_id IN ($cycles_with_this_user)
				AND ($conditions)";
$vce->log($query);
				$components = $vce->db->get_data_object($query, 0);

				$cycle_ids = array();
				foreach ($components as $key => $value) {
					$cycle_ids[] = $value['component_id'];
				}

				$vce->log($cycle_ids);

				if (count($all_users) || count($cycle_ids)) {
					if (count($all_users)) {
						$user_keys = array_keys($all_users);
						$vce->site->add_attributes('user_search_results', json_encode($user_keys));
						// $vce->log($user_keys);
					} else {
						$vce->site->add_attributes('user_search_results', null);
					}
					if (count($cycle_ids)) {
						$vce->site->add_attributes('cycle_search_results', json_encode($cycle_ids));
						// $vce->log($cycle_ids);
					} else {
						$vce->site->add_attributes('cycle_search_results', null);
					}
					$vce->site->add_attributes('search_value', $input['search']);
					
					echo json_encode(array('response' => 'success', 'form' => 'search'));
					return;
				}

// $vce->log('error');
		$vce->site->add_attributes('search_value', $input['search']);
		$vce->site->add_attributes('user_search_results', null);
		$vce->site->add_attributes('cycle_search_results', NULL);
		
		echo json_encode(array('response' => 'error', 'form' => 'search', 'message' => 'No results found.'));
		return;
	
	}



	/*
 add config info for this component
*/
public function component_configuration() {
	global $vce;
	$content = NULL;



	// who can see cycles in list?
	//
	$elements = null;
	$elements .= '<div>These roles can see cycles created by users in their jurisdiction:</div>';
	$input = array(
		'type' => 'checkbox',
		'name' => 'see_all_users',
		'selected' => (isset($this->configuration['see_all_users']) ? explode('|', $this->configuration['see_all_users']) : null),
		'flags' => array(
			'label_tag_wrap' => true
		)
	);
	// add site roles as options
	foreach (json_decode($vce->site->site_roles) as $each_role) {
		foreach ($each_role as $key=>$value) {
			$input['options'][] = array(
				'value' => $key,
				'label' => $value->role_name
			);
		}
	}
	$elements .= $vce->content->input_element($input);
	$content .= $elements;



	return $content;
}


	/**
	 *
	 */
	public function recipe_fields($recipe) {
	
		global $site;
		
		$site->get_template_names();
	
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
		$url = isset($recipe['url']) ? $recipe['url'] : null;
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
<input type="text" name="url" value="$url" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">URL</div>
<div class="label-error">Enter a URL</div>
</div>
</label>

<label>
<select name="template">
<option value=""></option>
EOF;

	foreach($site->get_template_names() as $key=>$value) {
	
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