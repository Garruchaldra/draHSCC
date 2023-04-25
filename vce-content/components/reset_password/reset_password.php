<?php

class ResetPassword extends Component {

    /**
     * basic info about the component
     */
    public function component_info() {
        return array(
            'name' => 'Reset Password',
            'description' => 'user can reset password',
            'category' => 'user',
            'recipe_fields' => array('auto_create','title','url','template')
        );
    }

    /**
     * Component has been activated.
     */
    public function activated() {
        // create tokens datalist
        global $vce;
        
        // find datalist_id for 'invitations_datalist'
		$datalist = $vce->get_datalist(array('datalist' => 'password_reset_datalist'));
		
		if (empty($datalist)) {

			$attributes = array(
				'datalist' => 'password_reset_datalist',
				'aspects' => array('name' => 'Password Reset'),
			);
			$vce->create_datalist($attributes);
        
		}
        
    }

    /**
     * Component has been disabled.
     */
    public function disabled() {

        global $vce;

        // remove the datalist associated with this component
        $attributes = array(
            'datalist' => 'password_reset_datalist',
        );
        $vce->remove_datalist($attributes);
    }

    /**
     *
     */
    public function as_content($each_component, $vce) {

    	if (isset($vce->user->user_id)) {
			header('Location: ' . $vce->site_url);
			exit;
    	}

		// check for reset token on url
		if (isset($vce->query_string)) {
			$token_key = json_decode($vce->query_string, true)['token'];
			if (isset($token_key)) {
				$token_list = $vce->get_datalist_items(array('datalist' => 'password_reset_datalist'));                  
				foreach ($token_list['items'] as $item) {
						
					// valid for 30 minutes
					if (!isset($item['expires']) || $item['expires'] < time()) {
						// expired / garbage collection
						$attributes = array(
						'item_id' =>  $item['item_id'],
						'datalist_id' => $token_list['datalist_id']
						);
						$vce->remove_datalist($attributes);
						continue;
					}
					
					if (isset($item['token']) && isset($item['user_id']) &&  $item['token'] == $token_key) {
					
						// get info for current user
						$user_info = $vce->user->get_users(array('user_ids' => $item['user_id']));
						
						// nice username
						$user_name = $user_info[0]->first_name . ' ' . $user_info[0]->last_name;

						// add javascript to page
						$vce->site->add_script(dirname(__FILE__) . '/js/script.js');

						// the instructions to pass through the form with specifics
						$dossier = array(
							'type' => 'ResetPassword',
							'procedure' => 'reset_password',
							'user_id' => $user_info[0]->user_id,
							'item_id' =>  $item['item_id'],
							'datalist_id' => $token_list['datalist_id']
						);

						// add dossier, which is an encrypted json object of details uses in the form
						$dossier_for_password = $vce->generate_dossier($dossier);

						$content = <<<EOF
<p>
{$this->lang('Hello')} $user_name, {$this->lang('please enter your new password')}:<br>
{$this->lang('')}
</p>{$this->lang('passwords_must_be_least_chars')}
 
<form id="password" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_password">
EOF;

			$input = array(
			'type' => 'password',
			'name' => 'password',
			'class' => 'password-input',
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
				'placeholder' => 'Enter Your New Password'
			)
			);
		
			$content .= $vce->content->create_input($input,'Enter your new password','Enter password');


			$input = array(
			'type' => 'password',
			'name' => 'password2',
			'class' => 'password-input',
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
				'placeholder' => 'Retype Your New Password'
			)
			);
		
			$content .= $vce->content->create_input($input,'Confirm your new password','Confirm password');


$content .= <<<EOF
<input type="submit" value="{$this->lang('Change Password')}">
<label class="ignore" style="color:#666;"><input class="show-password-input" type="checkbox" name="show-password"> Show Password</label>
</form>
EOF;


			$add_content = $vce->content->accordion('Reset Your Password', $content, true, true);

			// add content
			$vce->content->add('main', $add_content);
					} 
				}
				
				if (!isset($content)) {
				
					// token expired message
					$vce->content->add('main', '<div class="form-message form-error">Your reset password token has expired! &nbsp;&nbsp; <a class="link-button" href="' . $vce->site_url . '/' . $vce->requested_url . '">Try Again</a></div>');                    
				
				}
			}
		} else {

			// add javascript to page
			$vce->site->add_script(dirname(__FILE__) . '/js/script.js');

			// the instructions to pass through the form with specifics
			$dossier = array(
				'type' => 'ResetPassword',
				'procedure' => 'reset',
				'requested_url' => rtrim($vce->requested_url, '/'),
			);

			// add dossier, which is an encrypted json object of details uses in the form
			$dossier_for_reset = $vce->generate_dossier($dossier);

			$site_url = $vce->site->site_url;

			$content = <<<EOF
<p>{$this->lang('forgot_your_password')}</p>
<form id="register" class="asynchronous-form" method="post" action="$vce->input_path" autocomplete="off">
<input type="hidden" name="dossier" value="$dossier_for_reset">
EOF;

			$input = array(
			'type' => 'text',
			'name' => 'email',
			'data' => array(
				'autocapitalize' => 'none',
				'tag' => 'required',
				'placeholder' => $this->lang('Enter Your Email Address')
			)
			);
		
			$content .= $vce->content->create_input($input,'Email','Enter Your Email');

			$content .= <<<EOF
<input type="submit" value="{$this->lang('Send')}">
<a class="link-button" href="$site_url">{$this->lang('Cancel')}</a>
</form>
EOF;

			$add_content = $vce->content->accordion('Reset Your Password', $content, true, true);

			// add content
			$vce->content->add('main', $add_content);
		
        }

    }

    /**
     * reset password form submitted
     */
    public function reset($input) {
    
        $vce = $this->vce;

        // call to function to get id
        // $user_id = $vce->user->find_id_by_email($input['email']);
        
		// validate and check for pseudonym
		$user_validated = $vce->user->email_resolver($input['email']);

        if (empty($user_validated)) {
            echo json_encode(array('response' => 'error', 'message' => 'Email not found'));
            return;
        }

		$user_id = $user_validated['user_id'];
        $user_info = $vce->user->get_users(array('user_ids' => $user_id));

        // use email from database
        $user_email = $user_info[0]->email;
        
        // create a lowercase version to check against
		$input_email = strtolower($input['email']);

        $class_name = get_class();

        if (isset($vce->site->$class_name)) {
            $value = $vce->site->$class_name;
            $minutia = $class_name . '_minutia';
            $vector = $vce->site->$minutia;
            $config = json_decode($vce->site->decryption($value, $vector), true);
        }
        
        // anonymous function to generate token
		$random_token = function($token = null) use (&$random_token) {
			$charset = "0123456789abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ";
			$newchar = substr($charset, mt_rand(0, (strlen($charset) - 1)), 1);
			if (strlen($token) == 32) {
				return $token;
			}
			return $random_token($token . $newchar);
		};
        
        $token = $random_token();
        
        // create temporary link
        $link = $vce->site->site_url . '/' . $input['requested_url'] . '?token=' . $token;

        // save in datalist
        $token_list = $vce->get_datalist(array('datalist' => 'password_reset_datalist'));
        $datalist_id = current($token_list)['datalist_id'];
        $attributes = array(
            'datalist_id' => $datalist_id,
            'items' => array(array(
                'expires' => time() + 1800,
                'token' => $token,
                'user_id' => $user_id
            )),
        );
        $vce->insert_datalist_items($attributes);

        $reset_email_from = $vce->site->site_email;
        $reset_email_subject = isset($config['reset_email_subject']) ? $config['reset_email_subject'] : 'Password Reset Requested';
        $reset_email_body = isset($config['reset_email_body']) ? $config['reset_email_body'] : 'Hello {user}, here is the link to reset your password: {link}';
        $reset_email_body = str_replace('{user}', $user_info[0]->first_name . ' ' . $user_info[0]->last_name, $reset_email_body);
        $reset_email_body = str_replace('{link}', $link, $reset_email_body);

        // send email notification
        $attributes = array(
            'html' => true,
            'from' => array($reset_email_from, $vce->site->site_title),
            'to' => array($user_email, $user_info[0]->first_name . ' ' . $user_info[0]->last_name),
            'reply' => $reset_email_from,
            'subject' => $reset_email_subject,
            'message' => $reset_email_body,
        );

        $vce->mail($attributes);
        
        // pseudonym email
        if ($user_email != $input_email) {
        
       		$message = null;
        
        	$message .= '<div>' . $input_email . ' is linked to your ' . $user_email . ' account</div>';
        	$message .= '<div>&nbsp;</div>';
        	$message .= '<div>A password reset email has been sent to ' . $user_email . '</div>';
        	$message .= '<div>&nbsp;</div>';
			$message .= '<div><a class="link-button" href="' .  $vce->site->site_url . '">Click here to continue</a></div>'; 
			
			echo json_encode(array('response' => 'success', 'message' => $message));
			return;

		}

        echo json_encode(array('response' => 'success', 'message' => 'A password reset email has been sent to ' . $user_email, 'action' => 'reload'));
        return;

    }


    public function reset_password($input) {
    
		$vce = $this->vce;
		
		$item_id =  $input['item_id'];
		$datalist_id = $input['datalist_id'];
		
		// make sure these do not end up in user meta_data
		unset($input['item_id'],$input['datalist_id']);
		
		// call to user->update
    	$response = $vce->user->update($input);
    	
    	if ($response['response'] == 'error') {
			echo json_encode(array('response' => 'error', 'message' => $response['message']));
       	 	return;
    	}
        
        // delete datalist item
		$attributes = array(
		'item_id' =>  $item_id,
		'datalist_id' => $datalist_id
		);
		$vce->remove_datalist($attributes);
		
		$user_info = $vce->user->find_users($input['user_id']);
		
        $class_name = get_class();

        if (isset($vce->site->$class_name)) {
            $value = $vce->site->$class_name;
            $minutia = $class_name . '_minutia';
            $vector = $vce->site->$minutia;
            $config = json_decode($vce->site->decryption($value, $vector), true);
        }
        
        $confimation_email_from = $vce->site->site_email;
        $confimation_email_subject = isset($config['confirmation_email_subject']) ? $config['confirmation_email_subject'] : 'Password Has Been Reset';
        $confimation_email_body = isset($config['confirmation_email_body']) ? $config['confirmation_email_body'] : 'Hello {user}, your password has been reset';
        $confimation_email_body = str_replace('{user}', $user_info[0]->first_name . ' ' . $user_info[0]->last_name, $confimation_email_body);

        // send email notification
        $attributes = array(
            'html' => true,
            'from' => array($confimation_email_from, $vce->site->site_title),
            'to' => array($user_info[0]->email, $user_info[0]->first_name . ' ' . $user_info[0]->last_name),
            'reply' => $confimation_email_from,
            'subject' => $confimation_email_subject,
            'message' => $confimation_email_body,
        );

        $vce->mail($attributes);
        
        echo json_encode(array('response' => 'success', 'message' => 'Your password has been reset. Please Login!', 'action' => 'reload', 'url' => $vce->site->site_url));
        return;
    }


    /**
     * add config info for this component
     */
    public function component_configuration() {

        global $vce;

        $elements = null;

        $class_name = get_class();

        if (isset($vce->site->$class_name)) {
            $value = $vce->site->$class_name;
            $minutia = $class_name . '_minutia';
            $vector = $vce->site->$minutia;
            $config = json_decode($vce->site->decryption($value, $vector), true);
        }

        // email notifications

        $reset_email_subject = isset($config['reset_email_subject']) ? $config['reset_email_subject'] : null;
        $reset_email_body = isset($config['reset_email_body']) ? str_replace(array('\r\n'), PHP_EOL, stripcslashes($config['reset_email_body'])) : null;
        $confirmation_email_subject = isset($config['confirmation_email_subject']) ? $config['confirmation_email_subject'] : null;
        $confirmation_email_body = isset($config['confirmation_email_body']) ? str_replace(array('\r\n'), PHP_EOL, stripcslashes($config['confirmation_email_body'])) : null;

        $elements .= <<<EOF
<div class="clickbar-container">
	<div class="clickbar-content clickbar-open">
		<p>{$this->lang('Password Reset Request Email')}</p>
		<label>
			<input type="text" name="reset_email_subject" value="$reset_email_subject" tag="required" autocomplete="off">
			<div class="label-text">
				<div class="label-message">{$this->lang('Reset Email Subject')}</div>
				<div class="label-error">{$this->lang('Enter Reset Email Subject')}</div>
			</div>
		</label>
		<p>
			<div>{user} => {$this->lang('Name of user')}</div>
			<div>{link} => {$this->lang('Reset password link')}</div>
		</p>
		<label>
			<textarea name="reset_email_body" class="textarea-input" tag="required">$reset_email_body</textarea>
			<div class="label-text">
				<div class="label-message">{$this->lang('Reset Email Body')}</div>
				<div class="label-error">{$this->lang('Enter Reset Email Body')}</div>
			</div>
		</label>
		<hr>
		<p>{$this->lang('Password Reset Confirmation Email')}</p>
		<label>
			<input type="text" name="confirmation_email_subject" value="$confirmation_email_subject" tag="required" autocomplete="off">
			<div class="label-text">
				<div class="label-message">{$this->lang('Confirmation Email Subject')}</div>
				<div class="label-error">{$this->lang('Enter Confirmation Email Subject')}</div>
			</div>
		</label>
		<p>
			<div>{user} => {$this->lang('Name of user')}</div>
		</p>
		<label>
			<textarea name="confirmation_email_body" class="textarea-input" tag="required">$confirmation_email_body</textarea>
			<div class="label-text">
				<div class="label-message">{$this->lang('Confirmation Email Body')}</div>
				<div class="label-error">{$this->lang('Enter Confirmation Email Body')}</div>
			</div>
		</label>
	</div>
	<div class="clickbar-title"><span>{$this->lang('Email Notification')}</span></div>
</div>
EOF;

        return $elements;

    }
    
}
