<?php
class Json_editor  extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'JSON Editor',
			'description' => '3rd Party JS tool for editing JSON objects',
			'category' => 'utilities'
		);
	}
	


    /**
     *
     */
    public function as_content($each_component, $vce) {

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/jsoneditor/dist/jsoneditor.min.js', 'jsoneditor');
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'pagejs');

		$vce->site->add_style(dirname(__FILE__) . '/jsoneditor/dist/jsoneditor.min.css');

		$content = NULL;

		$default_json = '{
				"array": [1, 2, 3],
				"boolean": true,
				"null": null,
				"number": 123,
				"object": {"a": "b", "c": "d"},
				"string": "Default JSON (select a JSON object above.)"
			  }';

		 // if an existing JSON object was selected, use it, otherwise show default
		$json_object_to_edit = (isset($vce->json_object_to_edit)) ? $vce->json_object_to_edit : $default_json;
		$json_object_title = (isset($vce->json_object_to_edit)) ? $vce->json_object_title : "Default Example";
		$json_object_name = (isset($vce->json_object_to_edit)) ? $vce->json_object_name : "default_example";


// $vce->dump($json_object_to_edit);
		$content .= <<<EOF
		<style type="text/css">
		code {
		  background-color: #f5f5f5;
		}
		#jsoneditor {
		  width: 900px;
		  height: 1000px;
		}
	  </style>
	</head>
	<body>
	<div>JSON Editor</div>

EOF;


	$content .= <<<EOF

	<script>
	$(document).ready(function() {
		const container = document.getElementById('jsoneditor')
		
		const options = {
			mode: 'tree',
			modes: ['code', 'form', 'text', 'tree', 'view', 'preview'], // allowed modes
			onModeChange: function (newMode, oldMode) {
				console.log('Mode switched from', oldMode, 'to', newMode)
			}
		}

		const json = $json_object_to_edit;
	
		const editor = new JSONEditor(container, options, json)

		$('#save-current').on('click', function(e) {

			current_editor_data = JSON.stringify(editor.get());
			pre_editor_data = JSON.stringify(json);
			// alert(current_editor_data);
			$('input[name=json_obj_pre_edit]').val(pre_editor_data);
			$('input[name=json_obj_post_edit]').val(current_editor_data);
			$('#save-json-obj').submit();
		});

		$('#update-current').on('click', function(e) {
			var selected_json_obj = $('#selected_json_obj_placeholder').html();
			selected_json_obj = JSON.parse(selected_json_obj);
			console.log(selected_json_obj);
			editor.update(selected_json_obj);
		});

	});
	</script>
EOF;


            // select JSON to edit
			$json_obj_options = array();
			if (isset($json_object_name)) {
				$json_obj_options[] = array('name' => $json_object_title,'value' => $json_object_name);
			}
			if ($json_object_name != 'resource_library_taxonomy') {
				$json_obj_options[] = array('name' => 'Resource Library Taxonomy','value' => 'resource_library_taxonomy');
			}
			if ($json_object_name != 'simplified_resource_library_taxonomy') {
				$json_obj_options[] = array('name' => 'Simplified Resource Library Taxonomy','value' => 'simplified_resource_library_taxonomy');
			}
			$json_obj_options[] = array('name' => 'test1','value' => 'Test 1');
			$json_obj_options[] = array('name' => 'test2','value' => 'Test 2');
			$json_obj_options[] = array('name' => 'test3','value' => 'Test 3');
			$json_obj_options[] = array('name' => 'test4','value' => 'Test 4');
			$json_obj_options[] = array('name' => 'test5','value' => 'Test 5');

			
			// JSON edit input
			$input = array(
			'type' => 'select',
			'name' => 'selected_json_obj',
			'value' => $vce->user->role_id,
			'data' => array(
				'tag' => 'required',
			),
			'options' => $json_obj_options
			);
	
			$json_obj_dropdown = $vce->content->create_input($input,'JSON Object Select Menu','Choose a JSON object to edit.');
			$dossier_for_select_json_obj = $vce->user->encryption(json_encode(array('type' => 'Json_editor','procedure' => 'get_json')),$vce->user->session_vector);		

	$content .= <<<EOF
		<form id="select-json-obj" class="asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_for_select_json_obj">
			$json_obj_dropdown
			<input class="button__primary" type="submit" value="Select JSON object">
		</form>
