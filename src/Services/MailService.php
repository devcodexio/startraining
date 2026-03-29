<?php

namespace App\Services;

require_once __DIR__ . '/../libs/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer.php';
require_once __DIR__ . '/../libs/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailService {
    
    /**
     * Envía un correo electrónico usando Gmail SMTP.
     * Los datos se obtienen de las variables de entorno: GMAIL_USER, GMAIL_PASS
     */
    public static function sendGmail($to, $subject, $message, $toName = '') {
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['GMAIL_USER'] ?? '';
            $mail->Password   = $_ENV['GMAIL_PASS'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Destinatarios
            $mail->setFrom($_ENV['GMAIL_USER'] ?? '', 'StarTraining Platform');
            $mail->addAddress($to, $toName);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = nl2br($message);
            $mail->AltBody = strip_tags($message);

            $mail->send();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => "Error de PHPMailer: {$mail->ErrorInfo}"];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => "Error general: {$e->getMessage()}"];
        }
    }
}
