<?php
include('vce-config.php');

// set up mysqli
$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);




// get site_url
$query = "SELECT a.meta_value AS site_url FROM vce_site_meta AS a WHERE a.meta_key = 'site_url'";

$site_url = NULL;
if ($result = mysqli_query($db,$query)){
    while ($obj = mysqli_fetch_object($result)){
        $site_url = $obj->site_url;
    }
    mysqli_free_result($result);
 }

 // get session type
$query = "SELECT a.meta_value AS activated_components FROM vce_site_meta AS a WHERE a.meta_key = 'activated_components'";

$activated_components = NULL;
if ($result = mysqli_query($db,$query)){
    while ($obj = mysqli_fetch_object($result)){
        $activated_components = $obj->activated_components;
    }
    mysqli_free_result($result);
 }

$activated_components = json_decode($activated_components, TRUE);
foreach ($activated_components as $k=>$v) {
    if ($k == 'DatabaseSessions' || $k == 'PHPSessions' ) {
        $session_type = $k;
    }
}
// echo '<pre>';
// print_r($session_type, false);
// echo '</pre>';

// exit;

if (isset($session_type) && $session_type == 'DatabaseSessions') {


    
} elseif (isset($session_type) && $session_type == 'PHPSessions'){
    // start session
    if (session_status() == PHP_SESSION_NONE) {
        start_php_session($site_url);
            //record when the session started
            if (!isset($_SESSION['started'])) {
                    $_SESSION['started'] = time();
            }
    }
} else {
    echo "no session component available";
    exit;
}



    // start session
    if (session_status() == PHP_SESSION_NONE) {
        start_php_session($site_url);
            //record when the session started
            if (!isset($_SESSION['started'])) {
                    $_SESSION['started'] = time();
            }
    }
// report errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
    


if(!isset($_SESSION['happening']) || $_SESSION['happening'] != 'goforit') {	

    $persontocheck = (isset($_POST["persontocheck"])) ? $_POST["persontocheck"] : NULL;
    $passwordtocheck = (isset($_POST["passwordtocheck"])) ? $_POST["passwordtocheck"] : NULL;
    $qualifier = (isset($_POST["qualifier"])) ? $_POST["qualifier"] : NULL;

    $input = array(
        'email' => $persontocheck,
        'password' => $passwordtocheck,
        'qualifier' => $qualifier,
        'site_url' => $site_url
    );
    
    $check_login_status = form_input($input, $db);
    if ($check_login_status != 'go_ahead') {
        exit;
    }
}


  /**
     * Starts session
     * @return sets ini values
     */
function start_php_session($site_url) {

        // set hash algorithm
        ini_set('session.hash_function', 'sha512');

        // send hash
        ini_set('session.hash_bits_per_character', 5);

        // set additional entropy
        ini_set('session.entropy_file', '/dev/urandom');

        // set additional entropy
        ini_set('session.entropy_length', 256);

        // prevents session module to use uninitialized session ID
        ini_set('session.use_strict_mode', true);

        // SESSION FIXATION PREVENTION

        // do not include the identifier in the URL, and not to read the URL for identifiers.
        ini_set('session.use_trans_sid', 0);

        // tells browsers not to store cookie to permanent storage
        ini_set('session.cookie_lifetime', 0);

        // force the session to only use cookies, not URL variables.
        ini_set('session.use_only_cookies', true);

        // make sure the session cookie is not accessible via javascript.
        ini_set('session.cookie_httponly', true);

        // check for https within site_url
        if (parse_url($site_url, PHP_URL_SCHEME) == "https") {
            // set to true if using https
            ini_set('session.cookie_secure', true);
            // I have no way of testing this for samesite
        	// ini_set('session.cookie_samesite', 'None');
        } else {
            ini_set('session.cookie_secure', false);
            // Strict
        	// ini_set('session.cookie_samesite', 'Strict');
        }

        // get url path
        $url_path = parse_url($site_url, PHP_URL_PATH);
        // if this has a value, set cookie_path
        if (!empty($url_path)) {
            ini_set('session.cookie_path', $url_path);
        }

        // chage session name
        session_name('_s');

        // set the cache expire to 30 minutes / HTTP cache expiration time
        session_cache_expire(30);

        // start the session
        session_start();


        return;

    }

    function form_input($input, $db) {

        if (isset($input['qualifier'])) {
            if ($input['qualifier'] != 'greatscott'){
                exit;
            }
        }
        if (isset($input['email'])) {
            $input['email'] = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
            // if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            //     // echo json_encode(array('response' => 'error','message' => 'Not a valid email address','action' => 'clear'));
            //     echo 'Not a valid email address';
            //     exit;
            // }
        

            $hash = generate_hash($input['email'], $input['password']);

            // get user_id,role_id, and hash by crypt value
            $query = "SELECT user_id, role_id FROM " . TABLE_PREFIX . "users WHERE hash='" . $hash . "' LIMIT 1";
            // $user_id = $db->fetch_object($query);
            $result = $db->query($query);
            $result_obj = $result->fetch_object();
// echo var_dump($result_obj);
            $user_id = $result_obj->user_id;
            $user_role_id = $result_obj->role_id;
// echo var_dump($user_id);
// echo var_dump($user_role_id);

        }
        if (!empty($user_id) && !empty($user_role_id) && $user_role_id == 1) {

            // user and password check out, log in
            if (!isset($_SESSION['happening'])) {
                $_SESSION['happening'] = 'goforit';
            }
            return 'go_ahead';

        } else {
            // user and login don't check out
    $version = "version01";

	$login = <<<EOF
<!DOCTYPE html>
<html>
<head>
    <title>adjust</title>
    <style type="text/css">
    .container {
        width: 500px;
        clear: both;
    }
    .container input {
        width: 100%;
        clear: both;
    }

    </style>
</head>

<body>

<div class="container">
<form action="" method="post" enctype="multipart/form-data">
 <label>Name</label>
<input type="text" name="persontocheck"><br>
 <label>Password</label>
<input type="password" name="passwordtocheck"><br>
    <label>code</label>
<input type="password" name="qualifier"><br>
<input type="submit" value="Log In" name="submit $version">

</form>

</body>
</html>
EOF;
            echo $login;
            exit;
        

    }


}


    function generate_hash($email, $password) {

        // SITE_KEY
        // this constant is created at install and stored in vce-config.php
        // bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));

        // get salt
        $user_salt = substr(hash('md5', str_replace('@', hex2bin(SITE_KEY), $email)), 0, 22);

        // combine credentials
        $credentials = $email . $password;

        // new hash value
        return crypt($credentials, '$2y$10$' . $user_salt . '$');

    }