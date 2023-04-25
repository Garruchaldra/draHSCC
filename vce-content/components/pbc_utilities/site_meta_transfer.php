<?php 
exit;
include '../../../vce-config.php';
// include 'utility_login.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_keys = array('export_db');
    foreach ($_POST as $k => $v) {
        if (in_array($k, $post_keys)){
            //ENTER THE RELEVANT INFO BELOW
            $mysqlUserName      = DB_USER;
            $mysqlPassword      = DB_PASSWORD;
            $mysqlHostName      = DB_HOST;
            $DbName             = DB_NAME;
            $backup_name        = "mybackup.sql";

        //or add 5th parameter(array) of specific tables:    array("mytable1","mytable2","mytable3") for multiple tables

            Export_Database($mysqlHostName,$mysqlUserName,$mysqlPassword,$DbName,  $tables=false, $backup_name=false );
        }
    }
}


// Report  errors [set to 0 for live site]
ini_set('error_reporting', E_ALL);

// Define BASEPATH as this file's path
define('BASEPATH', '../../../');

$content = '';

if (isset($_POST['config_contents'])) {
    $config_contents = '<?php ' . $_POST['config_contents'];
    // print_r($config_contents);
    file_put_contents(BASEPATH . 'vce-config.php', $config_contents);
    $content .= "vce-config.php has been edited.";
}




$this_page = htmlspecialchars("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
$content .= <<<EOF
<form method="post" action="$this_page" id="config-form">  
EOF;

$config = file_get_contents(BASEPATH . 'vce-config.php');
$config = preg_replace('/\<\?php/', '', $config);
$content .= '<textarea name="config_contents" rows="30" cols="300" form="config-form">'.$config.'</textarea><br>';
$content .= <<<EOF
<input type="submit" name="submit" value="Submit">  <br><br><br><br><br>
</form>
EOF;




$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_errno) {
    echo "Sorry, this website is experiencing problems.";
    echo "Error: Failed to make a MySQL connection, here is why: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    echo "Error: " . $mysqli->connect_error . "\n";
    exit;
}





$content .= <<<EOF
<form method="post" action="$this_page">  
<input type="hidden" name="correct_assignees" value="TRUE">
<input type="submit" name="submit" value="Correct Assignees">  <br>
</form>
EOF;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_keys = array('correct_assignees');

    // find grants
    $sql = "SHOW GRANTS FOR CURRENT_USER";
    if (!$result = $mysqli->query($sql)) {
        // Oh no! The query failed. 
        echo "Sorry, can't show grants.";
        echo "Error: Our query failed to execute and here is why: \n";
        echo "Query: " . $sql . "\n";
        echo "Errno: " . $mysqli->errno . "\n";
        echo "Error: " . $mysqli->error . "\n";
        exit;
    }

    // echo grants
    foreach ($result as $r) {
        echo print_r($r, TRUE);
    }

    foreach ($_POST as $k => $v) {
        if (in_array($k, $post_keys) && $v == TRUE){

$corrections = array();
$corrections[0]['target']='user_ids_observers';
$corrections[0]['sql'] = "
SELECT  a.component_id, a.meta_value 
FROM vce_components_meta AS a 
JOIN vce_components_meta AS b ON a.component_id = b.component_id  
WHERE a.meta_key = 'user_ids_observers' 
AND b.component_id NOT IN (SELECT DISTINCT component_id FROM vce_components_meta WHERE meta_key = 'observers' OR meta_key = 'observed') 
AND b.meta_key = 'type'
ORDER BY a.component_id DESC
";

$corrections[1]['target']='user_ids_observed';
$corrections[1]['sql'] =  "
SELECT  a.component_id, a.meta_value 
FROM vce_components_meta AS a 
JOIN vce_components_meta AS b ON a.component_id = b.component_id  
WHERE a.meta_key = 'user_ids_observed' 
AND b.component_id NOT IN (SELECT DISTINCT component_id FROM vce_components_meta WHERE meta_key = 'observers' OR meta_key = 'observed') 
AND b.meta_key = 'type'
ORDER BY a.component_id DESC
";

$corrections[2]['target']='user_ids_aps_assignee';
$corrections[2]['sql'] =  "
SELECT  a.component_id, a.meta_value 
FROM vce_components_meta AS a 
JOIN vce_components_meta AS b ON a.component_id = b.component_id  
WHERE a.meta_key = 'user_ids_aps_assignee' 
AND b.component_id NOT IN (SELECT DISTINCT component_id FROM vce_components_meta WHERE meta_key = 'aps_assignee') 
AND b.meta_key = 'type'
ORDER BY a.component_id DESC
";

$corrections[3]['target']='user_ids_cycle_participants';
$corrections[3]['sql'] =  "
SELECT  a.component_id, a.meta_value 
FROM vce_components_meta AS a 
JOIN vce_components_meta AS b ON a.component_id = b.component_id  
WHERE a.meta_key = 'user_ids_cycle_participants' 
AND b.component_id NOT IN (SELECT DISTINCT component_id FROM vce_components_meta WHERE meta_key = 'aps_assignee') 
AND b.meta_key = 'type'
ORDER BY a.component_id DESC
";

foreach ($corrections as $c) {
    correct_assignees($c['sql'], $mysqli, $c['target']);
}

        }
    }
}







