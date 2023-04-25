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

<div class="site-info site-link" id ="<?php echo $site->site_url; ?>" >
<h1 class="ccicon">

Coaching Companion<!-- <div class="trademark">&#8482;</div> -->
<div class="site-description">Office of Head Start Coaching Companion V.3</div>
<!-- <div class="site-description"><?php $site->site_description(); ?></div> -->
</h1>
</div>
<div class="responsive-menu-ccicon site-link"></div>
<div class="responsive-menu-icon"></div>
</div>
</div>

<div class="responsive-menu">
<?php $content->menu('main'); ?>
</div>


<div id="info-bar">
<div class="inner">
<div id="info-bar-left">
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
<!-- <div class="admin-toggle add-toggle link-button">+Add Content</div><div class="admin-toggle edit-toggle link-button">&#9998;Edit Content</div> -->
</div>
</div>


<br>
<div class="inner">
<div class="breadcrumbs"><?php $content->breadcrumb(); ?></div>

<!-- 
This is the logo and welcome text
 -->
<!-- 
<div class="welcome_wrapper">
<div class="welcome_image"><img src="<?php echo $site->theme_path; ?>/images/LogoBig02_18pc.png" alt="OHSCC"></div>
<div class="welcome_text_wrapper">
<div class="welcome_text">
Welcome to your Coaching Companion home page. Here you will see all PBC Cycles in which you are participating.<br>
To start a new cycle, click on &quot;Add New PBC Cycle&quot;
</div>
</div>
</div>
 -->





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