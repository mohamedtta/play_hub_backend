<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/databases.php';
require_once '../models/booking.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        
        // Debug: Log the user_id being queried
        error_log("Fetching bookings for user_id: " . $user_id);
        
        // First, check if there are any bookings at all
        $checkQuery = "SELECT COUNT(*) as total FROM bookings";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute();
        $totalBookings = $checkStmt->fetch(PDO::FETCH_ASSOC);
        error_log("Total bookings in database: " . $totalBookings['total']);
        
        // Check bookings for this specific user
        $userCheckQuery = "SELECT COUNT(*) as total FROM bookings WHERE user_id = ?";
        $userCheckStmt = $db->prepare($userCheckQuery);
        $userCheckStmt->execute([$user_id]);
        $userBookings = $userCheckStmt->fetch(PDO::FETCH_ASSOC);
        error_log("Bookings for user_id $user_id: " . $userBookings['total']);
        
        $booking = new Booking($db);
        $stmt = $booking->getUserBookingsByUserId($user_id);
        
        $bookings = [];
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $bookings[] = [
                    "id" => $row['id'],
                    "slot_id" => $row['slot_id'],
                    "club_name" => $row['club_name'],
                    "facility_name" => $row['facility_name'] ?? '',
                    "date" => $row['date'],
                    "time_slot" => $row['time_slot'],
                    "status" => $row['status'] ?? 'confirmed',
                    "booking_date" => $row['booking_date'],
                    "amount" => $row['amount'] ? floatval($row['amount']) : null
                ];
            }
        }
        
        echo json_encode([
            "success" => true,
            "data" => $bookings,
            "debug" => [
                "user_id" => $user_id,
                "total_bookings_found" => count($bookings)
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "User ID required"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
?>