<?php
session_start();

// Contact form processing
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Here you would typically send the email or save to database
        // For now, just show success message
        $success_message = 'Thank you for your message! We will get back to you soon.';
        
        // Clear form data after successful submission
        $name = $email = $phone = $subject = $message = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Hotel Room Booking System</title>
    <meta name="description" content="Get in touch with Melissa Hotel. We're here to help with your booking inquiries and provide excellent customer service.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="../assets/css/contact.css" rel="stylesheet">

</head>
<body>

<?php include_once '../includes/header.php'; ?>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-10" data-aos="fade-up">
                <div class="hero-content">
                    <div class="hero-badge mb-3">
                        <i class="fas fa-star me-2"></i>5-Star Luxury Hotel
                    </div>
                    <h1>Get In Touch With Hotel du Parc HaNoi</h1>
                    <p class="lead">Experience exceptional service and luxury in the heart of Hanoi. We're here to help you with any questions or booking inquiries. Get in touch with our friendly team!</p>
                    <div class="hero-stats row mt-4">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h4>10,000+</h4>
                                <p>Happy Guests</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <i class="fas fa-award fa-2x mb-2"></i>
                                <h4>15+</h4>
                                <p>Awards Won</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h4>24/7</h4>
                                <p>Service</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-item">
                                <i class="fas fa-star fa-2x mb-2"></i>
                                <h4>4.9/5</h4>
                                <p>Guest Rating</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Floating Elements -->
    <div class="floating-element element-1"></div>
    <div class="floating-element element-2"></div>
    <div class="floating-element element-3"></div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        
        <!-- Alert Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-custom" style="background-color: #5998d6;" data-aos="fade-in">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-custom" style="background-color: #5998d6;" data-aos="shake">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            
            <!-- Contact Information -->
            <div class="col-lg-4">
                
                <!-- Contact Cards -->
                <div class="contact-card" data-aos="fade-right" data-aos-delay="100">
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h5>Phone</h5>
                        <p class="mb-2"><strong>Hotline:</strong> +84 24 3974 8888</p>
                        <p class="mb-2"><strong>Support:</strong> +84 24 3974 9999</p>
                        <p class="mb-0"><strong>Emergency:</strong> +84 987 654 321</p>
                    </div>
                </div>

                <div class="contact-card" data-aos="fade-right" data-aos-delay="200">
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5>Email</h5>
                        <p class="mb-2"><strong>General:</strong> info@hotelduparc.com</p>
                        <p class="mb-2"><strong>Booking:</strong> reservation@hotelduparc.com</p>
                        <p class="mb-0"><strong>Events:</strong> events@hotelduparc.com</p>
                    </div>
                </div>

                <div class="contact-card" data-aos="fade-right" data-aos-delay="300">
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h5>Address</h5>
                        <p class="mb-0">Hotel du Parc HaNoi<br>84 Tran Nhan Tong Street<br>Hai Ba Trung District, Hanoi<br>Vietnam</p>
                    </div>
                </div>

                <!-- Office Hours -->
                <div class="office-hours" data-aos="fade-right" data-aos-delay="400">
                    <h5><i class="fas fa-clock me-2"></i>Office Hours</h5>
                    <div class="hours-item">
                        <span class="day">Monday - Friday</span>
                        <span class="time">8:00 AM - 10:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span class="day">Saturday - Sunday</span>
                        <span class="time">9:00 AM - 11:00 PM</span>
                    </div>
                    <div class="hours-item">
                        <span class="day">Public Holidays</span>
                        <span class="time">10:00 AM - 8:00 PM</span>
                    </div>
                </div>

               
              

            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <!-- Hotel Information Banner -->
                <div class="contact-form-container mb-4" data-aos="fade-left" data-aos-delay="100" style="padding: 40px; text-align: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                    <h4 class="text-primary mb-3"><i class="fas fa-hotel me-2"></i>Hotel du Parc HaNoi</h4>
                    <p class="mb-3">Indulge in unparalleled luxury and modern comfort in the vibrant heart of Hanoi. Our hotel seamlessly blends world-class amenities, elegant design.