EOF;

	$dossier_for_save_json_obj = $vce->user->encryption(json_encode(array('type' => 'Json_editor','procedure' => 'put_json', 'component_id' => $each_component->component_id, 'json_object_name' => $json_object_name)),$vce->user->session_vector);		
	$json_object_pre_edit = (isset($vce->json_object_to_edit)) ? $vce->json_object_to_edit : null;


		// create dropdown for restore version if exists
		if (isset($json_object_name)) {
			$query = "SELECT * FROM " . TABLE_PREFIX . "components_meta WHERE component_id = ". $each_component->component_id ." AND meta_key = '$json_object_name'";
			$vce->log($query);			
			$result = $vce->db->get_data_object($query);
			
			if (!empty($result)) {
				// translate saved, encoded JSON into PHP array. This array contains all the backups
				$default_json_array = json_decode(base64_decode($result[0]->meta_value), TRUE);
				// $vce->plog($default_json_array);
				$backup_obj_options = array();
				foreach($default_json_array as $k=>$v){
						$v['created'] = (isset($v['created'])) ? date('l jS \of F Y h:i:s A', $v['created']) : 'no created date';
						$v['json_obj'] = (isset($v['json_obj'])) ? base64_encode(serialize($v['json_obj'])) : 'no json_obj';
						$backup_obj_options[] = array('name' => $v['created'], 'value' => $v['json_obj']);
				}
							// JSON edit input
			$input = array(
				'type' => 'select',
				'name' => 'selected_backup_obj',
				'value' => $vce->user->role_id,
				'data' => array(
					'tag' => 'required',
				),
				'options' => $backup_obj_options
				);
		
				$backup_obj_dropdown = $vce->content->create_input($input,'Backup Object Select Menu','Choose a Backup object by date to edit.');
				$dossier_for_select_backup_obj = $vce->user->encryption(json_encode(array('type' => 'Json_editor','procedure' => 'get_backup_object', 'component_id' => $each_component->component_id, 'json_object_name' => $json_object_name)),$vce->user->session_vector);		
	
		$content .= <<<EOF
			<div id="selected_json_obj_placeholder"></div>
			<form id="select-backup-obj" class="asynchronous-form" method="post" action="$vce->input_path">
				<input type="hidden" name="dossier" value="$dossier_for_select_backup_obj">
				$backup_obj_dropdown
				<input class="button__primary" type="submit" value="Select Backup object">
			</form>
EOF;

			}
		}

	// save json
	$content .= <<<EOF
		<form id="save-json-obj" class="asynchronous-form" method="post" action="$vce->input_path">
			<input type="hidden" name="dossier" value="$dossier_for_save_json_obj">
			<input schema="json" type="hidden" name="json_obj_pre_edit" value="{}" id="json-obj-pre-edit">
			<input schema="json" type="hidden" name="json_obj_post_edit" value="{}" id="json-obj-post-edit">
		</form>
		<br><br><br>
		<hr>
		<div><input  id="save-current"  class="button__primary" type="submit" value="Save Edited JSON object"> <input  id="update-current"  class="button__primary" type="submit" value="Update Editor Content"></div>
		<br>

		<br>
		<div id="testdiv"></div>
		<p>
		JSON object in editor:&nbsp&nbsp $json_object_title
	  	</p>
		<div id="jsoneditor"></div>
