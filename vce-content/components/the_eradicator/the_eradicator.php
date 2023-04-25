<?php

class TheEradicator extends Component {

    /**
     * basic info about the component
     */
    public function component_info() {
        return array(
            'name' => 'The Eradicator',
            'description' => 'Remove all users and their generated content from a site',
            'category' => 'admin',
            'recipe_fields' => array('auto_create','title',array('url' => 'required'))
        );
    }

    /**
     *
     */
    public function as_content($each_component, $vce) {
    
    
		// site roles
		$roles = json_decode($vce->site->site_roles, true);
		
		// dossier for invite
		$dossier = array(
		'type' => 'TheEradicator',
		'procedure' => 'eradicate'
		);

		// generate dossier
		$dossier = $vce->generate_dossier($dossier);
		
		foreach ($roles as $each_key=>$each_role) {
		
			if ($each_key == 0) {
				continue;
			}
			
			$role = array_values($each_role)[0];
			
			$site_roles[] = array(
				'value' => $role['role_id'],
				'label' => $role['role_name']
			);
			
		}
		
		$input = array(
			'type' => 'checkbox',
			'name' => 'roles',
			'disabled' => 1,
			'options' => $site_roles,
			'flags' => array('label_tag_wrap' => true)
		);
		
		$roles = $vce->content->create_input($input,'test');
    
    
		$content = <<<EOF
<p>"Tuesday is no good for The Eradicator"</p>
<p>
<iframe id="player-$each_component->component_id"
src="https://www.youtube.com/embed/HO0FxifkzFQ"
frameborder="0"
style="border: solid 4px #37474F"
class="player"
></iframe>
</p>
<form id="edit-group" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier">
$roles
<input type="submit" value="Eradicate Users">
</form>
EOF;

		$vce->content->add('main',$content);
		
	}
	
	
	
	public function eradicate($input) {
	
		global $vce;
		
		$roles = array();
		
		foreach ($input as $key=>$value) {
		
			$test = strpos($key, 'roles_');

			if ($test !== false) {
				$roles[] = $value;
			}
		
		}
		
		$query = "SELECT * FROM  " . TABLE_PREFIX . "users WHERE role_id IN (" . implode(',',$roles) . ")"; 
		$users = $vce->db->get_data_object($query);
		
		foreach($users as $each_user) {
		
			
			$query = "SELECT * FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components AS b ON b.component_id=a.component_id WHERE a.component_id IN (SELECT component_id FROM  " . TABLE_PREFIX . "components_meta WHERE meta_key='created_by' AND meta_value='" . $each_user->user_id . "')"; 
			$assocatied_components = $vce->db->get_data_object($query, false);
			
			$components = $vce->page->assemble_component_objects($assocatied_components, $vce);
			
			if (!empty($components)) {
				foreach($components as $each_component) {
					$input = (array) $each_component;
					unset($input['configuration']);
					$each_component->delete($input);
				}
			}
			
			$query = "SELECT * FROM  " . TABLE_PREFIX . "datalists WHERE user_id='" . $each_user->user_id . "'"; 
			$datalists = $vce->db->get_data_object($query);
			
			if (!empty($datalists)) {
				foreach($datalists as $each) {
					$vce->remove_datalist(array('datalist_id' => $each->datalist_id));
				}
			}
			
			
			$where = array('user_id' => $each_user->user_id);
			$vce->db->delete('users_meta', $where);
			
			$where = array('user_id' => $each_user->user_id);
			$vce->db->delete('users', $where);
		
		}
		
		echo json_encode(array('response' => 'sucess','procedure' => 'update','message' => "Eraticated!"));
		return;
    
    }
 

}