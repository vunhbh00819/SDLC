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
    <title>Hotel Room Booking System - Enterprise Solution for Hotel Chains</title>
    <meta name="description" content="Complete hotel booking management system with real-time availability, secure payments, and advanced analytics.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="../assets/css/home.css" rel="stylesheet">
    <link href="../assets/css/features.css" rel="stylesheet">
</head>
<body>

<?php include_once '../includes/header.php'; ?>

<!-- Hero Banner Section -->
<section class="hero-banner">
    <div class="banner-container">
        <div class="banner-slide active">
            <img src="../assets/images/banner1.png" alt="Hotel Room Booking System">
            <div class="banner-overlay">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-10 text-center">
                            <h1 class="display-2 fw-bold text-white mb-4" data-aos="fade-up">
                                WELCOME TO MELISSA HOTEL
                            </h1>
                            <p class="lead text-white mb-5" data-aos="fade-up" data-aos-delay="200">
                                Experience Luxury and Comfort in Every Stay
                            </p>
                            <div class="d-flex justify-content-center gap-3 flex-wrap" data-aos="fade-up" data-aos-delay="400">
                                <a href="#booking" class="btn btn-gradient btn-lg px-5 py-3">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Start Booking Now
                                </a>
                                <a href="#demo" class="btn btn-outline-light btn-lg px-5 py-3">
                                    <i class="fas fa-play me-2"></i>
                                    Watch Demo
                                </a>
                            </div>
                            <!-- Trust Indicators -->
                            <div class="trust-indicators mt-5" data-aos="fade-up" data-aos-delay="600">
                                <div class="row g-4">
                                    <div class="col-6 col-md-3">
                                        <div class="trust-item">
                                            <h4 class="counter">500+</h4>
                                            <p class="text-white-50 mb-0">Luxury Rooms</p>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="trust-item">
                                            <h4 class="counter">4.9</h4>
                                            <p class="text-white-50 mb-0">Guest Rating</p>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="trust-item">
                                            <h4 class="counter">15+</h4>
                                            <p class="text-white-50 mb-0">Years Experience</p>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="trust-item">
                                            <h4>24/7</h4>
                                            <p class="text-white-50 mb-0">Room Service</p>
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
        <div class="banner-slide">
            <img src="../assets/images/banner2.png" alt="Online Booking Platform">
            <div class="banner-overlay">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-8">
                            <h1 class="display-3 fw-bold mb-4">
                                STREAMLINE YOUR OPERATIONS
                            </h1>
                            <p class="lead mb-5">
                                Real-time availability & seamless booking experience
                            </p>
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                <a href="#features" class="btn btn-gradient btn-lg px-5 py-3">
                                    <i class="fas fa-cogs me-2"></i>
                                    Explore Features
                                </a>
                                <a href="#pricing" class="btn btn-outline-light btn-lg px-5 py-3">
                                    <i class="fas fa-tag me-2"></i>
                                    View Pricing
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="banner-slide">
            <img src="../assets/images/banner3.png" alt="Secure Payment Processing">
            <div class="banner-overlay">
                <div class="container">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-8">
                            <h1 class="display-3 fw-bold mb-4">
                                SECURE PAYMENT GATEWAY
                            </h1>
                            <p class="lead mb-5">
                                Multiple payment methods with advanced security
                            </p>
                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                <a href="#contact" class="btn btn-gradient btn-lg px-5 py-3">
                                    <i class="fas fa-phone me-2"></i>
                                    Get Started
                                </a>
                                <a href="#support" class="btn btn-outline-light btn-lg px-5 py-3">
                                    <i class="fas fa-headset me-2"></i>
                                    Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="banner-nav">
        <span class="nav-dot active" onclick="currentSlide(1)"></span>
        <span class="nav-dot" onclick="currentSlide(2)"></span>
        <span class="nav-dot" onclick="currentSlide(3)"></span>
    </div>
   
</section>

