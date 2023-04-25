<?php

class Analytics extends Component {

    /**
     * basic info about the component
     */
    public function component_info() {
        return array(
            'name' => 'Analytics',
            'description' => 'Save analytics data into DB table',
            'category' => 'analytics',
            'recipe_fields' => false
        );
    }
    
	public function installed() {

		global $vce;
	
		$query = "CREATE TABLE `" . TABLE_PREFIX . "analytics` (`id` bigint(20) UNSIGNED NOT NULL,`timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,`user_id` bigint(20) UNSIGNED NOT NULL,`session` bigint(20) UNSIGNED NOT NULL,`component_id` bigint(20) UNSIGNED NOT NULL,`action` varchar(255) NOT NULL,`object` text NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8";
		$vce->db->query($query);
	
		$query = "ALTER TABLE `" . TABLE_PREFIX . "analytics` ADD PRIMARY KEY (`id`),ADD KEY `user_id` (`user_id`),ADD KEY `session` (`session`),ADD KEY `component_id` (`component_id`),ADD KEY `action` (`action`)"; 	
		$vce->db->query($query);
	
		$query = "ALTER TABLE `" . TABLE_PREFIX . "analytics` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1";
		$vce->db->query($query);
	
		// get meta_data associated with datalist_id
		$query = "SHOW TABLES LIKE '" . TABLE_PREFIX . "users_log'";
		$table_exists = $vce->db->query($query);
	
		if (isset($table_exists->num_rows) && $table_exists->num_rows > 0) {
	
			// get meta_data associated with datalist_id
			$query = "SELECT * FROM " . TABLE_PREFIX . "users_log";
			$users_log = $vce->db->get_data_object($query);
		
			foreach ($users_log as $each_users_log) {
		
				// $vce->dump($each_users_log);
				$user = json_decode($each_users_log->log_info, true);
			
				// title
				$records[] = array(
				'timestamp' => $each_users_log->log_time,
				'user_id' => $each_users_log->user_id,
				'session' => '0',
				'component_id' => '0',
				'action' => 'login',
				'object' => $user['HTTP_USER_AGENT'],
				);
		
			}
				
			$vce->db->insert('analytics', $records);
	
		}
		
	}
	
	
	public function removed() {
	}

    /**
     * things to do when this component is preloaded
     */
    public function preload_component() {
    	
    	if (isset($this->configuration)) {
    	
			$content_hook = array();
		
			if (isset($this->configuration['record_user_login'])) {
				$content_hook['user_make_user_object'] = 'Analytics::user_make_user_object';
			}
		
			if (isset($this->configuration['record_page_view'])) {
				$content_hook['page_construct_object'] = 'Analytics::page_construct_object';
			}
		
			if (isset($this->configuration['record_component_create'])) {
				$content_hook['create_component_before'] = 'Analytics::create_component_before';
			} 	
		
			if (isset($this->configuration['record_component_update'])) {
				$content_hook['update_component_before'] = 'Analytics::update_component_before';
			} 

			if (isset($this->configuration['record_component_delete'])) {
				$content_hook['delete_extirpate_component'] = 'Analytics::delete_extirpate_component';
			}
			
			if (isset($this->configuration['record_input_handler'])) {
				$content_hook['input_handler'] = 'Analytics::input_handler';
			} 

			return $content_hook;

        }

    }

	public static function create_component_before($input) {
	
		global $vce;
		
		$component_id = isset($input['component_id']) ? $input['component_id'] : 0;
		
		$saved_input = str_replace("'","&#39;", $input);
		
		// title
		$records[] = array(
		'user_id' => $vce->user->user_id,
		'session' => $vce->ilkyo($vce->user->session_vector),
		'component_id' => $component_id,
		'action' => 'create',
		'object' => json_encode($saved_input)
		);
				
		$vce->db->insert('analytics', $records);

	}
	
	public static function update_component_before($input) {
	
		global $vce;
		
		$component_id = isset($input['component_id']) ? $input['component_id'] : 0;
		
		$saved_input = str_replace("'","&#39;", $input);
		
		// title
		$records[] = array(
		'user_id' => $vce->user->user_id,
		'session' => $vce->ilkyo($vce->user->session_vector),
		'component_id' => $component_id,
		'action' => 'update',
		'object' => json_encode($saved_input)
		);
				
		$vce->db->insert('analytics', $records);

	}
	
