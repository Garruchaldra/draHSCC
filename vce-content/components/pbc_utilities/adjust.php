<?php   

/* 
* adjust.php is a utility which edits server specific configuration files
*
*
* 	The configurations include
* DB address, name, user, and password
* CAS login config
* URL in the DB
*/


// include '../../../vce-config.php';
// include 'utility_login.php';


// $content = '';
// $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// if ($mysqli->connect_errno) {
//     echo "Sorry, this website is experiencing problems.";
//     echo "Error: Failed to make a MySQL connection, here is why: \n";
//     echo "Errno: " . $mysqli->connect_errno . "\n";
//     echo "Error: " . $mysqli->connect_error . "\n";
//     exit;
// }



 
 
// // Report  errors [set to 0 for live site]
// ini_set('error_reporting', E_ALL);

// // Define BASEPATH as this file's path
// define('BASEPATH', '../../../');


// $config = file_get_contents(BASEPATH . 'vce-config.php');
// echo '<pre>';
// echo '<textarea name="$label" rows="5" cols="40">'.$config.'</textarea><br>';
// echo '</pre>';
// exit;



// // Define DOCROOT as this file's directory
// define('DOCROOT', $_SERVER['DOCUMENT_ROOT'] . '/');

// // Define DOCPATH as the path from the DOCROOT to the file
// define('DOCPATH', '/'.str_replace(DOCROOT, '', BASEPATH));

// // This is the variable which will contain all the HTML to display
// $GLOBALS['content'] = '';

// 	// configuration file 
// 	include_once(BASEPATH . 'vce-config.php');
// 	// require vce
// require_once(BASEPATH . 'vce-application/class.vce.php');
// $vce = new VCE();

// // require database class
// require_once(BASEPATH . 'vce-application/class.db.php');
// $db = new DB($vce);

// // create site object
// require_once(BASEPATH . 'vce-application/class.site.php');
// $site = new Site($vce);

// 	include_once(BASEPATH . 'vce-application/class.site.php');
// // 	include_once(BASEPATH . 'vce-application/class.page.php');
// // 	include_once(BASEPATH . 'vce-application/class.user.php');
// 	// $site = new Site();
// // 	$page = new Page();
// // 	$user = new User();
// // $site->dump($_POST);

// if (session_status() == PHP_SESSION_NONE) {
// //     start_session();
//     session_start();
//     //record when the session started
//     if (!isset($_SESSION['started'])) {
//    		 $_SESSION['started'] = time();
// 	}
// }

// //do this if logged in
// if(isset($_POST["persontocheck"]) == "Max" && isset($_POST["passwordtocheck"]) && $_POST["persontocheck"] == "Max" && $_POST["passwordtocheck"] == "Moritz1865") {
// 	$_SESSION['happening'] = 'goforit';
// }




// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// //an additional little password check
// $version = "version01";
// if($_SESSION['happening'] != 'goforit') {	
// 	$login = <<<EOF
// <!DOCTYPE html>
// <html>
// <head>
//     <title>adjust</title>
//     <style type="text/css">
//     .container {
//         width: 500px;
//         clear: both;
//     }
//     .container input {
//         width: 100%;
//         clear: both;
//     }

//     </style>
// </head>

// <body>

// <div class="container">
// <form action="" method="post" enctype="multipart/form-data">
//  <label>Name</label>
// <input type="text" name="persontocheck"><br>
//  <label>Password</label>
// <input type="password" name="passwordtocheck"><br>
// <input type="submit" value="Log In" name="submit $version">

// </form>

// </body>
// </html>
// EOF;
// 	echo $login;
// 	exit;
	
// }



// if($_SESSION['happening'] == 'goforit') {

// print_r($_SESSION);
// ?>
// <!DOCTYPE html>
// <html>
// <head>
//     <title> echo $version ?></title>
//     <link rel="stylesheet" type="text/css" href="vce-application/css/vce.css">
// </head>

// <body>





// // $config = file_get_contents('vce-config.php', true);
// // echo "<div><pre>";
// // echo nl2br(htmlentities($config));
// // echo "</pre></div>";
// // exit;

// //Enter database information

// 	$GLOBALS['content'] = check_database();	
// 	 $this_file = '';
// 	 $dbhost = !empty($_POST['dbhost']) ? $_POST['dbhost'] : check_config_value('DB_HOST');
// 	 $dbname = !empty($_POST['dbname']) ? $_POST['dbname'] : check_config_value('DB_NAME');
// 	 $dbprefix = !empty($_POST['dbprefix']) ? $_POST['dbprefix'] : check_config_value('TABLE_PREFIX');
// 	 $dbuser= !empty($_POST['dbuser']) ? $_POST['dbuser'] : check_config_value('DB_USER');
// 	 $dbpassword= !empty($_POST['dbpassword']) ? $_POST['dbpassword'] : check_config_value('DB_PASSWORD');
// 	 if (check_config_value('DB_PORT') != ''){
// 	 	$dbport= !empty($_POST['dbport']) ? $_POST['dbport'] : check_config_value('DB_PORT');
// 	 }else{
// 	 	$dbport= !empty($_POST['dbport']) ? $_POST['dbport'] : '3306';
// 	 }

// $GLOBALS['content'] .= <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content clickbar-open">
// <form class="installer-form" action="$this_file" method="post" autocomplete="off">
// <input type="hidden" name="formname" value="db">
// <label>
// <input type="text" name="dbname" tag="required" autocomplete="off" value="$dbname">
// <div class="label-text">
// <div class="label-message">Database Name $version</div>
// <div class="label-error">Enter Database Name</div>
// </div>
// </label>
// <label>
// <input type="text" name="dbuser" tag="required" autocomplete="off" value="$dbuser">
// <div class="label-text">
// <div class="label-message">Database User</div>
// <div class="label-error">Enter Database User</div>
// </div>
// </label>
// <label>
// <input type="text" name="dbpassword" tag="required" autocomplete="off" value="$dbpassword">
// <div class="label-text">
// <div class="label-message">Database User Password</div>
// <div class="label-error">Enter Database User Password</div>
// </div>
// </label>

// <label>
// <input type="text" name="dbhost" tag="required" autocomplete="off" value="$dbhost">
// <div class="label-text">
// <div class="label-message">Database Host</div>
// <div class="label-error">Enter Database Host</div>
// </div>
// </label>
// <label>
// <input type="text" name="dbport" tag="required" autocomplete="off" value="$dbport">
// <div class="label-text">
// <div class="label-message">Database Port</div>
// <div class="label-error">Enter Database Port</div>
// </div>
// </label>

// <input type="submit" value="Connect to Database">
// </form>
// </div>
// <div class="clickbar-title"><span>Configure Database Connection</span></div>
// </div>
// EOF;


// 	 $this_file = '';
// 	 $dbprefix = !empty($_POST['dbprefix']) ? $_POST['dbprefix'] : check_config_value('TABLE_PREFIX');