EOF;
		
		$vce->content->add('main', $content);
	
	}

	/**
	 * get JSON object to edit
	 */
	public function get_json($input, $json_object_name=NULL, $component_id=NULL, $user_id=NULL) {

		global $vce;

		$selected_json_obj = $input['selected_json_obj'];
		$json_object_to_edit = $this->create_json_from_datalists($selected_json_obj);


		if(isset($json_object_to_edit)) {
			$vce->site->add_attributes('json_object_to_edit',$json_object_to_edit);
			$vce->site->add_attributes('json_object_title', "Resource Library Taxonomy");
			$vce->site->add_attributes('json_object_name', $selected_json_obj);
		}

		echo json_encode(array('response' => 'success','procedure' => 'get_json', 'form' => 'select-json-obj', 'message' => 'loaded: ' . $selected_json_obj));
		return;
	}


		/**
	 * create JSON object from datalist
	 */
	public function get_backup_object($input, $datalist_name=NULL, $datalist_id=NULL, $user_id=NULL) {
		global $vce;
		// $vce->log($input);
		$selected_backup_obj = json_encode(unserialize(base64_decode($input['selected_backup_obj'])));
		// $vce->plog($selected_backup_obj);

		echo json_encode(array('response' => 'success','procedure' => 'get_backup_object', 'form' => 'select-backup-obj', 'message' => 'loaded: '. $input['json_object_name'], 'selected_backup_obj' => $selected_backup_obj));
		return;


	}

	/**
	 * create JSON object from datalist
	 */
	public function create_json_from_datalists($datalist_name=NULL, $datalist_id=NULL, $user_id=NULL) {
		global $vce;

		$json_object_to_edit = NULL;

		if(isset($datalist_name) && $datalist_name == 'resource_library_taxonomy') {

			// $query = "SELECT * FROM " . TABLE_PREFIX . "components_meta WHERE component_id = $component_id AND meta_key = '$json_object_name'";
			// $result = $vce->db->get_data_object($query);
			
			// if (!empty($result)) {
			// 	// translate saved, encoded JSON into PHP array. This array contains all the backups
			// 	$default_json_array = json_decode(base64_decode($result[0]->meta_value), TRUE);

			$json_object_to_edit = $this->slow_content_load_resource_library();
			$json_object_to_edit = json_encode($json_object_to_edit);
		}

		if(isset($datalist_name) && $datalist_name == 'simplified_resource_library_taxonomy') {

			$json_object_to_edit = $this->simplified_slow_content_load_resource_library();
			$json_object_to_edit = json_encode($json_object_to_edit);
		}

		return $json_object_to_edit;
	}


	public function slow_content_load_resource_library() {

		global $vce;



		$return_array = array();	
		// get datalist that is associated with this component
		$taxonomy = $vce->get_datalist(array('datalist' => 'resource_library_taxonomy'));

		$datalist_id = NULL;
		if (!is_bool(current($taxonomy))) {
			$datalist_id = current($taxonomy)['datalist_id'];
		}
		// get datalist that is associated with this component
		$taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $datalist_id));
