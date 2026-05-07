// Common JavaScript for all pages - FIXED VERSION

// Detect path prefix based on script location depth
const _pathPrefix = (function() {
    const scripts = document.querySelectorAll('script[src]');
    for (const s of scripts) {
        if (s.getAttribute('src') && s.getAttribute('src').includes('common.js')) {
            const src = s.getAttribute('src');
            return src.startsWith('../') ? '../' : '';
        }
    }
    return '';
})();

// ========== DARK MODE FUNCTIONS ==========
function initDarkMode() {
    const isDark = localStorage.getItem('darkMode') === 'true';
    if (isDark) {
        document.body.classList.add('dark-mode');
        const toggleBtn = document.getElementById('darkModeToggle');
        if (toggleBtn) toggleBtn.textContent = '☀️';
    }
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);

    const toggleBtn = document.getElementById('darkModeToggle');
    if (toggleBtn) {
        toggleBtn.textContent = isDark ? '☀️' : '🌙';
    }

    showNotification(isDark ? 'Dark mode activated 🌙' : 'Light mode activated ☀️', 'info');
}

// ========== SCROLL TO TOP ==========
function initScrollToTop() {
    const scrollBtn = document.getElementById('scrollTop');
    if (!scrollBtn) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            scrollBtn.style.display = 'flex';
        } else {
            scrollBtn.style.display = 'none';
        }
    });

    scrollBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

// ========== NOTIFICATION SYSTEM ==========
function showNotification(message, type = 'info') {
    // Remove any existing notification
    const existing = document.querySelector('.dynamic-notification');
    if (existing) existing.remove();
    
    const notification = document.createElement('div');
    notification.className = 'dynamic-notification';
    notification.textContent = message;

    const colors = {
        success: '#06D6A0',
        error: '#EF476F',
        warning: '#FFE66D',
        info: '#4ECDC4'
    };

    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        background: ${colors[type] || colors.info};
        color: ${type === 'warning' ? '#333' : 'white'};
        z-index: 10000;
        animation: slideIn 0.3s ease;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-family: 'Century Gothic', 'Trebuchet MS', 'Futura', 'Arial', sans-serif;
    `;

    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ========== LOGOUT FUNCTION - FIXED ==========
// This works with localStorage (frontend auth) since PHP backend may not be connected
function logout() {
    // Clear ALL localStorage data
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('username');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('selectedIngredients');
    localStorage.removeItem('challengeJoined');
    localStorage.removeItem('galleryLikedItems');
    localStorage.removeItem('userStats');
    localStorage.removeItem('userProfile');
    localStorage.removeItem('fromRecipes');
    
    // Clear sessionStorage
    sessionStorage.clear();
    
    // Try PHP logout as well (if available)
    try {
        fetch(_pathPrefix + 'php/logout.php', { 
            method: 'GET',
            cache: 'no-cache'
        }).catch(() => {});
    } catch(e) {
        // Ignore PHP errors - localStorage logout is enough
    }
    
    // Show notification
    showNotification('Logged out successfully! Redirecting...', 'success');
    
    // Redirect to home page
    setTimeout(() => {
        window.location.href = _pathPrefix + 'index.html';
    }, 800);
}

// ========== CHECK AUTHENTICATION - Uses localStorage (no PHP dependency) ==========
function isLoggedIn() {
    return localStorage.getItem('isLoggedIn') === 'true';
}

// ========== UPDATE NAVBAR based on localStorage ==========
function updateNavbar() {
    const loggedIn = isLoggedIn();

    if (loggedIn) {
        document.body.classList.add('logged-in');
    } else {
        document.body.classList.remove('logged-in');
    }

    // Refresh dropdown username if it exists
    const dropdown = document.getElementById('userDropdown');
    if (dropdown && loggedIn) {
        const username = localStorage.getItem('username') || 'My Account';
        const email    = localStorage.getItem('userEmail') || '';
        const nameEl   = dropdown.querySelector('#dropdownUsername');
        const headerName  = dropdown.querySelector('.dropdown-header-name');
        const headerEmail = dropdown.querySelector('.dropdown-header-email');
        const avatarEl    = dropdown.querySelector('.user-avatar-badge');
        if (nameEl)      nameEl.textContent      = username;
        if (headerName)  headerName.textContent  = username;
        if (headerEmail) headerEmail.textContent = email;
        if (avatarEl)    avatarEl.textContent    = username.charAt(0).toUpperCase();
    }
    
    // IMPORTANT: Re-initialize dropdown after navbar update
    // This ensures the dropdown works even if common.js loaded before navbar was ready
    if (loggedIn && dropdown) {
        // Small delay to ensure DOM is ready
        setTimeout(() => {
            initUserDropdown();
        }, 100);
    }
}


// ========== PROTECT PAGE - Redirect to login if not authenticated ==========
function protectPage() {
    if (!isLoggedIn()) {
        // Store the current page to redirect back after login        sessionStorage.setItem('redirectAfterLogin', window.location.pathname);
        showNotification('Please login to access this page', 'info');
        setTimeout(() => {
            window.location.href = _pathPrefix + 'login.html';
        }, 500);
        return false;
    }
    return true;
}

// ========== CHECK AUTH FOR PROTECTED PAGES ==========
async function checkAuthAndRedirect(page) {
    if (isLoggedIn()) {
        window.location.href = page;
    } else {
        window.location.href = _pathPrefix + 'login.html';
    }
}

// ========== MOBILE MENU TOGGLE ==========
function initMobileMenu() {
    const menuBtn = document.getElementById('menuBtn');
    const navMenu = document.getElementById('navMenu');

    if (menuBtn && navMenu) {
        menuBtn.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            const isActive = navMenu.classList.contains('active');
            menuBtn.setAttribute('aria-expanded', isActive);
        });

        // Close menu on outside click
        document.addEventListener('click', (e) => {
            if (menuBtn && navMenu && !menuBtn.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                menuBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }
}

// ========== NEWSLETTER SUBSCRIPTION ==========
function initNewsletter() {
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const input = newsletterForm.querySelector('input');
            if (input && input.value) {
                showNotification(`Subscribed with ${input.value}! Check your inbox.`, 'success');
                input.value = '';
            }
        });
    }
}

// ========== Add CSS animations if not present ==========
function addAnimationStyles() {
    if (!document.getElementById('common-animation-styles')) {
        const style = document.createElement('style');
        style.id = 'common-animation-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .scroll-top {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 45px;
                height: 45px;
                background: linear-gradient(135deg, #FF6B35, #7209B7);
                color: white;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                display: none;
                align-items: center;
                justify-content: center;
                font-size: 1.3rem;
                z-index: 999;
                transition: all 0.3s ease;
            }
            .scroll-top:hover {
                transform: translateY(-5px);
            }
            .scroll-top.show {
                display: flex;
            }
        `;
        document.head.appendChild(style);
    }
}

