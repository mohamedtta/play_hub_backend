<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/databases.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get user_id from request body instead of session
    if (empty($data->user_id)) {
        echo json_encode(array(
            "success" => false,
            "message" => "User ID is required"
        ));
        exit();
    }
    
    $user_id = $data->user_id;
    
    if (!empty($data->new_phone)) {
        $new_phone = $data->new_phone;
        
        // Optional: Validate phone format (adjust regex as needed)
        if (!preg_match('/^[0-9+\-\s()]+$/', $new_phone)) {
            echo json_encode(array(
                "success" => false,
                "message" => "Invalid phone number format"
            ));
            exit();
        }
        
        // Check if phone number already exists for another user
        $checkQuery = "SELECT id FROM users WHERE phone = :phone AND id != :user_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(":phone", $new_phone);
        $checkStmt->bindParam(":user_id", $user_id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode(array(
                "success" => false,
                "message" => "Phone number already exists"
            ));
            exit();
        }
        
        // Update phone number
        $query = "UPDATE users SET phone = :phone WHERE id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":phone", $new_phone);
        $stmt->bindParam(":user_id", $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(array(
                "success" => true,
                "message" => "Phone number updated successfully",
                "new_phone" => $new_phone
            ));
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "Failed to update phone number"
            ));
        }
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "Please provide new phone number"
        ));
    }
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid request method"
    ));
}
?>