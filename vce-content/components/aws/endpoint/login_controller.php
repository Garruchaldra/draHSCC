<?php

/**
 * Login Controller
 *
 * @category   AWS
 * @package    AWS
 * @author     Andy Sodt <asodt@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */

require_once __DIR__ . '/../aws_support.php';


/**
 * Controller for user login
 *
 */
class LoginController {

    use AWSSupport;

    /**
     * creates a logged in user object.  Also returns merged aws credentials, etc.
     * ex: curl -d '{"email":"clvce@uw.edu","password":"password"}' http://localhost:8888/endpoint/login/
     * @param [type] $data
     * @param [type] $vce
     * @return void
     */
    public function create($data, $vce) {

        if (isset($data->email) && isset($data->password)) {

            $data->email = strtolower($data->email);

            $vce->user->login((array)$data);
            $aws_config = LoginController::get_config($vce);
            $logged_in_user_with_aws_config = (array)$vce->user;
            $logged_in_user_with_aws_config = array_merge($logged_in_user_with_aws_config, $aws_config);
   
            echo json_encode($logged_in_user_with_aws_config, JSON_PRETTY_PRINT) . "\n";

        } else {

            die();

        }
        
    }

	// Look up user by email and password
    public function read($data, $vce) {

        // TODO
        die();
    }

    public function update($data, $vce) {

        // TODO
        die();
    }

    public function delete($data, $vce) {

        // TODO
        die();
    }

    public function exists($data, $vce) {

        // TODO
		die();
    }

    public function all_ids($data, $vce) {

        // TODO

        return [];
    }

}
