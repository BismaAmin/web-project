// Toast Notification System
class NotificationManager {
    constructor() {
        this.container = null;
        this.createContainer();
    }
    
    createContainer() {
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(this.container);
    }
    
    show(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        
        const colors = {
            success: '#06D6A0',
            error: '#EF476F',
            warning: '#FFE66D',
            info: '#4ECDC4'
        };
        
        const icons = {
            success: '✓',
            error: '✗',
            warning: '⚠',
            info: 'ℹ'
        };
        
        notification.style.cssText = `
            background: ${colors[type] || colors.info};
            color: ${type === 'warning' ? '#2B2D42' : 'white'};
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Century Gothic', sans-serif;
            font-weight: 500;
            animation: slideIn 0.3s ease;
            cursor: pointer;
        `;
        
        notification.innerHTML = `
            <span style="font-size: 1.2rem;">${icons[type]}</span>
            <span>${message}</span>
        `;
        
        notification.onclick = () => this.hide(notification);
        
        this.container.appendChild(notification);
        
        setTimeout(() => this.hide(notification), duration);
    }
    
    hide(notification) {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }
    
    success(message, duration) {
        this.show(message, 'success', duration);
    }
    
    error(message, duration) {
        this.show(message, 'error', duration);
    }
    
    warning(message, duration) {
        this.show(message, 'warning', duration);
    }
    
    info(message, duration) {
        this.show(message, 'info', duration);
    }
}

// Add animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Global instance
window.notifications = new NotificationManager();

// Global helper function
window.showNotification = (message, type = 'info') => {
    window.notifications.show(message, type);
};