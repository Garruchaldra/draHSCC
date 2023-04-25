<?php

class PHPMailerSixComponent extends Component {

	/**
	 * basic info about the component
	 */
	public function component_info() {
		return array(
			'name' => 'PHP Six Mailer',
			'description' => 'PHP email creation and transport Class',
			'category' => 'mail'
		);
	}

	/**
	 * things to do when this component is preloaded
	 */
	public function preload_component() {
		
		$content_hook = array (
		'site_mail_transport' => 'PHPMailerSixComponent::mail_transport'
		);

		return $content_hook;

	}

	/**
	 * PHPMailer
	 * https://github.com/PHPMailer/PHPMailer
	 */
	public static function mail_transport($vce, $attributes) {
	
		// old way 5
		// load PHPMailer
		// require_once(dirname(__FILE__) . '/phpmailer/PHPMailerAutoload.php');
		
		// new way
		// they say these are now required.
		// use PHPMailer\PHPMailer\PHPMailer;
		// use PHPMailer\PHPMailer\SMTP;
		// use PHPMailer\PHPMailer\Exception;
		// 
		// require 'path/to/PHPMailer/src/Exception.php';
		// require 'path/to/PHPMailer/src/PHPMailer.php';
		// require 'path/to/PHPMailer/src/SMTP.php';
		
		// instead of using the autoloader
		require_once(dirname(__FILE__) . '/phpmailer/Exception.php');
		require_once(dirname(__FILE__) . '/phpmailer/PHPMailer.php');
		require_once(dirname(__FILE__) . '/phpmailer/SMTP.php');

		if (isset($vce->site->PHPMailerComponent)) {
			// get config info for this component
			$config = $vce->site->PHPMailerComponent;
			$vector = $vce->site->PHPMailerComponent_minutia;
			$smtp = json_decode($vce->site->decryption($config,$vector));
		}
		
		/*
		//Import PHPMailer classes into the global namespace
		//These must be at the top of your script, not inside a function
		use PHPMailer\PHPMailer\PHPMailer;
		use PHPMailer\PHPMailer\SMTP;
		use PHPMailer\PHPMailer\Exception;

		//Load Composer's autoloader
		require 'vendor/autoload.php';

		//Instantiation and passing `true` enables exceptions
		$mail = new PHPMailer(true);

		try {
			//Server settings
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
			$mail->isSMTP();                                            //Send using SMTP
			$mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
			$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
			$mail->Username   = 'user@example.com';                     //SMTP username
			$mail->Password   = 'secret';                               //SMTP password
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
			$mail->Port       = 587;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

			//Recipients
			$mail->setFrom('from@example.com', 'Mailer');
			$mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
			$mail->addAddress('ellen@example.com');               //Name is optional
			$mail->addReplyTo('info@example.com', 'Information');
			$mail->addCC('cc@example.com');
			$mail->addBCC('bcc@example.com');

			//Attachments
			$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
			$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

			//Content
			$mail->isHTML(true);                                  //Set email format to HTML
			$mail->Subject = 'Here is the subject';
			$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			$mail->send();
			echo 'Message has been sent';
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}

*/

		$mail = new PHPMailer(true);
		
		if (!empty($smtp)) {
			$mail->IsSMTP(); // Set mailer to use SMTP
			$mail->Host = $smtp->host; // Specify main and backup SMTP servers
			$mail->SMTPAuth = true; // Enable SMTP authentication
			$mail->Username = $smtp->username; // SMTP username
			$mail->Password = $smtp->password; // SMTP password
			$mail->SMTPSecure = $smtp->encryption; // Enable TLS encryption, `tls` or `ssl`  accepted
			$mail->Port = $smtp->port; // TCP port to connect to
			// $mail->SMTPDebug = 3;
		}
		
		/*
		$mail->isSMTP();
		$mail->Host = 'relay-hosting.secureserver.net';
		$mail->Port = 25;
		$mail->SMTPAuth = false;
		$mail->SMTPSecure = false;
		*/

		// array to translate methods from vce to PHPMailer
		$translate = array(
		'from' => 'setFrom',
		'to' => 'addAddress',
		'reply' => 'addReplyTo',
		'cc' => 'addCC',
		'bcc' => 'addBCC',
		'subject' => 'Subject',
		'message' => 'Body'
		);
		
		// send as html email
		if (isset($attributes['html']) && $attributes['html'] == true) {
			$mail->isHTML(true);
			// clean-up any html email copy
			$attributes['message'] = html_entity_decode(stripcslashes($attributes['message']));
		}		
		
	
		foreach ($attributes as $key=>$value) {
			if (isset($translate[$key])) {
				$method = $translate[$key];
			} else {
				continue;
			}
			if (is_array($value)) {
				$each_values = array_values($value);
				if (is_array($each_values[0])) {
					foreach ($each_values as $sub_key=>$sub_value) {
						$address = isset($sub_value[0]) ? $sub_value[0] : null;
						$name = isset($sub_value[1]) ? $sub_value[1] : $address;
						// call
						$mail->$method($address, $name);
					}
				} else {
					$address = isset($each_values[0]) ? $each_values[0] : null;
					$name = isset($each_values[1]) ? $each_values[1] : $address;
					// call
					$mail->$method($address, $name);
				}
			} else {
				$mail->$method = trim($value);
			}
		}
		

		
 		if (!$mail->send()) {
 			echo 'PHPMailer error: ' . $mail->ErrorInfo;
			return false;
 		} else {
 			return true;
 		}

	}

	/**
	 * return form fields
	 */
	public function component_configuration() {
	
		global $vce;
		$config = $vce->site->PHPMailerComponent;
		$vector = $vce->site->PHPMailerComponent_minutia;
		$smtp = json_decode($vce->site->decryption($config,$vector), true);
		
		$host = isset($smtp['host']) ? $smtp['host'] : '';
		$port = isset($smtp['port']) ? $smtp['port'] : '';
		$encryption = isset($smtp['encryption']) ? $smtp['encryption'] : '';
		$username = isset($smtp['username']) ? $smtp['username'] : '';
		$password = isset($smtp['password']) ? $smtp['password'] : '';
		
$elements = <<<EOF
<label>
<input type="text" name="host" value="$host" autocomplete="off">
<div class="label-text">
<div class="label-message">Host</div>
<div class="label-error">Enter a Host</div>
</div>
</label>
<label>
<input type="text" name="port" value="$port" autocomplete="off">
<div class="label-text">
<div class="label-message">Port</div>
<div class="label-error">Enter a Port</div>
</div>
</label>
<label>
<input type="text" name="encryption" value="$encryption" autocomplete="off">
<div class="label-text">
<div class="label-message">Encryption (ssl or tls)</div>
<div class="label-error">Enter Encryption</div>
</div>
</label>
<label>
<input type="text" name="username" value="$username" autocomplete="off">
<div class="label-text">
<div class="label-message">Username</div>
<div class="label-error">Enter Username</div>
</div>
</label>
<label>
<input type="password" name="password" value="$password" autocomplete="off">
<div class="label-text">
<div class="label-message">Password</div>
<div class="label-error">Enter Password</div>
</div>
</label>
EOF;

		return $elements;

	}

	
	/**
	 * hide this component from being added to a recipe
	 */
	public function recipe_fields($recipe) {
		return false;
	}

}