<!-- Features Section -->
<section class="features-section py-5" id="features">
    <div class="container-fluid">
        <!-- Section Header -->
        <div class="section-header text-center mb-5" data-aos="fade-up">
            
            <h2 class="display-4 fw-bold mb-4">Discover Our Premium Services</h2>
            <div class="separator mx-auto"></div>
        </div>

        <!-- Features Grid -->
        <div class="row g-4">
            <!-- Feature Card 1 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card h-100">
                    <div class="icon-box mb-4">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h4 class="feature-title">Easy Booking</h4>
                    <p class="feature-desc">
                        Real-time availability updates with instant confirmation and automated room allocation system
                    </p>
                </div>
            </div>

            <!-- Feature Card 2 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card h-100">
                    <div class="icon-box mb-4">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="feature-title">Secure Payments</h4>
                    <p class="feature-desc">
                        Multiple payment options with advanced security and fraud protection systems
                    </p>
                </div>
            </div>

            <!-- Feature Card 3 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card h-100">
                    <div class="icon-box mb-4">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <h4 class="feature-title">24/7 Service</h4>
                    <p class="feature-desc">
                        Round-the-clock customer support and room service for your comfort
                    </p>
                </div>
            </div>

            <!-- Feature Card 4 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card h-100">
                    <div class="icon-box mb-4">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h4 class="feature-title">Spa & Wellness</h4>
                    <p class="feature-desc">
                        Luxury spa treatments and wellness facilities for ultimate relaxation
                    </p>
                </div>
            </div>

            <!-- Feature Card 5 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-card h-100">
                    <div class="icon-box mb-4">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h4 class="feature-title">Fine Dining</h4>
                    <p class="feature-desc">
                        Exquisite restaurants offering international and local cuisine
                    </p>
                </div>
            </div>

            <!-- Feature Card 6 -->
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="600">
                <div class="feature-card h-100">
                    <div class="icon-box mb-4">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <h4 class="feature-title">Free Wi-Fi</h4>
                    <p class="feature-desc">
                        High-speed internet access throughout the hotel premises
                    </p>
                </div>
            </div>
        </div>

        <!-- Image Section -->
        <div class="row mt-5">
            <div class="col-12" data-aos="fade-up" data-aos-delay="700">
                <div class="position-relative">
                    <img src="../assets/images/banner4.png" alt="Luxury Hotel Experience" 
                         class="img-fluid w-100 rounded-4 shadow-lg" style="height: 700px;">
                    <div class="overlay-text">
                        <h3 class="mb-3">Experience Luxury</h3>
                        <p class="mb-4">Discover our world-class amenities and services</p>
                        <a href="#booking" class="btn btn-light btn-lg">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-5 text-white" id="contact" style="background-color: #6fabe8;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="display-5 fw-bold mb-4">
                    <i class="fas fa-rocket me-3"></i>
                    Transform Your Hotel Business Today
                </h2>
                <p class="lead mb-4">
                    Join over <strong>2,500+ hotels worldwide</strong> that have revolutionized their booking operations.
                </p>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="stat-card text-center p-3 rounded-3 bg-white bg-opacity-10">
                            <h3 class="fw-bold mb-1">98.7%</h3>
                            <p class="small mb-0">System Uptime</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center p-3 rounded-3 bg-white bg-opacity-10">
                            <h3 class="fw-bold mb-1">$2.5M+</h3>
                            <p class="small mb-0">Revenue Processed</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6" data-aos="fade-left">
                <div class="contact-form-wrapper bg-white rounded-4 p-4 shadow-lg">
                    <h4 class="mb-4 text-center text-dark">
                        <i class="fas fa-headset me-2 text-primary"></i>
                        Get Started Today
                    </h4>
                    <form class="contact-form">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-dark">Hotel Name</label>
                                <input type="text" class="form-control form-control-lg" placeholder="Your Hotel Name" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-dark">Email Address</label>
                                <input type="email" class="form-control form-control-lg" placeholder="your@email.com" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-gradient w-100 btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Get Free Consultation
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="../assets/js/home.js"></script>
<script>
    // Initialize AOS
    AOS.init({
        duration: 1000,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });
</script>

</body>
</html>