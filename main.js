// Main JavaScript - Sizzle & Share
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modules
    initMobileMenu();
    initNewsletterForm();
    initCountdowns();
    initScrollAnimations();
});

// Mobile Menu Toggle
function initMobileMenu() {
    const menuBtn = document.getElementById('menuBtn');
    const navMenu = document.getElementById('navMenu');
    
    if (menuBtn && navMenu) {
        menuBtn.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }
}

// Newsletter Subscription
function initNewsletterForm() {
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = newsletterForm.querySelector('input').value;
            if (email) {
                showNotification('Subscribed successfully! Check your inbox.', 'success');
                newsletterForm.reset();
            }
        });
    }
}

// Countdown Timers
function initCountdowns() {
    document.querySelectorAll('.countdown').forEach(el => {
        let days = parseInt(el.dataset.days);
        if (days && days > 0) {
            el.textContent = `${days} days left`;
        }
    });
}

// Scroll Animations
function initScrollAnimations() {
    const elements = document.querySelectorAll('[data-aos]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('aos-animate');
            }
        });
    });
    
    elements.forEach(el => observer.observe(el));
}

// Show Notification (global)
window.showNotification = function(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        background: ${type === 'success' ? '#2ECC71' : type === 'error' ? '#E74C3C' : '#9B59B6'};
        color: white;
        z-index: 10000;
        animation: slideLeft 0.3s ease;
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
};