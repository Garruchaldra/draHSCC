<?php

class nestor_report_summaries_HSCC  extends Nestor_reportsType {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Nestor Report Summaries for the HSCC',
			'description' => 'adds methods for the summary section of Nestor Reports',
			'category' => 'nestor_reports'
		);
	}

	public function is_valid_timestamp($timestamp) {
		// global $vce;
		// // $timestamp = $timestamp + 
    	return ((string) (int) $timestamp === $timestamp) 
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
	}


	// get all users in jurisdiction 
	public function find_users_in_jurisdiction($user, $vce, $get_user_metadata = false,  $users_filter = NULL) {

		// if ($get_user_metadata == false && isset($vce->users_in_jurisdiction_list)) {
		// 	return $vce->users_in_jurisdiction_list;

		// }
		// if ($get_user_metadata == TRUE && isset($vce->users_in_jurisdiction_array)) {
		// 	return unserialize($vce->users_in_jurisdiction_array);

		// }

		// only use those roles which have been selected
		if (isset($users_filter)) {
			$users_info = array('roles' => $users_filter);
		} else {
			$users_info = array('roles' => 'all');
		}

		switch ($user->role_hierarchy) {
			case 1:
			case 2:
				// get all users
				// $users_info = array('roles' => '2,3,4,5,6,7');
				$all_users = $vce->user->get_users($users_info);
				break;
			case 3:
			//get users in same organization
				$users_info = array('roles' => 'all');
				$all_users = $vce->user->get_users($users_info);
				foreach ($all_users as $key=>$value) {
					if (!isset($value->organization) || $value->organization != $vce->user->organization) {
						unset($all_users[$key]);
					}
				}
				break;
			case 4:
			// get users in same group
				$users_info = array('roles' => 'all');
				$all_users = $vce->user->get_users($users_info);
				foreach ($all_users as $key=>$value) {
					if (!isset($value->group) || $value->group != $vce->user->group) {
						unset($all_users[$key]);
					}
				}
				break;
			case 5:
				$all_users = array();
				break;
			case 6:
				$all_users = array();
				break;
			default:
				$all_users = array();
		}


		//remove test users
		
		$test_users = array();
		if (!isset($vce->site->tester_toggle) || $vce->site->tester_toggle != 'on') {
			$query = "SELECT user_id FROM " . TABLE_PREFIX . "users_meta where meta_key='tester' and meta_value='TRUE'";
			$data = $vce->db->get_data_object($query);
			foreach($data as $this_data) {
				$test_users[] = $this_data->user_id;
			}
		}

		foreach ($all_users as $k=>$v) {
			if (in_array($v->user_id, $test_users)) {
				unset($all_users[$k]);
			}
		}

		// return user object array
		if ($get_user_metadata == true) {
			$all_users_serialized = serialize($all_users);
			$vce->users_in_jurisdiction_array = $all_users_serialized;
			// $vce->site->add_attributes('users_in_jurisdiction_array',$all_users_serialized);
			return $all_users;
		}

		// create comma-delineated list of users
		$user_list = array();
		foreach ($all_users as $this_user) {
			$user_list[] = $this_user->user_id;
		}
		if (empty($user_list)) {
			$user_list[] = $user->user_id;
		}
		$user_list = implode(',', $user_list);

		$vce->users_in_jurisdiction_list = $user_list;
		// $vce->site->add_attributes('users_in_jurisdiction_list',$user_list);
		return $user_list;
	}

		/**
	 * Summary method
	 * a method which is included in the summary must start with the string "summary_"
	 * the output will be inserted in a field like this: <td>$output</td>
	 * the name of the method (minus "summary_") will be used as the heading in the summary table
	 */
	public function summary_total_number_of_users($input){

		global $vce;
		// $vce->log($input);
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, TRUE, $users_filter);

		foreach ($users_in_jurisdiction AS $k => $v) {
			$v->created_at = (isset($v->created_at)) ? $v->created_at : 0;
			if ($v->created_at < $start_date || $v->created_at > $end_date ) {
				unset($users_in_jurisdiction[$k]);
			}
		}

        // $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
        // $data = $vce->db->get_data_object($query);
        // foreach($data as $this_data) {
        //     $total_users = $this_data->total;
        // }

		return count($users_in_jurisdiction);
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
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id  WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('Comments')  AND d.meta_key IN ('created_by') AND d.meta_value IN ($users_in_jurisdiction)";
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
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
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);


		$query = "SELECT count(DISTINCT d.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('Comments') AND d.meta_key IN ('created_by') AND d.meta_value IN ($users_in_jurisdiction)";
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
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
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND b.meta_value IN ('VimeoVideo') AND d.meta_key IN ('created_by') AND d.meta_value IN ($users_in_jurisdiction)";
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
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
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);


		$query = "SELECT count(DISTINCT d.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND b.meta_value IN ('VimeoVideo') AND d.meta_key IN ('created_by') AND d.meta_value IN ($users_in_jurisdiction)";
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
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
	public function summary_total_number_of_cycles_created($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);


		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('Pbccycles')  AND d.meta_key IN ('created_by') AND d.meta_value IN ($users_in_jurisdiction)";
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
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
	public function summary_total_number_of_cycles_marked_as_complete($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);


		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id  JOIN " . TABLE_PREFIX . "components_meta as e on a.component_id = e.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('Pbccycles') AND d.meta_key = 'pbccycle_status' AND d.meta_value = 'Complete'  AND e.meta_key IN ('created_by') AND e.meta_value IN ($users_in_jurisdiction)";
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
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
	public function summary_total_focused_observations_created($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);


		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('Pbc_focused_observation')  AND d.meta_key IN ('created_by') AND d.meta_value IN ($users_in_jurisdiction)";
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
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
	public function summary_total_action_plan_steps_created($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as e on a.component_id = e.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('Pbc_step')  AND e.meta_key IN ('created_by') AND e.meta_value IN ($users_in_jurisdiction)";
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
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
	public function summary_total_action_plan_steps_marked_as_complete($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);


		// $query = "SELECT count(*) AS total FROM " . TABLE_PREFIX . "users";
		$query = "SELECT count(b.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id JOIN " . TABLE_PREFIX . "components_meta as e on a.component_id = e.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date AND b.meta_key = 'type' AND b.meta_value IN ('Pbc_step') AND d.meta_key = 'pbccycle_status' AND d.meta_value = 'Complete'  AND e.meta_key IN ('created_by') AND e.meta_value IN ($users_in_jurisdiction)";
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
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
	public function summary_total_number_of_users_creating_cycles($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);


		$query = "SELECT count(DISTINCT d.meta_value) AS sum FROM " . TABLE_PREFIX . "components_meta AS a RIGHT JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id INNER JOIN " . TABLE_PREFIX . "components_meta as d on a.component_id = d.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND b.meta_value IN ('Pbccycles') AND d.meta_key IN ('created_by') AND d.meta_value IN ($users_in_jurisdiction)";
		$data = $vce->db->get_data_object($query);
        foreach($data as $this_data) {
            $sum = $this_data->sum;
        }

		$sum = ($sum > 0) ? $sum : 0;
		return $sum;
	}

	// public function summary_role_breakdown($input){

	// 	global $vce;
	// 	extract($input);

    //     $query = "SELECT count(*) AS sum FROM " . TABLE_PREFIX . "users WHERE role_id = 3";
    //     $data = $vce->db->get_data_object($query);
    //     foreach($data as $this_data) {
	// 		$this_data->sum = ($this_data->sum > 0) ? $this_data->sum : 0;
    //         $coachees = $this_data->sum;
	// 	}
	// 	$query = "SELECT count(*) AS sum FROM " . TABLE_PREFIX . "users WHERE role_id = 2";
    //     $data = $vce->db->get_data_object($query);
    //     foreach($data as $this_data) {
	// 		$this_data->sum = ($this_data->sum > 0) ? $this_data->sum : 0;
    //         $coaches = $this_data->sum;
	// 	}
	// 	$query = "SELECT count(*) AS sum FROM " . TABLE_PREFIX . "users WHERE role_id = 6";
    //     $data = $vce->db->get_data_object($query);
    //     foreach($data as $this_data) {
	// 		$this_data->sum = ($this_data->sum > 0) ? $this_data->sum : 0;
    //         $group_admins = $this_data->sum;
	// 	}
	// 	$query = "SELECT count(*) AS sum FROM " . TABLE_PREFIX . "users WHERE role_id = 5";
    //     $data = $vce->db->get_data_object($query);
    //     foreach($data as $this_data) {
	// 		$this_data->sum = ($this_data->sum > 0) ? $this_data->sum : 0;
    //         $org_admins = $this_data->sum;
	// 	}
		

	// 	return "Coachees: $coachees, Coaches: $coaches, Group Admins: $group_admins, Organization Admins: $org_admins";
	// }

	public function summary_role_breakdown($input){

		global $vce;
		extract($input);

		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, TRUE, $users_filter);
		$users_by_role = array();

		$user_roles = json_decode($vce->site->roles, TRUE);

		foreach ($users_in_jurisdiction AS $this_user) {
			$this_user->created_at = (isset($this_user->created_at )) ? $this_user->created_at : 0;
			if ($this_user->created_at < $start_date || $this_user->created_at > $end_date ) {
				continue;
			}
			$user_roles[$this_user->role_id]['role_name'] = (isset($user_roles[$this_user->role_id]['role_name'])) ? $user_roles[$this_user->role_id]['role_name'] : 'unassigned';
			$users_by_role[$user_roles[$this_user->role_id]['role_name']][] = $this_user->user_id;

		}

		$return_string = NULL;
		foreach($users_by_role as $k=>$v) {
			$k = ucfirst(str_replace('_', ' ', $k));
			$v = count($v);
			$return_string .= "$k: $v, ";
		}

		$return_string = trim($return_string, ', ' );
		return $return_string;

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
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		$query = "SELECT d.meta_value AS media_type FROM vce_components_meta AS a RIGHT JOIN vce_components_meta AS b ON a.component_id = b.component_id INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id JOIN vce_components_meta as e on a.component_id = e.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND b.meta_key = 'type' AND b.meta_value = 'Media' AND d.meta_key = 'media_type'  AND e.meta_key IN ('created_by') AND e.meta_value IN ($users_in_jurisdiction) GROUP BY d.meta_value";
		$data = $vce->db->get_data_object($query);
		$media_types = array();
        foreach($data as $this_data) {
			$media_types[] = $this_data->media_type;
		 }


		$media_counts = NULL;
		 $query = "SELECT count(d.meta_value) AS sum FROM vce_components_meta AS a RIGHT JOIN vce_components_meta AS b ON a.component_id = b.component_id INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id JOIN vce_components_meta as e on a.component_id = e.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND b.meta_key = 'type' AND b.meta_value = 'Media' AND d.meta_key = 'media_type'  AND e.meta_key IN ('created_by') AND e.meta_value IN ($users_in_jurisdiction)";
		 $data = $vce->db->get_data_object($query);
		 foreach($data as $this_data) {
			 $media_counts .= 'Total Uploads: '.$this_data->sum.'. Breakdown: ';
		  }
		
		 
		 foreach ($media_types as $k=>$v) {
			$query = "SELECT count(d.meta_value) AS sum FROM vce_components_meta AS a RIGHT JOIN vce_components_meta AS b ON a.component_id = b.component_id INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id JOIN vce_components_meta as e on a.component_id = e.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND b.meta_key = 'type' AND b.meta_value = 'Media' AND d.meta_key = 'media_type' AND d.meta_value = '$v'  AND e.meta_key IN ('created_by') AND e.meta_value IN ($users_in_jurisdiction)";
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
	public function summary_total_number_and_type_of_library_resources_used($input){

		global $vce;
		extract($input);


		$start_date_test = $this->is_valid_timestamp($start_date);
		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		}
		$end_date_test = $this->is_valid_timestamp($end_date);
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		}

	

		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// get all alias components
		$query = "SELECT aa.meta_value AS id FROM vce_components_meta AS aa  WHERE aa.meta_key = 'Alias_id' AND aa.meta_value NOT LIKE '%template%'";
		$data = $vce->db->get_data_object($query);
		$alias_ids = array();
        foreach($data as $this_data) {
			if (!in_array($this_data->id, $alias_ids) && !empty($this_data->id)) {
				$alias_ids[] = $this_data->id;
			}
		 }
		 if (count($alias_ids) > 0) {
			$alias_ids = implode(',', $alias_ids);
		 } else {
			$alias_ids = 0;
		 }
		 // get rid of test remains
		 $query = "DELETE FROM vce_components_meta WHERE meta_value like '%{template_component_id}%'";
		//  $vce->db->query($query);


		$query = "SELECT d.meta_value AS media_type FROM vce_components_meta AS a INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id JOIN vce_components_meta as e on a.component_id = e.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND a.meta_value > $start_date AND c.meta_value < $end_date  AND a.component_id IN ($alias_ids) AND d.meta_key = 'media_type'  AND e.meta_key IN ('created_by') AND e.meta_value IN ($users_in_jurisdiction) GROUP BY d.meta_value";

		$data = $vce->db->get_data_object($query);
		$media_types = array();
        foreach($data as $this_data) {
			$media_types[] = $this_data->media_type;
		 }

		 if (count($media_types) < 1) {
			 return "There were no library resources used during this time period.";
		 }

		$media_counts = NULL;
		 $query = "SELECT count(d.meta_value) AS sum FROM vce_components_meta AS a INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER  JOIN vce_components_meta as d on a.component_id = d.component_id  JOIN vce_components_meta as e on a.component_id = e.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND a.component_id IN ($alias_ids) AND d.meta_key = 'media_type'  AND e.meta_key IN ('created_by') AND e.meta_value IN ($users_in_jurisdiction)";
		 $data = $vce->db->get_data_object($query);
		 foreach($data as $this_data) {
			 $media_counts .= 'Total Resources Used: '.$this_data->sum.'. Breakdown: ';
		  }
		
		 
		 foreach ($media_types as $k=>$v) {
			$query = "SELECT count(d.meta_value) AS sum FROM vce_components_meta AS a  INNER JOIN vce_components_meta as c on a.component_id = c.component_id INNER JOIN vce_components_meta as d on a.component_id = d.component_id JOIN vce_components_meta as e on a.component_id = e.component_id WHERE a.meta_key = 'created_at' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date  AND a.component_id IN ($alias_ids) AND d.meta_key = 'media_type' AND d.meta_value = '$v'  AND e.meta_key IN ('created_by') AND e.meta_value IN ($users_in_jurisdiction)";
			$data = $vce->db->get_data_object($query);
			foreach($data as $this_data) {
				$media_counts .= ucfirst($v).': '.$this_data->sum.', ';
			}
		}

		return trim($media_counts, ', ');

	}



}