// database export
$content .= <<<EOF
<form method="post" action="$this_page">  
<input type="hidden" name="export_db" value="TRUE">
<input type="submit" name="submit" value="Export DB">  <br>
</form>
EOF;

echo $content;











if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_keys = array('site_url', 'site_menus', 'site_theme', 'roles', 'installed_components', 'preloaded_components', 'activated_components','user_attributes');

    foreach ($_POST as $k => $v) {
        if (in_array($k, $post_keys)){
            // print_r($k.': '.$v);
            // echo '<br><br><br>';
            $sql = "UPDATE vce_site_meta SET meta_value = ? WHERE meta_key = ?";
            $stmt = $mysqli->prepare("UPDATE vce_site_meta SET meta_value = ? WHERE meta_key = ?");
            if ($stmt === false) {
                trigger_error($this->mysqli->error, E_USER_ERROR);
                return;
              }
            if (!$stmt->bind_param('ss', $v, $k)) {
                echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
            }

            // $stmt->execute();

            // $sql = "UPDATE vce_site_meta SET meta_value = '$v' WHERE meta_key = '$k'";
            if (!$result = $stmt->execute()) {
                // Oh no! The query failed. 
                echo "Sorry, the website is experiencing problems.";
                echo "Error: Our query failed to execute and here is why: \n";
                echo "Query: " . $sql . "\n";
                echo "Errno: " . $mysqli->errno . "\n";
                echo "Error: " . $mysqli->error . "\n";
                exit;
            } else {
                echo "$k has been processed <br>";
            }
        }
    }
}

// $sql = "UPDATE vce_site_meta SET meta_value = '$v' WHERE meta_key = '$k'";
// if (!$result = $mysqli->query($sql)) {
//     // Oh no! The query failed. 
//     echo "Sorry, the website is experiencing problems.";
//     echo "Error: Our query failed to execute and here is why: \n";
//     echo "Query: " . $sql . "\n";
//     echo "Errno: " . $mysqli->errno . "\n";
//     echo "Error: " . $mysqli->error . "\n";
//     exit;
// } else {
//     echo "$k has been processed <br>";
// }


// Perform an SQL query
$sql = "SELECT meta_key, meta_value, minutia FROM vce_site_meta";
if (!$result = $mysqli->query($sql)) {
    // Oh no! The query failed. 
    echo "Sorry, the website is experiencing problems.";
    echo "Error: Our query failed to execute and here is why: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    echo "Error: " . $mysqli->error . "\n";
    exit;
}


if ($result->num_rows === 0) {
    echo "There are no rows in this result. Please try again.";
    exit;
}

$content2 = <<<EOF
<form method="post" action="$this_page">  
<input type="submit" name="submit" value="Submit Site_meta fields">  <br>
EOF;


// Now, we know only one result will exist in this example so let's 
// fetch it into an associated array where the array's keys are the 
// table's column names
// $content2 = '';
$info = $result->fetch_assoc();
foreach ($result as $r) {
    $label = '';
    foreach ($r as $key=>$value) { 
        if ($key == 'meta_key'){
            $label = $value;
            continue;
        }
        if ($key == 'minutia'){
            $label = 'minutia';
        }
        $content2 .= <<<EOF
        $label:  <textarea name="$label" rows="5" cols="40">$value</textarea><br>
EOF;
$content3 .= <<<EOF
$key:  $value<br>
EOF;
    }
    $content2 .= <<<EOF
<br><br><br>
EOF;
$content3 .= <<<EOF
<br><br><br>
EOF;
}


echo "<br><br><br><br><br><br>";
echo $content2;
echo "<br><br><br><br><br><br>";
echo $content3;



/*
This function lists the assignees for drag and drop (old style) menus in fo and ap_steps
It can be used in conjunction with the edit_assignees() to convert old data to new
*/
function correct_assignees ($sql, $mysqli, $target, $edit=FALSE) {
    // Perform an SQL query
if (!$result = $mysqli->query($sql)) {
    // Oh no! The query failed. 
    echo "Sorry, the correction function is experiencing problems.";
    echo "Error: Our query failed to execute and here is why: \n";
    echo "Query: " . $sql . "\n";
    echo "Errno: " . $mysqli->errno . "\n";
    echo "Error: " . $mysqli->error . "\n";
    exit;
}
switch ($target) {
    case "user_ids_observers":
        $new_target = 'observers';
        break;
    case "user_ids_observed":
        $new_target = 'observed';
        break;
    case "user_ids_aps_assignee":
        $new_target = 'aps_assignee';
        break;
    case "user_ids_cycle_participants":
        $new_target = 'cycle_participants';
        break;
}
$correction_content = $target . '<br>';
// $info = $result->fetch_assoc();
$i = 0;
foreach ($result as $r) {
    foreach ($r as $key=>$value) {
        if ($i > 30) {
            break;
        }
        if ($key == 'component_id') {
            $component_id_display = $i . '.) ' . $value . ': ';
            $component_id = $value;
        }
        if ($key == 'meta_value') {
            $dl_input = json_decode(html_entity_decode($value));
            // $correction_content .=  print_r($dl_input, true);
            if (strlen($dl_input->user_ids) == 0) {
                $correction_content .= $component_id_display . 'empty<br>';
                $new_value = '';
            } else {
                $assignees = trim($dl_input->user_ids, '|');
                $assignees = explode('|', $assignees);
                $correction_content .= $component_id_display;
                $new_value = '|';
                foreach ($assignees as $user_id) {
                    $correction_content .= $user_id . ' ';
                    $new_value .= $user_id . '|';
                }
                $correction_content .= '<br>';
            }
            edit_assignees($target, $new_target, $new_value, $component_id, $mysqli);
        }
    }
    $i++;
}

echo $correction_content;

}


