// Authentication check for all protected pages
async function checkAuthAndRedirect(page) {
    try {
        const response = await fetch('php/check-session.php');
        const data = await response.json();
        
        if (data.logged_in) {
            window.location.href = page;
        } else {
            window.location.href = 'login.html';
        }
    } catch (error) {
        window.location.href = 'login.html';
    }
}

// Update navbar based on login status
async function updateNavbar() {
    try {
        const response = await fetch('php/check-session.php');
        const data = await response.json();
        
        const loginItem = document.getElementById('loginNavItem');
        const signupItem = document.getElementById('signupNavItem');
        const logoutItem = document.getElementById('logoutNavItem');
        
        if (data.logged_in) {
            if (loginItem) loginItem.style.display = 'none';
            if (signupItem) signupItem.style.display = 'none';
            if (logoutItem) logoutItem.style.display = 'block';
        } else {
            if (loginItem) loginItem.style.display = 'block';
            if (signupItem) signupItem.style.display = 'block';
            if (logoutItem) logoutItem.style.display = 'none';
        }
    } catch (error) {
        console.log('Error checking login status');
    }
}

function logout() {
    window.location.href = 'logout.php';
}

// Run update on page load
document.addEventListener('DOMContentLoaded', updateNavbar);