<?php
// Pfade zu den PHPMailer-Dateien einrichten
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// E-Mail-Einstellungen
$empfaenger = "dershortler@gmail.com";
$betreff = "Test-E-Mail";
$nachricht = "Hallo, dies ist eine Test-E-Mail.";

// SMTP-Einstellungen
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'panel.freehosting.com';
$mail->SMTPAuth = true;
$mail->Username = 'info@nexuin.com';
$mail->Password = 'NexuinChat1234!!';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

// E-Mail-Empfänger und Inhalt festlegen
$mail->setFrom('info@nexuin.com', 'Absendername');
$mail->addAddress($empfaenger);
$mail->Subject = $betreff;
$mail->Body = $nachricht;

try {
    // E-Mail senden
    $mail->send();
    echo "Die E-Mail wurde erfolgreich versendet.";
} catch (Exception $e) {
    echo "Beim Versenden der E-Mail ist ein Fehler aufgetreten: {$mail->ErrorInfo}";
}
?>