	public static function delete_extirpate_component($input) {
	
		global $vce;
		
		$component_id = isset($input['component_id']) ? $input['component_id'] : 0;
		
		$saved_input = str_replace("'","&#39;", $input);
		
		// title
		$records[] = array(
		'user_id' => $vce->user->user_id,
		'session' => $vce->ilkyo($vce->user->session_vector),
		'component_id' => $component_id,
		'action' => 'delete',
		'object' => json_encode($saved_input)
		);
				
		$vce->db->insert('analytics', $records);

	}
    
    
	public static function user_make_user_object($user_info, $vce) {
	
		global $vce;
		
		if (!empty($vce->user->user_id)) {
		
			$requested_id = isset($vce->page->requested_id) ? $vce->page->requested_id : 0;

			// title
			$records[] = array(
			'user_id' => $vce->user->user_id,
			'session' => $vce->ilkyo($vce->user->session_vector),
			'component_id' => $requested_id,
			'action' => 'login',
			'object' => $_SERVER['HTTP_USER_AGENT']
			);
		
			$vce->db->insert('analytics', $records);
		
		}
	
	}
    
    
	public static function page_construct_object($component) {
	
		global $vce;
		
		if (isset($vce->user->user_id)) {
		
			// title
			$records[] = array(
			'user_id' => $vce->user->user_id,
			'session' => $vce->ilkyo($vce->user->session_vector),
			'component_id' => $component->component_id,
			'action' => 'view',
			'object' => $_SERVER['REQUEST_URI']
			);
		
			$vce->db->insert('analytics', $records);
		
		}
	
	}
	
	public static function input_handler($dossier, $post_data) {
	
		global $vce;
		
		if (isset($vce->user->user_id)) {
		
			$post_data = str_replace("'","&#39;", $post_data);
		
			$object = array(
			'dossier' => $dossier,
			'post' => $post_data
			);
		
			// title
			$records[] = array(
			'user_id' => $vce->user->user_id,
			'session' => $vce->ilkyo($vce->user->session_vector),
			'component_id' => (isset($dossier['component_id']) ? $dossier['component_id'] : 0),
			'action' => 'input',
			'object' => json_encode($object)
			);
		
			$vce->db->insert('analytics', $records);
		
		}
	
	}
	
	/**
	 * add config info for this component
	 */
	public function component_configuration() {
	
		global $vce;
		
		$elements = null;
		
		$input = array(
		'type' => 'checkbox',
		'name' => 'record_user_login',
		'options' => array(
		'value' => 'on',
		'selected' => ((isset($this->configuration['record_user_login']) && $this->configuration['record_user_login'] == 'on') ? true :  false),
		'label' => 'record user login'
		)
		);
		
		$elements .= $vce->content->input_element($input);
		
		$input = array(
		'type' => 'checkbox',
		'name' => 'record_page_view',
		'options' => array(
		'value' => 'on',
		'selected' => ((isset($this->configuration['record_page_view']) && $this->configuration['record_page_view'] == 'on') ? true :  false),
		'label' => 'record page views'
		)
		);
		
		$elements .= $vce->content->input_element($input);

		$input = array(
		'type' => 'checkbox',
		'name' => 'record_component_create',
		'options' => array(
		'value' => 'on',
		'selected' => ((isset($this->configuration['record_component_create']) && $this->configuration['record_component_create'] == 'on') ? true :  false),
		'label' => 'record component create'
		)
		);
		
		$elements .= $vce->content->input_element($input);

		$input = array(
		'type' => 'checkbox',
		'name' => 'record_component_update',
		'options' => array(
		'value' => 'on',
		'selected' => ((isset($this->configuration['record_component_update']) && $this->configuration['record_component_update'] == 'on') ? true :  false),
		'label' => 'record component update'
		)
		);
		
		$elements .= $vce->content->input_element($input);
		
		$input = array(
		'type' => 'checkbox',
		'name' => 'record_component_delete',
		'options' => array(
		'value' => 'on',
		'selected' => ((isset($this->configuration['record_component_delete']) && $this->configuration['record_component_delete'] == 'on') ? true :  false),
		'label' => 'record component delete'
		)
		);
		
		$elements .= $vce->content->input_element($input);
		
		$input = array(
		'type' => 'checkbox',
		'name' => 'record_input_handler',
		'options' => array(
		'value' => 'on',
		'selected' => ((isset($this->configuration['record_input_handler']) && $this->configuration['record_input_handler'] == 'on') ? true :  false),
		'label' => 'record input handler'
		)
		);
		
		$elements .= $vce->content->input_element($input);
		
		$return = $vce->content->create_input($elements, "Options");
	

		return $return;
		
	
	}


}