<?php

require_once '../lib/phpmailer/PHPMailerAutoload.php';
require_once '../lib/phpmailer/class.smtp.php';

$mail = new PHPMailer;

$mail->SMTPDebug = 1;

$mail->isSMTP();
$mail->CharSet = 'UTF-8';
$mail->Host = 'mail.assistemas.com.br';
$mail->SMTPAuth = true;
$mail->Username = 'webmaster@assistemas.com.br';
$mail->Password = 'TNTVvlO0Wu';
$mail->SMTPSecure = 'ssl';
$mail->port = 465;

$mail->setFrom('postmaster.assistemas@gmail.com', 'Inscrição do curso no Aulas a Distância.');
$mail->AddCC('postmaster.assistemas@gmail.com', 'Postmaster AS Sistemas');
$mail->isHTML(true);
