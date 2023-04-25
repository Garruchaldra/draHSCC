<?php

class DatabaseSessions extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'Database Sessions',
			'description' => 'Component to store sessions in the database.',
			'category' => 'admin',
			'recipe_fields' => false
		);
	}
	
	// time unit: minutes, hours
	static $time_unit = 'hours';
	// number of time units
	static $time_unit_number = 6;

	/**
	 * create component specific database table when installed
	 */
	public function installed() {
		global $vce;
		$sql = "CREATE TABLE " . TABLE_PREFIX . "sessions (session_id varchar(255) COLLATE utf8_unicode_ci NOT NULL,session_expires datetime NOT NULL,session_data TEXT COLLATE utf8_unicode_ci, PRIMARY KEY (session_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$vce->db->query($sql);
	}

	/**
	 * delete component specific database table when removed
	 */
	public function removed() {
		global $vce;
		$sql = "DROP TABLE IF EXISTS " . TABLE_PREFIX . "sessions;";
		$vce->db->query($sql);
	}
	
	/**
	 * clear component specific database table when disabled
	 */
	public function disabled() {
		
		// drop and then recreated
		
		global $vce;
		$sql = "DROP TABLE IF EXISTS " . TABLE_PREFIX . "sessions;";
		$vce->db->query($sql);
		
		$sql = "CREATE TABLE " . TABLE_PREFIX . "sessions (session_id varchar(255) COLLATE utf8_unicode_ci NOT NULL,session_expires datetime NOT NULL,session_data TEXT COLLATE utf8_unicode_ci, PRIMARY KEY (session_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$vce->db->query($sql);

	}

	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
		
		$content_hook = array (
		'user_start_session_override' => 'DatabaseSessions::user_start_session_override',
		'user_store_session_override' => 'DatabaseSessions::user_store_session_override',
		'user_logout_override' => 'DatabaseSessions::user_logout_override',
		'site_obtrude_attributes' => 'DatabaseSessions::site_obtrude_attributes',
		'site_add_attributes' => 'DatabaseSessions::site_add_attributes',
		'site_retrieve_attributes' => 'DatabaseSessions::site_retrieve_attributes',
		'site_remove_attributes' => 'DatabaseSessions::site_remove_attributes',
		'user_add_attributes' => 'DatabaseSessions::user_add_attributes',
		'user_remove_attributes' => 'DatabaseSessions::user_remove_attributes'		
		);

		return $content_hook;

	}

	/**
	 * start session
	 */
	public static function user_start_session_override($vce) {
	
// $vce->content->add('main','<div style="background:#ffc;">user_start_session_override: start</div>');
	
		// handles the creation of the cookie, or retrieving session data
	
		if (isset($_COOKIE['_dbs'])) {
			// cookie has already been set, so use value from session id to retrieve session data
			
			$session_id = $_COOKIE['_dbs'];

			$query = "SELECT * FROM " . TABLE_PREFIX . "sessions WHERE session_id = '" . $session_id . "'";
			$results = $vce->db->get_data_object($query);
			
			if (!empty($results)) {

				$session_data_decryption = $vce->site->decryption($results[0]->session_data, $results[0]->session_id);
				
				$session_data = json_decode($session_data_decryption);
							
				// return stored object
				return $session_data->user;
				
			}
			
		}
	
		// destroy the current cookie since no database entry is associated with it
		self::destroy_session_cookie($vce);
		
// $vce->content->add('main','<div style="background:#ccc;">user_start_session_override: null</div>');

		return;

	}


	/**
	 * store session
	 */
	public static function user_store_session_override($user_object, $vce) {
	
// $vce->content->add('main','<div style="background:#9c3;">user_store_session_override: ' . print_r($user_object, true) . '</div>');

		$session_id = null;

		if (isset($_COOKIE['_dbs'])) {

			$session_id = $_COOKIE['_dbs'];
			
			// add $user_object to an array
			$session_data = array('user' => $user_object);
			
			$session_data_value = json_encode($session_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
		
			$session_data_encryption = $vce->site->encryption($session_data_value, $session_id);

			$update = array('session_data' => $session_data_encryption);
			$update_where = array('session_id' => $session_id);
			$vce->db->update('sessions', $update, $update_where);

		} else {

			// no cookie, so there is no session set
			
// $vce->content->add('main','<div style="background:#ccc;">user_store_session_override: No Cookie</div>');

			// add $user_object to an array
			$session_data = array('user' => $user_object);
			
			// set session_id to null so that a new one will be created 
			$session_id = self::create_session($session_data, $vce);
			
			// garbage collection
			
			// set Logan's Run Carousel age for database entries, which currently is set for 2 hours old. 
			$gc_date = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - ' . (self::$time_unit_number * 24) . ' ' . self::$time_unit));
							
			// garbage collection for old session entries in database, and ignoring the current one
			$vce->db->query("DELETE FROM " . TABLE_PREFIX . "sessions WHERE session_id != '" . $session_id . "' AND session_expires <= '" . $gc_date . "'");
	
		}

		return $session_id;
	
	}
		
	
	/**
	 * method to create session
	 */
	private static function create_session($session_data, $vce) {


// $vce->content->add('main','<div style="background:#9c3;">create_session: start</div>');

// $vce->content->add('main','<div style="background:#9c3;">create_session: No Session_id</div>');

		$session_id = self::create_cookie($vce);

// $vce->content->add('main','<div style="background:#9c3;">create_session: ' . $session_id . '</div>');


		$expires = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . '+ ' . self::$time_unit_number .  ' ' . self::$time_unit));
		
		$session_data_value = json_encode($session_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

		$session_data_encryption = $vce->site->encryption($session_data_value, $session_id);

		$user_data = array(
			'session_id' => $session_id,
			'session_expires' => $expires,
			'session_data' => $session_data_encryption
		);
		$vce->db->insert('sessions', $user_data);
		
// $vce->content->add('main','<div style="background:#9c3;">create_session: Add to database</div>');

		return $session_id;
	
	}
	
	
	/**
	 *
	 */
	private static function create_cookie($vce) {

// $vce->content->add('main','<div style="background:#9c3;">create_cookie: start</div>');

		// SameSite value default is Strict
		$same_site = 'Strict';
	
		// check for https within site_url
		if (parse_url($vce->site->site_url, PHP_URL_SCHEME) == "https") {
			$cookie_secure = "; Secure";
			$same_site = 'None';
			// SameSite=None
		} else {
			$cookie_secure = null;
			//  SameSite=Strict
		}
		
		// get url path
		$url_path = parse_url($vce->site->site_url, PHP_URL_PATH);
		// if this has a value, set cookie_path
		if (empty($url_path)) {
			$url_path = '/';
		}

		// get a vector value
		$session_id = $vce->site->create_vector();
		
		$cookie = "_dbs=" . $session_id . "; Path=" . $url_path . $cookie_secure . "; HttpOnly; SameSite=" . $same_site;
		
		// false flag allows for multiples of this header
        header("Set-Cookie: " . $cookie, false);
		
		return $session_id;
	
	}


	/**
	 * delete cookie
	 */
	private static function destroy_session_cookie($vce) {
	
		$same_site = 'Strict';
	
		// check for https within site_url
		if (parse_url($vce->site->site_url, PHP_URL_SCHEME) == "https") {
			$cookie_secure = "; Secure";
			$same_site = 'None';
		} else {
			$cookie_secure = null;
		}
		
		// get url path
		$url_path = parse_url($vce->site->site_url, PHP_URL_PATH);
		// if this has a value, set cookie_path
		if (empty($url_path)) {
			$url_path = '/';
		}
		
		// 7 days in the past
		$expires_time = time() - 604800;
	
		// Wed, 25 Mar 2020 16:46:58 GMT
		$cookie_expires = gmdate('D, d M Y H:i:s', $expires_time) . ' GMT';
		
		$cookie_value = '';
		
		// Max-Age=0 should expire the cookie immediately.
		
		$cookie = "_dbs=" . $cookie_value . "; Expires=" . $cookie_expires . "; Max-Age=0; Path=" . $url_path . $cookie_secure . "; HttpOnly; SameSite=" . $same_site;

		// false flag allows for multiples of this header
		header("Set-Cookie: " . $cookie, false);

	}
	

	/**
	 * delete
	 */
	public static function user_logout_override($user_id) {
	
		if (isset($_COOKIE['_dbs'])) {
		
			global $vce;
			
			$session_id = $_COOKIE['_dbs'];
			
			// delete the db session
			$where = array('session_id' => $session_id);
			$vce->db->delete('sessions', $where);
		
			self::destroy_session_cookie($vce);
        }
	
	}

	
	/**
	 * site_obtrude_attributes
	 */
	public static function site_obtrude_attributes($vce) {

		global $vce;

		// get our session data
		$session = self::get_session_data($vce);

		if (!empty($session)) {
		
			if (isset($session['session_data']['add_attributes'])) {
				foreach ($session['session_data']['add_attributes'] as $key=>$value) {
					// if there is a persistent value set
					if ($key == 'persistent') {
						$persistent = $value;
						foreach ($persistent as $persistent_key=>$persistent_value) {
							$vce->$persistent_key = $persistent_value;
							$vce->site->$persistent_key = $persistent_value;
						}
					} else {
						// normal value
						$vce->$key = $value;
						$vce->site->$key = $value;
					}
				}
 
			}
			
			// clear it
			unset($session['session_data']['add_attributes']);
			// rewrite if persistent value had been set
			if (isset($persistent)) {
				$session['session_data']['add_attributes'] = array('persistent' => $persistent);
			}
			
			// create a new session id and save a new database record with it
			if (strtotime($session['session_expires']) <= time()) {
			
				// but only do this if there is not a valid dossier being posted
				if (isset($_POST['dossier']) && !empty(json_decode($vce->site->decryption($_POST['dossier'], $vce->site->session_vector)))) {
					return;
				}

				// delete the db session
				$where = array('session_id' => $session['session_id']);
				$vce->db->delete('sessions', $where);
				
				self::create_session($session['session_data'], $vce);
				
				return;
			
			}
			
// $vce->log('- - -');
// $vce->log('site_obtrude_attributes');
// $vce->log('- - -');


			// put session data into database
			self::put_session_data($session, $vce);
			
		}
	
	}
	
	
	/**
	 * site_add_attributes
	 */
	public static function site_add_attributes($key, $value, $persistent) {
	
		global $vce;
		
		// get our session data
		$session = self::get_session_data($vce);
		
		if (!empty($session)) {
		
			if ($persistent) {
				// add to persistent sub array
				$session['session_data']['add_attributes']['persistent'][$key] = $value;
			} else {
				// add as normal
				$session['session_data']['add_attributes'][$key] = $value;
			}

// $vce->log('- - -');
// $vce->log('site_add_attributes');
// $vce->log('- - -');

			// put session data into database
			self::put_session_data($session, $vce);
		
		}
	
	}
		
		
	/**
	 * retrieve_attributes
	 */
	public static function site_retrieve_attributes($key) {
	
		global $vce;
		
		$attribute_value = null;
		
		// get our session data
		$session = self::get_session_data($vce);
		
		if (!empty($session)) {
			if (isset($session['session_data']['add_attributes']['persistent'][$key])) {
				$attribute_value =  $session['session_data']['add_attributes']['persistent'][$key];
			}
			if (isset($session['session_data']['add_attributes'][$key])) {
				$attribute_value =  $session['session_data']['add_attributes'][$key];
			}
		}
		
		return $attribute_value;
		
	}
	
	
	/**
	 * site_remove_attributes
	 */
	public static function site_remove_attributes($key, $on_user = false) {
	
		global $vce;
		
		// get our session data
		$session = self::get_session_data($vce);
		
		if (!empty($session)) {
		
			if ($on_user) {
				unset($session['session_data']['user'][$key]);
			} else {
 				unset($session['session_data']['add_attributes']['persistent'][$key], $session['session_data']['add_attributes'][$key]);
			}

// $vce->log('- - -');
// $vce->log('site_remove_attributes');
// $vce->log('- - -');

			// put session data into database
			self::put_session_data($session, $vce);
		
		}
	
	}

	/**
	 * user_add_attributes
	 */
	public static function user_add_attributes($key, $value) {
	
		global $vce;
		
		// get our session data
		$session = self::get_session_data($vce);
		
		if (!empty($session)) {
			$session['session_data']['user'][$key] = $value;

			// put session data into database
			self::put_session_data($session, $vce);
		
		}
	
	}

	/**
	 * user_remove_attributes
	 */
	public static function user_remove_attributes($key) {
	
		global $vce;
		
		// get our session data
		$session = self::get_session_data($vce);
		
		if (!empty($session)) {
		
			unset($session['session_data']['user'][$key]);
			
			// put session data into database
			self::put_session_data($session, $vce);
		
		}
	
	}

	
	/**
	 * get session data
	 */
	private static function get_session_data($vce) {

		if (isset($_COOKIE['_dbs'])) {

			$session_id = $_COOKIE['_dbs'];

			$query = "SELECT * FROM " . TABLE_PREFIX . "sessions WHERE session_id = '" . $session_id . "'";
			$results = $vce->db->get_data_object($query);
	
			if (!empty($results)) {
			
				$session_id = $results[0]->session_id;
			
				$session_expires = $results[0]->session_expires;
			
				// get and decrypt session data
				$session_data_decryption = $vce->site->decryption($results[0]->session_data, $results[0]->session_id);

				$session_data = json_decode($session_data_decryption, true);
						
				return array('session_id' => $session_id, 'session_expires' => $session_expires ,'session_data' => $session_data);
				
			}
		}
		
		return null;
	}
	
	
	/**
	 * put session data back into the database
	 */
	private static function put_session_data($session, $vce) {

// $vce->content->add('main','<div style="background:#ccc;">put_session_data: start</div>');
	
		$session_data_value = json_encode($session['session_data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
		
		$session_data_encryption = $vce->site->encryption($session_data_value, $session['session_id']);
		
		// update record
		$update = array('session_data' => $session_data_encryption);
		$update_where = array('session_id' => $session['session_id']);
		$vce->db->update('sessions', $update, $update_where);
	
	}    

}