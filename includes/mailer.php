<?php
// Manual PHPMailer install: the library lives in includes/PHPMailer/src/
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send a booking confirmation email to the client + notification to admin.
 * Returns [bool $success, string $error]
 */
function sendBookingEmails(array $booking): array {

    // ═══════════════════════════════════════════
    //   CONFIGURATION — CHANGE THESE VALUES
    // ═══════════════════════════════════════════
    $smtpHost       = 'smtp.gmail.com';
    $smtpPort       = 587;
    $smtpUsername   = 'your-email@gmail.com';      // Your Gmail
    $smtpPassword   = 'your-app-password';         // 16-character Gmail App Password, NO spaces
    $smtpFromEmail  = 'your-email@gmail.com';
    $smtpFromName   = "D'FACEEE ShamySpice";
    $adminEmail     = 'owner-email@gmail.com';     // Where YOU receive alerts
    // ═══════════════════════════════════════════

    // Escape everything that came from the booking form before putting it in HTML
    $esc = function ($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    };
    $name    = $esc($booking['name']);
    $phone   = $esc($booking['phone'] ?? '');
    $email   = $esc($booking['email']);
    $service = $esc($booking['service']);
    $date    = $esc($booking['date']);
    $time    = $esc($booking['time']);
    $total   = number_format((float)$booking['total'], 2);
    $deposit = number_format((float)$booking['deposit'], 2);
    $balance = number_format((float)$booking['total'] - (float)$booking['deposit'], 2);

    $mail = new PHPMailer(true);

    try {
        // SMTP setup
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUsername;
        $mail->Password   = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtpPort;
        $mail->CharSet    = 'UTF-8';

        // Local XAMPP only: if you get an SSL certificate verify error, uncomment this.
        // Remove it before putting the site online.
        // $mail->SMTPOptions = ['ssl' => [
        //     'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true
        // ]];

        $mail->setFrom($smtpFromEmail, $smtpFromName);

        // --- CLIENT EMAIL ---
        $mail->addAddress($booking['email'], $booking['name']);
        $mail->isHTML(true);
        $mail->Subject = "Your Booking is Confirmed — D'FACEEE";
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #C5A059; border-radius: 10px; overflow: hidden;'>
                <div style='background: #1A1A1A; padding: 30px; text-align: center;'>
                    <h1 style='color: #C5A059; font-family: Georgia, serif; margin: 0;'>D'FACEEE</h1>
                    <p style='color: #fff; margin: 5px 0 0; font-style: italic;'>Where looks says it all</p>
                </div>
                <div style='padding: 30px;'>
                    <h2 style='color: #1A1A1A;'>Hello {$name},</h2>
                    <p style='color: #555;'>Your appointment has been secured. Here are your details:</p>
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <tr style='background: #FDFBF7;'><td style='padding: 10px; border: 1px solid #eee;'><strong>Service</strong></td><td style='padding: 10px; border: 1px solid #eee;'>{$service}</td></tr>
                        <tr><td style='padding: 10px; border: 1px solid #eee;'><strong>Date</strong></td><td style='padding: 10px; border: 1px solid #eee;'>{$date}</td></tr>
                        <tr style='background: #FDFBF7;'><td style='padding: 10px; border: 1px solid #eee;'><strong>Time</strong></td><td style='padding: 10px; border: 1px solid #eee;'>{$time}</td></tr>
                        <tr><td style='padding: 10px; border: 1px solid #eee;'><strong>Deposit Paid</strong></td><td style='padding: 10px; border: 1px solid #eee; color: #27ae60;'>\${$deposit}</td></tr>
                        <tr style='background: #FDFBF7;'><td style='padding: 10px; border: 1px solid #eee;'><strong>Balance Due on Arrival</strong></td><td style='padding: 10px; border: 1px solid #eee; color: #C5A059;'><strong>\${$balance}</strong></td></tr>
                    </table>
                    <p style='color: #999; font-size: 12px; margin-top: 30px;'>Please arrive on time. Cancellations must be made 24 hours in advance. No plus ones allowed.</p>
                </div>
                <div style='background: #1A1A1A; padding: 15px; text-align: center; color: #999; font-size: 12px;'>
                    &copy; " . date('Y') . " D'FACEEE ShamySpice Collection
                </div>
            </div>
        ";
        $mail->send();

        // --- ADMIN EMAIL ---
        $mail->clearAddresses();
        $mail->addAddress($adminEmail, "D'FACEEE Admin");
        $mail->Subject = "🔔 New Booking — " . $booking['name'] . " (" . $booking['service'] . ")";
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto;'>
                <h2 style='color: #C5A059;'>New Booking Received</h2>
                <p><strong>Client:</strong> {$name}</p>
                <p><strong>Phone:</strong> {$phone}</p>
                <p><strong>Email:</strong> {$email}</p>
                <hr style='border: 1px solid #eee;'>
                <p><strong>Service:</strong> {$service}</p>
                <p><strong>Date &amp; Time:</strong> {$date} at {$time}</p>
                <p><strong>Total:</strong> \${$total}</p>
                <p><strong>Deposit Paid:</strong> <span style='color: #27ae60;'>\${$deposit}</span></p>
                <p><strong>Balance Due:</strong> \${$balance}</p>
                <p style='margin-top: 30px;'><a href='http://localhost/dfacacee/admin/index.php' style='background: #C5A059; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View in Dashboard</a></p>
            </div>
        ";
        $mail->send();

        return [true, ''];

    } catch (Exception $e) {
        return [false, $mail->ErrorInfo];
    } catch (\Throwable $e) {
        return [false, $e->getMessage()];
    }
}