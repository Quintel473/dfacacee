<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize Input
    $name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $service = htmlspecialchars($_POST['service']);
    $date = $_POST['booking_date'];
    $time = $_POST['booking_time'];
    $total_price = $_POST['total_price'];
    $deposit = $_POST['deposit_paid'];

    // 2. Validate (Basic)
    if(empty($name) || empty($email) || empty($deposit) || $deposit == 0) {
        die("Please fill in all fields and select a service.");
    }

    // 3. SIMULATE PAYMENT PROCESSING
    // In a real scenario, you would integrate Stripe or PayPal API here.
    // Since this is a demo, we assume payment was successful.
    $payment_status = "Paid (Simulated)"; 

    // 4. Insert into Database
    try {
        $sql = "INSERT INTO bookings (full_name, email, phone, service, booking_date, booking_time, total_price, deposit_paid, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $phone, $service, $date, $time, $total_price, $deposit, $payment_status]);

        // 5. Success Message
        echo "
        <div style='font-family: sans-serif; text-align: center; padding: 50px;'>
            <h1 style='color: #C5A059;'>Booking Confirmed!</h1>
            <p>Thank you, $name.</p>
            <p>Your appointment for <strong>$service</strong> on <strong>$date at $time</strong> is secured.</p>
            <p>We have received your deposit of <strong>$$deposit</strong>.</p>
            <p>Remaining balance to be paid on the day: <strong>$" . ($total_price - $deposit) . "</strong></p>
            <br>
            <a href='index.php' style='background: #C5A059; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Return Home</a>
        </div>
        ";

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>