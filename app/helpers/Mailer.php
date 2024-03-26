<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

class Mailer
{
    public static PHPMailer $mail;
    
    public static function sendCode(array $to): bool
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        self::$mail = new PHPMailer(true);

        try {
            //Server settings
            self::$mail->SMTPDebug = 0;                                 // Enable verbose debug output
            self::$mail->isSMTP();                                      // Set mailer to use SMTP

            self::$mail->Host = $_ENV['SMTP_HOST'];                     // Specify main and backup SMTP servers
            self::$mail->Username = $_ENV['SMTP_USERNAME'];           // SMTP username
            self::$mail->Password = $_ENV['SMTP_PASSWORD'];              // SMTP password
            
            self::$mail->SMTPAuth = true;                               // Enable SMTP authentication
            self::$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            self::$mail->Port = 587;                                    // TCP port to connect to
        
            //Recipients
            self::$mail->setFrom($_ENV['SMTP_USER_MAIL'], $_ENV['SMTP_USER_NICKNAME']);
            self::$mail->addAddress($to['email'], $to['name']);     // Add a recipient
        
            self::$mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ];

            // Content
            self::$mail->isHTML(true);                                  // Set email format to HTML
            self::$mail->ContentType = 'text/html; charset=UTF-8';
            self::$mail->Subject = 'SEU CÓDIGO CHEGOU!';

            $htmlFile = file_get_contents('../../resources/view/email/index.html');
            $htmlFile = str_replace('{{NAME}}', $to['name'], $htmlFile);
            $htmlFile = str_replace('{{CODE}}', 12345, $htmlFile);

            self::$mail->AddEmbeddedImage("../../resources/view/email/images/logo2.png", "logo2");
            self::$mail->AddEmbeddedImage("../../resources/view/email/images/logo1.png", "logo");
            self::$mail->AddEmbeddedImage("../../resources/view/email/images/lock.png", "lock");
            self::$mail->AddEmbeddedImage("../../resources/view/email/images/instagram-rounded-gray.png", "instagram-rounded-gray");
            self::$mail->AddEmbeddedImage("../../resources/view/email/images/whatsapp-rounded-gray.png", "whatsapp-rounded-gray");
        
            self::$mail->send();
            
            return true;
        } catch (Exception $e) {
            //echo "Message could not be sent. Mailer Error: " . self::$mail->ErrorInfo;
            return false;
        }
    }

}
