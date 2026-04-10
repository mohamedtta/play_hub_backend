<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../config/databases.php';
require_once '../models/availability.php';

$database = new Database();
$db = $database->getConnection();
$availability = new Availability($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['club_id']) && isset($_GET['date'])) {
        $club_id = $_GET['club_id'];
        $date = $_GET['date'];
        
        $stmt = $availability->getAvailableSlots($club_id, $date);
        
        $slots = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $slots[] = [
                "id" => $row['id'],
                "time_slot" => $row['time_slot'],
                "is_booked" => (bool)$row['is_booked']
            ];
        }
        
        echo json_encode([
            "success" => true,
            "data" => [
                "club_id" => $club_id,
                "date" => $date,
                "slots" => $slots
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Club ID and date required"
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
?>