<?php
session_start();
require '../includes/db.php';
require '../includes/mailer.php';

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['pending_booking'])) {
    header("Location: ../pages/booking.php");
    exit;
}

$b = $_SESSION['pending_booking'];
$payment_status = "Paid";

// Generate a unique booking reference (e.g., DF-00001)
$refStmt = $pdo->query("SELECT MAX(id) as max_id FROM bookings");
$maxId = $refStmt->fetchColumn();
$booking_ref = "DF-" . str_pad(($maxId + 1), 5, '0', STR_PAD_LEFT);

try {
    $sql = "INSERT INTO bookings (full_name, email, phone, service, booking_date, booking_time, total_price, deposit_paid, payment_status, stylist_preference, special_notes, booking_reference) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $b['name'], $b['email'], $b['phone'], $b['service'], 
        $b['date'], $b['time'], $b['total'], $b['deposit'], $payment_status,
        $b['stylist'], $b['notes'], $booking_ref
    ]);

    // Send emails
    [$mailOk, $mailError] = sendBookingEmails($b);
    if (!$mailOk) { error_log("Email send failed: " . $mailError); }

    unset($_SESSION['pending_booking']);

    $pageTitle = "Booking Confirmed | D'FACEEE";
    include '../includes/header.php';
    ?>
    
    <div class="success-card">
        <div class="checkmark">✓</div>
        <h1 style="color: var(--black); margin-bottom: 10px;">Booking Confirmed!</h1>
        <p style="color: var(--text-gray); margin-bottom: 5px;">Thank you, <?php echo htmlspecialchars($b['name']); ?>.</p>
        <p style="color: var(--text-gray); margin-bottom: 20px; font-size: 0.9rem;">Your reference is <strong><?php echo $booking_ref; ?></strong>. A confirmation email is on its way.</p>
        
        <div class="summary-box" style="text-align: left;">
            <div class="summary-row"><span>Service:</span> <strong><?php echo htmlspecialchars($b['service']); ?></strong></div>
            <div class="summary-row"><span>Date:</span> <strong><?php echo htmlspecialchars($b['date']); ?></strong></div>
            <div class="summary-row"><span>Time:</span> <strong><?php echo htmlspecialchars($b['time']); ?></strong></div>
            <div class="summary-row"><span>Stylist:</span> <strong><?php echo htmlspecialchars($b['stylist']); ?></strong></div>
            <div class="summary-row"><span>Deposit Paid:</span> <strong>$<?php echo number_format($b['deposit'], 2); ?></strong></div>
            <div class="summary-row total"><span>Balance Due:</span> <span>$<?php echo number_format($b['total'] - $b['deposit'], 2); ?></span></div>
        </div>

        <?php if (!$mailOk): ?>
            <p style="font-size: 0.8rem; color: #e74c3c;">Note: We couldn't send the email, but your booking is saved.</p>
        <?php endif; ?>

        <a href="/index.php" class="btn-primary" style="font-size: 0.9rem; padding: 10px 20px;">Return Home</a>
    </div>

    <?php
    include '../includes/footer.php';

} catch (PDOException $e) {
    echo "Error saving booking: " . $e->getMessage();
}
?>