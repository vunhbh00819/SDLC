<?php
session_start();
include_once '../config/database.php';

$error_message = '';
$success_message = '';


if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success_message = 'You have been successfully logged out. Please login again.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        $sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($password === $user['password']) {
                $_SESSION['user'] = $user; 
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $success_message = 'Login successful! Redirecting...';
                if ($user['role'] === 'admin') {
                    echo '<script> setTimeout(() => { window.location.href = "report.php"; }, 1500);</script>';
                } else {
                    // Customer: check if they came from booking
                    echo '<script> if (sessionStorage.getItem("returnUrl")) { setTimeout(() => {
                                window.location.href = sessionStorage.getItem("returnUrl");
                            }, 1500);
                        } else {
                            setTimeout(() => {
                                window.location.href = "room.php";
                            }, 1500);
                        }
                    </script>';
                }
            } else {
                $error_message = 'Incorrect password.';
            }
        } else {
            $error_message = 'Email does not exist in the system.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotel Room Booking System</title>
    <meta name="description" content="Login to the hotel room booking system to manage bookings and enjoy the best services.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

<a href="home.php" class="back-link">
    <i class="fas fa-arrow-left me-2"></i>
    Home
</a>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="login-container mt-3 mb-4" data-aos="fade-up">
                <div class="row g-0">
                    <!-- Left side - Hotel Info -->
                    <div class="col-lg-5">
                        <div class="login-header h-100 d-flex flex-column justify-content-center">
                            <div class="position-relative z-2">
                                <i class="fas fa-hotel fs-1 mb-3"></i>
                                <h2 class="mb-3">Melissa Hotel</h2>
                          
                                
                                <div class="hotel-info">
                                    <div class="feature-item">
                                        <i class="fas fa-crown"></i>
                                        <span>VIP Rooms with Amenities</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-wifi"></i>
                                        <span>Free High-Speed WiFi</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-car"></i>
                                        <span>Free Parking</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-swimming-pool"></i>
                                        <span>Modern Pool & Gym</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-concierge-bell"></i>
                                        <span>24/7 Professional Service</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right side - Login Form -->
                    <div class="col-lg-7">
                        <div class="login-form">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark">Login</h3>
                                <p class="text-muted">Welcome back!</p>
                            </div>
                            
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger" data-aos="shake">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success" data-aos="fade-in">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                                    <label for="email">
                                        <i class="fas fa-envelope me-2"></i>Email
                                    </label>
                                </div>
                                
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                    <label for="password">
                                        <i class="fas fa-lock me-2"></i>Password
                                    </label>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" required>
                                        <label class="form-check-label" for="remember">
                                            Remember me
                                        </label>
                                    </div>
                                    <a href="#" class="text-decoration-none">Forgot password?</a>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Login
                                </button>
                            </form>
                            
                          
                            <div class="text-center mt-4">
                                <p class="mb-0">Don't have an account? 
                                    <a href="register.php" class="text-decoration-none fw-bold">
                                        Register now
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
    
    // Form validation
    const form = document.querySelector('form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Email validation
        if (!emailInput.value || !isValidEmail(emailInput.value)) {
            showFieldError(emailInput, 'Please enter a valid email');
            isValid = false;
        } else {
            removeFieldError(emailInput);
        }
        
        // Password validation
        if (!passwordInput.value || passwordInput.value.length < 3) {
            showFieldError(passwordInput, 'Password must be at least 3 characters');
            isValid = false;
        } else {
            removeFieldError(passwordInput);
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    function showFieldError(field, message) {
        removeFieldError(field);
        field.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    function removeFieldError(field) {
        field.classList.remove('is-invalid');
        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    // Auto-hide logout success message after 5 seconds
    <?php if (isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
    setTimeout(function() {
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            successAlert.style.transition = 'opacity 0.5s ease';
            successAlert.style.opacity = '0';
            setTimeout(() => {
                successAlert.remove();
            }, 500);
        }
    }, 5000);
    <?php endif; ?>
});
</script>

</body>
</html>
