<?php

class nestor_report_summaries_state  extends Nestor_reportsType {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Nestor Report Summaries, State Version',
			'description' => 'adds methods for the summary section of Nestor Reports',
			'category' => 'nestor_reports'
		);
	}

	public function is_valid_timestamp($timestamp) {
    	return ((string) (int) $timestamp === $timestamp) 
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
	}


	// get all users in jurisdiction 
	public function find_users_in_jurisdiction($user, $vce, $get_user_metadata = false) {

		$user_id = $user->user_id;

		$component_name = 'Nestor_reports';

		// get component configuration inforamtion from site object
		$value = $vce->site->$component_name;
		$minutia = $component_name . '_minutia';
		$vector = $vce->site->$minutia;
		$config = json_decode($vce->site->decryption($value, $vector), true);

		$see_all_users = NULL;
		if (isset($config['see_all_users'])) {
			$see_all_users = $config['see_all_users'];
			$see_all_users = explode('|', $see_all_users);
		}


		// get user site role from the configurable site->site_role db entry
		$sr = array();
		foreach (json_decode($vce->site->site_roles, TRUE) as $k=>$v) {
			foreach ($v as $kk=>$vv) {
				$sr[$vv['role_id']] = $k;
			}
		}
		$user_site_role_id = $sr[$user->role_id];
		// $vce->log($user_site_role_id);

		if (in_array($user->role_id, $see_all_users)) {
			// get all users
			$users_info = array('roles' => 'all');
			$all_users = $vce->user->get_users($users_info);
		} else {
			// get users in same partnerships
			$filtered_user_list = array();
			$user_id = $user->user_id;
			$query = "SELECT b.meta_value AS members_list FROM vce_components_meta AS a RIGHT JOIN vce_components_meta AS b ON a.component_id = b.component_id WHERE a.meta_key = 'type' AND a.meta_value IN ('CoachingPartnership') AND b.meta_key = 'pbc_members' AND b. meta_value LIKE '%|$user_id|%'";
			$data = $vce->db->get_data_object($query);
			$user_id_list = array();
			foreach($data as $this_data) {
				$list = trim($this_data->members_list, '|');
				$list = explode('|', $list);
				foreach ($list as $k=>$v) {
					$user_id_list[$v] = NULL;
				}
			}
			foreach ($user_id_list as $k=>$v) {
				$filtered_user_list[] = $k;
			}
			// create comma-delineated list of users
				if (empty($filtered_user_list)) {
					$filtered_user_list[] = $user->user_id;
				}
				$user_list = implode(',', $filtered_user_list);
				$all_users = $vce->user->find_users($user_list);
		}


		// return user object array
		if (isset($get_user_metadata) && $get_user_metadata == true) {
			return $all_users;
		}

		return $user_list;
	}
	
	/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_number_of_comments($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		// $users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE);

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key IN ('type') AND b.meta_value = 'Comments'";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}

		/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_number_of_commenting_users($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		$query = "SELECT count(DISTINCT d.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('Comments') AND d.meta_key IN ('created_by')";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}


		/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_number_of_videos_uploaded($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'media_type' AND b.meta_value IN ('AWSVideo', 'WebDam')";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}

		/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_number_of_users_uploading_videos($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		$query = "SELECT count(DISTINCT d.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'media_type' AND b.meta_value IN ('AWSVideo', 'WebDam') AND d.meta_key IN ('created_by')";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}


		/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_number_of_coaching_partnerships_created($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('CoachingPartnership')";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}

		/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_number_of_coaching_partnerships_marked_as_active($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('CoachingPartnership') AND d.meta_key = 'partnership_status' AND d.meta_value = 'pbc_active'";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}

	/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_number_of_coaching_partnerships_marked_as_archived($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('CoachingPartnership') AND d.meta_key = 'partnership_status' AND d.meta_value = 'pbc_archived'";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}


	/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_shared_goals_created($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('SharedGoals')";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}


		/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_shared_goals_completed($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('SharedGoals') AND d.meta_key = 'status' AND d.meta_value = 'completed'";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}


		/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_action_plans_created($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('ActionPlans') ";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}

			/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_action_plans_marked_as_complete($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('ActionPlans') AND d.meta_key = 'status' AND d.meta_value = 'completed'";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
			// $vce->log($this_data);
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}




	public function summary_role_breakdown($input){

		global $vce;
		extract($input);

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, TRUE);
		// $vce->log($users_in_jurisdiction);
		$users_by_role = array();

		foreach ($users_in_jurisdiction AS $this_user) {

			$users_by_role[$this_user->role_id][] = $this_user->user_id;
		}
		$super_admins = count($users_by_role[1]);
		$administrators = count($users_by_role[2]);
		$coach_leads = count($users_by_role[3]);
		$coaches = count($users_by_role[4]);
		$coachees = count($users_by_role[5]);
		
		
		

		return "Coachees: $coachees, Coaches: $coaches, Coach Leads: $coach_leads, Administrators: $administrators, Super Admins: $super_admins";
	}


	public function summary_role_breakdown_by_county($input){

		global $vce;
		extract($input);

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, TRUE);
		// $vce->log($users_in_jurisdiction);
		$users_by_county = array();

		$attr = array('datalist_id'=>1346);
		$counties = $vce->get_datalist_items($attr);
		// $vce->log($counties);
		$county_array = array();
		foreach($counties['items'] as $k=>$v){
			$county_array[$k] = $v['name'];
		}

		foreach ($users_in_jurisdiction AS $this_user) {
			$county = $county_array[$this_user->county];
			$users_by_county[$county][$this_user->role_id][] = $this_user->user_id;
			$county = NULL;
		}

		$table = <<<EOF
		<table>
			<tr>
				<th>County</th>
				<th>Role Breakdown</th>
	  		</tr>
EOF;

		foreach($users_by_county as $k => $v) {
			$role_display = array();
			$role_display[] = (count($v[1]) > 0)? 'Super Admins: ' . count($v[1]) : NULL;
			$role_display[] = (count($v[2]) > 0)? 'Administrators: ' . count($v[2]) : NULL;
			$role_display[] = (count($v[3]) > 0)? 'Coach Leads: ' . count($v[3]) : NULL;
			$role_display[] = (count($v[4]) > 0)? 'Coaches: ' . count($v[4]) : NULL;
			$role_display[] = (count($v[5]) > 0)? 'Coachees: ' . count($v[5]) : NULL;

			foreach ($role_display as $kk => $vv){
				if ($vv == NULL) {
					unset($role_display[$kk]);
				}
			}

			$k = ($k == '')? 'not specified' : $k;

			$breakdown = implode(', ', $role_display);

		$table .= <<<EOF
	  		<tr>
				<td>$k</td>
				<td>$breakdown</td>
			  </tr>
EOF;
		}
			  
		$table .= <<<EOF
		</table>
EOF;

		return $table;

	}


	public function summary_goals_by_person_and_county($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		// this gets all users with a role-hierarchy lesser than the logged-in user
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, TRUE);

		$users_by_county = array();
		$regexp_search_string = array();

		// create array of counties and their datalist-item ids
		// $attr = array('datalist_id'=>1346);
		$attr = array('name'=>'county');
		$counties = $vce->get_datalist_items($attr);
		$county_array = array();
		foreach($counties['items'] as $k=>$v){
			$county_array[$k] = $v['name'];
		}

		// order users by county with the name of the county as 1st dim id
		// also create a regexp string for use in mysql query with all users in jurisdiction as coachee. This will enable searching in stored JSON objects
		foreach ($users_in_jurisdiction AS $this_user) {
			$county = $county_array[$this_user->county];

			$users_by_county[$county][$this_user->user_id]['first_name'] = $this_user->first_name;
			$users_by_county[$county][$this_user->user_id]['last_name'] = $this_user->last_name;
			$users_by_county[$county][$this_user->user_id]['email'] = $this_user->email;
			$users_by_county[$county][$this_user->user_id]['SharedGoals'] = array();


			$regexp_search_string[] = '"' . $this_user->user_id . '":{"pbc_role":"coachee"}';
			$county = NULL;
		}
		$regexp_search_string = implode('|', $regexp_search_string);

// find all Coaching Partnerships in which has users in jurisdiction have a subrole as coachee
// the component id's will identify the parent id's for SharedGoals within these CoachingPartnerships
		$query = "SELECT a.component_id as component_id FROM vce_components_meta AS a RIGHT JOIN vce_components_meta AS b ON a.component_id = b.component_id INNER JOIN vce_components_meta as c on a.component_id = c.component_id JOIN vce_components_meta as d on a.component_id = d.component_id LEFT JOIN vce_components_meta as e on a.component_id = e.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > 580630400 AND c.meta_value < 1612252800 AND b.meta_key = 'type' AND b.meta_value IN ('CoachingPartnership') AND d.meta_key = 'pbc_roles' AND  d.meta_value REGEXP '$regexp_search_string'";
		// $vce->log($query);
		// exit;		
		$data = $vce->db->get_data_object($query);
		$component_ids = array();
        foreach($data as $this_data) {
			$component_ids[] = $this_data->component_id;
		}
		// create a comma delineated list of component_ids
		$component_ids = implode(',', $component_ids);
		// $vce->log($component_ids);
		// exit;


		// find all shared_goals which have the users in jurisdiction as "coachees" in the parent CoachingPartnership components
		$query = "SELECT a.component_id AS component_id, e.meta_value AS status, d.meta_value AS created_by FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components AS parent on a.component_id = parent.component_id RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id LEFT JOIN " . TABLE_PREFIX . "components_meta as e on a.component_id = e.component_id AND e.meta_key = 'status' WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('SharedGoals') AND d.meta_key = 'created_by' AND parent.parent_id IN ($component_ids)";
		// $vce->log($query);
		// exit;
		$data = $vce->db->get_data_object($query);
		// create an array of users with shared goals, and the status of those goals
		$shared_goals_by_user = array();
        foreach($data as $this_data) {
			$status = ($this_data->status == 'completed') ? 'completed' : 'in_progress';
			$shared_goals_by_user[$this_data->created_by][$status][] = $this_data->component_id;
		}

		// find the name of this summary function
		$div_id = __FUNCTION__;

		// create a table in a div with a class name the same as the name of this method (this is so that the div contents can automatically be printed out as a PDF using JS)
		// this div will be in one field of the summary table
		$table = <<<EOF
		<button class="print-summary-button button__primary" target="$div_id" >Print This Field as PDF</button><br>
		<div id="$div_id">

		<table>
			<tr>
				<th>County</th>
				<th>Number of Shared Goals In-Progress</th>
				<th>Number of Shared Goals Completed</th>
	  		</tr>
EOF;

		// for the output, cycle through all counties, looking for users with recorded shared goals and list by status. Otherwise, list as 0
		foreach($users_by_county as $k => $v) {
			$in_progress = 0;
			$completed = 0;
			$completed_names = array();
			$in_progress_names = array();
			// find counts of in-progress and completed SharedGoals and the names of the people who created them
			foreach($v as $kk => $vv) {
				// $vce->log($vv);
				$ip_count = count($shared_goals_by_user[$kk]['in_progress']);
				$c_count = count($shared_goals_by_user[$kk]['completed']);
				$in_progress += $ip_count;
				$completed += $c_count;
				
				if ($ip_count > 0) {
					$in_progress_names[] = $vv['first_name'] . ' ' . $vv['last_name'];
				}
				if ($c_count > 0) {
					$completed_names[] = $vv['first_name'] . ' ' . $vv['last_name'];
				}
			}
			// if Shared goals exist for this county, make a list of names
			$in_progress_names = (count($in_progress_names) > 0) ? '(<small>' . implode(', ', $in_progress_names) . '</small>)' : NULL;
			$completed_names = (count($completed_names) > 0) ? '(<small>' . implode(', ', $completed_names) . '</small>)' : NULL;

			$k = ($k == '')? 'not specified' : $k;


		// create table content
		$table .= <<<EOF
	  		<tr>
				<td>$k</td>
				<td>$in_progress $in_progress_names</td>
				<td>$completed $completed_names</td>
			  </tr>
EOF;
		}
			  
		$table .= <<<EOF
		</table>
		</div>
EOF;

		return $table;

	}



	/**
	 * meta_key_translations_ are used to supply a lookup array for 
	 * any build which gives human-readable names to meta_keys of the components_meta table
	 * 
	 */
	public function meta_key_translations_hscc(){

		global $vce;

		$translations_array = array(
'action_plan_goal' => 'Goal',
'alias_id' => 'ID of Media Alias',
'ap_id' => 'ID of Action Plan',
'ap_step_id' => 'ID of Action Plan Step',
'aps_assignee' => 'Assignee IDs of Action Plan Step',
'assignment_category' => 'Assignment Category',
'comments' => 'Comments',
'content_create' => NULL,
'content_delete' => NULL,
'content_edit' => NULL,
'created_at' => 'Created At',
'created_by' => 'Created By',
'cycle_participants' => 'Cycle Participants',
'date' => 'Date',
'description' => 'Description',
'duration' => 'Duration',
'email' => 'Email',
'end_date' => 'End Date',
'first_name' => 'First Name',
'fo_id' => 'ID of Focused Observation',
'focus' => 'Focus',
'goal_achievement_evidence' => 'Evidence of Goal Achievement',
'group' => 'Group',
'guid' => 'Guid',
'last_name' => 'Last Name',
'link' => 'Link',
'list_order' => 'List Order',
'media_type' => 'Media Type',
'mediaAmp_id' => 'MediaAmp ID',
'name' => 'Name',
'not_saved_directly_aps_assignee' => NULL,
'not_saved_directly_cycle_participants' => NULL,
'not_saved_directly_observed' => NULL,
'not_saved_directly_observers' => NULL,
'observed' => 'Observed',
'observers' => 'Observers',
'organization' => 'Organization',
'original_id' => NULL,
'original_taxonomy' => 'Original Taxonomy',
'originator' => 'Originator',
'originator_id' => 'ID of Originator',
'password' => 'Password',
'path' => 'Path',
'pbc_cycles_id' => 'ID of Cycle',
'pbccycle_begins' => 'Cycle Begin Date',
'pbccycle_name' => 'Cycle Name',
'pbccycle_review' => 'Cycle Review',
'pbccycle_status' => 'Cycle Status',
'preparation_notes' => 'Preparation Notes',
'progress' => 'Progress',
'published' => 'Published',
'recipe' => 'Recipe',
'recipe_name' => 'Recipe Name',
'redirect_url' => 'Redirect Url',
'review_sibling_id' => 'ID of Review Sibling',
'rf_id' => 'RF ID',
'role_access' => 'Role Access',
'role_id' => 'Role ID',
'start_date' => 'Start Date',
'step_comments' => 'Comments on Action Plan Step',
'sub_roles' => NULL,
'taxonomy' => 'Taxonomy',
'taxonomy2' => NULL,
'template' => NULL,
'text' => NULL,
'thumbnail_url' => 'Thumbnail URL',
'timestamp' => 'Timestamp',
'title' => 'Title',
'type' => 'Type',
'updated_at' => 'Updated At',
'user_access' => 'User Access',
'user_id' => 'User ID',
'user_ids_aps_assignee' => 'Action Plan Step Assignee IDs',
'user_ids_cycle_participants' => 'Cycle Participant IDs',
'user_ids_observed' => 'Focused Observation Observed User IDs',
'user_ids_observers' => 'Focused Observation Observing User IDs',
'user_oldids_aps_assignee' => NULL,
'user_oldids_cycle_participants' => NULL,
'user_oldids_observed' => NULL,
'user_oldids_observers' => NULL
);

		return $translations_array;
	}
	


		/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_number_and_type_of_media_uploads($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		$query = "SELECT d.meta_value AS media_type FROM vce_components_meta AS a RIGHT JOIN vce_components_meta AS b ON a.component_id = b.component_id INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND b.meta_key = 'type' AND b.meta_value = 'Media' AND d.meta_key = 'media_type' GROUP BY d.meta_value";
		$data = $vce->db->get_data_object($query);
		$media_types = array();
        foreach($data as $this_data) {
			$media_types[] = $this_data->media_type;
		 }


		$media_counts = NULL;
		 $query = "SELECT count(d.meta_value) AS sum FROM vce_components_meta AS a RIGHT JOIN vce_components_meta AS b ON a.component_id = b.component_id INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND b.meta_key = 'type' AND b.meta_value = 'Media' AND d.meta_key = 'media_type'";
		 $data = $vce->db->get_data_object($query);
		 foreach($data as $this_data) {
			 $media_counts .= 'Total Uploads: '.$this_data->sum.'. Breakdown: ';
		  }
		
		 
		 foreach ($media_types as $k=>$v) {
			$query = "SELECT count(d.meta_value) AS sum FROM vce_components_meta AS a RIGHT JOIN vce_components_meta AS b ON a.component_id = b.component_id INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND b.meta_key = 'type' AND b.meta_value = 'Media' AND d.meta_key = 'media_type' AND d.meta_value = '$v'";
			$data = $vce->db->get_data_object($query);
			foreach($data as $this_data) {
				$media_counts .= ucfirst($v).': '.$this_data->sum.', ';
			}
		}

		return trim($media_counts, ', ');

	}

	/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_number_and_type_of_resources($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = strtotime($end_date);
		}

		$query = "SELECT aa.meta_value AS id FROM vce_components_meta AS aa  WHERE aa.meta_key = 'Alias_id'";
		$data = $vce->db->get_data_object($query);
		$alias_ids = array();
        foreach($data as $this_data) {
			$alias_ids[] = $this_data->id;
		 }
		 if (count($alias_ids) > 0) {
			$alias_ids = implode(',', $alias_ids);
		 } else {
			$alias_ids = 0;
		 }
		 $query = "DELETE FROM vce_components_meta WHERE meta_value like '%{template_component_id}%'";
		 $vce->db->query($query);
		$query = "SELECT d.meta_value AS media_type FROM vce_components_meta AS a INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND a.meta_value > $start_date AND c.meta_value < $end_date  AND a.component_id IN ($alias_ids) AND d.meta_key = 'media_type' GROUP BY d.meta_value";
		// $vce->log($query);
		$data = $vce->db->get_data_object($query);
		$media_types = array();
        foreach($data as $this_data) {
			$media_types[] = $this_data->media_type;
		 }


		$media_counts = NULL;
		 $query = "SELECT count(d.meta_value) AS sum FROM vce_components_meta AS a INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND a.component_id IN ($alias_ids) AND d.meta_key = 'media_type'";
		 $data = $vce->db->get_data_object($query);
		 foreach($data as $this_data) {
			 $media_counts .= 'Total Resources Used: '.$this_data->sum.'. Breakdown: ';
		  }
		
		 
		 foreach ($media_types as $k=>$v) {
			$query = "SELECT count(d.meta_value) AS sum FROM vce_components_meta AS a  INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND a.component_id IN ($alias_ids) AND d.meta_key = 'media_type' AND d.meta_value = '$v'";
			$data = $vce->db->get_data_object($query);
			foreach($data as $this_data) {
				$media_counts .= ucfirst($v).': '.$this_data->sum.', ';
			}
		}

		return trim($media_counts, ', ');

	}

}