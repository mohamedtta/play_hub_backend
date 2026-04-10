<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database files
require_once '../config/databases.php';
require_once '../models/booking.php';

// Initialize response
$response = [];

try {
    // Get input data
    $input = file_get_contents("php://input");
    
    // Check if input is empty
    if (empty($input)) {
        throw new Exception("No input data received");
    }
    
    $data = json_decode($input);
    
    // Check if JSON is valid
    if ($data === null) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }
    
    // Check required fields
    if (empty($data->booking_id)) {
        throw new Exception("Booking ID is required");
    }
    
    if (empty($data->user_id)) {
        throw new Exception("User ID is required");
    }
    
    // Connect to database
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    // Cancel the booking
    $booking = new Booking($db);
    $result = $booking->cancelBookingById($data->booking_id, $data->user_id);
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>