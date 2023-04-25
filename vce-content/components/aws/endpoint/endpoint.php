<?php
  
/**
 * Endpoint for RESTFul functions
 *
 * @category   Endpoint
 * @package    MEET
 * @author     Andy Sodt <asodt@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */

include_once "aws_media_controller.php";
include_once "login_controller.php";

/**
 * Endpoint for RESTFul functions
 */
class Endpoint extends Component
{

	/**
	 * basic info about the component
	 */
	public function component_info()
	{
		return array(
			// basic component properties are required
			'name' => 'Endpoint',
			'description' => 'Endpoint for RESTFul functions',
			'category' => 'admin',
			'recipe_fields' => array(
				'auto_create',
				'title',
                array('url' => 'required'),
            )
		);
	}

    /**
     * output to page
     */
    public function as_content($each_component, $vce) {

        $content = file_get_contents(dirname(__FILE__) . '/info.html');
        $vce->content->add('main', $content);

    }

    /**
	 * This method can be used to route a url path to a specific component method. 
	 */
	public function path_routing() {
	
		$path_routing = array(
            'endpoint/view' => array('Endpoint','get_component_tree'),
            'endpoint/get_component_tree' => array('Endpoint','get_component_tree'),
            'endpoint/browser_view' => array('Endpoint','browser_view'),
            'endpoint/input' => array('Endpoint','input_response'),
            'endpoint/token' => array('Endpoint','grant_oauth2_token'),
            'endpoint/oauth2' => array('Endpoint','grant_oauth2_token'),
            'endpoint/user' => array('Endpoint','get_user_info'),
			'endpoint/{controller}/{id}' => array('Endpoint','restful_call'),
            'endpoint/{controller}' => array('Endpoint','restful_call'),
		);
		 
		return $path_routing;

    }

    public function get_user_info() {

        global $vce;

        $args = $this->parse_request();

        $this->add_user_and_page($vce, $args);
        $this->authorize($vce);

        $data = $args->data;

        if (isset($data->email) && isset($data->password)) {

            $data->email = strtolower($data->email);

            $vce->user->login((array)$data);
            $aws_config = LoginController::get_config($vce);
            $logged_in_user_with_aws_config = (array)$vce->user;
            $logged_in_user_with_aws_config = array_merge($logged_in_user_with_aws_config, $aws_config);
   
   			// adding menus into the returned
   			$logged_in_user_with_aws_config['site_menus'] = json_decode($vce->site->site_menus);
   
            echo json_encode($logged_in_user_with_aws_config, JSON_PRETTY_PRINT) . "\n";

        }

        die();
        
    }

    /**
     * Returns html for the requested component tree.
     * 
     * Here is an example post or get: http://localhost:8888/washingtoncc/endpoint/get_component_tree?url=<url>&user_id=<user_id>&access_token=<token>
     * 
     * The url is a releative url, like my-resources/2/1551740937 
     * 
     * @return echo json
     */
    public function get_component_tree() {

        global $vce;

        $args = $this->parse_request();

        // get 'home' url to work.  /
        if (empty($args->url)) {
            $args->url = '/';
        }
        
        // the url argument is most likly url encoded, so decode and trim it.
        $args->url = urldecode($args->url);
        $site_url = $vce->site->site_url;
        $args->url = trim(preg_replace("#^$site_url#i", '', $args->url), '/');

        // temp fix
        if (substr( $args->url, 0, 2 ) === "c/") {
            $args->url = substr($args->url, 2, strlen($args->url));
        }
        
        // Authorize the call
        $this->add_user_and_page($vce, $args);
        $this->authorize($vce);

        // Site related tasks
//        $full_requested_url = $vce->site->parse_requested_url($vce, $args->url);
//        $query_string = $vce->site->parse_query_string($vce, $vce->site->requested_url);
//        $post_variables = $vce->site->parse_post_variables($vce);
//        $vce->site->requested_url = $args->url;
//        $vce->site->parse_path_routing($vce, $query_string, $post_variables);

        $full_requested_url = $args->url;
        $requested_url_array = explode('?', $full_requested_url);

        // url path without query string
        $requested_url = trim($requested_url_array[0], '/');

        // get the trimmed site url path
        $site_url = trim(parse_url($vce->site->site_url, PHP_URL_PATH ), '/');

        // clean up the requested url by triming $requested_url slashes before and after, removing $site_url from $requested_url. 
        // # is used instead of / to prevent unknown modifier error
        $vce->site->requested_url = trim(preg_replace("#^$site_url#i", '', $requested_url), '/');

        $query_string = $vce->site->parse_query_string($vce, $full_requested_url);

        // Page related tasks
        $vce->page->construct_content($vce);

        // Here we change the input path so that the mobile app will hit this endpoint.
        $vce->input_path = $vce->site->site_url . '/endpoint/input';
            
        echo json_encode($vce->content->store['default'], JSON_PRETTY_PRINT);

        die();

    }

