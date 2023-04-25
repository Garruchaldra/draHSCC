<?php
/* 
*/
include_once('utility_login.php');


// if (isset($_POST)) {
//     echo '<pre>';
//    var_dump($_POST);
//     echo '</pre>';
// }

// $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$query_results = NULL;
$main_query = NULL;
if (isset($_POST['main_query'])) {
    $main_query = $_POST['main_query'];
    $query = $_POST['main_query'];

    if ($result = mysqli_query($db,$query)){
        if (isset($result)) {
            while ($obj = mysqli_fetch_object($result)){
                foreach ($obj as $k=>$v) {
                    $query_results .= $obj->$k;
                    $query_results .=  "<br><br>";
                }
            }
            mysqli_free_result($result);
        }
    }
    mysqli_close($db);
}



$directory_tree = listFolderFiles('.');
$content = NULL;
$content .= <<<EOF
<!DOCTYPE html>
<html>
<head>
<script>
    /*

    CollapsibleLists.js

    An object allowing lists to dynamically expand and collapse

    Created by Kate Morley - http://code.iamkate.com/ - and released under the terms
    of the CC0 1.0 Universal legal code:

    http://creativecommons.org/publicdomain/zero/1.0/legalcode

    */

    const CollapsibleLists = (function(){

    // Makes all lists with the class 'collapsibleList' collapsible. The
    // parameter is:
    //
    // doNotRecurse - true if sub-lists should not be made collapsible
    function apply(doNotRecurse){

        [].forEach.call(document.getElementsByTagName('ul'), node => {

        if (node.classList.contains('collapsibleList')){

            applyTo(node, true);

            if (!doNotRecurse){

            [].forEach.call(node.getElementsByTagName('ul'), subnode => {
                subnode.classList.add('collapsibleList')
            });

            }

        }

        })

    }

    // Makes the specified list collapsible. The parameters are:
    //
    // node         - the list element
    // doNotRecurse - true if sub-lists should not be made collapsible
    function applyTo(node, doNotRecurse){

        [].forEach.call(node.getElementsByTagName('li'), li => {

        if (!doNotRecurse || node === li.parentNode){

            li.style.userSelect       = 'none';
            li.style.MozUserSelect    = 'none';
            li.style.msUserSelect     = 'none';
            li.style.WebkitUserSelect = 'none';

            li.addEventListener('click', handleClick.bind(null, li));

            toggle(li);

        }

        });

    }

    // Handles a click. The parameter is:
    //
    // node - the node for which clicks are being handled
    function handleClick(node, e){

        let li = e.target;
        while (li.nodeName !== 'LI'){
        li = li.parentNode;
        }

        if (li === node){
        toggle(node);
        }

    }

    // Opens or closes the unordered list elements directly within the
    // specified node. The parameter is:
    //
    // node - the node containing the unordered list elements
    function toggle(node){

        const open = node.classList.contains('collapsibleListClosed');
        const uls  = node.getElementsByTagName('ul');

        [].forEach.call(uls, ul => {

        let li = ul;
        while (li.nodeName !== 'LI'){
            li = li.parentNode;
        }

        if (li === node){
            ul.style.display = (open ? 'block' : 'none');
        }

        });

        node.classList.remove('collapsibleListOpen');
        node.classList.remove('collapsibleListClosed');

        if (uls.length > 0){
        node.classList.add('collapsibleList' + (open ? 'Open' : 'Closed'));
        }

    }

    return {apply, applyTo};

    })();
    document.addEventListener("DOMContentLoaded", function(){
        // Handler when the DOM is fully loaded
        CollapsibleLists.apply();
        // runOnLoad(CollapsibleLists);
    });
</script>
</head>
<body>

<h2>Nestor DB Utility v2</h2>

<form method="post">
<textarea name="main_query" rows="20" cols="100">
$main_query
</textarea>
  <br><br>
  <input type="submit" value="Submit">
</form>

<p>Enter any SQL query and the result will be printed to the page.</p>
<br>
$query_results
<br><br>

$directory_tree
<br>
</body>
</html>
EOF;

echo $content;

function listFolderFiles($dir){
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

    $this_content = '<ul class="collapsibleList">';
    foreach($ffs as $ff){
        if ($ff == '.git' || $ff == 'data_interface_bhu9') {
            continue;
        }
        $dir_symbol = NULL;
        $file_symbol = '&nbsp;&nbsp;&nbsp;&nbsp;';
        if(is_dir($dir.'/'.$ff)) {
            $dir_symbol = '[dir] ';
            $file_symbol = NULL;
        }
        $this_content .= '<li>'.$file_symbol.$dir_symbol.$ff;
        
        if(is_dir($dir.'/'.$ff)) {
            $level_down = listFolderFiles($dir.'/'.$ff);
            $this_content .= $level_down;
        }
        $this_content .= '</li>';
    }
    $this_content .= '</ul>';

    return $this_content;
}






