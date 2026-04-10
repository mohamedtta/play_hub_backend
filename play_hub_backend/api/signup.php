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
    
   
    if (!empty($data->username) && !empty($data->email) && !empty($data->password)) {
        
        $checkQuery = "SELECT id FROM users WHERE email = :email OR username = :username";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(":email", $data->email);
        $checkStmt->bindParam(":username", $data->username);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            echo json_encode(array(
                "success" => false,
                "message" => "Email or Username already exist"
            ));
            exit();
        }
        
        $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (username, email, password, phone) 
                  VALUES (:username, :email, :password, :phone)";
        
        $stmt = $db->prepare($query);
        
        $stmt->bindParam(":username", $data->username);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":phone", $data->phone);
        
        if ($stmt->execute()) {
            echo json_encode(array(
                "success" => true,
                "message" => "Account Created Success",
                "user_id" => $db->lastInsertId()
            ));
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "Error Happen While Creating the Account"
            ));
        }
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "Please, Enter All the Data Needed"
        ));
    }
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "طريقة غير مسموح بها"
    ));
}
?>