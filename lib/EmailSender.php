<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender
{
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? $this->loadConfig();
    }

    private function loadConfig(): array
    {
        $path = __DIR__ . '/../config/email.php';
        if (is_file($path)) {
            return require $path;
        }
        return [];
    }

    public function sendCertificate(string $to, string $nombre, string $pdfPath): bool
    {
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("EmailSender: Email inválido: $to");
            return false;
        }

        if (!is_file($pdfPath)) {
            error_log("EmailSender: Archivo no encontrado: $pdfPath");
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_user'] ?? '';
            $mail->Password = $this->config['smtp_pass'] ?? '';
            $mail->SMTPSecure = $this->config['smtp_secure'] ?? PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['smtp_port'] ?? 587;
            $mail->CharSet = 'UTF-8';

            // Remitente
            $mail->setFrom(
                $this->config['from_email'] ?? 'noreply@example.com',
                $this->config['from_name'] ?? 'Sistema de Certificados'
            );

            if (!empty($this->config['reply_to'])) {
                $mail->addReplyTo($this->config['reply_to']);
            }

            // Destinatario
            $mail->addAddress($to, $nombre);

            // Adjunto
            $mail->addAttachment($pdfPath, 'Certificado_Titulo.pdf');

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Certificado de Título - Liceo Bicentenario Domingo Santa María';
            $mail->Body = $this->buildEmailBody($nombre);
            $mail->AltBody = $this->buildEmailBodyPlain($nombre);

            $mail->send();
            error_log("EmailSender: Certificado enviado exitosamente a $to");
            return true;

        } catch (Exception $e) {
            error_log("EmailSender: Error al enviar correo: " . $mail->ErrorInfo);
            return false;
        }
    }

    private function buildEmailBody(string $nombre): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #092483; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Liceo Bicentenario Domingo Santa María</h2>
        </div>
        <div class="content">
            <p>Estimado/a <strong>{$nombre}</strong>,</p>
            <p>Adjunto a este correo encontrará su <strong>Certificado de Título</strong> en formato PDF.</p>
            <p>Este documento certifica la obtención de su título profesional en nuestro establecimiento.</p>
            <p>Si tiene alguna consulta, no dude en contactarnos.</p>
            <p>Saludos cordiales,<br>
            <strong>Liceo Bicentenario Domingo Santa María</strong></p>
        </div>
        <div class="footer">
            <p>Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function buildEmailBodyPlain(string $nombre): string
    {
        return <<<TEXT
Estimado/a {$nombre},

Adjunto a este correo encontrará su Certificado de Título en formato PDF.

Este documento certifica la obtención de su título profesional en nuestro establecimiento.

Si tiene alguna consulta, no dude en contactarnos.

Saludos cordiales,
Liceo Bicentenario Domingo Santa María

---
Este es un correo automático, por favor no responder.
TEXT;
    }
}