// $vce->dump($taxonomy_items);
		// anon fuction
		$build_hierarchy = function($taxonomy_items, $counter = 0, $parent_item_id = 0) use (&$build_hierarchy, $vce, &$return_array) {

			$counter++;
		
			if (!empty($taxonomy_items)) {
				foreach ($taxonomy_items as $key=>$value) {
					$item_id = $value['item_id'];
					$category_name = $value['category_name'];
					// OHSCC change: we don't want to see all the categories listed, so these
					// categories are left out of the visible taxonomy on the page
					// if ($category_name == 'Default' || $category_name == 'Teaching Domains' || $category_name == 'Uncategorized Videos'){
					// 	continue;
					// }
		
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

// 					$return_array .= <<<EOF
// <div class="resource-library-category" item_id="$item_id">
// 	<button class="level-title level-$counter-category" item_id="$item_id" role="button" aria-expanded="false" aria-controls="$item_id-content" id="$item_id">$category_name$arrow</button>
// EOF;
$return_array[] = array('item_id'=>$item_id, 'parent_item_id'=>$parent_item_id, 'category_name'=>$category_name, 'children'=>array());

					if (!empty($taxonomy_items['items'])) {
					// this takes 4 seconds
						$build_hierarchy($taxonomy_items['items'],$counter,$item_id);
					}

// 					$content .= <<<EOF
// </div>
// EOF;
				
				}
			}
		};
		// trigger the build of the return array
		$build_hierarchy($taxonomy_items['items']);


		$tree = $this->build_tree($return_array);


		return $tree;
	}



	public function simplified_slow_content_load_resource_library() {

		global $vce;


		$return_array = array();	
		// get datalist that is associated with this component
		$taxonomy = $vce->get_datalist(array('datalist' => 'resource_library_taxonomy'));

		$datalist_id = NULL;
		if (!is_bool(current($taxonomy))) {
			$datalist_id = current($taxonomy)['datalist_id'];
		}
		// get datalist that is associated with this component
		$taxonomy_items = $vce->get_datalist_items(array('datalist_id' => $datalist_id));

		// anon fuction
		$build_hierarchy = function($taxonomy_items, $counter = 0, $parent_item_id = 0) use (&$build_hierarchy, $vce, &$return_array) {

			$counter++;
		
			if (!empty($taxonomy_items)) {

				foreach ($taxonomy_items as $key=>$value) {
					$item_id = $value['item_id'];
					$category_name = $value['category_name'];
					// OHSCC change: we don't want to see all the categories listed, so these
					// categories are left out of the visible taxonomy on the page
					// if ($category_name == 'Default' || $category_name == 'Teaching Domains' || $category_name == 'Uncategorized Videos'){
					// 	continue;
					// }
		
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

$return_array[] = array('item_id'=>$item_id, 'parent_item_id'=>$parent_item_id, 'category_name'=>$category_name, 'children'=>array());

					if (!empty($taxonomy_items['items'])) {
					// this takes 4 seconds
						$build_hierarchy($taxonomy_items['items'],$counter,$item_id);
					}

// 					$content .= <<<EOF
// </div>
// EOF;
				
				}
			}
		};
		// trigger the build of the return array
		$build_hierarchy($taxonomy_items['items']);


		$tree = $this->simplified_build_tree($return_array);

		$tree = $this->remove_meta_data($tree);


		return $tree;
	}

/** build_tree
 * recursive function
 * turns a one-dim array with parent id's into a tree
 */
	public function build_tree(array $elements, $parent_id = 0) {
		$branch = array();
	
		foreach ($elements as $element) {
			if ($element['parent_item_id'] == $parent_id) {
				$children = $this->build_tree($elements, $element['item_id']);
				if ($children) {
					$element['children'] = $children;
				}
				$branch[] = $element;
			}
		}
	
		return $branch;
	}


	/** simplified build_tree
 * recursive function
 * turns a one-dim array with parent id's into a tree
 */
public function simplified_build_tree(array $elements, $parent_id = 0) {
	$branch = array();
	// $return_array[] = array('item_id'=>$item_id, 'parent_item_id'=>$parent_item_id, 'category_name'=>$category_name, 'children'=>array());

	foreach ($elements as $element) {
		if ($element['parent_item_id'] == $parent_id) {
			$children = $this->simplified_build_tree($elements, $element['item_id']);
			if ($children) {
				foreach ($children as $child) {
					// unset($child['category_name']);
					// unset($child['item_id']);
					// unset($child['parent_item_id']);
					$element[$child['category_name']] = $child;
				}
			}
			// unset($element['children']);
			$branch[$element['category_name']] = $element;
		}
	}

	return $branch;
}

