<?php

/**
 * Amazon aws mailer component
 *
 * @category   Media
 * @package    AWS
 * @author     Andy Sodt <asodt@uw.edu>
 * @copyright  2018 University of Washington
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @version    git: $Id$
 */

require_once __DIR__ . '/../aws_support.php';

class AWSMailer extends Component {

    /**
     * basic info about the component
     */
    public function component_info() {

        return array(
            'name' => 'AWSMailer',
            'description' => 'AWS email creation and transport Class',
            'category' => 'mail',
            'recipe_fields' => array('auto_create', 'title', 'url')
        );
    }

    /**
     * output to page
     */
    public function as_content($each_component, $vce) {

        $dossier = array(
            'type' => 'AWSMailer',
            'procedure' => 'send',
        );

        $content = '';
        $x = $vce->content;

        $content .= $x->create_text_input('from', '', $this->lang('from'));
        $content .= $x->create_text_input('to', '', $this->lang('to'));
        $content .= $x->create_text_input('cc', '', $this->lang('cc'));
        $content .= $x->create_text_input('bcc', '', $this->lang('bcc'));
        $content .= $x->create_text_input('subject', '', $this->lang('subject'));
        $content .= $x->create_textarea_input('message', '', $this->lang('message'));

        $content = $x->update_form($content, $this, $dossier, $this->lang('send'));
        $content = $x->accordion($this->lang('mail'), $content, true, false);

        $vce->content->add('main', $content);

    }

    public function send($input) {

        global $vce;

        $attributes = array(
            'html' => true,
            'from' => array(trim(htmlspecialchars_decode($input['from'])), trim(htmlspecialchars_decode($input['from']))),
            'subject' => trim(htmlspecialchars_decode($input['subject'])),
            'message' => trim(htmlspecialchars_decode($input['message'])),
        );

		if (isset($input['to']) && $input['to'] != '') {
            $attributes['to'] = $this->email_array(trim(htmlspecialchars_decode($input['to'])));
		}
		
        if (isset($input['cc']) && $input['cc'] != '') {
            $attributes['cc'] = $this->email_array(trim(htmlspecialchars_decode($input['cc'])));
        }

        if (isset($input['bcc']) && $input['bcc'] != '') {
            $attributes['bcc'] = $this->email_array(trim(htmlspecialchars_decode($input['bcc'])));
		}
        
        global $vce;

        $vce->mail($attributes);

        echo json_encode(array('response' => 'success', 'message' => 'Mail Sent!'));

    }

	public function email_array($emails) {
		$emails = explode(',', $emails);
		$email_array = array();
		foreach ($emails as $email) {
			array_push($email_array, array($email, $email));
		}

		return $email_array;
	}

    /**
     * things to do when this component is preloaded
     */
    public function preload_component() {

        $content_hook = array(
            'site_mail_transport' => 'AWSMailer::mail_transport',
        );

        return $content_hook;

    }

    public static function mail_transport($vce, $attributes) {

        $config = AWSSupport::get_config($vce);
        $sdk = AWSSupport::get_sdk($config);

        // send as html email
        if (isset($attributes['html']) && $attributes['html'] == true) {
            // clean-up any html email copy
            $attributes['message'] = html_entity_decode(stripcslashes($attributes['message']));
        }
        
        $mail_sender = $attributes['from'][0];

        // If the mail_sender is set, override the from attribute.
        if (!empty($config['mail_sender'])) {
            $mail_sender = $config['mail_sender'];
        }

        // clean up to make aws mailer happy
		$from[] = $mail_sender;
		$value = preg_replace("/\&#039\;/", "'", $attributes['from'][1]);
		$from[] = preg_replace("/[^a-zA-Z0-9-' ]/", "", html_entity_decode($value));

		$to[] = $attributes['to'][0];
		$value = preg_replace("/\&#039\;/", "'", $attributes['to'][1]);
		$to[] = preg_replace("/[^a-zA-Z0-9-' ]/", "", html_entity_decode($value));

        $message_id = AWSSupport::send_mail(
            $from,
            $to,
            isset($attributes['cc']) ? $attributes['cc'] : [],
            isset($attributes['bcc']) ? $attributes['bcc'] : [],
            $attributes['subject'],
            $attributes['message'],
            $sdk,
            $config
        );
    }

}