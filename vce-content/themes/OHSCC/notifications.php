<?php
/*
Template Name: notifications
*/


global $vce;

//add javascript for theme specific things
$vce->site->add_script($vce->site->theme_path . '/js/scripts.js','jquery');

//add stylesheet
$vce->site->add_style($vce->site->theme_path . '/css/style.css', 'ccce-theme-style');
$vce->site->add_style($vce->site->theme_path . '/css/notifications_style.css', 'ccce-theme-style');

$user_data = NULL;
if (class_exists('Pbc_utilities')) { 
  $user_data = Pbc_utilities::get_user_data($vce->user->user_id);
}

?>
<!DOCTYPE html>
<html lang="en">
<head> 
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title><?php $site->site_title(); ?> : <?php $page->title(); ?></title>
  <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
  <link rel="shortcut icon" href="<?php echo $site->theme_path; ?>/favicon.png" type="image/x-icon">	
  <?php $content->javascript(); ?>
  <?php $content->stylesheet(); ?>
</head>

<body>
  <div id="wrapper">
    <div class="content-wrap">
      <header id="header">
        <div id="header-bg">
          <div id="decorative-bar">
            <a href="https://eclkc.ohs.acf.hhs.gov/" target="_blank">
            <img id="eclkc-logo" src="<?php echo $site->theme_path; ?>/images/Early_Childhood Development_Teaching_and_Learning.png" alt="Head Start Early Childhood Learning & Knowledge Center" />
            </a>
          </div>
          <div class="inner">
            <div id="welcome-bar">
              <?php if (isset($user->user_id)) { ?>
                <div class="side-menu_container">
                  <p class="welcome-text">Welcome, <?php echo $user->first_name; ?> <?php echo $user->last_name; ?></p>
                  <p>Org: <?php echo $user_data->organization_name; ?></p>
                  <p>Group: <?php echo $user_data->group_name; ?></p>
                  <div><?php $content->menu('side_menu'); ?></div>
                  <?php
                  // echo $content->notifications(null, true, true); 
                  ?>
                </div>
              <?php } ?>
            </div>

            <a href="<?php echo $site->site_url; ?>">
              <img id="ohscc-logo" src="<?php echo $site->theme_path; ?>/images/CoachingCompanionOHSLogo.png" alt="Head Start Coaching Companion" />
            </a>
            <button class="responsive-menu-icon" aria-label="responsive menu icon"></button>
            <div class="site-info"></div>
          </div> 

          <div class="responsive-menu">
            <div id="responsive-welcome-bar">
              <?php if (isset($user->user_id)) { ?>
                <div class="side-menu_container">
                  <p class="welcome-text">Welcome, <?php echo $user->first_name; ?> <?php echo $user->last_name; ?></p>
                  <p>Org: <?php echo $user_data->organization_name; ?></p>
                  <p>Group: <?php echo $user_data->group_name; ?></p>
                  <?php $content->menu('side_menu'); ?>
                </div>
              <?php } ?>
            </div>
            <hr>
            <?php $content->menu('main'); ?>
          </div>

          <div id="info-bar">
            <div class="inner">
              <?php $content->menu('main'); ?>
            </div>
          </div>
        </div>

        <div id="admin-bar">
          <div class="inner">
          </div>
        </div>
      </header>



      <div id="main">
        <div class="inner" id="main-inner">
          <?php if (isset($page->message)) { ?>
            <div class="form-message form-success"><?php echo $page->message; ?><div class="close-message">x</div></div>
          <?php } else { ?>
            <div class="form-message"></div>
          <?php } ?>



          <div class="pbc-item-header notifications"><h1>Notifications</h1></div>
			<?php echo $vce->content->notifications(null, true, true); ?>

		
		</div>
	</div>       
	   
	   




    <footer id="footer">
      <div id="footer-main">
        <div class="inner">
          <div class="quick-links">
            <?php $content->menu('quick_links'); ?>
          </div>
        </div>
        <a href="https://www.hhs.gov/" target="_blank" tabindex="-1">
          <img id="dhhs-logo" tabindex="0" src="<?php echo $site->theme_path; ?>/images/dhhs-logo-black.png" alt="Department of Health and Human Services" />
        </a>
        <a href="https://www.acf.hhs.gov/" target="_blank" tabindex="-1">
          <img id="acf-logo" tabindex="0" src="<?php echo $site->theme_path; ?>/images/acf_logo.png" alt="Administration for Children and Families" />
        </a>
        <a href="https://eclkc.ohs.acf.hhs.gov/ncecdtl" target="_blank" tabindex="-1">
          <img id="ncecdtl-logo" tabindex="0" src="<?php echo $site->theme_path; ?>/images/NCECDTL_logo.png" alt="National Center on Early Childhood Development, Teaching, and Learning" />
        </a>
      </div>

      <div id="footer-bottom-bar">
        <div class="inner">
          <?php footer(); ?>
        </div>
      </div>
    </footer>
  </div>
</body>
</html>