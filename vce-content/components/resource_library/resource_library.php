<?php

class ResourceLibrary extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Resource Library',
			'description' => 'Resource Library for Materials, customized for OHSCC',
			'category' => 'state',
			'permissions' => array(
				array(
					'name' => 'taxonomy_administrator',
					'description' => 'The site roles that can edit, update and delete taxonomy'
				),
				array(
					'name' => 'resource_administrator',
					'description' => 'The site roles can edit, update and delete for the Resource Library'
				)
			)
		);
	}


	/**
	 * component has been installed, now do the following
	 */
	public function installed() {
	
		global $vce;

		$attributes = array (
		'datalist' => 'resource_library_taxonomy',
		'aspects' => array ('name' => 'Resource Library Taxonomy')
		);
		
		$vce->create_datalist($attributes);

	}
	
	
	/**
	 * component has been removed, as in deleted
	 */
	public function removed() {
	
		global $vce;
		
		$attributes = array (
		'datalist' => 'resource_library_taxonomy'
		);
		
		$vce->remove_datalist($attributes);
	
	}


	/**
	 * 
	 */
	public function preload_component() {

			$content_hook = array (
			'media_file_uploader' => 'ResourceLibrary::media_file_uploader',
			'mediatype_add' => 'ResourceLibrary::media_file_uploader'
			);

			return $content_hook;

	}


	/**
	 * 
	 */
	public static function media_file_uploader($recipe_component, $vce) {

		// get the parent component of this add media call
		$parent_of_media =isset($vce->page->components) ? end($vce->page->components) : null;
		if (isset($parent_of_media->components[0]->type) && $parent_of_media->components[0]->type == "ResourceLibrary") {
$content_media = <<<EOF
<label>
	<textarea id="description" name="description" class="textarea-input"></textarea>
	<div class="label-text">
		<div class="label-message">Description of resource to be uploaded </div>
		<div class="label-error">You Must Enter A Description</div>
		<div class="tooltip-icon">
			<div class="tooltip-content">
			Description of this resource
			</div>
		</div>
	</div>
</label>
EOF;
				
			// build select menues
			if (!isset($vce->content_media)) {
				$vce->content_media = self::taxonomy_menus();
				$content_media .= $vce->content_media;
			} else {
				$content_media .= $vce->content_media;
			}
			return $content_media;
		
		}

	}
	
	
	/**
	 * method to build select menus of taxonony, which can be reused
	 * this is what takes 9 seconds to load
	 */
	private static function taxonomy_menus($requested_id = array()) {
	// return false;
		global $vce;
	
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui select2');
		
		if (empty($requested_id)) {
			$selected_taxonomy = null;
			$selected = array();
		} else {
			$selected_taxonomy = trim($requested_id['taxonomy'],'|');
			$selected = explode('|', $selected_taxonomy);
		}

		// get datalist that is associated with this component
		$taxonomy = $vce->get_datalist(array('datalist' => 'resource_library_taxonomy'));

		$datalist_id = NULL;
		if (!is_bool(current($taxonomy))) {
			$datalist_id = current($taxonomy)['datalist_id'];
		}
		
		// get datalist that is associated with this component
		$taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $datalist_id));
		// $vce->dump($taxonomy_items);
		// return 'false';	
		
$content = <<<EOF
<input class="taxonomy-selected" type="hidden" name="taxonomy" value="|$selected_taxonomy|">
EOF;

		// anonymous function to create select options from taxonomy datalist
		$create_options = function ($taxonomy_items, $level = 0) use (&$create_options, &$content, $vce, $selected) {

			$level++;

			for ($spacer = "",$x=1;$x <= $level;$x++) {
				$spacer .= '&bullet; ';
			}

			foreach ($taxonomy_items['items'] as $item_id=>$item_info) {

				$sub_taxonomy = $vce->get_datalist(array('item_id' => $item_id));

				$sub_datalist_id = NULL;
				if (!is_bool(current($sub_taxonomy))) {
					$sub_datalist_id = current($sub_taxonomy)['datalist_id'];
				}
	
				// get datalist that is associated with this component
				$sub_taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $sub_datalist_id));

				if (!empty($sub_taxonomy_items)) {
				// disabled
					$content .= '<option value="' . $item_id . '"';
					if (in_array($item_id, $selected)) {
						$content .= ' selected';
					}
					$content .= '>' . $spacer . $item_info['category_name'] . '</option>';
					$content .= $create_options($sub_taxonomy_items, $level);
				} else {
					$content .= '<option value="' . $item_id . '"';
					if (in_array($item_id, $selected)) {
						$content .= ' selected';
					}
					$content .= '>' . $spacer . $item_info['category_name'] . '</option>';
				}

			}

		};

		if (isset($taxonomy_items['items'])) {
			foreach ($taxonomy_items['items'] as $item_id=>$item_info) {
				$primary_category_title = $item_info['category_name'];

$content .= <<<EOF
<label>
	<select class="select-taxonomy ignore-input" multiple="multiple">
EOF;

				$sub_taxonomy = $vce->get_datalist(array('item_id' => $item_id));

				$sub_datalist_id = NULL;
				if (!is_bool(current($sub_taxonomy))) {
					$sub_datalist_id = current($sub_taxonomy)['datalist_id'];
				}
	
				// get datalist that is associated with this component
				$sub_taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $sub_datalist_id));

				if (!empty($sub_taxonomy_items)) {
				// disabled
					$content .= '<option value="' . $item_id . '"';
					if (in_array($item_id, $selected)) {
						$content .= ' selected';
					}
					$content .= '>' . $primary_category_title . '</option>';
					$content .= $create_options($sub_taxonomy_items);
				} else {
					$content .= '<option value="' . $item_id . '"';
					if (in_array($item_id, $selected)) {
						$content .= ' selected';
					}
					$content .= '>' . $primary_category_title . '</option>';
				}

$content .= <<<EOF
	</select>
	<div class="label-text">
		<div class="label-message">$primary_category_title</div>
		<div class="label-error">error</div>
		<div class="tooltip-icon">
			<div class="tooltip-content">
				Categories
			</div>
		</div>
	</div>
