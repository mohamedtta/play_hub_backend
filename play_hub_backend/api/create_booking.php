<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/databases.php';
require_once '../models/availability.php';
require_once '../models/booking.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!empty($data->slot_id) && !empty($data->user_id) && 
        !empty($data->user_name) && !empty($data->user_email)) {
        
        error_log("Creating booking for user_id: " . $data->user_id);
        error_log("Slot ID: " . $data->slot_id);
        
        $availability = new Availability($db);
        $result = $availability->bookSlot(
            $data->slot_id,
            $data->user_name,
            $data->user_phone ?? '',
            $data->user_email,
            $data->user_id  // Pass user_id directly
        );
        
        if ($result['success']) {
            echo json_encode([
                "success" => true,
                "message" => "Booking successful",
                "booking_id" => $result['booking_id']
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => $result['message']
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Missing required fields"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
?>