<?php

class Pbc_UserMedia extends Component {
	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC User Media',
			'description' => 'Allows a user to manage their media',
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
	 *
	 */
	public function as_content($each_component, $vce) {

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



		// convert query_sting
		if (isset($vce->query_string)) {
			$vce->query_string = json_decode($vce->query_string);
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
 		// OHSCC:  addition of hook: usermedia_resource_library_view
		if (isset($vce->site->hooks['usermedia_resource_library_view'])) {
			foreach($vce->site->hooks['usermedia_resource_library_view'] as $hook) {
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
		
			// $user->user_id
			// get media
			$subquery = "SELECT component_id FROM " . TABLE_PREFIX . "components_meta WHERE meta_key='type' AND meta_value='Media'  OR meta_value='Alias'";
			$query = "SELECT component_id FROM " . TABLE_PREFIX . "components_meta WHERE component_id in (" . $subquery . ") AND meta_key='created_by' AND meta_value='" . $vce->user->user_id . "'";
			$component_ids = $vce->db->get_data_object($query);
			
			
			
			// strategy to take query out of foreach loop
			$component_ids_commadelineated = '';
			foreach ($component_ids as $each_id) {
				$component_ids_commadelineated .= $each_id->component_id . ',';
			}
			if (!strlen($component_ids_commadelineated) > 0) {
				$component_ids_commadelineated = 0;
			}
			$component_ids_commadelineated = trim($component_ids_commadelineated, ',');
			
			// get alias components meta data using a list of component ids from last query
			$query = "SELECT component_id, meta_key, meta_value, minutia FROM " . TABLE_PREFIX . "components_meta WHERE component_id IN (" . $component_ids_commadelineated . ") ORDER BY meta_key";

			// $query = "SELECT component_id, meta_key, meta_value, minutia FROM " . TABLE_PREFIX . "components_meta WHERE component_id IN (3234,1,4) ORDER BY meta_key";
			$components_meta = $vce->db->get_data_object($query);
			
			// cycle through results and add meta_key / meta_value pairs to component object
			$used_component_ids = array();
			foreach ($components_meta as $each_meta) {
				$key = $each_meta->meta_key;
				$new_component[$each_meta->component_id][$key] = $each_meta->meta_value;

	
				// adding minutia if it exists within database table
				if (!empty($each_meta->minutia)) {
					$key .= "_minutia";
					$new_component[$each_meta->component_id][$key] = $each_meta->minutia;
				}
			}			
			
			
			// create array of component objects
			foreach ($new_component as $key=>$value) {
				$each_component = new stdClass();
				$each_component->component_id = $key;
				foreach ($new_component[$key] as $key2=>$value2) {
					if ($key2 == 'title') {
						if (trim($value2) == 'Alias') {
							$query = "SELECT component_id, meta_key, meta_value, minutia FROM " . TABLE_PREFIX . "components_meta WHERE component_id = " . $value['alias_id'] . " AND meta_key = 'title'";
							$components_meta = $vce->db->get_data_object($query);
							if (empty($components_meta)) {
								continue 2;
							}
							if (!empty($components_meta[0]->meta_value)) {
								$each_component->alphabetic_title  = $components_meta[0]->meta_value;
							} else {
								$each_component->alphabetic_title = $value2;
							}
							// $vce->dump($components_meta);
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


			// load hooks
			if (isset($vce->site->hooks['titleBar'])) {
				foreach ($vce->site->hooks['titleBar'] as $hook) {
					$title = call_user_func($hook, 'My Library', 'my_library');
				}
			}

			$vce->content->add('title', $title);
			$add_to_step_heading = '';

			// this heading only occurs if we accessed the page through the "Add From My Library" button
			if (isset($vce->as_resource_requester_id)) {
				$add_to_step_heading = '<th class="table-icon">Add to Step</th>';
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



$content .= <<<EOF

<div class="page-info">This is a list of the media that you have personally uploaded. You can edit and delete all of your personal media here on this page.</div>

<div id="my-library_table-container">
EOF;


// this allows me to build the table and find out the pagination info from that data before 
// adding the table to the content, so that the pagination markup can come before the table
$table_of_mediaitems = NULL;

$table_of_mediaitems .= <<<EOF
<table class="tablesorter">
<thead>
<tr class="table-react">
<th class="table-media-type">File Type</th>
<th class="table-title">Title</th>
$add_to_step_heading
</tr>
</thead>
EOF;


$pagination_length = 20;
$pagination_current = (isset($vce->pagination_current))? $vce->pagination_current : 1;
$pagination_offset = ($pagination_current - 1) * $pagination_length;

		$vce->number_of_pages =  ceil(count($user_media) / $pagination_length);

		$component_id_list = array();
		$i = 0;
		foreach($user_media as $each_media_item) {

			if ($i < $pagination_offset) {
				$i++;
				continue;
			}
			// $vce->dump($each_media_item);
			$each_media_item->originaltype = $each_media_item->type;
			// list each resource or alias only once
	// if (in_array($each_media_item->component_id, $component_id_list) || in_array($each_media_item->alias_id, $component_id_list)) {
	// 	// $vce->dump($each_media_item->component_id);
	// 	continue;
	// }
			
			if ($each_media_item->type == "Alias") {
				$component_id_list[] = $each_media_item->alias_id;
				$target_component = $vce->page->get_requested_component($each_media_item->alias_id);
				// $vce->dump($target_component);

				$vce->site->add_attributes('alias_of_' . $target_component->component_id, $each_media_item, TRUE);

				// $vce->dump($each_media_item);
// $vce->dump($target_component->component_id);
// $vce->dump($vce->{$target_component->component_id});
				$this_title = (isset($each_media_item->title) && trim($each_media_item->title) != 'Alias') ? $each_media_item->title : $target_component->title;
				$each_media_item-> component_id= $target_component->component_id;
				$each_media_item->title = $this_title . ' &nbsp;&nbsp;(copy of: ' . $target_component->title . ')';
				$each_media_item->name = $target_component->name;
				$each_media_item->type = $target_component->type;
				$each_media_item->path = $target_component->path;
				$each_media_item->media_type = $target_component->media_type;
				$each_media_item->originaltype = 'Alias';
				// $each_media_item-> = $target_component->;
			}

			if ($each_media_item->originaltype != "Alias") {
				$each_media_item->path = (isset($each_media_item->path)) ? $each_media_item->path : '';
				$media_path_parts = explode('_', $each_media_item->path);
				if (count($media_path_parts) > 1 && $media_path_parts[0] != $vce->user->user_id) {
					// $vce->dump($each_media_item->path );
					// find or create uploads directory for merge_to user
					$target_dir = BASEPATH . 'vce-content/uploads/' . $vce->user->user_id;
					$vce->log($target_dir);
					if (!is_dir($target_dir)) {
						if (!mkdir($target_dir, 0775, false)) {
							die('Failed to create new directories...');
						}
					}
					// create new path and name for file
					$old_path = $each_media_item->path;
					$new_path = $vce->user->user_id . '_' . $media_path_parts[1];
					$full_old_path = BASEPATH . 'vce-content/uploads/' . $media_path_parts[0] . '/' . $old_path;
					$full_new_path = BASEPATH . 'vce-content/uploads/' . $vce->user->user_id . '/' . $new_path;
					rename($full_old_path, $full_new_path);

					// change current file path for this pageload
					$each_media_item->path = $new_path;

					// change path metadata in components_meta
					$query = "UPDATE vce_components_meta SET meta_value='" . $new_path . "' WHERE meta_value='" . $old_path . "'";
					$vce->db->query($query);
				}
			}

			$component_id_list[] = $each_media_item->component_id;

			//$link_url = $vce->page->find_url($each_media_item->component_id);
			$edit_resource = $vce->site->site_url . '/' . $vce->requested_url . '?mode=edit&resource_id=' . $each_media_item->component_id;
			$dossier_for_view = $vce->user->encryption(json_encode(array('type' => 'Pbc_UserMedia','procedure' => 'view_resource','component_id' => $each_media_item->component_id)),$vce->user->session_vector);		
			$media_type = ($each_media_item->media_type == 'VimeoVideo' ? 'Video' : $each_media_item->media_type);
			$title = $each_media_item->title;
			$image_path = $vce->site->path_to_url(dirname(__FILE__)) . '/images/';
			$each_media_item_id = $each_media_item->component_id;
			$component_title = isset($vce->as_resource_requester_title) ? $vce->as_resource_requester_title : 'Last Step';

$description = isset($each_media_item->description) ? $each_media_item->description : null;

			$table_of_mediaitems .= <<<EOF
<maresourcefile title="$title" type="$media_type" href="$edit_resource">
<tr class="display-resources">
<td class="table-media-type"><div class="$media_type-icon media_icon" title="$media_type"></div></td>
<td class="table-title">
<div>
	<a href="$edit_resource">$title</a></div>
	<div>$description</div>
	<a class="resource-edit-link" href="$edit_resource" tabindex="-1">
		<button id="edit-btn" class="admin-toggle edit-toggle link-button align-right align-center"><span class="edit-btn-text">Edit</span></button>
	</a>

</td>
EOF;

			// show add button if we reached this page through the "add resource from my library" button
		if (isset($vce->as_resource_requester_id)) {

			$dossier = array(
				'type' => 'Pbc_step',
				'procedure' => 'create_alias',
				'org_id' => $vce->user->organization,
				'parent_id' => $vce->as_resource_requester_id,
				'created_by' => $vce->user->user_id,
				'redirect_url' => $vce->redirect_url
				);
			// generate dossier
			$dossier_copy_resource = $vce->generate_dossier($dossier);
// $vce->dump($vce->as_resource_requester_id);
// $vce->dump($vce->redirect_url);
			$table_of_mediaitems .= <<<EOF
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
		$table_of_mediaitems .= <<<EOF
		</tr>
	</maresourcefile>

EOF;

		$i++;
		if ($i >= $pagination_length + $pagination_offset) {
			break;
		}
			}


		// Pagination
		// the instructions to pass through the form
		$dossier = array(
			'type' => 'Pbc_UserMedia',
			'procedure' => 'pagination',
		);

		// add dossier, which is an encrypted json object of details uses in the form
		$dossier_for_pagination = $vce->generate_dossier($dossier);

		// $pagination_current = (isset($vce->pagination_current))? $vce->pagination_current : 1;
		$number_of_pages = (isset($vce->number_of_pages))? $vce->number_of_pages : 1;

		// these defaults are not currently used, but might be in the future:
		// $sort_by = (isset($vce->sort_by))? $vce->sort_by : "name";
		// $sort_direction = (isset($vce->sort_direction))? $vce->sort_direction : "asc";
		// $inputtypes = (isset($vce->inputtypes))? $vce->inputtypes : "";


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

				$table_of_mediaitems .= <<<EOF
</table>
</div>
EOF;

	$content .= <<<EOF
	$pagination_markup
	$table_of_mediaitems
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

	if ($component['created_by'] == $vce->user->user_id || $vce->user->role_id == 1 || $show_resource == TRUE) {

	
	$resource = $vce->page->instantiate_component($component, $vce);

	// send object within array to display_components
	$vce->page->display_components($resource);	
		

		$content = "";

		
		$title = $resource->title;

		
		$back = $vce->site->site_url . '/' . $vce->requested_url;

		if ($vce->query_string->mode == 'edit') {
			// resource title input
		$input = array(
			'type' => 'text',
			'name' => 'title',
			'required' => 'true',
			'value' => $title,
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
			)
		);

		$resource_title_input = $vce->content->create_input($input,'Resource Title');

		$description = nl2br($resource->description);


		$input = array(
			'type' => 'text',
			'name' => 'description',
			'value' => $description,
			'data' => array(
				'autocapitalize' => 'none',
			)
		);
// $vce->dump($original_alias);
// $vce->dump($resource);
		$resource_description_input = $vce->content->create_input($input,'Resource Description');

		// dossier for update
		$dossier = array(
			'type' => 'ResourceLibrary',
			'procedure' => 'update_resource',
			'component_id' => $resource->component_id,
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


		if ($vce->query_string->mode == 'edit') {

			if (!isset($original_alias['component_id'])) {
				$content .= <<<EOF
				<input type="submit" value="Update">
				<div class="link-button cancel-button">Cancel</div>
EOF;
			} else {
				$content .= <<<EOF

				<div>&nbsp;</div>
EOF;
			}
	

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