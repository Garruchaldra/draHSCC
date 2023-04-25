<?php

class Pbc_delete_doubles extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Delete Doubles',
			'description' => 'This looks for users with the same email but distinct cases, and consolidates them.',
			'category' => 'pbc'
		);
	}
	



        /**
         * as_content contains all forms which spawn reports. Since these reports result in downloading a .csv file,
		 * I have used as_content as the method for assembling the data as well. This can be farmed out to individual methods, but must
		 * be called from as_content to create the headers necessary for downloads.
         */


public function as_content($each_component, $vce) {

                
			
                // add javascript to page
                $vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter jquery-ui');
                $vce->site->add_style(dirname(__FILE__) . '/css/style.css');

                $dossier_for_users_report = $vce->user->encryption(json_encode(array('type' => 'Pbc_reports','procedure' => 'array_to_csv_download', 'report'=>'users_report', 'user_id' => $vce->user->user_id)),$vce->user->session_vector);
                $dossier_for_cycles_report = $vce->user->encryption(json_encode(array('type' => 'Pbc_reports','procedure' => 'array_to_csv_download', 'report'=>'cycles_report', 'user_id' => $vce->user->user_id)),$vce->user->session_vector);
                $dossier_for_notifications_report = $vce->user->encryption(json_encode(array('type' => 'Pbc_reports','procedure' => 'array_to_csv_download', 'report'=>'notifications_report', 'user_id' => $vce->user->user_id)),$vce->user->session_vector);

								// load hooks for title bar
								if (isset($vce->site->hooks['titleBar'])) {
									foreach ($vce->site->hooks['titleBar'] as $hook) {
										$title = call_user_func($hook, 'Delete Doubles', 'delete_doubles');
									}
								}

		$vce->content->add('title', $title);

		//find out if we can create tables
		$db_user = "'".DB_USER."'" . "@" . "'".DB_HOST."'";

		$query = "SHOW GRANTS FOR $db_user";
		$grants = $vce->db->query($query);
		$vce->dump('grants:');	
		foreach ($grants as $grant) {
			$vce->dump($grant);	
		}

		// find out if delete_doubles table exists and is populated
		$query = "SELECT count(id) AS count FROM delete_doubles WHERE record_type != 9 AND record_type != 10";
		$rows = $vce->db->get_data_object($query);

		// if already created and populated, work through the rows
		// if record_type 1 exists, delete all those users (these are accounts which have doubles and are not the accounts actually being used.)
		// if record_type 2 exists, update the emails by making them lower-case, and updating the hash, lookup encrypted email, and order-preserving hash 
		// for every action, set the record_type to 9 if it was 1 and 10 if it was 2

		// if it was not already created and populated, create and populate it. 
		// first, this utility makes a list of emails with capitalizations, pairs them with their non-capitalized counterparts, finds the account being used by searching for created content and/or non-default organization, then deletes the unused accounts. The Capitalized emails are then edited.
		if ($rows[0]->count > 0) {
			$vce->dump('PROCESSING IDs IN delete_doubles table:');
//	show table
		$query = "SELECT * FROM delete_doubles";
		$whole_table = $vce->db->get_data_object($query);

		$content = 'The delete_doubles table:<br><br><table class="table-style">';
		foreach ($whole_table AS $this_row) {
			$content .= '<tr>';
			$id = $this_row->id;
			$record_type = $this_row->record_type;
			$user_id = $this_row->user_id;
			$content .= "<th>$id</th><th>$record_type</th><th>$user_id</th>";
			$content .= '</tr>';
		}
		$content .= '</table>';
		$vce->content->add('main', $content);
		// $vce->dump($whole_table);
		// return;
		// delete users without seniority
		$query = "SELECT user_id FROM delete_doubles WHERE record_type = 1";
		$users_to_delete = $vce->db->get_data_object($query);
		$vce->dump('deleting these users: ');
		$vce->dump($users_to_delete);
		foreach ($users_to_delete as $this_user) {
			$user_id = $this_user->user_id;
			$vce->user->delete_user($user_id);
			$query = "UPDATE delete_doubles SET record_type = 9 WHERE user_id = $user_id";
			$vce->db->query($query);
		}

		// get 10 capitalized email records from db and work through it.
		$query = "SELECT a.user_id, b.vector FROM delete_doubles AS a JOIN vce_users AS b ON a.user_id = b.user_id WHERE record_type = 2 LIMIT 10";
		$rows = $vce->db->get_data_object($query);
		if (count($rows) > 0) {
			$vce->dump('Updating these users in table');
			foreach ($rows as $this_user) {
				$user_id = $this_user->user_id;
				$user_array = $vce->user->get_users($user_id);
				$user_array[0]->vector = $this_user->vector;
				$user_array[0]->email = strtolower($user_array[0]->email);
				$password = $vce->user->generate_password();
				$hash = $vce->user->create_hash($user_array[0]->email, $password);
				$lookup = $vce->user->lookup($user_array[0]->email);
				$encrypted_email = $vce->user->encryption($user_array[0]->email, $user_array[0]->vector);
				$order_preserving_hash = $vce->user->order_preserving_hash($user_array[0]->email);

				$query_update_hash = "UPDATE vce_users SET hash = '$hash' WHERE user_id = $user_id";
				$query_update_lookup = "UPDATE vce_users_meta SET meta_value = '$lookup' WHERE user_id = $user_id AND meta_key = 'lookup'";
				$query_encrypted_email_and_oph = "UPDATE vce_users_meta SET meta_value = '$encrypted_email', minutia='$order_preserving_hash' WHERE user_id = $user_id AND meta_key = 'email'";
				$query_update_delete_doubles = "UPDATE delete_doubles SET record_type = 10 WHERE user_id = $user_id";

				$vce->db->query($query_update_hash);
				$vce->db->query($query_update_lookup);
				$vce->db->query($query_encrypted_email_and_oph);
				$vce->db->query($query_update_delete_doubles);
				$vce->dump($user_array);
			}
		}


			return;
		} else {

			
	$vce->dump('Analysing all users, creating lists and storing them ');
		//find default organization and group id's based on name
		$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'New Users'";
		$result = $vce->db->get_data_object($query);
		$default_organization_id = $result[0]->item_id;
		
		$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'New Users Default'";
		$result = $vce->db->get_data_object($query);
		$default_group_id = $result[0]->item_id;

		
		
		
		
		
		// get all user ids
		$query = "SELECT user_id FROM " . TABLE_PREFIX . "users";





	$current_list = $vce->db->get_data_object($query);

	$find_these_users = array();
	foreach ($current_list as $this_user) {
		$find_these_users[] = $this_user->user_id;
	}
	$find_these_users =implode(",",$find_these_users);


		// get user objects from all users
		$user_array = $vce->user->get_users($find_these_users);

		$double_users = array();
		$capital_users = array();
		$lc_user_array = array();

		// create user array indexed with id's
		$id_user_array = array();
		$email_user_array = array();
		foreach ($user_array as $k=>$v) {
			foreach ($v as $k2=>$v2) {
				$id_user_array[$v->user_id][$k2] = $v2;
				// $email_user_array[$v->email][$k2] = $v2;
			}
		}

		// find emails with capitols in them
		foreach ($user_array as $this_user) {
			$lc_email = strtolower($this_user->email);
			if ($lc_email != $this_user->email) {
				$capital_users[$lc_email] = array('user_id' => $this_user->user_id, 'uc_email' => $this_user->email); 
			} else {
				$lowercase_users[$lc_email] = array('user_id' => $this_user->user_id); 
			}
			// if (array_key_exists($lc_email, $lc_user_array)) {
			// 	$double_users[] = array('lc' => array('email' => $lc_email, 'user_id' => $this_user->user_id), 'uc' => array('email' => $this_user->email, 'user_id' => $lc_user_array[$lc_email]));
			// }
			// $all_user_array[$lc_email] = $this_user->user_id;
		}

		foreach ($capital_users as $k => $v) {
			$search_email = strtolower($k);
			if (isset($lowercase_users[$search_email]['user_id'])) {
				$double_users[] = array('lc' => array('email' => $search_email, 'user_id' => $lowercase_users[$search_email]['user_id']), 'uc' => array('email' => $v['uc_email'], 'user_id' => $v['user_id']));
			}
		}

		$conflicted_doubles = array();
		foreach ($double_users as $k => $v) {
			$lc_id = $v['lc']['user_id'];
			$uc_id = $v['uc']['user_id'];
			$double_users[$k]['lc']['organization'] = $id_user_array[$lc_id]['organization'];
			$double_users[$k]['uc']['organization'] = $id_user_array[$uc_id]['organization'];

			$query = "SELECT count(component_id) AS count FROM vce_components_meta WHERE meta_key = 'created_by' AND meta_value = $lc_id";
			$lc_components = $vce->db->get_data_object($query);
			$double_users[$k]['lc']['component_count'] = $lc_components[0]->count;

			$query = "SELECT count(component_id) AS count FROM vce_components_meta WHERE meta_key = 'created_by' AND meta_value = $uc_id";
			$uc_components = $vce->db->get_data_object($query);
			$double_users[$k]['uc']['component_count'] = $uc_components[0]->count;

			if ($uc_components[0]->count != 0 && $lc_components[0]->count ==0) {
				$double_users[$k]['uc']['seniority'] = 1;
			} else if ($uc_components[0]->count == 0 && $lc_components[0]->count !=0) {
				$double_users[$k]['lc']['seniority'] = 1;
			} else if ($uc_components[0]->count == 0 && $lc_components[0]->count == 0) {
				
				if ($id_user_array[$lc_id]['organization'] == $default_organization_id) {
					$double_users[$k]['uc']['seniority'] = 1;
				}
				if ($id_user_array[$uc_id]['organization'] == $default_organization_id) {
					$double_users[$k]['lc']['seniority'] = 1;
				}
				if (!isset($double_users[$k]['lc']['seniority']) && !isset($double_users[$k]['uc']['seniority'])) {
					if ($double_users[$k]['lc']['organization'] < $double_users[$k]['lc']['organization']) {
						$double_users[$k]['lc']['seniority'] = 1;
					} else {
						$double_users[$k]['uc']['seniority'] = 1;
					}
				}
				if ($double_users[$k]['lc']['seniority'] == 1 && $double_users[$k]['uc']['seniority'] == 1){
					$conflicted_doubles[] = $k;
				}
			} else {
				if ($double_users[$k]['lc']['organization'] < $double_users[$k]['lc']['organization']) {
					$double_users[$k]['lc']['seniority'] = 1;
				} else {
					$double_users[$k]['uc']['seniority'] = 1;
				}
			}
			
			// $vce->dump($lc_components);
			// if (isset($lowercase_users[$search_email]['user_id'])) {
			// 	$double_users[] = array('lc' => array('email' => $search_email, 'user_id' => $lowercase_users[$search_email]['user_id']), 'uc' => array('email' => $v['uc_email'], 'user_id' => $v['user_id']));
			// }
		}

		if (count($double_users) < 1 && count($capital_users) < 1) {
			$vce->dump('all done');
			return;
		}

/*
Steps:
Write findings to db
erase all non-seniority users
When there are no more double users, start working on re-classifying the cap users, do a couple at a time.


CREATE TABLE `delete_doubles` (
  `id` int(24) NOT NULL,
  `type` int(2) NOT NULL,
  `user_id` int(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

*/


$query = <<<EOF
CREATE TABLE IF NOT EXISTS `delete_doubles` (
	`id` int(24) NOT NULL,
	`record_type` int(2) NOT NULL,
	`user_id` int(12) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

EOF;
$vce->db->query($query);
$query = <<<EOF
ALTER TABLE `delete_doubles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);
EOF;
$vce->db->query($query);
$query = <<<EOF
ALTER TABLE `delete_doubles`
  MODIFY `id` int(24) NOT NULL AUTO_INCREMENT;
EOF;
$vce->db->query($query);


// $query = <<<EOF
//   TRUNCATE `delete_doubles`;
// EOF;
// $vce->db->query($query);

foreach ($double_users as $k => $v) {

	if (isset($v['uc']['seniority']) && $v['uc']['seniority'] == 1) {
		$lc_user_id = $v['lc']['user_id'];
		$query = "INSERT INTO delete_doubles (record_type, user_id) VALUES (1, $lc_user_id)";
		$vce->db->query($query);
		continue;
	}

	if (isset($v['lc']['seniority']) && $v['lc']['seniority'] == 1) {
		$uc_user_id = $v['uc']['user_id'];
		$query = "INSERT INTO delete_doubles (record_type, user_id) VALUES (1, $uc_user_id)";
		$vce->db->query($query);
		// remove this entry from the list of capital users which will later be updated, because this user is no longer with us
		$email_to_remove = strtolower($v['uc']['email']);
		unset($capital_users[$email_to_remove]);
		continue;
	}

	$query = "INSERT INTO delete_doubles (record_type, user_id) VALUES (3, 0)";
	$vce->db->query($query);

}

foreach ($capital_users as $k => $v) {
	$user_id = $v['user_id'];
	$query = "INSERT INTO delete_doubles (record_type, user_id) VALUES (2, $user_id)";
	$vce->db->query($query);

}

$conflicts_message = 'here are the number of conflicts: '.count($conflicted_doubles);
$vce->dump($conflicts);
$double_message = 'here are the number of double users: '.count($double_users);
$vce->dump($double_message);
$capital_message = 'here are the number of capital users: '.count($capital_users);
$vce->dump($capital_message);
$vce->dump('all capital users:');
$vce->dump($capital_users);
$vce->dump('all double_users:');
$vce->dump($double_users);

return;
}

	// if the download cycles report button has been pressed
// $vce->cycles_report = true;
	if($vce->cycles_report == true){
// $vce->cycles_start_date =	1;
// $vce->cycles_end_date  = 1535752800;

		$cycles_user_list = array();
		$cycles_user_data = array();
		$user_attribute_list = $user_array[0];

		//put user data in an array with each user key = user_id and an array of attributes with names as keys
		foreach ($user_array as $user) {
			if ($user[0] == 'User Id') {
				continue;
			}
			$cycles_user_list[] = $user[0];
			foreach ($user_attribute_list as $key => $value){
				$cycles_user_data[$user[0]][$value] = $user[$key];
			}
		}

		
		// 	SELECT * FROM `vce_components_meta` as a left join `vce_components_meta` as b on a.component_id = b.component_id WHERE a.meta_key = 'created_by' AND  a.meta_value = '13' AND b.meta_key = 'type' AND  b.meta_value = 'Pbccycles' AND a.created_at > ''
		$start_date = $vce->cycles_start_date;
		$end_date = $vce->cycles_end_date;


		//get all cycles belonging to users within admin's jurisdiction
		$query = "SELECT a.component_id as component_id FROM " . TABLE_PREFIX . "components_meta as a inner join " . TABLE_PREFIX . "components_meta as b on a.component_id = b.component_id inner join " . TABLE_PREFIX . "components_meta as c on a.component_id = c.component_id WHERE a.meta_key = 'created_by' AND  a.meta_value  IN (" . implode(",",$cycles_user_list) . ") AND b.meta_key = 'type' AND  b.meta_value = 'Pbccycles' AND c.meta_key = 'created_at' AND c.meta_value > $start_date AND c.meta_value < $end_date";
		$cycle_ids = $vce->db->get_data_object($query);

		// exit;
		// rekey data
		$list_of_cycle_ids = array();
		foreach ($cycle_ids as $each_cycle_data) {
			$list_of_cycle_ids[]=$each_cycle_data->component_id;
		}
		// exit;
		
		// get all data from those cycles
		$query = "SELECT * FROM " . TABLE_PREFIX . "components_meta WHERE component_id  IN (" . implode(",",$list_of_cycle_ids) . ")";
		$cycle_data = $vce->db->get_data_object($query);
		
		// Create column header titles
		$cycle_headers = array();

		foreach ($cycle_data as $this_cycle_data) {
			// fill out cycle data array
			$list_of_cycle_data[$this_cycle_data->component_id][$this_cycle_data->meta_key] = $this_cycle_data->meta_value;
			// get emails of participants and add to array
			if ($this_cycle_data->meta_key == "user_ids_cycle_participants") {
				if (isset($list_of_cycle_data[$this_cycle_data->component_id]['user_ids_cycle_participants'])){
					$cycle_participant_id_list = json_decode($list_of_cycle_data[$this_cycle_data->component_id]['user_ids_cycle_participants'], true);
					$cycle_participant_id_list = explode('|', $cycle_participant_id_list['user_ids']);
				}
				$list_of_cycle_data[$this_cycle_data->component_id]['participants'] = '';
				foreach ($cycle_participant_id_list as $participant) {
					$list_of_cycle_data[$this_cycle_data->component_id]['participants'] .= ', '.$cycles_user_data[$participant]['Email'];
					
				}
				$list_of_cycle_data[$this_cycle_data->component_id]['participants'] = ltrim($list_of_cycle_data[$this_cycle_data->component_id]['participants'], ", ");
			}
			
			// convert "created_at" from timestamp to date
			if ($this_cycle_data->meta_key == "created_at") {
				$list_of_cycle_data[$this_cycle_data->component_id]['created_at'] = date('Y-m-d H:i:s',$this_cycle_data->meta_value);
			}

			// add name and email of originator
			if ($this_cycle_data->meta_key == "originator_id") {
				$list_of_cycle_data[$this_cycle_data->component_id]['originator_name'] = $cycles_user_data[$this_cycle_data->meta_value]['First Name'].' '.$cycles_user_data[$this_cycle_data->meta_value]['Last Name'];
				$list_of_cycle_data[$this_cycle_data->component_id]['originator_email'] = $cycles_user_data[$this_cycle_data->meta_value]['Email'];
				// get organization and group of cycle/originator
				$list_of_cycle_data[$this_cycle_data->component_id]['organization'] = $cycles_user_data[$this_cycle_data->meta_value]['Organization'];
				$list_of_cycle_data[$this_cycle_data->component_id]['group'] = $cycles_user_data[$this_cycle_data->meta_value]['Group'];

			}



			// remove all data which will not be in the report
			unset($list_of_cycle_data[$this_cycle_data->component_id]['type']);
			unset($list_of_cycle_data[$this_cycle_data->component_id]['sub_roles']);
			unset($list_of_cycle_data[$this_cycle_data->component_id]['title']);
			unset($list_of_cycle_data[$this_cycle_data->component_id]['user_ids_cycle_participants']);
			unset($list_of_cycle_data[$this_cycle_data->component_id]['user_oldids_cycle_participants']);
			unset($list_of_cycle_data[$this_cycle_data->component_id]['user_access']);
			unset($list_of_cycle_data[$this_cycle_data->component_id]['created_by']);
			unset($list_of_cycle_data[$this_cycle_data->component_id]['originator']);

			foreach ($list_of_cycle_data[$this_cycle_data->component_id] as $key=>$value) {
				$nice_attribute_title = ucwords(str_replace('_', ' ', $key));
				if(!array_key_exists($key, $cycle_headers)) {
					$cycle_headers[$key] = $nice_attribute_title;
				}
				unset($nice_attribute_title);
			}
		}

		$list_of_cycle_data[0] = $cycle_headers;

		foreach ($list_of_cycle_data as $key => $value) {
			if($key == 0) {
				ksort($list_of_cycle_data[$key]);
				continue;
			}
			foreach ($cycle_headers as $key2=>$value2) {
				if(!array_key_exists($key2, $value)) {
					$list_of_cycle_data[$key][$key2] = '';
				}
			}
			ksort($list_of_cycle_data[$key]);
			unset($key);
			unset($value);
			unset($key2);
			unset($value2);
		}
		ksort($list_of_cycle_data);

		// exit;

		// convert cycles array to csv and output
		$now = date("Y-m-d_h_i_sa");
		$filename = 'ohsccreport_cycles_'.$now.'-'.'.csv';
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'";');

		// open the "output" stream
		// see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
		$fp = fopen('php://output', 'w');



		foreach ($list_of_cycle_data as $line) {
			fputcsv($fp, $line);
			
		}
		fclose($fp);

		exit;
		// exit;
	}
	

	// if the download users report button has been pressed
	if($vce->users_report == true){
				// convert user array to csv and output
				$now = date("Y-m-d_h_i_sa");
				$filename = 'ohsccreport_users_'.$now.'-'.'.csv';
				header('Content-Type: application/csv');
				header('Content-Disposition: attachment; filename="'.$filename.'";');

				// open the "output" stream
				// see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
				$fp = fopen('php://output', 'w');
	// 				fputcsv($fp, array_keys($user_array[0]));

				foreach ($user_array as $line) {
					fputcsv($fp, $line);
					
				}
				fclose($fp);

				exit;
		}


		
	// if the download notifications report button has been pressed
	if($vce->notifications_report == true){
				
		// convert user array to csv and output
		$now = date("Y-m-d_h_i_sa");
		$filename = 'ohsccreport_notifications_'.$now.'-'.'.csv';
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'";');

		// open the "output" stream
		// see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
		$fp = fopen('php://output', 'w');
	// 				fputcsv($fp, array_keys($user_array[0]));

		foreach ($user_array as $line) {
			fputcsv($fp, $line);
			
		}
		fclose($fp);

		exit;
	}

		
		// for date picker
		$id_key = uniqid();
			
		$user_name = $vce->user->first_name . ' ' . $vce->user->last_name;

		switch ($vce->user->role_id) {
			case 1:
				$report_scope = 'all users on the site';
				break;
			case 2:
				$report_scope = 'all users on the site';
				break;
			case 5:
				$report_scope = 'all users in your organization';
				break;
			case 6:
				$report_scope = 'all users in your group';
				break;

			default:
				$report_scope = 'all users for whom you are the admin';
				break;
		}

		    // start date input
				$input = array(
					'type' => 'text',
					'name' => 'start_date',
					'required' => 'true',
					'class' => 'datepicker',
					'id' => 'start-date-$id_key',
					'data' => array (
						'autocapitalize' => 'none',
						'tag' => 'required',
					)
				);
			
			$start_date = $vce->content->create_input($input,'Start Date');

			// end date input
			$input = array(
				'type' => 'text',
				'name' => 'end_date',
				'required' => 'true',
				'class' => 'datepicker',
				'id' => 'end-date-$id_key',
				'data' => array (
					'autocapitalize' => 'none',
					'tag' => 'required',
				)
			);
		
		$end_date = $vce->content->create_input($input,'End Date');
 
$content = <<<EOF
<p class="reports-message">
By submitting the forms below, reports pertaining to $report_scope will be generated and downloaded as a CSV file.
<br><br>

<form class="asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_users_report">

<button class="button__primary" type="submit">Download Users Report</button>
</form>
EOF;

$cyclesReportContent = <<<EOF
<form class="asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_cycles_report">

$start_date
$end_date

<button class="button__primary" type="submit">Download Cycles Report</button>
</form>
EOF;

$content .= <<<EOF
<form class="asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_notifications_report">

<button class="button__primary" type="submit">Download Notifications Report</button>

</form>
EOF;

		// create accordion box
		$content .= $vce->content->accordion('Download Cycles Report', $cyclesReportContent);

$content .= <<<EOF
</p>
EOF;
                $vce->content->add('main', $content);


}
   
	public function assemble_user_info($vce) {
	

			// minimal user attributers
			$default_attributes = array(
				'user_id' => array(
					'title' => 'User Id',
					'sortable' => 1
				),
				'role_id' => array(
					'title' => 'Role Id',
					'sortable' => 1
				),
				'email' => array(
					'title' => 'Email',
					'required' => 1,
					'type' => 'text',
					'sortable' => 1
				)
			);
			
			// all other user attributes
			$user_attributes = json_decode($vce->site->user_attributes, true);
		
			$attributes = array_merge($default_attributes, $user_attributes);
			
			// look for filter_by values in the page object
			$filter_by = array();
			foreach ($vce->page as $key=>$value) {
				if (strpos($key, 'filter_by_') !== FALSE) {
					$filter_by[str_replace('filter_by_', '', $key)] = $value;
				}
			}

			// filter by organization or group depending on role
			if ($vce->user->role_id == 5 || $vce->user->role_id == 6) {
				$filter_by['organization'] = $vce->user->organization;
			}
			if ($vce->user->role_id == 6) {
				$filter_by['group'] = $vce->user->group;
			}
			
			// manage_users_attributes_filter_by
			if (isset($vce->site->hooks['manage_users_attributes_filter_by'])) {
				foreach($vce->site->hooks['manage_users_attributes_filter_by'] as $hook) {
					$filter_by = call_user_func($hook, $filter_by, $vce);
				}
			}
			
			// check if edit_user is within the page object, which means we want to edit this user
			$edit_user = isset($vce->edit_user) ? $vce->edit_user : null;
			
			// get roles
			$roles = json_decode($vce->site->roles, true);
		
			// get roles in hierarchical order
			$roles_hierarchical = json_decode($vce->site->site_roles, true);

			// variables
			$sort_by = isset($vce->sort_by) ? $vce->sort_by : 'email';
			$sort_direction = isset($vce->sort_direction) ? $vce->sort_direction : 'ASC';
			$display_users = true;
	
	
			// create search in values
			$role_id_in = array();
			foreach ($roles_hierarchical as $roles_each) {
				foreach ($roles_each as $key => $value) {
					if ($value['role_hierarchy'] >= $roles[$vce->user->role_id]['role_hierarchy']) {
						// add to role array
						$role_id_in[] = $key;
					}
				}
			}
			
			
			// First we query the user table to get user_id and vector
				// towards the standard way
				// with role_id filter
				if (!empty($filter_by)) {
					$query = "SELECT * FROM " . TABLE_PREFIX . "users";
					$sort_by = null;
				} else if ($sort_by == 'user_id' || $sort_by == 'role_id') {
					// if user_id or role_id is the sort
					$query = "SELECT * FROM " . TABLE_PREFIX . "users WHERE role_id IN (" . implode(',',$role_id_in) . ") ORDER BY $sort_by " . $sort_direction;
				} else {
					// the standard way
					$query = "SELECT " . TABLE_PREFIX . "users.* FROM " . TABLE_PREFIX . "users_meta INNER JOIN " . TABLE_PREFIX . "users ON " . TABLE_PREFIX . "users_meta.user_id = " . TABLE_PREFIX . "users.user_id WHERE " . TABLE_PREFIX . "users.role_id IN (" . implode(',',$role_id_in) . ") AND " . TABLE_PREFIX . "users_meta.meta_key='" . $sort_by . "' ORDER BY " . TABLE_PREFIX . "users_meta.minutia " . $sort_direction ;
				}
			
			
			// if this is a report for notifications, use that user's list
			if(isset($vce->current_list_notifications)) {
				$current_list = $vce->current_list_notifications;
			} else {
				$current_list = $vce->db->get_data_object($query);
			}
			// $vce->dump($current_list);
			
			// rekey data into array for user_id and vectors
			foreach ($current_list as $each_list) {
				$users_list[] = $each_list->user_id;
				$users[$each_list->user_id]['user_id'] = $each_list->user_id;
				$users[$each_list->user_id]['role_id'] = $each_list->role_id;
				$users[$each_list->user_id]['role_name'] = $roles[$each_list->role_id]['role_name'];
				$vectors[$each_list->user_id] = $each_list->vector;
			}
	
			// Second we query the user_meta table for user_ids
			if (isset($users_list) ) {
				// get meta data for the list of user_ids
				$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE user_id IN (" . implode($users_list,',') . ")";
			} else {
				// get all meta data for all users because of filtering
				$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta";
			}
			
			$meta_data = $vce->db->get_data_object($query);
			
			// rekey data
			foreach ($meta_data as $each_meta_data) {
				// skip lookup
				if ($each_meta_data->meta_key == 'lookup') {
					continue;
				}
				// add
				$users[$each_meta_data->user_id][$each_meta_data->meta_key] = $vce->user->decryption($each_meta_data->meta_value,$vectors[$each_meta_data->user_id]);
			}



			//this is the solution to the time problem: find a way to get the max meta_value from each user's components without putting this in a foreach loop
			// then add it to the users array
			// get last activity of users
			$query = "SELECT b.meta_value as last_activity, a.meta_value as user_id FROM " . TABLE_PREFIX . "components_meta a LEFT JOIN " . TABLE_PREFIX . "components_meta b on a.component_id = b.component_id WHERE a.meta_key = 'created_by' AND  b.meta_key = 'created_at' ORDER BY a.meta_value ASC";
			$meta_data = $vce->db->get_data_object($query);

			// create array with only the highest timestamp per user
			$users_last_activities = array();
			foreach ($meta_data as $this_meta_data) {
				if (isset($this_meta_data->user_id)) {
					if (isset($users_last_activities[$this_meta_data->user_id])){
						if ($this_meta_data->last_activity > $users_last_activities[$this_meta_data->user_id]) {
							$users_last_activities[$this_meta_data->user_id] = $this_meta_data->last_activity;
						}
					} else {
						$users_last_activities[$this_meta_data->user_id] = $this_meta_data->last_activity;
					}
				}
			}

			// go through each user and add either the date-time value or 'none on record'
			foreach ($users as $key => $value) {
				if (isset($users_last_activities[$key])) {
					$created_at = $users_last_activities[$key];
					$created_at = date('Y-m-d H:i:s',$created_at);	
					$users[$key]['last_activity'] = $created_at;
				} else {
					$users[$key]['last_activity'] = 'none on record';
				}
			}

	
				// load hooks
				if (isset($vce->site->hooks['manage_users_attributes_list'])) {
					$user_attributes_list = array();
					foreach($vce->site->hooks['manage_users_attributes_list'] as $hook) {
						$user_attributes_list = call_user_func($hook, $user_attributes_list);
					}
					foreach ($user_attributes_list as $each_attribute_key=>$each_attribute_value) {
						if (!is_array($each_attribute_value)) {
							$attributes[$each_attribute_value] = array(
							'title' => $each_attribute_value,
							'sortable' => 1
							);
						} else {
							$attributes[$each_attribute_key] = $each_attribute_value;
						}
					}
				}
	
				// add notifications to attributes if this is the notifications report
				if($vce->notifications_report == true){
					foreach ($notification_titles as $key => $value) {
						$attr_key = strtolower(preg_replace('/ /', '_', $key));
						$attributes[$attr_key] = array(
							'type' => 'text',
							'title' => $key
						);
					}
				}
	
				// $vce->dump($attributes);
	
				// the array that goes into the csv
				$user_array = array();
				
				foreach ($attributes as $each_attribute_key=>$each_attribute_value) {
					// prepare the attributes
					// if conceal is set, as in the case of password, skip to next
					if (isset($each_attribute_value['type']) && $each_attribute_value['type'] == 'conceal') {
						continue;
					}
					
					//create titles for the attributes which can be used as column headers
					$nice_attribute_title = ucwords(str_replace('_', ' ', $each_attribute_key));
				
					if ($each_attribute_key == $sort_by) {
						if ($sort_direction == 'ASC') {
							$sort_class = 'sort-icon sort-active sort-asc';
							$direction = 'DESC';
						} else {
							$sort_class = 'sort-icon sort-active sort-desc';
							$direction = 'ASC';
						}
						$th_class = 'current-sort';
					} else {
						$sort_class = 'sort-icon sort-inactive';
						$direction = 'ASC';
						$th_class = '';
					}
					$user_array[0][] = $nice_attribute_title;	
				}
				$user_array[0][] = 'Last Activity';

			//write the results into user array
			// $user_array = array(0=>array('id','email','first name','last name','role','organization','group'));
			// get role names
			$roles = json_decode($vce->site->roles, true);	

			// prepare for filtering of roles limited by hierarchy
			if (!empty($filter_by)) {
				$role_hierarchy = array();
				// create a lookup array from role_name to role_hierarchy
				foreach ($roles as $roles_key=>$roles_value) {
					$role_hierarchy[$roles_key] = $roles_value['role_hierarchy'];
				}
			}
			
			
			// loop through users, applying filters
			foreach ($users_list as $each_user) {
			
				// apply filters
				if (!empty($filter_by)) {
					// loop through filters and check if any user fields are a match
					foreach ($filter_by as $filter_key=>$filter_value) {
						// prevent roles hierarchy above this from displaying
						if ($role_hierarchy[$users[$each_user]['role_id']] < $role_hierarchy[$vce->user->role_id]) {
							continue 2;
						}

						if ($filter_key == "role_id") {
							// make title of role
							//	$filter_value = $roles[$filter_value]['role_name'];
							if ($users[$each_user]['role_id'] != $filter_value) {
								continue 2;
							}
							
							continue;
						}
						// check if $filter_value is an array
						if (is_array($filter_value)) {
							// check that meta_key exists for this user
							if (!isset($users[$each_user][$filter_key])) {
								continue 2;
							}
							// check if not in the array
							if (!in_array($users[$each_user][$filter_key],$filter_value)) {
								// continue foreach before this foreach
								continue 2;
							}
						} else {
							// doesn't match so continue
							if ($users[$each_user][$filter_key] != $filter_value) {
								// continue foreach before this foreach
								continue 2;	
							}
						}
					}
				}

				// create an array entry for each user which includes all that user's attributes
				$user_attributes = array();
				foreach ($attributes as $each_attribute_key=>$each_attribute_value) {
				
					// exception for role_id, change to role_name
					if ($each_attribute_key == 'role_id') {
						$each_attribute_key = 'role_name';
					}
				
					// if conceal is set, as in the case of password, skip to next
					if (isset($each_attribute_value['type']) && $each_attribute_value['type'] == 'conceal') {
						continue;
					}
					// prevent error if not set
					$attribute_value = isset($users[$each_user][$each_attribute_key]) ? $users[$each_user][$each_attribute_key] : null;

					if (isset($each_attribute_value['datalist'])) {
												
						if (isset($datalist_cache[$attribute_value])) {
						
							// user saved value
							$attribute_name = $datalist_cache[$attribute_value];

						} else {
					
							$datalist = $vce->get_datalist_items(array('item_id' => $attribute_value));
						
							$attribute_name = isset($datalist['items'][$attribute_value]['name']) ? $datalist['items'][$attribute_value]['name'] : null;
					
							// save it so we dont need to look up again
							$datalist_cache[$attribute_value] = $attribute_name;
						
						}
						
						$attribute_value = $attribute_name;
						
					}

					$user_attributes[] = $attribute_value;

				}

				$user_attributes[] = $users[$each_user]['last_activity'];

				
				$user_array[] = $user_attributes;
			}	
		
		return $user_array;
	}
        
		public function array_to_csv_download($input) {
		
			global $vce;
			
			// set page attribute to which report to download. When the page reloads, the report will be compiled and a PHP file download header sent
			if (isset($input['report'])) {
				$vce->site->add_attributes('report', true);
				$vce->site->add_attributes($input['report'], true);
				if ($input['report'] == 'cycles_report') {
					$message = 'Cycles Report is Downloading (Please wait as the report is compiled.)';

					$start_date = strtotime($input['start_date']);
					$end_date = strtotime($input['end_date'] . '+1 day');
					$vce->site->add_attributes(cycles_start_date, $start_date);
					$vce->site->add_attributes(cycles_end_date, $end_date);
				}
				if ($input['report'] == 'users_report') {
					$message = 'Users Report is Downloading (Please wait as the report is compiled.)';
				}
				if ($input['report'] == 'notifications_report') {
					$message = 'Notifications Report is Downloading (Please wait as the report is compiled.)';
				}
			}

			echo json_encode(array('response' => 'success','message' => $message,'form' => 'create','action' => ''));
            return;
		}        
		
		
	 /**
         *
         */
        /**
         *
         */
        public function as_contentBAK($each_component, $vce) {                
				
			
                // add javascript to page
                $vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'tablesorter');

                $vce->site->add_style(dirname(__FILE__) . '/css/style.css');

                // check if value is in page object
                $user_id = isset($vce->user_id) ? $vce->user_id : null;

                $roles = json_decode($vce->site->roles, true);

                $pagination_current = isset($vce->pagination_current) ? $vce->pagination_current : 1;
                $pagination_length = isset($vce->pagination_length) ? $vce->pagination_length : 50;
                $pagination_offset = ($pagination_current != 1) ? ($pagination_length * ($pagination_current - 1)) : 0;

                $filter_by = array();
                $paginate = true;

                // if value is set, disable pagination
                if (isset($user_id)) {
                        $paginate = false;
                }
                
                // Show only users who belong to the SANDBOX organization
                $sandbox_id = $this->sandbox_id();
                if (isset($sandbox_id['organization'])) {
					$vce->filter_by_organization = $sandbox_id['organization'];
				}

                foreach ($vce->page as $key=>$value) {
                        if (strpos($key, 'filter_by_') !== FALSE) {
                                $filter_by[str_replace('filter_by_', '', $key)] = $value;
                                if ($key != 'filter_by_role_id') {
                                        $paginate = false;
                                }
                        }
                }
                
// 				$vce->user_search_results = $this->sandbox_search();
                if (isset($vce->user_search_results)) {

                        $site_users = json_decode($vce->user_search_results);

                        // set value to hide pagination next time around
                        $vce->site->add_attributes('search_results_edit',true);


                } else {

		
		// initialize array to store users
     	$site_users = array();
     	$all_users = array();

		// get all users of specific roles as an array
		$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users";
		if (isset($user_id)) {
				$query .= " WHERE user_id='" . $user_id . "'";
		} else if (isset($filter_by['role_id'])) {
				$query .= " WHERE role_id='" . $filter_by['role_id'] . "'";
		}
		
		$find_users_by_role = $vce->db->get_data_object($query, 0);

		// cycle through users
		foreach ($find_users_by_role as $key=>$value) {
			// add user_id to array for the IN contained within database call
			$users_id_in[] = $value['user_id'];
			// and these other values
			$all_users[$value['user_id']]['user_id'] = $value['user_id'];
			$all_users[$value['user_id']]['role_id'] = $value['role_id'];
			$all_users[$value['user_id']]['vector'] = $value['vector'];
		}
		

		$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE minutia='' AND user_id IN (" . implode(",",$users_id_in) . ")";
		$users_meta_data = $vce->db->get_data_object($query, 0);
		
		foreach ($users_meta_data as $key=>$value) {
			// decrypt the values
			$all_users[$value['user_id']][$value['meta_key']] = $vce->user->decryption($value['meta_value'], $all_users[$value['user_id']]['vector']);
		}


                        $all_users_total = $all_users;

                        // only paginate for role_id

                        if ($paginate === true) {
                                // use array_slice to limit users
                                $all_users = array_slice($all_users, $pagination_offset, $pagination_length);
                        }


                        foreach ($all_users as $each_user=>$value) {

                                // create array
                                $user_object = array();

                                // add the values into the user object
                                $user_object['user_id'] = $value['user_id'];
                                $user_object['role_id'] = $value['role_id'];
                                
                                // look through metadata
                                foreach ($value as $each_metadata=>$value2) {
                                        // add the values into the user object
                                        $user_object[$each_metadata] = $vce->db->clean($value2);
                                }


                                // filter users by anything that is in filter_by
                                foreach ($filter_by as $each_filter_key=>$each_filter_value) {

                                        if ($user_object[$each_filter_key] != $each_filter_value && $each_filter_key != 'role_id') {
                                                // skip to next,. one level up
                                                continue 2;
                                        }
                                }

                                // save into site_users array
                                $site_users[$each_user] = (object) $user_object;
                        }
                }

                // total number of users
                $pagination_total = isset($all_users_total) ? count($all_users_total) : count($site_users);
                $pagination_count = ceil($pagination_total / $pagination_length);


                if (isset($user_id)) {
//this was the update user section


                } else {
       //this was the create new user section         
                
                }




                // only show if we are not editing search results
                if (!isset($vce->user_id)) {

                        $user_attributes_list = array('user_id','last_name','first_name','email');

                        // load hooks
                        if (isset($vce->site->hooks['user_attributes_list'])) {
                                foreach($vce->site->hooks['user_attributes_list'] as $hook) {
                                        $user_attributes_list = call_user_func($hook, $user_attributes_list);
                                }
                        }

// list site users
$content .= <<<EOF
<p>
<div class="clickbar-container">
<div class="clickbar-content no-padding clickbar-open">
<div class="pagination">
EOF;




                        if ($paginate === true) {

                                // the instructions to pass through the form
                                $dossier = array(
                                'type' => 'ManageUsers',
                                'procedure' => 'filter'
                                );

                                // add dossier, which is an encrypted json object of details uses in the form
                                $dossier_for_filter = $vce->generate_dossier($dossier);

                                for ($x = 1;$x <= $pagination_count; $x++) {

                                        $class = ($x == $pagination_current) ? 'class="highlighted"': '';


$content .= <<<EOF
<form class="pagination-form inline-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_filter">
EOF;

                                foreach ($filter_by as $key=>$value) {
                                        $content .= '<input type="hidden" name="filter_by_' . $key . '" value="' . $value . '">';
                                }

$content .= <<<EOF
<input type="hidden" name="pagination_current" value="$x">
<input $class type="submit" value="$x">
</form>
EOF;

                                }

                                $start = $pagination_offset + 1;
                                $end = ($pagination_offset + $pagination_length) < $pagination_total ? $pagination_offset + $pagination_length : $pagination_total;
                                $label_text = $start . ' - ' . $end . ' of ' . $pagination_total . ' total';

$content .= <<<EOF
$label_text
EOF;

                                } else {
$content .= 'By clicking on &quot;Masquerade&quot;, you will become the test user listed in that row.';                              
$content .= '<br><br>';
$content .= 'You will be able to create and participate in Cycles in the &quot;SANDBOX&quot; Organization.'; 
$content .= '<br><br>';
$content .= 'When you want to go back to being your own user, you must log out and log in again. Otherwise, you will continue to be logged in as the SANDBOX test user you chose.';
$content .= '<br><br>';
$content .= 'Other SANDBOX participants may choose the same role as you do, so you may be sharing the same sandbox identity with others.'; 
$content .= '<br><br>';                                

$content .= count($site_users) + 1 . ' total';

                                }

$content .= <<<EOF
</div>
<table id="users" class="tablesorter">
<thead>
<tr>
<th></th>
<th>Site Role</th>
EOF;


                        foreach ($user_attributes_list as $each_user_attribute) {

                                $content .= '<th>' . ucwords(str_replace('_', ' ', $each_user_attribute)) . '</th>';

                        }

$content .= <<<EOF
</tr>
</thead>
EOF;

                if (!empty($site_users)) {
                        foreach ($site_users as $each_site_user) {

                                // allow both simple and complex role definitions
                                $user_role = is_array($roles[$each_site_user->role_id]) ? $roles[$each_site_user->role_id]['role_name'] : $roles[$each_site_user->role_id];

                                if ($each_site_user->user_id == "1") {

$content .= <<<EOF
<tr>
<td></td>

<td>$user_role</td>
EOF;

                                } else {

                                        $dossier_for_masquerade = $vce->user->encryption(json_encode(array('type' => 'ManageUsers','procedure' => 'masquerade','user_id' => $each_site_user->user_id)),$vce->user->session_vector);


$content .= <<<EOF
<tr>
<td class="align-center">
<form class="inline-form asynchronous-form" method="post" action="$vce->input_path">
<input type="hidden" name="dossier" value="$dossier_for_masquerade">
<input type="submit" value="Masquerade">
</form>
</td>
<td>$user_role</td>
EOF;

                                }


                                foreach ($user_attributes_list as $each_user_attribute) {

                                        $content .= '<td>';
                                        if (isset($each_site_user->$each_user_attribute)) {
                                                $content .= $each_site_user->$each_user_attribute;
                                        }
                                        $content .= '</td>';

                                }

$content .= <<<EOF
</tr>
EOF;

                        }
                }
$content .= <<<EOF
</table>
</div>
<div class="clickbar-title disabled"><span>Users</span></div>
</div>
</p>
EOF;


                }

                $vce->content->add('main', $content);


        }
        
        
	/**
	 * get the id numbers of organization and group named New Users
	 */
	public function sandbox_id() {
	
		global $vce;

		//find default organization and group id's based on name
		$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'SANDBOX'";
		$result = $vce->db->get_data_object($query);
		$sandbox_id['organization'] = $result[0]->item_id;
		
		$query = "SELECT item_id FROM " . TABLE_PREFIX . "datalists_items_meta WHERE meta_value = 'SANDBOX default'";
		$result = $vce->db->get_data_object($query);
		$sandbox_id['group'] = $result[0]->item_id;
		
		return $sandbox_id;
	}
	
	
	/**
	 *
	 */

	
	/**
	 * Create a new user
	 */
	public function createBAK($input) {
	
		global $vce;
	
		// remove type so that it's not created for new user
		unset($input['type']);
	
		// test email address for validity
		$input['email'] = filter_var(strtolower($input['email']), FILTER_SANITIZE_EMAIL);
		if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
			echo json_encode(array('response' => 'error','message' => 'Email is not a valid email address','form' => 'create', 'action' => ''));
			return;
		}
		
		$lookup = $vce->user->lookup($input['email']);
		
		// check
		$query = "SELECT id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and meta_value='" . $lookup . "'";
		$lookup_check = $vce->db->get_data_object($query);
		
		if (!empty($lookup_check)) {
			echo json_encode(array('response' => 'error','message' => 'Email is already in use','form' => 'create', 'action' => ''));
			return;
		}
		
		// call to user class to create_hash function
		$hash = $vce->user->create_hash($input['email'], $input['password']);
		
		// get a new vector for this user
		$vector = $vce->user->create_vector();

		$user_data = array(
		'vector' => $vector, 
		'hash' => $hash,
		'role_id' => $input['role_id']
		);
		$user_id = $vce->db->insert('users', $user_data);
		
		unset($input['procedure']);
		unset($input['password']);
		unset($input['role_id']);
				
		// now add meta data

		$records = array();
				
		$lookup = $vce->user->lookup($input['email']);
		
		$records[] = array(
		'user_id' => $user_id,
		'meta_key' => 'lookup',
		'meta_value' => $lookup,
		'minutia' => 'false'
		);
		
		foreach ($input as $key => $value) {

			// encode user data			
			$encrypted = $vce->user->encryption($value, $vector);
			
			$records[] = array(
			'user_id' => $user_id,
			'meta_key' => $key,
			'meta_value' => $encrypted,
			'minutia' => null
			);
			
		}		
		
		$vce->db->insert('users_meta', $records);

		echo json_encode(array('response' => 'success','message' => 'User has been created','form' => 'create','action' => ''));
		return;
	}

	/**
	 * edit user
	 */
	public function editBAK($input) {

		// add attributes to page object for next page load using session
		global $vce;
		
		$vce->site->add_attributes('user_id',$input['user_id']);
				
		$vce->site->add_attributes('pagination_current',$input['pagination_current']);
	
		
		echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
		return;
		
	}

	/**
	 * update user
	 */
	public function updateBAK($input) {
	
		global $vce;
	
		$user_id = $input['user_id'];
	
		$query = "SELECT role_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "'";
		$user_info = $vce->db->get_data_object($query);
		
		$role_id = $user_info[0]->role_id;
		$vector = $user_info[0]->vector;
		
		// has role_id been updated?
		if ($input['role_id'] != $role_id) {

			$update = array('role_id' => $input['role_id']);
			$update_where = array('user_id' => $user_id);
			$vce->db->update('users', $update, $update_where );

		}
		
		// clean up
		unset($input['type'],$input['procedure'],$input['role_id'],$input['user_id']);
		
		// delete old meta data
		foreach ($input as $key => $value) {
				
			// delete user meta from database
			$where = array('user_id' => $user_id, 'meta_key' => $key);
			$vce->db->delete('users_meta', $where);
		
		}
		
		// now add meta data
		
		$records = array();
		
		foreach ($input as $key => $value) {

			// encode user data			
			$encrypted = $vce->user->encryption($value, $vector);
			
			$records[] = array(
			'user_id' => $user_id,
			'meta_key' => $key,
			'meta_value' => $encrypted,
			'minutia' => null
			);
			
		}
		
		$vce->db->insert('users_meta', $records);
				
		echo json_encode(array('response' => 'success','message' => 'User Updated','form' => 'create','action' => ''));
		return;
	
	}	

	
	/**
	 * Masquerade as user
	 */
	public function masqueradeBAK($input) {
	
		global $vce;
			
		// pass user id to masquerade as
		$vce->user->make_user_object($input['user_id']);
		
		
		echo json_encode(array('response' => 'success','message' => 'User masquerade','form' => 'masquerade','action' => $vce->site->site_url));
		return;
	
	}	
	
	
	/**
	 * Delete a user
	 */
	public function deleteBAK($input) {
	
		global $vce;
	
		// delete user from database
		$where = array('user_id' => $input['user_id']);
		$vce->db->delete('users', $where);
		
		// delete user from database
		$where = array('user_id' => $input['user_id']);
		$vce->db->delete('users_meta', $where);
		
		echo json_encode(array('response' => 'success','message' => 'User has been deleted','form' => 'delete','user_id' => $input['user_id'] ,'action' => ''));
		return;
	
	}
	
	  /**
         * Create a new user
         */
        public function create($input) {

                global $vce;


                // remove type so that it's not created for new user
                unset($input['type']);

                // test email address for validity
                $input['email'] = filter_var(strtolower($input['email']), FILTER_SANITIZE_EMAIL);
                if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                        echo json_encode(array('response' => 'error','message' => 'Email is not a valid email address','form' => 'create', 'action' => ''));
                        return;
                }

                $lookup = $vce->user->lookup($input['email']);

                // check
                $query = "SELECT id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and meta_value='" . $lookup . "'";
                $lookup_check = $vce->db->get_data_object($query);

                if (!empty($lookup_check)) {
                        echo json_encode(array('response' => 'error','message' => 'Email is already in use','form' => 'create', 'action' => ''));
                        return;
                }

                // call to user class to create_hash function
                $hash = $vce->user->create_hash($input['email'], $input['password']);

                // get a new vector for this user
                $vector = $vce->user->create_vector();

                $user_data = array(
                'vector' => $vector,
                'hash' => $hash,
                'role_id' => $input['role_id']
                );
                $user_id = $vce->db->insert('users', $user_data);

    

                // now add meta data

                $records = array();

                $lookup = $vce->user->lookup($input['email']);

                $records[] = array(
                'user_id' => $user_id,
                'meta_key' => 'lookup',
                'meta_value' => $lookup,
                'minutia' => 'false'
                );

                foreach ($input as $key => $value) {

                        // encode user data
                        $encrypted = $vce->user->encryption($value, $vector);

                        $records[] = array(
                        'user_id' => $user_id,
                        'meta_key' => $key,
                        'meta_value' => $encrypted,
                        'minutia' => null
                        );

                }

                $vce->db->insert('users_meta', $records);
                
                $input['user_id'] = $user_id;    
                            

                // load hooks (this hook adds the updated user group to the list of users in that group in the datalist
                if (isset($vce->site->hooks['manage_user_create'])) {
                        foreach($vce->site->hooks['manage_user_create'] as $hook) {
                                call_user_func($hook, $input);
                        }
                }

                echo json_encode(array('response' => 'success','message' => 'User has been created','form' => 'create','action' => ''));
                return;
        }

        /**
         * edit user
         */
        public function edit($input) {

                // add attributes to page object for next page load using session
                global $vce;

                $vce->site->add_attributes('user_id',$input['user_id']);

                $vce->site->add_attributes('pagination_current',$input['pagination_current']);


                echo json_encode(array('response' => 'success','message' => 'session data saved', 'form' => 'edit'));
                return;

        }

        /**
         * update user
         */
        public function update($input) {

                global $vce;

                // load hooks (this hook adds the updated user group to the list of users in that group in the datalist
                if (isset($vce->site->hooks['manage_user_update'])) {
                        foreach($vce->site->hooks['manage_user_update'] as $hook) {
                                call_user_func($hook, $input);
                        }
                }

                $user_id = $input['user_id'];

                $query = "SELECT role_id, vector FROM " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "'";
                $user_info = $vce->db->get_data_object($query);

                $role_id = $user_info[0]->role_id;
                $vector = $user_info[0]->vector;

                // has role_id been updated?
                if ($input['role_id'] != $role_id) {

                        $update = array('role_id' => $input['role_id']);
                        $update_where = array('user_id' => $user_id);
                        $vce->db->update('users', $update, $update_where );

                }

                // clean up
                unset($input['type'],$input['procedure'],$input['role_id'],$input['user_id']);

                // delete old meta data
                foreach ($input as $key => $value) {

                        // delete user meta from database
                        $where = array('user_id' => $user_id, 'meta_key' => $key);
                        $vce->db->delete('users_meta', $where);

                }

                // now add meta data

                $records = array();

                foreach ($input as $key => $value) {

                        // encode user data
                        $encrypted = $vce->user->encryption($value, $vector);

                        $records[] = array(
                        'user_id' => $user_id,
                        'meta_key' => $key,
                        'meta_value' => $encrypted,
                        'minutia' => null
                        );

                }

                $vce->db->insert('users_meta', $records);

                echo json_encode(array('response' => 'success','message' => 'User Updated','form' => 'create','action' => ''));
                return;

        }


        /**
         * Masquerade as user
         */
        public function masquerade($input) {

                global $vce;
                // pass user id to masquerade as
                $vce->user->make_user_object($input['user_id']);


                echo json_encode(array('response' => 'success','message' => 'User masquerade','form' => 'masquerade','action' => $vce->site->site_url));
                return;

        }


        /**
         * Delete a user
         */
        public function delete($input) {

                global $vce;

                // load hooks (this takes the user OUT of the list of users per-group
                if (isset($vce->site->hooks['manage_user_delete'])) {
                        foreach($vce->site->hooks['manage_user_delete'] as $hook) {
                                call_user_func($hook, $input);
                        }
                }

                // delete user from database
                $where = array('user_id' => $input['user_id']);
                $vce->db->delete('users', $where);

                // delete user from database
                $where = array('user_id' => $input['user_id']);
                $vce->db->delete('users_meta', $where);

                echo json_encode(array('response' => 'success','message' => 'User has been deleted','form' => 'delete','user_id' => $input['user_id'] ,'action' => ''));
                return;

        }
	
	/**
	 * Filter
	 */
	public function filter($input) {
	
		global $vce;
		
		foreach ($input as $key=>$value) {
			if (strpos($key, 'filter_by_') !== FALSE) {
				$vce->site->add_attributes($key,$value);
			}
		}
		
		$vce->site->add_attributes('pagination_current',$input['pagination_current']);
	
		echo json_encode(array('response' => 'success','message' =>'Filter'));
		return;
	
	}


	/**
	 * search for a user
	 */
	public function search($input) {
		
		global $vce;
		
		// hook here
		// manage_users_search
		
		// return whatever
		
		if (!isset($input['search']) || strlen($input['search']) < 3) {
			// return a response, but without any results
			echo json_encode(array('response' => 'success','results' => null));
			return;
		}
		
		// break into array based on spaces
		$search_values = explode('|',preg_replace('/\s+/','|',$input['search']));

		// create the IN
		$role_id_in = "";
		foreach (json_decode($vce->site->roles, true) as $key=>$value) {
			if ($key >= $vce->user->role_id) {
				$role_id_in .= $key . ',';
			}
		}
		$role_id_in = rtrim($role_id_in,',');		
		
		// get all users of specific roles as an array
		$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users WHERE role_id IN (" . $role_id_in . ")";
		$find_users_by_role = $vce->db->get_data_object($query, 0);
		

		// cycle through users
		foreach ($find_users_by_role as $key=>$value) {
			// add user_id to array for the IN contained within database call
			$users_id_in[] = $value['user_id'];
			// and these other values
			$all_users[$value['user_id']]['user_id'] = $value['user_id'];
			$all_users[$value['user_id']]['role_id'] = $value['role_id'];
			$all_users[$value['user_id']]['vector'] = $value['vector'];
			// set for search
			$match[$value['user_id']] = 0;
		}
		
		if (!isset($users_id_in)) {
			echo json_encode(array('response' => 'success','results' => null));
			return;
		}

		$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE minutia='' AND user_id IN (" . implode(",",$users_id_in) . ")";
		$users_meta_data = $vce->db->get_data_object($query, 0);
		
		foreach ($users_meta_data as $key=>$value) {
			// decrypt the values
			$all_users[$value['user_id']][$value['meta_key']] = $vce->user->decryption($value['meta_value'], $all_users[$value['user_id']]['vector']);
			// test multiples
			for ($i = 0; $i < count($search_values); $i++) {
				// create a search
				$search = '/^' . $search_values[$i] . '/i';
    			if (preg_match($search, $all_users[$value['user_id']][$value['meta_key']]) && !isset($counter[$value['user_id']][$i])) {
        			// add to specific match
        			$match[$value['user_id']]++;
        			// set a counter to prevent repeats
        			$counter[$value['user_id']][$i] = true;
        			// break so it only counts once for this value
        			break;
    			}
			}
		}
		
		// cycle through match to see if the number is equal to count
		foreach ($match as $match_user_id=>$match_user_value) {
			// unset vector
			unset($all_users[$match_user_id]['vector']);
			// if there are fewer than count, then unset
			if ($match_user_value < count($search_values)) {
				// unset user info if the count is less than the total
				unset($all_users[$match_user_id]);
			}
		}
		
		if (count($all_users)) {
			
			$vce->site->add_attributes('search_value',$input['search']);
			$vce->site->add_attributes('user_search_results',json_encode($all_users));
		
			echo json_encode(array('response' => 'success', 'form' => 'edit'));
			return;
		}
		
		$vce->site->add_attributes('search_value',$input['search']);
		$vce->site->add_attributes('user_search_results',null);
		
		echo json_encode(array('response' => 'success','form' => 'edit'));
		return;
	
	}
	
	
		public function sandbox_search($input = array('search'=>'SANDBOX')) {
		
		global $vce;
		
		// hook here
		// manage_users_search
		
		// return whatever
		
		if (!isset($input['search']) || strlen($input['search']) < 3) {
			// return a response, but without any results
			echo json_encode(array('response' => 'success','results' => null));
			return;
		}
		
		// break into array based on spaces
		$search_values = explode('|',preg_replace('/\s+/','|',$input['search']));

		// create the IN
		$role_id_in = "";
		foreach (json_decode($vce->site->roles, true) as $key=>$value) {
			if ($key >= $vce->user->role_id) {
				$role_id_in .= $key . ',';
			}
		}
		$role_id_in = rtrim($role_id_in,',');		
		
		// get all users of specific roles as an array
		$query = "SELECT user_id, role_id, vector FROM " . TABLE_PREFIX . "users";
		$find_users_by_role = $vce->db->get_data_object($query, 0);
		

		// cycle through users
		foreach ($find_users_by_role as $key=>$value) {
			// add user_id to array for the IN contained within database call
			$users_id_in[] = $value['user_id'];
			// and these other values
			$all_users[$value['user_id']]['user_id'] = $value['user_id'];
			$all_users[$value['user_id']]['role_id'] = $value['role_id'];
			$all_users[$value['user_id']]['vector'] = $value['vector'];
			// set for search
			$match[$value['user_id']] = 0;
		}
		
		if (!isset($users_id_in)) {
			echo json_encode(array('response' => 'success','results' => null));
			return;
		}

		$query = "SELECT * FROM " . TABLE_PREFIX . "users_meta WHERE minutia='' AND user_id IN (" . implode(",",$users_id_in) . ")";
		$users_meta_data = $vce->db->get_data_object($query, 0);
		
		foreach ($users_meta_data as $key=>$value) {
			// decrypt the values
			$all_users[$value['user_id']][$value['meta_key']] = $vce->user->decryption($value['meta_value'], $all_users[$value['user_id']]['vector']);
			// test multiples
			for ($i = 0; $i < count($search_values); $i++) {
				// create a search
				$search = '/^' . $search_values[$i] . '/i';
    			if (preg_match($search, $all_users[$value['user_id']][$value['meta_key']]) && !isset($counter[$value['user_id']][$i])) {
        			// add to specific match
        			$match[$value['user_id']]++;
        			// set a counter to prevent repeats
        			$counter[$value['user_id']][$i] = true;
        			// break so it only counts once for this value
        			break;
    			}
			}
		}
		
		// cycle through match to see if the number is equal to count
		foreach ($match as $match_user_id=>$match_user_value) {
			// unset vector
			unset($all_users[$match_user_id]['vector']);
			// if there are fewer than count, then unset
			if ($match_user_value < count($search_values)) {
				// unset user info if the count is less than the total
				unset($all_users[$match_user_id]);
			}
		}
		
		if (count($all_users)) {
			
			$vce->site->add_attributes('search_value',$input['search']);
			$vce->site->add_attributes('user_search_results',json_encode($all_users));
		
		
// 			echo json_encode(array('response' => 'success', 'form' => 'edit'));
			return json_encode($all_users);
		}
		
		$vce->site->add_attributes('search_value',$input['search']);
		$vce->site->add_attributes('user_search_results',null);
		
		echo json_encode(array('response' => 'success','form' => 'edit'));
		return;
	
	}



	/**
	 * fileds to display when this is created
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