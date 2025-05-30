<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'assets/vendor/phpmailer/phpmailer/src/Exception.php';
require 'assets/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'assets/vendor/phpmailer/phpmailer/src/SMTP.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) exit("No input");

$mail = new PHPMailer(true);
$customer = new PHPMailer(true);

try {
    // Common SMTP settings
    foreach ([$mail, $customer] as $m) {
        $m->isSMTP();
        $m->Host = 'smtp.gmail.com';
        $m->SMTPAuth = true;
        $m->Username = 'companycloudwisel@gmail.com';
        $m->Password = 'ikzy nmil ltnw epiu'; // App password
        $m->SMTPSecure = 'ssl';
        $m->Port = 465;

        // From alias (inbox display name)
        $m->setFrom('support@cloudwisel.com', 'CloudWisel');
    }

    // --- 1. ADMIN EMAIL ---
    $mail->addAddress('support@cloudwisel.com');
    $mail->isHTML(true);
    $mail->Subject = 'New Purchase - ' . $data['plan'];
    $mail->Body = "
        <h2>💳 New Website Plan Purchase</h2>
        <p><strong>Name:</strong> {$data['name']}</p>
        <p><strong>Email:</strong> {$data['email']}</p>
        <p><strong>Phone:</strong> {$data['contact']}</p>
        <p><strong>Plan:</strong> {$data['plan']}</p>
        <p><strong>Payment ID:</strong> {$data['payment_id']}</p>
        <hr>
        <p style='font-size: 13px; color: #888;'>CloudWisel Admin Notification</p>
    ";
    $mail->send();

    // --- 2. CUSTOMER EMAIL ---
    $customer->addAddress($data['email']);
    $customer->isHTML(true);
    $customer->Subject = 'Thank You for Your Purchase!';
    $customer->Body = "
        <h3>🎉 Thank you for your purchase, {$data['name']}!</h3>
        <p>We're thrilled to help you grow your business with the <strong>{$data['plan']}</strong> plan.</p>
        <p><strong>Payment ID:</strong> {$data['payment_id']}</p>
        <p>If you have any questions, feel free to contact us at <a href='mailto:support@cloudwisel.com'>support@cloudwisel.com</a>.</p>
        <br>
        <small style='color: #555;'>— CloudWisel Team</small>
    ";
    $customer->send();

    echo "OK";

} catch (Exception $e) {
    http_response_code(500);
    echo "Mailer Error: {$mail->ErrorInfo}";
}
