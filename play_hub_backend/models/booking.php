<?php
class Booking {
    private $conn;
    private $table_name = "bookings";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserBookingsByUserId($user_id) {
        try {
            $query = "SELECT b.*, a.date, a.time_slot, c.name as club_name, c.facility_name 
                      FROM " . $this->table_name . " b
                      JOIN availability_slots a ON b.slot_id = a.id
                      JOIN clubs c ON a.club_id = c.id
                      WHERE b.user_id = ? AND (b.status = 'confirmed' OR b.status IS NULL)
                      ORDER BY a.date DESC, a.time_slot DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id]);
            return $stmt;
        } catch (Exception $e) {
            error_log("getUserBookingsByUserId error: " . $e->getMessage());
            return false;
        }
    }

    public function updateBookingUserId($booking_id, $user_id) {
        try {
            $query = "UPDATE " . $this->table_name . " SET user_id = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$user_id, $booking_id]);
        } catch (Exception $e) {
            error_log("updateBookingUserId error: " . $e->getMessage());
            return false;
        }
    }

    public function cancelBookingById($booking_id, $user_id) {
        try {
            // Start transaction
            $this->conn->beginTransaction();
            
            // First, check if booking exists and belongs to user
            $checkQuery = "SELECT id, slot_id FROM " . $this->table_name . " 
                          WHERE id = ? AND user_id = ? AND (status = 'confirmed' OR status IS NULL)";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([$booking_id, $user_id]);
            $booking = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$booking) {
                $this->conn->rollBack();
                return ["success" => false, "message" => "Booking not found or already cancelled"];
            }
            
            // Update the availability slot to available (is_booked = 0)
            $updateSlotQuery = "UPDATE availability_slots 
                               SET is_booked = 0, booked_by = NULL, booking_time = NULL 
                               WHERE id = ?";
            $updateSlotStmt = $this->conn->prepare($updateSlotQuery);
            $updateSlotStmt->execute([$booking['slot_id']]);
            
            // Update the booking status to cancelled
            $updateBookingQuery = "UPDATE " . $this->table_name . " 
                                  SET status = 'cancelled' 
                                  WHERE id = ?";
            $updateBookingStmt = $this->conn->prepare($updateBookingQuery);
            $updateBookingStmt->execute([$booking_id]);
            
            // Commit transaction
            $this->conn->commit();
            
            return ["success" => true, "message" => "Booking cancelled successfully"];
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("cancelBookingById error: " . $e->getMessage());
            return ["success" => false, "message" => "Failed to cancel booking: " . $e->getMessage()];
        }
    }
}
?>