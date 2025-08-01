<?php
session_start();
include_once '../config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['booking_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$booking_id = (int)$input['booking_id'];
$status = $input['status'];

// Validate status
$valid_statuses = ['pending', 'paid', 'cancelled', 'checked_out'];
if (!in_array($status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // Update booking status
    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        // If payment is successful, also create a payment record if not exists
        if ($status === 'paid' && isset($_SESSION['booking_data'])) {
            // Check if payment record already exists
            $check_payment_sql = "SELECT id FROM payments WHERE booking_id = ?";
            $check_stmt = $conn->prepare($check_payment_sql);
            $check_stmt->bind_param("i", $booking_id);
            $check_stmt->execute();
            $payment_exists = $check_stmt->get_result()->num_rows > 0;
            
            if (!$payment_exists) {
                $amount = $_SESSION['booking_data']['total_amount'];
                $method = $_SESSION['booking_data']['payment_method'];
                
                // Validate payment method before inserting
                $valid_methods = ['card', 'wallet', 'cash', 'bank_transfer', 'vietcombank', 'techcombank', 'bidv'];
                if (!in_array($method, $valid_methods)) {
                    $method = 'bank_transfer'; // Default fallback
                }
                
                $payment_sql = "INSERT INTO payments (booking_id, amount, method) VALUES (?, ?, ?)";
                $payment_stmt = $conn->prepare($payment_sql);
                $payment_stmt->bind_param("ids", $booking_id, $amount, $method);
                
                if (!$payment_stmt->execute()) {
                    throw new Exception('Failed to create payment record: ' . $payment_stmt->error);
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
    } else {
        throw new Exception('Failed to update booking status');
    }
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
