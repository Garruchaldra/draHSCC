<?php
/*
Template Name: Login
*/
?>
<!DOCTYPE html>
<html lang="en">
<meta charset="utf-8">
<title><?php $site->site_title(); ?> : <?php $page->title(); ?></title>
<link rel="shortcut icon" href="<?php echo $site->theme_path; ?>/favicon.png" type="image/x-icon">	
<?php $content->javascript(); ?>
<?php $content->stylesheet(); ?>
</head>
<body>
<div id="header"><div id="header-logo"></div></div>
<div id="container">
<h1>In-Person Course Download (Login)</h1>
<?php $content->output(array('admin', 'premain', 'main', 'postmain')); ?>
<?php $content->menu('registration','<div class="seporator">|</div>'); ?>
<div style="clear:both;"><?php footer(); ?></div>
</div>
</body>
</html>