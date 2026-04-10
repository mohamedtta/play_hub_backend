<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/databases.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(array(
            "success" => false,
            "message" => "User not logged in"
        ));
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    $query = "SELECT id, username, email, phone FROM users WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(array(
            "success" => true,
            "user" => array(
                "id" => $user['id'],
                "username" => $user['username'],
                "email" => $user['email'],
                "phone" => $user['phone'] ?? "Not set"
            )
        ));
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