<?php
/**
 * mailer.php
 *
 * This file handles secure mail transport using the Swiftmailer library with Google reCAPTCHA integration.
 *
 * @author Rochelle Lewis <rlewis37@cnm.edu>
 *
 **/

// require all composer dependencies
require_once(dirname(__DIR__, 1) . "/vendor/autoload.php");

// require mail-config.php
require_once ("mail-config.php");

use\SendGrid\Mail;
$sendgrid = new \SendGrid($sendGridSecret);

// verify user's reCAPTCHA input
$recaptcha = new \ReCaptcha\ReCaptcha($secret);
$resp = $recaptcha->verify($_POST["g-recaptcha-response"], $_SERVER["REMOTE_ADDR"]);

try {
	// if there's a reCAPTCHA error, throw an exception
		if (!$resp->isSuccess()) {
				throw(new Exception("reCAPTCHA error!"));
		}

	/**
	 * Sanitize the inputs from the form: name email subject and message.
	 * This assumes jQuery (NOT Angular!) will be AJAX submitting the form,
	 * so we're using the $_POST superglobal.
	 *
	 **/
	$firstName = filter_input(INPUT_POST, "First name", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$lastName = filter_input(INPUT_POST, "Last name", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$emailAddress = filter_input(INPUT_POST, "Email address", FILTER_SANITIZE_EMAIL);
	$message = filter_input(INPUT_POST, "Message", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

	// create SendGrid object
	$emailObject = new Mail();

	/**
	 * Attach the sender to the message.
	 * This takes the form of an associative array where $email is the key for the real name.
	 **/
	$emailObject->setFrom($email, $firstName, $lastName);

	/**
	 * Attach the recipients to the message.
	 * $MAIL_RECIPIENTS is set in mail-config.php
	 **/
	$recipients = $MAIL_RECIPIENTS;
	$emailObject->addTo("sunkaskasingers@yahoo.com", "Sunka Ska");

	// attach the message for the email
	$emailObject->addMessage("text/plain", $message);

	/**
	 *  Using the SendGrid object from above call the send method and use the emailObject as an argument.
	 **/
	$response = $sendgrid->send($emailObject);

	// report a successful send!
	echo "<div class=\"alert alert-success\" role=\"alert\">Email sent successfully.</div>";
} catch(\Exception $exception) {
	echo "<div class= \"alert alert-danger\" role=\"alert\"><strong>Oops!</strong> Unable to send email: " . $exception->getMessage() . "</div>";
}
