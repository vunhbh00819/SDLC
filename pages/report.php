<?php
session_start();
include_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get date range
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Update revenue query to include accurate payment data
$revenue_sql = "SELECT 
    SUM(p.amount) as total_revenue,
    COUNT(DISTINCT b.id) as total_bookings,
    SUM(CASE WHEN r.type = 'vip' THEN p.amount ELSE 0 END) as vip_revenue,
    SUM(CASE WHEN r.type = 'standard' THEN p.amount ELSE 0 END) as standard_revenue,
    COUNT(DISTINCT b.user_id) as unique_customers
FROM bookings b 
JOIN rooms r ON b.room_id = r.id
JOIN payments p ON p.booking_id = b.id
WHERE b.status = 'paid'
AND b.booking_date BETWEEN ? AND ?";

$stmt = $conn->prepare($revenue_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$revenue_data = $stmt->get_result()->fetch_assoc();

// Room Type Performance
$room_type_sql = "SELECT 
    r.type,
    COUNT(b.id) as booking_count,
    SUM(b.total_amount) as revenue,
    AVG(b.total_amount) as avg_booking_value
FROM rooms r
LEFT JOIN bookings b ON r.id = b.room_id AND b.status = 'paid'
AND b.booking_date BETWEEN ? AND ?
GROUP BY r.type";

$stmt = $conn->prepare($room_type_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$room_type_stats = $stmt->get_result();

// Payment Methods Distribution
$payment_sql = "SELECT 
    p.method,
    COUNT(*) as count,
    SUM(p.amount) as total_amount
FROM payments p
JOIN bookings b ON p.booking_id = b.id
WHERE b.status = 'paid'
AND p.payment_date BETWEEN ? AND ?
GROUP BY p.method
ORDER BY total_amount DESC";

$stmt = $conn->prepare($payment_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$payment_stats = $stmt->get_result();

// Recent Bookings
$recent_sql = "SELECT 
    b.*, r.room_code, r.type,
    u.full_name, u.email,
    p.method as payment_method
FROM bookings b
JOIN rooms r ON b.room_id = r.id
JOIN users u ON b.user_id = u.id
LEFT JOIN payments p ON b.id = p.booking_id
WHERE b.booking_date BETWEEN ? AND ?
ORDER BY b.booking_date DESC
LIMIT 5";

$stmt = $conn->prepare($recent_sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$recent_bookings = $stmt->get_result();

// Add room status overview
$room_status_sql = "SELECT 
    status,
    COUNT(*) as count,
    GROUP_CONCAT(room_code) as rooms
FROM rooms
GROUP BY status";

$room_status_result = $conn->query($room_status_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
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
                        <a class="nav-link text-white" href="manager_booking.php">
                            <i class="fas fa-calendar-check me-2"></i>Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="report.php">
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
                <h1>Reports Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <form class="row g-3 align-items-center">
                        <div class="col-auto">
                            <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-auto">
                            <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Revenue Overview Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="card-title">Total Revenue</h6>
                            <h2 class="card-text"><?php echo number_format($revenue_data['total_revenue'] * 1000, 0, ',', '.'); ?> VND</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="card-title">Total Bookings</h6>
                            <h2 class="card-text"><?php echo $revenue_data['total_bookings']; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="card-title">VIP Revenue</h6>
                            <h2 class="card-text"><?php echo number_format($revenue_data['vip_revenue'] * 1000, 0, ',', '.'); ?> VND</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title">Unique Customers</h6>
                            <h2 class="card-text"><?php echo $revenue_data['unique_customers']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Room Type Performance -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Room Type Performance</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                        <th>Avg. Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($type = $room_type_stats->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo ucfirst($type['type']); ?></td>
                                        <td><?php echo $type['booking_count']; ?></td>
                                        <td><?php echo number_format($type['revenue'] * 1000, 0, ',', '.'); ?> VND</td>
                                        <td><?php echo number_format($type['avg_booking_value'] * 1000, 0, ',', '.'); ?> VND</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Payment Methods Distribution</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>Count</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($payment = $payment_stats->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo ucfirst($payment['method']); ?></td>
                                        <td><?php echo $payment['count']; ?></td>
                                        <td><?php echo number_format($payment['total_amount'] * 1000, 0, ',', '.'); ?> VND</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo $booking['full_name']; ?></td>
                                    <td>
                                        <?php echo $booking['room_code']; ?>
                                        <span class="badge <?php echo $booking['type'] === 'vip' ? 'bg-warning' : 'bg-primary'; ?>">
                                            <?php echo strtoupper($booking['type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($booking['total_amount'] * 1000, 0, ',', '.'); ?> VND</td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $booking['status'] === 'paid' ? 'success' : 
                                                ($booking['status'] === 'pending' ? 'warning' : 'secondary'); 
                                        ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($booking['booking_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Room Status Overview -->
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Room Status Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php while ($status = $room_status_result->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo ucfirst($status['status']); ?></h6>
                                        <p class="h2"><?php echo $status['count']; ?></p>
                                        <small class="text-muted"><?php echo ($status['rooms'] ?: 'No rooms'); ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>
