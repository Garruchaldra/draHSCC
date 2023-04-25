<?php
class Pbc_config_transfer  extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Config Transfer',
			'description' => 'Config Transfer',
			'category' => 'pbc'
		);
	}
	
	/**
	 * add a hook that fires at initiation of site hooks
	 */
	// public function preload_component() {
		// $content_hook = array (
		// 	'instructions' => 'Pbc_testing::example',
		// );
		// return $content_hook;
	// }


    /**
     *
     */
    public function as_content($each_component, $vce) {

		$content = '<div>Configuration Transfer</div>';
		
		// get config fields
			

// user_attributes

            // create the dossier
            $dossier_for_update = $vce->generate_dossier(array('type' => 'Pbc_config_transfer', 'procedure' => 'update'));

            $content .= <<<EOF
<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_update">
EOF;

		$query = "SELECT meta_key, meta_value FROM " . TABLE_PREFIX . "site_meta";
		$results = $vce->db->get_data_object($query, 1);

		foreach($results as $r) {
			//create inputs from each field
			// $vce->dump($r);
			$content .= <<<EOF
			<div>
			$r->meta_key
				<div>
					<textarea name="$r->meta_key">$r->meta_value</textarea>
				</div>
			</div>
EOF;


		}


		$content .= <<<EOF
		<input type="submit" value="Update Site Meta Table">
		<div class="link-button cancel-button">Cancel</div>
		</form>
EOF;
		$vce->content->add('main', $content);
	
	}



    /**
     * update site_meta table
	 */ 
    public function update($input) {

		global $vce;
		unset($input['type'], $input['procedure'], $input['role_id'], $input['user_id']);
		foreach($input as $key=>$value) {
			$value = html_entity_decode($value);
			$query = "UPDATE " . TABLE_PREFIX . "site_meta SET meta_value = '$value' WHERE meta_key = '$key'";
			$vce->log($query);
			$vce->db->query($query);
		}

//exit;
        echo json_encode(array('response' => 'success', 'message' => 'Site_Meta table Updated', 'form' => 'create', 'action' => ''));
        return;

    }
	
	 /**
	 * hide this component from being added to a recipe
	 */
	public function recipe_fields($recipe) {
		$title = isset($recipe['title']) ? $recipe['title'] : $this->component_info()['name'];
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
