<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

include '../config/database.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Function to update room statuses
function updateRoomStatus($conn, $room_id = null) {
    $current_time = date('Y-m-d H:i:s');
    $updates = false;
    
    try {
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        if ($room_id) {
            // Single room update
            $conditions = "r.id = " . intval($room_id);
        } else {
            // All rooms update
            $conditions = "1=1";
        }
        
        // Update rooms with ended bookings
        $query = "UPDATE rooms r 
                 LEFT JOIN (
                     SELECT room_id, MIN(start_time) as next_start,
                            MAX(CASE WHEN start_time <= ? AND end_time >= ? THEN 1 ELSE 0 END) as is_active
                     FROM bookings 
                     WHERE status = 'paid'
                     GROUP BY room_id
                 ) b ON r.id = b.room_id
                 SET r.status = CASE
                     WHEN b.is_active = 1 THEN 'in_use'
                     WHEN b.next_start > ? THEN 'booked'
                     WHEN r.status != 'maintenance' THEN 'available'
                     ELSE r.status
                 END
                 WHERE " . $conditions;
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sss', $current_time, $current_time, $current_time);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to update room status');
        }
        
        $updates = mysqli_stmt_affected_rows($stmt) > 0;
        
        // Update expired bookings to checked_out
        $query = "UPDATE bookings 
                 SET status = 'checked_out' 
                 WHERE end_time < ? 
                 AND status IN ('paid', 'pending')" .
                 ($room_id ? " AND room_id = " . intval($room_id) : "");
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $current_time);
        mysqli_stmt_execute($stmt);
        
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $updates = true;
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        return $updates;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $room_id = isset($input['room_id']) ? (int)$input['room_id'] : null;
    
    $updates = updateRoomStatus($conn, $room_id);
    
    echo json_encode([
        'success' => true,
        'updated' => $updates,
        'timestamp' => $current_time
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

mysqli_close($conn);
?>
            
            if (!mysqli_stmt_execute($update_room_stmt)) {
                throw new Exception('Failed to update room status');
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Room status updated successfully',
                'updated_bookings' => mysqli_affected_rows($conn)
            ]);
        } else {
            // No expired bookings found
            echo json_encode([
                'success' => false, 
                'message' => 'No expired bookings found for this room'
            ]);
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

mysqli_close($conn);
?>