// 	 $query = 'SELECT * FROM '.$dbprefix.'site_meta WHERE meta_key = "site_url" ';
// $result = $db->query($query) or print('Error performing query \'<strong>query\': ' . mysqli_error($db) . '<br /><br />');
// foreach ($result as $r) {
// 	 $site_url = $r['meta_value'];
// }
	 
// 	 $current_url = !empty($_POST['site_url']) ? $_POST['site_url'] : $site_url;
// 	 $php_self = $_SERVER['PHP_SELF'];
// 	 $filepath = str_replace( '/'.basename(__FILE__), '', $_SERVER['PHP_SELF']);
// 	 $url = 'https://'.$_SERVER['HTTP_HOST'].$filepath;
// // 	  $url = basename(__FILE__);

// $GLOBALS['content'] .= <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content clickbar-open">
// <form class="installer-form" action="$this_file" method="post" autocomplete="off">
// <input type="hidden" name="formname" value="site_url">
// <label>
// <input type="text" name="site_url" tag="required" autocomplete="off" value="$url">
// <div class="label-text">
// <div class="label-message">URL</div>
// <div class="label-error">Enter URL</div>
// </div>
// </label>


// <input type="submit" value="Set Site URL"> (Current URL: $current_url)
// </form>
// </div>
// <div class="clickbar-title"><span>Set Site URL</span></div>
// </div>
// EOF;

// $GLOBALS['content'] .= <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content clickbar-open">
// <form class="installer-form" action="$this_file" method="post" autocomplete="off">
// <input type="hidden" name="formname" value="purge_file">
// <label>
// <input type="text" name="file_to_purge" tag="required" autocomplete="off" value="some_file.txt">

// <div class="label-text">
// <div class="label-message">File Name</div>
// <div class="label-error">Enter File Name</div>
// </div>
// </label>


// <input type="submit" value="Purge File"> 
// </form>
// </div>
// <div class="clickbar-title"><span>Purge File</span></div>
// </div>
// EOF;

// $GLOBALS['content'] .= <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content clickbar-open">
// <form class="installer-form" action="$this_file" method="post" autocomplete="off">
// <input type="hidden" name="formname" value="test_email">
// <label>
// <input type="text" name="test_email" tag="required" autocomplete="off" value="daytonra@uw.edu">

// <div class="label-text">
// <div class="label-message">Email</div>
// <div class="label-error">Enter Email</div>
// </div>
// </label>
// <label>
// <input type="text" name="message" tag="required" autocomplete="off" value="a test">

// <div class="label-text">
// <div class="label-message">Message Here</div>
// <div class="label-error">Enter Message</div>
// </div>
// </label>


// <input type="submit" value="Test Mailer"> 
// </form>
// </div>
// <div class="clickbar-title"><span>Test Mail</span></div>
// </div>
// EOF;






// //turn email on or off
// 	$step_title = 'Email Toggle';
//  	$step_description = 'Edit Email Toggle<br>';
// 	//Main content
// 	title_description($step_title, $step_description);

// 	//if all fields are present, personalize site
// 	if (isset($_POST['email_toggle']) && isset($_POST['formname']) && $_POST['formname'] == 'email_toggle') {
// 		$_SESSION['carryon'] = define_config_value('SITE_MAIL', $_POST['email_toggle']);
// 		$GLOBALS['content'] .= '<span style="color:green;">Currently, your email toggle is set to &quot;'.check_config_value('SITE_MAIL').'&quot; .</span">';
// 	}
	
// 	$this_file = '';
// 	$email_toggle = !empty($_POST['email_toggle']) ? $_POST['email_toggle'] : check_config_value('SITE_MAIL');

// $GLOBALS['content'] .= <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content clickbar-open">
// <form class="installer-form" tag="required" action="$this_file" method="post" autocomplete="off">
// <input type="hidden" name="formname" value="email_toggle">
// <label>
// <input type="text" name="email_toggle" tag="required" autocomplete="off" value="$email_toggle">
// <div class="label-text">
// <div class="label-message">Email Toggle</div>
// <div class="label-error">Enter Email Toggle</div>
// </div>
// </label>
// <input type="submit" value="Submit">
// </form>
// </div>
// <div class="clickbar-title"><span>Email Toggle</span></div>
// </div>
// EOF;





// //turn site_log functionality on or off
// $step_title = 'Site Log Toggle';
// $step_description = 'Edit Site Log Toggle<br>';
// //Main content
// title_description($step_title, $step_description);

// //if all fields are present, personalize site
// if (isset($_POST['site_log_toggle']) && isset($_POST['formname']) && $_POST['formname'] == 'site_log_toggle') {
//    $_SESSION['carryon'] = define_config_value('SITE_LOG', $_POST['site_log_toggle']);
//    $GLOBALS['content'] .= '<span style="color:green;">Currently, your site_log toggle is set to &quot;'.check_config_value('SITE_LOG').'&quot; .</span">';
// }

// $this_file = '';
// $site_log_toggle = !empty($_POST['site_log_toggle']) ? $_POST['site_log_toggle'] : check_config_value('SITE_LOG');

// $GLOBALS['content'] .= <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content clickbar-open">
// <form class="installer-form" tag="required" action="$this_file" method="post" autocomplete="off">
// <input type="hidden" name="formname" value="site_log_toggle">
// <label>
// <input type="text" name="site_log_toggle" tag="required" autocomplete="off" value="$site_log_toggle">
// <div class="label-text">
// <div class="label-message">Site Log Toggle</div>
// <div class="label-error">Enter Site Log Toggle</div>
// </div>
// </label>
// <input type="submit" value="Submit">
// </form>
// </div>
// <div class="clickbar-title"><span>Site Log Toggle</span></div>
// </div>
// EOF;


// //turn site_dump functionality on or off
// $step_title = 'Site Dump Toggle';
// $step_description = 'Edit Site Dump Toggle<br>';
// //Main content
// title_description($step_title, $step_description);

// //if all fields are present, personalize site
// if (isset($_POST['site_dump_toggle']) && isset($_POST['formname']) && $_POST['formname'] == 'site_dump_toggle') {
//    $_SESSION['carryon'] = define_config_value('SITE_DUMP', $_POST['site_dump_toggle']);
//    $GLOBALS['content'] .= '<span style="color:green;">Currently, your site_dump_toggle is set to &quot;'.check_config_value('SITE_DUMP').'&quot; .</span">';
// }

// $this_file = '';
// $site_dump_toggle = !empty($_POST['site_dump_toggle']) ? $_POST['site_dump_toggle'] : check_config_value('SITE_DUMP');

// $GLOBALS['content'] .= <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content clickbar-open">
// <form class="installer-form" tag="required" action="$this_file" method="post" autocomplete="off">
// <input type="hidden" name="formname" value="site_dump_toggle">
// <label>
// <input type="text" name="site_dump_toggle" tag="required" autocomplete="off" value="$site_dump_toggle">
// <div class="label-text">
// <div class="label-message">Site Dump Toggle</div>
// <div class="label-error">Enter Site Dump Toggle</div>
// </div>
// </label>
// <input type="submit" value="Submit">
// </form>
// </div>
// <div class="clickbar-title"><span>Site Dump Toggle</span></div>
// </div>
// EOF;







