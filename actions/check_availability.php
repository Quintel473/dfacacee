<?php
require '../includes/db.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? '';
if (!$date) {
    echo json_encode(['error' => 'No date provided']);
    exit;
}

// 1. Get booked time slots
$stmt = $pdo->prepare("SELECT booking_time FROM bookings WHERE booking_date = ? AND payment_status != 'Cancelled'");
$stmt->execute([$date]);
$booked = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 2. Get blocked time slots (Admin blocks)
$stmtBlocked = $pdo->prepare("SELECT block_time FROM blocked_slots WHERE block_date = ?");
$stmtBlocked->execute([$date]);
$blocked = $stmtBlocked->fetchAll(PDO::FETCH_COLUMN);

// If a block_time is NULL, it means the whole day is blocked
$isFullDayBlocked = in_array(null, $blocked);
if ($isFullDayBlocked) {
    $blocked = ['05:00', '06:00', '07:00', '15:00', '16:00', '17:00', '18:00', '19:00']; // Block everything
} else {
    // Filter out nulls just in case
    $blocked = array_filter($blocked);
}

echo json_encode([
    'booked' => $booked,
    'blocked' => array_values($blocked)
]);