<?php

class Pbc_quick_masquerade extends Component {

	/**
	 * basic info about the component
	 * this component was based on the "Reflection_assignment" component
	 */
	public function component_info() {
		return array(
			'name' => 'Pbc Quick Masquerade',
			'description' => 'Quickly change role ID',
			'category' => 'pbc',
			'recipe_fields' => array('auto_create','title','url')
		);
	}

	
	/**
	 * adding assignment specific meta to page object
	 */
	public function check_access($each_component, $vce) {

		return true;
	}




	/**
	 *
	 */
	public function as_content($each_component, $vce) {
		// return false;

// 		$pre_masquerade_id = $vce->site->retrieve_attributes('pre_masquerade_id');
// 		$masquerade_id = $vce->site->retrieve_attributes('masquerade_id');
// $vce->dump($pre_masquerade_id);
// $vce->dump($masquerade_id);

		// include dirname(__FILE__) . '/../pbc_utilities/utility_login.php';

		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js','jquery-ui');
		// add touch-punch jquery to page
		$vce->site->add_script(dirname(__FILE__) . '/js/jquery.ui.touch-punch.min.js','jquery-touchpunch-ui');
		// add stylesheet to page
		$vce->site->add_style(dirname(__FILE__) . '/css/style.css','assignment-style');
		


		// dossier for change_user_id
		$dossier = array(
			'type' => 'Pbc_quick_masquerade',
			'procedure' => 'change_user_id',
		);
		// generate dossier
		$dossier_change_user_id = $vce->generate_dossier($dossier);

		$content = '<div>This is PBC Quick Masquerade</div><br>';
		$content .= <<<EOF
		<form id="change_id" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
		<input type="hidden" name="dossier" value="$dossier_change_user_id">
EOF;

    // search input
    $input = array(
			'type' => 'text',
			'name' => 'masquerade_id',
			'value' => $vce->user->user_id,
			'data' => array(
					'autocapitalize' => 'none',
			)
	);
	
	$masquerade_input = $vce->content->create_input($input,'Masquerade');
	$content .= <<<EOF
	$masquerade_input
	<input type="submit" value="Masquerade as this ID">
	</form>
EOF;


		// dossier for reset_user_id
		$dossier = array(
			'type' => 'Pbc_quick_masquerade',
			'procedure' => 'reset_user_id',
		);
		// generate dossier
		$dossier_reset_user_id = $vce->generate_dossier($dossier);


		$content .= <<<EOF
		<br><br>
		<form id="reset" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
		<input type="hidden" name="dossier" value="$dossier_reset_user_id">
EOF;


	
	$content .= <<<EOF
	<input type="submit" value="Reset User ID">
	</form>
EOF;



		

		

		$vce->content->add('main',$content);


	}

	/**
	 * close div for tab
	 */
	public function as_content_finish($each_component, $vce) {
	



	
// 		$vce->content->add('postmain',$content);
	
	}



	/**
	 * change user id
	 */
	public function change_user_id($input) {
		global $vce;
		extract($input);
		// $vce->log($masquerade_id);
		$query = "DELETE FROM " . TABLE_PREFIX . "site_meta WHERE meta_key = 'default_id' OR meta_key = 'masquerade_id'";
		// $vce->db->query($query);

		$pre_masquerade_id = $vce->site->retrieve_attributes('pre_masquerade_id');
		// if ($pre_masquerade_id != false) {
		// 	$vce->log($vce->user->role_id);
		// 	$vce->log(isset($pre_masquerade_id));
		// 	$vce->log($pre_masquerade_id);
		// 	$vce->log($masquerade_id);
		// }
		// don't allow quick masquerade capabilities for non-admins unless they are just masquerading as non-admins
		// if ($pre_masquerade_id != false || $vce->user->role_id == 1 || $vce->user->role_id == 4) {
		// 	// $vce->log('clear');
		// } else {
		// 	// $vce->log('error');
		// 	echo json_encode(array('response' => 'success','response_type' => 'no_masquerade', 'form'=>'change_id','action' => 'reload', 'message' => 'You do not have the necessary permission to use the quick-masquerade function.'));
		// 	return;
		// }


	
		// get user_ids from admins, and make masquerading as those impossible
		$query = "SELECT user_id FROM " . TABLE_PREFIX . "users WHERE role_id = 1";
		$quarantine_user_ids = array();
		$admin_user_ids = $vce->db->get_data_object($query);
		foreach ($admin_user_ids as $k => $v) {
			if ($v->user_id != $pre_masquerade_id) {
				$quarantine_user_ids[] = $v->user_id;
			}
		}
// $vce->log($quarantine_user_ids);
		if (!in_array($masquerade_id, $quarantine_user_ids)) {
			if (empty($pre_masquerade_id)) {
				$original_user_id = $vce->user->user_id;
				$vce->site->add_attributes('pre_masquerade_id', $original_user_id, 'TRUE');
			}
			$vce->site->add_attributes('masquerade_id', $masquerade_id, 'TRUE');
			$vce->user->make_user_object($masquerade_id);
		}

		echo json_encode(array('response' => 'success', 'response_type' => 'masquerade', 'form' => 'change_id','action' => 'reload', 'message' => 'User_Id Changed'));
		return;
			
	}



		/**
	 * reset user id
	 */
	public function reset_user_id($input) {
		global $vce;
		
		
		$pre_masquerade_id = $vce->site->retrieve_attributes('pre_masquerade_id');
		$masquerade_id = $vce->site->retrieve_attributes('masquerade_id');
		// $vce->log($pre_masquerade_id);
		if (isset($masquerade_id) && isset($pre_masquerade_id)) {

			
			$vce->site->remove_attributes('masquerade_id');
			$vce->site->remove_attributes('pre_masquerade_id');
			$vce->user->make_user_object($pre_masquerade_id);

			echo json_encode(array('response' => 'success','response_type' => 'success','form' => 'reset','action' => 'reload', 'message' => 'Original ID restored'));
			return;

		}
		

		echo json_encode(array('response' => 'success', 'response_type' => 'error', 'form' => 'reset','action' => '', 'message' => 'There was no masquerade occurring.'));
		return;

			
	}


}