// //Personalize the site


// 	$step_title = 'Personalize Your Installation';
//  	$step_description = 'Enter the site\'s name and other specific information.<br>';
// 	//Main content
// 	title_description($step_title, $step_description);


// 	//if all fields are present, personalize site
// 	if (isset($_POST['site_name']) && isset($_POST['site_description']) && isset($_POST['formname']) && $_POST['formname'] == 'description') {
// 		$_SESSION['carryon'] = personalize_site($_POST['site_name'], $_POST['site_description'], $db);
// 		$GLOBALS['content'] .= '<span style="color:green;">Currently, your site name is &quot;'.$_POST['site_name'].'&quot; and your site description is &quot;'.$_POST['site_description'].'&quot;.</span">';
// 	}
	
	
// 	$this_file = '';
// 	$site_name = !empty($_POST['site_name']) ? $_POST['site_name'] : '';
// 	$site_description = !empty($_POST['site_description']) ? $_POST['site_description'] : '';

// $GLOBALS['content'] .= <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content clickbar-open">
// <form class="installer-form" tag="required" action="$this_file" method="post" autocomplete="off">
// <input type="hidden" name="formname" value="description">
// <label>
// <input type="text" name="site_name" tag="required" autocomplete="off" value="$site_name">
// <div class="label-text">
// <div class="label-message">Site Name</div>
// <div class="label-error">Enter Site Name</div>
// </div>
// </label>
// <label>
// <input type="text" name="site_description" tag="required" autocomplete="off" value="$site_description">
// <div class="label-text">
// <div class="label-message">Site Description</div>
// <div class="label-error">Enter Site Description</div>
// </div>
// </label>
// <input type="submit" value="Submit">
// </form>
// </div>
// <div class="clickbar-title"><span>Site Personalization</span></div>
// </div>
// EOF;


// 	$this_file = '';

// $GLOBALS['content'] .= <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content clickbar-open">
// <form class="installer-form" tag="required" action="$this_file" method="post" autocomplete="off">
// <input type="hidden" name="formname" value="whitelist">
// <label>
// <input type="text" name="whitelist" tag="required" autocomplete="off">
// <div class="label-text">
// <div class="label-message">Whitelist</div>
// <div class="label-error">Whitelist Names Comma Separated</div>
// </div>
// </label>
// <input type="submit" value="Whitelist">
// </form>
// </div>
// <div class="clickbar-title"><span>Add Emails to Whitelist</span></div>
// </div>
// EOF;



// $GLOBALS['content'] .= <<<EOF
// <div class="clickbar-container">
// <div class="clickbar-content clickbar-open">
// <form class="installer-form" tag="required" action="$this_file" method="post" autocomplete="off">
// <input type="hidden" name="formname" value="eradicate">
// <input type="submit" value="DELETE THIS FILE">
// </form>
// </div>
// <div class="clickbar-title"><span>Delete this config utility</span></div>
// </div>
// EOF;

	
	
// echo $GLOBALS['content'];



// //whitelist names
// if (isset($_POST['formname']) && $_POST['formname'] == 'whitelist') {
// 	$whitelist = explode(',', $_POST['whitelist']);
// 	foreach($whitelist as $email){
// 		$prefix = check_config_value('TABLE_PREFIX');
//  		$sql = 'INSERT INTO '.$prefix.'whitelist (email) VALUES ("'.$email.'")';
//  		$db->query($sql);
	
// 	}
// 	echo 'whitelisted';
// }

// //test email
// if (isset($_POST['formname']) && $_POST['formname'] == 'test_email') {
// // 	mail($_POST['test_email'],"Test Email",$_POST['message']);

	
	
// 	$mail_attributes = array (
// 	  'from' => array('coachingcompanion@eclkc.info', 'DoNotReply'),
// 	  'to' => array(
// 	  array($_POST['test_email'], 'test user')
// 	    ),
// 	'subject' => 'test01',
// 	 'message' => $_POST['message']
// 	 );
	
// 		global $site;
// 	$site->mail($mail_attributes);
// 	$site->dump($mail_attributes);
// 	echo 'email sent (hopefully) '.$_POST['test_email'].$_POST['message'];
// }


// //purge file
// if (isset($_POST['formname']) && $_POST['formname'] == 'purge_file') {
// 	//erase the file "debug.txt" which had been put there by CAS
// 	if (file_exists($_POST['file_to_purge'])){
// 		unlink($_POST['file_to_purge']);
// 	}
// }

// //update site_url
// if (isset($_POST['formname']) && $_POST['formname'] == 'site_url') {

// 		$prefix = check_config_value('TABLE_PREFIX');
//  		$sql = 'UPDATE '.$prefix.'site_meta SET meta_value = "'.$_POST["site_url"].'" WHERE meta_key = "site_url" ';
//  		$db->query($sql);

// 	echo 'Site URL updated to: '.$_POST['site_url'];
// }


// //ERASE this file and Redirect to Admin Home Page at the end of the installation
// if (isset($_POST['formname']) && $_POST['formname'] == 'eradicate') {
// 	session_unset();
// 	session_destroy();
// 	$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
// 	$url = str_replace(basename(__FILE__), '', $url);
// 	unlink(__FILE__);
// 	header('Location: '.$url);
// }


// //DELETE users and anything they created
// if (isset($_POST['formname']) && $_POST['formname'] == 'delete_users') {
// 	$_POST['delete_users_start'];
// }



// /**
//  * END
//  */

// $copy = '&copy; ' . date("Y");

// //write post-content and footer
// $content = <<<EOF
// </div>
// </div>
// <footer id="footer">
// <div class="inner" >
// <div class="copy">$copy University of Washington. All rights reserved</div>
// </div>
// </footer>
// </div></div>
// </body>
// </html>
// EOF;


// echo $content;

// }



// /**
//  * FUNCTIONS: here are all the functions called in the step-by-step
//  */

// 	/**
// 	 * session start 
// 	 */
// 	function start_session() {

// 		// SESSION HIJACKING PREVENTION

// 		// set hash algorithm
// 		ini_set('session.hash_function', 'sha512');
	
// 		// send hash
// 		ini_set('session.hash_bits_per_character', 5);
   
// 		// set additional entropy
// 		ini_set('session.entropy_file', '/dev/urandom');
	
//    		// set additional entropy	
// 		ini_set('session.entropy_length', 256);
	
// 		// prevents session module to use uninitialized session ID
// 		ini_set('session.use_strict_mode', true);
   
// 		// SESSION FIXATION PREVENTION
   
// 		// do not include the identifier in the URL, and not to read the URL for identifiers.
// 		ini_set('session.use_trans_sid', 0);
	
//  		// tells browsers not to store cookie to permanent storage
//  		ini_set('session.cookie_lifetime', 0);
 
