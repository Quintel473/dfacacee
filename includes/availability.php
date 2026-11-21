<?php
/**
 * Returns true if the given date+time slot is ALREADY booked.
 */
function isSlotBooked(PDO $pdo, string $date, string $time): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_date = ? AND booking_time = ? AND payment_status != 'Cancelled'");
    $stmt->execute([$date, $time]);
    return (int)$stmt->fetchColumn() > 0;
}

/**
 * Returns an array of booked time slots for a given date.
 */
function getBookedSlots(PDO $pdo, string $date): array {
    $stmt = $pdo->prepare("SELECT booking_time FROM bookings WHERE booking_date = ? AND payment_status != 'Cancelled'");
    $stmt->execute([$date]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}