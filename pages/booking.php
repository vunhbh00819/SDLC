<?php
session_start();

// Thêm headers để prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include_once '../config/database.php';

// Kiểm tra xem có room_id được truyền không
if (!isset($_GET['room_id']) || empty($_GET['room_id'])) {
    header('Location: room.php');
    exit();
}

$room_id = (int)$_GET['room_id'];

// Get room information
$sql = "SELECT * FROM rooms WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: room.php');
    exit();
}

$room = $result->fetch_assoc();
$isAvailable = $room['status'] == 'available';

// Xử lý booking
$booking_success = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_room'])) {
    // Kiểm tra đăng nhập - check both session variables
    if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
        $error_message = 'login_required';
    } else {
        $user_name = trim($_POST['user_name']);
        $user_email = trim($_POST['user_email']);
        $user_phone = trim($_POST['user_phone']);
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $guests = (int)$_POST['guests'];
        $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
        $duration_type = isset($_POST['duration_type']) ? $_POST['duration_type'] : 'hour';
        $duration_quantity = isset($_POST['duration_quantity']) ? (int)$_POST['duration_quantity'] : 1;

        // Debug: Log the received data
        error_log("Booking attempt: " . json_encode($_POST));
        error_log("Duration type: " . $duration_type);
        error_log("Duration quantity: " . $duration_quantity);
        
        // Validate all required fields
        if (empty($user_name) || empty($user_email) || empty($user_phone) || empty($check_in) || empty($check_out) || empty($payment_method)) {
            $error_message = 'Please fill in all required fields and select a payment method.';
        } else {
            // Get user_id from session
            $user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

            if (!$user_id) {
                $error_message = 'User session not found. Please login again.';
            } else {
                // Validate payment method
                $valid_payment_methods = ['vietcombank', 'techcombank', 'bidv'];
                if (!in_array($payment_method, $valid_payment_methods)) {
                    $error_message = 'Invalid payment method selected.';
                } else {
                    // Set timezone to Vietnam (UTC+7)
                    date_default_timezone_set('Asia/Ho_Chi_Minh');
                    
                    // Parse check-in time properly
                    $start_time = new DateTime($check_in);
                    $start_time->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
                    
                    // Calculate end time based on duration for hourly bookings
                    if ($duration_type === 'hour') {
                        $end_time = clone $start_time;
                        $interval = new DateInterval('PT' . $duration_quantity . 'H'); // PT1H for 1 hour
                        $end_time->add($interval);
                        $check_out = $end_time->format('Y-m-d H:i:s');
                    } else {
                        $end_time = new DateTime($check_out);
                        $end_time->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
                    }
                    
                    $current_time = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));

                    // Check if check-in is in the future
                    if ($start_time <= $current_time) {
                        $error_message = 'Check-in time must be in the future.';
                    } else if ($end_time <= $start_time) {
                        $error_message = 'Check-out time must be after check-in time.';
                    } else {
                        // Check for overlapping bookings
                        $check_overlap_sql = "SELECT COUNT(*) as overlap_count 
                            FROM bookings 
                            WHERE room_id = ? 
                            AND status IN ('pending', 'paid')
                            AND (
                                (start_time BETWEEN ? AND ?) OR
                                (end_time BETWEEN ? AND ?) OR
                                (start_time <= ? AND end_time >= ?)
                            )";
                        
                        $check_stmt = $conn->prepare($check_overlap_sql);
                        $check_stmt->bind_param("issssss", 
                            $room_id, 
                            $check_in_db, $check_out_db,
                            $check_in_db, $check_out_db,
                            $check_in_db, $check_out_db
                        );
                        $check_stmt->execute();
                        $overlap_result = $check_stmt->get_result();
                        $overlap_count = $overlap_result->fetch_assoc()['overlap_count'];

                        if ($overlap_count > 0) {
                            $error_message = 'This room is already booked for the selected time period.';
                        } else {
                            // Calculate total amount based on duration type and quantity (from frontend)
                            $base_rate = $room['hourly_rate'];
                        switch($duration_type) {
                            case 'day':
                                $rate_per_unit = $base_rate * 24 * 0.8; // 20% off daily rate
                                break;
                            case 'week':
                                $rate_per_unit = $base_rate * 24 * 7 * 0.65; // 35% off weekly rate
                                break;
                            case 'month':
                                $rate_per_unit = $base_rate * 24 * 30 * 0.5; // 50% off monthly rate
                                break;
                            case 'hour':
                            default:
                                $rate_per_unit = $base_rate;
                                break;
                        }
                        
                        // Use quantity from frontend instead of calculating from dates
                        $total_amount = ceil($rate_per_unit * $duration_quantity);

                        // Verify that the calculated checkout time matches the expected duration
                        $expected_duration_hours = 0;
                        switch($duration_type) {
                            case 'hour':
                                $expected_duration_hours = $duration_quantity;
                                break;
                            case 'day':
                                $expected_duration_hours = $duration_quantity * 24;
                                break;
                            case 'week':
                                $expected_duration_hours = $duration_quantity * 24 * 7;
                                break;
                            case 'month':
                                $expected_duration_hours = $duration_quantity * 24 * 30;
                                break;
                        }
                        
                        // Calculate actual duration from dates
                        $duration = $start_time->diff($end_time);
                        $actual_hours = ($duration->days * 24) + $duration->h + ($duration->i / 60);
                        
                        // Allow small tolerance for rounding (5 minutes)
                        if (abs($actual_hours - $expected_duration_hours) > 0.083) { // 0.083 hours = 5 minutes
                            error_log("Duration mismatch: Expected {$expected_duration_hours} hours, got {$actual_hours} hours");
                            $error_message = 'Duration calculation error. Please try again.';
                        } else {
                            // Convert to database format
                            $check_in_db = $start_time->format('Y-m-d H:i:s');
                            $check_out_db = $end_time->format('Y-m-d H:i:s');

                        // Begin transaction
                        $conn->begin_transaction();

                        try {
                            // Insert booking
                            $insert_sql = "INSERT INTO bookings (user_id, room_id, start_time, end_time, total_amount, status) VALUES (?, ?, ?, ?, ?, 'pending')";
                            $insert_stmt = $conn->prepare($insert_sql);
                            $insert_stmt->bind_param("iissd", $user_id, $room_id, $check_in_db, $check_out_db, $total_amount);

                            if ($insert_stmt->execute()) {
                                $booking_id = $conn->insert_id;

                                // Insert payment record
                                $payment_sql = "INSERT INTO payments (booking_id, amount, method) VALUES (?, ?, ?)";
                                $payment_stmt = $conn->prepare($payment_sql);
                                $payment_amount = $total_amount;
                                $payment_stmt->bind_param("ids", $booking_id, $payment_amount, $payment_method);

                                if ($payment_stmt->execute()) {
                                    // Update booking status to paid
                                    $update_booking_sql = "UPDATE bookings SET status = 'paid' WHERE id = ?";
                                    $update_booking_stmt = $conn->prepare($update_booking_sql);
                                    $update_booking_stmt->bind_param("i", $booking_id);
                                    $update_booking_stmt->execute();

                                    // Update room status to booked
                                    $update_room_sql = "UPDATE rooms SET status = 'booked' WHERE id = ?";
                                    $update_room_stmt = $conn->prepare($update_room_sql);
                                    $update_room_stmt->bind_param("i", $room_id);
                                    $update_room_stmt->execute();

                                    // Commit transaction
                                    $conn->commit();

                                    $_SESSION['booking_data'] = [
                                        'booking_id' => $booking_id,
                                        'total_amount' => $total_amount,
                                        'payment_method' => $payment_method,
                                        'room_code' => $room['room_code']
                                    ];

                                    $booking_success = true;
                                } else {
                                    throw new Exception('Payment insertion failed: ' . $payment_stmt->error);
                                }
                            } else {
                                throw new Exception('Booking insertion failed: ' . $insert_stmt->error);
                            }
                        } catch (Exception $e) {
                            $conn->rollback();
                            $error_message = 'Booking failed. Please try again. Error: ' . $e->getMessage();
                        }
                        }
                    }
                }
            }
        }
    }
}
} // <-- Add this closing brace to fix the unclosed block

