<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/databases.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!empty($data->user_id) && !empty($data->new_username)) {
        $user_id = $data->user_id;
        $new_username = $data->new_username;
        
        // Check if username already exists for another user
        $checkQuery = "SELECT id FROM users WHERE username = :username AND id != :user_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(":username", $new_username);
        $checkStmt->bindParam(":user_id", $user_id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode(array(
                "success" => false,
                "message" => "Username already exists"
            ));
            exit();
        }
        
        // Update username
        $query = "UPDATE users SET username = :username WHERE id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":username", $new_username);
        $stmt->bindParam(":user_id", $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(array(
                "success" => true,
                "message" => "Username updated successfully",
                "new_username" => $new_username
            ));
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "Failed to update username"
            ));
        }
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "Please provide user ID and new username"
        ));
    }
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid request method"
    ));
}
?>