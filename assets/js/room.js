document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        mirror: false
    });

    // Initialize countdown timers
    initializeCountdownTimers();

    // Tạo modal xem ảnh
    const imageModal = document.createElement('div');
    imageModal.className = 'image-modal';
    imageModal.innerHTML = `
        <span class="close-modal">×</span>
        <img class="modal-image" src="" alt="Room Preview">
    `;
    document.body.appendChild(imageModal);

    // Handle modal close
    const closeModal = imageModal.querySelector('.close-modal');
    closeModal.onclick = () => {
        imageModal.classList.remove('show');
        setTimeout(() => {
            imageModal.style.display = 'none';
        }, 300);
    };

    // Đóng modal khi click ngoài ảnh
    imageModal.onclick = (e) => {
        if (e.target === imageModal) {
            closeModal.click();
        }
    };

    // Get filter elements and pagination
    const typeFilter = document.getElementById('typeFilter');
    const capacityFilter = document.getElementById('capacityFilter');
    const statusFilter = document.getElementById('statusFilter');
    const roomsContainer = document.getElementById('rooms-container') || document.querySelector('.row.g-4');
    const paginationContainer = document.querySelector('.pagination');
    let currentPage = 1;

    // Handle click on room image
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('room-image')) {
            const fullImg = e.target.getAttribute('data-full-img');
            const modalImg = imageModal.querySelector('.modal-image');
            modalImg.src = fullImg;
            imageModal.style.display = 'block';
            setTimeout(() => {
                imageModal.classList.add('show');
            }, 10);
        }
    });

    // Handle ESC key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && imageModal.classList.contains('show')) {
            closeModal.click();
        }
    });

    // Function to fetch and display rooms
    async function fetchAndDisplayRooms(page = 1) {
        // Hiển thị loading state
        roomsContainer.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

        // Get filter values
        const type = typeFilter.value;
        const capacity = capacityFilter.value;
        const status = statusFilter.value;
        currentPage = page;

        try {
            // Tạo URL với các tham số lọc
            const url = new URL('../public/filter_room.php', window.location.origin + window.location.pathname);
            if (type) url.searchParams.append('type', type);
            if (capacity) url.searchParams.append('capacity', capacity);
            if (status) url.searchParams.append('status', status);
            url.searchParams.append('page', page);

            const response = await fetch(url);
            const data = await response.json();

            if (data.status === 'success') {
                // Hiển thị kết quả
                roomsContainer.innerHTML = data.rooms.join('');
                
                // Khởi tạo lại AOS cho các phần tử mới
                AOS.refresh();

                // Initialize countdown timers for newly loaded rooms
                initializeCountdownTimers();

                // Cập nhật URL để có thể share/bookmark
                const currentUrl = new URL(window.location.href);
                if (type) currentUrl.searchParams.set('type', type);
                else currentUrl.searchParams.delete('type');
                if (capacity) currentUrl.searchParams.set('capacity', capacity);
                else currentUrl.searchParams.delete('capacity');
                if (status) currentUrl.searchParams.set('status', status);
                else currentUrl.searchParams.delete('status');
                currentUrl.searchParams.set('page', currentPage);
                window.history.pushState({}, '', currentUrl);

                // Hiển thị thông báo nếu không có kết quả
                if (data.total === 0) {
                    roomsContainer.innerHTML = '<div class="col-12"><div class="alert alert-info text-center"><i class="fas fa-info-circle me-2"></i>No rooms found matching the filter criteria.</div></div>';
                }

                // Cập nhật phân trang
                updatePagination(data.totalPages);
            }
        } catch (error) {
            console.error('Error:', error);
            roomsContainer.innerHTML = '<div class="col-12"><div class="alert alert-danger text-center"><i class="fas fa-exclamation-triangle me-2"></i>An error occurred while loading data. Please try again later.</div></div>';
        }
    }

    // Function to update pagination
    function updatePagination(totalPages) {
        if (!paginationContainer) return;
        
        let paginationHTML = '';
        
        // Nút Previous
        if (currentPage > 1) {
            paginationHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>`;
        }

        // Các nút số trang
        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 || // Luôn hiện trang đầu
                i === totalPages || // Luôn hiện trang cuối
                (i >= currentPage - 1 && i <= currentPage + 1) // Hiện 1 trang trước và sau trang hiện tại
            ) {
                paginationHTML += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
            } else if (
                (i === currentPage - 2 && currentPage > 3) ||
                (i === currentPage + 2 && currentPage < totalPages - 2)
            ) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        // Nút Next
        if (currentPage < totalPages) {
            paginationHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${currentPage + 1}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>`;
        }

        paginationContainer.innerHTML = paginationHTML;

        // Thêm event listeners cho các nút phân trang
        paginationContainer.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(e.target.closest('.page-link').dataset.page);
                if (page && page !== currentPage) {
                    fetchAndDisplayRooms(page);
                }
            });
        });
    }

    // Thêm event listeners cho các filter
    [typeFilter, capacityFilter, statusFilter].forEach(filter => {
        filter.addEventListener('change', fetchAndDisplayRooms);
    });

    // Khôi phục trạng thái filter từ URL khi tải trang
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('type')) typeFilter.value = urlParams.get('type');
    if (urlParams.has('capacity')) capacityFilter.value = urlParams.get('capacity');
    if (urlParams.has('status')) statusFilter.value = urlParams.get('status');
    if (urlParams.has('page')) currentPage = parseInt(urlParams.get('page'));

    // Kiểm tra nếu có filter hoặc phân trang, thì gọi AJAX, ngược lại dùng dữ liệu từ server
    const hasFilters = urlParams.has('type') || urlParams.has('capacity') || urlParams.has('status') || (urlParams.has('page') && parseInt(urlParams.get('page')) > 1);
    
    if (hasFilters) {
        // Nếu có filter hoặc không phải trang 1, gọi AJAX để lấy dữ liệu
        fetchAndDisplayRooms(currentPage);
    }

    // Handle book room button click
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-book-now') || e.target.closest('.btn-book-now')) {
            const button = e.target.classList.contains('btn-book-now') ? e.target : e.target.closest('.btn-book-now');
            const roomId = button.getAttribute('data-room-id');
            
            if (roomId) {
                // Chuyển hướng đến trang đặt phòng
                window.location.href = `booking.php?room_id=${roomId}`;
            }
        }
    });

    // Thêm debounce để tránh gọi API quá nhiều
    let debounceTimer;
    function debounce(func, delay) {
        return function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(this, arguments), delay);
        }
    }

    // Áp dụng debounce cho hàm fetch
    const debouncedFetch = debounce(fetchAndDisplayRooms, 300);

    // Sự kiện cho các filter với debounce
    [typeFilter, capacityFilter, statusFilter].forEach(filter => {
        filter.removeEventListener('change', fetchAndDisplayRooms);
        filter.addEventListener('change', debouncedFetch);
    });
});