    /**
     * Returns html for the requested component tree.
     * 
     * Here is an example post or get: http://localhost:8888/ohscc2/endpoint/browser_view?url=http%3A%2F%2Flocalhost%3A8888%2Fohscc2%2Fassignments&access_token=eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpZCI6ImVhZjI5MTVhOTAzM2Y3YTE3ZmVjMTBhODcxNzU4MjI5ODQ1N2FlMDEiLCJqdGkiOiJlYWYyOTE1YTkwMzNmN2ExN2ZlYzEwYTg3MTc1ODIyOTg0NTdhZTAxIiwiaXNzIjoiIiwiYXVkIjoiYXNvZHRAdXcuZWR1Iiwic3ViIjoiM010TDJRbUp5WTlHXC9QekFZSjhXeGc9PSIsImV4cCI6MTY0ODUwMTUwNywiaWF0IjoxNjQ4NDk3OTA3LCJ0b2tlbl90eXBlIjoiYmVhcmVyIiwic2NvcGUiOm51bGx9.OYKLh3GFFszvpRfUWkp9k28nzWVZ_TSsI66-oi44VNxQJtZ6T6vivN0Byw3HercREX30T1vlLj_CEhy39-Sk8EpQLFY-TGTe2Z7TOnywQhnrL5Nf_gP2bfG62b5LiNSv-PCTOFkQ-Ar0TCpRtdbbotuLXj_cnsBT51oS0Cx2o3ocCjlUStNU3apzEs6hdK-MwKdd6_w4LW5eCbHZK6mbSshV5ZZS1fmGRfd8tGajTHMa1Zf_4OxBwZj6zKiLUXjD3PbCt8JnZfizdEW4BPklNnFFSxW8TfZHxYw1YWuY3dtD0bdx4FXsx3qDDXpng5F3cqmS5P2GcSrvokfL8uCQKA
     */
    public function browser_view() {

        global $vce;

        $vce->site->add_script(dirname(__FILE__) . '/js/script.js');

        $args = $this->parse_request();

        // the url argument is most likly url encoded, so decode it.
        $args->url = urldecode($args->url);

        $this->add_user_and_page($vce, $args);
        $this->authorize($vce);
        $vce->user->make_user_object($vce->user->user_id);

        header("location: " . $args->url);

        die();

    }

    public function trim_query_string($url) {

        $url = explode('?', $url);

        // url path without query string
        $url = trim($url[0], '/');
       
        return $url;
        
    }

