<?php

class nestor_reports_table_HSCC  extends Nestor_reports_tableType {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Nestor Reports Table for the HSCC',
			'description' => 'A minion for Nestor Reports Table; adds methods for the tables of Nestor Reports Table. HSCC specific.',
			'category' => 'nestor_reports'
		);
	}


	// get all users in jurisdiction 
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

		$test_users = array();
		if (!isset($vce->site->testusers) || $vce->site->testusers != 'on') {
			$query = "SELECT user_id FROM " . TABLE_PREFIX . "users_meta where meta_key='tester' and meta_value='TRUE'";
			// $vce->log($query);
			$data = $vce->db->get_data_object($query);
			foreach($data as $this_data) {
				// $vce->log($this_data);
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

	public function is_valid_timestamp($timestamp) {
		// global $vce;
    	return ((string) (int) $timestamp === $timestamp) 
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
	}


	public function time_ranges($start_date, $end_date, $sum_interval) {

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


	public function method_users_created($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, TRUE, $users_filter);

// $vce->dump($users_in_jurisdiction);
// $vce->dump(count($users_in_jurisdiction));
		/** 
 * for filtering out old users:
// $vce->dump($users_in_jurisdiction);
// $vce->dump(count($users_in_jurisdiction));
		$query = "SELECT email FROM user_migration";
		$emails = $vce->db->get_data_object($query);
		$old_emails = array();
		foreach ($emails as $this_email) {
			$old_emails[] = $this_email->email;
		}
// $vce->dump($old_emails);
		$orgs = array();

*/
		$created_at_timestamps = array();
		$total_in_timespan = array();
		foreach ($users_in_jurisdiction AS $k => $v) {
			if  (!isset($v->created_at)) {
				unset($users_in_jurisdiction[$k]);
				continue;
			}
			
			if ($v->created_at < $start_date || $v->created_at > $end_date) {
				unset($users_in_jurisdiction[$k]);
				continue;
			}

/** 
 * for filtering out old users:
			if ($v->group == '1804') {
				unset($users_in_jurisdiction[$k]);
				continue;
			}
*/


			if(isset($v->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $v->created_at));
/** 
 * for filtering out old users:
				$format = 'Y-m-d H:i';
				$component_datetime = new DateTime(date('Y-m-d H:i:s', $v->created_at));
*/

				$formatted_date = $component_datetime->format($format);
				$total_in_timespan[] = $v->user_id;;
				$time_info_output[$sum_interval][$formatted_date][] = $v->user_id;
				$created_at_timestamps[] = $v->created_at;

/** 
 * for filtering out old users:
				$orgs[$formatted_date][] =  $v->user_id.', '.$v->email.', '. $v->organization.','.$formatted_date;
*/

			}
		}




/** 
 * for filtering out old users:
// $vce->dump(count($orgs));
asort($orgs);
$i=0;
foreach ($orgs as $k=>$v){
	if(count($v) > 1) {
		unset($orgs[$k]);
		continue;
	}
	$i=$i+count($v);
}


$vce->dump($i);
// 		$orgs = array_unique($orgs);
// $vce->dump(count($orgs));

*/




		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Users Created"
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}
		// $vce->log($method_output[0]);
		return $method_output;
	}


	public function method_users_created_with_names($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, TRUE, $users_filter);

		// $vce->dump($users_in_jurisdiction);
/** 
 * for filtering out old users:
// $vce->dump($users_in_jurisdiction);
// $vce->dump(count($users_in_jurisdiction));
		$query = "SELECT email FROM user_migration";
		$emails = $vce->db->get_data_object($query);
		$old_emails = array();
		foreach ($emails as $this_email) {
			$old_emails[] = $this_email->email;
		}
// $vce->dump($old_emails);
		$orgs = array();

*/
		$created_at_timestamps = array();
		$total_in_timespan = array();
		foreach ($users_in_jurisdiction AS $k => $v) {
			if ($v->created_at < $start_date || $v->created_at > $end_date ) {
				unset($users_in_jurisdiction[$k]);
				continue;
			}

/** 
 * for filtering out old users:
			if ($v->group == '1804') {
				unset($users_in_jurisdiction[$k]);
				continue;
			}
*/


			if(isset($v->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $v->created_at));
/** 
 * for filtering out old users:
				$format = 'Y-m-d H:i';
				$component_datetime = new DateTime(date('Y-m-d H:i:s', $v->created_at));
*/

				$formatted_date = $component_datetime->format($format);
				$total_in_timespan[] = $v->user_id;;
				$time_info_output[$sum_interval][$formatted_date][] = $v->user_id;
				$created_at_timestamps[] = $v->created_at;

/** 
 * for filtering out old users:
				$orgs[$formatted_date][] =  $v->user_id.', '.$v->email.', '. $v->organization.','.$formatted_date;
*/

			}
		}




