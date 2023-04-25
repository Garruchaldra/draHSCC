<?php
/* this is a utility to turn database sessions on or off for the Nestor core code.
This is necessary so that if some system configuration causes the database sessions to fail, as they have in the past, we can turn them off.


*/

include_once('utility_login.php');


// if (isset($_POST)) {
//     echo '<pre>';
//    var_dump($_POST);
//     echo '</pre>';
// }

// view the file specified
if (isset($_POST['view_log']) ){
    $file = './log.txt';
    $log_contents = file_get_contents($file);
    echo '<pre>';
   var_dump($log_contents);
    echo '</pre>';
}

if (isset($_POST['view_plog']) ){
    $file = './plog.txt';
    $plog_contents = file_get_contents($file);
    echo '<pre>';
    var_dump($plog_contents);
     echo '</pre>';
}

// empty the file specified
if (isset($_POST['empty_plog']) ){
    $file = './plog.txt';
    file_put_contents($file, '');
}
if (isset($_POST['empty_log']) ){
    $file = './log.txt';
    file_put_contents($file, '');
}



$content = NULL;

$content .= <<<EOF
<!DOCTYPE html>
<html>
<head>

</head>
<body>

<h2>Nestor Log Management</h2>

<form method="post">
<input type="checkbox" id="empty-log" name="empty_log" value="empty_log">
<label for="empty-plog">Empty the log</label><br>
<input type="checkbox" id="empty-plog" name="empty_plog" value="empty_plog">
<label for="empty-plog">Empty the plog</label><br>
<input type="checkbox" id="view-log" name="view_log" value="view_log">
<label for="view-log">View the log</label><br>
<input type="checkbox" id="view-plog" name="view_plog" value="view_plog">
<label for="view-plog">View the plog</label><br>
  <br><br>
  <input type="submit" value="Submit">
</form>


</body>
</html>
EOF;

echo $content;