    /**
     * Returns html for the requested input on _POST.
     * 
     * @return echo json response
     */
    public function input_response() {

        global $vce;

        $args = $this->parse_request();

        $this->add_user_and_page($vce, $args);

        $this->authorize($vce);

        // exit if no dossier is set
		if (!isset($_POST['dossier'])) {
            echo json_encode(array('response' => 'error','message' => 'Dossier not found'));
			exit();
		}

		// decryption of dossier and cast json_decode as an array, mostly to keep the $_POST array concept alive
		// continues through to procedures where $input is worked with as an array
		$dossier = json_decode($vce->user->decryption($_POST['dossier'], $vce->user->session_vector), true);

		// which component type to send this input data to
		$type = preg_replace("/[^A-Za-z0-9_]+/", '', trim($dossier['type']));

		// list of input types as json object
		// we could use this to sanitize different input types
		// $_POST['inputtypes'];
		// json can be set as the input type when using the asynchronous-form path by adding schema="json" within the input element

		$inputtypes = array();
		if (isset($_POST['inputtypes'])) {
			$inputtypes_decode = json_decode($_POST['inputtypes'],true);
			if (!empty($inputtypes_decode)) {
				foreach ($inputtypes_decode as $each_input) {
					if (isset($each_input['name'])) {
						$inputtypes[$each_input['name']] = $each_input['type'];
					}
				}
			}
		}

		// unset what is not needed and prevent component type and component procedure from being changed
		unset($_POST['type'],$_POST['procedure'], $_POST['dossier'], $_POST['inputtypes'], $_POST['access_token']);

		// create array to pass on
		$input = array();

		// add dossier values first
		foreach ($dossier as $key=>$value) {
			$input[$key] = $value;
		}

		// sanitize and rekey
		foreach ($_POST as $key=>$value) {
			// select input elements with multiple
			// note: name of input element needs to have a [] at the end ( test[] ) to tell PHP to place contents into array
			if (is_array($value)) {
				$sanitized = array();
				foreach ($value as $each_value) {
					$sanitized[] = filter_var($each_value, FILTER_SANITIZE_STRING);
				}
				$input[$key] = $sanitized;
				continue;
			}
			// else everything else
			$value = trim($value);
			if (isset($inputtypes[$key])) {
				if ($inputtypes[$key] == 'json') {
					// make sure that the json object is valid
					// value will be passed as a json object into the procedure
					json_decode($value);
					if (json_last_error() == JSON_ERROR_NONE) {
						$input[$key] = $value;
					} else {
						// json error reporting here
						$input[$key] = 'json object error';
					}
				} elseif ($inputtypes[$key] == 'textarea') {
					// load hooks
					if (isset($vce->site->hooks['input_sanitize_textarea'])) {
						foreach($vce->site->hooks['input_sanitize_textarea'] as $hook) {
							$value = call_user_func($hook, $value);
						}
					} else {
						// textarea default is FILTER_SANITIZE_STRING
						$value = filter_var($value, FILTER_SANITIZE_STRING);
					}
					// remove line returns if input contains html
					if ($value != strip_tags($value)) {
						$value = str_replace(array("\r", "\n"), '', $value);
					}
					// add to input
					$input[$key] = $vce->db->sanitize($value);
				} else {
					// default filtering
					$input[$key] = $vce->db->sanitize($value);	
				}
			} else {
				// this will be updated when manange recipes and menus is updated
				if ($key === 'json') {
					// make sure that the json object is valid
					// value will be passed as a json object into the procedure
					json_decode($value);
					if (json_last_error() == JSON_ERROR_NONE) {
						$input[$key] = $value;
					} else {
						// json error reporting here
						$input[$key] = 'json object error';
					}
				} else {
					// default filtering
					$input[$key] = $vce->db->sanitize($value);
				}
			}
		}

		// load base components class
		require_once(BASEPATH .'vce-application/class.component.php');
		
		// create array of installed components
		$activated_components = json_decode($vce->site->activated_components, true);
		
		// check that component type exists
		if (isset($activated_components[$type])) {
			
			$meta_data = array();
			
			if (!empty($input['component_id'])) {
			
				$query = "SELECT * FROM " . TABLE_PREFIX . "components_meta AS a JOIN " . TABLE_PREFIX . "components AS b ON b.component_id=a.component_id WHERE a.component_id='" . $input['component_id'] . "'"; 
				$component_data = $vce->db->get_data_object($query, false);
						
				foreach ($component_data as $each_data) {
	
					if (!isset($meta_data['component_id'])) {
					
						// create object and add component table data
						$meta_data['component_id'] = $each_data['component_id'];
						$meta_data['parent_id'] = $each_data['parent_id'];
						$meta_data['sequence'] = $each_data['sequence'];

						// found a url so make sub_url = true
						if (!empty($each_data['url'])) {
							$meta_data['url'] = $each_data['url'];
						}

					}

					// create a var from meta_key
					$key = $each_data['meta_key'];

					// add meta_value
					$meta_data[$key] = (($key != 'recipe') ? $vce->db->clean($each_data['meta_value']) : $each_data['meta_value']);
		
					// adding minutia if it exists within database table
					if (!empty($each_data['minutia'])) {
						$key .= "_minutia";
						$meta_data[$key] = $each_data['minutia'];
					}

				}
			
			}
			
			$meta_data['type'] = $type;
			
			// create an instance of the class
			$this_component = $vce->page->instantiate_component($meta_data, $vce);
			
			// adding vce object as component property
			$this_component->vce = $vce;
			
			// call to procedure method on type class
			$this_component->form_input($input);
            exit();
		
		} elseif (isset($type)) {
			echo json_encode(array('response' => 'error','message' => 'Component not found'));
			exit();
		}

    }