public function remove_meta_data(array $elements) {

	// $return_array[] = array('item_id'=>$item_id, 'parent_item_id'=>$parent_item_id, 'category_name'=>$category_name, 'children'=>array());
global $vce;
// $vce->log($elements);
	foreach ($elements as $k=>$element) {
		if (isset($element['category_name'])){
			unset($element['category_name']);
		}
		if (isset($element['item_id'])){
			unset($element['item_id']);
		}
		if (isset($element['parent_item_id'])){
			unset($element['parent_item_id']);
		}
		if (isset($element['children'])){
			unset($element['children']);
		}
		// $vce->log(gettype($element));
		// $vce->log($element);
		if (is_array($element) && count($element) > 0) {
					$element = $this->remove_meta_data($element);
		} elseif (is_array($element) && count($element) == 0) {
			$element = '';
		}



		$elements[$k] = $element;
	}

	return $elements;
}
	


	/**
	 * put JSON object to edit
	 */
	public function put_json($input) {
		global $vce;
		$vce->plog($input, false);

		$component_id = $input['component_id'];
		$json_object_name = $input['json_object_name'];


		// convert 
		$json_obj_post_edit = json_decode($input['json_obj_post_edit'], TRUE);

		// $vce->log(is_array($json_obj_post_edit));
		// $isJson = $this->isJson($json_obj_post_edit);
		// $vce->log($isJson);
		// if ($isJson == TRUE) {
		// $vce->log('$isJson');
		// }



		$query = "SELECT * FROM " . TABLE_PREFIX . "components_meta WHERE component_id = $component_id AND meta_key = '$json_object_name'";
		$result = $vce->db->get_data_object($query);
		
		if (!empty($result)) {
			// translate saved, encoded JSON into PHP array. This array contains all the backups
			$default_json_array = json_decode(base64_decode($result[0]->meta_value), TRUE);

			// test to see if it is truly JSON
			// $isJson = $this->isJson($saved_json_obj);
			// $vce->plog(array_keys($default_json_array), true);
			// $vce->plog($default_json_array, true);
			// $vce->plog($json_obj_post_edit, true);

			// put recently edited JSON in array[0], and add the datetime as key;
			$datetime_now = time();
			array_unshift($default_json_array, array('created' => $datetime_now, 'json_obj' => $json_obj_post_edit));
			// remove last element of array
			array_pop($default_json_array);
			// $vce->plog($default_json_array, false);

			// encode array to JSON
			$default_json_array = json_encode($default_json_array);
			// encode JSON object in base64 to save
			$default_json_array = base64_encode($default_json_array);
			
			// save to db
			$query = "UPDATE " . TABLE_PREFIX . "components_meta SET meta_value = '" . $default_json_array . "' WHERE component_id =" . $input['component_id'] . " AND meta_key = '$json_object_name'" ;
			// $vce->log($query);
			$vce->db->query($query);

		} else {

			// create default array with 10 JSON objects
			$default_json_array = array(
				'{}','{}','{}','{}','{}','{}','{}','{}','{}','{}'
			);
			// put recently edited JSON in array[0]
			array_unshift($default_json_array, array('created' => $datetime_now, 'json_obj' => $json_obj_post_edit));
			// remove last element of array
			array_pop($default_json_array);
			// encode array to JSON
			$default_json_array = json_encode($default_json_array);
			// encode JSON object in base64 to save
			$default_json_array = base64_encode($default_json_array);

			$query = "INSERT INTO " . TABLE_PREFIX . "components_meta  (component_id, meta_key, meta_value, minutia) VALUES (" . $input['component_id'] . ", '$json_object_name', '" . $default_json_array . "', '')";
			// $vce->log($query);
			$vce->db->query($query);

		}

		echo json_encode(array('response' => 'success', 'form' => 'save-json-obj', 'procedure' => 'put_json','action' => '', 'message' => 'Saved ' . $json_object_name));
		return;
	}

	public function isJson($string) {
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	 }


	 /**
	 * 
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
