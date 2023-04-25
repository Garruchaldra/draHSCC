<?php
/*
Template Name: Index
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

<div id="wrapper">

<div id="content">

<div id="decorative-bar"></div>

<div id="header">
<div class="inner">
<div class="site-info">
<h1>
Coaching Companion<div class="trademark">&#8482;</div>
<div class="site-description"><?php $site->site_description(); ?></div>
</h1>
</div>
<div class="responsive-menu-icon"></div>
</div>
</div>

<div class="responsive-menu">
<?php $content->menu('main'); ?>
</div>


<div id="info-bar">
<div class="inner">
<div id="info-bar-left">
<?php $content->breadcrumb(); ?>
</div>

<div id="info-bar-right">
<?php if (isset($user->user_id)) { ?>
<span id="welcome-text">Welcome, <?php echo $user->first_name; ?> <?php echo $user->last_name; ?></span>
<?php } ?>
<?php $content->menu('main'); ?>
</div>

</div>
</div>

<div id="admin-bar">
<div class="inner">
<div class="admin-toggle add-toggle link-button">+Add Content</div><div class="admin-toggle edit-toggle link-button">&#9998;Edit Content</div>
</div>
</div>

<br>
<div class="inner">

<?php if (isset($page->message)) { ?>
<div class="form-message form-success"><?php echo $page->message; ?><div class="close-message">x</div></div>
<?php } ?>

<?php $content->output(array('admin', 'premain', 'main', 'postmain')); ?>

</div>
</div>

<footer id="footer">
<div class="inner">
<?php footer(); ?>
</div>
</footer>

</div>
</body>
</html>