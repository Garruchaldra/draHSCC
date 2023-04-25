<?php

class AnalyticsMigration extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		
		global $vce;
			
		return array(
			'name' => 'Analytics Migration',
			'description' => 'Note: Disable Analytics before activating this component<br><br><a class="link-button" href="' . $vce->site->site_url . '/analytics-migration">Click Here To Migrate After Activation</a>',
			'category' => 'analytics',
			'recipe_fields' => array('auto_create','title',array('url' => 'required'))

		);
	}
	
	
	public function installed() {

		global $vce;
  
		$query = "CREATE TABLE vce_analyticsBAK LIKE vce_analytics";
		$vce->db->query($query);
		
		$query = "INSERT INTO vce_analyticsBAK SELECT * FROM vce_analytics";
		$vce->db->query($query);
		
		$sql = "DROP TABLE IF EXISTS vce_analytics";
		$vce->db->query($sql);
		
	}
	
	/**
	 * This method can be used to route a url path to a specific component method. 
	 */
	public function path_routing() {
	
		$file_directory = 'analytics-migration';

		$path_routing = array(
			$file_directory => array('AnalyticsMigration','do_migration')
		);
		 
		return $path_routing;

	}
	
	public function do_migration() {

		global $vce;

		/*
		vce_analytics to analytics k->v migration script

		Instructions:
		1. suspend the analytics component
		2. rename vce_analytics table to vce_analyticsBAK
		3. copy this script into the top of any page's "as_content" method.
		4. repeatedly reload the page with this script in the "as_content" method. Several thousand arrays will be dumped to the screen as you progress.
		5. When there are no more dumped arrays, the migration is finished. You may have to reload the page over 20 times to get through all the data.
		*/

        $query = "SHOW TABLES LIKE '" . TABLE_PREFIX . "analytics_meta'";
		$table_exists = $vce->db->query($query);
		
		if (empty($table_exists->num_rows)) {

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

		// find migration status
		$query = "SELECT count(*) AS migration_status FROM vce_analytics";
		$result = $vce->db->get_data_object($query);
		//$vce->dump($result);
		foreach($result as $r) {
			$migration_status = isset($r->migration_status)?$r->migration_status : 0;
		}
		$migration_status++;
		// $vce->dump($migration_status);
		// exit;
		
		$query = "SELECT count(*) AS migration_status FROM vce_analyticsBAK";
		$result = $vce->db->get_data_object($query);
		
		foreach($result as $r) {
			$migration_total = isset($r->migration_status) ? $r->migration_status : 0;
		}
		
		// get data from old analytics table, limited to x rows at a time
		$query = "SELECT * from vce_analyticsBAK limit $migration_status, 5000";
		$result = $vce->db->get_data_object($query);

		// create array to format and hold query results
		$result_array = array();
		foreach ($result as $r) {
			foreach ($r as $k=>$v) {
					// use the object member to hold all key->value pairs
					if ($k == 'object') {
						// decode all JSON objects into sub-array
						if ($r->action == 'create' || $r->action == 'delete') {
							$v = json_decode($v, TRUE);
						// for login and view, no JSON decode is necessary; create array and insert value as k->v pair
						} elseif ($r->action == 'login') {
							$v = array(
								'login' => $v
							);
						}  elseif ($r->action == 'view') {
							$v = array(
								'view' => $v
							);
						}
					}
				// convert mysql datetime to unix timestamp
				if ($k == 'timestamp') {
					$timestamp = strtotime($v);
				}
				// add each value per row to $result_array. Object is now an array of all k->v pairs
				$result_array[$r->id][$k] = $v;
			}
			// add unix timestamp to Object k->v pairs
			if (isset($timestamp)) {
				$result_array[$r->id]['object']['created_at'] = $timestamp;
			}
			// add user_id to Object k->v pairs
			if (isset($r->user_id)) {
				$result_array[$r->id]['object']['created_by'] = $r->user_id;
			}
			// add action to Object k->v pairs
			if (isset($r->action)) {
				$result_array[$r->id]['object']['action'] = $r->action;
			}
		}

		foreach ($result_array as $k => $v) {
				$analytics_records = array();
				$analytics_records[0] = array(
				'timestamp' => $v['timestamp'],
				'user_id' => $v['user_id'],
				'session' => $v['session'],
				'component_id' => $v['component_id'],
				);
				$analytics_id = $vce->db->insert('analytics', $analytics_records);
				$analytics_id = $analytics_id[0];
				// $vce->dump($analytics_id);

				$analytics_meta_records = array();
				foreach($v['object'] as $kk => $vv) {
					$analytics_meta_records[] = array(
					'analytics_id' => $analytics_id,
					'meta_key' => $kk,
					'meta_value' => $vv,
					'minutia' => null
					);
				}
				$vce->db->insert('analytics_meta', $analytics_meta_records);
		}

		echo '<pre style="padding: 20px;">';
		if ($migration_status != $migration_total) {
			echo '<div>migration step ' . $migration_status . ' continued</div>';
			echo '<a class="link-button" href="">Continue</a>';
		} else {
			echo '<div>migration completed</div>';
		}
		echo '</pre>';
		
	}
	
}



