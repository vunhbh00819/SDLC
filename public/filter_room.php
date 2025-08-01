<?php
include_once '../config/database.php';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$capacity = isset($_GET['capacity']) ? $_GET['capacity'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$where_conditions = [];
if (!empty($type)) {
    $where_conditions[] = "type = '$type'";
}
if (!empty($status)) {
   $where_conditions[] = "status = '$status'";
}
if (!empty($capacity)) {
    switch($capacity) {
        case '1-2':
            $where_conditions[] = "capacity <= 2";
            break;
        case '2-3':
            $where_conditions[] = "capacity >= 2 AND capacity <= 3";
            break;
        case '3-5':
            $where_conditions[] = "capacity >= 3 AND capacity <= 5";
            break;
    }
}
$sql = "SELECT r.*, 
    (SELECT b.start_time 
     FROM bookings b 
     WHERE b.room_id = r.id 
     AND b.status IN ('paid', 'pending')
     AND b.end_time > NOW()
     ORDER BY b.start_time ASC 
     LIMIT 1) as next_booking_start
FROM rooms r";

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
} else {
    // Only show available rooms by default when no filters are applied
    $sql .= " WHERE r.status = 'available'";
}

// Order by type and room code
$sql .= " ORDER BY r.type DESC, r.room_code ASC";
$result = $conn->query($sql);
$rooms = [];

if ($result->num_rows > 0) {
    while($room = $result->fetch_assoc()) {
        // Xác định trạng thái phòng
        $status = $room['status'];
        $isAvailable = $status == 'available';
        $isBooked = $status == 'booked';
        $isInUse = $status == 'in_use';
        $isMaintenance = $status == 'maintenance';
        
        // Thiết lập class, text và icon cho từng trạng thái
        $statusClass = '';
        $statusText = '';
        $statusIcon = '';
        
        switch($status) {
            case 'available':
                $statusClass = 'available';
                $statusText = 'Available';
                $statusIcon = 'fa-check-circle';
                break;
            case 'booked':
                $statusClass = 'booked';
                $statusText = 'Booked';
                $statusIcon = 'fa-calendar-check';
                break;
            case 'in_use':
                $statusClass = 'in-use';
                $statusText = 'In Use';
                $statusIcon = 'fa-user-clock';
                break;
            case 'maintenance':
                $statusClass = 'maintenance';
                $statusText = 'Maintenance';
                $statusIcon = 'fa-tools';
                break;
            default:
                $statusClass = 'maintenance';
                $statusText = 'Unavailable';
                $statusIcon = 'fa-ban';
                break;
        }

        // Check if room has upcoming booking
        if ($room['next_booking_start'] && $isAvailable) {
            $next_booking = new DateTime($room['next_booking_start']);
            $now = new DateTime();
            $interval = $now->diff($next_booking);
            
            if ($interval->days == 0 && $interval->h < 2) { // Changed to 2 hours window
                $statusClass = 'soon';
                $statusText = 'Reserved Soon';
                $statusIcon = 'fa-clock';
            }
        }
        $html = '<div class="col-lg-4 col-md-6" data-aos="fade-up">';
        $html .= '<div class="room-card card h-100 ' . ($room['type'] == 'vip' ? 'vip-room' : 'standard-room') . '">';
        $html .= '<div class="room-image-container position-relative">';
        $html .= '<img src="' . $room['image_url'] . '" class="room-image card-img-top" alt="Room ' . $room['room_code'] . '" loading="lazy" data-full-img="' . $room['image_url'] . '">';
        $html .= '<div class="room-status-badge ' . $statusClass . '">';
        $html .= '<i class="fas ' . $statusIcon . '"></i> ' . $statusText;
        $html .= '</div>';
        
        // Add countdown timer if room is available but has upcoming booking
        if ($room['next_booking_start'] && $isAvailable) {
            $html .= '<div class="next-booking-countdown" data-room-id="' . $room['id'] . '" data-start-time="' . $room['next_booking_start'] . '">';
            $html .= '<i class="fas fa-hourglass-half"></i>';
            $html .= '<span data-countdown="' . $room['id'] . '">Loading...</span>';
            $html .= '</div>';
        }
        $html .= '<div class="room-zoom-hint">';
        $html .= '<i class="fas fa-search-plus"></i>';
        $html .= 'Click to zoom';
        $html .= '</div>';
        
        if ($room['type'] == 'vip') {
            $html .= '<div class="vip-badge">';
            $html .= '<i class="fas fa-crown"></i> VIP';
            $html .= '</div>';
        }
        
        $html .= '<div class="room-overlay">';
        $html .= '<div class="room-info-wrapper">';
        $html .= '<div class="room-type-capacity">';
        $html .= '<span class="feature-badge">';
        $html .= '<i class="fas fa-users"></i> ';
        $html .= $room['capacity'] . ' people';
        $html .= '</span>';
        $html .= '</div>';
        $html .= '<div class="room-price">';
        $html .= '<span class="price-badge">';
        $html .= '<i class="fas fa-tag"></i> ';
        $html .= number_format($room['hourly_rate'] * 1000, 0, ',', '.') . ' VND/hour';
        $html .= '</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="card-body d-flex flex-column">';
        $html .= '<h5 class="card-title mb-3">';
        $html .= '<i class="fas ' . ($room['type'] == 'vip' ? 'fa-crown text-warning' : 'fa-bed text-muted') . ' me-2"></i>';
        $html .= 'Room ' . $room['room_code'];
        $html .= '</h5>';
        
        $html .= '<div class="room-amenities mb-3">';
        $html .= '<span class="amenity-badge"><i class="fas fa-wifi me-1"></i> Wifi</span>';
        $html .= '<span class="amenity-badge"><i class="fas fa-snowflake me-1"></i> AC</span>';
        $html .= '<span class="amenity-badge"><i class="fas fa-tv me-1"></i> TV</span>';
        $html .= '</div>';
        
        if ($isAvailable) {
            $html .= '<button class="book-btn btn-book-now" data-room-id="' . $room['id'] . '">';
            $html .= '<i class="fas fa-calendar-check me-2"></i>Book Now';
            $html .= '</button>';
        } else {
            $html .= '<button class="book-btn booked" disabled>';
            $html .= '<i class="fas fa-lock me-2"></i>Already Booked';
            $html .= '</button>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $rooms[] = $html;
    }
}

// Handle pagination
$items_per_page = 9;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$total_rooms = count($rooms);
$total_pages = max(1, ceil($total_rooms / $items_per_page));

// Ensure current page doesn't exceed total pages
$current_page = min($current_page, $total_pages);

// Get items for current page
$start = max(0, ($current_page - 1) * $items_per_page);
$rooms_page = array_slice($rooms, $start, $items_per_page);

// Return result as JSON
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'rooms' => $rooms_page,
    'total' => $total_rooms,
    'totalPages' => $total_pages,
    'currentPage' => $current_page
]);
