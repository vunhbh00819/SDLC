<?php
include_once '../config/database.php';

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Update rooms status based on bookings
function updateRoomStatuses($conn) {
    $current_time = date('Y-m-d H:i:s');
    
    // First, reset rooms that have no current bookings to available
    $sql_reset = "UPDATE rooms r 
                  LEFT JOIN (
                      SELECT room_id 
                      FROM bookings 
                      WHERE status = 'paid' 
                      AND start_time <= ? 
                      AND end_time > ?
                  ) active_bookings ON r.id = active_bookings.room_id 
                  SET r.status = 'available' 
                  WHERE active_bookings.room_id IS NULL 
                  AND r.status != 'maintenance'";
    
    $stmt = $conn->prepare($sql_reset);
    $stmt->bind_param("ss", $current_time, $current_time);
    $stmt->execute();

    // Update rooms to 'booked' for future bookings
    $sql_to_booked = "UPDATE rooms r 
                      INNER JOIN (
                          SELECT room_id 
                          FROM bookings 
                          WHERE status = 'paid' 
                          AND start_time > ? 
                          GROUP BY room_id
                      ) future_bookings ON r.id = future_bookings.room_id 
                      SET r.status = 'booked' 
                      WHERE r.status = 'available'";
    
    $stmt = $conn->prepare($sql_to_booked);
    $stmt->bind_param("s", $current_time);
    $stmt->execute();

    // Update rooms to 'in_use' for current bookings
    $sql_to_in_use = "UPDATE rooms r 
                      INNER JOIN bookings b ON r.id = b.room_id 
                      SET r.status = 'in_use' 
                      WHERE b.status = 'paid' 
                      AND b.start_time <= ? 
                      AND b.end_time > ?";
    
    $stmt = $conn->prepare($sql_to_in_use);
    $stmt->bind_param("ss", $current_time, $current_time);
    $stmt->execute();
}

// Run the update
updateRoomStatuses($conn);

// Return response
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Room statuses updated', 'timestamp' => date('Y-m-d H:i:s')]);
?>
