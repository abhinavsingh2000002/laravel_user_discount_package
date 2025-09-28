/**
 * CSRF Token Helper for Laravel User Discounts Package
 */

// Get CSRF token from meta tag
function getCSRFToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : null;
}

// Get headers with CSRF token
function getCSRFHeaders() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCSRFToken(),
        'Accept': 'application/json'
    };
}

// Make authenticated fetch request
function authenticatedFetch(url, options = {}) {
    const defaultOptions = {
        headers: getCSRFHeaders(),
        ...options
    };

    return fetch(url, defaultOptions);
}

// Assign discount with CSRF protection
function assignDiscount(userId, discountId) {
    return authenticatedFetch('/api/discounts/assign', {
        method: 'POST',
        body: JSON.stringify({
            user_id: userId,
            discount_id: discountId
        })
    });
}

// Revoke discount with CSRF protection
function revokeDiscount(userId, discountId) {
    return authenticatedFetch('/api/discounts/revoke', {
        method: 'POST',
        body: JSON.stringify({
            user_id: userId,
            discount_id: discountId
        })
    });
}

// Apply discount with CSRF protection
function applyDiscount(userId, amount) {
    return authenticatedFetch('/api/discounts/apply', {
        method: 'POST',
        body: JSON.stringify({
            user_id: userId,
            amount: amount
        })
    });
}

// Get eligible discounts
function getEligibleDiscounts(userId) {
    return authenticatedFetch(`/api/discounts/eligible?user_id=${userId}`);
}

// Get user discounts
function getUserDiscounts(userId) {
    return authenticatedFetch(`/api/discounts/user/${userId}/discounts`);
}
