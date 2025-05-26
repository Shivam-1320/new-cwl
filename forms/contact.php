<?php

error_log(print_r($_POST, true));


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../config.php'; // Include config
require '../assets/vendor/phpmailer/phpmailer/src/Exception.php';
require '../assets/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../assets/vendor/phpmailer/phpmailer/src/SMTP.php';

// Validate input
if (
    empty($_POST['name']) ||
    empty($_POST['email']) ||
    empty($_POST['subject']) ||
    empty($_POST['message'])
) {
    exit('Error: All fields are required.');
}

// Sanitize inputs
$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);
$subject = htmlspecialchars($_POST['subject']);
$message = nl2br(htmlspecialchars($_POST['message']));

// Save to DB
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, strip_tags($message)]);
} catch (PDOException $e) {
    exit('Error saving to DB: ' . $e->getMessage());
}

// Send Email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = EMAIL_USERNAME; // FROM config.php
    $mail->Password   = EMAIL_PASSWORD; // FROM config.php
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    
    $mail->setFrom('support@cloudwisel.com', 'CloudWisel');// alias address

    $mail->addAddress(EMAIL_TO); // Admin inbox

    $mail->isHTML(true);
    $mail->Subject = "New Contact Form Message: $subject";
    $mail->Body = "
        <strong>Name:</strong> $name<br>
        <strong>Email:</strong> $email<br>
        <strong>Subject:</strong> $subject<br><br>
        <strong>Message:</strong><br>$message
    ";
    $mail->send();

    // Confirmation email to sender
    $mail->clearAddresses();
    $mail->addAddress($email);
    $mail->Subject = "Thank you for contacting CloudWisel";
    $mail->Body = "
        <h3>Hi $name,</h3>
        <p>Thank you for your message. Here's a copy:</p>
        <blockquote>$message</blockquote>
        <p>We'll get back to you soon.</p>
        <small>- CloudWisel Team</small>
    ";
    $mail->send();

    echo 'OK';

} catch (Exception $e) {
    echo "Error: Mailer Error: {$mail->ErrorInfo}";
}