// Get similar rooms (same type but different ID)
$similar_sql = "SELECT * FROM rooms WHERE type = ? AND id != ? LIMIT 3";
$similar_stmt = $conn->prepare($similar_sql);
$similar_stmt->bind_param("si", $room['type'], $room_id);
$similar_stmt->execute();
$similar_result = $similar_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room <?php echo $room['room_code']; ?> - Hotel Room Booking System</title>
    <meta name="description" content="Book room <?php echo $room['room_code']; ?> - <?php echo $room['type'] == 'vip' ? 'VIP' : 'Standard'; ?> with special rates">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="../assets/css/room.css" rel="stylesheet">
    <link href="../assets/css/booking.css" rel="stylesheet">
</head>

<body>
    <?php include_once '../includes/header.php'; ?>

    <?php if ($booking_success): ?>
        <!-- Success Modal -->
        <!-- Success Modal -->
<div class="modal fade show" id="successModal" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-4 border-0">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Booking Successful!
                </h5>
            </div>
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 class="text-success fw-bold mb-3">Your room has been booked successfully!</h4>

                <div class="booking-details bg-light p-3 px-4 rounded-3 text-start mx-auto" style="max-width: 400px;">
                    <p class="mb-1"><strong>Room:</strong> <?php echo $room['room_code']; ?></p>
                    <?php if (isset($_SESSION['booking_data'])): ?>
                        <p class="mb-1"><strong>Booking ID:</strong> #<?php echo $_SESSION['booking_data']['booking_id']; ?></p>
                        <p class="mb-1"><strong>Total Amount:</strong> <?php echo number_format($_SESSION['booking_data']['total_amount'] * 1000, 0, ',', '.'); ?> VND</p>
                        <p class="mb-0"><strong>Payment Method:</strong> <?php echo ucfirst($_SESSION['booking_data']['payment_method']); ?></p>
                    <?php endif; ?>
                </div>

                <p class="text-muted mt-3">You will receive a confirmation email shortly.</p>
            </div>

            <div class="modal-footer justify-content-center border-0 pb-4">
                <button type="button" class="btn btn-success px-4" onclick="closeSuccessModal()">
                    <i class="fas fa-check me-2"></i>Continue
                </button>
                <a href="room.php" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-2"></i>Back to Rooms
                </a>
            </div>
        </div>
    </div>
