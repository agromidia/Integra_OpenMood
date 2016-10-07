<?php

require_once '../lib/phpmailer/PHPMailerAutoload.php';
require_once '../lib/phpmailer/class.smtp.php';

$mail = new PHPMailer;

$mail->SMTPDebug = 3;

$mail->isSMTP();
$mail->isHTML(true);
$mail->CharSet = 'UTF-8';
$mail->SMTPAuth = true;
$mail->Host = 'smtp.nutrimidia.com.br';
$mail->Username = 'no-reply@nutrimidia.com.br';
$mail->Password = 'KhN0x6j0';
$mail->SMTPSecure = 'ssl';
$mail->port = 465;
$mail->From = 'postmaster.assistemas@gmail.com';
$mail->FromName = 'Inscrição do curso no Aulas a Distância.';
$mail->AddCC('postmaster.assistemas@gmail.com', 'Postmaster AS Sistemas');