// 		// force the session to only use cookies, not URL variables.
// 		ini_set('session.use_only_cookies', true);
   
// 		// make sure the session cookie is not accessible via javascript.
// 		ini_set('session.cookie_httponly', true);
   
// 		// set to true if using https   
// 		ini_set('session.cookie_secure', false);

// 		// chage session name
// 		session_name('_s');
		
// 		// set the cache expire to 30 minutes
// 		session_cache_expire(5);
	
// 		// start the session
// 		session_start();
// 	}
	
// function set_page() {
// 	if (isset($_POST['pagecheck'])) {
// 		return;
// 	}

// 	if (isset($_POST['direction'])) {
// 		if ($_POST['direction'] == 'back') {
// 			if (isset($_SESSION['installer_page'])) {
// 				$_SESSION['installer_page']--;
// 			}else{
// 				$_SESSION['installer_page'] = 0;
// 			}
// 		}elseif ($_POST['direction'] == 'continue') {
// 			if (isset($_SESSION['installer_page'])) {
// 				$_SESSION['installer_page']++;
// 			}else{
// 				$_SESSION['installer_page'] = 0;
// 			}
// 		}
// 	}
// }

// /**
//  * Creates the "Back" and "Continue" buttons for each step of the installation
//  * @param 
//  * @return HTML for the form buttons
//  */
// function back_continue($carryon) {
//  $this_file = '';
// $continue_message = ($carryon == 'wait' ? 'Submit Form First' : 'Continue');
// $continue_button = <<<EOF
// <div class="inner">
// <div class="clickbar-container">
// <div class="clickbar-content” style="display: block;">
// <!-- <form class="inline-form asynchronous-form" method="post" action="$this_file">
// <input type="hidden" name="direction" value="back">
// <input type="submit" value="Back (temporary)">
// </form> -->
// <form onsubmit="return checkContinueForm(this);" class="inline-form asynchronous-form" method="post" action="$this_file">
// <input type="hidden" name="direction" value="$carryon">
// <input type="submit" value="$continue_message">
// </form>
// </div>
// </div>
// EOF;
// $GLOBALS['content'] = str_replace('<div class="inner">', $continue_button, $GLOBALS['content']);
// }
 

// /**
//  * Creates the title and description for each step of the installation
//  * @param string $title
//  * @param string $description
//  * @return HTML for the title and description
//  */ 
// function title_description($step_title, $step_description){
// 	$GLOBALS['content'] .= '<h2>'.$step_title.'</h2>';
// 	$GLOBALS['content'] .= '<p>'.$step_description.'</p>';
// }


// /**
//  * Prepares vce-config.php for the installation
//  * @param string $title
//  * @param string $description
//  * @return HTML for the title and description
//  */ 
// function edit_vce_config(){
// 	if(file_exists(BASEPATH.'vce-config')){
// 		$GLOBALS['content'] .= '<p><strong><span style="color:red;">Caution!</span></strong><span style="color:red;">You have run this installer script previously.
// 		<br><strong>Running it again will overwrite your site key and erase your user data.</strong>
// 		<br>You are seeing this message because there is already a configuration file. If you wish to start a new installation, either start anew with newly unzipped contents, or erase the existing config file, upload this installer again from the zip file, and run it.
// 		<br>This installer has now been deleted to prevent this from happening again in the future.</p></span>';
// 		unlink(__FILE__);
// 		return;
// 	}
// // 	if(file_exists(BASEPATH.'vce-config.php')){
// // 		$list = glob('vce-config_BAK*.php');
// // 		foreach($list as $file){
// // 			unlink($file);
// // 		}
// // 		copy('vce-config.php', 'vce-config_BAK'.date('Y_m_d_h_i_s').'.php');
// // 		unlink(BASEPATH.'vce-config.php');
// // 	}
// 	if(!file_exists(BASEPATH.'vce-config.php')){
// 		touch(BASEPATH.'vce-config.php');
// 		$newfile = fopen(BASEPATH."vce-config.php", "w") or die("Unable to open file!");
// 		if(file_exists(BASEPATH.'vce-config-sample.php')){
// 			$content = file_get_contents(BASEPATH.'vce-config-sample.php');
// 		}else{
		
// $content = <<<EOF


// /* Site key - DO NOT CHANGE THIS */
// define('SITE_KEY', 'installer_generated_site_key_here');

// /* The name of the database */
// define('DB_NAME', 'database_name_here');

// /* MySQL database username */
// define('DB_USER', 'username_here');

// /* MySQL database password */
// define('DB_PASSWORD', 'password_here');

// /* MySQL hostname */
// define('DB_HOST', 'localhost');

// /* MySQL table_prefix */
// define('TABLE_PREFIX', 'vce_');

// /* Enable query string input */
// define('QUERY_STRING_INPUT', true);

// /* Enable persistant login */
// define('PERSISTANT_LOGIN', true);

// /* set the path to uploaded files */
// define('PATH_TO_UPLOADS', 'vce-content/uploads');

// /* display MySQL and PHP errors */
// define('VCE_DEBUG', true);

// EOF;
// 		}
// 		fwrite($newfile, $content);
// 		fclose($newfile);
// 	}
	
// 	$reading = fopen(BASEPATH.'vce-config.php', 'r');
// 	$writing = fopen(BASEPATH.'configTEMP.php', 'w');

// 	$replaced = false;

// 	while (!feof($reading)) {
//   		$line = fgets($reading);
//   		if (stristr($line, 'SITE_KEY')) {
//   			$line = "define('SITE_KEY', '');".PHP_EOL;
//    			$replaced = true;
//  		}
//  		fputs($writing, $line);
// 	}
// 	fclose($reading); fclose($writing);
// 	// might as well not overwrite the file if we didn't replace anything
// 	if ($replaced) 
// 	{
//   		rename('configTEMP.php', 'vce-config.php');
// 	} else {
//  		 unlink('configTEMP.php');
// 	}


// }
 
 
//  function site_admin($username, $pwd, $firstname, $lastname, $db) {
//  	//clean form input
//  		$username = $db->mysqli_escape($username);
//  		$pwd = $db->mysqli_escape($pwd);
//  		$firstname = $db->mysqli_escape($firstname);
//  		$lastname = $db->mysqli_escape($lastname);
 		
//  		$prefix = check_config_value('TABLE_PREFIX');
//  		$sql = 'DELETE a, b FROM '.$prefix.'users as a, '.$prefix.'users_meta as b WHERE a.role_id = 1';
//  		$db->query($sql);
 		
// 		$return = array();
		
// 		$lookup = lookup($username);
		
// 		// check if exists
// 		$query = "SELECT id FROM " . TABLE_PREFIX . "users_meta WHERE meta_key='lookup' and meta_value='" . $lookup . "'";
// 		$lookup_check = $db->get_data_object($query);
		
// 		if (!empty($lookup_check)) {
// 			$return['failure'] = '<span style="color:red;">Email is already in use; this user already exists.</span>';
// 			return $return;
// 		}
		
