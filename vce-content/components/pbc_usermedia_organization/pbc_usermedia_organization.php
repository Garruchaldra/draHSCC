<?php

class Pbc_UserMedia_organization extends Component {
	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC User Media Organization',
			'description' => 'Allows an organization to manage their media',
			'category' => 'pbc'
		);
	}
	
	public function find_sub_components($requested_component, $vce, $components, $sub_components) {
		return false;
	}

	public function check_access($each_component, $vce) {
			return true;
	}

	// toggle if 
	public function build_sub_components($each_component, $vce) {
			return false;
	}

	public function allow_sub_components($each_component, $vce) {

		if (isset($vce->query_string)) {

			if (isset($vce->query_string->mode) && $vce->query_string->mode == 'edit') {
				return false;
			}
		}
		return true;
	}



		/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {

		$content_hook = array(
			'media_create_component' => 'Pbc_UserMedia_organization::add_org_id_to_media',
		);

		return $content_hook;
	}

	/**
	 * add current org_id to media being saved which has this component as parent
	 */
	public static function add_org_id_to_media($input) {

		global $vce;

		$input['org_id'] = $vce->user->organization;

		return $input;
	}

	/**
	 *
	 */
	public function as_content($each_component, $vce) {
// $vce->dump($vce->user);
		// this is to avoid having add buttons on the resources 
		if (isset($vce->redirect_url) && isset($vce->as_resource_requester_id) && $vce->as_resource_requester_id == $each_component->component_id) {
			$vce->site->remove_attributes('redirect_url');
			unset($vce->redirect_url);
		}
		if (isset($vce->as_resource_requester_id) && $vce->as_resource_requester_id == $each_component->component_id) {
			// $vce->dump($vce->as_resource_requester_id);
			$vce->site->remove_attributes('as_resource_requester_id');
			unset($vce->as_resource_requester_id);
		}

		

		// $vce->dump($each_component);
		// $vce->plog($vce->user);
		$usermedia_each_component = $each_component;
		$content = NULL;
		
		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','vidbox-hover-style');

		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui tablesorter');


		// // Edit a resource
		// if (isset($vce->resource_id)) {
		// 	self::manage_resource($each_component, $vce);
		// 	return;
		// }

		// convert query_sting
		if (isset($vce->query_string)) {
			$vce->query_string = json_decode($vce->query_string);
		}

		// Call utility for adding "org_id" to media children of this library
		if (isset($vce->query_string->utility_to_run) && $vce->query_string->utility_to_run == 'utility_add_org_ids') {
			// $vce->dump($vce->query_string);
			self::utility_add_org_ids($vce);
			return;
		}

		// Call utility for adding "org_id" to media children of this library
		if (isset($vce->query_string->utility_to_run) && $vce->query_string->utility_to_run == 'correct_alias_type') {
			// $vce->dump($vce->query_string);
			self::correct_alias_type($vce);
			return;
		}

		// View an individual resource
		if (isset($vce->query_string->resource_id)) {
			// $vce->dump($vce->query_string);
			self::view_resource($each_component, $vce);
			return;
		}

			
		// if a media id is present, show that media
		if (isset($vce->media_id)) {

			$subquery = "SELECT * FROM  " . TABLE_PREFIX . "components WHERE component_id='" . $vce->media_id . "'";
			$component_info = $vce->db->get_data_object($subquery);

			$query = "SELECT meta_key, meta_value FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $vce->media_id . "'";
			$meta_data = $vce->db->get_data_object($query, 0);
			
			$media_component = new stdClass();
			
			$media_component->component_id = $vce->media_id;
			$media_component->sequence = $component_info[0]->sequence;

			//rekey the component attributes
			foreach ($meta_data as $this_meta_data) {
					$key = $this_meta_data['meta_key'];
					$value = $this_meta_data['meta_value'];
					$media_component->$key = $value;
			}

			//check for access permission
			if ($media_component->created_by == $vce->user->user_id || $vce->user->role_id == 1) {
			
				$class_name = $media_component->type;
			
				// get list of activated components
				$activated_components = json_decode($vce->site->activated_components, true);
			
				if (!class_exists($class_name)) {

					// load class
					require_once($activated_components[$class_name]);

				}
			
				// create object from media component class
				$this_component = new $class_name((object) $media_component);
			
				$this_component->as_content((object) $media_component, $vce);
			
				$this_component->as_content_finish((object) $media_component, $vce);
				
				$query = "SELECT component_id FROM  " . TABLE_PREFIX . "components_meta WHERE meta_key='alias_id' and meta_value='" . $vce->media_id . "'";
				$alias_ids = $vce->db->get_data_object($query);


		
		$title = $media_component->title;
		$description = nl2br($media_component->description);
		$back = $vce->site->site_url . '/' . $vce->requested_url;



		// load hooks
		// resource_library_view
		if (isset($vce->site->hooks['resource_library_view'])) {
			foreach($vce->site->hooks['resource_library_view'] as $hook) {
				$content .= call_user_func($hook, $media_component, $vce);
			}
		}
 		// OHSCC:  addition of hook: org_resource_library_view
		if (isset($vce->site->hooks['orgmedia_resource_library_view'])) {
			foreach($vce->site->hooks['orgmedia_resource_library_view'] as $hook) {
				$content .= call_user_func($hook, $media_component, $vce);
			}
		}


$content .= <<<EOF
<br>
<a href="$back" class="link-button">Back To Previous Page</a> 
EOF;
		
			
				
				foreach ($alias_ids as $key=>$value) {
				
					$query = "SELECT component_id, meta_key, meta_value FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $value->component_id . "'";
					$meta_data = $vce->db->get_data_object($query);
				
					$each_component = new stdClass();
			
					$each_component->component_id = $value->component_id;

					// cycle through results and add meta_key / meta_value pairs to component object
					foreach ($meta_data as $each_meta) {
			
						$key = $each_meta->meta_key;
	
						$each_component->$key = $each_meta->meta_value;
	
						// adding minutia if it exists within database table
						if (!empty($each_meta->minutia)) {
							$key .= "_minutia";
							$each_component->$key = $each_meta->minutia;
						}
			
					}
			
					$user_media[] = $each_component;
				
				}
				
				if (!empty($user_media)) {
				
					$content .= '<h3>Aliases Of This Media Item</h3>';
				
					foreach ($user_media as $key=>$each_media_item) {
				
						$link_url = $vce->page->find_url($each_media_item->component_id);

						$dossier = array(
						'type' => 'Pbc_UserMedia',
						'procedure' => 'delete',
						'component_id' => $each_media_item->component_id,
						'created_at' =>  $each_media_item->created_at,
						'parent_url' => $vce->requested_url
						);

						$dossier_for_delete = $vce->user->encryption(json_encode($dossier),$vce->user->session_vector);		

		
$content .= <<<EOF
$link_url

<a href="$link_url" class="link-button">View In Location</a>

<form id="delete_$each_media_item->component_id" class="delete-form inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete">
<input type="submit" value="Delete">
</form>
<hr>
EOF;

		
					}
				}
				
			
			} else {
			
				$content .= "You do not have permission to access this component";
			
				return false;
			
			}
		
		
		} else {



			// if no media was selected, show list of media
			$user_media = array();



						// Pagination
            // the instructions to pass through the form
            $dossier = array(
                'type' => 'Pbc_UserMedia_organization',
                'procedure' => 'pagination',
            );


			// add dossier, which is an encrypted json object of details uses in the form
			$dossier_for_pagination = $vce->generate_dossier($dossier);

			$pagination_current = (isset($vce->pagination_current))? $vce->pagination_current : 1;
			$number_of_pages = (isset($vce->number_of_pages))? $vce->number_of_pages : 1;
			$pagination_length = isset($vce->pagination_length) ? $vce->pagination_length : 25;

			// get total count of media items
			// $query = "SELECT count(*) as count FROM " . TABLE_PREFIX . "users WHERE role_id IN (" . implode(',', $role_id_in) . ")";
			$query = "SELECT count(a.component_id) as count FROM " . TABLE_PREFIX . "components AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.parent_id = b.component_id AND b.meta_key='type' AND b.meta_value='Pbc_UserMedia_organization' JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key='type' AND (c.meta_value='Media' OR c.meta_value='Alias')  JOIN " . TABLE_PREFIX . "components_meta AS d ON a.component_id = d.component_id AND d.meta_key='org_id' AND d.meta_value=" . $vce->user->organization . " JOIN " . TABLE_PREFIX . "components_meta AS e ON a.component_id = e.component_id AND e.meta_key='org_id' AND e.meta_value=" . $vce->user->organization;

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

			

			// these defaults are not currently used, but might be in the future:
			$sort_by = (isset($vce->sort_by))? $vce->sort_by : 'title';
			$sort_direction = (isset($vce->sort_direction))? $vce->sort_direction : "asc";
			$inputtypes = (isset($vce->inputtypes))? $vce->inputtypes : "";


			$pagination_previous = ($pagination_current > 1) ? $pagination_current - 1 : 1;
			$pagination_next = ($pagination_current < $number_of_pages) ? $pagination_current + 1 : $number_of_pages;


			$pagination_markup = NULL;
			$pagination = TRUE;
			
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
		


			// query to find all components which are:
			// children of the pbc_usermedia_organization component
			// either Media or Alias components
			// were saved with the user's current organization id as attribute "org_id"

			$query = "SELECT a.component_id AS component_id, e.meta_value AS title FROM " . TABLE_PREFIX . "components AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.parent_id = b.component_id AND b.meta_key='type' AND b.meta_value='Pbc_UserMedia_organization' JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key='type' AND (c.meta_value='Media' OR c.meta_value='Alias')  JOIN " . TABLE_PREFIX . "components_meta AS d ON a.component_id = d.component_id AND d.meta_key='org_id' AND d.meta_value=" . $vce->user->organization . " JOIN " . TABLE_PREFIX . "components_meta AS e ON a.component_id = e.component_id AND e.meta_key='title' ORDER BY $sort_by " .  $sort_direction . " LIMIT " . $pagination_length . " OFFSET " . $pagination_offset;;
			$media_component_ids = $vce->db->get_data_object($query);

			// $vce->dump($query);
			
			
			// strategy to take query out of foreach loop
			$component_ids_commadelineated = '';
			foreach ($media_component_ids as $each_id) {
				$component_ids_commadelineated .= $each_id->component_id . ',';
			}
			if (!strlen($component_ids_commadelineated) > 0) {
				$component_ids_commadelineated = 0;
			}
			$component_ids_commadelineated = trim($component_ids_commadelineated, ',');
			
			// get alias components meta data using a list of component ids from last query
			$query = "SELECT component_id, meta_key, meta_value, minutia FROM " . TABLE_PREFIX . "components_meta WHERE component_id IN (" . $component_ids_commadelineated . ") ORDER BY meta_key";
// $vce->dump($query);
			// $query = "SELECT component_id, meta_key, meta_value, minutia FROM " . TABLE_PREFIX . "components_meta WHERE component_id IN (3234,1,4) ORDER BY meta_key";
			$components_meta = $vce->db->get_data_object($query);
		
			// cycle through results and add meta_key / meta_value pairs to component object
			$used_component_ids = array();
			$new_component = array();
			foreach ($components_meta as $each_meta) {
				if ($each_meta->component_id == 0) {
					continue;
				}
				$key = $each_meta->meta_key;
				$new_component[$each_meta->component_id][$key] = $each_meta->meta_value;

	
				// adding minutia if it exists within database table
				if (!empty($each_meta->minutia)) {
					$key .= "_minutia";
					$new_component[$each_meta->component_id][$key] = $each_meta->minutia;
				}
			}			

			$new_component = (isset($new_component)) ? $new_component : array();
			$original_each_component = $each_component;
			// $vce->dump($new_component);
			// create array of component objects
			foreach ($new_component as $key=>$value) {
				$each_component = new stdClass();
				$each_component->component_id = $key;
				// $vce->dump($new_component[$key]);
				foreach ($new_component[$key] as $key2=>$value2) {
					if ($key2 == 'title') {
						if (trim($value2) == 'Alias') {
							$query = "SELECT component_id, meta_key, meta_value, minutia FROM " . TABLE_PREFIX . "components_meta WHERE component_id = " . $value['alias_id'] . " AND meta_key = 'title'";
							// $vce->dump($query);
							$components_meta = $vce->db->get_data_object($query);
							if (empty($components_meta)) {
								// $vce->dump('yep');
								continue 2;
							}
							if (!empty($components_meta[0]->meta_value)) {
								$each_component->alphabetic_title  = $components_meta[0]->meta_value . ' (copy)';
							} else {
								$each_component->alphabetic_title = $value2;
							}
							unset($query, $components_meta);
						} else {
							$each_component->alphabetic_title = $value2;
						}
					}
					$each_component->$key2 = $value2;
				}
				$each_component->alphabetic_title = (isset($each_component->alphabetic_title))? $each_component->alphabetic_title : '';
				$user_media[$each_component->alphabetic_title] = $each_component;
			}

			// ksort($user_media);
			uksort($user_media, "strnatcasecmp");
			// $this_media = '';
			// foreach ($user_media as $k=>$v){
			// 	$this_media .= "
			// 	 $k";
			// }
			// $vce->dump($this_media);

			// $vce->dump($user_media);

			// user information
			$user_data = NULL;
			if (class_exists('Pbc_utilities')) { 
				$user_data = Pbc_utilities::get_user_data($vce->user->user_id);
			}
			// $vce->dump($user_data);

			// load hooks
			if (isset($vce->site->hooks['titleBar'])) {
				foreach ($vce->site->hooks['titleBar'] as $hook) {
					$title = call_user_func($hook, 'Organization Media Library for ' . $user_data->organization_name, 'org_media_library');
				}
			}

			$vce->content->add('title', $title);
			$add_to_step_heading = '';

			// this heading only occurs if we accessed the page through the "Add From Resource Library" button
			if (isset($vce->as_resource_requester_id)) {
				$add_to_step_heading = '<th class="table-icon">Add to Org Library</th>';
			}

			// I don't know why, but the Media "Add New Media" button does not show up unless the $each_component
			// object is set to values of a media_item. 
			// The next few lines create a default $each_component object if there has been no media uploaded yet.
			$test_media_item = new stdClass();
			$test_media_item->component_id = 1010101010;
			$test_media_item->created_by = $vce->user->user_id;
			$test_media_item->alphabetic_title ='add test';
			$test_media_item->type = 'Image';

			$default_media_item = array("default media item"=>$test_media_item);

			$each_component = new stdClass();

			foreach ($default_media_item as $key => $value)
			{
				$each_component->$key = $value;
			}

			
			$each_component->url = isset($each_component->url) ? $each_component->url : $vce->site->site_url;






	$dossier = array(
		'type' => 'Pbc_utilities',
		'procedure' => 'add_as_resource_requester_id',
		'url_of_resource_library' => $vce->site->site_url . '/resource_library',
		'component_id' => $usermedia_each_component->component_id,
		'redirect_url' => $usermedia_each_component->parent->url,
		'component_title' => $usermedia_each_component->title
		);
// $vce->dump($dossier);
		// add dossier for requesting a resource
		$dossier_for_add_resource_requester_id = $vce->generate_dossier($dossier);
				
		$content .= <<<EOF
<form id="action_plan_step_resource_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_add_resource_requester_id">
<a href="" tabindex="-1"><button class="resource-library-btn button__primary">Add Resource from Resource Library</button></a>
</form>
EOF;

$dossier = array(
	'type' => 'Pbc_utilities',
	'procedure' => 'add_as_resource_requester_id',
	'url_of_resource_library' => $vce->site->site_url . '/usermedia',
	'component_id' => $usermedia_each_component->component_id,
	'redirect_url' => $usermedia_each_component->parent->url,
	'component_title' => $usermedia_each_component->title
	);

// add dossier for requesting a resource
$dossier_for_add_my_library_resource_requester_id = $vce->generate_dossier($dossier);


		$content .= <<<EOF
<form id="action_plan_step_my_library_form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_add_my_library_resource_requester_id">
<a href="" tabindex="-1"><button class="resource-library-btn button__primary">Add Resource from My Library</button></a>
</form>
EOF;

$content .= <<<EOF
	$pagination_markup
EOF;



$content .= <<<EOF

<div class="page-info">This is a list of the media uploaded to this library by members of the organization "$user_data->organization_name".<br>
Each user can edit and delete the media items they have personally uploaded.</div>

<div id="my-library_table-container">
<table class="tablesorter">
<thead>
<tr class="table-react">
<th class="table-media-type">File Type</th>
<th class="table-title">Title</th>
$add_to_step_heading
</tr>
</thead>
EOF;
		// $vce->dump($user_media);
		$component_id_list = array();

// $vce->dump($each_component);

		foreach($user_media as $each_media_item) {
			// $vce->dump($each_media_item);

			// list each resource or alias only once
	// if (in_array($each_media_item->component_id, $component_id_list) || in_array($each_media_item->alias_id, $component_id_list)) {
	// 	// $vce->dump($each_media_item->component_id);
	// 	continue;
	// }
			
			if (isset($each_media_item->type ) && $each_media_item->type == "Alias") {
				
				$component_id_list[] = $each_media_item->alias_id;
				$target_component = $vce->page->get_requested_component($each_media_item->alias_id);
				// $vce->dump($target_component);

				$vce->site->add_attributes('alias_of_' . $target_component->component_id, $each_media_item, TRUE);

				// $vce->dump($each_media_item);
// $vce->dump($target_component->component_id);
// $vce->dump($vce->{$target_component->component_id});
				$this_title = (isset($each_media_item->title) && trim($each_media_item->title) != 'Alias') ? $each_media_item->title : $target_component->title;
				$each_media_item->component_id = $target_component->component_id;
				$each_media_item->title = $this_title . ' &nbsp;&nbsp;(copy of: ' . $target_component->title . ')';
				$each_media_item->name = $target_component->name;
				$each_media_item->type = $target_component->type;
				$each_media_item->path = $target_component->path;
				$each_media_item->media_type = $target_component->media_type;
				$each_media_item->description = (isset($each_media_item->description)) ? $each_media_item->description : $target_component->description;
				// $each_media_item-> = $target_component->;
			}


			$component_id_list[] = $each_media_item->component_id;

			//$link_url = $vce->page->find_url($each_media_item->component_id);
			$edit_resource = $vce->site->site_url . '/' . $vce->requested_url . '?mode=edit&resource_id=' . $each_media_item->component_id;
			$dossier_for_view = $vce->user->encryption(json_encode(array('type' => 'Pbc_UserMedia','procedure' => 'view_resource','component_id' => $each_media_item->component_id)),$vce->user->session_vector);
			$each_media_item->media_type = (isset($each_media_item->media_type)) ? $each_media_item->media_type : '';		
			$media_type = ($each_media_item->media_type == 'VimeoVideo') ? 'Video' : $each_media_item->media_type;
			$title = $each_media_item->title;
			$image_path = $vce->site->path_to_url(dirname(__FILE__)) . '/images/';
			$each_media_item_id = $each_media_item->component_id;
			$component_title = isset($vce->as_resource_requester_title) ? $vce->as_resource_requester_title : 'Last Step';

$description = isset($each_media_item->description) ? $each_media_item->description : null;

			$content .= <<<EOF
<tr class="display-resources">
<td class="table-media-type"><div class="$media_type-icon media_icon" title="$media_type"></div></td>
<td class="table-title">
<div><a href="$edit_resource">$title</a></div>
<div>$description</div>
	<a class="resource-edit-link" href="$edit_resource" tabindex="-1">
		<button id="edit-btn" class="admin-toggle edit-toggle link-button align-right align-center"><span class="edit-btn-text">Edit</span></button>
	</a>

</td>
EOF;

			// show add button if we reached this page through the "add resource from org library" button
		if (isset($vce->as_resource_requester_id)) {

			$dossier = array(
				'type' => 'Pbc_step',
				'procedure' => 'create_alias',
				'parent_id' => $vce->as_resource_requester_id,
				'org_id' => $vce->user->organization,
				'created_by' => $vce->user->user_id,
				'redirect_url' => $vce->redirect_url
			);
			// generate dossier
			$dossier_copy_resource = $vce->generate_dossier($dossier);
// $vce->dump($vce->as_resource_requester_id);
// $vce->dump($vce->redirect_url);
			$content .= <<<EOF
			<td class="table-icon">
			<form class="asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_copy_resource">
			<input type="hidden" class="add-button-info" title="$title" name="alias_id" value="$each_media_item_id" resource_requester_title="$component_title">
			<button class="plus-minus-icon">+</button>
			<div class="menu-container"></div>
			</form>
			</td>
			
EOF;
		}
		$content .= <<<EOF
		</tr>
EOF;

			}

			$content .= <<<EOF
</table>
</div>
EOF;
		
		}
		
		$vce->content->add('main', $content, array('place' => 'last'));

	
	}
	

	
		
	/**
	 *
	 */
	public function as_content_finish($each_component, $vce) {
	
	
	}



	/**
	 * show an individual resource
	 */
	private function view_resource($each_component, $vce) {
// $vce->dump($vce->query_string->resource_id);
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'jquery-ui select2');
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','resource-library-style');

	// $vce->dump($vce->as_resource_requester_id);
		// check that resource id is a number. If it is not, then return
		if (preg_match('/^\d+$/', $vce->query_string->resource_id, $matches) !== 1) {
			$vce->content->add('main','<div class="form-message form-error">Not a valid resource id</div>');
			return false;
		}
		
		
		// // check that this component is media contained within the resource library
		// $query = "SELECT * FROM  " . TABLE_PREFIX . "components INNER JOIN " . TABLE_PREFIX . "components_meta ON " . TABLE_PREFIX . "components.component_id=" . TABLE_PREFIX . "components_meta.component_id WHERE " . TABLE_PREFIX . "components.component_id='" . $vce->query_string->resource_id . "' AND " . TABLE_PREFIX . "components_meta.meta_key='taxonomy'";
		// $component_info = $vce->db->get_data_object($query);
		
		// // check that resource was returned
		// if (empty($component_info)) {
		// 	$vce->content->add('main','<div class="form-message form-error">The requested resource does not exist</div>');
		// 	return false;
		// }
	
		$query = "SELECT * FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $vce->query_string->resource_id . "'";
		$component_meta = $vce->db->get_data_object($query, false);
		// $vce->log($component_meta);
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
// 	$vce->dump($component);
// 	$vce->dump($component['component_id']);
// $vce->dump($vce->{$component['component_id']});
// $vce->dump($vce->{'alias_of_' . $component['component_id']});
	$show_resource = FALSE;
	if (isset($vce->{'alias_of_' . $component['component_id']})) {
		$original_alias = $vce->{'alias_of_' . $component['component_id']};
		$show_resource = TRUE;
	}

	$created_by = $vce->user->get_users($component['created_by']);
	// $vce->dump($created_by[0]->organization);
	// $vce->dump($vce->user->organization);
	if ($vce->user->organization == $created_by[0]->organization) {
		$show_resource = TRUE;
	}


	if ($component['created_by'] == $vce->user->user_id || $vce->user->role_id == 1 || $show_resource == TRUE) {

	
	$resource = $vce->page->instantiate_component($component, $vce);

	// send object within array to display_components
	$vce->page->display_components($resource);	
		

		$content = "";

		$component_title_to_update = $resource->title;
		if (isset($original_alias['title'])) {
			$component_title_to_update = $original_alias['title'];
		}
		
		$back = $vce->site->site_url . '/' . $vce->requested_url;

		if ($vce->query_string->mode == 'edit') {
			// resource title input
		$input = array(
			'type' => 'text',
			'name' => 'title',
			'required' => 'true',
			'value' => $component_title_to_update,
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$resource_title_input = $vce->content->create_input($input,'Resource Title');

		$description = nl2br($resource->description);

		$component_description_to_update = $resource->description;
		if (isset($original_alias['description'])) {
			$component_description_to_update = $original_alias['description'];
		}

		$input = array(
			'type' => 'text',
			'name' => 'description',
			'value' => $component_description_to_update,
			'data' => array(
				'autocapitalize' => 'none',
			)
		);
// $vce->dump($original_alias);
// $vce->dump($resource);
		$resource_description_input = $vce->content->create_input($input,'Resource Description');

		$component_id_to_update = $resource->component_id;
		if (isset($original_alias['component_id'])) {
			$component_id_to_update = $original_alias['component_id'];
		}

		// dossier for update
		$dossier = array(
			'type' => 'ResourceLibrary',
			'procedure' => 'update_resource',
			'component_id' => $component_id_to_update,
			'parent_url' => $vce->site->site_url . '/' . $vce->requested_url
		);

		// generate dossier
		$dossier_edit_resource = $vce->generate_dossier($dossier);

		$component_id_to_delete = $resource->component_id;
		if (isset($original_alias['component_id'])) {
			$component_id_to_delete = $original_alias['component_id'];
		}

		$created_by = $resource->created_by;
		if (isset($original_alias['created_by'])) {
			$created_by = $original_alias['created_by'];
		}

		$created_at = $resource->created_at;
		if (isset($original_alias['created_at'])) {
			$created_at = $original_alias['created_at'];
		}
// $vce->dump($component_id_to_delete);
		// dossier for delete
		$dossier = array(
			'type' => 'Pbc_UserMedia',
			'procedure' => 'delete_resource',
			'component_id' => $component_id_to_delete,
			'created_by' => $created_by,
			'created_at' => $created_at,
			'parent_url' => $vce->requested_url
			);

			// generate dossier
			$dossier_for_delete = $vce->generate_dossier($dossier);
			

		
		$content .= <<<EOF
		<form id="edit-resource" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
		<input type="hidden" name="dossier" value="$dossier_edit_resource">
		$resource_title_input
		$resource_description_input
EOF;
// $vce->dump($vce->query_string->mode);
// $vce->dump($component['created_by']);
			// show edit and delete buttons only if this user also created the resource
		if ($vce->query_string->mode == 'edit' && $vce->user->user_id == $created_by) {

			// if (!isset($original_alias['component_id'])) {
				$content .= <<<EOF
				<input type="submit" value="Update">
				<div class="link-button cancel-button">Cancel</div>
EOF;
// 			} else {
// 				$content .= <<<EOF

// 				<div>&nbsp;</div>
// EOF;
// 			}
	

			$content .= <<<EOF
			</form>
EOF;

			$delete_message = 'Delete Resource';

			if (isset($original_alias['component_id'])) {
				$delete_message = 'Delete This Copy of Resource';
			}
			
			$content .= <<<EOF
			<form id="delete-resource" class="delete-form float-right-form asynchronous-form" method="post" action="$vce->input_path">
				<input type="hidden" name="dossier" value="$dossier_for_delete">
				<input type="submit" value="$delete_message">
			</form>
	
EOF;

			} else {
				$content .= <<<EOF
				</form>
EOF;
			}


		} else {
			$content .= <<<EOF
<div>Title: $title</div> <p>
<div>$description</div><p>
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
		return;
	}

	$back = $vce->site->site_url . '/' . $vce->requested_url;
	$content .= <<<EOF
This resource was not uploaded by you.
<br><br>
<a href="$back" class="link-button">Back To Previous Page</a> 
EOF;

		$vce->content->add('main',$content);
	
	}

	/**
	 * change any Alias type attributes which were written incorrectly
	 */

	public static function correct_alias_type($vce) {

		$query = "UPDATE " . TABLE_PREFIX . "components_meta SET meta_value = 'Alias' WHERE meta_key='title' AND meta_value=' Alias'";
		$corrected_components = $vce->db->query($query);

		$vce->dump($corrected_components);

		return TRUE;
	}

	/**
	 * give each media item which has this component as its parent the org_id of its creator
	 */

	public static function utility_add_org_ids($vce) {

		$query = "SELECT a.component_id, d.meta_value, e.meta_value AS org_id FROM " . TABLE_PREFIX . "components AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.parent_id = b.component_id AND b.meta_key='type' AND b.meta_value='Pbc_UserMedia_organization' JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key='type' AND (c.meta_value='Media' OR c.meta_value='Alias') JOIN " . TABLE_PREFIX . "components_meta AS d ON a.component_id = d.component_id AND d.meta_key='created_by' LEFT JOIN " . TABLE_PREFIX . "components_meta AS e ON a.component_id = e.component_id AND e.meta_key='org_id'";
		$media_components = $vce->db->get_data_object($query);
		
		foreach ($media_components as $each_media_component) {
			if (empty($each_media_component->org_id)){
				$this_user = $vce->user->get_users($each_media_component->meta_value);			
				$query = "INSERT INTO  " . TABLE_PREFIX . "components_meta  (component_id, meta_key, meta_value, minutia) VALUES ('" . $each_media_component->component_id . "', 'org_id', '" .$this_user[0]->organization . "', '')";
				$result = $vce->db->query($query);
				// $vce->dump($query);
			}
		}
	}

	/**
	 * update or delete an existing resource library media item
	 */
	private function manage_resource($each_component, $vce) {

		// if ($vce->check_permissions('resource_administrator')) {
		
			$component_id = $vce->resource_id;
		

		
			$query = "SELECT * FROM  " . TABLE_PREFIX . "components_meta WHERE component_id='" . $component_id . "'";
			$component_meta = $vce->db->get_data_object($query);
		
			$requested_id = array();

			foreach ($component_meta as $meta_values) {
				$requested_id[$meta_values->meta_key] = $meta_values->meta_value;
			}
		
			$title = $requested_id['title'];
			$description = $requested_id['description'];
		
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



$content .= <<<EOF
			<input type="submit" value="Update">
			<div class="link-button cancel-button">Cancel</div>
		</form>
EOF;

			if ($vce->check_permissions('resource_administrator')) {
			
				// dossier for invite
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
			
		// }
		
		
	}






		/**
	 * 
	 */
	public function delete_resource($input) {
		global $vce;

		self::delete_component($input);
		$parent_url = $input['parent_url'];
	
		echo json_encode(array('response' => 'success','procedure' => 'delete','message' => 'Updated', 'url' => $parent_url));
		return;
	
	}


	/**
	* pagination of resources
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
	 * edit 
	 */
	public function view($input) {
	
		// add attributes to page object for next page load using session
		global $vce;
		// $vce->log($input);


		$vce->site->add_attributes('media_id',$input['component_id']);
	
		echo json_encode(array('response' => 'success','action' => 'reload', 'delay' => '0'));
		return;
		
	}


	/**
	 * fields for ManageRecipe
	 */
	public function recipe_fields($recipe) {
	
		global $vce;
	
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];

$elements = <<<EOF
<input type="hidden" name="auto_create" value="forward">
<label>
<input type="text" name="title" value="$title" tag="required" autocomplete="off">
<div class="label-text">
<div class="label-message">Title</div>
<div class="label-error">Enter a Title</div>
</div>
</label>
EOF;

		return $elements;
		
	}

}