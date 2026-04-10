<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/databases.php';
require_once '../models/category.php';

$database = new Database();
$db = $database->getConnection();
$category = new Category($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $category->getAll();
    $categories = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = [
            "id" => $row['id'],
            "name" => $row['name'],
        ];
    }
    
    echo json_encode([
        "success" => true,
        "data" => $categories
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
?>