</label>
EOF;
		
			}
		}
		
		return $content;
	
	}
	

	/**
	 * prevent display of sub components by default
	 */
	public function find_sub_components($requested_component, $vce, $components, $sub_components) {
		return false;
	}
	
	
	/**
	 *
	 */
	public function build_sub_components($each_component, $vce) {
		return false;
	}


	/**
	 * allow the creation of sub components within recipe
	 */
	public function allow_sub_components($each_component, $vce) {
		// return false;
		if ($vce->check_permissions('resource_administrator')) {
		
			// if a query string has been added, don't show
			if (isset($vce->query_string->resource_id) || isset($vce->resource_id)) {
				return false;
			}
			
			return true;
			
		}
		
		// add permissions here
		return false;
	}


	/**
	 * 
	 */
	public function as_content($each_component, $vce) {
// $vce->dump($vce->as_resource_requester_id);
// return false;

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/display.js', 'jquery-ui tablesorter');
		$vce->site->add_script(dirname(__FILE__) . '/js/taxonomy.js', 'jquery-ui');
		$vce->site->add_script(dirname(__FILE__) . '/js/slow_loader.js', 'slow-loader');
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui select2');
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','resource-library-style');
		$content = NULL;

		// Edit a resource
		if (isset($vce->resource_id) && $vce->check_permissions('resource_administrator')) {
			self::manage_resource($each_component, $vce);
			return;
		}
	
		// convert query_sting
		if (isset($vce->query_string)) {
			$vce->query_string = json_decode($vce->query_string);
		}
		
		// View an individual resource
		if (isset($vce->query_string->resource_id)) {
			self::view_resource($each_component, $vce);
			return;
		}

		// get datalist that is associated with this component
		$taxonomy = $vce->get_datalist(array('datalist' => 'resource_library_taxonomy'));

		$datalist_id = NULL;
		if (!is_bool(current($taxonomy))) {
			$datalist_id = current($taxonomy)['datalist_id'];
		}
		
		// error message
		if (empty($taxonomy)) {
		
			$attributes = array (
			'datalist' => 'resource_library_taxonomy',
			'aspects' => array ('name' => 'Resource Library Taxonomy')
			);
		
			$datalist_id = $vce->create_datalist($attributes);
		
			$vce->content->add('main','<div class="form-message form-error">An error occured when attempting to find resource_library_taxonomy datalist</div>');
		}
	

		// dossier for invite
		$dossier = array(
		'type' => 'ResourceLibrary',
		'procedure' => 'add_category',
		'datalist_id' => $datalist_id
		);

		// generate dossier
		$dossier_primary_category = $vce->generate_dossier($dossier);

		// category name input
		$input = array(
			'type' => 'text',
			'name' => 'category_name',
			'required' => 'true',
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$category_name_input = $vce->content->create_input($input,'Category Name');


		// add permissions
		// taxonomy_administration
		if ($vce->check_permissions('taxonomy_administrator')) {
			self::manage_taxonomy($each_component, $vce);
			$categoryContent = <<<EOF
<div class="add-resource-category-container">
<form id="add-category" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_primary_category">
$category_name_input
</form>
EOF;

// if this is during initial page load, display loading div
// if this is from an ajax call, get the output from build_taxonomy and insert it. 
// 		if ($load != false) {
// 			$categoryContent .= self::build_taxonomy($taxonomy_items, $vce);
// 			// echo $content;
// 			// return;
// 		} else {

// 					// dossier for slow loader
// 		$dossier = array(
// 			'type' => 'ResourceLibrary',
// 			'procedure' => 'build_taxonomy_custom'
// 			);
	
// 			// generate dossier
// 			$dossier_for_build_taxonomy = $vce->generate_dossier($dossier);


			
// 			//$loading_gif = $vce->site->site_url.'/vce-content/components/resource_library/images/loading_38.gif';
// $taxonomy_items = 'unqueried';
// $page_object =  json_encode($vce->page);
// $inputtypes = json_encode(array());
// $categoryContent .= <<<EOF
// <script>
// var taxonomy_items = '$taxonomy_items';
// </script>
// <div id="slow_content_loader2" dossier="$dossier_for_build_taxonomy" inputtypes="$inputtypes" action="$vce->input_path"></div>
// EOF;

// 		}

// 		// dossier for update
// 		$dossier = array(
// 		'type' => 'ResourceLibrary',
// 		'procedure' => 'update_sequence',
// 		'datalist_id' => $datalist_id
// 		);

// 		// generate dossier
// 		$dossier_update_sequence = $vce->generate_dossier($dossier);

// $categoryContent .= <<<EOF
// 	<form id="update-categories" method="post" action="$vce->input_path" autocomplete="off">
// 		<input type="hidden" name="dossier" value="$dossier_update_sequence">
// 		<input type="submit" value="Update">
// 		<div class="link-button cancel-button">Cancel</div>
// 	</form>
// </div>
// EOF;

// 		// create accordion box
// 		$categoryAccordion = $vce->content->accordion('Manage Taxonomy', $categoryContent, false, false, 'manage_taxonomy');
// 		$content .= $categoryAccordion;
		}


		if (isset($vce->query_string->search_value)) {
			$vce->search_value = $vce->query_string->search_value;
		}



		// $search_results = json_decode($vce->search_results, TRUE);
		$this_url = $vce->site->site_url . '/' . $vce->requested_url;
		// dossier for search
		$dossier = array(
		'type' => 'ResourceLibrary',
		'procedure' => 'search_resources',
		'this_url' => $this_url
		);
		// generate dossier
		$dossier_search_resource = $vce->generate_dossier($dossier);

		// dossier for clear search
		$dossier = array(
		'type' => 'ResourceLibrary',
		'procedure' => 'clear_search',
		'this_url' => $this_url
		);

		// generate dossier
		$dossier_clear_search = $vce->generate_dossier($dossier);

		$input_value = isset($vce->search_value) ? $vce->search_value : null;
		$view_resource = $vce->site->site_url . '/' . $vce->requested_url . '?resource_id=';
		$edit_resource = $vce->site->site_url . '/' . $vce->requested_url . '?mode=edit&resource_id=';

		// load hooks
		if (isset($vce->site->hooks['titleBar'])) {
			foreach ($vce->site->hooks['titleBar'] as $hook) {
				$title = call_user_func($hook, 'Resource Library', 'resource_library');
			}
		}



		$vce->content->add('title', $title);
		$empty_json_object = json_encode(array());
		
		// search form
$content .= <<<EOF
<div class="welcome_text">
	This is a library of videos and reference documents that support effective teaching practices. 
</div>

<form id="search-resources" class="asynchronous-form" results="search-results" method="post" action="$vce->input_path" autocomplete="off">
	<input type="hidden" name="dossier" value="$dossier_search_resource">
	<input type="hidden" name="inputtypes" value="$empty_json_object">
	<input type="hidden" name="this_url" value="$this_url">
EOF;



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
	
	$search_input = $vce->content->create_input($input,'Search');
	$reload_url = $vce->site->site_url . '/' . $vce->requested_url;

$content .= <<<EOF
$search_input
	<button class="submit-button button__primary" type="submit" value="Search">Search</button>
	<button class="link-button button__secondary clear-button" reload_url="$reload_url" dossier="$dossier_clear_search" action="$vce->input_path">Clear Search</button>
</form>
EOF;


// $content .= '<div style="background:#ffc;">';

		// despite the name, this appears only if a search term is opened, and lists all relevant resources
		$content .= self::display_search_results($each_component, $vce);
		
// $content .= '</div>';

$content .= <<<EOF
<div id="asynchronous-content" style="display:none;">
<table class="tablesorter">
<thead>
<tr>
<th class="file-type">File Type</th>
<th>Title</th>
EOF;
		 

	// OHSCC:  addition of hook: as_resource_library_view
		 if (isset($vce->site->hooks['template_resource_library_view'])) {
			foreach($vce->site->hooks['template_resource_library_view'] as $hook) {
				$content .= call_user_func($hook, $vce);
			}
		}
		$resource_associate_button = $vce->content->output('associate_resource', true);


if ($resource_associate_button != '') {
	$content .= <<<EOF
	<th class="table-icon">Add</th>
EOF;
}

$content .= <<<EOF
</tr>
</thead>
<tr class="display-resources">
<td><div class="{media_type}-icon media_icon"></div></td>
<td>
	<a href="$view_resource{component_id}">{title}</a>
	<div class="description">{description}</div>

EOF;

if ($vce->check_permissions('resource_administrator')) {
	$content .= <<<EOF

	<a class="resource-edit-link" href="$edit_resource{edit_id}" tabindex="-1">
		<button id="edit-btn" class="admin-toggle edit-toggle link-button align-right align-center"><span class="edit-btn-text">Edit</span></button>
	</a>
	
EOF;
}



$content .= <<<EOF
</td>
$resource_associate_button
</tr>
</table>
</div>
EOF;

		
		// dossier for update
		$dossier = array(
		'type' => 'ResourceLibrary',
		'procedure' => 'category_display'
		);

		// generate dossier
		$dossier_category_display = $vce->generate_dossier($dossier);


		// dossier for dossier_for_resource_library_slow_content_loader
		$dossier = array(
			'type' => 'ResourceLibrary',
			'procedure' => 'slow_content_load_resource_library',
			);
	
			// generate dossier
			$dossier_for_resource_library_slow_content_loader = $vce->generate_dossier($dossier);
			$inputtypes = json_encode(array());

		
$content .= <<<EOF
<div class="resource-container">
	<div class="resource-library" dossier="$dossier_category_display" action="$vce->input_path" inputtypes="$inputtypes">
		<div class="above-level-1">View Resources by Category<div class="arrow"></div>
	</div>
EOF;

// slow content goes here
// slow_content_load_resource_library();
$inputtypes = json_encode(array());
$content .= <<<EOF
	<div id="slow_content_loader" dossier="$dossier_for_resource_library_slow_content_loader" inputtypes="$inputtypes" action="$vce->input_path"></div>
EOF;

$content .= <<<EOF
<div class="below-level-1"></div>
</div>
<div class="resource-results" role="region"></div>
EOF;

		$vce->content->add('postmain',$content);
	
	}


	/**
	 * This creates the list of categories (the taxonomy)
	 * This is called by "slow_loader.js" when the page is loaded, and the resulting contents are 
	 * put into a div called slow_loader. The file "display.js" handles the onClick actions happening to the divs defined here.
	 */
	public function slow_content_load_resource_library() {
		global $vce;

		$content = '';	
		// get datalist that is associated with this component
		$taxonomy = $vce->get_datalist(array('datalist' => 'resource_library_taxonomy'));

		$datalist_id = NULL;
		if (!is_bool(current($taxonomy))) {
			$datalist_id = current($taxonomy)['datalist_id'];
		}
		// get datalist that is associated with this component
		$taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $datalist_id));

		// anon fuction
		$build_hierarchy = function($taxonomy_items, $counter = 0) use (&$build_hierarchy, $vce, &$content) {

			$counter++;
		
			if (!empty($taxonomy_items)) {

				foreach ($taxonomy_items as $key=>$value) {
					$item_id = $value['item_id'];
					$category_name = $value['category_name'];
					// OHSCC change: we don't want to see all the categories listed, so these
					// categories are left out of the visible taxonomy on the page
					if ($category_name == 'Default' || $category_name == 'Teaching Domains' || $category_name == 'Uncategorized Videos'){
						continue;
					}
		
					// get datalist that is associated with this component
					$taxonomy = $vce->get_datalist(array('item_id' => $item_id));
				
					$datalist_id = NULL;
					if (!is_bool(current($taxonomy))) {
						$datalist_id = current($taxonomy)['datalist_id'];
					}


					// get datalist that is associated with this component
					
					$taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $datalist_id));

					// if there are child categories display an arrow next to the name
					$arrow = !empty($taxonomy_items['items']) ? '<div class="arrow"></div>' : '';

					$content .= <<<EOF
<div class="resource-library-category" item_id="$item_id">
	<button class="level-title level-$counter-category" item_id="$item_id" role="button" aria-expanded="false" aria-controls="$item_id-content" id="$item_id">$category_name$arrow</button>

EOF;

					if (!empty($taxonomy_items['items'])) {
					// this takes 4 seconds
						$build_hierarchy($taxonomy_items['items'],$counter);
					}

					$content .= <<<EOF
</div>
EOF;
				
				}
			}
		};
		$build_hierarchy($taxonomy_items['items']);
		echo $content;
		return;
	}

	/**
	 * paginated view of all resources
	 */
	private function display_search_results($each_component, $vce) {
	
		// create link if no query string
		if (!isset($vce->search_results)) {
	
// 			$view_resource = $vce->site->site_url . '/' . $vce->requested_url . '?limit=20&pagination=1';

// $content = <<<EOF
// <a href="$view_resource" class="clickbar-title clickbar-closed"><span>View All Library Resources</span></a>
// EOF;
// 			$vce->content->add('postmain',$content);
	
			return;
		}
		
		$search_results_ids = '';
		if (isset($vce->search_results)) {
			$vce->site->add_attributes('search_results', $vce->search_results);
			$vce->site->add_attributes('search_value', $vce->search_value);
			$search_results = json_decode($vce->search_results, TRUE);
			$search_results_ids = array();
			foreach ($search_results as $r) {
				$search_results_ids[] = $r['component_id'];
			}
			$id_count = count($search_results_ids);
			if ($id_count > 0) {
				$search_results_ids = implode(',', $search_results_ids);
				$search_results_ids = "AND component_id IN ($search_results_ids)";
			} else {
				$search_results_ids = "AND component_id IN (0)";
			}
			$search_value = "search_value=" . $vce->search_value . "&";
		}
// $vce->dump($id_count);
	 	//    1 - 20 of 100 >
	 	// < 21 - 40 of 100 >
	 	// < 81 - 100  of 100
	 	
	 	$limit = isset($vce->query_string) ? $vce->query_string->limit : 20;
	 	$pagination = isset($vce->query_string) ? $vce->query_string->pagination : 1;
	 	$offset = ($pagination == 1) ? 0 : (($pagination - 1) * $limit) - 1;
		
		
		
		// get total count
		$query = "SELECT COUNT(*) AS counter FROM  " . TABLE_PREFIX . "components_meta WHERE meta_key='taxonomy' $search_results_ids";
		$row_counter  = $vce->db->get_data_object($query);
		$resource_count = $row_counter[0]->counter;
		$page_count = round($resource_count / $limit);
		

		// get children of current_id
		$query = "SELECT component_id FROM  " . TABLE_PREFIX . "components_meta WHERE meta_key='taxonomy' $search_results_ids ORDER BY component_id DESC LIMIT " . $limit . " OFFSET " . $offset;
		// $vce->dump($query);
		$requested_components = $vce->db->get_data_object($query);


		foreach ($requested_components as $each_component) {
			$search[] = $each_component->component_id;
		}	

		
		$components = array();
		if (!empty($search)) {

			// get meta_data
			$query = "SELECT * FROM  " . TABLE_PREFIX . "components_meta WHERE component_id IN (" . implode(',',$search) . ")";
			$meta_data = $vce->db->get_data_object($query);

		
			
			foreach ($meta_data as $each_record) {
				if (!isset($components[$each_record->component_id]['component_id'])) {
					$components[$each_record->component_id]['component_id'] = $each_record->component_id;
				}
				$components[$each_record->component_id][$each_record->meta_key] = $each_record->meta_value;
			}
		
		}

		// $vce->site->add_script(dirname(__FILE__) . '/js/display.js', 'jquery-ui tablesorter');

		
$content = <<<EOF
<table class="tablesorter results-of-search-table">
	<thead>
		<tr>
			<th class="file-type">File Type</th>
			<th>Title</th>
EOF;

		$enabled_mediatype = json_decode($vce->site->enabled_mediatype, true);
			// OHSCC:  addition of hook: as_resource_library_view
			// if (isset($vce->site->hooks['template_resource_library_view'])) {
			// 	foreach($vce->site->hooks['template_resource_library_view'] as $hook) {
			// 		$content .= call_user_func($hook, $vce);
			// 	}
			// }
		// $resource_associate_button = $vce->content->output('associate_resource', true);

		if (isset($vce->as_resource_requester_id)) {
			$content .= <<<EOF
			<th class="table-icon">Add</th>
EOF;
		}

		$content .= <<<EOF
		</tr>
	</thead>
EOF;

		if (count($components) < 1) {
			$content .= <<<EOF
	<tr class="display-resources">
		<td></td>
		<td><span>No resources match the search term.</span></td>
	</tr>
EOF;
		}

		foreach ($components as $each) {

			$view_resource = $vce->site->site_url . '/' . $vce->requested_url . '?resource_id=' . $each['component_id'];
			
			// get nice names for media types
			require_once($enabled_mediatype[$each['media_type']]);
			$media_type = $each['media_type'];
			$class_name = new $media_type;
			$component_info = $class_name->component_info();
			$media_type = isset($component_info['typename']) ? $component_info['typename'] : $each['media_type'];

			$title = $each['title'];
			if (isset($each['description'])) {
				$description = $each['description'];
			} else {
				$description = '';
			}


// $content .= <<<EOF
// <tr class="display-resources">
// 	<td><div class="$media_type-icon media_icon"</div></td>
// 	<td>
// 		<a href="$view_resource">$title</a>
// 		<div class="description">$description</div>
// 	</td>
// 	$resource_associate_button
// </tr>
// EOF;
// $vce->log("hey"."$media_type-icon");
$content .= <<<EOF
<tr class="display-resources">
	<td><div class="$media_type-icon media_icon"</div></td>
	<td>
		<a href="$view_resource">$title</a>
		<div class="description">$description</div>
	
EOF;
if ($vce->check_permissions('resource_administrator')) {

	$search_edit_resource = $vce->site->site_url . '/' . $vce->requested_url . '?mode=edit&resource_id=' . $each['component_id'];

	$content .= <<<EOF

	<a class="resource-edit-link" href="$search_edit_resource" tabindex="-1">
		<button id="edit-btn" class="admin-toggle edit-toggle link-button align-right align-center"><span class="edit-btn-text">Edit</span></button>
	</a>
	
EOF;
}
$content .= self::create_associate_button($vce, $title, $each['component_id']);
$content .= <<<EOF
	</td>
</tr>
EOF;


		}

$content .= <<<EOF
	<tr>
		<td colspan="2">
EOF;

		for ($x=1;$x<=$page_count;$x++) {

$classses = ($x == $pagination) ? 'link-button highlighted' : 'link-button';
$href = $vce->site->site_url . '/' . $vce->requested_url . '?' . $search_value . 'limit=' . $limit . '&pagination=' . $x;

$content .= <<<EOF
			<a href="$href" class="$classses">$x</a>
EOF;
		
		}
		


$content .= <<<EOF
		</td>
	</tr>
</table>
EOF;

		return $content;
		// $vce->content->add('main',$content);

	}
	
	


		/**
	 * 
	 */
	public static function create_associate_button($vce, $resource_title, $resource_id) {
		// $vce->log('template');
		if (isset($vce->as_resource_requester_id)) {
		
			$as_resource_requester_id = $vce->as_resource_requester_id;		
			$query = "SELECT meta_key, meta_value, minutia FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $vce->as_resource_requester_id . "' ORDER BY meta_key";
			$components_meta = $vce->db->get_data_object($query);
// 			
// 			$vce->site->dump($each_component);
			foreach ($components_meta as $each_meta) {
				// add title from requested id to page object base
				if ($each_meta->meta_key == "title") {
					$resource_requester_title = $each_meta->meta_value;
				}
			}
	
			$dossier = array(
			'type' => 'Pbc_step',
			'procedure' => 'create_alias',
			'parent_id' => $vce->as_resource_requester_id,
			'created_by' => $vce->user->user_id,
			'redirect_url' => $vce->redirect_url
			);
		
		
			// generate dossier
			$dossier_copy_resource = $vce->generate_dossier($dossier);
			$inputtypes = json_encode(array());



			// $vce->site->dump($each_component->title);
			$content = <<<EOF
			<td class="table-icon">
			<form class="asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_copy_resource">
			<input type="hidden" name="inputtypes" value="$inputtypes">
			<input type="hidden" class="add-button-info" title="$resource_title" name="alias_id" value="$resource_id" resource_requester_title="$resource_requester_title">
			
			<button class="plus-minus-icon">+</button><div class="menu-container"></div>
			</form>
			</td>

			
EOF;
			if (isset($vce->link_layout) && $vce->link_layout == 'inline') {

				return $content;
			
			} else {

				return $content;
				// $vce->content->add('associate_resource',$content);
			}
		}
	}



	
	/**
	 * show an individual resource
	 */
	private function view_resource($each_component, $vce) {

		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui select2');
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','resource-library-style');

		// check that resource id is a number. If it is not, then return
		if (preg_match('/^\d+$/', $vce->query_string->resource_id, $matches) !== 1) {
			$vce->content->add('main','<div class="form-message form-error">Not a valid resource id</div>');
			return false;
		}
		
		// check that this component is media contained within the resource library
		$query = "SELECT * FROM  " . TABLE_PREFIX . "components INNER JOIN " . TABLE_PREFIX . "components_meta ON " . TABLE_PREFIX . "components.component_id=" . TABLE_PREFIX . "components_meta.component_id WHERE " . TABLE_PREFIX . "components.component_id='" . $vce->query_string->resource_id . "' AND " . TABLE_PREFIX . "components_meta.meta_key='taxonomy'";
		$component_info = $vce->db->get_data_object($query);
		
		// check that resource was returned
		if (empty($component_info)) {
			$vce->content->add('main','<div class="form-message form-error">The requested resource does not exist</div>');
			return false;
		}
	
		$query = "SELECT * FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $vce->query_string->resource_id . "'";
		$component_meta = $vce->db->get_data_object($query, false);
		
		foreach ($component_meta as $meta_data) {

			if (!isset($component)) {
					// create object and add component table data
					$component = array();
					$component['component_id'] = $meta_data['component_id'];
					$component['sequence'] = 0;
					// set prevent_editing to disable the native component editing
					$component['prevent_editing'] = true;
			}

			// create a var from meta_key
			$key = $meta_data['meta_key'];

			// add meta_value
			$component[$key] = $vce->db->clean($meta_data['meta_value']);

			// adding minutia if it exists within database table
			if (!empty($meta_data['minutia'])) {
					$key .= "_minutia";
					$component[$key] = $meta_data['minutia'];
			}

	}

	$resource = $vce->page->instantiate_component($component, $vce);

	// send object within array to display_components
	$vce->page->display_components($resource);
		
		
		// get datalist that is associated with this component
		$taxonomy = $vce->get_datalist(array('datalist' => 'resource_library_taxonomy'));
	
		$datalist_id = NULL;
		if (!is_bool(current($taxonomy))) {
			$datalist_id = current($taxonomy)['datalist_id'];
		}
	
		// get datalist that is associated with this component
		$taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $datalist_id));
		

		$content = "";
		$selected = explode('|', $resource->taxonomy);

		// anonymous function to create list from taxonomy datalist
		$create_options = function ($taxonomy_items, $level = 0) use (&$create_options, &$content, $vce, $selected) {

			$level++;
			
			for ($spacer = "",$x=1;$x <= $level;$x++) {
				$spacer .= '&bullet; ';
			}
			$co_content = '';
			foreach ($taxonomy_items['items'] as $item_id=>$item_info) {
				
				$sub_taxonomy = $vce->get_datalist(array('item_id' => $item_id));

				$sub_datalist_id = NULL;
				if (!is_bool(current($sub_taxonomy))) {
					$sub_datalist_id = current($sub_taxonomy)['datalist_id'];
				}
	
				// get datalist that is associated with this component
				$sub_taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $sub_datalist_id));

				if (in_array($item_id, $selected)) {
					$co_content .= $spacer . $item_info['category_name'];
				}
	
				// recursive call	
				if (!empty($sub_taxonomy_items)) {
					$co_content .= $create_options($sub_taxonomy_items, $level);
				}

			}
			return $co_content;
		};
		
		
		$title = $resource->title;
		$description = nl2br($resource->description);
		$back = $vce->site->site_url . '/' . $vce->requested_url;

		if ($vce->check_permissions('resource_administrator') && isset($vce->query_string->mode) && $vce->query_string->mode == 'edit') {
			// resource title input
		$input = array(
			'type' => 'text',
			'name' => 'title',
			'required' => 'true',
			'value' => $title . $description,
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$resource_title_input = $vce->content->create_input($input,'Resource Title');

		// Category input
		$input = array(
			'type' => 'text',
			'name' => 'category_name',
			'required' => 'true',
			'value' => $create_options($taxonomy_items),
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$resource_category_input = $vce->content->create_input($input,'Resource Category');
		
			// dossier for update
			$dossier = array(
			'type' => 'ResourceLibrary',
			'procedure' => 'edit_resource',
			'resource_id' => $resource->component_id
			);

			// generate dossier
			$dossier_edit_resource = $vce->generate_dossier($dossier);
	
			
			$content .= <<<EOF
			<form class="asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_edit_resource">
			$resource_title_input
			$resource_category_input
EOF;
 


		if ($vce->query_string->mode == 'edit') {
			$component_id = $vce->query_string->resource_id;
		
			$query = "SELECT * FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $component_id . "'";
			$component_meta = $vce->db->get_data_object($query);
		
			$requested_id = array();

			foreach ($component_meta as $meta_values) {
				$requested_id[$meta_values->meta_key] = $meta_values->meta_value;
			}

			// build taxonomy menus
			$content .= self::taxonomy_menus($requested_id);

			$content .= <<<EOF
			<input type="submit" value="Update">
			</form>
EOF;

			}
		} else {
			$category_display = $create_options($taxonomy_items);
			$content .= <<<EOF
<div>Title: $title</div> <p>
<div>$description</div><p>
<div>Category: $category_display</div> <p>
EOF;
		}


		// load hooks
		// resource_library_view
		if (isset($vce->site->hooks['resource_library_view'])) {
			foreach($vce->site->hooks['resource_library_view'] as $hook) {
				$content .= call_user_func($hook, $resource, $vce);
			}
		}

		// OHSCC:  addition of hook: as_resource_library_view
		if (isset($vce->site->hooks['step_resource_library_view'])) {
			foreach($vce->site->hooks['step_resource_library_view'] as $hook) {
				$content .= call_user_func($hook, $resource, $vce);
			}
		}

		$resource_associate_button = $vce->content->output('associate_resource', true);

$content .= <<<EOF
$resource_associate_button
<a href="$back"><button class="button__primary">Back to Previous Page</button></a> 
EOF;

		$vce->content->add('main',$content);
	
	}

	// public function as_content_finish($each_component, $vce) {
	// 	$content = <<<EOF
	// 	$resource_associate_button
	// 	<a href="$back"><button class="button__primary">Back to Previous Page</button></a> 
	// 	EOF;
		
	// 			$vce->content->add('post_main',$content);
	// }
	/**
	 * manage taxonomy for library
	 */
	private function manage_taxonomy($each_component, $vce) {

		$vce->site->add_script(dirname(__FILE__) . '/js/taxonomy.js', 'jquery-ui');
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','resource-library-style');
		
		// get datalist that is associated with this component
		$taxonomy = $vce->get_datalist(array('datalist' => 'resource_library_taxonomy'));
		
		$datalist_id = NULL;
		if (!is_bool(current($taxonomy))) {
			$datalist_id = current($taxonomy)['datalist_id'];
		}
		
		// error message
		if (empty($taxonomy)) {
		
			$vce->content->add('main','<div class="form-message form-error">An error occured when attempting to find resource_library_taxonomy datalist</div>');
		}
	
		// get datalist that is associated with this component
		// $vce->log($datalist_id);
		$taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $datalist_id));
	
		// dossier for invite
		$dossier = array(
		'type' => 'ResourceLibrary',
		'procedure' => 'add_category',
		'datalist_id' => $datalist_id
		);

		// generate dossier
		$dossier_primary_category = $vce->generate_dossier($dossier);
		
		$clickbar_title = isset($vce->taxonomy_update) ? true : false;

		$input = array(
		'type' => 'text',
		'name' => 'category_name',
		'data' => array(
		'tag' => 'required'
		)
		);
		
		$form_input = $vce->content->form_input($input);

		$category_name = $vce->content->create_input($form_input['input'] . '<input class="inline-submit" type="submit" value="Create">','Category Name','Enter A Category Name');

