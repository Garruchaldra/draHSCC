<?php
/*
Template Name: Copy
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

<div id="header">
<div id="top-header">
<a href="/notifications" id="cc-notifications" class="notifier-icon-none">Notifications</a>
<div id="cc-logo"><a href="<?php echo $page->site->site_url; ?>"></a></div>
<div id="cc-user">
<div id="cc-user-info">
<div id="cc-user-name"><?php echo $user->first_name; ?> <?php echo $user->last_name; ?></div>
<div id="cc-user-items">

<?php $content->menu('user'); ?>

</div>
</div>
<div id="cc-user-image">
<img class="profile_picture" src="<?php echo $page->site->site_url . '/vce-application/images/user_' . ($page->user->user_id % 5) . '.png'; ?>">
</div>
</div>
</div>

<div id="main-menu">
<div id="responsive-menu-icon"></div>
<div id="responsive-user-menu">
<?php $content->menu('user'); ?>
</div>
<div id="responsive-user-icon"></div>
<div id="responsive-nav-menu">
<?php $content->menu('main-left'); ?>
<?php $content->menu('main-right'); ?>
</div>

<?php $content->menu('main-left',array("class" => "main-nav")); ?>
<?php $content->menu('main-right',array("class" => "main-nav")); ?>

<div id="coaching-model" class="logo-container" <?php // style="width: 50px; top: -50px; margin-bottom: -50px;" ?>>

<div id="logo-overlay" style="display: none;">
<a href="<?php echo $site->site_url; ?>/goals" id="blue-link" color="blue" class="coaching-model-link">
<div id="sfap-1" class="ccp-overlay"></div>
<div id="sfap-2" class="ccp-overlay"></div>
<div id="sfap-3" class="ccp-overlay"></div>
<div id="sfap-4" class="ccp-overlay"></div>
<div id="sfap-5" class="ccp-overlay"></div>
<div id="sfap-6" class="ccp-overlay"></div>
<div id="sfap-7" class="ccp-overlay"></div>
<div id="sfap-7a" class="ccp-overlay"></div>
<div id="sfap-8" class="ccp-overlay"></div>
<div id="sfap-9" class="ccp-overlay"></div>
<div id="sfap-10" class="ccp-overlay"></div>
<div id="sfap-11" class="ccp-overlay"></div>
<div id="sfap-12" class="ccp-overlay"></div>
</a>
<a href="<?php echo $site->site_url; ?>/observations" id="red-link" color="red" class="coaching-model-link">
<div id="fo-1" class="ccp-overlay"></div>
<div id="fo-2" class="ccp-overlay"></div>
<div id="fo-3" class="ccp-overlay"></div>
<div id="fo-4" class="ccp-overlay"></div>
<div id="fo-5" class="ccp-overlay"></div>
<div id="fo-5a" class="ccp-overlay"></div>
<div id="fo-6" class="ccp-overlay"></div>
<div id="fo-7" class="ccp-overlay"></div>
<div id="fo-8" class="ccp-overlay"></div>
<div id="fo-9" class="ccp-overlay"></div>
<div id="fo-10" class="ccp-overlay"></div>
<div id="fo-11" class="ccp-overlay"></div>
<div id="fo-12" class="ccp-overlay"></div>
</a>
<a href="<?php echo $site->site_url; ?>/reflections" id="yellow-link" color="yellow" class="coaching-model-link">
<div id="raf-1" class="ccp-overlay"></div>
<div id="raf-2" class="ccp-overlay"></div>
<div id="raf-3" class="ccp-overlay"></div>
<div id="raf-4" class="ccp-overlay"></div>
<div id="raf-5" class="ccp-overlay"></div>
<div id="raf-6" class="ccp-overlay"></div>
<div id="raf-7" class="ccp-overlay"></div>
<div id="raf-8" class="ccp-overlay"></div>
<div id="raf-9" class="ccp-overlay"></div>
<div id="raf-10" class="ccp-overlay"></div>
<div id="raf-11" class="ccp-overlay"></div>
<div id="raf-12" class="ccp-overlay"></div>
<div id="raf-13" class="ccp-overlay"></div>
<div id="raf-14" class="ccp-overlay"></div>
<div id="raf-15" class="ccp-overlay"></div>
<div id="raf-16" class="ccp-overlay"></div>
<div id="raf-17" class="ccp-overlay"></div>
</a>
</div>

<img id="logo-small" src="<?php echo $site->theme_path; ?>/images/coaching_model_small.png" style="display: block;">
<img id="logo" src="<?php echo $site->theme_path; ?>/images/coaching_model_large.png" style="display: none;">
<img id="logo-blue" class="logo-color" src="<?php echo $site->theme_path; ?>/images/coaching_model_blue.png" style="display: none;">
<img id="logo-red" class="logo-color" src="<?php echo $site->theme_path; ?>/images/coaching_model_red.png" style="display: none;">
<img id="logo-yellow" class="logo-color" src="<?php echo $site->theme_path; ?>/images/coaching_model_yellow.png" style="display: none;">
</div>



</div>
</div>

<div class="full-width-line"></div>

<div id="content">

<div id="breadcrumbs"><?php $content->breadcrumb(); ?></div>
<div id="title-block">
<div id="page-title"><?php $page->title(); ?></div>
<div id="page-buttons">
<?php /* ?>
<div class="admin-toggle add-toggle link-button">Add Content</div>
<div class="admin-toggle edit-toggle link-button">Edit Content</div>
<?php */ ?>
</div>
</div>


<?php if (isset($page->message)) { ?>
<div class="form-message form-success"><?php echo $page->message; ?><div class="close-message">x</div></div>
<?php } ?>

<?php $content->output(array('admin', 'premain', 'main', 'postmain')); ?>

</div>


<div id="footer">
	<div id="footer-contents">
		<div id="footer-links">
			<?php $content->menu('footer'); ?>
			<div id="footer-copyright">Copyright © <?php echo date('Y'); ?> University of Washington</div>
		</div>
		<div id="cqel-logo"><a href="http://cqel.org"><img src="<?php echo $site->theme_path; ?>/images/cultivate_learning_logo.png"></a></div>
	</div>
</div>

</div>
</body>
</html>