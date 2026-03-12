// Check authentication on each page
function checkAuth() {
    const userId = localStorage.getItem('user_id');
    const username = localStorage.getItem('username');
    const fullName = localStorage.getItem('full_name');
    
    if (!userId) {
        window.location.href = 'login.html';
        return false;
    }
    return true;
}

// Store user data after login
function setUserData(user) {
    localStorage.setItem('user_id', user.id);
    localStorage.setItem('username', user.username);
    localStorage.setItem('full_name', user.full_name);
}

// Clear user data on logout
function clearUserData() {
    localStorage.removeItem('user_id');
    localStorage.removeItem('username');
    localStorage.removeItem('full_name');
}

// Format money (for JavaScript use)
function formatMoney(amount) {
    return 'KES ' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Show loading spinner
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    }
}

// Show error message
function showError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="alert alert-danger">' + message + '</div>';
    }
}

// Initialize DataTables - check if already initialized to prevent reinitialization error
$(document).ready(function() {
    $('.datatable').each(function() {
        if (!$.fn.DataTable.isDataTable(this)) {
            $(this).DataTable();
        }
    });
});

// Handle logout
function logout() {
    clearUserData();
    window.location.href = 'login.html';
}

// Global error handler
window.onerror = function(msg, url, lineNo, columnNo, error) {
    console.error('Error: ', msg, '\nURL: ', url, '\nLine: ', lineNo, '\nColumn: ', columnNo, '\nError object: ', error);
    return false;
};
