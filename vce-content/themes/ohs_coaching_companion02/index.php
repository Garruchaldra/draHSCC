<?php
/*
Template Name: Index
*/
?>
<!DOCTYPE html>
<html lang="en">
<meta charset="utf-8">
<head>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5FW243J');</script>
<!-- End Google Tag Manager -->


<title><?php $site->site_title(); ?> : <?php $page->title(); ?></title>
<link rel="shortcut icon" href="<?php echo $site->theme_path; ?>/favicon.png" type="image/x-icon">	
<?php $content->javascript(); ?>
<?php $content->stylesheet(); ?>
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5FW243J"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div id="wrapper">

<div id="content">

<div id="decorative-bar"></div>

<div id="header">
<div class="inner">

<div class="site-info site-link">

<a class="eclkc-logo" href="https://eclkc.ohs.acf.hhs.gov"></a>

<h1 class="ccicon">

Head Start Coaching Companion<!-- <div class="trademark">&#8482;</div> -->
<!-- <div class="site-description">Office of Head Start Coaching Companion</div> -->
<!-- <div class="site-description"><?php $site->site_description(); ?></div> --></h1>
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