<!-- Bootstrap CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<!-- Custom CSS -->
<link href="/assets/css/room-status.css" rel="stylesheet">

<style>
/* Header Custom Styles */
:root {
    --primary-color: #5998d6;
    --primary-dark: #4a7eb8;
    --primary-light: #7bb0e3;
    --gradient-primary: linear-gradient(135deg, #5998d6 0%, #4a7eb8 100%);
    --shadow-light: 0 2px 10px rgba(89, 152, 214, 0.1);
    --shadow-medium: 0 4px 20px rgba(89, 152, 214, 0.15);
}

.header-top {
    background: var(--gradient-primary);
    color: white;
    font-size: 0.85rem;
}

.header-top i {
    color: #ffd700;
}

.header-top .contact-info {
    transition: all 0.3s ease;
}

.header-top .contact-info:hover {
    transform: translateY(-1px);
    color: #ffd700;
}

.main-navbar {
    background: #5998d6 !important;
    box-shadow: var(--shadow-medium);
    border-bottom: none;
}

.navbar-brand {
    position: relative;
}

.navbar-brand::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--gradient-primary);
    border-radius: 2px;
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.navbar-brand:hover::after {
    transform: scaleX(1);
}

.navbar-nav .nav-link {
    font-weight: 600;
    color: #ffffff !important;
    margin: 0 0.5rem;
    padding: 0.75rem 1rem !important;
    border-radius: 25px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.navbar-nav .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: var(--gradient-primary);
    transition: left 0.3s ease;
    z-index: -1;
    border-radius: 25px;
}

.navbar-nav .nav-link:hover::before,
.navbar-nav .nav-link.text-warning::before {
    left: 0;
}

.navbar-nav .nav-link:hover,
.navbar-nav .nav-link.text-warning {
    color: white !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-light);
}

.dropdown-menu {
    border: none;
    box-shadow: var(--shadow-medium);
    border-radius: 15px;
    padding: 1rem 0;
    margin-top: 0.5rem;
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    z-index: 1000;
    min-width: 200px;
    background-color: white;
}

.dropdown-menu.show {
    display: block;
}

.dropdown {
    position: relative;
}

.dropdown-item {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background: var(--gradient-primary);
    color: white;
    transform: translateX(5px);
}

.btn-logout {
    background: #ffffff;
    border: none;
    color: #5998d6;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-light);
}

.btn-logout:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
    color: white;
}

.btn-logout i {
    margin-right: 0.5rem;
}

.navbar-toggler {
    border: none;
    padding: 0.5rem;
}

.navbar-toggler:focus {
    box-shadow: none;
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2889, 152, 214, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Brand Logo Styling */
.brand-logo {
    font-size: 1.5rem;
    font-weight: 800;
    color: #ffffff !important;
    text-decoration: none;
}

.brand-logo:hover {
    -webkit-text-fill-color: transparent;
}

/* User Menu Styling */
.user-menu {
    margin-left: 1rem;
}

.btn-user-menu {
    background: rgba(255, 255, 255, 0.1);
    color: white !important;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 25px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-user-menu:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white !important;
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.btn-login {
    background: var(--gradient-primary);
    color: white !important;
    border: none;
    border-radius: 25px;
    padding: 0.5rem 1.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(89, 152, 214, 0.3);
}

.btn-login:hover {
    background: var(--gradient-secondary);
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(89, 152, 214, 0.4);
}

.dropdown-menu {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.dropdown-item {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    color: #333;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background: var(--gradient-primary);
    color: white;
    transform: translateX(5px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .header-top {
        font-size: 0.75rem;
        padding: 0.5rem 0;
    }
    
    .contact-info {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .navbar-nav .nav-link {
        margin: 0.25rem 0;
    }
}

/* Animation for mobile menu */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.navbar-collapse.show {
    animation: slideDown 0.3s ease;
}
</style>

<header>


  <!-- Main Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light main-navbar py-3" style="background-color: #5998d6;">
    <div class="container-fluid">
      <!-- Logo/Brand -->
      <a class="navbar-brand d-flex align-items-center" href="#" style="margin-left: 30px;">
        <div class="brand-logo">
          <i class="fas fa-hotel me-2" style="color: white; font-size: 1.8rem;"></i>
          MELISSA HOTEL
        </div>
      </a>

      <!-- Mobile Toggle Button -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Navigation Menu -->
      <div class="collapse navbar-collapse" id="navbarMain">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'home.php') ? 'text-warning' : ''; ?>" href="/pages/home.php">
              <i class="fas fa-home me-1"></i>Home
            </a>
          </li>
          
          <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'room.php') ? 'text-warning' : ''; ?>" href="/pages/room.php">
              <i class="fas fa-bed me-1"></i>Rooms
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'booking.php') ? 'text-warning' : ''; ?>" href="/pages/booking.php">
              <i class="fas fa-calendar-check me-1"></i>Book Now
            </a>
          </li>
          
          <li class="nav-item">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'text-warning' : ''; ?>" href="/pages/contact.php">
              <i class="fas fa-envelope me-1"></i>Contact
            </a>
          </li>
        </ul>

        <!-- User Menu -->
        <div class="user-menu">
          <?php if (isset($_SESSION['user']) || isset($_SESSION['user_name'])): ?>
            <!-- Logged in user -->
            <div class="dropdown">
              <button class="btn btn-user-menu dropdown-toggle" type="button" id="userDropdown" onclick="toggleUserDropdown()">
                <i class="fas fa-user-circle me-2"></i>
                <span><?php echo isset($_SESSION['user']['full_name']) ? $_SESSION['user']['full_name'] : $_SESSION['user_name']; ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" id="userDropdownMenu">
                <li><a class="dropdown-item" href="#" onclick="confirmLogout(event)"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
              </ul>
            </div>
          <?php else: ?>
            <!-- Not logged in -->
            <a href="login.php" class="btn btn-login">
              <i class="fas fa-sign-in-alt me-2"></i>
              <span>Login</span>
            </a>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
</header>

<!-- Bootstrap JS Bundle CDN (for dropdown, toggle) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Enhanced header interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to anchor links
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
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

    // Add active state management
    const currentPage = window.location.pathname;
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('text-warning');
        }
    });

    // Mobile menu auto-close
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const navbarLinks = document.querySelectorAll('.nav-link');
    
    navbarLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                bootstrap.Collapse.getInstance(navbarCollapse)?.hide();
            }
        });
    });

    // Header scroll effect
    let lastScrollTop = 0;
    const header = document.querySelector('header');
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 100) {
            header.style.transform = 'translateY(0)';
            header.style.position = 'fixed';
            header.style.top = '0';
            header.style.width = '100%';
            header.style.zIndex = '1030';
        } else {
            header.style.position = 'relative';
            header.style.transform = 'none';
        }
        
        lastScrollTop = scrollTop;
    });
});

// Logout function - direct logout without confirmation
function confirmLogout(event) {
    event.preventDefault();
    proceedLogout();
}

function proceedLogout() {
    // Kiểm tra xem đang ở trang nào để redirect đúng
    const currentPath = window.location.pathname;
    if (currentPath.includes('//')) {
        window.location.href = '/public/logout.php';
    } else {
        window.location.href = 'logout.php';
    }
}

// Function to toggle user dropdown menu
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdownMenu');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdownMenu');
    const dropdownButton = document.getElementById('userDropdown');
    
    if (dropdown && dropdownButton) {
        if (!dropdownButton.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    }
});
</script>
