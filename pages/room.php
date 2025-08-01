<?php
session_start();

// Thêm headers để prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache"); 
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Room Booking - Online Booking System</title>
    <meta name="description" content="Online hotel room booking system with full amenities, secure payment and professional service.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="../assets/css/room.css" rel="stylesheet">
    
</head>
<body>

<?php include_once '../includes/header.php'; ?>
<?php 
include_once '../config/database.php';

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Function to check if a room is available
function isRoomAvailable($conn, $room_id) {
    $current_time = date('Y-m-d H:i:s');
    
    $query = "SELECT COUNT(*) as booking_count 
              FROM bookings 
              WHERE room_id = ? 
              AND status IN ('pending', 'paid')
              AND start_time <= ? 
              AND end_time >= ?
              AND status != 'cancelled'";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $room_id, $current_time, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['booking_count'] == 0;
}

// Get all rooms with their current booking status
$rooms_query = "SELECT r.*, 
    (SELECT COUNT(*) 
     FROM bookings b 
     WHERE b.room_id = r.id 
     AND b.status IN ('pending', 'paid')
     AND b.start_time <= NOW() 
     AND b.end_time >= NOW()
     AND b.status != 'cancelled') as is_booked
FROM rooms r
ORDER BY r.type DESC, r.room_code";

$rooms_result = $conn->query($rooms_query);
?>

