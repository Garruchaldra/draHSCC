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

<div id="wrapper">

<div id="content">

<div id="decorative-bar"></div>

<div id="header">
<div class="inner">
<h1>Coaching Companion<div class="trademark">&#8482;</div>
<div class="site-description"><?php $site->site_description(); ?></div>
</h1>
</div>
</div>

<div id="info-bar">
<div class="inner">

<div id="info-bar-left">
<?php $content->breadcrumb(); ?>
</div>

<div id="info-bar-right">
</div>

</div>
</div>

<br>
<div class="inner">

<?php if (isset($page->message)) { ?>
<div class="form-message form-success"><?php echo $page->message; ?><div class="close-message">x</div></div>
<?php } ?>

<?php $content->output(array('admin', 'premain', 'main', 'postmain')); ?>
<?php $content->menu('registration','<div class="separator">|</div>'); ?>
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