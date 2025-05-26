<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../config.php'; 
require '../assets/vendor/phpmailer/phpmailer/src/Exception.php';
require '../assets/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../assets/vendor/phpmailer/phpmailer/src/SMTP.php';

// 1. Validate email input
if (empty($_POST['email'])) {
    exit('Error: No email address provided.');
}

$email = $_POST['email'];

// 2. Save to Database
try {
    // Replace with your DB credentials
    // its for the newsettler email connection with  data base

    $pdo = new PDO('mysql:host=localhost;dbname=cloud_wisel;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email) VALUES (:email)");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
} catch (PDOException $e) {
    exit('Error: Database error - ' . $e->getMessage());
}

// 3. Send Emails using PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP Config
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'companycloudwisel@gmail.com';
    $mail->Password   = 'ikzy nmil ltnw epiu'; // App Password
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    $mail->setFrom('noreply@cloudwisel.com', 'CloudWisel Newsettler'); // using alias

    // Email to Admin
    $mail->addAddress('noreply@cloudwisel.com');
    $mail->Subject = 'New Newsletter Subscriber';
    $mail->Body    = 'New subscription from: ' . $email;
    $mail->send();

    // Email to Subscriber
    $mail->clearAddresses();
    $mail->addAddress($email);
    $mail->Subject = 'Thank you for subscribing!';
    $mail->isHTML(true);
    $mail->Body    = "
        <h3>Thank you for subscribing to CloudWisel!</h3>
        <p>We'll keep you updated with the latest news, offers, and updates.</p>
        <p>If you didn't subscribe, you can ignore this email.</p>
        <br>
        <small>- CloudWisel Team</small>
    ";
    $mail->send();

    echo 'OK';

} catch (Exception $e) {
    echo "Error: Could not send email. Mailer Error: {$mail->ErrorInfo}";
}
