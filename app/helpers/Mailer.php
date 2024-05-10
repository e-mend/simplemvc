<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;
use App\Helpers\Secure;

class Mailer
{
    public static PHPMailer $mail;

    private static $types = [
        'code',
        'change-password',
        'new-user'
    ];
    
    public static function sendCode(array $to)
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $secure = Secure::getInstance();
        $secure->generatePin();

        self::$mail = new PHPMailer(true);

        try {
            //Server settings
            self::$mail->CharSet = 'UTF-8';
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
            self::$mail->Subject = 'Seu codigo de verificação é: ' . $secure->getPin();

            $htmlFile = file_get_contents('../resources/view/email/email.html');
            $htmlFile = str_replace('{{NAME}}', $to['name'], $htmlFile);
            $htmlFile = str_replace('{{CODE}}', $secure->getPin(), $htmlFile);

            self::$mail->AddEmbeddedImage("../resources/view/email/images/logo2.png", "logo2");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/logo1.png", "logo");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/lock.png", "lock");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/instagram-rounded-gray.png", "instagram-rounded-gray");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/whatsapp-rounded-gray.png", "whatsapp-rounded-gray");
        
            self::$mail->msgHTML($htmlFile);
            self::$mail->AltBody = 'O Código é: ' . $secure->getPin();

            self::$mail->send();

            return true;
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: " . self::$mail->ErrorInfo;
            //return false;
        }
    }

    public static function sendPassword(array $to)
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $secure = Secure::getInstance();
        $secure->generatePasswordToken($to['for']);

        self::$mail = new PHPMailer(true);

        try {
            //Server settings
            self::$mail->CharSet = 'UTF-8';
            self::$mail->SMTPDebug = 0;                                 // Enable verbose debug output
            self::$mail->isSMTP();                                      // Set mailer to use SMTP

            self::$mail->Host = $_ENV['SMTP_HOST'];                     // Specify main and backup SMTP servers
            self::$mail->Username = $_ENV['SMTP_USERNAME'];             // SMTP username
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
            self::$mail->Subject = 'Pedido de alteração de senha';

            $link = $_ENV['BASE_URL'] . '/password?token=' . $secure->getPasswordToken();

            $htmlFile = file_get_contents('../resources/view/email/password.html');
            $htmlFile = str_replace('{{NAME}}', $to['name'], $htmlFile);
            $htmlFile = str_replace('{{LINK}}', $link, $htmlFile);

            self::$mail->AddEmbeddedImage("../resources/view/email/images/logo2.png", "logo2");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/logo1.png", "logo");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/lock.png", "lock");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/instagram-rounded-gray.png", "instagram-rounded-gray");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/whatsapp-rounded-gray.png", "whatsapp-rounded-gray");
        
            self::$mail->msgHTML($htmlFile);
            self::$mail->AltBody = 'O link é: ' . $link;

            self::$mail->send();

            return true;
        } catch (Exception $e) {
            //return "Message could not be sent. Mailer Error: " . self::$mail->ErrorInfo;
            return false;
        }
    }

    public static function sendLink(array $to)
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $secure = Secure::getInstance();
        $secure->generatePasswordToken($to['for']);

        self::$mail = new PHPMailer(true);

        try {
            //Server settings
            self::$mail->CharSet = 'UTF-8';
            self::$mail->SMTPDebug = 0;                                 // Enable verbose debug output
            self::$mail->isSMTP();                                      // Set mailer to use SMTP

            self::$mail->Host = $_ENV['SMTP_HOST'];                     // Specify main and backup SMTP servers
            self::$mail->Username = $_ENV['SMTP_USERNAME'];             // SMTP username
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
            self::$mail->Subject = 'Pedido de alteração de senha';

            $link = $_ENV['BASE_URL'] . '/password?token=' . $secure->getPasswordToken();

            $htmlFile = file_get_contents('../resources/view/email/password.html');
            $htmlFile = str_replace('{{NAME}}', $to['name'], $htmlFile);
            $htmlFile = str_replace('{{LINK}}', $link, $htmlFile);

            self::$mail->AddEmbeddedImage("../resources/view/email/images/logo2.png", "logo2");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/logo1.png", "logo");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/lock.png", "lock");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/instagram-rounded-gray.png", "instagram-rounded-gray");
            self::$mail->AddEmbeddedImage("../resources/view/email/images/whatsapp-rounded-gray.png", "whatsapp-rounded-gray");
        
            self::$mail->msgHTML($htmlFile);
            self::$mail->AltBody = 'O link é: ' . $link;

            self::$mail->send();

            return true;
        } catch (Exception $e) {
            //return "Message could not be sent. Mailer Error: " . self::$mail->ErrorInfo;
            return false;
        }
    }

}
