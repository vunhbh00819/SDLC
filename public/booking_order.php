<?php
session_start();
include_once '../config/database.php';

// Check if booking data exists
if (!isset($_SESSION['booking_data'])) {
    header('Location: ../pages/room.php');
    exit();
}

$booking_data = $_SESSION['booking_data'];

// Get booking details from database
$sql = "SELECT b.*, r.room_code, r.type, u.full_name, u.email 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        JOIN users u ON b.user_id = u.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_data['booking_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../pages/room.php');
    exit();
}

$booking = $result->fetch_assoc();

// Clear session booking data
unset($_SESSION['booking_data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Hotel Room Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <h3><i class="fas fa-check-circle me-2"></i>Booking Confirmed!</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h5>Booking ID: #<?php echo $booking['id']; ?></h5>
                            <p class="text-muted">Your room has been successfully booked.</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-user me-2"></i>Guest Information</h6>
                                <p><strong>Name:</strong> <?php echo $booking['full_name']; ?></p>
                                <p><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-bed me-2"></i>Room Details</h6>
                                <p><strong>Room:</strong> <?php echo $booking['room_code']; ?></p>
                                <p><strong>Type:</strong> <?php echo ucfirst($booking['type']); ?></p>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h6><i class="fas fa-calendar me-2"></i>Booking Period</h6>
                                <p><strong>Check-in:</strong> <?php echo date('d/m/Y H:i', strtotime($booking['start_time'])); ?></p>
                                <p><strong>Check-out:</strong> <?php echo date('d/m/Y H:i', strtotime($booking['end_time'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-money-bill me-2"></i>Payment Details</h6>
                                <p><strong>Total Amount:</strong> <?php echo number_format($booking['total_amount'], 0, ',', '.'); ?> VND</p>
                                <p><strong>Status:</strong> <span class="badge bg-success">Paid</span></p>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="../pages/room.php" class="btn btn-primary">Book Another Room</a>
                            <button onclick="window.print()" class="btn btn-outline-secondary">Print Confirmation</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
