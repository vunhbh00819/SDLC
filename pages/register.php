<?php
session_start();
include_once '../config/database.php';

$error_message = '';
$success_message = '';

// Registration processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone_number = trim($_POST['phone_number']);
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all required information.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Password confirmation does not match.';
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = 'This email is already in use. Please choose another email.';
        } else {
            // Add new user to database
            $insert_sql = "INSERT INTO users (full_name, email, password, phone_number, role) VALUES (?, ?, ?, ?, 'customer')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssss", $full_name, $email, $password, $phone_number);
            
            if ($insert_stmt->execute()) {
                $success_message = 'Registration successful! You can login now.';
                
                // Auto login after registration
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'guest';
                
                // Redirect after 2 seconds
                header("refresh:2;url=room.php");
            } else {
                $error_message = 'An error occurred during registration. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hotel Room Booking System</title>
    <meta name="description" content="Create a new account to experience the best hotel room booking service.">
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

<div class="container" style="margin-left: 90px;">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="register-container" data-aos="fade-up">
                <div class="row g-0">
                    <!-- Left side - Benefits -->
                    <div class="col-lg-5">
                        <div class="register-header h-100 d-flex flex-column justify-content-center">
                            <div class="position-relative z-2">
                                <i class="fas fa-user-plus fs-1 mb-3"></i>
                                <h2 class="mb-3">Join Melissa Hotel</h2>
                              
                                
                                <div class="benefits-section">
                                    <h5 class="text-dark mb-3">Member Benefits:</h5>
                                    <div class="benefit-item"><i class="fas fa-percentage"></i><span>Special Discounts</span></div>
                                    <div class="benefit-item"><i class="fas fa-clock"></i><span>Quick & Easy Booking</span></div>
                                    <div class="benefit-item"><i class="fas fa-history"></i><span>Booking History Management</span></div>
                                    <div class="benefit-item"><i class="fas fa-bell"></i><span>Promotional Notifications</span></div>
                                    <div class="benefit-item"><i class="fas fa-headset"></i><span>24/7 Support</span></div>
                                    <div class="benefit-item"><i class="fas fa-star"></i><span>VIP Privileges & Points</span></div>
                                </div>

                            </div>
                        </div>
                    </div>
                    
                    <!-- Right side - Register Form -->
                    <div class="col-lg-7">
                        <div class="register-form">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark">Create New Account</h3>
                                <p class="text-muted">Fill in your information to start your journey</p>
                            </div>
                            
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger" data-aos="shake">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success success-animation" data-aos="fade-in">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo $success_message; ?>
                                    <div class="mt-2">
                                        <small>Redirecting to rooms page...</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="" id="registerForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                                            <label for="full_name">
                                                <i class="fas fa-user me-2"></i>Full Name *
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="Phone Number" value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
                                            <label for="phone_number">
                                                <i class="fas fa-phone me-2"></i>Phone Number
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    <label for="email">
                                        <i class="fas fa-envelope me-2"></i>Email *
                                    </label>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                            <label for="password">
                                                <i class="fas fa-lock me-2"></i>Password *
                                            </label>
                                        </div>
                                        <div class="password-strength" id="passwordStrength"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                                            <label for="confirm_password">
                                                <i class="fas fa-lock me-2"></i>Confirm Password *
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> 
                                        and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" required>
                                    <label class="form-check-label" for="newsletter">
                                        Receive promotional notifications and special offers via email
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-register">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Create Account
                                </button>
                            </form>
                            
                            <div class="text-center mt-4">
                                <p class="mb-0">Already have an account? 
                                    <a href="login.php" class="text-decoration-none fw-bold">
                                        Sign in now
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
    
    // Form elements
    const form = document.getElementById('registerForm');
    const fullNameInput = document.getElementById('full_name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const phoneInput = document.getElementById('phone_number');
    const termsCheckbox = document.getElementById('terms');
    const passwordStrengthDiv = document.getElementById('passwordStrength');
    
    // Password strength checker
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        updatePasswordStrength(strength);
    });
    
    // Confirm password validation
    confirmPasswordInput.addEventListener('input', function() {
        if (this.value && this.value !== passwordInput.value) {
            showFieldError(this, 'Password confirmation does not match');
        } else {
            removeFieldError(this);
        }
    });
    
    // Phone number formatting
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 10) value = value.substr(0, 10);
        this.value = value;
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Full name validation
        if (!fullNameInput.value.trim() || fullNameInput.value.trim().length < 2) {
            showFieldError(fullNameInput, 'Full name must be at least 2 characters');
            isValid = false;
        } else {
            removeFieldError(fullNameInput);
        }
        
        // Email validation
        if (!emailInput.value || !isValidEmail(emailInput.value)) {
            showFieldError(emailInput, 'Please enter a valid email address');
            isValid = false;
        } else {
            removeFieldError(emailInput);
        }
        
        // Password validation
        if (!passwordInput.value || passwordInput.value.length < 6) {
            showFieldError(passwordInput, 'Password must be at least 6 characters');
            isValid = false;
        } else {
            removeFieldError(passwordInput);
        }
        
        // Confirm password validation
        if (!confirmPasswordInput.value || confirmPasswordInput.value !== passwordInput.value) {
            showFieldError(confirmPasswordInput, 'Password confirmation does not match');
            isValid = false;
        } else {
            removeFieldError(confirmPasswordInput);
        }
        
        // Phone validation (optional but if provided must be valid)
        if (phoneInput.value && !isValidPhone(phoneInput.value)) {
            showFieldError(phoneInput, 'Invalid phone number');
            isValid = false;
        } else {
            removeFieldError(phoneInput);
        }
        
        // Terms checkbox validation
        if (!termsCheckbox.checked) {
            showFieldError(termsCheckbox, 'You must agree to the terms of service');
            isValid = false;
        } else {
            removeFieldError(termsCheckbox);
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        return strength;
    }
    
    function updatePasswordStrength(strength) {
        let text = '';
        let className = '';
        
        switch(strength) {
            case 0:
            case 1:
                text = 'Weak password';
                className = 'strength-weak';
                break;
            case 2:
            case 3:
                text = 'Medium password';
                className = 'strength-medium';
                break;
            case 4:
            case 5:
                text = 'Strong password';
                className = 'strength-strong';
                break;
        }
        
        passwordStrengthDiv.textContent = text;
        passwordStrengthDiv.className = `password-strength ${className}`;
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    function isValidPhone(phone) {
        return /^[0-9]{10}$/.test(phone);
    }
    
    function showFieldError(field, message) {
        removeFieldError(field);
        field.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        
        if (field.type === 'checkbox') {
            field.parentNode.parentNode.appendChild(errorDiv);
        } else {
            field.parentNode.appendChild(errorDiv);
        }
    }
    
    function removeFieldError(field) {
        field.classList.remove('is-invalid');
        
        let errorDiv;
        if (field.type === 'checkbox') {
            errorDiv = field.parentNode.parentNode.querySelector('.invalid-feedback');
        } else {
            errorDiv = field.parentNode.querySelector('.invalid-feedback');
        }
        
        if (errorDiv) {
            errorDiv.remove();
        }
    }
});
</script>

</body>
</html>
