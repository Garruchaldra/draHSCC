<?php

class nestor_reports_table_ANALYTICS  extends Nestor_reports_tableType {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Nestor Reports Table for all ANALYTICS',
			'description' => 'A minion for Nestor Reports Table; adds methods for the tables of Nestor Reports Table. Should work on all Analytics tables.',
			'category' => 'nestor_reports'
		);
	}



/**
 * get all users in jurisdiction 
 * this method is used by all report methods
 * Takes a user's role and returns either a list or an object with attributes of users heirarchically under the first user.
 */
	public function find_users_in_jurisdiction($user, $vce, $get_user_metadata = false,  $users_filter = NULL) {
		// $vce->dump($user->role_hierarchy);
		// $user->role_hierarchy = 3;
		if (isset($users_filter)) {
			$users_info = array('roles' => $users_filter);
		} else {
			$users_info = array('roles' => 'all');
		}
		switch ($user->role_hierarchy) {
			case 1:
			case 2:
				// get all users
				$all_users = $vce->user->get_users($users_info);
				// foreach ($all_users as $key=>$value) {
				// 	if (!isset($value->organization) || $value->organization != $vce->user->organization) {
				// 		unset($all_users[$key]);
				// 	}
				// }
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
			// no other users
			case 5:
				$all_users = array();
				break;
			// no other users
			case 6:
				$all_users = array();
				break;
			// no other users
			default:
				$all_users = array();
		}

		// return user object array
		if ($get_user_metadata == true) {
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

		return $user_list;
	}

/**
 * is_valid_timestamp
 * this method is used by all of the reports methods
 * Checks to make sure that the timestamp to be used is within the bounds of reality 
 * (Sometimes, a conversion to timestamp is malformed and must be changed in the programming)
 */

	public function is_valid_timestamp($timestamp) {
		// global $vce;
    	return ((string) (int) $timestamp === $timestamp) 
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
	}

/**
 * Time_ranges
 * this method is used by all of the reports methods
 * This takes the report time range specified by start and end dates, 
 * interpolates the sum interval dates,
 * and returns an empty array of dates which will then be filled with data which occured on those dates.
 */

	public function time_ranges($start_date, $end_date, $sum_interval) {
// global $vce;
// 		$vce->log($end_date);
		$start_date_test = $this->is_valid_timestamp("$start_date");

		if(!$start_date_test) {
			$start_date = strtotime($start_date);
		} else {
			$start_date = $start_date;
		}

		$end_date_test = $this->is_valid_timestamp("$end_date");
		if(!$end_date_test) {
			$end_date = new DateTime($end_date);
			$end_date->add(new DateInterval('P1D'));
			$end_date = date_format($end_date, 'm/d/Y');
			$end_date = strtotime($end_date);
		} else {
			$end_date = $end_date;
		}

			$start_datetime = new DateTime(date('Y-m-d', $start_date));
			$end_datetime = new DateTime(date('Y-m-d', $end_date));
			
			// $end_datetime = new DateTime($end_date);
			// $end_datetime->add(new DateInterval('P1D'));
			// $end_datetime = date_format($end_datetime, 'Y-m-d');
			// $vce->log($end_datetime);

			switch ($sum_interval) {
				case 'year':
					$difference = $start_datetime->diff($end_datetime);
					$number_of_dates = $difference->y;
					$interval = 'P1Y';
					$format = 'Y';
					break;
				case 'month':
					$difference = $start_datetime->diff($end_datetime);
					$number_of_dates = ($difference->y * 12) + $difference->m;
					$interval = 'P1M';
					$format = 'Y-m';
					break;
				case 'week':
					$difference = $start_datetime->diff($end_datetime);
					$number_of_dates = round($difference->days / 7);
					$interval = 'P1W';
					$format = 'Y-W';
					break;
				case 'day':
					$difference = $start_datetime->diff($end_datetime);
					$number_of_dates = $difference->days;
					$interval = 'P1D';
					$format = 'Y-m-d';
					break;
			}

			$working_date = $start_datetime;
			$time_info_output = array();
			for ($i=0; $i<=$number_of_dates; $i++){
				$time_info_output[$sum_interval][$working_date->format($format)] = array();
				$wdf = $working_date->format($format);
				$edf = $end_datetime->format($format);
				
				if ($i==$number_of_dates && $wdf != $edf) {
					$working_date->add(new DateInterval($interval));
					$time_info_output[$sum_interval][$working_date->format($format)] = array();
				} else {
					$working_date->add(new DateInterval($interval));
				}
			}

			$output = array(
				'start_datetime' => $start_datetime,
				'end_datetime' => $end_datetime,
				'format' => $format,
				'time_info_output' => $time_info_output,
				'start_date' => $start_date,
				'end_date' => $end_date
			);
			return $output;
	}



		/*
		Report
		This produces an array which the component_data_report turns into a table.

		Users Created: all users within jurisdiction created within the specified date range
		**/

	// 	// example only! change!!
	// public function method_users_createdBAK($input){

	// 	global $vce;
	// 	extract($input);

	// 	// get an array in which the date-range is interpolated into specified date intervals
	// 	// creates the main array "$time_info_output"
	// 	$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
	// 	extract($time_info);

	// 	// get comma-delineated list (or user-array) of users to use in data queries
	// 	$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, TRUE, $users_filter);

	// 	$total_in_timespan = array();
	// 	foreach ($users_in_jurisdiction AS $k => $v) {
	// 		if ($v->created_at < $start_date || $v->created_at > $end_date ) {
	// 			unset($users_in_jurisdiction[$k]);
	// 			continue;
	// 		}
	// 		if(isset($v->created_at)) {
	// 			// format the date 
	// 			$component_datetime = new DateTime(date('Y-m-d', $v->created_at));
	// 			$formatted_date = $component_datetime->format($format);
	// 			$total_in_timespan[] = $v->user_id;;
	// 			$time_info_output[$sum_interval][$formatted_date][] = $v->user_id;
	// 		}
	// 	}


	// 	// format the output array
	// 	$display_interval = ucfirst($sum_interval);
	// 	$method_output = array(
	// 		'total_in_timespan' => count($total_in_timespan),
	// 		'interpolated_array' => array (
	// 			0 => array(
	// 				$display_interval => "Users Created"
	// 			)
	// 		)
	// 	);

	// 	$i = 1;
	// 	foreach ($time_info_output[$sum_interval] AS $k => $v) {
	// 			$method_output['interpolated_array'][$i][$k] = count($v);
	// 			$i++;
	// 	}
	// 	// $vce->log($method_output[0]);
	// 	return $method_output;
	// }



	/*
		Report
		This produces an array which the component_data_report turns into a table.

		cycles_created: creates an array of date-timespans with the number of cycles created during each.
		$method_output is the array returned. Required format:
		$method_output = array(
			'total_in_timespan' => <total number of records for date-range>,
			'interpolated_array' => array (
				0 => array(
					$display_interval [<Capitalized Name of Sum Interval>] => "Number of Cycles Created" [<header for table values>]
				)
			)
		);
	**/


	// analytics logins
	public function method_analytics_logins($input){

		global $vce;

		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		// If the users in jurisdiction is all users, there is no need to add a condition.
		if ($vce->user->role_hierarchy > 2 || count(explode('|',$users_filter)) != count(json_decode($vce->site->site_roles, TRUE))) { 
			$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);
			$users_in_jurisdiction_in_condition = "IN ($users_in_jurisdiction)";
		} else {
			$users_in_jurisdiction_in_condition = "IS NOT NULL";
		}

		// query the data for this report
		// $query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'pbccycles' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";

		$query = "SELECT a.id, UNIX_TIMESTAMP(a.timestamp) AS created_at FROM " . TABLE_PREFIX . "analytics AS a JOIN " . TABLE_PREFIX . "analytics AS b ON a.id = b.id AND b.action = 'login'  AND UNIX_TIMESTAMP(a.timestamp) > $start_date AND UNIX_TIMESTAMP(a.timestamp) < $end_date JOIN " . TABLE_PREFIX . "analytics AS c ON a.id = c.id AND c.user_id $users_in_jurisdiction_in_condition";
		$login_data = $vce->db->get_data_object($query);
// $vce->log($query);
		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($login_data as $this_login_data) {
			if(isset($this_login_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_login_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_login_data->id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_login_data->id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					// <name of selected display interval> => <name of report>
					$display_interval => "Number of Logins"
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}


		return $method_output;
	}

	// analytics views
	public function method_analytics_views($input){

		global $vce;

		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		// If the users in jurisdiction is all users, there is no need to add a condition.
		if ($vce->user->role_hierarchy > 2 || count(explode('|',$users_filter)) != count(json_decode($vce->site->site_roles, TRUE))) { 
			$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);
			$users_in_jurisdiction_in_condition = "IN ($users_in_jurisdiction)";
		} else {
			$users_in_jurisdiction_in_condition = "IS NOT NULL";
		}

		// query the data for this report
		// $query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'pbccycles' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";

		$query = "SELECT a.id, UNIX_TIMESTAMP(a.timestamp) AS created_at FROM " . TABLE_PREFIX . "analytics AS a JOIN " . TABLE_PREFIX . "analytics AS b ON a.id = b.id AND b.action = 'view'  AND UNIX_TIMESTAMP(a.timestamp) > $start_date AND UNIX_TIMESTAMP(a.timestamp) < $end_date JOIN " . TABLE_PREFIX . "analytics AS c ON a.id = c.id AND c.user_id $users_in_jurisdiction_in_condition";
		$data = $vce->db->get_data_object($query);
// $vce->log($query);
		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($data as $this_data) {
			if(isset($this_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_data->id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_data->id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					// <name of selected display interval> => <name of report>
					$display_interval => "Number of Page Views (any types)"
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}


		return $method_output;
	}


	// analytics views
	public function method_analytics_views_cop_groups($input){

		global $vce;

		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		// If the users in jurisdiction is all users, there is no need to add a condition.
		if ($vce->user->role_hierarchy > 2 || count(explode('|',$users_filter)) != count(json_decode($vce->site->site_roles, TRUE))) { 
			$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);
			$users_in_jurisdiction_in_condition = "IN ($users_in_jurisdiction)";
		} else {
			$users_in_jurisdiction_in_condition = "IS NOT NULL";
		}

		// query the data for this report
		// $query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'pbccycles' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";

		$query = "SELECT a.id, UNIX_TIMESTAMP(a.timestamp) AS created_at FROM " . TABLE_PREFIX . "analytics AS a JOIN " . TABLE_PREFIX . "analytics AS b ON a.id = b.id AND b.action = 'view'  AND UNIX_TIMESTAMP(a.timestamp) > $start_date AND UNIX_TIMESTAMP(a.timestamp) < $end_date AND b.object LIKE '%cop-groups%' AND b.user_id $users_in_jurisdiction_in_condition";
		$data = $vce->db->get_data_object($query);
// $vce->log($query);
		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($data as $this_data) {
			if(isset($this_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_data->id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_data->id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					// <name of selected display interval> => <name of report>
					$display_interval => "Number of Page Views: Cop-Groups)"
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}


		return $method_output;
	}


	// analytics views
	public function method_analytics_views_cycles($input){

		global $vce;

		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		// If the users in jurisdiction is all users, there is no need to add a condition.
		if ($vce->user->role_hierarchy > 2 || count(explode('|',$users_filter)) != count(json_decode($vce->site->site_roles, TRUE))) { 
			$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);
			$users_in_jurisdiction_in_condition = "IN ($users_in_jurisdiction)";
		} else {
			$users_in_jurisdiction_in_condition = "IS NOT NULL";
		}

		// query the data for this report
		// $query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'pbccycles' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";

		$query = "SELECT a.id, UNIX_TIMESTAMP(a.timestamp) AS created_at FROM " . TABLE_PREFIX . "analytics AS a JOIN " . TABLE_PREFIX . "analytics AS b ON a.id = b.id AND b.action = 'view'  AND UNIX_TIMESTAMP(a.timestamp) > $start_date AND UNIX_TIMESTAMP(a.timestamp) < $end_date AND b.object LIKE '%pbccycles%' AND b.user_id $users_in_jurisdiction_in_condition";
		$data = $vce->db->get_data_object($query);
// $vce->log($query);
		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($data as $this_data) {
			if(isset($this_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_data->id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_data->id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					// <name of selected display interval> => <name of report>
					$display_interval => "Number of Page Views: Resource-library)"
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}


		return $method_output;
	}


		// analytics views
		public function method_analytics_views_resource_library($input){

			global $vce;
	
			extract($input);
	
			// get an array in which the date-range is interpolated into specified date intervals
			// creates the main array "$time_info_output"
			$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
			extract($time_info);
	
			// get comma-delineated list (or user-array) of users to use in data queries
			// If the users in jurisdiction is all users, there is no need to add a condition.
			if ($vce->user->role_hierarchy > 2 || count(explode('|',$users_filter)) != count(json_decode($vce->site->site_roles, TRUE))) { 
				$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);
				$users_in_jurisdiction_in_condition = "IN ($users_in_jurisdiction)";
			} else {
				$users_in_jurisdiction_in_condition = "IS NOT NULL";
			}
	
			// query the data for this report
			// $query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'pbccycles' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
	
			$query = "SELECT a.id, UNIX_TIMESTAMP(a.timestamp) AS created_at FROM " . TABLE_PREFIX . "analytics AS a JOIN " . TABLE_PREFIX . "analytics AS b ON a.id = b.id AND b.action = 'view'  AND UNIX_TIMESTAMP(a.timestamp) > $start_date AND UNIX_TIMESTAMP(a.timestamp) < $end_date AND b.object LIKE '%resource-library%' AND b.user_id $users_in_jurisdiction_in_condition";
			$data = $vce->db->get_data_object($query);
	// $vce->log($query);
			// adds one element per query data to the $time_info_output array
			$total_in_timespan = array();
			foreach ($data as $this_data) {
				if(isset($this_data->created_at)) {
					// format the date 
					$component_datetime = new DateTime(date('Y-m-d', $this_data->created_at));
					$formatted_date = $component_datetime->format($format);
					//add to output
					$total_in_timespan[] = $this_data->id;
					$time_info_output[$sum_interval][$formatted_date][] = $this_data->id;
				}
			}
	
			// format the output array
			$display_interval = ucfirst($sum_interval);
			$method_output = array(
				'total_in_timespan' => count($total_in_timespan),
				'interpolated_array' => array (
					0 => array(
						// <name of selected display interval> => <name of report>
						$display_interval => "Number of Page Views: Resource-library)"
					)
				)
			);
	
			$i = 1;
			foreach ($time_info_output[$sum_interval] AS $k => $v) {
					$method_output['interpolated_array'][$i][$k] = count($v);
					$i++;
			}
	
	
			return $method_output;
		}

		// analytics delete
		public function method_analytics_delete_documentation($input){

			global $vce;
	
			extract($input);
	
			// get an array in which the date-range is interpolated into specified date intervals
			// creates the main array "$time_info_output"
			$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
			extract($time_info);
	
			// get comma-delineated list (or user-array) of users to use in data queries
			// If the users in jurisdiction is all users, there is no need to add a condition.
			if ($vce->user->role_hierarchy > 2 || count(explode('|',$users_filter)) != count(json_decode($vce->site->site_roles, TRUE))) { 
				$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);
				$users_in_jurisdiction_in_condition = "IN ($users_in_jurisdiction)";
			} else {
				$users_in_jurisdiction_in_condition = "IS NOT NULL";
			}
	
			// query the data for this report
			// $query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'pbccycles' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
	
			$query = "SELECT a.id, UNIX_TIMESTAMP(a.timestamp) AS created_at FROM " . TABLE_PREFIX . "analytics AS a JOIN " . TABLE_PREFIX . "analytics AS b ON a.id = b.id AND b.action = 'delete'  AND UNIX_TIMESTAMP(a.timestamp) > $start_date AND UNIX_TIMESTAMP(a.timestamp) < $end_date AND b.object LIKE '%type\":\"Documentation%' AND b.user_id $users_in_jurisdiction_in_condition";
			$data = $vce->db->get_data_object($query);
	// $vce->log($query);
			// adds one element per query data to the $time_info_output array
			$total_in_timespan = array();
			foreach ($data as $this_data) {
				if(isset($this_data->created_at)) {
					// format the date 
					$component_datetime = new DateTime(date('Y-m-d', $this_data->created_at));
					$formatted_date = $component_datetime->format($format);
					//add to output
					$total_in_timespan[] = $this_data->id;
					$time_info_output[$sum_interval][$formatted_date][] = $this_data->id;
				}
			}
	
			// format the output array
			$display_interval = ucfirst($sum_interval);
			$method_output = array(
				'total_in_timespan' => count($total_in_timespan),
				'interpolated_array' => array (
					0 => array(
						// <name of selected display interval> => <name of report>
						$display_interval => "Number of Deletions: Documentation)"
					)
				)
			);
	
			$i = 1;
			foreach ($time_info_output[$sum_interval] AS $k => $v) {
					$method_output['interpolated_array'][$i][$k] = count($v);
					$i++;
			}
	
	
			return $method_output;
		}



}