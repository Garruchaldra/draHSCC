<?php

class AnalyticsRecorder extends Component {

    /**
     * basic info about the component
     */
    public function component_info() {
        return array(
            'name' => 'Analytics Recorder',
            'description' => 'Save analytics data into DB table',
            'category' => 'analytics',
            'recipe_fields' => false
        );
    }
    
	public function installed() {

		global $vce;
  
		$query = "CREATE TABLE " . TABLE_PREFIX . "analytics (id bigint(20) UNSIGNED NOT NULL,timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, user_id bigint(20) UNSIGNED NOT NULL, session bigint(20) UNSIGNED NOT NULL, component_id bigint(20) UNSIGNED NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$vce->db->query($query);
	
		$query = "ALTER TABLE " . TABLE_PREFIX . "analytics ADD PRIMARY KEY (id), ADD KEY component_id (component_id), ADD KEY user_id (user_id), ADD KEY session (id);";
		$vce->db->query($query);
	
		$query = "ALTER TABLE " . TABLE_PREFIX . "analytics MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1";
		$vce->db->query($query);

		$query = "CREATE TABLE " . TABLE_PREFIX . "analytics_meta (id bigint(20) UNSIGNED NOT NULL, analytics_id bigint(20) UNSIGNED NOT NULL, meta_key varchar(255) COLLATE utf8_unicode_ci NOT NULL, meta_value text COLLATE utf8_unicode_ci NOT NULL, minutia varchar(255) COLLATE utf8_unicode_ci NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$vce->db->query($query);
	
		$query = "ALTER TABLE " . TABLE_PREFIX . "analytics_meta ADD PRIMARY KEY (id), ADD KEY meta_key (meta_key), ADD KEY event_id (analytics_id), ADD KEY session (id);";
		$vce->db->query($query);
	
		$query = "ALTER TABLE " . TABLE_PREFIX . "analytics_meta MODIFY id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1";
		$vce->db->query($query);
		
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
				$content_hook['user_make_user_object'] = 'AnalyticsRecorder::user_make_user_object';
			}
		
			if (isset($this->configuration['record_page_view'])) {
				$content_hook['page_construct_object'] = 'AnalyticsRecorder::page_construct_object';
			}
		
			if (isset($this->configuration['record_component_create'])) {
				$content_hook['create_component_before'] = 'AnalyticsRecorder::create_component_before';
			} 	
		
			if (isset($this->configuration['record_component_update'])) {
				$content_hook['update_component_before'] = 'AnalyticsRecorder::update_component_before';
			} 

			if (isset($this->configuration['record_component_delete'])) {
				$content_hook['delete_extirpate_component'] = 'AnalyticsRecorder::delete_extirpate_component';
			} 

