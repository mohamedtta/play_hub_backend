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
    
    // Check if passwords are provided
    if (empty($data->current_password) || empty($data->new_password)) {
        echo json_encode(array(
            "success" => false,
            "message" => "Please provide current and new password"
        ));
        exit();
    }
    
    // Validate new password strength (optional)
    if (strlen($data->new_password) < 6) {
        echo json_encode(array(
            "success" => false,
            "message" => "New password must be at least 6 characters long"
        ));
        exit();
    }
    
    // Get current password from database
    $query = "SELECT password FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify current password
        if (password_verify($data->current_password, $user['password'])) {
            
            // Check if new password is same as current password
            if (password_verify($data->new_password, $user['password'])) {
                echo json_encode(array(
                    "success" => false,
                    "message" => "New password cannot be the same as current password"
                ));
                exit();
            }
            
            // Hash new password
            $hashed_password = password_hash($data->new_password, PASSWORD_DEFAULT);
            
            // Update password
            $updateQuery = "UPDATE users SET password = :password WHERE id = :user_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(":password", $hashed_password);
            $updateStmt->bindParam(":user_id", $user_id);
            
            if ($updateStmt->execute()) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Password changed successfully"
                ));
            } else {
                echo json_encode(array(
                    "success" => false,
                    "message" => "Failed to change password"
                ));
            }
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "Current password is incorrect"
            ));
        }
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "User not found"
        ));
    }
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid request method"
    ));
}
?>