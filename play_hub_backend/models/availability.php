<?php
class Availability {
    private $conn;
    private $table_name = "availability_slots";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAvailableSlots($club_id, $date) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE club_id = ? AND date = ? AND is_booked = 0 
                      ORDER BY time_slot";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$club_id, $date]);
            return $stmt;
        } catch (Exception $e) {
            error_log("getAvailableSlots error: " . $e->getMessage());
            return false;
        }
    }

    public function bookSlot($slot_id, $user_name, $user_phone, $user_email, $user_id) {
        try {
            $this->conn->beginTransaction();
            
            // Check if slot exists and is available
            $checkQuery = "SELECT is_booked, club_id FROM " . $this->table_name . " WHERE id = ? FOR UPDATE";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([$slot_id]);
            $slot = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$slot) {
                $this->conn->rollBack();
                return ["success" => false, "message" => "Slot not found"];
            }
            
            if ($slot['is_booked'] == 1) {
                $this->conn->rollBack();
                return ["success" => false, "message" => "Slot already booked"];
            }
            
            // Update slot as booked
            $updateQuery = "UPDATE " . $this->table_name . " 
                           SET is_booked = 1, booked_by = ?, booking_time = NOW() 
                           WHERE id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([$user_email, $slot_id]);
            
            // Create booking record with user_id
            $bookingQuery = "INSERT INTO bookings (slot_id, user_id, user_name, user_phone, user_email, booking_date) 
                            VALUES (?, ?, ?, ?, ?, NOW())";
            $bookingStmt = $this->conn->prepare($bookingQuery);
            $bookingStmt->execute([$slot_id, $user_id, $user_name, $user_phone, $user_email]);
            
            $booking_id = $this->conn->lastInsertId();
            
            $this->conn->commit();
            
            error_log("Booking created successfully. ID: $booking_id, User ID: $user_id");
            
            return [
                "success" => true, 
                "message" => "Booking successful", 
                "booking_id" => $booking_id
            ];
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("bookSlot error: " . $e->getMessage());
            return ["success" => false, "message" => "Booking failed: " . $e->getMessage()];
        }
    }
}
?>