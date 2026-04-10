<?php
class Booking {
    private $conn;
    private $table_name = "bookings";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserBookings($user_email) {
        $query = "SELECT b.*, a.date, a.time_slot, c.name as club_name, c.facility_name 
                  FROM " . $this->table_name . " b
                  JOIN availability_slots a ON b.slot_id = a.id
                  JOIN clubs c ON a.club_id = c.id
                  WHERE b.user_email = ? AND b.status = 'confirmed'
                  ORDER BY a.date DESC, a.time_slot DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_email]);
        return $stmt;
    }

    public function getUserBookingsByUserId($user_id) {
        $query = "SELECT b.*, a.date, a.time_slot, c.name as club_name, c.facility_name 
                  FROM " . $this->table_name . " b
                  JOIN availability_slots a ON b.slot_id = a.id
                  JOIN clubs c ON a.club_id = c.id
                  WHERE b.user_id = ? AND b.status = 'confirmed'
                  ORDER BY a.date DESC, a.time_slot DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt;
    }

    public function updateBookingUserId($booking_id, $user_id) {
        $query = "UPDATE " . $this->table_name . " SET user_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $booking_id]);
    }

    public function cancelBooking($booking_id, $user_email) {
        try {
            $this->conn->beginTransaction();
            
            $getQuery = "SELECT slot_id FROM " . $this->table_name . " 
                        WHERE id = ? AND user_email = ? AND status = 'confirmed'";
            $getStmt = $this->conn->prepare($getQuery);
            $getStmt->execute([$booking_id, $user_email]);
            $booking = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$booking) {
                return ["success" => false, "message" => "Booking not found"];
            }
            
            $updateSlotQuery = "UPDATE availability_slots 
                               SET is_booked = 0, booked_by = NULL, booking_time = NULL 
                               WHERE id = ?";
            $updateSlotStmt = $this->conn->prepare($updateSlotQuery);
            $updateSlotStmt->execute([$booking['slot_id']]);
            
            $updateBookingQuery = "UPDATE " . $this->table_name . " 
                                  SET status = 'cancelled' 
                                  WHERE id = ?";
            $updateBookingStmt = $this->conn->prepare($updateBookingQuery);
            $updateBookingStmt->execute([$booking_id]);
            
            $this->conn->commit();
            return ["success" => true, "message" => "Booking cancelled successfully"];
        } catch(Exception $e) {
            $this->conn->rollBack();
            return ["success" => false, "message" => "Cancellation failed: " . $e->getMessage()];
        }
    }

    public function cancelBookingById($booking_id, $user_id) {
        try {
            $this->conn->beginTransaction();
            
            $getQuery = "SELECT slot_id FROM " . $this->table_name . " 
                        WHERE id = ? AND user_id = ? AND status = 'confirmed'";
            $getStmt = $this->conn->prepare($getQuery);
            $getStmt->execute([$booking_id, $user_id]);
            $booking = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$booking) {
                return ["success" => false, "message" => "Booking not found"];
            }
            
            $updateSlotQuery = "UPDATE availability_slots 
                               SET is_booked = 0, booked_by = NULL, booking_time = NULL 
                               WHERE id = ?";
            $updateSlotStmt = $this->conn->prepare($updateSlotQuery);
            $updateSlotStmt->execute([$booking['slot_id']]);
            
            $updateBookingQuery = "UPDATE " . $this->table_name . " 
                                  SET status = 'cancelled' 
                                  WHERE id = ?";
            $updateBookingStmt = $this->conn->prepare($updateBookingQuery);
            $updateBookingStmt->execute([$booking_id]);
            
            $this->conn->commit();
            return ["success" => true, "message" => "Booking cancelled successfully"];
        } catch(Exception $e) {
            $this->conn->rollBack();
            return ["success" => false, "message" => "Cancellation failed: " . $e->getMessage()];
        }
    }
}
?>