// 		// call to user class to create_hash function
// 		$hash = create_hash(strtolower($username),$pwd);
		
// 		// get a new vector for this user
// 		$vector = create_vector();
		
// 		//for use in mailing the SITE_KEY to the new site admin
// 		$_SESSION['site_admin'] = $username;

// 		$user_data = array(
// 		'vector' => $vector, 
// 		'hash' => $hash,
// 		'role_id' => 1
// 		);

// 		$db->insert( 'users', $user_data );
// 		$user_id = $db->lastid();
	
				
// 		// now add meta data

// 		$records = array();
				
// 		$lookup = lookup($username);
		
// 		$records[] = array(
// 		'user_id' => $user_id,
// 		'meta_key' => 'lookup',
// 		'meta_value' => $lookup,
// 		'minutia' => 'false'
// 		);
		
		
// 		$input = array('email'=>$username, 'first_name'=>$firstname, 'last_name'=>$lastname);
		
// 		foreach ($input as $key => $value) {

// 			// encode user data			
// 			$encrypted = encryption($value, $vector);
			
// 			$records[] = array(
// 			'user_id' => $user_id,
// 			'meta_key' => $key,
// 			'meta_value' => $encrypted,
// 			'minutia' => null
// 			);
			
// 		}		
		
// 		$db->insert('users_meta', $records);
		
// 		$return['site_admin_email'] = $username;
// 		$return['site_admin_name'] = $firstname.' '.$lastname;
// 		$return['success'] = '<span style="color:green;">Success: your Site Admin user has been created.</span">';
		
		
// 		return $return;
	

// }
// 	/**
// 	 * take an email address and return the crypt
// 	 */
// 	function lookup($email) {

// 		// get salt
// 		$user_salt = substr(hash('md5', str_replace('@', hex2bin($GLOBALS['site_key']), $email)), 0, 22);

// 		// create lookup
// 		return crypt($email,'$2y$10$' . $user_salt . '$');
		
// 	}
	
// 	function create_hash($email, $password) {
	
// 		// get salt
// 		$user_salt = substr(hash('md5', str_replace('@', hex2bin($GLOBALS['site_key']), $email)), 0, 22);

// 		// combine credentials
// 		$credentials = $email . $password;

// 		// new hash value
// 		return crypt($credentials,'$2y$10$' . $user_salt . '$');
	
// 	}
	
// 	function create_vector() {
// 		if (OPENSSL_VERSION_NUMBER) {
// 			return base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc')));
// 		} else {
// 			return base64_encode(mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM));
// 		}
// 	}
	
// 	function encryption($encode_text,$vector) {
// 		if (OPENSSL_VERSION_NUMBER) {
// 			return base64_encode(openssl_encrypt($encode_text,'aes-256-cbc',hex2bin($GLOBALS['site_key']),OPENSSL_RAW_DATA,base64_decode($vector)));
// 		} else {
// 			return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, hex2bin($GLOBALS['site_key']), $encode_text, MCRYPT_MODE_CBC, base64_decode($vector)));
// 		}
// 	}


// function create_site_key() {
// 	$site_key = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
// 	define_config_value('SITE_KEY', $site_key);
// }

// /**
//  * Checks to see if a site key has already been defined.
//  * @param $superadmin_email to send the new key to the superadmin
//  * @return creates a site key if necessary
//  */
// function site_key($siteadmin_email) {
// 		$site_key = check_config_value('SITE_KEY');
// 		$GLOBALS['content'] .= "Hello ".$siteadmin_email.", your new site key has been created<br>
// 		It is:<br>
// 		<span style='color:green;'>".$site_key."</span><br>
// 		Please keep this in your records.";
// 		$msg = "Hello, \n
// 		This is the VCE SITE_KEY for your site:\n
// 		".$site_key."\n
// 		It is stored in the vce-config.php file at the root of your installation.\n
// 		Please keep it in your records to use in the event of a corruption of that configuration file.\n
// 		Thank you!";
// 		mail($siteadmin_email,'VCE SITE_KEY',$msg);
// 		return;
// }




// function check_database() {
// 	$return_toggle = FALSE;
// 	if (isset($_POST['formname']) && $_POST['formname']=='db') {
// 		if (empty($_POST['dbhost'])) {
// 			$GLOBALS['content'] .= '<br><span style="color:red;">You have not specified a database host.</span><br>';
// 			$return_toggle = TRUE;
// 		}
// 		if (empty($_POST['dbname'])) {
// 			$GLOBALS['content'] .= '<br><span style="color:red;">You have not specified a database name.</span><br>';
// 			$return_toggle = TRUE;
// 		}
// 		if (empty($_POST['dbuser'])) {
// 			$GLOBALS['content'] .= '<br><span style="color:red;">You have not specified a database user.</span><br>';
// 			$return_toggle = TRUE;
// 		}
// 		if (empty($_POST['dbpassword'])) {
// 			$GLOBALS['content'] .= '<br><span style="color:red;">You have not specified a database user password.</span><br>';
// 			$return_toggle = TRUE;
// 		}
// 		if ($return_toggle == TRUE) {
// 			return 'wait';
// 		}
// 		define_config_value('DB_HOST', $_POST['dbhost']);
// 		define_config_value('DB_NAME', $_POST['dbname']);
// 		define_config_value('DB_USER', $_POST['dbuser']);
// 		define_config_value('DB_PASSWORD', $_POST['dbpassword']);
		
// 		include_once(BASEPATH.'vce-config.php');
// 		try {
//             $dbconnection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, defined('DB_PORT') ? DB_PORT : '3306');
// //             mysqli_report(MYSQLI_REPORT_STRICT);
//     		if ($dbconnection->connect_error) {
//         		$GLOBALS['content'] .= '<br><span style="color:red;">The database connection has not been successful.<br>
//         		The error: '.$dbconnection->connect_error.'</span><br>';
//         		return 'wait';

//     		} else {            
//     			$GLOBALS['content'] .= '<br><span style="color:green;">You have connected successfully to the database "'.check_config_value('DB_NAME').'". Please click on &quot;continue&quot;.</span><br>';
// 				return;
// 			}
    
//         } catch (Exception $e) {
// 		die('Database connection failed');
//         }


// 	}
// 	return 'wait';
// }



// /**
//  * Checks to see if all required modules and services are present on the server.
//  * @param array $extensions names of extensions to check for
//  * @return prints out success or failure notices for everything checked
//  */
// function extension_check($extensions) {
//   	$fail = '';
// 	$pass = '';
	