$form_content = <<<EOF
<form id="add-category" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_primary_category">
$category_name 
</form>
EOF;

		$content = $vce->content->accordion('Add New First Level Category', $form_content);

		$content .= self::build_taxonomy($taxonomy_items, $vce);

		// dossier for update
		$dossier = array(
		'type' => 'ResourceLibrary',
		'procedure' => 'update_sequence',
		'datalist_id' => $datalist_id
		);

		// generate dossier
		$dossier_update_sequence = $vce->generate_dossier($dossier);

$content .= <<<EOF
<form id="update-categories" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_update_sequence">
<input type="submit" value="Update">
<button class="link-button cancel-button">Cancel</button>
</form>
EOF;

		$vce->content->add('main',$vce->content->accordion('Manage Taxonomy', $content, $clickbar_title, false));

	
	}
	
	
	
	/**
	 * feed get_datalist_items results to this recursive function
	 */
	private function build_taxonomy($taxonomy, $vce, $content = null, $counter = 0) {

		// what level are we on anyway?
		$counter++;

$content = <<<EOF
<ul class="resource-library-taxonomy cat-level-$counter connected-$counter ui-sortable" level="$counter">
EOF;

		if (isset($taxonomy['items'])) {
			foreach ($taxonomy['items'] as $item_id=>$item_info) {
		
				$category_name = $item_info['category_name'];
			
				// the instructions to pass through the form
				$dossier = array(
				'type' => 'ResourceLibrary',
				'procedure' => 'edit_category',
				'item_id' => $item_id
				);

				// generate dossier
				$dossier_for_edit = $vce->generate_dossier($dossier);
			
				// the instructions to pass through the form
				$dossier = array(
				'type' => 'ResourceLibrary',
				'procedure' => 'delete_category',
				'item_id' => $item_id
				);

				// generate dossier
				$dossier_for_delete = $vce->generate_dossier($dossier);
			
				// needed:
				// {"1505":{"datalist_id":"1505","parent_id":"0","item_id":"1955","component_id":"0","user_id":"0","sequence":"0","datalist":"sub_category_1955"}}
				$dossier = array(
				'type' => 'ResourceLibrary',
				'procedure' => 'add_category',
				'datalist_id' => $taxonomy['datalist_id'],
				'item_id' => $item_id,
				'url' => $vce->requested_url
				// 'parent_id' => ,
				// 'component_id' => ,
				// 'user_id' => ,
				// 'sequence_id' => ,
				);
// $vce->log($dossier);
				// generate dossier
				$dossier_update_category = $vce->generate_dossier($dossier);
				
				$input = array(
				'type' => 'text',
				'name' => 'category_name',
				'data' => array(
				'tag' => 'required'
				)
				);
		
				$form_input = $vce->content->form_input($input);

				$category_input = $vce->content->create_input($form_input['input'] . '<input class="inline-submit" type="submit" value="Create">','Add A New Sub Category','Enter A Sub Category');
			
$content .= <<<EOF
<li id="item_$item_id" item="$item_id" class="level-$counter-li">
<div class="li-title level-$counter-category">

<div class="sub-category-edit">&#9998;</div>

<div class="category-name">$category_name</div>

<form id="edit-category-$item_id" class="edit-category" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_edit">
<input type="text" class="small-input" name="category_name" value="$category_name">
<input type="submit" value="update">
</form>

<div class="sub-category-addition">+</div>
<div class="category-delete" dossier="$dossier_for_delete" action="$vce->input_path" title="delete">x</div>
</div>

<div class="li-contents">
<form id="add-category-$item_id" class="add-sub-category asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_update_category">

$category_input

</form>
EOF;

			$item_datalist = $vce->get_datalist(array('item_id' => $item_id));
			
			$item_datalist_id = NULL;
			if (!is_bool(current($item_datalist))) {
				$item_datalist_id = current($item_datalist)['datalist_id'];
			}
			
				$sub_taxonomy = $vce->get_datalist_items(array('datalist_id' => $item_datalist_id));
	
				if (isset($sub_taxonomy['items'])) {
					$content .= self::build_taxonomy($sub_taxonomy, $vce, $content, $counter);
				} else {

					$next = $counter + 1;

$content .= <<<EOF
<ul class="resource-library-taxonomy cat-level-$next connected-$next ui-sortable" level="$next"></ul>
EOF;

				
				}
			
$content .= <<<EOF
</div>
</li>
EOF;

			}
		}

$content .= <<<EOF
</ul>
EOF;
		
		// return taxonomy list
		return $content;
		
	}

	private function manage_taxonomyBAK($each_component, $vce, $load = TRUE) {

		$vce->site->add_script(dirname(__FILE__) . '/js/taxonomy.js', 'jquery-ui');
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','resource-library-style');
		
		// get datalist that is associated with this component
		$taxonomy = $vce->get_datalist(array('datalist' => 'resource_library_taxonomy'));
		
		$datalist_id = NULL;
		if (!is_bool(current($taxonomy))) {
			$datalist_id = current($taxonomy)['datalist_id'];
		}
		
		// error message
		if (empty($taxonomy)) {
		
			$attributes = array (
			'datalist' => 'resource_library_taxonomy',
			'aspects' => array ('name' => 'Resource Library Taxonomy')
			);
		
			$datalist_id = $vce->create_datalist($attributes);
		
			$vce->content->add('main','<div class="form-message form-error">An error occured when attempting to find resource_library_taxonomy datalist</div>');
		}
	
		// get datalist that is associated with this component
		$taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $datalist_id));
	
		// dossier for invite
		$dossier = array(
		'type' => 'ResourceLibrary',
		'procedure' => 'add_category',
		'datalist_id' => $datalist_id
		);

		// generate dossier
		$dossier_primary_category = $vce->generate_dossier($dossier);
		
		if (isset($vce->taxonomy_update)) {

			$clickbar_content = 'clickbar-content clickbar-open';
			$clickbar_title = 'clickbar-title';
		
		} else {
		
			$clickbar_content = 'clickbar-content';
			$clickbar_title = 'clickbar-title clickbar-closed';
		
		}
	
		// category name input
		$input = array(
			'type' => 'text',
			'name' => 'category_name',
			'required' => 'true',
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$category_name_input = $vce->content->create_input($input,'Create Category: Enter Name');

$categoryContent = <<<EOF
<div class="add-resource-category-container">
<form id="add-category" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_primary_category">
$category_name_input
</form>
EOF;

// if this is during initial page load, display loading div
// if this is from an ajax call, get the output from build_taxonomy and insert it. 
		if ($load != false) {
			$categoryContent .= self::build_taxonomy($taxonomy_items, $vce);
			// echo $content;
			// return;
		} else {

					// dossier for slow loader
		$dossier = array(
			'type' => 'ResourceLibrary',
			'procedure' => 'build_taxonomy_custom'
			);
	
			// generate dossier
			$dossier_for_build_taxonomy = $vce->generate_dossier($dossier);


			
			//$loading_gif = $vce->site->site_url.'/vce-content/components/resource_library/images/loading_38.gif';
$taxonomy_items = 'unqueried';
$page_object =  json_encode($vce->page);
$inputtypes = json_encode(array());
$categoryContent .= <<<EOF
<script>
var taxonomy_items = '$taxonomy_items';
</script>
<div id="slow_content_loader2" dossier="$dossier_for_build_taxonomy" inputtypes="$inputtypes" action="$vce->input_path"></div>
EOF;

		}

		// dossier for update
		$dossier = array(
		'type' => 'ResourceLibrary',
		'procedure' => 'update_sequence',
		'datalist_id' => $datalist_id
		);

		// generate dossier
		$dossier_update_sequence = $vce->generate_dossier($dossier);

$categoryContent .= <<<EOF
	<form id="update-categories" method="post" action="$vce->input_path" autocomplete="off">
		<input type="hidden" name="dossier" value="$dossier_update_sequence">
		<input type="submit" value="Update">
		<div class="link-button cancel-button">Cancel</div>
	</form>
</div>
EOF;

		// create accordion box
		$categoryAccordion = $vce->content->accordion('Manage Taxonomy', $categoryContent, false, false, 'add_category');

		$vce->content->add('main', $categoryAccordion);
	}
	
	
	
	/**
	 * feed get_datalist_items results to this recursive function
	 * this takes 9 seconds, same as the taxonomy_menus
	 */
	private function build_taxonomyBAK($taxonomy, $vce, $content = null, $counter = 0) {
		global $vce;
		// what level are we on anyway?
		$counter++;
$content = <<<EOF
<ul class="resource-library-taxonomy cat-level-$counter connected-$counter ui-sortable" level="$counter">
EOF;

		if (isset($taxonomy['items'])) {
			foreach ($taxonomy['items'] as $item_id=>$item_info) {
		
				$category_name = $item_info['category_name'];
			
				// the instructions to pass through the form
				$dossier = array(
				'type' => 'ResourceLibrary',
				'procedure' => 'edit_category',
				'item_id' => $item_id
				);

				// generate dossier
				$dossier_for_edit = $vce->generate_dossier($dossier);
			
				// the instructions to pass through the form
				$dossier = array(
				'type' => 'ResourceLibrary',
				'procedure' => 'delete_category',
				'item_id' => $item_id
				);

				// generate dossier
				$dossier_for_delete = $vce->generate_dossier($dossier);
			
				// dossier for invite
				$dossier = array(
				'type' => 'ResourceLibrary',
				'procedure' => 'add_category',
				'datalist_id' => $taxonomy['datalist_id'],
				'item_id' => $item_id
				);

				// generate dossier
				$dossier_update_category = $vce->generate_dossier($dossier);
		
$content .= <<<EOF
	<li id="item_$item_id" item="$item_id" class="level-$counter-li">
		<div class="li-title level-$counter-category">

			<div class="sub-category-edit">&#9998;</div>

			<div class="category-name">$category_name</div>

			<form id="edit-category-$item_id" class="edit-category" method="post" action="$vce->input_path" autocomplete="off">
				<input type="hidden" name="dossier" value="$dossier_for_edit">
				<input type="text" class="small-input" name="category_name" value="$category_name">
				<input type="submit" value="update">
			</form>

			<div class="sub-category-addition">+</div>
			<div class="category-delete" dossier="$dossier_for_delete" action="$vce->input_path" title="delete">x</div>
		</div>

		<div class="li-contents">
			<form id="add-category-$item_id" class="add-sub-category asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
				<input type="hidden" name="dossier" value="$dossier_update_category">
				<label>
					<input type="text" name="category_name" tag="required" autocomplete="off">
					<input class="inline-submit" type="submit" value="Create">
					<div class="label-text">
						<div class="label-message">Add A New Sub Category</div>
						<div class="label-error">Enter A Name</div>
					</div>
				</label>
			</form>
EOF;

			$item_datalist = $vce->get_datalist(array('item_id' => $item_id));
			
				$item_datalist_id = NULL;
				if (!is_bool(current($item_datalist))) {
					$item_datalist_id = current($item_datalist)['datalist_id'];
				}
			
				$sub_taxonomy = $vce->get_datalist_items(array('datalist_id' => $item_datalist_id));
				if (isset($sub_taxonomy['items'])) {
					$content .= self::build_taxonomy($sub_taxonomy, $vce, $content, $counter);
				}
			
$content .= <<<EOF
		</div>
	</li>
EOF;
// break;
			}
		}

$content .= <<<EOF
</ul>
EOF;
		
		// return taxonomy list
		return $content;
		
	}
	


	/**
	 * feed get_datalist_items results to this recursive function
	 */
	public function build_taxonomy_custom($taxonomy, $content = null, $counter = 0) {

		global $vce;

		if ($taxonomy == 'unqueried') {
				// get datalist that is associated with this component
				$taxonomy1 = $vce->get_datalist(array('datalist' => 'resource_library_taxonomy'));
		
				$datalist_id = NULL;
				if (!is_bool(current($taxonomy1))) {
					$datalist_id = current($taxonomy1)['datalist_id'];
				}
				
				
				// error message
				if (empty($taxonomy1)) {
					echo "No Taxonomy!";
				}
			
				// get datalist that is associated with this component
				$taxonomy = $vce->get_datalist_items(array('datalist_id' => $datalist_id));

			}

				// what level are we on anyway?
				$counter++;
$content = <<<EOF
<ul class="resource-library-taxonomy cat-level-$counter connected-$counter ui-sortable" level="$counter">
EOF;

		if (isset($taxonomy['items'])) {
			foreach ($taxonomy['items'] as $item_id=>$item_info) {
				$category_name = $item_info['category_name'];
			
				// the instructions to pass through the form
				$dossier = array(
				'type' => 'ResourceLibrary',
				'procedure' => 'edit_category',
				'item_id' => $item_id
				);

				// generate dossier
				$dossier_for_edit = $vce->generate_dossier($dossier);
				// the instructions to pass through the form
				$dossier = array(
				'type' => 'ResourceLibrary',
				'procedure' => 'delete_category',
				'item_id' => $item_id
				);

				// generate dossier
				$dossier_for_delete = $vce->generate_dossier($dossier);
			
				// dossier for invite
				$dossier = array(
				'type' => 'ResourceLibrary',
				'procedure' => 'add_category',
				'datalist_id' => $taxonomy['datalist_id'],
				'item_id' => $item_id
				);

				// generate dossier
				$dossier_update_category = $vce->generate_dossier($dossier);
		
$content .= <<<EOF
	<li id="item_$item_id" item="$item_id" class="level-$counter-li">
		<div class="li-title level-$counter-category">

			<div class="sub-category-edit">&#9998;</div>

			<div class="category-name">$category_name</div>

			<form id="edit-category-$item_id" class="edit-category" method="post" action="$vce->input_path" autocomplete="off">
				<input type="hidden" name="dossier" value="$dossier_for_edit">
				<input type="text" class="small-input" name="category_name" value="$category_name">
				<input type="submit" value="update">
			</form>

			<div class="sub-category-addition">+</div>
			<div class="category-delete" dossier="$dossier_for_delete" action="$vce->input_path" title="delete">x</div>
		</div>

		<div class="li-contents">
			<form id="add-category-$item_id" class="add-sub-category asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
				<input type="hidden" name="dossier" value="$dossier_update_category">
				<label>
					<input type="text" name="category_name" tag="required" autocomplete="off">
					<input class="inline-submit" type="submit" value="Create">
					<div class="label-text">
						<div class="label-message">Add A New Sub Category</div>
						<div class="label-error">Enter A Name</div>
					</div>
				</label>
			</form>
EOF;

			$item_datalist = $vce->get_datalist(array('item_id' => $item_id));
			
			$item_datalist_id = NULL;
			if (!is_bool(current($item_datalist))) {
				$item_datalist_id = current($item_datalist)['datalist_id'];
			}
				
			
				$sub_taxonomy = $vce->get_datalist_items(array('datalist_id' => $item_datalist_id));
				if (isset($sub_taxonomy['items'])) {
					$content .= self::build_taxonomy_custom($sub_taxonomy, $content, $counter);
				}
			
$content .= <<<EOF
		</div>
	</li>
EOF;
// break;
			}
		}

$content .= <<<EOF
</ul>
EOF;
		
		// return taxonomy list
		echo $content;
		return;
		
	}

			

	
	/**
	 * update or delete an existing resource library media item
	 */
	private function manage_resource($each_component, $vce) {

		if ($vce->check_permissions('resource_administrator')) {
		
			$component_id = $vce->resource_id;
		
			global $db;
		
			$query = "SELECT * FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $component_id . "'";
			$component_meta = $db->get_data_object($query);
		
			$requested_id = array();

			foreach ($component_meta as $meta_values) {
				$requested_id[$meta_values->meta_key] = $meta_values->meta_value;
			}
		
			$title = $requested_id['title'];
			$description = (isset($requested_id['description']))?$requested_id['description'] : NULL;
		
			// dossier for invite
			$dossier = array(
			'type' => 'ResourceLibrary',
			'procedure' => 'update_resource',
			'component_id' => $component_id
			);

			// generate dossier
			$dossier_update_media = $vce->generate_dossier($dossier);
		
$content = <<<EOF
<div class="clickbar-container">
	<div class="clickbar-content clickbar-open">

		<form id="edit-resource" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
			<input type="hidden" name="dossier" value="$dossier_update_media">

			<label>
				<input type="text" name="title" value="$title" tag="required" autocomplete="off">
				<div class="label-text">
					<div class="label-message">Title</div>
					<div class="label-error">Enter a Title</div>
				</div>
			</label>
			<label>
				<textarea id="description" name="description" class="textarea-input" tag="required">$description</textarea>
				<div class="label-text">
					<div class="label-message">Description of resource to be uploaded </div>
					<div class="label-error">You Must Enter A Description</div>
					<div class="tooltip-icon">
						<div class="tooltip-content">
							Description of this resource
						</div>
					</div>
				</div>
			</label>
EOF;

			// build taxonomy menus
			$content .= self::taxonomy_menus($requested_id);

$content .= <<<EOF
			<input type="submit" value="Update">
			<div class="link-button cancel-button">Cancel</div>
		</form>
EOF;

			if ($vce->check_permissions('resource_administrator')) {
			
				// dossier for delete
				$dossier = array(
				'type' => 'ResourceLibrary',
				'procedure' => 'delete_resource',
				'component_id' => $component_id
				);

				// generate dossier
				$dossier_for_delete = $vce->generate_dossier($dossier);

$content .= <<<EOF
		<form id="delete-resource" class="delete-form float-right-form asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_for_delete">
			<input type="submit" value="Delete">
		</form>
EOF;

			}

$content .= <<<EOF
	</div>
	<div class="clickbar-title"><span>Edit resource</span></div>
</div>
EOF;

			$vce->content->add('postmain',$content);
			
		}
		
		
	}
	
	
	/**
	 *
	 */
	public function add_category($input) {
	
		global $site;
		global $vce;
		// $vce->log('add category');
		// $vce->log($input);
		// get the datalist associated with this component

		$category_name = trim($input['category_name']);
		
		// check to see if item_id is already a datalist
		
		if (isset($input['item_id'])) {
		
			$attributes = array (
			'item_id' => $input['item_id']
			);
		
			$datalist =	$vce->get_datalist($attributes);
		// $vce->log($datalist);
		// exit;
			// if there is no datalist_id associated with the item_id, then make one!
			if (!empty($datalist)) {
		
				$datalist_id = NULL;
				if (!is_bool(current($datalist))) {
					$datalist_id = current($datalist)['datalist_id'];
				}

		
			} else {
				// $vce->log('creating datalist');
				$attributes = array (
				'item_id' => $input['item_id'],
				'parent_id' => $input['datalist_id'],
				'datalist' => 'sub_category_' . $input['item_id'],
				);
	 
				$datalist_id = $vce->create_datalist($attributes);

			}
		
		} else {
		
			// primary level
			$datalist_id = $input['datalist_id'];
		
		}
		
		$attributes = array (
	 	'datalist_id' => $datalist_id,
	 	'items' => array ( array ('sequence' => 0, 'category_name' => $category_name) )
	 	);
		
	 	$vce->insert_datalist_items($attributes);
	 	
		$vce->site->add_attributes('taxonomy_update','true');
		$url = $vce->site->site_url . '/' . $input['url'];
		

		echo json_encode(array('response' => 'success','procedure' => 'create','action' => 'reload', 'url'=>$url, 'message' => 'Created'));
		return;
	}


	/**
	 * 
	 */
	public function update_sequence($input) {
	
		global $vce;
		// $vce->log('update sequence');

		$base_id = $input['datalist_id'];
	
		$sort = json_decode($input['sort'], true);
		
		$update_categories = function($sort, $parent_id = 0) use (&$update_categories, $base_id, $vce) {
		
			foreach ($sort as $item_id=>$item_info) {
			
				$attributes = array (
				'item_id' => $item_id,
				'relational_data' => array('parent_id' => $parent_id)
				);

				$vce->update_datalist($attributes);

				$relational_data['sequence'] = $item_info['sequence'];
				if ($parent_id != 0) {
					// update datalist_id with value of parent_id when not a base category
					$relational_data['datalist_id'] = $parent_id;
				} else {
					$relational_data['datalist_id'] = $base_id;
				}
		
				$attributes = array (
				'item_id' => $item_id,
				'relational_data' => $relational_data
				);
			
				$vce->update_datalist_item($attributes);
				
				$current_datalist = $vce->get_datalist(array('item_id' => $item_id));
				
				$next_parent = isset(current($current_datalist)['datalist_id']) ? current($current_datalist)['datalist_id'] : $parent_id; 

				if (!empty($item_info['children'])) {
				
					$update_categories($item_info['children'], $next_parent);
				
				}
			
			}
		
		
		};
		
		
		$update_categories($sort);
		
		$vce->site->add_attributes('taxonomy_update','true');
		
		echo json_encode(array('response' => 'success','procedure' => 'update','message' => 'Updated'));
		return;

	}
	
	/**
	 * 
	 */
	public function edit_category($input) {
		
		global $vce;
		
		$category_name = trim($input['category_name']);
		
		$attributes = array (
		'item_id' => $input['item_id'],
		'meta_data' => array ( 'category_name' => $category_name )
		);
		
		$vce->update_datalist_item($attributes);
		
		echo json_encode(array('response' => 'success','procedure' => 'update','message' => 'Updated!'));
		return;

	
	}
	
	/**
	 * 
	 */
	public function delete_category($input) {
		
		global $vce;
		
		$attributes = array (
		'item_id' => $input['item_id']
		);
		
		$vce->remove_datalist($attributes);
		
		$vce->site->add_attributes('taxonomy_update','true');
	
		echo json_encode(array('response' => 'success','procedure' => 'update','message' => 'Updated'));
		return;
	
	}
	
	/**
	 * set a value and reload
	 */
	public function edit_resource($input) {
	
		global $vce;
		
		// add key value to page object on next load
		$vce->site->add_attributes('resource_id',$input['resource_id']);
		

		echo json_encode(array('response' => 'success','procedure' => 'create','action' => 'reload','message' => 'Edit'));
		return;
	
	}
	
	
	/**
	 * 
	 */
	public function update_resource($input) {
	
		global $vce;
		$component_id = $input['component_id'];
		$reload_url = $input['parent_url'];
		unset($input['type'],$input['component_id'],$input['parent_url']);
		

		$query = "SELECT meta_key FROM " . TABLE_PREFIX . "components_meta WHERE component_id = $component_id ";
		$result = $vce->db->get_data_object($query);
		$component_attributes = array();
		foreach ($result AS $k => $v) {
			$component_attributes[] = $v->meta_key;
		}
		// $vce->log($component_attributes);
		
		// $vce->log($input);
		
		foreach ($input as $key=>$value) {
			if (!in_array($key, $component_attributes)) {
				// $vce->log($key);
				$records[] = array(
					'component_id' => $component_id,
					'meta_key' => $key, 
					'meta_value' => $value,
					'minutia' => null
				);
				$vce->db->insert('components_meta', $records);
				continue;
			}
				$update = array('meta_value' => $value);
				$update_where = array('component_id' => $component_id, 'meta_key' => $key);
				$vce->db->update('components_meta', $update, $update_where);
		}
		
		echo json_encode(array('response' => 'success','procedure' => 'update','message' => 'Updated', 'url'=>$reload_url));
		return;
	
	}
	
	/**
	 * 
	 */
	public function delete_resource($input) {
	
		self::extirpate_component($input['component_id']);
	
		echo json_encode(array('response' => 'success','procedure' => 'delete','message' => 'Deleted'));
		return;
	
	}


	/**
	 *  Clear search variables
	 */
	public function clear_search($input) {

		global $vce;
		// $vce->log('clear');
		$vce->site->remove_attributes('search_value');
		$vce->site->remove_attributes('search_results');
		
		echo json_encode(array('response' => 'success', 'form' => 'edit', 'action' => $input['reload_url']));
        return;


	}

	
	/**
	 * Search for resources
	 */
	public function search_resources($input) {

		global $vce;

		// echo json_encode(array('response' => 'success', 'form' => 'edit', 'message' => json_encode('term')));
        // return;
		
		$enabled_mediatype = json_decode($vce->site->enabled_mediatype, true);
		
		$search_term = trim($input['search']);
		
		$search = array();
		
		// get meta_data associated with datalist_id
		$query = "SELECT component_id, meta_key, meta_value FROM " . TABLE_PREFIX . "components_meta WHERE meta_key IN ('title','description','media_type') AND component_id IN (SELECT component_id FROM " . TABLE_PREFIX . "components_meta WHERE meta_key IN ('title','description') AND meta_value LIKE '%" . $search_term . "%' AND component_id IN (SELECT component_id FROM " . TABLE_PREFIX . "components_meta WHERE meta_key='taxonomy'))";
		$search_results = $vce->db->get_data_object($query);
		
		if (!empty($search_results)) {
		
			foreach ($search_results as $meta_data) {
			
			// OHSCC change
				// if ($meta_data->meta_value == 'VimeoVideo') {
				// 	$meta_data->meta_value = 'Video';
				// }
				
				// get nice name for this media type
				if ($meta_data->meta_key == "media_type") {
					require_once($enabled_mediatype[$meta_data->meta_value]);
					$media_type = $meta_data->meta_value;
					$class_name = new $media_type;
					$component_info = $class_name->component_info();
					$meta_data->meta_value = isset($component_info['typename']) ? $component_info['typename'] : $meta_data->meta_value;
				}
			
				if (!isset($search[$meta_data->component_id]['component_id'])) {
					$search[$meta_data->component_id]['component_id'] = $meta_data->component_id;
				}
			
				$search[$meta_data->component_id][$meta_data->meta_key] = $meta_data->meta_value;
			
			}
		
		}

		$search_results = json_encode($search);
		$vce->site->add_attributes('search_value', $input['search']);
        $vce->site->add_attributes('search_results', $search_results);
		// $vce->log($input['this_url']);

        echo json_encode(array('response' => 'success', 'message' => 'Searching for "' . $input['search'] . '"... ',   'form' => 'edit', 'action' => $input['this_url']));
        return;

	}
	

	/**
	 * show all resources associated with a category
	 * This is what is called through the JS file "display.js" when a category is clicked on.
	 */
	public function category_display($input) {

		global $db;
		global $site;
		global $vce;

		$enabled_mediatype = json_decode($vce->site->enabled_mediatype, true);

		$item_id = $input['item_id'];
		

		$search = array();

		// get meta_data associated with datalist_id
		$query = "SELECT component_id, meta_key, meta_value FROM " . TABLE_PREFIX . "components_meta WHERE meta_key IN ('title','description','media_type') AND component_id IN (SELECT component_id FROM " . TABLE_PREFIX . "components_meta WHERE meta_key='taxonomy' AND meta_value LIKE '%|" . $item_id . "|%' AND component_id IN (SELECT component_id FROM " . TABLE_PREFIX . "components_meta WHERE meta_key='taxonomy'))";
		$search_results = $vce->db->get_data_object($query);

		if (!empty($search_results)) {
		
			foreach ($search_results as $meta_data) {

				// get nice name for this media type
				if ($meta_data->meta_key == "media_type") {
					$em = $enabled_mediatype[$meta_data->meta_value];

					// $vce->log($em);

					require_once($em);
					
					// require_once($enabled_mediatype[$meta_data->meta_value]);
					$media_type = $meta_data->meta_value;
					$class_name = new $media_type;
					$component_info = $class_name->component_info();
					$meta_data->meta_value = isset($component_info['typename']) ? $component_info['typename'] : $meta_data->meta_value;
				}
			
				if (!isset($search[$meta_data->component_id]['component_id'])) {
					$search[$meta_data->component_id]['component_id'] = $meta_data->component_id;
				}
				if (!isset($search[$meta_data->component_id]['edit_id'])) {
					$search[$meta_data->component_id]['edit_id'] = $meta_data->component_id;
				}
				if (!isset($search[$meta_data->component_id]['template_component_id'])) {
					$search[$meta_data->component_id]['template_component_id'] = $meta_data->component_id;
				}
				if (!isset($search[$meta_data->component_id]['template_title']) && $meta_data->meta_key == 'title') {
					$search[$meta_data->component_id]['template_title'] = $meta_data->meta_value;
				}
			
				$search[$meta_data->component_id][$meta_data->meta_key] = $meta_data->meta_value;
			
			}
		
		}
		echo json_encode(array('response' => 'success','procedure' => 'search','message' => json_encode($search)));
		return;

	
	}



	/**
	 * easy access to datalists
	 */
	public function datalist_access($mode, $dl_name=NULL, $dl_id=NULL) {

		global $vce;

	}

	/**
	 * 
	 */
	public function recipe_fields($recipe) {
	
		global $vce;
		
		$vce->site->get_template_names();
	
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
		<input type="text" name="url" value="$url" autocomplete="off">
		<div class="label-text">
			<div class="label-message">URL</div>
			<div class="label-error">Enter a URL</div>
		</div>
	</label>

	<label>
		<select name="template">
		<option value=""></option>
EOF;

	foreach($vce->site->get_template_names() as $key=>$value) {
	
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