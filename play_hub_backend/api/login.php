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
    
    if (!empty($data->email) && !empty($data->password)) {
        
        $query = "SELECT id, username, email, password FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":email", $data->email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($data->password, $user['password'])) {
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                echo json_encode(array(
                    "success" => true,
                    "message" => "Login Success",
                    "user" => array(
                        "id" => $user['id'],
                        "username" => $user['username'],
                        "email" => $user['email']
                    )
                ));
            } else {
                echo json_encode(array(
                    "success" => false,
                    "message" => "Wrong Password"
                ));
            }
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "Email Not Found"
            ));
        }
    } else {
        echo json_encode(array(
            "success" => false,
            "message" => "Please Enter you email and password"
        ));
    }
} else {
    echo json_encode(array(
        "success" => false,
        "message" => "Incorrect Way"
    ));
}
?>