// 	if (version_compare(phpversion(), '5.3.0', '<')) {
// 		$fail .= '<li>You need<strong> PHP 5.3.0</strong> (or greater)</li>';
// 	} else {
// 		$pass .='<li>Your version of PHP '.phpversion().' is greateer than PHP 5.3.0</li>';
// 	}
// 	if (!ini_get('safe_mode')) {
// 		$pass .='<li>Safe Mode is <strong>off</strong></li>';
// // 		preg_match('/[0-9]\.[0-9]+\.[0-9]+/', mysqli_get_server_info(), $version);
// // 		
// // 		if (version_compare($version[0], '4.1.20', '<')) {
// // 			$fail .= '<li>You need<strong> MySQL 4.1.20</strong> (or greater)</li>';
// // 		} else {
// // 			$pass .='<li>You have<strong> MySQL 4.1.20</strong> (or greater)</li>';
// // 		}
// 	} else {
// 		$fail .= '<li>Safe Mode is <strong>on</strong></li>';
// 	}
	
// 	foreach($extensions as $extension) {
// 		if (!extension_loaded($extension)) {
// 			$fail .= '<li> You are missing the <strong>'.$extension.'</strong> extension</li>';
// 		} else {
// 			$pass .= '<li>You have the <strong>'.$extension.'</strong> extension</li>';
// 		}
// 	}
	
// 	// adding message about date.timezone
// 	if (!date_default_timezone_get()) {
// 		/*
// 		'Kwajalein',
// 		'Pacific/Midway',
// 		'Pacific/Honolulu',
// 		'America/Anchorage',
// 		'America/Los_Angeles',
// 		'America/Denver',
// 		'America/Tegucigalpa',
// 		'America/New_York',
// 		'America/Caracas',
// 		'America/Halifax',
// 		'America/St_Johns',
// 		'America/Argentina/Buenos_Aires',
// 		'America/Sao_Paulo',
// 		'Atlantic/South_Georgia',
// 		'Atlantic/Azores',
// 		'Europe/Dublin',
// 		'Europe/Belgrade',
// 		'Europe/Minsk',
// 		'Asia/Kuwait',
// 		'Asia/Tehran',
// 		'Asia/Muscat',
// 		'Asia/Yekaterinburg',
// 		'Asia/Kolkata',
// 		'Asia/Katmandu',
// 		'Asia/Dhaka',
// 		'Asia/Rangoon',
// 		'Asia/Krasnoyarsk',
// 		'Asia/Brunei',
// 		'Asia/Seoul',
// 		'Australia/Darwin',
// 		'Australia/Canberra',
// 		'Asia/Magadan',
// 		'Pacific/Fiji',
// 		'Pacific/Tongatapu'
// 		*/
// 		$pass .= '<li>date.timezone has not been set in php.ini and will default to America/Los_Angeles</li>';
// 	}
	
// 	$pass .= '<li>Your .htaccess file has been successfully updated.</li>';
	
// 	if ($fail) {
// 		$GLOBALS['content'] .= '<p><strong>Your server does not meet the following requirements in order to install VCE.</strong>';
// 		$GLOBALS['content'] .= '<br>The following requirements failed, please contact your hosting provider in order to receive assistance with meeting the system requirements for VCE:';
// 		$GLOBALS['content'] .= '<ul>'.$fail.'</ul></p>';
// 		$GLOBALS['content'] .= 'The following requirements were successfully met:';
// 		$GLOBALS['content'] .= '<ul>'.$pass.'</ul>';
// 		return 'wait';
// 	} else {
// 		$GLOBALS['content'] .= '<p><strong><span style="color:green;">Congratulations!</span></strong><span style="color:green;"> Your server meets the requirements for VCE.</p></span>';
// 		$GLOBALS['content'] .= '<ul>'.$pass.'</ul>';
// 		return 'continue';

// 	}
// }

// /**
//  * Decides to use existing config.php file or create one.
//  * Checks to see if there is a config file, creates one if not, and uses the config_sample as a template if exists.
//  * @return creates vce-config.php
//  */
// function check_config_file() {
// 	if (!file_exists(BASEPATH.'vce-config.php') && file_exists(BASEPATH.'vce-config-sample.php')) {
// 		$GLOBALS['using_config_sample'] = TRUE;
// 		touch(BASEPATH.'vce-config.php');
// 	} elseif (!file_exists(BASEPATH.'vce-config.php') && !file_exists(BASEPATH.'vce-config-sample.php')) {
// 		touch(BASEPATH.'vce-config.php');
// 	} else {
// 		$GLOBALS['config_file_exists'] = TRUE;
// 	}
// }

// /**
//  * Decides to use existing .htaccess file or create one.
//  * @return creates .htaccess
//  */
// function check_htaccess_file() {
// 	if (!file_exists(BASEPATH.'.htaccess')) {
// 		touch(BASEPATH.'.htaccess');
		
// 	}

// }

// /**
//  * Edits htaccess file.
//  * Goes through the .htaccess  file line by line, and replaces target directives with correct (or same) directives
//  * or creates them.
//  * @return new .htaccess file with corrected directives
//  */
// function edit_htaccess_file() {
// 	$reading = file_get_contents(BASEPATH.'.htaccess');
// 	$writing = fopen(BASEPATH.'.htaccessTEMP', 'w');

// 	$replaced = false;

// $required_content = PHP_EOL.'RewriteEngine On'.PHP_EOL.'
// RewriteBase '.DOCPATH.''.PHP_EOL.'
// RewriteRule ^index\.php$ - [L]'.PHP_EOL.'
// RewriteCond %{REQUEST_FILENAME} !-f'.PHP_EOL.'
// RewriteCond %{REQUEST_FILENAME} !-d'.PHP_EOL.'
// RewriteRule . '.DOCPATH.'index.php [L]'.PHP_EOL.'
// RedirectMatch 301 '.DOCPATH.'vce-content/uploads/(.*) '.DOCPATH.PHP_EOL;

// 	preg_match('/.*<IfModule\s*mod_rewrite.c>(.*?)<\/IfModule>/ms', $reading, $matches);
// 	//to disable the parsing and simply wipe .htacces clean and add new content:
// 	if (1 == 2) {	
// // if (isset($matches[1])) {
// // echo '<br>';
// // echo $matches[1];
// 	$matches[1] = str_replace($required_content, '', $matches[1]);
// 	$matches[1] = str_replace('###vce-directives', '', $matches[1]);
// // 	$matches[1] = str_replace(PHP_EOL, '', $matches[1]);
	
// // 	echo '<br>';
// // echo $matches[1];
// 	$replacement = '<IfModule mod_rewrite.c>'.$matches[1].PHP_EOL.'###vce-directives'.$required_content.'###vce-directives'.PHP_EOL.'</IfModule>';
// 	$data = preg_replace('/<IfModule\s*mod_rewrite.c>(.*?)<\/IfModule>/ms', $replacement, $reading);
// 	$data2 = str_replace($data, '', $reading);
    
// 	fputs($writing, $data);
	
// 	$replaced = true;
// } else {
// 	$insertion = '<IfModule mod_rewrite.c>'.PHP_EOL.'###vce-directives'.$required_content.'###vce-directives'.PHP_EOL.'</IfModule>';
// 	fputs($writing, $insertion);
// 	$replaced = true;
// }

