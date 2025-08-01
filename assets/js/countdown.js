// Khởi tạo đếm ngược
function initializeCountdownTimers() {
    // Khởi tạo đếm ngược cho phòng đang sử dụng
    document.querySelectorAll('[data-current-status="occupied"]').forEach(room => {
        const timeInfoElement = room.querySelector('.room-time-info');
        if (timeInfoElement) {
            const endTimeText = timeInfoElement.textContent.match(/Kết thúc: (\d{2}\/\d{2}\/\d{4} \d{2}:\d{2})/);
            if (endTimeText) {
                const endTime = parseVietnameseDateTime(endTimeText[1]);
                updateCountdown(timeInfoElement, endTime);
                setInterval(() => updateCountdown(timeInfoElement, endTime), 60000); // Cập nhật mỗi phút
            }
        }
    });

    // Khởi tạo đếm ngược cho phòng đã đặt
    document.querySelectorAll('[data-current-status="reserved"]').forEach(room => {
        const timeInfoElement = room.querySelector('.room-time-info');
        if (timeInfoElement) {
            const startTimeText = timeInfoElement.textContent.match(/Bắt đầu: (\d{2}\/\d{2}\/\d{4} \d{2}:\d{2})/);
            if (startTimeText) {
                const startTime = parseVietnameseDateTime(startTimeText[1]);
                updateCountdown(timeInfoElement, startTime);
                setInterval(() => updateCountdown(timeInfoElement, startTime), 60000); // Cập nhật mỗi phút
            }
        }
    });
}

// Chuyển đổi định dạng ngày giờ Việt Nam
function parseVietnameseDateTime(dateTimeStr) {
    const [date, time] = dateTimeStr.split(' ');
    const [day, month, year] = date.split('/');
    const [hours, minutes] = time.split(':');
    return new Date(year, month - 1, day, hours, minutes);
}

// Cập nhật hiển thị đếm ngược
function updateCountdown(element, targetTime) {
    const now = new Date();
    const timeDiff = targetTime - now;
    
    if (timeDiff <= 0) {
        // Tải lại trang khi hết thời gian
        window.location.reload();
        return;
    }

    const hours = Math.floor(timeDiff / (1000 * 60 * 60));
    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
    
    const timeRemainingText = hours > 0 ? 
        `${hours}h ${minutes}m` : 
        `${minutes}m`;

    // Cập nhật hiển thị đếm ngược và badge
    const countdownElement = element.querySelector('small');
    const statusBadge = element.closest('.room-card').querySelector('.room-status-badge');
    
    if (countdownElement) {
        if (element.closest('[data-current-status="occupied"]')) {
            countdownElement.innerHTML = `
                <i class="fas fa-clock me-1"></i>
                Kết thúc: ${targetTime.toLocaleString('vi-VN')}
                <br><i class="fas fa-hourglass-half me-1"></i>Còn lại: ${timeRemainingText}`;
            
            if (statusBadge) {
                statusBadge.innerHTML = `
                    <i class="fas fa-user-clock me-1"></i>
                    Đang sử dụng (${timeRemainingText})`;
            }
        } else {
            countdownElement.innerHTML = `
                <i class="fas fa-calendar-clock me-1"></i>
                Bắt đầu: ${targetTime.toLocaleString('vi-VN')}
                <br><i class="fas fa-hourglass-start me-1"></i>Còn: ${timeRemainingText}`;
            
            if (statusBadge) {
                statusBadge.innerHTML = `
                    <i class="fas fa-calendar-check me-1"></i>
                    Đã đặt (${timeRemainingText})`;
            }
        }
    }
}

// Khởi tạo khi trang đã load
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo đếm ngược
    initializeCountdownTimers();
    
    // Cập nhật trạng thái và thời gian mỗi phút
    setInterval(function() {
        initializeCountdownTimers();
    }, 60000);
});