    private function authorize($vce) {

        $args = $this->parse_request();
        $args->user = $vce->user->email;
        $args->session_id = $vce->user->session_vector;

        $oauth2 = $this->get_oauth2_server($vce, $args);

        if (!$oauth2->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $oauth2->getResponse()->send();
            die();
        }

    }

    private function add_user_and_page($vce, $args) {

        if (!isset($vce->user)) {
		
            // this requires hooks, so call that method now
            $vce->site->hooks = $vce->site->get_hooks($vce);
            
            if (!empty($args->url)) {

                $vce->site->requested_url = $args->url;

            }

            // create user object
            require_once(BASEPATH . 'vce-application/class.user.php');
            $vce->user = new User($vce, false);
            
            // create user object
            require_once(BASEPATH . 'vce-application/class.page.php');
            $vce->page = new Page($vce, false);
            
        }

        // Temp fix for post
        if (!empty($args->data->access_token)) {
            $args->access_token = $args->data->access_token;
        }

        if (!empty($args->access_token)) {

            // get session_id
            $query = "SELECT user_id FROM  {$vce->db->prefix}oauth_data WHERE access_token='{$args->access_token}' LIMIT 1";
            $obj = $vce->db->get_data_object($query);
            if (isset($obj[0]) && isset($obj[0]->user_id)) {

                // Manually put session_id into the COOKIE
                $session_id = $obj[0]->user_id;
                $_COOKIE['_dbs'] = $session_id;

                if (isset($vce->site->hooks['user_start_session_override'])) {
                    foreach ($vce->site->hooks['user_start_session_override'] as $hook) {
                        $user_session = call_user_func($hook, $vce);
                        if (!empty($user_session)) {
                            // set user info values
                            // foreach ($user_object as $key => $value)
                            foreach ($user_session as $key => $value) {
                                $vce->user->$key = $value;
                            }
                        }
                    }
                }

            }
        }
    }

    /**
     * These are the RESTFul api calls.
     *
     */
    public function restful_call() {

        global $vce;

        $args = $this->parse_request();

        $this->add_user_and_page($vce, $args);

        $this->authorize($vce);

        // Note that data_access_call is in charge of echoing the json response
        // This is for legacy reasons.
        Endpoint::data_access_call($args, $vce);
        die();

    }

    /**
     * grants oauth tokens.
     *
     * @return void Dies after processing.
     */
    public function grant_oauth2_token() {

        global $vce;

        require_once __DIR__ . '/oauth2-server-php/src/OAuth2/Autoloader.php';
        OAuth2\Autoloader::register();

        $request = OAuth2\Request::createFromGlobals();
        $args = $this->parse_request();
        $this->add_user_and_page($vce, $args);

        $hash = $vce->user->generate_hash($args->user, $args->password);

        // get user_id
        $query = "SELECT user_id FROM " . TABLE_PREFIX . "users WHERE hash='" . $hash . "' LIMIT 1";
        $obj = $vce->db->get_data_object($query);

        if (isset($obj[0]) && isset($obj[0]->user_id) && $obj[0]->user_id > 0) {

            $session_id = null;
            $args->user_id = $obj[0]->user_id;

            $user_object = $vce->user->load_user_object($args->user_id);

            if (isset($vce->site->hooks['user_store_session_override'])) {
                foreach ($vce->site->hooks['user_store_session_override'] as $hook) {
                    $session_id = call_user_func($hook, $user_object, $vce);
                    if ($session_id != false) {
                        $args->session_id = $session_id;
                    }
                }
            }

            $oauth2 = $this->get_oauth2_server($vce, $args);
            $oauth2->handleTokenRequest($request)->send();
        }

        die();
    }

