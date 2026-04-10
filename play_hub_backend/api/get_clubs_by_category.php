<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/databases.php';
require_once '../models/club.php';

$database = new Database();
$db = $database->getConnection();
$club = new Club($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['category_id'])) {
        $category_id = $_GET['category_id'];
        $area = isset($_GET['area']) ? $_GET['area'] : null;
        
        // If area is specified and not 'all', filter by area
        if ($area && $area != 'all') {
            $stmt = $club->getByCategoryAndArea($category_id, $area);
        } else {
            $stmt = $club->getByCategory($category_id);
        }
        
        $clubs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clubs[] = [
                "id" => $row['id'],
                "name" => $row['name'],
                "facility_name" => $row['facility_name'],
                "area" => $row['area'],
                "courts_count" => $row['courts_count'],
                "price_per_hour" => floatval($row['price_per_hour']),
                "image_url" => $row['image_url'],
                "category_name" => $row['category_name']
            ];
        }
        
        echo json_encode([
            "success" => true,
            "data" => $clubs
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Category ID required"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
?>