<!-- Price List Modal -->
<div class="modal fade" id="priceListModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient text-white border-0" style="background-color: #5998d6;">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-tags me-2"></i>
                    Price List
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="py-3 px-4 text-center">
                                    <i class="fas fa-hotel me-2"></i>Room Type
                                </th>
                                <th class="py-3 px-4 text-center">
                                    <i class="fas fa-clock me-2"></i>Price/Hour
                                </th>
                                <th class="py-3 px-4 text-center">
                                    <i class="fas fa-sun me-2"></i>Price/Day (24h)
                                </th>
                                <th class="py-3 px-4 text-center">
                                    <i class="fas fa-calendar-week me-2"></i>Price/Week
                                </th>
                                <th class="py-3 px-4 text-center">
                                    <i class="fas fa-calendar-alt me-2"></i>Price/Month
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- VIP Rooms -->
                            <tr class="table-warning border-0">
                                <td class="py-4 px-4 text-center fw-bold">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="fas fa-crown text-warning me-2 fs-5"></i>
                                        <span class="fs-5">VIP</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="fw-bold text-warning fs-6">500.000 - 750.000</div>
                                    <small class="text-muted">VND</small>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="fw-bold">9.600.000 - 14.400.000</div>
                                    <small class="text-muted d-block">VND</small>
                                    <span class="badge bg-success mt-1">20% Off</span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="fw-bold">57.120.000 - 85.680.000</div>
                                    <small class="text-muted d-block">VND</small>
                                    <span class="badge bg-info mt-1">15% Off</span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="fw-bold">201.600.000 - 302.400.000</div>
                                    <small class="text-muted d-block">VND</small>
                                    <span class="badge bg-danger mt-1">30% Off</span>
                                </td>
                            </tr>
                            
                            <!-- Standard Rooms -->
                            <tr class="border-0">
                                <td class="py-4 px-4 text-center fw-bold">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="fas fa-bed text-primary me-2 fs-5"></i>
                                        <span class="fs-5">Standard</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="fw-bold text-primary fs-6">150.000 - 350.000</div>
                                    <small class="text-muted">VND</small>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="fw-bold">2.880.000 - 6.720.000</div>
                                    <small class="text-muted d-block">VND</small>
                                    <span class="badge bg-success mt-1">20% Off</span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="fw-bold">17.136.000 - 39.984.000</div>
                                    <small class="text-muted d-block">VND</small>
                                    <span class="badge bg-info mt-1">15% Off</span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="fw-bold">60.480.000 - 141.120.000</div>
                                    <small class="text-muted d-block">VND</small>
                                    <span class="badge bg-danger mt-1">30% Off</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
               
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                <h6 class="text-warning mb-3">
                                    <i class="fas fa-crown me-2"></i>VIP Room includes:
                                </h6>
                                <div class="row">
                                    <div class="col-6">
                                        <ul class="list-unstyled small text-start">
                                            <li><i class="fas fa-check text-success me-2"></i>24/7 Room Service</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Mini Spa in Room</li>
                                        </ul>
                                    </div>
                                    <div class="col-6">
                                        <ul class="list-unstyled small text-start">
                                            <li><i class="fas fa-check text-success me-2"></i>Free Minibar</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Best View</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-bed me-2"></i>Standard Room includes:
                                </h6>
                                <div class="row">
                                    <div class="col-6">
                                        <ul class="list-unstyled small text-start">
                                            <li><i class="fas fa-check text-success me-2"></i>Free High-Speed WiFi</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Inverter Air Conditioning</li>
                                        </ul>
                                    </div>
                                    <div class="col-6">
                                        <ul class="list-unstyled small text-start">
                                            <li><i class="fas fa-check text-success me-2"></i>Flat Screen TV</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Private Bathroom</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Room List</h2>
        <button class="btn" style="background-color: #5998d6; color: white;" data-bs-toggle="modal" data-bs-target="#priceListModal">
            <i class="fas fa-tags me-2"></i>View Price List
        </button>
    </div>

    <!-- Filter Section -->
    <div class="filter-section p-4 mb-5 rounded-3 shadow-sm">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label fw-bold"><i class="fas fa-hotel me-2"></i>Room Type</label>
                    <select class="form-select" id="typeFilter">
                        <option value="">All Room Types</option>
                        <option value="vip">VIP</option>
                        <option value="standard">Standard</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label fw-bold"><i class="fas fa-users me-2"></i>Capacity</label>
                    <select class="form-select" id="capacityFilter">
                        <option value="">All Capacities</option>
                        <option value="1-2">1-2 people</option>
                        <option value="2-3">2-3 people</option>
                        <option value="3-5">3-5 people</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label fw-bold"><i class="fas fa-door-open me-2"></i>Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                                <option value="available">Available</option>
                        <option value="booked">Booked</option>
                        <option value="in_use">In Use</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4" id="rooms-container">
        <?php
        // Pagination
        $items_per_page = 9;
        $current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($current_page - 1) * $items_per_page;

        // Handle filters
        $where_conditions = [];
        $type_filter = isset($_GET['type']) ? $_GET['type'] : '';
        $capacity_filter = isset($_GET['capacity']) ? $_GET['capacity'] : '';
        $status_filter = isset($_GET['status']) ? $_GET['status'] : '';

        if (!empty($type_filter)) {
            $type_filter = $conn->real_escape_string($type_filter);
            $where_conditions[] = "r.type = '$type_filter'";
        }
        if (!empty($status_filter)) {
            $status_filter = $conn->real_escape_string($status_filter);
            $where_conditions[] = "r.status = '$status_filter'";
        }
        if (!empty($capacity_filter)) {
            switch($capacity_filter) {
                case '1-2':
                    $where_conditions[] = "r.capacity <= 2";
                    break;
                case '2-3':
                    $where_conditions[] = "r.capacity >= 2 AND r.capacity <= 3";
                    break;
                case '3-5':
                    $where_conditions[] = "r.capacity >= 3 AND r.capacity <= 5";
                    break;
            }
        }

        // Count total rooms
        $count_sql = "SELECT COUNT(*) as total FROM rooms";
        if (!empty($where_conditions)) {
            $count_sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        $count_result = $conn->query($count_sql);
        $total_rooms = $count_result->fetch_assoc()['total'];
        $total_pages = ceil($total_rooms / $items_per_page);
        
        // Ensure current page doesn't exceed total pages
        $current_page = min($current_page, max(1, $total_pages));

        // Get room list with pagination and filters
        $sql = "SELECT r.*, 
                r.status as room_status,
                r.type as room_type,
                r.capacity as room_capacity,
                r.hourly_rate,
                (SELECT b.start_time
                 FROM bookings b 
                 WHERE b.room_id = r.id 
                 AND b.status = 'paid'
                 AND b.start_time > NOW()
                 ORDER BY b.start_time ASC 
                 LIMIT 1) as next_booking_start,
                (SELECT TIMESTAMPDIFF(MINUTE, NOW(), b.start_time)
                 FROM bookings b 
                 WHERE b.room_id = r.id 
                 AND b.status = 'paid'
                 AND b.start_time > NOW()
                 ORDER BY b.start_time ASC 
                 LIMIT 1) as minutes_until_start,
                (SELECT b.end_time 
                 FROM bookings b 
                 WHERE b.room_id = r.id 
                 AND b.status = 'paid'
                 AND b.start_time <= NOW() 
                 AND b.end_time >= NOW()
                 AND b.status != 'cancelled'
                 ORDER BY b.end_time DESC 
                 LIMIT 1) as current_booking_end,
                (SELECT CONCAT(
                    TIMESTAMPDIFF(HOUR, NOW(), b.end_time), 'h ',
                    MOD(TIMESTAMPDIFF(MINUTE, NOW(), b.end_time), 60), 'm'
                 )
                 FROM bookings b 
                 WHERE b.room_id = r.id 
                 AND b.status IN ('pending', 'paid')
                 AND b.start_time <= NOW() 
                 AND b.end_time >= NOW()
                 AND b.status != 'cancelled'
                 ORDER BY b.end_time DESC 
                 LIMIT 1) as time_remaining
                FROM rooms r";
                
        if (!empty($where_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $where_conditions);
        }
        $sql .= " ORDER BY r.type DESC, r.room_code LIMIT " . max(0, $offset) . ", $items_per_page";
        
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($room = $result->fetch_assoc()) {
                $isAvailable = $room['status'] === 'available';
                $bookingEndTime = $room['current_booking_end'];
                $timeRemaining = $room['time_remaining'];
                ?>
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="room-card card h-100 <?php echo $room['room_type'] == 'vip' ? 'vip-room' : 'standard-room'; ?>">
                        <div class="room-image-container position-relative">
                            <!-- Status Badge -->
                            <div class="status-badge <?php echo $room['room_status']; ?>">
                                <?php if($room['room_status'] == 'available'): ?>
                                    <i class="fas fa-check-circle"></i> Available
                                <?php elseif($room['room_status'] == 'booked'): ?>
                                    <i class="fas fa-calendar-check"></i> Booked
                                <?php endif; ?>
                            </div>
                            
                            <?php if($room['room_capacity']): ?>
                                <div class="capacity-badge">
                                    <i class="fas fa-users"></i> <?php echo $room['room_capacity']; ?> people
                                </div>
                            <?php endif; ?>

                            <!-- Countdown for booked rooms -->
                            <?php if($room['room_status'] == 'booked' && $room['minutes_until_start']): 
                                $hours = floor($room['minutes_until_start'] / 60);
                                $minutes = $room['minutes_until_start'] % 60;
                            ?>
                                <div class="countdown-badge">
                                    <i class="fas fa-clock"></i> Starts in: <?php echo $hours; ?>h <?php echo $minutes; ?>m
                                </div>
                            <?php endif; ?>

                            <img src="<?php echo htmlspecialchars($room['image_url']); ?>" 
                                 class="room-image card-img-top" 
                                 alt="Room <?php echo htmlspecialchars($room['room_code']); ?>" 
                                 loading="lazy" 
                                 onerror="this.src='../assets/images/room-placeholder.jpg'" 
                                 data-full-img="<?php echo htmlspecialchars($room['image_url']); ?>">
                            <?php
                                $statusClass = '';
                                $statusIcon = '';
                                $statusText = '';
                                
                                switch($room['status']) {
                                    case 'available':
                                        $statusClass = 'available';
                                        $statusIcon = 'fa-check-circle';
                                        $statusText = 'Available';
                                        break;
                                    case 'booked':
                                        $statusClass = 'booked';
                                        $statusIcon = 'fa-calendar-check';
                                        $statusText = 'Booked';
                                        break;
                                    case 'in_use':
                                        $statusClass = 'in-use';
                                        $statusIcon = 'fa-user-clock';
                                        $statusText = 'In Use';
                                        break;
                                    case 'maintenance':
                                        $statusClass = 'maintenance';
                                        $statusIcon = 'fa-tools';
                                        $statusText = 'Maintenance';
                                        break;
                                }
                            ?>
                            <div class="room-status-badge <?php echo $statusClass; ?>">
                                <i class="fas <?php echo $statusIcon; ?>"></i>
                                <?php echo $statusText; ?>
                            </div>
                            
                            <?php 
                            // Show countdown for booked rooms
                            if ($room['status'] === 'booked' && !empty($room['next_booking_start'])): 
                                $next_booking = new DateTime($room['next_booking_start']);
                                $now = new DateTime();
                                $interval = $now->diff($next_booking);
                                $hours = $interval->h + ($interval->days * 24);
                                $countdown = sprintf(
                                    "%dh %dm",
                                    $hours,
                                    $interval->i
                                );
                            ?>
                                <div class="countdown-timer upcoming">
                                    <div class="countdown-content">
                                        <i class="fas fa-clock me-1"></i>
                                        <span class="countdown-text">Starts in: <?php echo $countdown; ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php 
                            // Show countdown for in-use rooms
                            if ($room['status'] === 'in_use' && !empty($room['current_booking_start'])): 
                                $start_time = new DateTime($room['current_booking_start']);
                                $now = new DateTime();
                                $interval = $now->diff($start_time);
                                $hours_elapsed = $interval->h + ($interval->days * 24);
                            ?>
                                <div class="countdown-timer in-use">
                                    <div class="countdown-content">
                                        <i class="fas fa-clock me-1"></i>
                                        <span class="countdown-text">Time remaining: <?php echo $timeRemaining; ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="room-zoom-hint">
                                <i class="fas fa-search-plus"></i>
                                Click to zoom
                            </div>
                            <?php if($room['type'] == 'vip'): ?>
                            <div class="vip-badge">
                                <i class="fas fa-crown"></i> VIP
                            </div>
                            <?php endif; ?>
                            <div class="room-overlay">
                                <div class="room-info-wrapper">
                                    <div class="room-type-capacity">
                                        <span class="feature-badge">
                                            <i class="fas fa-users"></i>
                                            <?php echo $room['capacity']; ?> people
                                        </span>
                                    </div>
                                    <div class="room-price">
                                        <span class="price-badge">
                                            <i class="fas fa-tag"></i>
                                            <?php echo number_format($room['hourly_rate'] * 1000, 0, ',', '.'); ?> VND/hour
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-3">
                                <i class="fas <?php echo $room['type'] == 'vip' ? 'fa-crown text-warning' : 'fa-bed text-muted'; ?> me-2"></i>
                                Room <?php echo $room['room_code']; ?>
                            </h5>
                            <div class="room-amenities mb-3">
                                <span class="amenity-badge">
                                    <i class="fas fa-wifi me-1"></i> Wifi
                                </span>
                                <span class="amenity-badge">
                                    <i class="fas fa-snowflake me-1"></i> AC
                                </span>
                                <span class="amenity-badge">
                                    <i class="fas fa-tv me-1"></i> TV
                                </span>
                            </div>
                            <?php if($room['status'] == 'available'): ?>
                                <button class="book-btn btn-book-now" data-room-id="<?php echo $room['id']; ?>">
                                    <i class="fas fa-calendar-check me-2"></i>Book Now
                                </button>
                            <?php else: ?>
                                <button class="book-btn <?php echo $room['status']; ?>" disabled>
                                    <i class="fas <?php 
                                        echo $room['status'] == 'maintenance' ? 'fa-tools' : 
                                            ($room['status'] == 'in_use' ? 'fa-user-clock' : 'fa-lock'); 
                                    ?> me-2"></i>
                                    <?php 
                                        switch($room['status']) {
                                            case 'booked':
                                                echo 'Already Booked';
                                                break;
                                            case 'in_use':
                                                echo 'Currently In Use';
                                                break;
                                            case 'maintenance':
                                                echo 'Under Maintenance';
                                                break;
                                        }
                                    ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p class='text-center'>No rooms available.</p>";
        }
        ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="d-flex justify-content-center mt-5">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php include_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });

    // Show price modal after 2 seconds
    setTimeout(function() {
        const priceModal = new bootstrap.Modal(document.getElementById('priceListModal'));
        priceModal.show();
    }, 2000);

    // Update room statuses every minute
    function updateRoomStatuses() {
        fetch('../public/update_room_status_auto.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateRoomList();
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Update room list
    function updateRoomList() {
        const currentUrl = window.location.href.split('?')[0];
        const searchParams = new URLSearchParams(window.location.search);
        fetch(currentUrl + '?' + searchParams.toString())
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const roomsContainer = doc.getElementById('rooms-container');
                if (roomsContainer) {
                    document.getElementById('rooms-container').innerHTML = roomsContainer.innerHTML;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Start automatic updates
    setInterval(updateRoomStatuses, 60000); // Check every minute
});
</script>
<script src="../assets/js/room.js"></script>

</body>
</html>