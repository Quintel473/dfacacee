<?php
session_start();
require '../includes/db.php';
require '../includes/availability.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: booking.php");
    exit;
}

// Server-side slot re-check (critical - user could have been on payment screen for minutes)
if (isSlotBooked($pdo, $_POST['booking_date'], $_POST['booking_time'])) {
    die("
        <div style='font-family: sans-serif; text-align:center; padding: 60px;'>
            <h1 style='color:#c0392b;'>Sorry, that slot was just taken!</h1>
            <p>The time you selected is no longer available. Please <a href='booking.php'>start over</a>.</p>
        </div>
    ");
}

$_SESSION['pending_booking'] = [
    'service' => $_POST['service'],
    'total'   => $_POST['total_price'],
    'deposit' => $_POST['deposit_paid'],
    'name'    => $_POST['full_name'],
    'email'   => $_POST['email'],
    'phone'   => $_POST['phone'],
    'date'    => $_POST['booking_date'],
    'time'    => $_POST['booking_time']
];

$b = $_SESSION['pending_booking'];

$pageTitle = "Secure Checkout | D'FACEEE";
include '../includes/header.php';
?>

<div class="checkout-container">
    <div class="secure-badge">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"/></svg>
        Secure SSL Encrypted Payment
    </div>

    <h2 style="text-align:center; margin-bottom: 5px;">Payment Details</h2>
    <p style="text-align:center; color: var(--text-gray); font-size: 0.9rem;">Pay the 50% deposit to confirm your booking.</p>
    <div class="total-display">$<?php echo number_format($b['deposit'], 2); ?></div>

    <form action="/dfacacee/actions/process_payment.php" method="POST">
        <div class="card-input-group">
            <label>Card Number</label>
            <input type="text" placeholder="0000 0000 0000 0000" maxlength="19" required>
        </div>
        <div class="card-row">
            <div class="card-input-group">
                <label>Expiry Date</label>
                <input type="text" placeholder="MM/YY" maxlength="5" required>
            </div>
            <div class="card-input-group">
                <label>CVC</label>
                <input type="text" placeholder="123" maxlength="3" required>
            </div>
        </div>
        <div class="card-input-group">
            <label>Name on Card</label>
            <input type="text" placeholder="<?php echo htmlspecialchars($b['name']); ?>" required>
        </div>
        <button type="submit" class="btn-pay">Pay $<?php echo number_format($b['deposit'], 2); ?> & Confirm</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>