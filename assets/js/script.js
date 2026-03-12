// Server-side authentication check
async function checkAuth() {
    try {
        const response = await fetch('../php/check_auth.php');
        const data = await response.json();
        
        if (!data.authenticated) {
            window.location.href = data.redirect || 'login.html';
            return false;
        }
        return true;
    } catch (error) {
        console.error('Auth check error:', error);
        window.location.href = 'login.html';
        return false;
    }
}

// No client storage needed - server session handles

// Server-side logout
async function logout() {
    try {
        await fetch('../php/logout.php');
    } catch (error) {
        console.error('Logout error:', error);
    }
    window.location.href = 'login.html';
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
