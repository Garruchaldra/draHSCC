<?php
/*
* Get count of unread notifications 
*/
global $vce;

$base_datalist = $vce->get_datalist_items(array('datalist' => 'notifications_datalist', 'user_id' => $vce->user->user_id));

if (empty($base_datalist['items'])) {

    $new_notification_count = 0;

} else {

    // sort by created_at is desc order
    $items = $vce->sorter($base_datalist['items'], 'created_at', 'desc', 'timestamp');
    
    $new_notification_count = 0;
    foreach ($items as $each_notification) {

        

        // $link = isset($each_notification['link']) ? $each_notification['link'] : null;
        // $subject = isset($each_notification['subject']) ? $each_notification['subject'] : null;
        // $message = isset($each_notification['message']) ? $each_notification['message'] : null;
        // $created_at = isset($each_notification['created_at']) ? date("F j, Y, g:i a", $each_notification['created_at']) : null;
        if ($each_notification['viewed'] == 'false') {
            $new_notification_count++;
        }
    }
}