    /**
     * Creates and returns the oauth server with all admins added as api users.
     *
     * @param VCE $vce the global class
     * @param VCE $args the request args
     * @return the server
     */
    private function get_oauth2_server($vce, $args) {

        require_once __DIR__ . '/oauth2-server-php/src/OAuth2/Autoloader.php';
        OAuth2\Autoloader::register();

        $publicKey = file_get_contents(__DIR__ . '/keys/pubkey.pem');
        $privateKey = file_get_contents(__DIR__ . '/keys/privkey.pem');

        // create storage using our own storage model.  See VCEStorage.php.  It adds all admins as API users.
        $storage = new OAuth2\Storage\VCEStorage(array(
            'keys' => array(
                'public_key' => $publicKey,
                'private_key' => $privateKey,
            ),
            // add all admins as API users
            'client_credentials' => array(
                "{$args->user}" => array('user_id' => $args->session_id, 'client_id' => $args->session_id),
            ),
            'vce' => $vce,
        ));

        $server = new OAuth2\Server($storage, array(
            'use_jwt_access_tokens' => true,
        ));
        $server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));

        return $server;
    }

    /**
     * Handle all calls to the data access layer
     *
     * @param string $requested_url
     * @param VCE $vce the global class
     * @return json representation of data. 
     */
    public static function data_access_call($args, $vce) {

        // an array of controller factories.  this is used in the dispatcher
        // to create controller for the access call.  if we add support for more models,
        // put the controller in here.
        $controllers = array(
            'aws_media' => function () {return new AwsMediaController();},
            'login' => function () {return new LoginController();},
        );

        // an array of functions to respond to the access call: get, post, put, or delete
        $functions = array(
            'get' => function ($args, $controller, $vce) {return Endpoint::get_models($args, $controller, $vce);},
            'post' => function ($args, $controller, $vce) {return Endpoint::post_model($args, $controller, $vce);},
            'put' => function ($args, $controller, $vce) {return Endpoint::put_model($args, $controller, $vce);},
            'delete' => function ($args, $controller, $vce) {return Endpoint::delete_model($args, $controller, $vce);},
        );

        // Get the controller and function based on args
        $controller_factory = $controllers[$args->type];
        $function = $functions[$args->action];

        if (!isset($controller_factory)) {
            throw new Exception("controller " . $args->type . " not supported");
        }

        if (!isset($function)) {
            throw new Exception("function " . $args->action . " not supported");
        }

        // call the function with the function, args, and controller
        $ret = call_user_func_array($function, array($args, call_user_func($controller_factory), $vce));
		return $ret;
    }

	/**
     * Echo a list of models as json.  If just one, don't use a list.
     *
     * @param [type] $args contains the ids of the model.  Not set if requesting all the models
     * @param [type] $controller the controller to use
     * @return void
     */
    private static function get_models($args, $controller, $vce) {

		$result = '';

        if (isset($args->id)) {
            $model = $controller->read($args->id, $vce);
            if (!isset($model)) {
                Endpoint::error("model " . $args->id . " does not exist");
            }
            $result = json_encode($model, JSON_PRETTY_PRINT);
        } else {
            $ids = $controller->all_ids($args, $vce);
            $result = '[';
            foreach ($ids as $each_id) {
                $result .= json_encode($controller->read($each_id, $vce), JSON_PRETTY_PRINT);
                if ($each_id != end($ids)) {
                    $result .= ',';
                }
            }
            $result .= ']';
		}
        
        echo $result . "\n";

		return $result;
    }

    /**
     * post action creates a new model
     *
     * @param [stdClass] $args the data
     * @param [Controller] $controller
     * @return boolean
     */
    private static function post_model($args, $controller, $vce) {
        return $controller->create($args->data, $vce);
    }

    /**
     * put action updates a model
     *
     * @param [stdClass] $args the data
     * @param [Controller] $controller
     * @return boolean
     */
    private static function put_model($args, $controller, $vce) {
        return $controller->update($args->data, $vce);
    }

    /**
     * delete action deletes a model
     *
     * @param [stdClass] $args the data
     * @param [Controller] $controller
     * @return boolean
     */
    private static function delete_model($args, $controller, $vce) {
        return $controller->delete($args->data, $vce);
    }

    /**
     * Parse a RESTFul request
     *
     * @param [string] $requested_url
     * @return stdClass the arguments class
     */
    private function parse_request() {
        $args = new stdClass();

        $args->action = strtolower($_SERVER['REQUEST_METHOD']);
        if ($args->action == 'post' || $args->action == 'put') {
            $args->data = json_decode(file_get_contents('php://input'));
            $a = (array) $args->data;
            foreach ($a as $key=>$value) {
			    if (is_object($value)) {
                    $value = json_encode($value);
                }
                $_POST[$key] = $value;
            }
        }

        foreach ($_GET as $key => $value) {
            $args->{$key} = $value;
        }
 
        $headers = $this->getHeadersFromServer($_SERVER);

        if (empty($this->user) && !empty($headers['PHP_AUTH_USER'])) {
            $args->user = $headers['PHP_AUTH_USER'];
        }

        if (empty($this->password) && !empty($headers['PHP_AUTH_PW'])) {
            $args->password = $headers['PHP_AUTH_PW'];
        }

        if (empty($this->user) && !empty($args->data->client_id)) {
            $args->user = $args->data->client_id;
        }

        if (empty($this->password) && !empty($args->data->client_secret)) {
            $args->password = $args->data->client_secret;
        }

        $args->type = $this->controller;

        // Strip off query string
        
        return $args;
    }

    private function getHeadersFromServer($server)
    {
        $headers = array();
        foreach ($server as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
            // CONTENT_* are not prefixed with HTTP_
            elseif (in_array($key, array('CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'))) {
                $headers[$key] = $value;
            }
        }

        if (isset($server['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $server['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW'] = isset($server['PHP_AUTH_PW']) ? $server['PHP_AUTH_PW'] : '';
        } else {
            /*
             * php-cgi under Apache does not pass HTTP Basic user/pass to PHP by default
             * For this workaround to work, add this line to your .htaccess file:
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             *
             * A sample .htaccess file:
             * RewriteEngine On
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             * RewriteCond %{REQUEST_FILENAME} !-f
             * RewriteRule ^(.*)$ app.php [QSA,L]
             */

            $authorizationHeader = null;
            if (isset($server['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $server['HTTP_AUTHORIZATION'];
            } elseif (isset($server['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $server['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (function_exists('apache_request_headers')) {
                $requestHeaders = (array) apache_request_headers();

                // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

                if (isset($requestHeaders['Authorization'])) {
                    $authorizationHeader = trim($requestHeaders['Authorization']);
                }
            }

            if (null !== $authorizationHeader) {
                $headers['AUTHORIZATION'] = $authorizationHeader;
                // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
                if (0 === stripos($authorizationHeader, 'basic')) {
                    $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)));
                    if (count($exploded) == 2) {
                        list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                    }
                }
            }
        }

        // PHP_AUTH_USER/PHP_AUTH_PW
        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['AUTHORIZATION'] = 'Basic '.base64_encode($headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW']);
        }

        return $headers;
    }

    /**
     * Report error then die
     *
     * @param [string] $message
     * @return void
     */
    private static function error($message) {
        header("HTTP/1.1 403 Access Forbidden");
        header("Content-Type: text/plain");
        echo $message;
        die();
    }

}