// Dark Mode Toggle functionality
class DarkModeManager {
    constructor() {
        this.isDark = localStorage.getItem('darkMode') === 'true';
        this.init();
    }
    
    init() {
        // Create toggle button if not exists
        this.createToggleButton();
        
        // Apply saved theme
        if(this.isDark) {
            this.enableDarkMode();
        }
        
        // Listen for system preference changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if(!localStorage.getItem('darkMode')) {
                if(e.matches) {
                    this.enableDarkMode();
                } else {
                    this.disableDarkMode();
                }
            }
        });
    }
    
    createToggleButton() {
        const existingBtn = document.querySelector('.theme-toggle');
        if(existingBtn) return;
        
        const btn = document.createElement('button');
        btn.className = 'theme-toggle';
        btn.innerHTML = this.isDark ? '☀️' : '🌙';
        btn.setAttribute('aria-label', 'Toggle dark mode');
        btn.style.cssText = `
            background: transparent;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        `;
        
        btn.onclick = () => this.toggle();
        
        // Insert into navbar
        const navMenu = document.querySelector('.nav-menu');
        if(navMenu) {
            const li = document.createElement('li');
            li.className = 'nav-item';
            li.appendChild(btn);
            navMenu.appendChild(li);
        }
    }
    
    enableDarkMode() {
        document.body.classList.add('dark-mode');
        this.isDark = true;
        localStorage.setItem('darkMode', 'true');
        this.updateButtonIcon();
        this.applyDarkStyles();
    }
    
    disableDarkMode() {
        document.body.classList.remove('dark-mode');
        this.isDark = false;
        localStorage.setItem('darkMode', 'false');
        this.updateButtonIcon();
        this.removeDarkStyles();
    }
    
    toggle() {
        if(this.isDark) {
            this.disableDarkMode();
            window.showNotification('Light mode activated', 'info');
        } else {
            this.enableDarkMode();
            window.showNotification('Dark mode activated', 'info');
        }
    }
    
    updateButtonIcon() {
        const btn = document.querySelector('.theme-toggle');
        if(btn) {
            btn.innerHTML = this.isDark ? '☀️' : '🌙';
        }
    }
    
    applyDarkStyles() {
        const darkStyles = document.createElement('style');
        darkStyles.id = 'dark-mode-styles';
        darkStyles.textContent = `
            body.dark-mode {
                background: #1a1a2e !important;
                color: #eee !important;
            }
            body.dark-mode .navbar,
            body.dark-mode .card,
            body.dark-mode .auth-card,
            body.dark-mode .ingredient-card,
            body.dark-mode .recipe-card,
            body.dark-mode .challenge-card,
            body.dark-mode .chef-card,
            body.dark-mode .step-card,
            body.dark-mode .stat-card,
            body.dark-mode .flip-card-front {
                background: #16213e !important;
                color: #eee !important;
            }
            body.dark-mode .footer {
                background: #0f0f1a !important;
            }
            body.dark-mode .form-group input,
            body.dark-mode .filter-select,
            body.dark-mode .search-bar input {
                background: #0f0f1a;
                color: #eee;
                border-color: #333;
            }
            body.dark-mode .text-muted {
                color: #aaa !important;
            }
            body.dark-mode .ingredient-card.selected {
                background: linear-gradient(135deg, #1a3a2a, #16213e) !important;
            }
        `;
        
        if(!document.getElementById('dark-mode-styles')) {
            document.head.appendChild(darkStyles);
        }
    }
    
    removeDarkStyles() {
        const styles = document.getElementById('dark-mode-styles');
        if(styles) styles.remove();
    }
}

// Initialize dark mode when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.darkMode = new DarkModeManager();
});