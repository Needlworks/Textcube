<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function encodeMail($str) {
	return '=?utf-8?b?' . base64_encode($str) . '?=';
}

function sendEmail($senderName, $senderEmail, $name, $email, $subject, $message ) {
	include_once( ROOT."/library/contrib/phpmailer/class.phpmailer.php" );
	$mail = new PHPMailer();
	$mail->SetLanguage( 'en', ROOT."/library/contrib/phpmailer/language/" );
	$mail->IsHTML(true);
	$mail->CharSet  = 'utf-8';
	$mail->From     = $senderEmail;
	$mail->FromName = $senderName;
	$mail->Subject  = $subject;
	$mail->Body     = $message;
	$mail->AltBody  = 'To view this email message, open the email with html enabled mailer.';
	$mail->AddAddress( $email, $name );

	if( !getServiceSetting( 'useCustomSMTP', 0 ) ) {
		$mail->IsMail();
	} else {
		$mail->IsSMTP();
		$mail->Host = getServiceSetting( 'smtpHost', '127.0.0.1' );
		$mail->Port = getServiceSetting( 'smtpPort', 25 );
	}

	ob_start();
	$ret = $mail->Send();
	ob_clean();

	if( !$ret ) {
		return array( false, $mail->ErrorInfo );
	}
	return true;
}

?>
