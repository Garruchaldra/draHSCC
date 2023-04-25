<?php
if (session_status() == PHP_SESSION_NONE) {
    //     start_session();
        session_start();
        //record when the session started
        if (!isset($_SESSION['started'])) {
                $_SESSION['started'] = time();
        }
    }

//do this if logged in
if(isset($_POST["persontocheck"]) && isset($_POST["passwordtocheck"]) && $_POST["persontocheck"] == DB_USER && $_POST["passwordtocheck"] == DB_PASSWORD) {
	$_SESSION['happening'] = 'goforit';
}

error_reporting(E_ALL);
ini_set('display_errors', 1);


//an additional little password check
$version = "version01";
if(!isset($_SESSION['happening']) || $_SESSION['happening'] != 'goforit') {	
	$login = <<<EOF
<!DOCTYPE html>
<html>
<head>
    <title>site_meta adjustments</title>
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
<br>
<form action="" method="post" enctype="multipart/form-data">
 <label>Name</label>
<input type="text" name="persontocheck"><br>
 <label>Password</label>
<input type="password" name="passwordtocheck"><br>
<input type="submit" value="Log In" name="submit $version">

</form>

</body>
</html>
EOF;
	echo $login;
	exit;
	
}