</div>

    <?php endif; ?>

    <?php if ($error_message && $error_message != 'login_required'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Error!</strong> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="booking-container">
        <div class="container-fluid">
            <!-- Back Button -->
            <a href="room.php" class="back-to-rooms">
                <i class="fas fa-arrow-left"></i>
                Back to rooms list
            </a>

            <!-- Room Detail Card -->
            <div class="room-detail-card" data-aos="fade-up">
                <div class="row g-0">
                    <!-- Left: Image Gallery -->
                    <div class="col-lg-6">
                        <div class="room-image-gallery">
                            <img src="<?php echo $room['image_url']; ?>"
                                class="main-room-image"
                                alt="Room <?php echo $room['room_code']; ?>"
                                id="mainRoomImage">

                            <!-- Room Status Badge -->
                            <div class="room-status-badge <?php echo $isAvailable ? 'available' : 'booked'; ?>"
                                style="position: absolute; top: 20px; right: 20px;">
                                <i class="fas <?php echo $isAvailable ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                <?php echo $isAvailable ? 'Available' : 'Booked'; ?>
                            </div>

                            <?php if ($room['type'] == 'vip'): ?>
                                <div class="vip-badge" style="position: absolute; top: 20px; left: 20px;">
                                    <i class="fas fa-crown"></i> VIP
                                </div>
                            <?php endif; ?>

                            <!-- Image Controls -->
                            <div class="image-controls">
                                <button class="image-control-btn" onclick="zoomImage()">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="image-control-btn" onclick="resetImage()">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Room Information -->
                    <div class="col-lg-6">
                        <div class="room-info-section">
                            <h4 class="mb-3">
                                <i class="fas <?php echo $room['type'] == 'vip' ? 'fa-crown text-warning' : 'fa-bed text-muted'; ?> me-2"></i>
                                Room <?php echo $room['room_code']; ?>
                            </h4>

                            <div class="room-details">
                                <p class="mb-2">
                                    <strong><i class="fas fa-tag me-2"></i>Room Type:</strong>
                                    <?php echo $room['type'] == 'vip' ? 'VIP' : 'Standard'; ?>
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-users me-2"></i>Capacity:</strong>
                                    <?php echo $room['capacity']; ?> people
                                </p>
                                <p class="mb-2">
                                    <strong><i class="fas fa-dollar-sign me-2"></i>Base Price:</strong>
                                    <?php echo number_format($room['hourly_rate'] * 1000, 0, ',', '.'); ?> VND/hour
                                </p>
                                <?php if (!empty($room['description'])): ?>
                                    <div class="room-description mt-3">
                                        <h6><i class="fas fa-info-circle me-2"></i>Room Description:</h6>
                                        <p class="text-muted"><?php echo $room['description']; ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Amenities -->
                            <div class="amenity-list">
                                <div class="amenity-item">
                                    <div class="amenity-icon">
                                        <i class="fas fa-wifi"></i>
                                    </div>
                                    <span>Free WiFi</span>
                                </div>
                                <div class="amenity-item">
                                    <div class="amenity-icon">
                                        <i class="fas fa-snowflake"></i>
                                    </div>
                                    <span>Air Conditioning</span>
                                </div>
                                <div class="amenity-item">
                                    <div class="amenity-icon">
                                        <i class="fas fa-tv"></i>
                                    </div>
                                    <span>Flat-screen TV</span>
                                </div>
                                <div class="amenity-item">
                                    <div class="amenity-icon">
                                        <i class="fas fa-bath"></i>
                                    </div>
                                    <span>Private Bathroom</span>
                                </div>
                                <?php if ($room['type'] == 'vip'): ?>
                                    <div class="amenity-item">
                                        <div class="amenity-icon">
                                            <i class="fas fa-concierge-bell"></i>
                                        </div>
                                        <span>24/7 Room Service</span>
                                    </div>
                                    <div class="amenity-item">
                                        <div class="amenity-icon">
                                            <i class="fas fa-spa"></i>
                                        </div>
                                        <span>Mini Spa</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!$isAvailable): ?>
                                <div class="alert alert-danger mt-4">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    This room is currently unavailable. Please choose another room.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Calculator Section -->
            <?php if ($isAvailable): ?>
                <div class="price-calculator-section py-5" data-aos="fade-up">
                    <div class="price-calculator container-fluid bg-light p-4 rounded shadow">
                        <h3 class="mb-4 text-center text-white">
                            <i class="fas fa-calculator me-2"></i>
                            Calculate room price & book now
                        </h3>

                        <!-- Duration Type Selector -->
                        <div class="duration-selector d-grid gap-3 mb-4" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                            <div class="duration-option text-center border rounded p-3 active" data-type="hour" data-rate="<?php echo $room['hourly_rate']; ?>" onclick="selectDurationType(this)">
                                <div><i class="fas fa-clock fa-lg mb-2 text-info"></i></div>
                                <div><strong>Hourly</strong></div>
                                <div><?php echo number_format($room['hourly_rate'] * 1000, 0, ',', '.'); ?> VND</div>
                            </div>
                            <div class="duration-option text-center border rounded p-3" data-type="day" data-rate="<?php echo $room['hourly_rate'] * 24 * 0.8; ?>" onclick="selectDurationType(this)">
                                <div><i class="fas fa-sun fa-lg mb-2 text-warning"></i></div>
                                <div><strong>Daily</strong></div>
                                <div><?php echo number_format($room['hourly_rate'] * 24 * 0.8 * 1000, 0, ',', '.'); ?> VND</div>
                                <small class="text-success">(20% off)</small>
                            </div>
                            <div class="duration-option text-center border rounded p-3" data-type="week" data-rate="<?php echo $room['hourly_rate'] * 24 * 7 * 0.65; ?>" onclick="selectDurationType(this)">
                                <div><i class="fas fa-calendar-week fa-lg mb-2 text-secondary"></i></div>
                                <div><strong>Weekly</strong></div>
                                <div><?php echo number_format($room['hourly_rate'] * 24 * 7 * 0.65 * 1000, 0, ',', '.'); ?> VND</div>
                                <small class="text-success">(35% off)</small>
                            </div>
                            <div class="duration-option text-center border rounded p-3" data-type="month" data-rate="<?php echo $room['hourly_rate'] * 24 * 30 * 0.5; ?>" onclick="selectDurationType(this)">
                                <div><i class="fas fa-calendar-alt fa-lg mb-2 text-danger"></i></div>
                                <div><strong>Monthly</strong></div>
                                <div><?php echo number_format($room['hourly_rate'] * 24 * 30 * 0.5 * 1000, 0, ',', '.'); ?> VND</div>
                                <small class="text-success">(50% off)</small>
                            </div>
                        </div>

                        <!-- Calculator Controls -->
                        <div class="calculator-controls container bg-light rounded shadow-sm p-4 mt-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-4">
                                <!-- Quantity Control -->
                                <div class="quantity-control d-flex align-items-center gap-2">
                                    <label class="fw-bold mb-0 text-dark">Quantity:</label>
                                    <button class="btn btn-outline-secondary" onclick="changeQuantity(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" id="quantityInput" class="form-control text-center" style="width: 70px;" value="1" min="1" max="10">
                                    <button class="btn btn-outline-secondary" onclick="changeQuantity(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>

                                <!-- Total Price -->
                                <div class="total-price text-center bg-white p-3 rounded border">
                                    <div class="fw-bold text-dark">Total Payment:</div>
                                    <div id="totalAmount" class="fs-5 fw-bold text-success">550,000 VND</div>
                                    <div id="discountInfo" style="font-size: 0.85rem; color: #555;"></div>
                                </div>

                                <!-- Booking Button -->
                                <div>
                                    <button class="btn btn-lg px-4 text-white" style="background-color: #5998D6;" onclick="toggleBookingForm()">
                                        <i class="fas fa-credit-card me-2"></i> BOOK NOW
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Booking Form Section (Hidden by default) -->
            <div class="booking-form-section mt-5" id="bookingFormSection" style="display: none;" data-aos="fade-up">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow-lg border-0">
                                <div class="card-header text-white py-4" style="background: linear-gradient(135deg, #5998d6 0%, #4a7eb8 100%);">
                                    <h3 class="mb-0 text-center">
                                        <i class="fas fa-calendar-check me-2"></i>Book Room <?php echo $room['room_code']; ?>
                                    </h3>
                                </div>
                                <div class="card-body p-5">
                                    <form method="POST" id="inlineBookingForm" onsubmit="return validateBookingForm()">
                                        <input type="hidden" name="duration_type" id="duration_type" value="hour">
                                        <input type="hidden" name="duration_quantity" id="duration_quantity" value="1">
                                        <div class="row g-4">
                                            <!-- Guest Information -->
                                            <div class="col-lg-8">
                                                <div class="mb-5">
                                                    <h5 class="border-bottom pb-3 mb-4 text-primary">
                                                        <i class="fas fa-user me-2"></i>Guest Information
                                                    </h5>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label for="inline_user_name" class="form-label fw-bold">Full Name *</label>
                                                            <input type="text" class="form-control form-control-lg" id="inline_user_name" name="user_name" required
                                                                value="<?php echo isset($_SESSION['user']['full_name']) ? $_SESSION['user']['full_name'] : ''; ?>"
                                                                placeholder="Enter your full name">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="inline_user_email" class="form-label fw-bold">Email *</label>
                                                            <input type="email" class="form-control form-control-lg" id="inline_user_email" name="user_email" required
                                                                value="<?php echo isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : ''; ?>"
                                                                placeholder="Enter your email">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="inline_user_phone" class="form-label fw-bold">Phone Number *</label>
                                                            <input type="tel" class="form-control form-control-lg" id="inline_user_phone" name="user_phone" required
                                                                value="<?php echo isset($_SESSION['user']['phone_number']) ? $_SESSION['user']['phone_number'] : ''; ?>"
                                                                placeholder="Enter your phone number">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="inline_guests" class="form-label fw-bold">Number of Guests *</label>
                                                            <select class="form-select form-select-lg" id="inline_guests" name="guests" required>
                                                                <?php for ($i = 1; $i <= $room['capacity']; $i++): ?>
                                                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?></option>
                                                                <?php endfor; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Booking Details -->
                                                <div class="mb-5">
                                                    <h5 class="border-bottom pb-3 mb-4 text-primary">
                                                        <i class="fas fa-calendar me-2"></i>Booking Details
                                                    </h5>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label for="inline_check_in" class="form-label fw-bold">Check-in Date & Time *</label>
                                                            <input type="datetime-local" class="form-control form-control-lg" id="inline_check_in" name="check_in" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="inline_check_out" class="form-label fw-bold">Check-out Date & Time *</label>
                                                            <input type="datetime-local" class="form-control form-control-lg" id="inline_check_out" name="check_out" required readonly>
                                                            <small class="text-muted">Automatically calculated based on duration and quantity selected above</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Payment Method -->
                                                <div class="mb-4">
                                                    <h5 class="border-bottom pb-3 mb-4 text-primary">
                                                        <i class="fas fa-credit-card me-2"></i>Payment Method
                                                    </h5>
                                                    <div class="row g-3">
                                                        <div class="col-md-4">
                                                            <div class="payment-option-card" onclick="selectInlinePayment('vietcombank')">
                                                                <div class="card payment-card h-100 text-center">
                                                                    <div class="card-body d-flex flex-column justify-content-center">
                                                                        <i class="fas fa-university fa-2x text-primary mb-2"></i>
                                                                        <h6 class="card-title mb-1">Vietcombank</h6>
                                                                        <small class="text-muted">Pay via QR Code</small>
                                                                        <input type="radio" name="payment_method" value="vietcombank" id="inline_vietcombank" class="d-none">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="payment-option-card" onclick="selectInlinePayment('techcombank')">
                                                                <div class="card payment-card h-100 text-center">
                                                                    <div class="card-body d-flex flex-column justify-content-center">
                                                                        <i class="fas fa-university fa-2x text-success mb-2"></i>
                                                                        <h6 class="card-title mb-1">Techcombank</h6>
                                                                        <small class="text-muted">Pay via QR Code</small>
                                                                        <input type="radio" name="payment_method" value="techcombank" id="inline_techcombank" class="d-none">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="payment-option-card" onclick="selectInlinePayment('bidv')">
                                                                <div class="card payment-card h-100 text-center">
                                                                    <div class="card-body d-flex flex-column justify-content-center">
                                                                        <i class="fas fa-university fa-2x text-warning mb-2"></i>
                                                                        <h6 class="card-title mb-1">BIDV</h6>
                                                                        <small class="text-muted">Pay via QR Code</small>
                                                                        <input type="radio" name="payment_method" value="bidv" id="inline_bidv" class="d-none">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Booking Summary -->
                                            <div class="col-lg-4">
                                                <div class="card bg-light border-2 sticky-top" style="top: 100px;">
                                                    <div class="card-header bg-white border-bottom-2">
                                                        <h5 class="card-title mb-0 text-center">
                                                            <i class="fas fa-receipt me-2 text-primary"></i>Booking Summary
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Room:</span>
                                                            <span class="fw-bold"><?php echo $room['room_code']; ?> (<?php echo ucfirst($room['type']); ?>)</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Duration:</span>
                                                            <span class="fw-bold" id="summaryDuration">-</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Guests:</span>
                                                            <span class="fw-bold" id="summaryGuests">1</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Check-in:</span>
                                                            <span class="fw-bold" id="summaryCheckIn">-</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <span>Check-out:</span>
                                                            <span class="fw-bold" id="summaryCheckOut">-</span>
                                                        </div>
                                                        <hr>
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <span class="h6">Total Amount:</span>
                                                            <span class="h5 text-success fw-bold" id="finalTotalAmount">-</span>
                                                        </div>
                                                        <div class="d-grid gap-2">
                                                            <button type="submit" name="book_room" class="btn btn-lg text-white fw-bold" style="background: linear-gradient(135deg, #5998d6 0%, #4a7eb8 100%);">
                                                                <i class="fas fa-check me-2"></i>Confirm Booking
                                                            </button>
                                                            <button type="button" class="btn btn-outline-secondary" onclick="hideBookingForm()">
                                                                <i class="fas fa-times me-2"></i>Cancel
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Similar Rooms Section -->
            <div class="similar-rooms-section" data-aos="fade-up">
                <h3 class="section-title">Similar Rooms</h3>
                <div class="row g-4">
                    <?php
                    if ($similar_result->num_rows > 0) {
                        while ($similar_room = $similar_result->fetch_assoc()) {
                            $similarIsAvailable = $similar_room['status'] == 'available';
                    ?>
                            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                                <div class="room-card card h-100 <?php echo $similar_room['type'] == 'vip' ? 'vip-room' : 'standard-room'; ?>">
                                    <div class="room-image-container position-relative">
                                        <img src="<?php echo $similar_room['image_url']; ?>"
                                            class="room-image card-img-top"
                                            alt="Room <?php echo $similar_room['room_code']; ?>"
                                            loading="lazy">
                                        <div class="room-status-badge <?php echo $similarIsAvailable ? 'available' : 'booked'; ?>">
                                            <i class="fas <?php echo $similarIsAvailable ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                            <?php echo $similarIsAvailable ? 'Available' : 'Booked'; ?>
                                        </div>
                                        <?php if ($similar_room['type'] == 'vip'): ?>
                                            <div class="vip-badge">
                                                <i class="fas fa-crown"></i> VIP
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title mb-3">
                                            <i class="fas <?php echo $similar_room['type'] == 'vip' ? 'fa-crown text-warning' : 'fa-bed text-muted'; ?> me-2"></i>
                                            Room <?php echo $similar_room['room_code']; ?>
                                        </h5>
                                        <div class="room-amenities mb-3">
                                            <span class="amenity-badge">
                                                <i class="fas fa-wifi me-1"></i> WiFi
                                            </span>
                                            <span class="amenity-badge">
                                                <i class="fas fa-snowflake me-1"></i> AC
                                            </span>
                                            <span class="amenity-badge">
                                                <i class="fas fa-tv me-1"></i> TV
                                            </span>
                                        </div>
                                        <?php if ($similarIsAvailable): ?>
                                            <a href="booking.php?room_id=<?php echo $similar_room['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-calendar-check me-2"></i>Book Now
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-lock me-2"></i>Already Booked
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo '<div class="col-12"><div class="alert alert-info text-center">No other similar rooms available.</div></div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="image-modal" id="imageModal">
        <span class="close-modal" onclick="closeImageModal()">&times;</span>
        <img class="modal-image" id="modalImage" src="" alt="Room Preview">
    </div>

    <?php include_once '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="../assets/js/booking.js"></script>

    <script>
        // Initialize page data
        window.roomData = {
            id: <?php echo $room_id; ?>,
            code: '<?php echo $room['room_code']; ?>',
            hourlyRate: <?php echo $room['hourly_rate']; ?>
        };

        window.userData = {
            fullName: '<?php echo isset($_SESSION['user']['full_name']) ? $_SESSION['user']['full_name'] : ''; ?>',
            email: '<?php echo isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : ''; ?>',
            phone: '<?php echo isset($_SESSION['user']['phone_number']) ? $_SESSION['user']['phone_number'] : ''; ?>'
        };

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($error_message == 'login_required'): ?>
                if (confirm('You need to login to book a room. Would you like to login now?')) {
                    sessionStorage.setItem('returnUrl', window.location.href);
                    window.location.href = 'login.php';
                }
            <?php endif; ?>

            <?php if ($booking_success): ?>
                // Auto-hide success modal after 10 seconds
                setTimeout(() => {
                    const modal = document.getElementById('successModal');
                    if (modal && modal.style.display !== 'none') {
                        modal.style.opacity = '0.5';
                        setTimeout(() => {
                            modal.style.display = 'none';
                        }, 1000);
                    }
                }, 10000);
            <?php endif; ?>

            // Initialize AOS
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                mirror: false
            });

            // Initialize booking manager
            if (typeof BookingManager !== 'undefined') {
                BookingManager.init(window.roomData.id, window.roomData.hourlyRate);
            }

            // Duration selection functionality
            let currentDurationType = 'hour';
            let currentRate = window.roomData.hourlyRate;
            
            const durationOptions = document.querySelectorAll('.duration-option');
            durationOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove active class from all options
                    durationOptions.forEach(opt => opt.classList.remove('active'));
                    
                    // Add active class to clicked option
                    this.classList.add('active');
                    
                    // Update current values
                    currentDurationType = this.dataset.type;
                    currentRate = parseFloat(this.dataset.rate);
                    
                    console.log('Duration type changed to:', currentDurationType, 'Rate:', currentRate);
                    
                    // Reset quantity to 1
                    document.getElementById('quantityInput').value = 1;
                    
                    // Update price and discount info
                    updateTotalPrice();
                    updateDiscountInfo();
                    
                    // Update checkout time if check-in is set
                    setTimeout(() => {
                        calculateCheckoutTime();
                    }, 50);
                });
            });

            // Global functions for onclick events
            window.changeQuantity = (change) => {
                const input = document.getElementById('quantityInput');
                let currentValue = parseInt(input.value) || 1;
                let newValue = currentValue + change;
                
                if (newValue >= 1 && newValue <= 10) {
                    input.value = newValue;
                    console.log('Quantity changed to:', newValue);
                    updateTotalPrice();
                    setTimeout(() => {
                        calculateCheckoutTime();
                        updateBookingSummary();
                    }, 50);
                }
            };
            
            window.toggleBookingForm = () => {
                const formSection = document.getElementById('bookingFormSection');
                if (formSection.style.display === 'none') {
                    formSection.style.display = 'block';
                    formSection.scrollIntoView({ behavior: 'smooth' });
                    console.log('Booking form shown, initializing...');
                    setTimeout(() => {
                        initializeBookingForm();
                    }, 100);
                } else {
                    formSection.style.display = 'none';
                }
            };
            
            window.hideBookingForm = () => {
                document.getElementById('bookingFormSection').style.display = 'none';
            };
            
            window.selectInlinePayment = (method) => {
                // Remove previous selections
                document.querySelectorAll('.payment-card').forEach(card => {
                    card.classList.remove('border-primary');
                });
                
                // Add selection to clicked card
                const selectedCard = document.querySelector(`#inline_${method}`).closest('.payment-card');
                selectedCard.classList.add('border-primary');
                
                // Check the radio button
                document.getElementById(`inline_${method}`).checked = true;
            };
            
            window.zoomImage = () => {
                const img = document.getElementById('mainRoomImage');
                img.style.transform = 'scale(1.5)';
            };
            
            window.resetImage = () => {
                const img = document.getElementById('mainRoomImage');
                img.style.transform = 'scale(1)';
            };
            
            window.openImageModal = (src) => {
                const modal = document.getElementById('imageModal');
                const modalImg = document.getElementById('modalImage');
                modal.style.display = 'block';
                modalImg.src = src;
                setTimeout(() => modal.classList.add('show'), 10);
            };
            
            window.closeImageModal = () => {
                const modal = document.getElementById('imageModal');
                modal.classList.remove('show');
                setTimeout(() => modal.style.display = 'none', 300);
            };

            window.closeSuccessModal = () => {
                const modal = document.getElementById('successModal');
                if (modal) {
                    modal.style.display = 'none';
                    // Optional: Redirect after closing
                    // window.location.href = 'room.php';
                }
            };

            function updateTotalPrice() {
                const quantity = parseInt(document.getElementById('quantityInput').value) || 1;
                const total = currentRate * quantity * 1000;
                document.getElementById('totalAmount').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VND';
                
                // Update final total in summary
                const finalTotal = document.getElementById('finalTotalAmount');
                if (finalTotal) {
                    finalTotal.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VND';
                }

                // Update hidden form fields
                const durationTypeField = document.getElementById('duration_type');
                const durationQuantityField = document.getElementById('duration_quantity');
                if (durationTypeField) durationTypeField.value = currentDurationType;
                if (durationQuantityField) durationQuantityField.value = quantity;
            }

            function updateDiscountInfo() {
                const discountInfo = document.getElementById('discountInfo');
                if (!discountInfo) return;
                
                switch(currentDurationType) {
                    case 'day':
                        discountInfo.textContent = 'Save 20% compared to hourly rate';
                        break;
                    case 'week':
                        discountInfo.textContent = 'Save 35% compared to hourly rate';
                        break;
                    case 'month':
                        discountInfo.textContent = 'Save 50% compared to hourly rate';
                        break;
                    default:
                        discountInfo.textContent = '';
                }
            }

            function calculateCheckoutTime() {
                console.log('=== calculateCheckoutTime called ===');
                const checkInInput = document.getElementById('inline_check_in');
                const checkOutInput = document.getElementById('inline_check_out');
                const quantityInput = document.getElementById('quantityInput');
                
                console.log('Elements found:', {
                    checkInInput: !!checkInInput,
                    checkOutInput: !!checkOutInput,
                    quantityInput: !!quantityInput
                });
                
                if (!checkInInput || !checkOutInput || !quantityInput) {
                    console.log('Missing required elements');
                    return;
                }
                
                const checkInValue = checkInInput.value;
                const quantity = parseInt(quantityInput.value) || 1;
                
                console.log('Input values:', {
                    checkInValue: checkInValue,
                    quantity: quantity,
                    currentDurationType: currentDurationType
                });
                
                if (!checkInValue) {
                    console.log('No check-in value');
                    return;
                }
                
                const checkInDate = new Date(checkInValue);
                console.log('Check-in date parsed:', checkInDate);
                
                // Make sure we have a valid check-in date
                if (isNaN(checkInDate.getTime())) {
                    console.log('Invalid check-in date');
                    return;
                }
                
                let checkOutDate = new Date(checkInDate.getTime()); // Create a proper copy
                console.log('Initial checkout date:', checkOutDate);
                
                switch(currentDurationType) {
                    case 'hour':
                        checkOutDate.setHours(checkOutDate.getHours() + quantity);
                        console.log(`Added ${quantity} hours`);
                        break;
                    case 'day':
                        checkOutDate.setDate(checkOutDate.getDate() + quantity);
                        console.log(`Added ${quantity} days`);
                        break;
                    case 'week':
                        checkOutDate.setDate(checkOutDate.getDate() + (quantity * 7));
                        console.log(`Added ${quantity * 7} days`);
                        break;
                    case 'month':
                        checkOutDate.setMonth(checkOutDate.getMonth() + quantity);
                        console.log(`Added ${quantity} months`);
                        break;
                }
                
                console.log('Final checkout date:', checkOutDate);
                
                // Format the date properly for datetime-local input
                const year = checkOutDate.getFullYear();
                const month = String(checkOutDate.getMonth() + 1).padStart(2, '0');
                const day = String(checkOutDate.getDate()).padStart(2, '0');
                const hours = String(checkOutDate.getHours()).padStart(2, '0');
                const minutes = String(checkOutDate.getMinutes()).padStart(2, '0');
                
                const formattedDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                console.log('Formatted datetime:', formattedDateTime);
                
                checkOutInput.value = formattedDateTime;
                console.log('Set checkout input value to:', checkOutInput.value);
                
                updateBookingSummary();
                console.log('=== calculateCheckoutTime finished ===');
            }

            function updateBookingSummary() {
                const checkIn = document.getElementById('inline_check_in')?.value;
                const checkOut = document.getElementById('inline_check_out')?.value;
                const summaryDuration = document.getElementById('summaryDuration');
                const summaryCheckIn = document.getElementById('summaryCheckIn');
                const summaryCheckOut = document.getElementById('summaryCheckOut');
                const summaryGuests = document.getElementById('summaryGuests');
                const quantityInput = document.getElementById('quantityInput');
                
                if (quantityInput && summaryDuration) {
                    const quantity = parseInt(quantityInput.value) || 1;
                    let durationText = '';
                    switch(currentDurationType) {
                        case 'hour':
                            durationText = quantity + ' hour' + (quantity > 1 ? 's' : '');
                            break;
                        case 'day':
                            durationText = quantity + ' day' + (quantity > 1 ? 's' : '');
                            break;
                        case 'week':
                            durationText = quantity + ' week' + (quantity > 1 ? 's' : '');
                            break;
                        case 'month':
                            durationText = quantity + ' month' + (quantity > 1 ? 's' : '');
                            break;
                    }
                    summaryDuration.textContent = durationText;
                }
                
                if (checkIn && checkOut && summaryCheckIn && summaryCheckOut) {
                    const checkInDate = new Date(checkIn);
                    const checkOutDate = new Date(checkOut);
                    
                    const vietnamTimeOptions = {
                        timeZone: 'Asia/Ho_Chi_Minh',
                        day: '2-digit',
                        month: '2-digit', 
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    };
                    
                    summaryCheckIn.textContent = checkInDate.toLocaleString('vi-VN', vietnamTimeOptions);
                    summaryCheckOut.textContent = checkOutDate.toLocaleString('vi-VN', vietnamTimeOptions);
                }

                const guestsSelect = document.getElementById('inline_guests');
                if (guestsSelect && summaryGuests) {
                    summaryGuests.textContent = guestsSelect.value;
                }
            }

            function initializeBookingForm() {
                // Set minimum date/time to current Vietnam time
                const now = new Date();
                const vietnamTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Ho_Chi_Minh"}));
                const minDateTime = vietnamTime.toISOString().slice(0, 16);
                
                const checkInInput = document.getElementById('inline_check_in');
                const checkOutInput = document.getElementById('inline_check_out');
                
                if (checkInInput) {
                    checkInInput.min = minDateTime;
                    
                    // Set default check-in to Vietnam time + 1 hour
                    const defaultCheckIn = new Date(vietnamTime.getTime() + 60 * 60 * 1000);
                    const year = defaultCheckIn.getFullYear();
                    const month = String(defaultCheckIn.getMonth() + 1).padStart(2, '0');
                    const day = String(defaultCheckIn.getDate()).padStart(2, '0');
                    const hours = String(defaultCheckIn.getHours()).padStart(2, '0');
                    const minutes = String(defaultCheckIn.getMinutes()).padStart(2, '0');
                    const formattedDefaultCheckIn = `${year}-${month}-${day}T${hours}:${minutes}`;
                    
                    checkInInput.value = formattedDefaultCheckIn;
                    
                    // Setup event listeners for check-in changes
                    checkInInput.addEventListener('change', () => {
                        console.log('Check-in changed to:', checkInInput.value);
                        calculateCheckoutTime();
                    });
                    
                    checkInInput.addEventListener('input', () => {
                        console.log('Check-in input to:', checkInInput.value);
                        calculateCheckoutTime();
                    });
                    
                    // Calculate initial checkout time
                    setTimeout(() => {
                        calculateCheckoutTime();
                    }, 100);
                }

                if (checkOutInput) {
                    checkOutInput.min = minDateTime;
                }

                // Setup event listeners for form updates
                const guestsSelect = document.getElementById('inline_guests');
                if (guestsSelect) {
                    guestsSelect.addEventListener('change', updateBookingSummary);
                }

                // Initial summary update
                setTimeout(() => {
                    updateBookingSummary();
                }, 200);
            }

            function validateBookingForm() {
                const requiredFields = [
                    'inline_user_name',
                    'inline_user_email', 
                    'inline_user_phone',
                    'inline_check_in',
                    'inline_check_out'
                ];

                let isValid = true;
                let firstInvalidField = null;

                // Check required fields
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && !field.value.trim()) {
                        field.classList.add('is-invalid');
                        if (!firstInvalidField) firstInvalidField = field;
                        isValid = false;
                    } else if (field) {
                        field.classList.remove('is-invalid');
                    }
                });

                // Check payment method
                const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
                const isPaymentSelected = Array.from(paymentMethods).some(radio => radio.checked);
                
                if (!isPaymentSelected) {
                    alert('Please select a payment method.');
                    isValid = false;
                }

                // Check datetime validity
                const checkIn = document.getElementById('inline_check_in');
                const checkOut = document.getElementById('inline_check_out');
                
                if (checkIn && checkOut && checkIn.value && checkOut.value) {
                    const checkInDate = new Date(checkIn.value);
                    const checkOutDate = new Date(checkOut.value);
                    const now = new Date();

                    if (checkInDate <= now) {
                        alert('Check-in time must be in the future.');
                        checkIn.focus();
                        return false;
                    }

                    if (checkOutDate <= checkInDate) {
                        alert('Check-out time must be after check-in time.');
                        checkOut.focus();
                        return false;
                    }
                }

                if (!isValid && firstInvalidField) {
                    firstInvalidField.focus();
                    alert('Please fill in all required fields.');
                }

                return isValid;
            }

            // Initialize total price
            updateTotalPrice();
        });

        // Global functions for compatibility
        function selectDurationType(option) {
            if (window.BookingManager) {
                window.BookingManager.selectDurationType(option);
                
                // Update hidden fields for form submission
                const durationTypeInput = document.getElementById('duration_type');
                const durationQuantityInput = document.getElementById('duration_quantity');
                const quantityInput = document.getElementById('quantityInput');
                
                if (durationTypeInput) {
                    durationTypeInput.value = window.BookingManager.currentType;
                }
                if (durationQuantityInput && quantityInput) {
                    durationQuantityInput.value = quantityInput.value;
                }
            }
        }

        function changeQuantity(change) {
            if (window.BookingManager) {
                window.BookingManager.changeQuantity(change);
                
                // Update hidden field for form submission
                const durationQuantityInput = document.getElementById('duration_quantity');
                const quantityInput = document.getElementById('quantityInput');
                
                if (durationQuantityInput && quantityInput) {
                    durationQuantityInput.value = quantityInput.value;
                }
            }
        }

        function toggleBookingForm() {
            if (window.BookingManager) {
                window.BookingManager.toggleBookingForm();
            }
        }

        function hideBookingForm() {
            if (window.BookingManager) {
                window.BookingManager.hideBookingForm();
            }
        }

        function selectInlinePayment(method) {
            if (window.BookingManager) {
                window.BookingManager.selectInlinePayment(method);
            }
        }

        function updateTotalPrice() {
            if (window.BookingManager) {
                window.BookingManager.updateTotalPrice();
                
                // Update hidden fields
                const durationTypeInput = document.getElementById('duration_type');
                const durationQuantityInput = document.getElementById('duration_quantity');
                const quantityInput = document.getElementById('quantityInput');
                
                if (durationTypeInput) {
                    durationTypeInput.value = window.BookingManager.currentType;
                }
                if (durationQuantityInput && quantityInput) {
                    durationQuantityInput.value = quantityInput.value;
                }
            }
        }

        function calculateCheckoutTime() {
            if (window.BookingManager) {
                window.BookingManager.calculateCheckoutTime();
            }
        }

        function updateBookingSummary() {
            if (window.BookingManager) {
                window.BookingManager.updateBookingSummary();
            }
        }

    </script>
</body>
</html>
