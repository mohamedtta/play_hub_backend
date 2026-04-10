<?php
class Availability {
    private $conn;
    private $table_name = "availability_slots";

    public $id;
    public $club_id;
    public $date;
    public $time_slot;
    public $is_booked;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByClubAndDate($club_id, $date) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE club_id = ? AND date = ? 
                  ORDER BY time_slot";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$club_id, $date]);
        return $stmt;
    }

    public function getAvailableSlots($club_id, $date) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE club_id = ? AND date = ? AND is_booked = 0 
                  ORDER BY time_slot";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$club_id, $date]);
        return $stmt;
    }

    public function getSlotsForDateRange($club_id, $start_date, $end_date) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE club_id = ? AND date BETWEEN ? AND ? 
                  ORDER BY date, time_slot";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$club_id, $start_date, $end_date]);
        return $stmt;
    }

    public function bookSlot($slot_id, $user_name, $user_phone, $user_email) {
        try {
            $this->conn->beginTransaction();
            
            $checkQuery = "SELECT is_booked FROM " . $this->table_name . " WHERE id = ? FOR UPDATE";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([$slot_id]);
            $slot = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($slot['is_booked'] == 1) {
                return ["success" => false, "message" => "Slot already booked"];
            }
            
            $updateQuery = "UPDATE " . $this->table_name . " 
                           SET is_booked = 1, booked_by = ?, booking_time = NOW() 
                           WHERE id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([$user_email, $slot_id]);
            
            $bookingQuery = "INSERT INTO bookings (slot_id, user_name, user_phone, user_email, amount) 
                            VALUES (?, ?, ?, ?, (SELECT price_per_hour FROM clubs WHERE id = (SELECT club_id FROM availability_slots WHERE id = ?)))";
            $bookingStmt = $this->conn->prepare($bookingQuery);
            $bookingStmt->execute([$slot_id, $user_name, $user_phone, $user_email, $slot_id]);
            
            $this->conn->commit();
            return ["success" => true, "message" => "Booking successful", "booking_id" => $this->conn->lastInsertId()];
        } catch(Exception $e) {
            $this->conn->rollBack();
            return ["success" => false, "message" => "Booking failed: " . $e->getMessage()];
        }
    }
}
?>