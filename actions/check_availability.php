<?php
require '../includes/db.php';
require '../includes/availability.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? '';
if (!$date) {
    echo json_encode(['error' => 'No date provided']);
    exit;
}

$booked = getBookedSlots($pdo, $date);
echo json_encode(['booked' => $booked]);