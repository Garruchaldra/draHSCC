<?php
/*
Template Name: Default
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
<h1>In-Person Course Download</h1>
<?php admin(); ?>
<?php //$site->menu('main'); ?>
<?php if (isset($user->user_id)) { ?>
<div class="admin-toggle add-toggle link-button">+ADD</div><div class="admin-toggle edit-toggle link-button">&#9998;EDIT</div> 
Welcome, <?php $user->first_name(); ?> <?php $user->last_name(); ?> (<?php $user->email(); ?>)
<?php } ?>
<?php if (isset($user->user_id)) { ?>
<div class="breadcrumb">
<?php $content->breadcrumb(); ?>
</div>
<?php } ?>
<?php $content->output(array('admin', 'premain', 'main', 'postmain')); ?>
<?php if (isset($user->user_id)) { ?>
<div class="breadcrumb"></div>
<?php } ?>
<?php $content->menu('main'); ?>
<div style="clear:both;"><?php footer(); ?></div>
</div>
</body>
</html>