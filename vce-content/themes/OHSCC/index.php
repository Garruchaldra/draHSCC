<?php
/*
Template Name: Index
*/
global $vce;
$user_data = NULL;
if (class_exists('Pbc_utilities')) { 
  $user_data = Pbc_utilities::get_user_data($vce->user->user_id);
  $new_notification_count = Pbc_utilities::get_new_notification_count($vce->user->user_id);
}
// $vce->dump($user_data);
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
            <img id="eclkc-logo" src="<?php echo $site->theme_path; ?>/images/Early_Childhood Development_Teaching_and_Learning02.png" alt="Head Start Early Childhood Learning & Knowledge Center" />
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
                  // this shows the new notifications which will be listed in My Account
                  $new_notification_count = (!isset($new_notification_count) || $new_notification_count == 0) ? '' : $new_notification_count;
                  // this content is hidden, but prepended to side_menu on page load
                  $icon_content = '<span id="envelope-icon-span" style="display:none"><img id="envelope-icon" style="height:20px; width:20px; top:5px; position:relative;" src="' . $site->theme_path . '/images/envelope_icon.png" alt="messages" /><span class="unread-notification-count" style="color:red;">' . $new_notification_count . '</span>&nbsp;</span>';
                  echo  $icon_content;
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

      <div class="progress-section">
        <div class="inner">
          <?php $content->output('progress_arrows'); ?>
        </div>
      </div>

      <!--Completed modal- when completed checkbox is checked-->
      <div id="completed-modal" class="modal hide">
        <div class="modal-content">
          <button class="close close-completed-modal">&times;</button>
          <svg id="checkmark-svg" class="run-animation" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 193.3 193.3"><circle class="cls-1 circle" cx="96.65" cy="96.65" r="94.15"></circle><polyline class="cls-1 checkmark" points="46.9 101.4 76.9 131.4 146.4 61.9"/></svg>
          <h1 class="congrats">Congratulations!</h1>
          <hr>
          <p>This item will be archived, but still accessible for reference in the area labeled "Completed."</p>
          <button type="button" class="btn button__primary complete-btn">Mark as Complete</button>
          <button type="button" class="btn button__primary cancel cancel-completed">Cancel</button>
        </div>
      </div>

      <!--Modal for when completed checkbox is unchecked-->
      <div id="unchecked-modal" class="modal hide">
        <div class="modal-content">
          <button class="close close-unchecked-modal">&times;</button>
          <p>Change the status of this item to "in-progress"?</p>
          <button type="button" class="btn button__primary unchecked-btn">Change to in-progress</button>
          <button type="button" class="btn button__primary cancel cancel-uncheck">Cancel</button>
        </div>
      </div>

      <div id="main">
        <div class="inner" id="main-inner">
          <?php if (isset($page->message)) { ?>
            <div class="form-message form-success"><?php echo $page->message; ?><div class="close-message">x</div></div>
          <?php } else { ?>
            <div class="form-message"></div>
          <?php } ?>

          <?php $content->output(array('admin', 'title', 'premain', 'sidebar', 'main', 'postmain')); ?>
        </div>
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
