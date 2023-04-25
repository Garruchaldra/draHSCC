<?php

class Pbc_notifications extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PBC Notifications',
			'description' => 'Link from login (check_access) to create a custom notification which needs to be checked to continue.',
			'category' => 'pbc'
		);
	}


	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
	
		$content_hook = array (
			'login_check_access_true' => 'Pbc_notifications::check_notifications'
		);

		return $content_hook;

	}
	
	/**
	 *
	 */
	public static function check_notifications($each_component, $vce) {
		global $site;
		
// $site->dump($vce->user);
		// add javascript to page
		$vce->site->add_script(dirname(__FILE__) . '/js/script.js', 'notification');	

		//set method variables for ease of use
		$first_name = isset($vce->user->first_name) ? $vce->user->first_name : null;
		$last_name = isset($vce->user->last_name) ? $vce->user->last_name : null;
		$user_id = isset($vce->user->user_id) ? $vce->user->user_id : null;
		//this is just an attempt to use a separate user object on the fly. I decided not to use it, but to
		//set it equal to the $user object
		$temp_user_object = $vce->user;
		// $temp_user_object = self::make_user_object($user_id);

// $site->dump($temp_user_object);

		// This is the list of notifications. Use titles which will be displayed for users. Add each title as the key for the next
		// element in the $notification_titles array.
		// the values are used for role assignment. Empty is for everyone
		$notification_titles = array();
		$notification_titles['Organization and Group Administrator Liability: Shared Media'] = array('target_audience' => '1|2|3|4', 'metadata' => array('date' => date(DATE_RFC2822)));
		// $notification_titles['Test Notification'] = '1';
		// $notification_titles['Second Test'] = array('target_audience' => '1|2|3|4', 'metadata' => array('date' => date(DATE_RFC2822)));
		$notification_titles['Welcome Notification'] = array('target_audience' => '1|2|3|4|5|6', 'metadata' => array('date' => date(DATE_RFC2822)));
		$notification_titles['Intake Form'] = array('target_audience' => '1|2|3|4|5|6|7|8', 'metadata' => array('date' => date(DATE_RFC2822)));



		//check to see if the user needs to acknowledge the notification
		foreach ($notification_titles as $key => $value) {
			// if the value is not set, then everyone sees the notification
			if (!isset($value['target_audience'])) {
				$value['target_audience'] = '1|2|3|4|5|6|7|8';
			}
			$value_array = explode('|', $value['target_audience']);
// $site->dump($value_array);
			$use_notification = false;
			foreach ($value_array as $role) {
				if ($role == $vce->user->role_hierarchy) {
					$use_notification = true;
				}
			}
			// take out any notification which does not have a role_hierarchy match
			if ($use_notification == false) {
				unset($notification_titles[$key]);
			}
		}
// $site->dump($notification_titles);
		if (!empty($notification_titles)) {
			//check if the user has already acknowledged the notification
			$list_of_notifications = '';
			foreach ($notification_titles as $key => $value) {
				$attr_key = strtolower(preg_replace('/ /', '_', $key));
				$list_of_notifications .= "'$attr_key',";
			}
			$list_of_notifications = trim($list_of_notifications, ',');

			$query = "SELECT meta_key, meta_value FROM " . TABLE_PREFIX . "site_meta WHERE meta_key IN ($list_of_notifications)";
			$site_meta_data = $vce->db->get_data_object($query);

			$query = "SELECT meta_key, meta_value, minutia FROM " . TABLE_PREFIX . "users_meta WHERE user_id = $user_id AND meta_key in ($list_of_notifications)";
			$user_meta_data = $vce->db->get_data_object($query);

			foreach ($notification_titles as $key => $value) {
				// remove whitespaces
				$notification_name = strtolower(preg_replace('/ /', '_', $key));

				// remove any notifications which are already in DB users_meta table
				foreach ($user_meta_data as $each_meta) {
					if(isset($each_meta->meta_key)) {
						if ($each_meta->meta_key == $notification_name) {
							unset($notification_titles[$key]);
						}
					}
				}

				// remove any notifications which are already in DB site_meta table
				foreach ($site_meta_data as $each_meta) {
					if(isset($each_meta->meta_key)) {
						if ($each_meta->meta_key == $notification_name && $each_meta->meta_value == 'FALSE') {
							unset($notification_titles[$key]);
						}
					}
				}

			}
		}

if (!empty($notification_titles)) {
$content = '';
$default_content_head = <<<EOF
<p>
<div class="clickbar-container">
<div class="clickbar-content clickbar-open">
Hello $first_name $last_name!<br><br>
Please read and acknowledge the notifications on this page.<br>
Notifications appear only until they have been acknowledged.
<br>
<br>

</div>
<div class="clickbar-title disabled"><span>Notifications</span></div>
</div>
</p>
EOF;

$default_content_foot = <<<EOF
<p style="padding:15px">
	<input type="submit" value="Submit">
</p>
<div class="label-text">
<div class="label-message">$notification_title</div>
<div class="label-error">Please Check Each Box to Acknowledge Reading</div>
</div>
</label>
</form>
EOF;


foreach ($notification_titles as $key => $value) {
	$content .= self::display_notifications($vce, $key, $value['metadata']);
}




			$vce->content->add('main', $content);
			return false;
		} else { 
			return true;
		}
	
	}


	/**
	 * Show the content of any notifications
	 * 
	 */
	public static function display_notifications($vce, $this_title, $this_metadata) {

		$content =  '';
		$notification_name = strtolower(preg_replace('/ /', '_',  $this_title));
		$notification_title = $this_title;
		//get user vector
		$query = $query = "SELECT vector  FROM " . TABLE_PREFIX . "users WHERE user_id = '" . $vce->user->user_id ."'";
		$user_data = $vce->db->get_data_object($query);
		foreach ($user_data as $each_user_data) {
			$user_vector = $each_user_data->vector;
		}
		$notification_metadata = $vce->user->encryption(json_encode($this_metadata),$user_vector);
		// $vce->site->dump($user_vector);
		
		$dossier_for_notification = $vce->user->encryption(json_encode(array('type' => 'Pbc_notifications','procedure' => 'acknowledge_notifications', 'notification_name' => $notification_name, 'notification_metadata' => $notification_metadata, 'user_id' => $vce->user->user_id)),$vce->user->session_vector);

$content .= <<<EOF
<form id="form" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_notification">
EOF;


//this is the actual notification. Copy, paste, and edit for each notification
switch ($notification_title) {
	case 'Organization and Group Administrator Liability: Shared Media' :
$content .= <<<EOF
<p style="padding:15px">
	<span style="font-size:120%;font-weight: bold;">
		Organization and Group Administrator Liability: Shared Media
	</span>
<br>
<br>
	<span style="font-weight: bold;">
		Review the liability waivers related to media-sharing for organization and group administrators. Select each box to continue.
	</span>
<br>

		<span>
			<input type="checkbox" name="point_1" tag="required">I acknowledge that this tool allows for the exchange of media between coaches and coachees in my organization, and neither the Office of Head Start (OHS), the National Center on Early Childhood Development, Teaching, and Learning (NCECDTL), nor the hosting platform are responsible for misuse of the media shared.
			</input><br><br>
		</span>

		<span>
		<input type="checkbox" name="point_2" tag="required">
			Appropriate media-sharing (e.g., video and photos) is limited to the platform and exclusively between coach and coachee or other participants in the Practice-Based Coaching (PBC) cycle.
		</input><br><br>
		</span>

		<span>
		<input type="checkbox" name="point_3" tag="required">
			It is strictly forbidden to download or stream shared media for other purposes, such as training and technical assistance (T/TA) or for use in presentations, website posting, or social media, in either a professional or personal capacity.
		</input><br><br>

		</span>

		<span>
		<input type="checkbox" name="point_4" tag="required">
			I acknowledge that my organization or group has policies in place that are communicated to all employees or consultants participating in each PBC cycle.
		</input><br><br>
		</span>

		<span>
		<input type="checkbox" name="point_5" tag="required">
			I acknowledge that, as the administrator, I am responsible for removing any participant who no longer works for or is contracted by my organization.
		</input><br><br>
		</span>

		<span>
		<input type="checkbox" name="point_6" tag="required">
			In the event that the organization or group is accepting external consultants as coaches, I understand that I am responsible for incorporating clauses in the contract that protect the confidentiality of those in the media shared in each PBC cycle.
		</input><br><br>
		</span>
		<span>
			<button type="submit" class="btn button__primary got-it">Submit</button>
		</span>
</p>
</form>
EOF;
		break;
		case 'Test Notification' :
			$content .= <<<EOF
			<p style="padding:15px">
				<span style="font-size:120%;font-weight: bold;">
					Organization and Group Administrator Liability: Shared Media
				</span>
			<br>
			<br>
				<span style="font-weight: bold;">
					Review the liability waivers related to media-sharing for organization and group administrators. Select each box to continue.
				</span>
			<br>
			
					<span>
						<input type="checkbox" name="point_1" tag="required">I acknowledge that this tool allows for the exchange of media between coaches and coachees in my organization, and neither the Office of Head Start (OHS), the National Center on Early Childhood Development, Teaching, and Learning (NCECDTL), nor the hosting platform are responsible for misuse of the media shared.
						</input><br><br>
					</span>

			
			</p>
EOF;
			break;
			case 'Welcome Notification' :
			$content .= <<<EOF
<div id="welcome-modal" class="modal">
	<div class="modal-content">
		<div class="close">&times;</div>
		<h1>Welcome to the Head Start Coaching Companion</h1>
		<div class="welcome-modal__arrows-container">
			<h2>Here's how it works:</h2>
			<div class="progress-arrows__container progress-arrows__show">
					<div class="progress-arrows progress-arrows__one progress-arrows__active"></div>
					<div class="down-arrow"></div>
					<div class="progress-arrows progress-arrows__two"></div>
					<div class="down-arrow"></div>
					<div class="progress-arrows progress-arrows__three"></div>
					<div class="down-arrow"></div>
					<div class="progress-arrows progress-arrows__four"></div>
					<p class="progress-arrow-text progress-arrow-text__one">Create a Practice-Based Coaching (PBC) Cycle</p>
					<p class="progress-arrow-text progress-arrow-text__two">Choose a measurable goal for the cycle</p>
					<p class="progress-arrow-text progress-arrow-text__three">Come up with actionable steps to reach your goal</p>
					<p class="progress-arrow-text progress-arrow-text__four">Add focused observations, reflection & feedback</p>
				</div>
		</div>
		<p>Coaching Companion is built around the idea of Practice-Based Coaching (PBC). To learn more about PBC, visit the 
			<a href="https://eclkc.ohs.acf.hhs.gov/professional-development/article/practice-based-coaching-pbc" target="_blank">
			Early Childhood Learning and Knowledge Center (ELCKC)</a>.
		</p>
		<h2>Coaching Companion Tips</h2>
		<div class="tips-text">
			<div class="light-bulb-icon tip1"></div><p class="tip1"><span class="important-text">Important:</span> When recording videos for upload, we recommend <span class="bold-text">lowering the resolution on your recording device</span> for 
			faster upload times.</p>
			<div class="light-bulb-icon tip2"></div><p class="tip2">Coaching Companion is now fully accessible on mobile devices and tablets.</p>
		</div>
			<button type="submit" class="btn button__primary got-it">Ok, got it!</button>
	</div>
</div>
</form>
EOF;
					break;	
			case 'Intake Form':
				$site_url = $vce->site->site_url ;
				$intake_url = $site_url . '/intake-form';
				header("Location: $intake_url");
// 				$content .= <<<EOF
// 				<p style="padding:15px">
// 					<span style="font-size:120%;font-weight: bold;">
// 						Intake Form Test: Shared Media
// 					</span>
// 				<br>
// 				<br>
// 					<span style="font-weight: bold;">
// 						Review the liability waivers related to media-sharing for organization and group administrators. Select each box to continue.
// 					</span>
// 				<br>
				
// 						<span>
// 							<input type="checkbox" name="point_1" tag="required">I acknowledge that this tool allows for the exchange of media between coaches and coachees in my organization, and neither the Office of Head Start (OHS), the National Center on Early Childhood Development, Teaching, and Learning (NCECDTL), nor the hosting platform are responsible for misuse of the media shared.
// 							</input><br><br>
// 						</span>
	
				
// 				</p>
// EOF;
				break;
		default:
			break;


}



		return $content;

	}

	/**
	 * record acknowledgement of notification
	 */
	public function acknowledge_notifications($input) {
		global $site;
		global $user;	
		global $vce;
		
// $vce->log($input);
// exit;

		// rekey input
		foreach ($input as $key=>$value) {
			$$key = $value;
		}
		
		$notification_title = ucwords(preg_replace('/_/', ' ', $notification_name));
		// $notification_metadata = $user->encryption($notification_metadata, $user->vector);
		//set new user attribute, using the name of the notification
		$query1 = "INSERT INTO " . TABLE_PREFIX . "users_meta (user_id, meta_key, meta_value, minutia) VALUES ('$user_id','$notification_name','$notification_metadata', '" . time() . "')";
		// $site->log($query1);
		$vce->db->query($query1);


		echo json_encode(array('response' => 'success','message' => "Your acknowledgement of $notification_title has been saved.",'form' => 'create','action' => ''));
		return;

	}




		/**
	 * Creates user object from user_id
	 * @global object $site
	 * @param string $user_id
	 * @return call to self::store_session()
	 */
	public function make_user_object($user_id) {

		// database
		global $vce;
		
		// site
		global $site;

		global $user;
		
		// create array to contain values
		$user_object = array();
		

		
		// get user_id,role_id, and vector
		$query = "SELECT user_id,vector,role_id FROM  " . TABLE_PREFIX . "users WHERE user_id='" . $user_id . "' LIMIT 1";
		$results = $vce->db->get_data_object($query);

		if ($results) {

			// loop through results
			foreach ($results[0] as $key => $value) {
				//add values to user object
				$user_object[$key] = $value;
			}
			
			// grab all user meta data that has no minutia
			$query = "SELECT meta_key,meta_value FROM  " . TABLE_PREFIX . "users_meta WHERE user_id='" . $user_object['user_id'] . "'";
			$metadata = $vce->db->get_data_object($query);

			if ($metadata) {
			
				// look through metadata
				foreach ($metadata as $array_key => $each_metadata) {

					// decrypt the values using vi/vector for decrypting user meta data
					$value = $user->decryption($each_metadata->meta_value, $user_object['vector']);

					// add the values into the user object	
					$user_object[$each_metadata->meta_key] = $vce->db->clean($value);	
				}

				// we can then remove vector from the user object
				unset($user_object['vector'],$user_object['lookup'],$user_object['persistant_login']);
				
				// add user meta data specific to site roles.
				$roles = json_decode($site->roles, true);
				
				// check if role associated info is an array
				if (is_array($roles[$user_object['role_id']])) {
					// add key=>value to user object if they don't already exist.
					// user_meta key=>value takes precidence over role key=>value
					// this allows for user specific granulation of permissions, et cetera
					foreach ($roles[$user_object['role_id']] as $role_meta_key=>$role_meta_value) {
						// check if the value is an array
						if (is_array($role_meta_value)) {
							$suffix = '_' . $role_meta_key;
							foreach ($role_meta_value as $sub_meta_key=>$sub_meta_value) {
								// add simple key=>value to user object
								if (!isset($user_object[$sub_meta_key . $suffix])) {
									$user_object[$sub_meta_key . $suffix] = $sub_meta_value;
								} else {
									// add on to existing
									$user_object[$sub_meta_key . $suffix] .= ',' . $sub_meta_value;
								}
							}
						} else {
							// add simple key=>value to user object
							if (!isset($user_object[$role_meta_key])) {
								$user_object[$role_meta_key] = $role_meta_value;
							} else {
								$user_object[$role_meta_key] .= ',' . $role_meta_value;
							}
						}					
					}		
				}

				// create a session vector
				// this is used to create an edit / delete token for components
				$user_object['session_vector'] = $user::create_vector();

				// rekey user object
				// foreach ($user_object as $key => $value) {
				// 	$this->$key = $value;
				// }

				return $user_object;

			}
			
		}

	}
	

	public function get_notifications() {
		// get all installed components
			foreach (new DirectoryIterator(BASEPATH . $components_dir . '/components/')  as $key=>$each_component) {
		
				// base path for each component
				$base_component_path = $components_dir . '/components/' . $each_component->getFilename();
				
				// filter out dot files et cetera
				if ($each_component->isDir() && !$each_component->isDot()) {
			
					// full file path to component with script name the same as the directory name
					$component_path = $base_component_path . '/' . $each_component->getFilename() . '.php';
					
					// check that file exists first before attempting to get it's contents
					if (!file_exists(BASEPATH . $component_path)) {
						continue;
					}
			
					// get the file content to search for Class name
					$component_text = file_get_contents(BASEPATH . $component_path, NULL, NULL, 0, 100);
			
					// looking for Child Class name
					$pattern = "/class\s+([([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+)\s+extends\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+)\s+{/m";
	
					// found that
					if (preg_match($pattern, $component_text, $matches)) {
					
						if (isset($matches[1]) && class_exists($matches[2])) {
						
							// Class name for component
							$type = $matches[1];
							$parent = $matches[2];
										
							// check if class already exists, and if it does, then skip ahead
							if (isset($components_list[$type])) {
								continue;
							}
			
							// require the component script
							require_once(BASEPATH . $component_path);
			
							// create an instance of the Class
							$current_component = new $type();
							
							// add type to list to check against later
							$components_list[$type] = true;
						
							// get compontent info, such as name and description
							$info = $current_component->component_info();
							
							// add category to array for sorting
							$categories[$info['category']] = true;
							
							$content .= '<div class="all-components each-component ' . $info['category'] . '-component" type="' .  $type . '" parent="' . $parent . '" url="' . $component_path . '" state="';
							
							if (isset($activated_components[$type])) {
								$content .= 'activated';
							} else {
								$content .= 'disabled';
							}
						
							$content .= '">';
						
							$content .= '<div class="each-component-switch"><div class="switch activated';
						
							if (isset($activated_components[$type])) {
								$content .= ' highlight';
							}
							
							if (!isset($installed_components[$type])) {						
								$content .= ' install';
							}
						
							$content .= '">';
							
							if (!isset($installed_components[$type])) {						
								$content .= 'Install';
							} else {
								$content .= 'Activated';
							}
							
							$content .= '</div><div class="switch disabled';
						
							if (!isset($activated_components[$type])) {
								$content .= ' highlight';
							}
						
							$content .= '">Disabled</div>';
							
							// if ASSETS_URL has been set, hide delete because site is using a shared vce 
							if (!isset($activated_components[$type]) && !defined('ASSETS_URL')) {
							
	
							}
						}
					}
				}
			}
		
	}




	public function recipe_fields($recipe) {
	
		$title = isset($recipe['title']) ? $recipe['title'] : self::component_info()['name'];
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