From stylish rooms and gourmet dining to tranquil spa experiences, every detail is thoughtfully curated. Whether you're visiting for business or leisure, we are dedicated to delivering an unforgettable stay tailored just for you.</p>
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="feature-item">
                                <i class="fas fa-star text-warning fa-2x mb-2"></i>
                                <h6>5-Star Luxury</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="feature-item">
                                <i class="fas fa-bed text-primary fa-2x mb-2"></i>
                                <h6>150+ Rooms</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="feature-item">
                                <i class="fas fa-swimming-pool text-info fa-2x mb-2"></i>
                                <h6>Swimming Pool</h6>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="feature-item">
                                <i class="fas fa-clock text-success fa-2x mb-2"></i>
                                <h6>24/7 Service</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-form-container" data-aos="fade-left">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark">Send us a Message</h3>
                        <p class="text-muted">Fill out the form below and we'll get back to you as soon as possible</p>
                    </div>

                    <form method="POST" action="" id="contactForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                    <label for="name">
                                        <i class="fas fa-user me-2"></i>Full Name *
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    <label for="email">
                                        <i class="fas fa-envelope me-2"></i>Email Address *
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    <label for="phone">
                                        <i class="fas fa-phone me-2"></i>Phone Number
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="">Select Subject</option>
                                        <option value="booking" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'booking') ? 'selected' : ''; ?>>Room Booking Inquiry</option>
                                        <option value="cancellation" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'cancellation') ? 'selected' : ''; ?>>Booking Cancellation</option>
                                        <option value="complaint" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'complaint') ? 'selected' : ''; ?>>Complaint</option>
                                        <option value="feedback" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'feedback') ? 'selected' : ''; ?>>Feedback</option>
                                        <option value="other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                    <label for="subject">
                                        <i class="fas fa-tag me-2"></i>Subject *
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="form-floating">
                                <textarea class="form-control" id="message" name="message" placeholder="Your Message" style="height: 150px" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                <label for="message">
                                    <i class="fas fa-comment-alt me-2"></i>Your Message *
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-contact">
                            <i class="fas fa-paper-plane me-2"></i>
                            Send Message
                        </button>
                    </form>
                </div>

                <!-- Map -->
                <div class="map-container" data-aos="fade-up" data-aos-delay="200" style="height: 485px;">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.6966667891043!2d105.83931871485413!3d21.017721300621555!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab8f9af04521%3A0xdccfaebef264a0b1!2sHotel%20du%20Parc%20HaNoi!5e0!3m2!1sen!2s!4v1642752835901!5m2!1sen!2s" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- Hotel Highlights -->
        <div class="row mt-5">
            <div class="col-12" data-aos="fade-up">
                <h3 class="text-center mb-5">Why Choose Hotel du Parc HaNoi?</h3>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="highlight-card text-center">
                            <div class="highlight-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <h5>Prime Location</h5>
                            <p>Located in the heart of Hanoi, walking distance to major attractions and business districts.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="highlight-card text-center">
                            <div class="highlight-icon">
                                <i class="fas fa-medal"></i>
                            </div>
                            <h5>Award Winning</h5>
                            <p>Recognized for excellence in hospitality and customer service by leading travel organizations.</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="highlight-card text-center">
                            <div class="highlight-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h5>Eco-Friendly</h5>
                            <p>Committed to sustainable practices and environmental responsibility in all our operations.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="row mt-5">
            <div class="col-12" data-aos="fade-up">
                <h3 class="text-center mb-5">Frequently Asked Questions</h3>
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="fas fa-clock me-2"></i>What are your check-in and check-out times?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Check-in time is 2:00 PM and check-out time is 12:00 PM. Early check-in and late check-out are available upon request and may be subject to additional charges.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="fas fa-car me-2"></i>Do you provide airport transfer service?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we offer airport transfer service for our guests. Please contact us in advance to arrange the pickup. The service is available 24/7 with additional charges.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="fas fa-paw me-2"></i>Are pets allowed at the hotel?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we are a pet-friendly hotel. Small pets are welcome with prior notification. Additional pet fees may apply. Please contact us for specific pet policies.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="fas fa-credit-card me-2"></i>What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept all major credit cards (Visa, MasterCard, American Express), cash, and bank transfers. Online bookings can be paid through secure payment gateways.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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

    // Form validation
    const form = document.getElementById('contactForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const subjectSelect = document.getElementById('subject');
    const messageTextarea = document.getElementById('message');

    // Phone number formatting
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 11) value = value.substr(0, 11);
        this.value = value;
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Name validation
        if (!nameInput.value.trim() || nameInput.value.trim().length < 2) {
            showFieldError(nameInput, 'Full name must be at least 2 characters');
            isValid = false;
        } else {
            removeFieldError(nameInput);
        }

        // Email validation
        if (!emailInput.value || !isValidEmail(emailInput.value)) {
            showFieldError(emailInput, 'Please enter a valid email address');
            isValid = false;
        } else {
            removeFieldError(emailInput);
        }

        // Subject validation
        if (!subjectSelect.value) {
            showFieldError(subjectSelect, 'Please select a subject');
            isValid = false;
        } else {
            removeFieldError(subjectSelect);
        }

        // Message validation
        if (!messageTextarea.value.trim() || messageTextarea.value.trim().length < 10) {
            showFieldError(messageTextarea, 'Message must be at least 10 characters');
            isValid = false;
        } else {
            removeFieldError(messageTextarea);
        }

        // Phone validation (optional but if provided must be valid)
        if (phoneInput.value && !isValidPhone(phoneInput.value)) {
            showFieldError(phoneInput, 'Invalid phone number');
            isValid = false;
        } else {
            removeFieldError(phoneInput);
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    // Real-time validation
    nameInput.addEventListener('blur', function() {
        if (this.value.trim() && this.value.trim().length < 2) {
            showFieldError(this, 'Full name must be at least 2 characters');
        } else {
            removeFieldError(this);
        }
    });

    emailInput.addEventListener('blur', function() {
        if (this.value && !isValidEmail(this.value)) {
            showFieldError(this, 'Please enter a valid email address');
        } else {
            removeFieldError(this);
        }
    });

    messageTextarea.addEventListener('blur', function() {
        if (this.value.trim() && this.value.trim().length < 10) {
            showFieldError(this, 'Message must be at least 10 characters');
        } else {
            removeFieldError(this);
        }
    });

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidPhone(phone) {
        return /^[0-9]{10,11}$/.test(phone);
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
});

// WhatsApp function
function openWhatsApp() {
    const phoneNumber = '+842439748888';
    const message = 'Hello! I would like to inquire about room booking at Hotel du Parc HaNoi.';
    const url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank');
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>

</body>
</html>
