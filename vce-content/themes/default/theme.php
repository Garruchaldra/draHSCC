<?php
/*
Theme Name: Default VCE Theme
*/

function admin() {
	global $admin;
	
	if ($admin) {
	echo '<div class="clickbar-container"><div class="clickbar-content">';
	echo $admin;
	echo '</div><div class="clickbar-title clickbar-closed"><span>Manage Sub Components</span></div></div>';
	}
}

function menu($menu_name) {
	global $site;
	echo $site->menu($menu_name);
}

function content() {
	global $content;
	echo $content;
}

function footer() {
	//page-speed
	global $starttime;
	$mtime = microtime(); 
	$mtime = explode(" ",$mtime); 
	$mtime = $mtime[1] + $mtime[0]; 
	$endtime = $mtime; 
	$totaltime = ($endtime - $starttime);
	echo '<div class="copy">&copy; ' . date("Y") . ' University of Washington. All rights reserved</div>';
	echo round($totaltime,3) . " @ " . round(memory_get_usage()/1024,2);
}

global $site;

//add javascript for theme specific things
$site->add_script($site->theme_path . '/js/scripts.js','jquery');

//add stylesheet
$site->add_style($site->theme_path . '/css/style.css','default-theme-style');