			return $content_hook;

        }

    }

	public static function create_component_before($input) {
	
		global $vce;
		
		$configuration = self::get_config($vce);
	
		if (isset($configuration['ignore_role']) && in_array($vce->user->role_id, explode('|', $configuration['ignore_role']))) {
			return;
		}

		$component_id = isset($input['component_id']) ? $input['component_id'] : 0;
		
		// title
		$records[] = array(
			'user_id' => $vce->user->user_id,
			'session' => $vce->ilkyo($vce->user->session_vector),
			'component_id' => $component_id,
		);
		$analytics_id = $vce->db->insert('analytics', $records);
		if (isset($analytics_id)) {
			$analytics_id = $analytics_id[0];
		}

		$analytics_meta_records = array();
		$analytics_meta_records[] = array(
			'analytics_id' => $analytics_id,
			'meta_key' => 'action',
			'meta_value' => 'create',
			'minutia' => null
		);
		foreach ($input as $k => $v) {
			$analytics_meta_records[] = array(
				'analytics_id' => $analytics_id,
				'meta_key' => $k,
				'meta_value' => $v,
				'minutia' => null
			);
		}
		$vce->db->insert('analytics_meta', $analytics_meta_records);

	}
	
	public static function update_component_before($input) {
	
		global $vce;

		$configuration = self::get_config($vce);
	
		if (isset($configuration['ignore_role']) && in_array($vce->user->role_id, explode('|', $configuration['ignore_role']))) {
			return;
		}

		$component_id = isset($input['component_id']) ? $input['component_id'] : 0;
		
		// title
		$records[] = array(
			'user_id' => $vce->user->user_id,
			'session' => $vce->ilkyo($vce->user->session_vector),
			'component_id' => $component_id,
		);
		$analytics_id = $vce->db->insert('analytics', $records);
		if (isset($analytics_id)) {
			$analytics_id = $analytics_id[0];
		}

		$analytics_meta_records = array();
		$analytics_meta_records[] = array(
			'analytics_id' => $analytics_id,
			'meta_key' => 'action',
			'meta_value' => 'update',
			'minutia' => null
		);
		foreach ($input as $k => $v) {
			$analytics_meta_records[] = array(
				'analytics_id' => $analytics_id,
				'meta_key' => $k,
				'meta_value' => $v,
				'minutia' => null
			);
		}
		$vce->db->insert('analytics_meta', $analytics_meta_records);

	}
	
	public static function delete_extirpate_component($input) {
	
		global $vce;
		
		$configuration = self::get_config($vce);
	
		if (isset($configuration['ignore_role']) && in_array($vce->user->role_id, explode('|', $configuration['ignore_role']))) {
			return;
		}


		$component_id = isset($input['component_id']) ? $input['component_id'] : 0; 
		
		// title
		$records[] = array(
			'user_id' => $vce->user->user_id,
			'session' => $vce->ilkyo($vce->user->session_vector),
			'component_id' => $component_id,
		);
		$analytics_id = $vce->db->insert('analytics', $records);
		if (isset($analytics_id)) {
			$analytics_id = $analytics_id[0];
		}

		$analytics_meta_records = array();
		$analytics_meta_records[] = array(
			'analytics_id' => $analytics_id,
			'meta_key' => 'action',
			'meta_value' => 'delete',
			'minutia' => null
		);
		foreach ($input as $k => $v) {
			$analytics_meta_records[] = array(
				'analytics_id' => $analytics_id,
				'meta_key' => $k,
				'meta_value' => $v,
				'minutia' => null
			);
		}
		$vce->db->insert('analytics_meta', $analytics_meta_records);

	}
    
    
	public static function user_make_user_object($user_info, $vce) {
	
		global $vce;

		$configuration = self::get_config($vce);
	
		if (isset($configuration['ignore_role']) && in_array($vce->user->role_id, explode('|', $configuration['ignore_role']))) {
			return;
		}

		$requested_id = isset($vce->page->requested_id) ? $vce->page->requested_id : 0;

		// title
		$records[] = array(
			'user_id' => $vce->user->user_id,
			'session' => $vce->ilkyo($vce->user->session_vector),
			'component_id' => $requested_id,
		);
		$analytics_id = $vce->db->insert('analytics', $records);
		if (isset($analytics_id)) {
			$analytics_id = $analytics_id[0];
		}

		$analytics_meta_records = array();
		$analytics_meta_records[] = array(
			'analytics_id' => $analytics_id,
			'meta_key' => 'action',
			'meta_value' => 'login',
			'minutia' => null
		);
		$analytics_meta_records[] = array(
			'analytics_id' => $analytics_id,
			'meta_key' => 'login',
			'meta_value' => $_SERVER['HTTP_USER_AGENT'],
			'minutia' => null
		);
		$vce->db->insert('analytics_meta', $analytics_meta_records);
	}
    
    
	public static function page_construct_object($component) {
	
		global $vce;

		if (isset($vce->user->user_id)) {
		
			$configuration = self::get_config($vce);
		
			if (isset($configuration['ignore_role']) && in_array($vce->user->role_id, explode('|', $configuration['ignore_role']))) {
				return;
			}
		
			// title
			$records[] = array(
				'user_id' => $vce->user->user_id,
				'session' => $vce->ilkyo($vce->user->session_vector),
				'component_id' => $component->component_id,
			);
			$analytics_id = $vce->db->insert('analytics', $records);
			if (isset($analytics_id)) {
				$analytics_id = $analytics_id[0];
			}

			$analytics_meta_records = array();
			$analytics_meta_records[] = array(
				'analytics_id' => $analytics_id,
				'meta_key' => 'action',
				'meta_value' => 'view',
				'minutia' => null
			);
			$analytics_meta_records[] = array(
				'analytics_id' => $analytics_id,
				'meta_key' => 'view',
				'meta_value' => $_SERVER['REQUEST_URI'],
				'minutia' => null
			);
			$vce->db->insert('analytics_meta', $analytics_meta_records);
		
		}
	
	}
	
	/**
	 * add config info for this component
	 */
	public function component_configuration() {
	
		global $vce;
		
		$elements = null;
		
		
		$elements .= '<div>Do not recorder analytics for the following user roles:</div>';
		
		$input = array(
		'type' => 'checkbox',
		'name' => 'ignore_role',
		'selected' => (isset($this->configuration['ignore_role']) ? explode('|', $this->configuration['ignore_role']) : null),
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
		
		$elements .= '<br><div>Recorder analytics for the following actions:</div>';
		
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
		
		$return = $vce->content->create_input($elements, "Options");
	

		return $return;
		
	
	}


}