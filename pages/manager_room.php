<?php
session_start();
include_once '../config/database.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle room operations (add/edit/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $room_code = $_POST['room_code'];
                $type = $_POST['type'];
                $capacity = $_POST['capacity'];
                $hourly_rate = $_POST['hourly_rate'];
                $image_url = $_POST['image_url'];
                $description = $_POST['description'];

                $sql = "INSERT INTO rooms (room_code, type, capacity, hourly_rate, image_url, description) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssiiss", $room_code, $type, $capacity, $hourly_rate, $image_url, $description);
                $stmt->execute();
                break;

            case 'edit':
                $id = $_POST['room_id'];
                $status = $_POST['status']; 
                $hourly_rate = $_POST['hourly_rate'];
                $description = $_POST['description'];
                
                // Validate status against enum values
                $valid_statuses = ['available', 'booked', 'maintenance'];
                if (!in_array($status, $valid_statuses)) {
                    die('Invalid status value');
                }

                $sql = "UPDATE rooms SET status = ?, hourly_rate = ?, description = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdsi", $status, $hourly_rate, $description, $id);
                if (!$stmt->execute()) {
                    die('Error updating room: ' . $stmt->error);
                }
                break;

            case 'delete':
                try {
                    $conn->begin_transaction();
                    
                    $id = $_POST['room_id'];
                    
                    // Kiểm tra xem phòng có booking nào không
                    $check_sql = "SELECT COUNT(*) as booking_count FROM bookings WHERE room_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("i", $id);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    $booking_count = $result->fetch_assoc()['booking_count'];
                    
                    if ($booking_count > 0) {
                        // Nếu có booking, chuyển status sang maintenance thay vì xóa
                        $update_sql = "UPDATE rooms SET status = 'maintenance' WHERE id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("i", $id);
                        $update_stmt->execute();
                        
                        $conn->commit();
                        echo "<script>alert('Room has bookings. Status changed to maintenance instead of deletion.');</script>";
                    } else {
                        // Nếu không có booking nào, tiến hành xóa
                        $delete_sql = "DELETE FROM rooms WHERE id = ?";
                        $delete_stmt = $conn->prepare($delete_sql);
                        $delete_stmt->bind_param("i", $id);
                        
                        if ($delete_stmt->execute()) {
                            $conn->commit();
                            echo "<script>alert('Room deleted successfully!');</script>";
                        } else {
                            throw new Exception("Error deleting room");
                        }
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
                }
                break;
        }
    }
}

// Get all rooms
$sql = "SELECT * FROM rooms ORDER BY room_code";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - Admin Dashboard</title>
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
                <h1>Room Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                    <i class="fas fa-plus me-2"></i>Add New Room
                </button>
            </div>

            <!-- Room List -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Room Code</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Rate/Hour</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($room = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $room['room_code']; ?></td>
                            <td>
                                <span class="badge <?php echo $room['type'] === 'vip' ? 'bg-warning' : 'bg-primary'; ?>">
                                    <?php echo strtoupper($room['type']); ?>
                                </span>
                            </td>
                            <td><?php echo $room['capacity']; ?> people</td>
                            <td><?php echo number_format($room['hourly_rate'] * 1000, 0, ',', '.'); ?> VND</td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $room['status'] === 'available' ? 'success' : 
                                        ($room['status'] === 'booked' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo ucfirst($room['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editRoom(<?php echo htmlspecialchars(json_encode($room)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteRoom(<?php echo $room['id']; ?>)">
                                    <i class="fas fa-trash"></i>
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

<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog p-3">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">Add New Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                <input type="hidden" name="action" value="add">
                 <div class="row">
               
                   <div class="mb-3 col-md-6">
                        <label class="form-label">Room Code</label>
                        <input type="text" class="form-control" name="room_code" required>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type" required>
                            <option value="standard">Standard</option>
                            <option value="vip">VIP</option>
                        </select>
                    </div>
                   </div> 
                    <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Capacity</label>
                        <input type="number" class="form-control" name="capacity" required min="1" max="5">
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Hourly Rate (VND)</label>
                        <input type="number" class="form-control" name="hourly_rate" required>
                    </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="url" class="form-control" name="image_url" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Room</button>
                </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="room_id" id="edit_room_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="available">Available</option>
                            <option value="booked">Booked</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hourly Rate (VND)</label>
                        <input type="number" class="form-control" name="hourly_rate" id="edit_hourly_rate" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editRoom(room) {
    document.getElementById('edit_room_id').value = room.id;
    document.getElementById('edit_hourly_rate').value = room.hourly_rate;
    document.getElementById('edit_description').value = room.description;
    document.querySelector('#editRoomModal select[name="status"]').value = room.status;
    
    new bootstrap.Modal(document.getElementById('editRoomModal')).show();
}

function deleteRoom(id) {
    if (confirm('Warning: This will permanently delete the room if it has no bookings.\nIf the room has existing bookings, it will be marked as under maintenance instead.\n\nDo you want to continue?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="room_id" value="${id}">
        `;
        document.body.append(form);
        form.submit();
    }
}
</script>

</body>
</html>
