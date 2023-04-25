<?php

class Pbc_batch_process extends Component {

	/**
	 * basic info about the component
	 * this component was based on the "Reflection_assignment" component
	 */
	public function component_info() {
		return array(
			'name' => 'Pbc Batch Process',
			'description' => 'Utility for Batch Processing Components',
			'category' => 'pbc'
		);
	}

	/**
	 * adding assignment specific mata to page object
	 */
	public function check_access($each_component, $vce) {


	
		return true;
	}

	/**
	 * last component was the requested id, so generate links for this component
	 * by default this is a simple html link
	 */
	public function as_link($each_component, $vce) {
		return false;
	}


	/**
	 *
	 */
	public function as_content($each_component, $vce) {

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');

		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','assignment-style');
		

		$content = NULL;
		$content .= <<<EOF
<div>
EOF;

		// get ids of cycles 13 created
		$query = "SELECT a.component_id FROM vce_components_meta AS a JOIN vce_components_meta AS b ON a.component_id=b.component_id AND a.meta_key='created_by' AND a.meta_value='13' AND b.meta_key='type' AND b.meta_value='Pbccycles'";
		$components_meta_data = $vce->db->get_data_object($query);

		$component_list = array();
		foreach ($components_meta_data as $each_meta) {
			// for ($i=1; $i<60; $i++){
				$component_list[] = $each_meta->component_id;
			// }
		}

		$component_list = implode(',', $component_list);


if ($vce->user->role_id == 1) {

	// the instructions to pass through the form
	$dossier = array(
	'type' => 'Pbc_batch_process',
	'procedure' => 'delete_component_list',
	'component_id' => $each_component->component_id,
	'created_at' => $each_component->created_at,
	'parent_url' => $each_component->parent_url
	);
		// generate dossier
		$dossier_for_delete_component_list = $vce->generate_dossier($dossier);

	//notes input 
	$input = array(
		'type' => 'textarea',
		'name' => 'components_to_delete',
		'value' => $component_list,
		'data' => array(
			'rows'=> 5,
			'cols'=> 330,
				'autocapitalize' => 'none',
		),
	);
	$component_ids_input = $vce->content->create_input($input,'Components to Delete');



	$content .= <<<EOF
<form id="delete_$each_component->component_id" class="delete-form float-right-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_delete_component_list">
$component_ids_input
<button type="submit" class="button__primary" >Delete the listed components.</button>

EOF;

}

$content .= <<<EOF
</form>
</div>
EOF;

		$vce->content->add('main',$content);
	}


		/**
	 * delete list of components
	 */
	public function delete_component_list($input) {	

		global $vce;

		$components_to_delete = explode(',', $input['components_to_delete']);

		if (isset($input['url'])) {
			$redirect_url	= $input['url'];
		}

		$i = 1;
		foreach ($components_to_delete as $this_component_id) {

			if (isset($this_component_id) && $this_component_id > 15){

				if ($i > 10) {
					echo json_encode(array('response' => 'success','procedure' => 'delete','action' => 'no_reload','url' => $redirect_url, 'message' => "Deleted 10 but there are more."));
					return;
				}


				$query = "SELECT b.* FROM vce_components_meta AS a JOIN vce_components_meta AS b on a.component_id=b.component_id   AND a.component_id = $this_component_id AND a.meta_key='type' AND a.meta_value='Pbccycles'"; 
				$components_meta_data = $vce->db->get_data_object($query);

			

			if (!empty($components_meta_data)) {
				foreach ($components_meta_data as $each_meta) {
					$component_info[$each_meta->meta_key] = $each_meta->meta_value;
				}	
				$input2 = array(
					'type' => $component_info['type'],
					'procedure' => 'delete',
					'component_id' => $this_component_id,
					'created_at' => $component_info['created_at'],
					'parent_url' => $component_info['parent_url'],
					);
				// $vce->log($input2);
					// exit;
					$parent_url = self::delete_component($input2);

					$i++;
				}
			}
		}
		$redirect_url = '';
				echo json_encode(array('response' => 'success','procedure' => 'delete','action' => 'reload','url' => $redirect_url, 'message' => "Deleted"));
				return;


		


	}

	/**
	 * close div for tab
	 */
	public function as_content_finish($each_component, $vce) {	
// 		$content = <<<EOF
// 		EOF;
// 
// 	
// 		$vce->content->add('postmain',$content);
	
	}

	

        
   
   
   public function delete($input) {

		$parent_url = self::delete_component($input);
		global $site;
// 		$site->log($input);

		if (isset($parent_url)) {
		global $site;
		
			//delete review as well (a review is created automatically when a goal is created, so all reviews should be extirpated)
			if (isset($input['review_ids'])) {
				$review_ids =explode('|', $input['review_ids']);
				foreach($review_ids as $id){
					$input2 = array(
						'component_id' => $id,
						'created_at' => $input['created_at']
					);
// 									$site->log($input2);
						$review_deleted = self::delete_component($input2);
				}
			}
			
			//success, if both goal and review are deleted
			if (isset($review_deleted)) {
				global $site;
				$site->add_attributes('message',self::component_info()['name'] . " Deleted");

				echo json_encode(array('response' => 'success','procedure' => 'delete','action' => 'reload','url' => $parent_url, 'message' => "Deleted"));
				return;
			}
		}

		echo json_encode(array('response' => 'error','procedure' => 'update','message' => "Errors"));
		return;
	
	}
        



	
	/**
	 * 
	 */
	public function recipe_fields($recipe) {
	
		global $site;
		
		global $vce;
	
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