// 	//fclose($reading);
// 	 fclose($writing);
// 	// might as well not overwrite the file if we didn't replace anything
// 	if ($replaced) {
//    		rename('.htaccessTEMP', '.htaccess');
// 	} else {
//   		 unlink('.htaccessTEMP');
// 	}
// }

// /**
//  * Edits constants in the config.php file.
//  * Goes through the config.php file line by line, looking for the $constant_name to edit, and replaces
//  * that whole line if it finds it. Otherwise writes the same line it has just read. Does NOT create the file
//  * if it does not exist.
//  * @param string $constant_name
//  * @param string $constant_value
//  * @return new config.php file with new constant value
//  */
// function define_config_value($constant_name, $constant_value) {
// 	$reading = fopen(BASEPATH.'vce-config.php', 'r');
// 	$writing = fopen(BASEPATH.'vce-configTEMP.php', 'w');
// 	$replaced = FALSE;

// 	while (!feof($reading)) {
//   			$line = fgets($reading);
//   		if (strstr($line, $constant_name)) { 	
//   			if ($constant_name == 'SITE_MAIL' || $constant_name == 'SITE_LOG' || $constant_name == 'SITE_DUMP') {
//    				$line = "define('".$constant_name."', ".$constant_value.");".PHP_EOL;
//    				$replaced = true;
//  			} else {
//  			   	$line = "define('".$constant_name."', '".$constant_value."');".PHP_EOL;
//    			 	$replaced = true;  			 	
//  			}
 		
//  		}
//  		fputs($writing, $line);
// 	} 
// 	if ($replaced == FALSE && !empty($constant_name)) {
// 		if ($constant_name == 'SITE_MAIL' || $constant_name == 'SITE_LOG' || $constant_name == 'SITE_DUMP') {
// 			$line = "define('".$constant_name."', ".$constant_value.");".PHP_EOL;
//    				$replaced = true;
//  			} else {
//  			   	$line = "define('".$constant_name."', '".$constant_value."');".PHP_EOL;
//    			 	$replaced = true;
//  			}
// 		fputs($writing, $line); 
// 	}

// 	fclose($reading); fclose($writing);
// 	// might as well not overwrite the file if we didn't replace anything
// 	if ($replaced == true) {
// // 		unlink(BASEPATH.'vce-config.php');
//   		rename(BASEPATH.'vce-configTEMP.php', BASEPATH.'vce-config.php');
// 	} else {
//  		unlink(BASEPATH.'vce-configTEMP.php');
// 	}
// }


// /**
//  * Checks constant values in the config.php file.
//  * Uses PHP's "token_get_all" to look at all the defined constants in vce_config.php
//  * @param string $constant_name
//  * @return mixed $constant_value
//  */
// function check_config_value($constant_name) {
// 	$defines = array();
// 	$state = 0;
// 	$key = '';
// 	$value = '';

// 	$file = file_get_contents(BASEPATH.'vce-config.php');
// 	$tokens = token_get_all($file);
// 	$token = reset($tokens);
// 	while ($token) {
//     	if (is_array($token)) {
//        	 if ($token[0] == T_WHITESPACE || $token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
//            	 // do nothing
//        	 } else if ($token[0] == T_STRING && strtolower($token[1]) == 'define') {
//             $state = 1;
//        	 } else if ($state == 2 && is_constant($token[0])) {
//        	     $key = $token[1];
//        	     $state = 3;
//       	  } else if ($state == 4 && is_constant($token[0])) {
//       	      $value = $token[1];
//        	     $state = 5;
//       	  }
//    	 } else {
//      	   $symbol = trim($token);
//      	   if ($symbol == '(' && $state == 1) {
//      	       $state = 2;
//      	   } else if ($symbol == ',' && $state == 3) {
//      	       $state = 4;
//      	   } else if ($symbol == ')' && $state == 5) {
//      	       $defines[strip($key)] = strip($value);
//      	       $state = 0;
//      	   }
//    	 }
//   	  $token = next($tokens);
// 	}
// 	//checks constant existance and returns value if exists
// 	foreach ($defines as $k => $v) {
// 		if ($constant_name == $k) {
// //   	 	 	echo "'$k' => '$v'\n";
//   	 	 	return $v;
//   	 	 }
// 	}

// }
// /**
//  * Checks if token is constant.
//  * Called from check_config_value().
//  * @param mixed $token
//  * @return mixed $token
//  */
// function is_constant($token) {
//     return $token == T_CONSTANT_ENCAPSED_STRING || $token == T_STRING ||
//         $token == T_LNUMBER || $token == T_DNUMBER;
// }


// /**
//  * Strips constant value.
//  * Called from check_config_value().
//  * @param mixed $value
//  * @return mixed $value
//  */
// function strip($value) {
// 	  return preg_replace('!^([\'"])(.*)\1$!', '$2', $value);
// }



// function form_validation_js() {
// $script = <<<EOF
// <script src="vce-application/js/jquery/jquery.min.js"></script>
// <script src="vce-application/js/jquery/jquery-ui.min.js"></script>
// <script type='text/javascript'>
// $(document).ready(function() {

// // click-bar
// $('.clickbar-title').on('click touchend', function(e) {
// 	if ($(this).hasClass('disabled') !== true) {
// 		$(this).toggleClass('clickbar-closed');
// 		$(this).parent('.clickbar-container').children('.clickbar-content').slideToggle();
// 	}
// });

// $(document).on('focus', 'textarea, input[type=text],input[type=email], input[type=password], select', function() {
// 	$('.form-error').fadeOut(1000, function(){ 
//     	$(this).remove();
// 	});
// 	$(this).parent('label').removeClass('highlight-alert').addClass('highlight');
// 	$(this).parents().eq(1).children(':submit').addClass('active-button');
// });

// $(document).on('blur', 'textarea, input[type=text], input[type=email], input[type=password], select', function() {
// 	$(this).parent('label').removeClass('highlight');
// 	if ($(this).val() === "") {
// 		$(this).parents().eq(1).children(':submit').removeClass('active-button');
// 	}
// });

// $('.installer-form').on('submit', function(e) {

// 	var formsubmitted = $(this);
	
// 	var submittable = true;
	
// 	var textareatest = $(this).find('textarea');
// 		textareatest.each(function(index) {
// 			if ($(this).val() == "" && $(this).attr('tag') == 'required') {
// 				$(this).parent('label').addClass('highlight-alert');
// 				submittable = false;
// 			}
// 		});
			
// 	var typetest = $(this).find('input[type=text]');
// 		typetest.each(function(index) {
// 			if ($(this).val() == "" && $(this).attr('tag') == 'required') {
// 				$(this).parent('label').addClass('highlight-alert');
// 				submittable = false;
// 			}
// 		});
	
// 	var emailtest = $(this).find('input[type=email]');
// 		emailtest.each(function(index) {
// 			reg = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
// 			if (!reg.test($(this).val()) && $(this).attr('tag') == 'required') {
// 				$(this).parent('label').addClass('highlight-alert');
// 				submittable = false;
// 			}
// 		});
		
