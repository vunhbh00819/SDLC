<?php
session_start();
include_once '../config/database.php';


if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle booking status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $booking_id = $_POST['booking_id'];
        $status = $_POST['status'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update booking status
            $sql = "UPDATE bookings SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $status, $booking_id);
            $stmt->execute();
            
            // If booking is cancelled or checked out, update room status to available
            if ($status === 'cancelled' || $status === 'checked_out') {
                $sql = "UPDATE rooms r 
                        JOIN bookings b ON r.id = b.room_id 
                        SET r.status = 'available' 
                        WHERE b.id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $booking_id);
                $stmt->execute();
            }
            
            $conn->commit();
            
        } catch (Exception $e) {
            $conn->rollback();
            die('Error updating booking: ' . $e->getMessage());
        }
    }
}

// Get all bookings with related information - updated query
$sql = "SELECT b.*, r.room_code, r.type, r.hourly_rate,
        u.full_name, u.email, u.phone_number, 
        p.method as payment_method, p.amount as paid_amount,
        TIMESTAMPDIFF(HOUR, b.start_time, b.end_time) as duration_hours
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        JOIN users u ON b.user_id = u.id 
        LEFT JOIN payments p ON p.booking_id = b.id 
        ORDER BY b.booking_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="manager_room.php">
                            <i class="fas fa-bed me-2"></i>Rooms
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="manager_booking.php">
                            <i class="fas fa-calendar-check me-2"></i>Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="report.php">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Booking Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportToCSV()">Export</button>
                    </div>
                </div>
            </div>

            <!-- Booking List -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $booking['id']; ?></td>
                            <td>
                                <div><?php echo $booking['full_name']; ?></div>
                                <small class="text-muted"><?php echo $booking['email']; ?></small>
                                <small class="text-muted d-block"><?php echo $booking['phone_number']; ?></small>
                            </td>
                            <td>
                                <?php echo $booking['room_code']; ?>
                                <span class="badge <?php echo $booking['type'] === 'vip' ? 'bg-warning' : 'bg-primary'; ?>">
                                    <?php echo strtoupper($booking['type']); ?>
                                </span>
                            </td>
                            <td>
                                <div><?php echo date('d/m/Y H:i', strtotime($booking['start_time'])); ?></div>
                                <div><?php echo date('d/m/Y H:i', strtotime($booking['end_time'])); ?></div>
                                <small class="text-muted"><?php echo $booking['duration_hours']; ?> hours</small>
                            </td>
                            <td><?php echo number_format($booking['total_amount'] * 1000, 0, ',', '.'); ?> VND</td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo ucfirst($booking['payment_method'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $booking['status'] === 'paid' ? 'success' : 
                                        ($booking['status'] === 'pending' ? 'warning' : 
                                        ($booking['status'] === 'cancelled' ? 'danger' : 'secondary')); 
                                ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="updateStatus(<?php echo $booking['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-info" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Booking Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" id="update_booking_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="checked_out">Checked Out</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Guest Information</h6>
                        <p id="detail_guest"></p>
                        <p id="detail_email"></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Room Information</h6>
                        <p id="detail_room"></p>
                        <p id="detail_type"></p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Booking Period</h6>
                        <p id="detail_period"></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Payment Information</h6>
                        <p id="detail_amount"></p>
                        <p id="detail_payment"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(id) {
    document.getElementById('update_booking_id').value = id;
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}

function viewDetails(booking) {
    document.getElementById('detail_guest').textContent = `Name: ${booking.full_name}`;
    document.getElementById('detail_email').textContent = `Email: ${booking.email}`;
    document.getElementById('detail_room').textContent = `Room: ${booking.room_code}`;
    document.getElementById('detail_type').textContent = `Type: ${booking.type.toUpperCase()}`;
    document.getElementById('detail_period').innerHTML = `Check-in: ${formatDate(booking.start_time)}<br>Check-out: ${formatDate(booking.end_time)}`;
    document.getElementById('detail_amount').textContent = `Amount: ${formatCurrency(booking.total_amount)}`;
    document.getElementById('detail_payment').textContent = `Payment Method: ${booking.payment_method || 'N/A'}`;
    
    new bootstrap.Modal(document.getElementById('viewDetailsModal')).show();
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleString('vi-VN');
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount * 1000);
}

function exportToCSV() {
    // Implementation for CSV export
    alert('Export functionality to be implemented');
}
</script>

</body>
</html>