/*
This function is called from correct_assignees().
It takes old assignee info from existing components, converts it to new data format, inserts the new data
format in the old component and erases the old data
*/
function edit_assignees($old_target, $new_target, $new_value, $component_id, $mysqli) {
    $sql = "UPDATE vce_components_meta SET meta_key = '$new_target', meta_value = '$new_value' WHERE component_id = '$component_id' AND meta_key = '$old_target'";
    if (!$result = $mysqli->query($sql)) {
        echo "Couldn't Update.";
        echo "Here is why: \n";
        echo "Query: " . $sql . "\n";
        echo "Errno: " . $mysqli->errno . "\n";
        echo "Error: " . $mysqli->error . "\n";
    } else {
        echo "Edited $component_id, $old_target, $new_target: $new_value <br>";
    }

}


function Import_Database($host,$user,$pass,$name,  $tables=false, $backup_name=false ) {
   
    // Name of the file
    $filename = 'churc.sql';
    // MySQL host
    $mysql_host = 'localhost';
    // MySQL username
    $mysql_username = 'root';
    // MySQL password
    $mysql_password = '';
    // Database name
    $mysql_database = 'dump';

    // Connect to MySQL server
    mysql_connect($mysql_host, $mysql_username, $mysql_password) or die('Error connecting to MySQL server: ' . mysql_error());
    // Select database
    mysql_select_db($mysql_database) or die('Error selecting MySQL database: ' . mysql_error());

    // Temporary variable, used to store current query
    $templine = '';
    // Read in entire file
    $lines = file($filename);
    // Loop through each line
    foreach ($lines as $line)
    {
    // Skip it if it's a comment
    if (substr($line, 0, 2) == '--' || $line == '')
        continue;

    // Add this line to the current segment
    $templine .= $line;
    // If it has a semicolon at the end, it's the end of the query
    if (substr(trim($line), -1, 1) == ';')
    {
        // Perform the query
        mysql_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
        // Reset temp variable to empty
        $templine = '';
    }
    }
    echo "Tables imported successfully";

}

function Export_Database($host,$user,$pass,$name,  $tables=false, $backup_name=false )
{
    $mysqli = new mysqli($host,$user,$pass,$name); 
    $mysqli->select_db($name); 
    $mysqli->query("SET NAMES 'utf8'");

    $queryTables    = $mysqli->query('SHOW TABLES'); 
    while($row = $queryTables->fetch_row()) 
    { 
        $target_tables[] = $row[0]; 
    }   
    if($tables !== false) 
    { 
        $target_tables = array_intersect( $target_tables, $tables); 
    }
    foreach($target_tables as $table)
    {
        $result         =   $mysqli->query('SELECT * FROM '.$table);  
        $fields_amount  =   $result->field_count;  
        $rows_num=$mysqli->affected_rows;     
        $drop_table_command = "DROP TABLE IF EXISTS $table";
        $content        = (!isset($content) ?  '' : $content) . "\n\n".$drop_table_command.";\n\n";
        $res            =   $mysqli->query('SHOW CREATE TABLE '.$table); 
        $TableMLine     =   $res->fetch_row();
        $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

        for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) 
        {
            while($row = $result->fetch_row())  
            { //when started (and every after 100 command cycle):
                if ($st_counter%100 == 0 || $st_counter == 0 )  
                {
                        $content .= "\nINSERT INTO ".$table." VALUES";
                }
                $content .= "\n(";
                for($j=0; $j<$fields_amount; $j++)  
                { 
                    $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); 
                    if (isset($row[$j]))
                    {
                        $content .= '"'.$row[$j].'"' ; 
                    }
                    else 
                    {   
                        $content .= '""';
                    }     
                    if ($j<($fields_amount-1))
                    {
                            $content.= ',';
                    }      
                }
                $content .=")";
                //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) 
                {   
                    $content .= ";";
                } 
                else 
                {
                    $content .= ",";
                } 
                $st_counter=$st_counter+1;
            }
        } $content .="\n\n\n";
    }
    //$backup_name = $backup_name ? $backup_name : $name."___(".date('H-i-s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql";
    $backup_name = $backup_name ? $backup_name : $name.".sql";
    header('Content-Type: application/octet-stream');   
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-disposition: attachment; filename=\"".$backup_name."\"");  
    echo $content; exit;
}