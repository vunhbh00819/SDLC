// Utility function to get Vietnam time
function getVietnamTime() {
    return new Date(new Date().toLocaleString("en-US", {timeZone: "Asia/Ho_Chi_Minh"}));
}

// Function to format time for display
function formatTimeForDisplay(date) {
    return date.toLocaleDateString('vi-VN') + ' ' + 
           date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
}

// Function to show notifications
function showNotification(type, message, duration = 5000) {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.booking-notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show booking-notification`;
    notification.style.cssText = `
        position: fixed;
        top: 90px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        min-width: 400px;
        max-width: 600px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    `;
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle'} me-2"></i>
            <div>
                <strong>${type === 'error' ? 'Error!' : type === 'success' ? 'Success!' : 'Info:'}</strong>
                ${message}
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove
    if (duration > 0) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }
}

// Main Booking Manager
const BookingManager = {
    currentRate: 0,
    currentType: 'hour',
    roomId: 0,
    
    init: function(roomId, hourlyRate) {
        this.roomId = roomId;
        this.currentRate = hourlyRate;
        this.currentType = 'hour'; // Set default type
        this.initializePriceCalculator();
        this.setupEventListeners();
        
        // Set initial hidden field values
        setTimeout(() => {
            this.updateHiddenFields();
        }, 100);
    },
    
    initializePriceCalculator: function() {
        const durationOptions = document.querySelectorAll('.duration-option');
        durationOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                this.selectDurationType(e.target.closest('.duration-option'));
            });
        });
        
        const quantityInput = document.getElementById('quantityInput');
        if (quantityInput) {
            quantityInput.addEventListener('input', () => {
                this.updateTotalPrice();
                this.calculateCheckoutTime();
            });
        }
    },
    
    selectDurationType: function(option) {
        // Remove active class from all options
        document.querySelectorAll('.duration-option').forEach(opt => opt.classList.remove('active'));
        
        // Add active class to clicked option
        option.classList.add('active');
        
        // Update current rate and type
        this.currentRate = parseFloat(option.dataset.rate);
        this.currentType = option.dataset.type;
        
        // Reset quantity to 1
        const quantityInput = document.getElementById('quantityInput');
        if (quantityInput) {
            quantityInput.value = 1;
        }
        
        // Update hidden fields for form submission
        this.updateHiddenFields();
        
        // Update displays
        this.updateTotalPrice();
        this.updateDiscountInfo();
        
        // Recalculate checkout time with new duration type
        this.calculateCheckoutTime();
    },
    
    changeQuantity: function(change) {
        const input = document.getElementById('quantityInput');
        const currentValue = parseInt(input.value) || 1;
        const newValue = Math.max(1, Math.min(10, currentValue + change));
        
        input.value = newValue;
        
        // Update hidden fields for form submission
        this.updateHiddenFields();
        
        this.updateTotalPrice();
        this.calculateCheckoutTime();
    },
    
    updateHiddenFields: function() {
        const durationTypeInput = document.getElementById('duration_type');
        const durationQuantityInput = document.getElementById('duration_quantity');
        const quantityInput = document.getElementById('quantityInput');
        
        if (durationTypeInput) {
            durationTypeInput.value = this.currentType;
        }
        if (durationQuantityInput && quantityInput) {
            durationQuantityInput.value = quantityInput.value;
        }
    },
    
    updateTotalPrice: function() {
        const quantityInput = document.getElementById('quantityInput');
        const totalAmountElement = document.getElementById('totalAmount');
        
        if (!quantityInput || !totalAmountElement) return;
        
        const quantity = parseInt(quantityInput.value) || 1;
        const total = this.currentRate * quantity * 1000;
        
        totalAmountElement.textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VND';
        
        // Update hidden fields as well
        this.updateHiddenFields();
    },
    
    updateDiscountInfo: function() {
        const discountInfo = document.getElementById('discountInfo');
        if (!discountInfo) return;
        
        switch(this.currentType) {
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
    },
    
    calculateCheckoutTime: function() {
        const checkInInput = document.getElementById('inline_check_in') || document.getElementById('check_in');
        const checkOutInput = document.getElementById('inline_check_out') || document.getElementById('check_out');
        const quantityInput = document.getElementById('quantityInput');
        
        if (!checkInInput || !checkInInput.value || !quantityInput) return;
        
        // Convert input to Vietnam timezone
        const checkInStr = checkInInput.value; // Format: "2025-07-25T14:30"
        const [datePart, timePart] = checkInStr.split('T');
        const [year, month, day] = datePart.split('-');
        const [hours, minutes] = timePart.split(':');
        
        // Create date in Vietnam timezone
        const checkInDate = new Date(Date.UTC(year, month - 1, day, hours - 7, minutes)); // UTC+7 for Vietnam
        const quantity = parseInt(quantityInput.value) || 1;
        
        // Create new date object for checkout calculation
        let checkOutTime = checkInDate.getTime();
        
        // Add time based on duration type and quantity
        switch(this.currentType) {
            case 'hour':
                // Add hours
                checkOutTime += quantity * 60 * 60 * 1000;
                break;
            case 'day':
                // Add days (quantity * 24 hours * 60 minutes * 60 seconds * 1000 milliseconds)
                checkOutTime += quantity * 24 * 60 * 60 * 1000;
                break;
            case 'week':
                // Add weeks (quantity * 7 days * 24 hours * 60 minutes * 60 seconds * 1000 milliseconds)
                checkOutTime += quantity * 7 * 24 * 60 * 60 * 1000;
                break;
            case 'month':
                // Add months (approximate - 30 days)
                checkOutTime += quantity * 30 * 24 * 60 * 60 * 1000;
                break;
        }
        
        const checkOutDate = new Date(checkOutTime);
        
        // Set the checkout time
        if (checkOutInput) {
            checkOutInput.value = checkOutDate.toISOString().slice(0, 16);
            
            // Update booking summary if it exists
            this.updateBookingSummary();
        }
    },
    
    updateBookingSummary: function() {
        const checkIn = document.getElementById('inline_check_in')?.value;
        const checkOut = document.getElementById('inline_check_out')?.value;
        const summaryDuration = document.getElementById('summaryDuration');
        const finalTotal = document.getElementById('finalTotalAmount');
        const quantityInput = document.getElementById('quantityInput');
        const summaryCheckIn = document.getElementById('summaryCheckIn');
        const summaryCheckOut = document.getElementById('summaryCheckOut');
        
        if (checkIn && checkOut && summaryDuration && finalTotal && quantityInput) {
            const quantity = parseInt(quantityInput.value) || 1;
            
            // Calculate duration text
            let durationText = '';
            switch(this.currentType) {
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
            
            // Format and display check-in/check-out times with correct timezone
            if (summaryCheckIn && summaryCheckOut) {
                const checkInDate = new Date(checkIn);
                const checkOutDate = new Date(checkOut);
                
                // Format dates in Vietnam timezone
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
            
            const totalAmount = this.currentRate * quantity * 1000;
            finalTotal.textContent = new Intl.NumberFormat('vi-VN').format(totalAmount) + ' VND';
        }
    },
    
    showBookingForm: function() {
        const bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
        
        // Set current Vietnam time
        const vietnamTime = getVietnamTime();
        const minDateTime = vietnamTime.toISOString().slice(0, 16);
        
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        
        checkInInput.min = minDateTime;
        checkOutInput.min = minDateTime;
        
        // Set default check-in to Vietnam time + 1 hour
        const defaultCheckIn = new Date(vietnamTime.getTime() + 60 * 60 * 1000);
        checkInInput.value = defaultCheckIn.toISOString().slice(0, 16);
        
        // Calculate checkout based on current duration type and quantity
        const quantity = parseInt(document.getElementById('quantityInput')?.value) || 1;
        let checkOutTime = defaultCheckIn.getTime();
        
        switch(this.currentType) {
            case 'hour':
                checkOutTime += quantity * 60 * 60 * 1000;
                break;
            case 'day':
                checkOutTime += quantity * 24 * 60 * 60 * 1000;
                break;
            case 'week':
                checkOutTime += quantity * 7 * 24 * 60 * 60 * 1000;
                break;
            case 'month':
                checkOutTime += quantity * 30 * 24 * 60 * 60 * 1000;
                break;
        }
        
        const defaultCheckOut = new Date(checkOutTime);
        
        checkOutInput.value = defaultCheckOut.toISOString().slice(0, 16);
        
        // Pre-fill user data if available
        if (window.userData) {
            document.getElementById('user_name').value = window.userData.fullName || '';
            document.getElementById('user_email').value = window.userData.email || '';
            document.getElementById('user_phone').value = window.userData.phone || '';
        }
        
        this.setupModalEventListeners();
        this.updateModalPrice();
        this.validateModalForm();
        
        bookingModal.show();
    },
    
    toggleBookingForm: function() {
        const formSection = document.getElementById('bookingFormSection');
        if (formSection.style.display === 'none' || formSection.style.display === '') {
            this.showInlineBookingForm();
        } else {
            this.hideBookingForm();
        }
    },
    
    showInlineBookingForm: function() {
        const formSection = document.getElementById('bookingFormSection');
        formSection.style.display = 'block';
        
        // Scroll to form
        formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Set default check-in time to Vietnam time + 1 hour
        const vietnamTime = getVietnamTime();
        const defaultCheckIn = new Date(vietnamTime.getTime() + 60 * 60 * 1000);
        const checkInInput = document.getElementById('inline_check_in');
        
        if (checkInInput) {
            checkInInput.value = defaultCheckIn.toISOString().slice(0, 16);
            // Calculate checkout time immediately after setting check-in
            this.calculateCheckoutTime();
        }
        
        // Pre-fill user data
        if (window.userData) {
            const nameInput = document.getElementById('inline_user_name');
            const emailInput = document.getElementById('inline_user_email');
            const phoneInput = document.getElementById('inline_user_phone');
            
            if (nameInput) nameInput.value = window.userData.fullName || '';
            if (emailInput) emailInput.value = window.userData.email || '';
            if (phoneInput) phoneInput.value = window.userData.phone || '';
        }
        
        this.setupInlineEventListeners();
    },
    
    hideBookingForm: function() {
        const formSection = document.getElementById('bookingFormSection');
        formSection.style.display = 'none';
    },
    
    setupEventListeners: function() {
        // Event delegation for dynamic content
        document.body.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-book-now') || e.target.closest('.btn-book-now')) {
                const button = e.target.classList.contains('btn-book-now') ? e.target : e.target.closest('.btn-book-now');
                const roomId = button.getAttribute('data-room-id');
                if (roomId) {
                    window.location.href = `booking.php?room_id=${roomId}`;
                }
            }
            
            if (e.target.classList.contains('room-image')) {
                const fullImg = e.target.getAttribute('data-full-img');
                if (fullImg) {
                    this.openImageModal(fullImg);
                }
            }
        });
        
        // Keyboard events
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeImageModal();
            }
        });
        
        // Image modal events
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('click', (e) => {
                if (e.target === imageModal) {
                    this.closeImageModal();
                }
            });
        }
        
        // Main room image click
        const mainImage = document.getElementById('mainRoomImage');
        if (mainImage) {
            mainImage.addEventListener('click', () => {
                this.openImageModal(mainImage.src);
            });
        }
    },
    
    setupModalEventListeners: function() {
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        
        checkInInput.addEventListener('input', () => {
            this.updateModalPrice();
            this.validateModalForm();
        });
        
        checkOutInput.addEventListener('input', () => {
            this.updateModalPrice();
            this.validateModalForm();
        });
        
        // Form validation listeners
        ['user_name', 'user_email', 'user_phone'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('input', () => this.validateModalForm());
            }
        });
        
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', () => this.validateModalForm());
        });
        
        // Form submission
        const bookingForm = document.getElementById('bookingForm');
        if (bookingForm) {
            bookingForm.addEventListener('submit', (e) => this.handleFormSubmission(e));
        }
    },
    
    setupInlineEventListeners: function() {
        const checkInInput = document.getElementById('inline_check_in');
        if (checkInInput) {
            checkInInput.addEventListener('change', () => {
                this.calculateCheckoutTime();
                this.updateBookingSummary();
            });
        }
        
        const guestsSelect = document.getElementById('inline_guests');
        const summaryGuests = document.getElementById('summaryGuests');
        if (guestsSelect && summaryGuests) {
            guestsSelect.addEventListener('change', () => {
                summaryGuests.textContent = guestsSelect.value;
            });
        }
    },
    
    validateModalForm: function() {
        const userName = document.getElementById('user_name').value.trim();
        const userEmail = document.getElementById('user_email').value.trim();
        const userPhone = document.getElementById('user_phone').value.trim();
        const checkIn = document.getElementById('check_in').value;
        const checkOut = document.getElementById('check_out').value;
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        const confirmBtn = document.getElementById('confirmBookingBtn');
        
        let isValid = true;
        let errorMessage = '';
        
        // Validation logic
        if (!userName) {
            isValid = false;
            errorMessage = 'Please enter your full name';
        } else if (!userEmail) {
            isValid = false;
            errorMessage = 'Please enter your email address';
        } else if (!userPhone) {
            isValid = false;
            errorMessage = 'Please enter your phone number';
        } else if (!checkIn) {
            isValid = false;
            errorMessage = 'Please select check-in date and time';
        } else if (!checkOut) {
            isValid = false;
            errorMessage = 'Please select check-out date and time';
        } else if (!paymentMethod) {
            isValid = false;
            errorMessage = 'Please select a payment method';
        }
        
        // Email validation
        if (isValid && userEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(userEmail)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }
        
        // Phone validation
        if (isValid && userPhone && !/^[0-9+\-\s()]{10,}$/.test(userPhone)) {
            isValid = false;
            errorMessage = 'Please enter a valid phone number (at least 10 digits)';
        }
        
        // Date validation
        if (isValid && checkIn && checkOut) {
            const startDate = new Date(checkIn);
            const endDate = new Date(checkOut);
            const vietnamNow = getVietnamTime();
            
            if (startDate <= vietnamNow) {
                isValid = false;
                errorMessage = 'Check-in time must be in the future (Vietnam time)';
            } else if (endDate <= startDate) {
                isValid = false;
                errorMessage = 'Check-out time must be after check-in time';
            }
        }
        
        // Update button state
        if (isValid) {
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('btn-secondary');
            confirmBtn.classList.add('btn-primary');
            confirmBtn.title = 'Click to confirm your booking';
            this.removeValidationAlert();
        } else {
            confirmBtn.disabled = true;
            confirmBtn.classList.remove('btn-primary');
            confirmBtn.classList.add('btn-secondary');
            confirmBtn.title = errorMessage;
            this.showValidationAlert(errorMessage);
        }
        
        return isValid;
    },
    
    showValidationAlert: function(message) {
        this.removeValidationAlert();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning alert-sm booking-validation-alert mt-2';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <small>${message}</small>
        `;
        
        const confirmBtn = document.getElementById('confirmBookingBtn');
        confirmBtn.parentNode.insertBefore(alertDiv, confirmBtn.nextSibling);
    },
    
    removeValidationAlert: function() {
        const existingAlert = document.querySelector('.booking-validation-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
    },
    
    updateModalPrice: function() {
        const checkIn = document.getElementById('check_in').value;
        const checkOut = document.getElementById('check_out').value;
        
        if (!checkIn || !checkOut) {
            document.getElementById('durationDisplay').textContent = '-';
            document.getElementById('modalTotalAmount').textContent = '-';
            return;
        }
        
        const start = new Date(checkIn);
        const end = new Date(checkOut);
        const vietnamNow = getVietnamTime();
        
        if (end <= start) {
            document.getElementById('durationDisplay').textContent = 'Invalid: Check-out must be after check-in';
            document.getElementById('modalTotalAmount').textContent = '-';
            return;
        }
        
        if (start <= vietnamNow) {
            document.getElementById('durationDisplay').textContent = 'Invalid: Check-in must be in the future';
            document.getElementById('modalTotalAmount').textContent = '-';
            return;
        }
        
        const diffTime = Math.abs(end - start);
        const diffHours = diffTime / (1000 * 60 * 60);
        
        if (diffHours > 0 && window.roomData) {
            const total = Math.ceil(diffHours * window.roomData.hourlyRate);
            
            // Format duration display
            const days = Math.floor(diffHours / 24);
            const hours = Math.floor(diffHours % 24);
            const minutes = Math.floor((diffHours % 1) * 60);
            
            let durationText = '';
            if (days > 0) durationText += days + ' day' + (days > 1 ? 's' : '') + ' ';
            if (hours > 0) durationText += hours + ' hour' + (hours > 1 ? 's' : '') + ' ';
            if (minutes > 0) durationText += minutes + ' minute' + (minutes > 1 ? 's' : '');
            
            document.getElementById('durationDisplay').textContent = durationText.trim() || 'Less than 1 minute';
            document.getElementById('modalTotalAmount').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VND';
        }
    },
    
    selectPayment: function(method) {
        document.querySelectorAll('.payment-option').forEach(option => {
            option.classList.remove('selected');
            option.style.borderColor = '#dee2e6';
            option.style.backgroundColor = '#ffffff';
        });
        
        const clickedOption = event.currentTarget;
        clickedOption.classList.add('selected');
        clickedOption.style.borderColor = '#0d6efd';
        clickedOption.style.backgroundColor = '#e7f3ff';
        
        const radioButton = document.getElementById(method);
        if (radioButton) {
            radioButton.checked = true;
        }
        
        this.validateModalForm();
    },
    
    selectInlinePayment: function(method) {
        document.querySelectorAll('.payment-option-card .payment-card').forEach(card => {
            card.classList.remove('border-primary', 'border-3');
        });
        
        const selectedCard = document.querySelector(`#inline_${method}`);
        if (selectedCard) {
            selectedCard.checked = true;
            selectedCard.closest('.payment-option-card').querySelector('.payment-card').classList.add('border-primary', 'border-3');
        }
    },
    
    handleFormSubmission: function(e) {
        e.preventDefault();
        
        if (!this.validateModalForm()) {
            showNotification('error', 'Please complete all required fields correctly before submitting.');
            return;
        }
        
        const formData = new FormData(e.target);
        this.showPaymentModal(formData);
    },
    
    showPaymentModal: function(formData) {
        const paymentMethod = formData.get('payment_method');
        const checkIn = formData.get('check_in');
        const checkOut = formData.get('check_out');
        
        const start = new Date(checkIn);
        const end = new Date(checkOut);
        const diffTime = Math.abs(end - start);
        const diffHours = diffTime / (1000 * 60 * 60);
        const total = Math.ceil(diffHours * window.roomData.hourlyRate);
        
        // Close booking modal
        bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
        
        // Update payment modal content
        const bankNames = {
            'vietcombank': 'Vietcombank',
            'techcombank': 'Techcombank', 
            'bidv': 'BIDV'
        };
        
        const qrUrls = {
            'vietcombank': `https://img.vietqr.io/image/970436-0123456789-compact2.png?amount=${total}&addInfo=Hotel%20Booking%20${window.roomData.code}`,
            'techcombank': `https://img.vietqr.io/image/970407-0123456789-compact2.png?amount=${total}&addInfo=Hotel%20Booking%20${window.roomData.code}`,
            'bidv': `https://img.vietqr.io/image/970418-0123456789-compact2.png?amount=${total}&addInfo=Hotel%20Booking%20${window.roomData.code}`
        };
        
        document.getElementById('bankName').textContent = bankNames[paymentMethod];
        document.getElementById('qrCodeImage').src = qrUrls[paymentMethod];
        document.getElementById('paymentAmount').textContent = new Intl.NumberFormat('vi-VN').format(total) + ' VND';
        document.getElementById('paymentRoom').textContent = `Room ${window.roomData.code}`;
        
        // Show payment modal
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
        
        // Countdown timer
        this.startPaymentCountdown(formData);
    },
    
    startPaymentCountdown: function(formData) {
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(timer);
                this.submitBookingForm(formData);
            }
        }, 1000);
    },
    
    submitBookingForm: function(formData) {
        const hiddenForm = document.createElement('form');
        hiddenForm.method = 'POST';
        hiddenForm.style.display = 'none';
        
        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            hiddenForm.appendChild(input);
        }
        
        document.body.appendChild(hiddenForm);
        hiddenForm.submit();
    },
    
    // Image functions
    zoomImage: function() {
        const img = document.getElementById('mainRoomImage');
        if (img) img.style.transform = 'scale(1.5)';
    },
    
    resetImage: function() {
        const img = document.getElementById('mainRoomImage');
        if (img) img.style.transform = 'scale(1)';
    },
    
    openImageModal: function(src) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        if (modal && modalImg) {
            modalImg.src = src;
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('show'), 10);
        }
    },
    
    closeImageModal: function() {
        const modal = document.getElementById('imageModal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 300);
        }
    }
};

// Export for global access
window.BookingManager = BookingManager;
window.BookingUtils = {
    getVietnamTime,
    formatTimeForDisplay,
    showNotification
};