function calculateTotalPrice(hourlyRate, duration, durationType) {
    let hours = 0;
    switch(durationType) {
        case 'hour':
            hours = duration;
            break;
        case 'day':
            hours = duration * 24;
            break;
        case 'week':
            hours = duration * 24 * 7;
            break;
        case 'month':
            hours = duration * 24 * 30; // Giả định 1 tháng = 30 ngày
            break;
    }
    return hourlyRate * hours;
}

function updatePrice(formElement) {
    const hourlyRate = parseFloat(formElement.getAttribute('data-hourly-rate'));
    const duration = parseInt(formElement.querySelector('input[name="duration"]').value);
    const durationType = formElement.querySelector('select[name="duration_type"]').value;
    
    const total = calculateTotalPrice(hourlyRate, duration, durationType);
    
    // Cập nhật hiển thị giá
    const priceDisplay = formElement.querySelector('.total-price');
    if (priceDisplay) {
        priceDisplay.textContent = `Tổng tiền: $${total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    }
}

// Add countdown timer functionality
function initializeCountdownTimers() {
    const countdownElements = document.querySelectorAll('.countdown-timer');
    
    if (countdownElements.length === 0) return;

    countdownElements.forEach(function(element) {
        const endTimeStr = element.getAttribute('data-end-time');
        if (!endTimeStr) return;

        updateCountdownTimer(element, endTimeStr);
        
        // Update countdown every second
        const interval = setInterval(function() {
            updateCountdownTimer(element, endTimeStr);
        }, 1000);

        // Store interval ID for cleanup if needed
        element.setAttribute('data-interval-id', interval);
    });
}

function updateCountdownTimer(element, endTimeStr) {
    const endTime = new Date(endTimeStr);
    const now = new Date();
    const timeDiff = endTime - now;

    if (timeDiff <= 0) {
        // Time is up, room is available
        element.innerHTML = '<i class="fas fa-check-circle text-success"></i> Phòng đã có sẵn';
        element.classList.remove('countdown-active');
        element.classList.add('countdown-expired');
        
        // Clear the interval
        const intervalId = element.getAttribute('data-interval-id');
        if (intervalId) {
            clearInterval(parseInt(intervalId));
        }
        
        // Update room status in the UI automatically
        const roomCard = element.closest('.room-card');
        if (roomCard) {
            const statusBadge = roomCard.querySelector('.room-status-badge');
            const bookBtn = roomCard.querySelector('.book-btn');
            
            if (statusBadge) {
                statusBadge.className = 'room-status-badge available';
                statusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Available';
            }
            
            if (bookBtn) {
                bookBtn.className = 'book-btn btn-book-now';
                bookBtn.disabled = false;
                bookBtn.innerHTML = '<i class="fas fa-calendar-check me-2"></i>Book Now';
                bookBtn.setAttribute('data-room-id', bookBtn.getAttribute('data-room-id'));
            }
            
            // Update database status
            updateRoomStatusInDatabase(roomCard, bookBtn.getAttribute('data-room-id'));
        }
        return;
    }

    // Calculate remaining time
    const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);

    // Format the display with seconds always shown
    let timeStr = '';
    let urgencyClass = '';
    
    if (days > 0) {
        timeStr = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        urgencyClass = 'countdown-normal';
    } else if (hours > 0) {
        timeStr = `${hours}h ${minutes}m ${seconds}s`;
        urgencyClass = hours < 2 ? 'countdown-warning' : 'countdown-normal';
    } else if (minutes > 0) {
        timeStr = `${minutes}m ${seconds}s`;
        urgencyClass = minutes < 10 ? 'countdown-urgent' : 'countdown-warning';
    } else {
        timeStr = `${seconds}s`;
        urgencyClass = 'countdown-critical';
    }

    element.innerHTML = `<i class="fas fa-clock"></i> Times: ${timeStr}`;
    element.classList.add('countdown-active');
    element.classList.remove('countdown-normal', 'countdown-warning', 'countdown-urgent', 'countdown-critical');
    element.classList.add(urgencyClass);
}

// Function to update room status in database
async function updateRoomStatusInDatabase(roomCard, roomId) {
    if (!roomId) return;
    
    try {
        // Add loading indicator
        const statusBadge = roomCard.querySelector('.room-status-badge');
        if (statusBadge) {
            statusBadge.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        }
        
        const response = await fetch('../public/update_room_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                room_id: parseInt(roomId)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            console.log('Room status updated successfully:', result.message);
            
            // Update the status badge to show success
            if (statusBadge) {
                statusBadge.className = 'room-status-badge available';
                statusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Available';
            }
            
            // Show success notification (optional)
            showNotification('Room is now available!', 'success');
            
        } else {
            console.error('Failed to update room status:', result.message);
            
            // Revert status badge on error
            if (statusBadge) {
                statusBadge.className = 'room-status-badge booked';
                statusBadge.innerHTML = '<i class="fas fa-times-circle"></i> Booked';
            }
            
            showNotification('Failed to update room status', 'error');
        }
        
    } catch (error) {
        console.error('Error updating room status:', error);
        showNotification('Network error occurred', 'error');
    }
}

// Function to show notifications
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; animation: slideInRight 0.3s ease;';
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}
