<?php
/*
Theme Name: OHS Coaching Companion 02
*/

function footer() {
	//page-speed
// 	global $starttime;
// 	$mtime = microtime(); 
// 	$mtime = explode(" ",$mtime); 
// 	$mtime = $mtime[1] + $mtime[0]; 
// 	$endtime = $mtime; 
// 	$totaltime = ($endtime - $starttime);

echo '<div class="footer_text" style="float:left; color:white">The Coaching Companion was originally developed under Grant #90HC0002 for the U.S. Department of Health and Human Services,<br>
 Administration for Children and Families, Office of Head Start, by the National Center on Quality Teaching and Learning.</div>';
// 	echo '<div class="copy">&copy; ' . date("Y") . ' All rights reserved</div>';
	echo '<div class="footer_text" style="float:right; color:white">Office of Head Start Coaching Companion</div>';
// 	echo round($totaltime,3) . " @ " . round(memory_get_usage()/1024,2);
}

global $site;

//add javascript for theme specific things
$site->add_script($site->theme_path . '/js/scripts.js','jquery');

//add stylesheet
$site->add_style($site->theme_path . '/css/style.css', 'ohscc-theme-style');