<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

session_start();
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_email'] = $_POST['email'];

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ginoreyes1151@gmail.com'; // Your Gmail
    $mail->Password = '';     // App password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('yourgmail@gmail.com', 'Eye Assist');
    $mail->addAddress($_POST['email']);

    $mail->isHTML(true);
    $mail->Subject = '🔐 Your One-Time Password (OTP)';
    $mail->Body    = "<h3>Your OTP is: <b>$otp</b></h3><p>It is valid for 5 minutes.</p>";

    $mail->send();
    echo "OTP has been sent to your email!";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