// ========== USER DROPDOWN ==========
function initUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    if (!dropdown) return;
    
    // Only initialize if user is logged in
    if (!isLoggedIn()) return;

    const toggle = dropdown.querySelector('.user-dropdown-toggle');
    if (!toggle) return;
    
    // Remove old event listeners by cloning
    const newToggle = toggle.cloneNode(true);
    toggle.parentNode.replaceChild(newToggle, toggle);

    // Toggle open/close
    newToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = dropdown.classList.toggle('open');
        newToggle.setAttribute('aria-expanded', isOpen);
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
            newToggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            dropdown.classList.remove('open');
            newToggle.setAttribute('aria-expanded', 'false');
        }
    });
}

// ========== INITIALIZE ALL ==========
document.addEventListener('DOMContentLoaded', () => {
    // Set body class IMMEDIATELY before anything renders
    if (isLoggedIn()) {
        document.body.classList.add('logged-in');
    }
    
    addAnimationStyles();
    initDarkMode();
    initScrollToTop();
    initMobileMenu();
    initNewsletter();
    updateNavbar();
    initUserDropdown();
    
    // Double-check dropdown initialization after a short delay
    // This handles cases where navbar might be dynamically loaded
    setTimeout(() => {
        if (isLoggedIn()) {
            updateNavbar();
            initUserDropdown();
        }
    }, 300);
});

// Make functions globally available
window.logout = logout;
window.toggleDarkMode = toggleDarkMode;
window.showNotification = showNotification;
window.isLoggedIn = isLoggedIn;
window.protectPage = protectPage;
window.checkAuthAndRedirect = checkAuthAndRedirect;
window.initUserDropdown = initUserDropdown;
window.updateNavbar = updateNavbar;