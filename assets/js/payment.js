// ── Payment Script ──────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    initializePaymentHandlers();
    setupRentalMonthsToggle();
});

// ── Initialize Payment Form Handlers ────────────────────────────────────────
const initializePaymentHandlers = () => {
    const typeSelect = document.getElementById('type');
    if (typeSelect) {
        typeSelect.addEventListener('change', (e) => {
            handleOrderTypeChange(e.target.value);
        });
    }

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', (e) => {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
    }
};

// ── Toggle Rental Months Input ──────────────────────────────────────────────
const setupRentalMonthsToggle = () => {
    const typeSelect = document.getElementById('type');
    const rentalMonthsDiv = document.getElementById('rentalMonths');

    if (typeSelect && rentalMonthsDiv) {
        // Handle initial state
        if (typeSelect.value === 'rental') {
            rentalMonthsDiv.style.display = 'block';
        }
    }
};

// ── Handle Order Type Change ────────────────────────────────────────────────
const handleOrderTypeChange = (type) => {
    const rentalMonthsDiv = document.getElementById('rentalMonths');
    const rentalMonthsInput = document.getElementById('rental_months');

    if (rentalMonthsDiv) {
        if (type === 'rental') {
            rentalMonthsDiv.style.display = 'block';
            if (rentalMonthsInput) rentalMonthsInput.required = true;
        } else {
            rentalMonthsDiv.style.display = 'none';
            if (rentalMonthsInput) rentalMonthsInput.required = false;
        }
    }

    // Update total price display if exists
    updatePriceDisplay(type);
};

// ── Update Price Display ────────────────────────────────────────────────────
const updatePriceDisplay = (type) => {
    const priceDisplay = document.getElementById('totalPrice');
    if (!priceDisplay) return;

    const cartItems = document.querySelectorAll('[id^="book-"]').length;
    
    if (type === 'buy') {
        priceDisplay.textContent = (cartItems * 50).toFixed(2) + ' MAD';
    } else {
        const rentalMonths = parseInt(document.getElementById('rental_months')?.value || 1);
        priceDisplay.textContent = (cartItems * 10 * rentalMonths).toFixed(2) + ' MAD';
    }
};

// ── Form Validation ─────────────────────────────────────────────────────────
const validateForm = () => {
    const name = document.getElementById('name')?.value?.trim();
    const city = document.getElementById('city')?.value?.trim();
    const phone = document.getElementById('phone')?.value?.trim();
    const type = document.getElementById('type')?.value;
    const rentalMonths = document.getElementById('rental_months')?.value;

    if (!name || !city || !phone) {
        showNotification('Please fill all required fields', 'error');
        return false;
    }

    // Validate phone number (Moroccan format)
    if (!/^06\d{8}$/.test(phone)) {
        showNotification('Please enter a valid Moroccan phone number (06XXXXXXXX)', 'error');
        return false;
    }

    if (type === 'rental' && (!rentalMonths || parseInt(rentalMonths) < 1 || parseInt(rentalMonths) > 12)) {
        showNotification('Please enter a valid number of rental months (1-12)', 'error');
        return false;
    }

    return true;
};

// ── Show Notification ───────────────────────────────────────────────────────
const showNotification = (message, type = 'info') => {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) return;

    const alertClass = {
        success: 'alert-success',
        error: 'alert-error',
        info: 'alert-info'
    }[type] || 'alert-info';

    alertBox.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
    
    // Auto-hide after 4 seconds
    setTimeout(() => {
        alertBox.innerHTML = '';
    }, 4000);
};