// 	var passwordtest = $(this).find('input[type=password]');
// 		var test = [];
// 		passwordtest.each(function(index) {
// 		test[index] = $(this).val();
// 			if ($(this).val() == "" && $(this).attr('tag') == 'required') {
// 				$(this).parent('label').find('.label-error').text('Enter A Password');
// 				$(this).parent('label').addClass('highlight-alert');
// 				submittable = false;
// 			}
//       		re = /[0-9]/;
//       		if (!re.test($(this).val())) {
//        			$(this).parent('label').find('.label-error').text('Password must contain at least one number (0-9)');
//        	 		$(this).parent('label').addClass('highlight-alert');
//         		submittable = false;
//       		}
//       		re = /[a-z]/;
//       		if (!re.test($(this).val())) {
//        			$(this).parent('label').find('.label-error').text('Password must contain at least one lowercase letter (a-z)');
//        	 		$(this).parent('label').addClass('highlight-alert');
//         		submittable = false;
//       		}
//       		re = /[A-Z]/;
//       		if (!re.test($(this).val())) {
//        			$(this).parent('label').find('.label-error').text('Password must contain at least one uppercase letter (A-Z)');
//        	 		$(this).parent('label').addClass('highlight-alert');
//         		submittable = false;
//       		}
//       		if (test[0] && test[1]) {
//       			if (test[0] !== test[1]) {
//        			$(this).parent('label').find('.label-error').text('Passwords do not match');
//        	 		$(this).parent('label').addClass('highlight-alert');
//         		submittable = false;
//       			}
//       		}
// 		});
		
// 	var selecttest = $(this).find('select');
// 		selecttest.each(function(index) {
// 			if ($(this).find('option:selected').val() == "" && $(this).attr('tag') == 'required') {
// 				$(this).parent('label').addClass('highlight-alert');
// 				submittable = false;
// 			}
// 		});
	
// 	var checkboxtest = $(this).find('input[type=checkbox]');
// 		var box = {};	
// 		checkboxtest.each(function(index) {
// 			var boxname = $(this).attr('name');			
// 			var boxcheck = $(this).prop('checked');
// 			if (typeof box[boxname] !== 'undefined') {
// 				if (box[boxname] === false) {
// 					box[boxname] = boxcheck;
// 				}
// 			} else {
// 				box[boxname] = boxcheck;	
// 			}
// 			if (box[boxname] === false) {
// 				$(this).parent('label').parent('label').addClass('highlight-alert');
// 				submittable = false;
// 			} else {
// 				$(this).parent('label').parent('label').removeClass('highlight-alert');
// 				submittable = true;
// 			}
// 		});
	
// 	if (submittable) {
// 		return true;
// 	}
	
// 	return false;

// });

// function checkContinueForm(form)
//   {
//     if (form.direction.value == "wait") {
//       alert("You must successfully submit the form below to continue!");
//       form.username.focus();
//       return false;
//      }
//      return true; 
//   }

// });
// </script>
// EOF;

// return $script;
// }

// function installer_css(){

// $style = <<<EOF
// <style>

// * {
// font-family: sans-serif;
// font-weight: 400;
// font-size: 15px;
// color: #333;
// -webkit-tap-highlight-color: rgba(0,0,0,0);
// }

// html, body {
// height: 100%;
// margin: 0;
// padding: 0;
// -webkit-text-size-adjust: 100%;
// -moz-text-size-adjust: 100%;
// -ms-text-size-adjust: 100%;
// }

// #wrapper {
// position: relative;
// display: block;
// width: 100%;
// min-height: 100%;
// margin: 0;
// padding: 0;
// background: #fff;
// }

// #content {
// position: relative;
// display: block;
// padding: 0px 0px 100px 0px;
// }

// #decorative-bar {
// position: relative;
// display: block;
// height: 15px;
// background-color: #00A14B;
// }

// #header {
// position: relative;
// display: block;	
// background-color: #005EAC;
// height: 100px;
// }

// #header .inner {
// height: 100px;
// }

// #header h1 {
// font-size: 28px;
// letter-spacing: 2px;
// color: #FFF;
// text-transform: uppercase;
// text-align: center;
// padding-top: 20px;
// margin-top: 0px;
// margin-bottom: 0px;
// }

// h1 {
// font-size: 24px;
// letter-spacing: 2px;
// }

// #info-bar {
// height: 50px;
// padding: 10px 0px 10px 0px;
// background-color: #EEE8DA;
// }

// .inner {
// width: 940px;
// margin: 0 auto;
// }

// #info-bar-left {
// display: block;
// float: left;
// max-width: 45%;
// text-align: left;
// }

// #info-bar-right {
// display: block;
// float: right;
// max-width: 45%;
// text-align: right;
// }

// #welcome-text {
// display: block;
// padding: 0px 10px;
// }


// /* footer */
// #footer {
// position: absolute;
// display: block;
// width: 100%;
// height: 60px;
// bottom: 0px;
// left: 0px;
// color: #FFF;
// text-align: center;
// font-size: 11px;
// background-color: #00A14B;
// padding: 20px 0px 0px 0px;
// line-height: 20px;
// }

// #footer .inner {
// color: #FFF;
// text-align: center;
// font-size: 11px;
// }

// </style>
// EOF;

// return $style;

// }

// function print_globals() {
// 	foreach($_SESSION as $key=>$value) {

// 		echo $key.': ';
// 		print_r($value);
// 		echo '<br>';
// 	}
// }


// /**
//  * Records specifics about the site installation
//  *
//  */
// function personalize_site($site_name, $site_description, $db) {
// // echo '<br>sn: '.$site_name.'<br>st: '.$site_description;
// 	$site_name = $db->mysqli_escape($site_name);
// 	$site_description = $db->mysqli_escape($site_description);
// 	$sql1 = "UPDATE ".TABLE_PREFIX."site_meta SET meta_value = '".$site_name."' WHERE meta_key = 'site_title'";
// 	$sql2 = "UPDATE ".TABLE_PREFIX."site_meta SET meta_value = '".$_SESSION['site_admin']."' WHERE meta_key = 'site_email'";
// 	$table_query = 'SELECT meta_value FROM '.TABLE_PREFIX.'site_meta WHERE meta_key = "site_description"';
//  	if (!$result = $db->query($table_query)) {	
// 		$sql3 = "UPDATE ".TABLE_PREFIX."site_meta SET  meta_value = '".$site_name."' WHERE meta_key = 'site_description'";
// 	}else{
// 		$sql3 = "INSERT INTO ".TABLE_PREFIX."site_meta (meta_key, meta_value) VALUES ('site_description', '".$site_description."')";
// 	}
// 	$db->query($sql1);
// 	$db->query($sql2);
// 	$db->query($sql3);
// // 		define_config_value('VCE_SITE_NAME', $site_name);
// // 		define_config_value('VCE_SITE_DESCRIPTION', $site_description);
// 	return 'continue';
	
// }




