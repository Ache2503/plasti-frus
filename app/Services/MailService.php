<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        $host = $_ENV['MAIL_HOST'] ?? 'localhost';
        $port = (int)($_ENV['MAIL_PORT'] ?? 25);
        $username = $_ENV['MAIL_USERNAME'] ?? '';
        $password = $_ENV['MAIL_PASSWORD'] ?? '';
        $encryption = $_ENV['MAIL_ENCRYPTION'] ?? '';

        if ($username && $password) {
            $this->mailer->isSMTP();
            $this->mailer->Host = $host;
            $this->mailer->Port = $port;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $username;
            $this->mailer->Password = $password;
            if ($encryption) {
                $this->mailer->SMTPSecure = $encryption;
            }
        }

        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@plastifrus.com',
            $_ENV['MAIL_FROM_NAME'] ?? 'Plasti Frus'
        );
    }

    public function send(string $to, string $subject, string $body, string $altBody = ''): bool
    {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);
            $this->mailer->send();
            $this->mailer->clearAddresses();
            return true;
        } catch (Exception $e) {
            error_log("Mail error: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}
