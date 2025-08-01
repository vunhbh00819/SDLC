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

// Select rooms with their next booking time
$sql = "SELECT r.*, 
    (SELECT b.start_time 
     FROM bookings b 
     WHERE b.room_id = r.id 
     AND b.status = 'paid'
     AND b.start_time > NOW()
     ORDER BY b.start_time ASC 
     LIMIT 1) as next_booking_start
FROM rooms r";

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
    // Only show available rooms by default unless status filter is specified
    if (!isset($_GET['status'])) {
        $sql .= " AND (r.status = 'available' OR r.status = 'maintenance')";
    }
}

// Order by type and room code
$sql .= " ORDER BY r.type DESC, r.room_code ASC";
$result = $conn->query($sql);
$rooms = [];

if ($result->num_rows > 0) {
    while($room = $result->fetch_assoc()) {
        $status = $room['status'];
        $isAvailable = $status == 'available';
        $isBooked = $status == 'booked';
        $isInUse = $status == 'in_use';
        $isMaintenance = $status == 'maintenance';
        
        // Thiết lập trạng thái và style
        $statusClass = '';
        $statusText = '';
        $statusIcon = '';
        
        // Xác định trạng thái và style cho từng loại
        switch($status) {
            case 'available':
                $statusClass = 'available';
                $statusText = 'Trống';
                $statusIcon = 'fa-door-open';
                break;
            case 'booked':
                $statusClass = 'booked';
                $statusText = 'Đã đặt';
                $statusIcon = 'fa-calendar-check';
                break;
            case 'in_use':
                $statusClass = 'in-use';
                $statusText = 'Đang sử dụng';
                $statusIcon = 'fa-user-clock';
                break;
            case 'maintenance':
                $statusClass = 'maintenance';
                $statusText = 'Đang bảo trì';
                $statusIcon = 'fa-tools';
                break;
            default:
                $statusClass = 'unavailable';
                $statusText = 'Không khả dụng';
                $statusIcon = 'fa-ban';
                break;
        }

        // Kiểm tra phòng có booking sắp tới không
        if ($room['next_booking_start'] && $isAvailable) {
            $next_booking = new DateTime($room['next_booking_start']);
            $now = new DateTime();
            $interval = $now->diff($next_booking);
            
            // Nếu thời gian đặt phòng sắp đến (trong vòng 1 giờ)
            if ($interval->days == 0 && $interval->h < 1) {
                $statusClass = 'soon';
                $statusText = 'Sắp được sử dụng';
                $statusIcon = 'fa-clock';
            }
        }
        
        // Bắt đầu tạo HTML cho card phòng
        $html = '<div class="col-lg-4 col-md-6" data-aos="fade-up">';
        $html .= '<div class="room-card card h-100 ' . ($room['type'] == 'vip' ? 'vip-room' : 'standard-room') . '">';
        $html .= '<div class="room-image-container position-relative">';
        $html .= '<img src="' . $room['image_url'] . '" class="room-image card-img-top" alt="Room ' . $room['room_code'] . '" loading="lazy" data-full-img="' . $room['image_url'] . '">';
        
        // Hiển thị badge trạng thái
        $html .= '<div class="room-status-badge ' . $statusClass . '">';
        $html .= '<i class="fas ' . $statusIcon . '"></i> ' . $statusText;
        $html .= '</div>';
        
        // Hiển thị countdown nếu sắp có booking
        if ($room['next_booking_start'] && $isAvailable) {
            $html .= '<div class="next-booking-countdown" data-room-id="' . $room['id'] . '" data-start-time="' . $room['next_booking_start'] . '">';
            $html .= '<i class="fas fa-hourglass-half"></i>';
            $html .= '<span data-countdown="' . $room['id'] . '">Đang tải...</span>';
            $html .= '</div>';
        }

        // Thêm nút zoom và badge VIP
        $html .= '<div class="room-zoom-hint">';
        $html .= '<i class="fas fa-search-plus"></i>';
        $html .= 'Nhấn để phóng to';
        $html .= '</div>';
        
        if ($room['type'] == 'vip') {
            $html .= '<div class="vip-badge">';
            $html .= '<i class="fas fa-crown"></i> VIP';
            $html .= '</div>';
        }

        // Thông tin phòng
        $html .= '<div class="room-overlay">';
        $html .= '<div class="room-info-wrapper">';
        $html .= '<div class="room-type-capacity">';
        $html .= '<span class="feature-badge">';
        $html .= '<i class="fas fa-users"></i> ';
        $html .= $room['capacity'] . ' người';
        $html .= '</span>';
        $html .= '</div>';
        $html .= '<div class="room-price">';
        $html .= '<span class="price-badge">';
        $html .= '<i class="fas fa-tag"></i> ';
        $html .= number_format($room['hourly_rate'] * 1000, 0, ',', '.') . ' VND/giờ';
        $html .= '</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Thân card
        $html .= '<div class="card-body d-flex flex-column">';
        $html .= '<h5 class="card-title mb-3">';
        $html .= '<i class="fas ' . ($room['type'] == 'vip' ? 'fa-crown text-warning' : 'fa-bed text-muted') . ' me-2"></i>';
        $html .= 'Phòng ' . $room['room_code'];
        $html .= '</h5>';
        
        // Tiện ích
        $html .= '<div class="room-amenities mb-3">';
        $html .= '<span class="amenity-badge"><i class="fas fa-wifi me-1"></i> Wifi</span>';
        $html .= '<span class="amenity-badge"><i class="fas fa-snowflake me-1"></i> Điều hòa</span>';
        $html .= '<span class="amenity-badge"><i class="fas fa-tv me-1"></i> TV</span>';
        $html .= '</div>';
        
        // Nút đặt phòng
        if ($isAvailable) {
            $html .= '<button class="book-btn btn-book-now" data-room-id="' . $room['id'] . '">';
            $html .= '<i class="fas fa-calendar-check me-2"></i>Đặt ngay';
            $html .= '</button>';
        } else {
            $html .= '<button class="book-btn booked" disabled>';
            $html .= '<i class="fas fa-lock me-2"></i>Không khả dụng';
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