/** 
 * for filtering out old users:
// $vce->dump(count($orgs));
asort($orgs);
$i=0;
foreach ($orgs as $k=>$v){
	if(count($v) > 1) {
		unset($orgs[$k]);
		continue;
	}
	$i=$i+count($v);
}


$vce->dump($i);
// 		$orgs = array_unique($orgs);
// $vce->dump(count($orgs));

*/




		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Users Created"
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
			// $vce->dump($v);
				$method_output['interpolated_array'][$i][$k] = count($v);
				$i++;
		}
		// $vce->log($method_output[0]);
		return $method_output;
	}



	public function method_analytics_flat_table($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $vce->dump($users_in_jurisdiction);




		// get analytics data

		// convert timestamps to formatted dates to query analytics table
		$format = 'Y-m-d H:i';
		$query_datetime = new DateTime(date('Y-m-d', $start_date));
		$analytics_start_date = $query_datetime->format($format);
		$query_datetime = new DateTime(date('Y-m-d', $end_date));
		$analytics_end_date = $query_datetime->format($format);

		// query analytics table
		$query = "SELECT * FROM " . TABLE_PREFIX . "analytics WHERE timestamp > '$analytics_start_date' AND timestamp < '$analytics_end_date'  AND user_id IN ($users_in_jurisdiction)";
		$analytics_data = $vce->db->get_data_object($query);

		// $vce->log($query);
		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		$users_in_query = array();
		$headers = array();
		$skip_these = array(
			'id', 'timestamp', 'user_id', 'session', 'component_id'

		);
		foreach ($analytics_data as $this_analytics_data) {
			if(isset($this_analytics_data->timestamp)) {
				$analytics_data_row = array();
				$this_user = $vce->user->find_users($this_analytics_data->user_id);
				$analytics_data_row['user_id'] = $this_analytics_data->user_id;
				$analytics_data_row['user_role'] = $this_user[0]->role_name;
				$analytics_data_row['organization'] = $this_user[0]->organization;
				$users_in_query[$this_analytics_data->user_id] =  NULL;
				//add to output
				$total_in_timespan[] = $this_analytics_data->id;
				if ($this_analytics_data->action == 'input' || $this_analytics_data->action == 'create' ) {
					 $this_analytics_data->object = preg_replace('/\"\{/', '{', $this_analytics_data->object);  
					$this_analytics_data->object = preg_replace('/\}\"/', '}', $this_analytics_data->object);  
					$this_analytics_data->object = preg_replace('/\"\[\{/', '[{', $this_analytics_data->object);  
					$this_analytics_data->object = preg_replace('/\}\]\"/', '}]', $this_analytics_data->object);  

					$this_analytics_data->object = print_r(json_decode($this_analytics_data->object), true);
				}
				foreach ($this_analytics_data as $k=>$v) {
					if (in_array($k,$skip_these)) {
						continue;
					}
					$analytics_data_row[$k] = $v;
				}
				foreach ($analytics_data_row as $k=>$v) {
					$headers[$k] = $k;
				}
				$time_info_output[$sum_interval][$this_analytics_data->timestamp][] = $analytics_data_row;
			}
		}

// 		$me = $vce->user->find_users(13);
// $vce->log($me);




		// format the output array
		$display_interval = ucfirst($sum_interval);

		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => $headers
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
			// $vce->dump($v);
				$method_output['interpolated_array'][$i][$k] = $v;
				$i++;
		}
		// $vce->log($method_output[0]);
		return $method_output;
	}



	public function method_intake_forms_flat_table($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $vce->dump($users_in_jurisdiction);




		// get analytics data

		// convert timestamps to formatted dates to query analytics table
		$format = 'Y-m-d H:i';
		$query_datetime = new DateTime(date('Y-m-d', $start_date));
		$intake_forms_start_date = $query_datetime->format($format);
		$query_datetime = new DateTime(date('Y-m-d', $end_date));
		$intake_forms_end_date = $query_datetime->format($format);

		// query analytics table
		$query = "SELECT * FROM " . TABLE_PREFIX . "ingression_forms WHERE created > '$intake_forms_start_date' AND created < '$intake_forms_end_date'  AND user_id IN ($users_in_jurisdiction)";
		$intake_form_data = $vce->db->get_data_object($query);

		// $vce->log($query);
		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		$users_in_query = array();
		$headers = array();
		$skip_these = array(
			'id'
		);
		foreach ($intake_form_data as $this_intake_form_data) {
			// $vce->log($this_intake_form_data);
			if(isset($this_intake_form_data->created)) {
				$intake_form_data_row = array();
				// $this_user = $vce->user->find_users($this_intake_form_data->user_id);
				// $intake_form_data_row['user_id'] = $this_intake_form_data->user_id;
				// $intake_form_data_row['user_role'] = $this_user[0]->role_name;
				// $intake_form_data_row['organization'] = $this_user[0]->organization;
				$users_in_query[$this_intake_form_data->user_id] =  NULL;
				//add to output
				$total_in_timespan[] = $this_intake_form_data->id;
				// if ($this_intake_form_data->action == 'input' || $this_intake_form_data->action == 'create' ) {
				// 	 $this_intake_form_data->object = preg_replace('/\"\{/', '{', $this_intake_form_data->object);  
				// 	$this_intake_form_data->object = preg_replace('/\}\"/', '}', $this_intake_form_data->object);  
				// 	$this_intake_form_data->object = preg_replace('/\"\[\{/', '[{', $this_intake_form_data->object);  
				// 	$this_intake_form_data->object = preg_replace('/\}\]\"/', '}]', $this_intake_form_data->object);  

				// 	$this_intake_form_data->object = print_r(json_decode($this_intake_form_data->object), true);
				// }
				foreach ($this_intake_form_data as $k=>$v) {
					if (in_array($k,$skip_these)) {
						continue;
					}
					$intake_form_data_row[$k] = $v;
				}
				foreach ($intake_form_data_row as $k=>$v) {
					$headers[$k] = $k;
				}
				// $vce->log($intake_form_data_row);
				$time_info_output[$sum_interval][$this_intake_form_data->created][] = $intake_form_data_row;
			}
		}

// 		$me = $vce->user->find_users(13);
// $vce->log($me);




		// format the output array
		$display_interval = ucfirst($sum_interval);

		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => $headers
				)
			)
		);

		$i = 1;
		foreach ($time_info_output[$sum_interval] AS $k => $v) {
			// $vce->dump($v);
				$method_output['interpolated_array'][$i][$k] = $v;
				$i++;
		}
		// $vce->log($method_output[0]);
		return $method_output;
	}



	/*
		Report
		This produces an array which the component_data_report turns into a table.

		cycles_created: creates an array of date-timespans with the number of cycles created during each.
	**/

	public function method_cycles_created($input){

		global $vce;

		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// query the data for this report
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'pbccycles' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Cycles Created"
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

	/*
		Report
		This produces an array which the component_data_report turns into a table.

		cycles_created: creates an array of date-timespans with the number of cycles created during each.
	**/

	public function method_cycles_completed($input){

		global $vce;

		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// query the data for this report
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'pbccycles' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS d ON a.component_id = d.component_id AND d.meta_key = 'pbccycle_status' AND d.meta_value = 'Complete'  JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Cycles Marked as Complete"
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

	public function method_videos_created($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $vce->log($users_in_jurisdiction);
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'media_type' AND a.meta_value = 'VimeoVideo' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Videos Uploaded "
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


	public function method_pdf_created($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $vce->log($users_in_jurisdiction);
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'media_type' AND a.meta_value = 'PDF' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of PDFs Uploaded "
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

	public function method_images_created($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $vce->log($users_in_jurisdiction);
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'media_type' AND a.meta_value = 'Image' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Images Uploaded "
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

	public function method_media_created($input){

		global $vce;
		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// $vce->log($users_in_jurisdiction);
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'media_type' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		// $vce->log($query);
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Media (of any type) Uploaded "
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


		/*
		Report
		This produces an array which the component_data_report turns into a table.

		comments_created: creates an array of date-timespans with the number of comments created during each.
	**/

	public function method_comments_created($input){

		global $vce;

		extract($input);

		// get an array in which the date-range is interpolated into specified date intervals
		// creates the main array "$time_info_output"
		$time_info = $this->time_ranges($component_start_date, $component_end_date, $sum_interval);
		extract($time_info);

		// get comma-delineated list (or user-array) of users to use in data queries
		$users_in_jurisdiction = $this->find_users_in_jurisdiction($vce->user, $vce, FALSE, $users_filter);

		// query the data for this report
		$query = "SELECT a.component_id, b.meta_value AS created_at FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components_meta AS b ON a.component_id = b.component_id AND a.meta_key = 'type' AND a.meta_value = 'Comments' AND b.meta_key='created_at' AND b.meta_value > $start_date AND b.meta_value < $end_date JOIN " . TABLE_PREFIX . "components_meta AS c ON a.component_id = c.component_id AND c.meta_key = 'created_by' AND c.meta_value IN ($users_in_jurisdiction)";
		$created_at_data = $vce->db->get_data_object($query);

		// adds one element per query data to the $time_info_output array
		$total_in_timespan = array();
		foreach ($created_at_data as $this_created_at_data) {
			if(isset($this_created_at_data->created_at)) {
				// format the date 
				$component_datetime = new DateTime(date('Y-m-d', $this_created_at_data->created_at));
				$formatted_date = $component_datetime->format($format);
				//add to output
				$total_in_timespan[] = $this_created_at_data->component_id;
				$time_info_output[$sum_interval][$formatted_date][] = $this_created_at_data->component_id;
			}
		}

		// format the output array
		$display_interval = ucfirst($sum_interval);
		$method_output = array(
			'total_in_timespan' => count($total_in_timespan),
			'interpolated_array' => array (
				0 => array(
					$display_interval => "Number of Comments Created"
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