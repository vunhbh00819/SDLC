<?php
// Auto cleanup script - can be run via cron job
include 'config/database.php';

try {
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    echo "Starting automatic room status cleanup...\n";
    
    // Find all rooms with expired bookings
    $expired_bookings_query = "SELECT DISTINCT b.room_id, COUNT(*) as expired_count
                              FROM bookings b 
                              WHERE b.status IN ('paid', 'pending') 
                              AND b.end_time <= NOW()
                              GROUP BY b.room_id";
    
    $expired_result = mysqli_query($conn, $expired_bookings_query);
    
    if (mysqli_num_rows($expired_result) > 0) {
        $updated_rooms = [];
        
        while ($row = mysqli_fetch_assoc($expired_result)) {
            $room_id = $row['room_id'];
            $expired_count = $row['expired_count'];
            
            // Update expired bookings to 'checked_out'
            $update_booking_query = "UPDATE bookings 
                                   SET status = 'checked_out' 
                                   WHERE room_id = ? 
                                   AND status IN ('paid', 'pending') 
                                   AND end_time <= NOW()";
            
            $update_booking_stmt = mysqli_prepare($conn, $update_booking_query);
            mysqli_stmt_bind_param($update_booking_stmt, 'i', $room_id);
            
            if (mysqli_stmt_execute($update_booking_stmt)) {
                // Update room status to 'available'
                $update_room_query = "UPDATE rooms SET status = 'available' WHERE id = ?";
                $update_room_stmt = mysqli_prepare($conn, $update_room_query);
                mysqli_stmt_bind_param($update_room_stmt, 'i', $room_id);
                
                if (mysqli_stmt_execute($update_room_stmt)) {
                    $updated_rooms[] = "Room ID {$room_id} ({$expired_count} bookings updated)";
                    echo "✓ Updated Room ID {$room_id} - {$expired_count} expired bookings\n";
                } else {
                    echo "✗ Failed to update room status for Room ID {$room_id}\n";
                }
            } else {
                echo "✗ Failed to update bookings for Room ID {$room_id}\n";
            }
        }
        
        // Commit all changes
        mysqli_commit($conn);
        
        echo "\nCleanup completed successfully!\n";
        echo "Total rooms updated: " . count($updated_rooms) . "\n";
        
        if (!empty($updated_rooms)) {
            echo "Updated rooms:\n";
            foreach ($updated_rooms as $update) {
                echo "- {$update}\n";
            }
        }
        
    } else {
        echo "No expired bookings found. No cleanup needed.\n";
    }
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    echo "Error during cleanup: " . $e->getMessage() . "\n";
}

mysqli_close($conn);
echo "\nCleanup script finished at " . date('Y-m-d H:i:s') . "\n";
?>
