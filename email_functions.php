<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// GAMITIN ANG __DIR__ PARA HINDI MALIGAW ANG SYSTEM KAHIT NASA ADMIN FOLDER KA
require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';

function sendResortEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings for Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mcpebrinehq@gmail.com';
        
        // PAALALA: Siguraduhin na ito ay 'App Password' mula sa Google Account settings
        // Tinanggal ko na ang space dito para sure
        $mail->Password   = 'dtdxxxhcmauoeebt'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Disable debug for production (para hindi mag-error logs sa screen)
        $mail->SMTPDebug = 0; 
        
        // Recipients
        $mail->setFrom('mcpebrinehq@gmail.com', 'Heart Of D Ocean Resort');
        $mail->addAddress($to);
        $mail->addReplyTo('heartofdocean2005@yahoo.com', 'Heart Of D Ocean Resort');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $message)); // Clean text version
        
        $mail->send();
        
        // Log success (Optional)
        // file_put_contents(__DIR__ . '/email_log.txt', "[" . date('Y-m-d H:i:s') . "] SENT to $to\n", FILE_APPEND | LOCK_EX);
        return true;
        
    } catch (Exception $e) {
        // Log detailed error
        $errorMsg = "[" . date('Y-m-d H:i:s') . "] ERROR: " . $mail->ErrorInfo . "\n";
        file_put_contents(__DIR__ . '/email_log.txt', $errorMsg, FILE_APPEND | LOCK_EX);
        return false;
    }
}
?>