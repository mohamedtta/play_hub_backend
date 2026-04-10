<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/databases.php';
require_once '../models/club.php';

$database = new Database();
$db = $database->getConnection();
$club = new Club($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if category_id is provided
    if (isset($_GET['category_id'])) {
        $category_id = $_GET['category_id'];
        $stmt = $club->getByCategory($category_id);
        
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
    } 
    // Check if area search is provided
    elseif (isset($_GET['search'])) {
        $area = $_GET['search'];
        $stmt = $club->searchByArea($area);
        
        $clubs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clubs[] = [
                "id" => $row['id'],
                "name" => $row['name'],
                "facility_name" => $row['facility_name'],
                "area" => $row['area'],
                "courts_count" => $row['courts_count'],
                "price_per_hour" => floatval($row['price_per_hour'])
            ];
        }
        
        echo json_encode([
            "success" => true,
            "data" => $clubs
        ]);
    }
    // Get single club
    elseif (isset($_GET['id'])) {
        $club_id = $_GET['id'];
        $clubData = $club->getById($club_id);
        
        if ($clubData) {
            echo json_encode([
                "success" => true,
                "data" => $clubData
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Club not found"
            ]);
        }
    }
    // Get all clubs
    else {
        $stmt = $club->getAll();
        
        $clubs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clubs[] = [
                "id" => $row['id'],
                "name" => $row['name'],
                "facility_name" => $row['facility_name'],
                "area" => $row['area'],
                "courts_count" => $row['courts_count'],
                "category_name" => $row['category_name']
            ];
        }
        
        echo json_encode([
            "success" => true,
            "data" => $clubs
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
?>