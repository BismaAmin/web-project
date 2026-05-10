// Authentication Module
class AuthManager {
    constructor() {
        this.isLoggedIn = false;
        this.user = null;
        this.checkSession();
    }
    
    async checkSession() {
        try {
            const response = await fetch('php/auth/session-check.php');
            const data = await response.json();
            this.isLoggedIn = data.logged_in;
            if (this.isLoggedIn) {
                this.user = data;
                this.updateUI();
            }
        } catch (error) {
            console.error('Session check failed:', error);
        }
    }
    
    updateUI() {
        const loginLinks = document.querySelectorAll('.nav-link[href="login.html"]');
        const signupLinks = document.querySelectorAll('.nav-link[href="signup.html"]');
        const dashboardLinks = document.querySelectorAll('.nav-link[href="dashboard.html"]');
        
        if (this.isLoggedIn) {
            loginLinks.forEach(link => {
                const parent = link.parentElement;
                if (parent) parent.style.display = 'none';
            });
            signupLinks.forEach(link => {
                const parent = link.parentElement;
                if (parent) parent.style.display = 'none';
            });
            dashboardLinks.forEach(link => {
                const parent = link.parentElement;
                if (parent) parent.style.display = 'block';
            });
        }
    }
    
    async login(email, password) {
        const formData = new FormData();
        formData.append('login_input', email);
        formData.append('password', password);
        
        try {
            const response = await fetch('php/auth/login-process.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                this.isLoggedIn = true;
                showNotification('Login successful!', 'success');
                setTimeout(() => window.location.href = 'dashboard.html', 1000);
            } else {
                showNotification(data.message || 'Login failed', 'error');
            }
        } catch (error) {
            showNotification('Network error', 'error');
        }
    }
    
    async signup(userData) {
        const formData = new FormData();
        Object.keys(userData).forEach(key => {
            formData.append(key, userData[key]);
        });
        
        try {
            const response = await fetch('php/auth/signup-process.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                showNotification('Account created! Redirecting to login...', 'success');
                setTimeout(() => window.location.href = 'login.html', 1500);
            } else {
                showNotification(data.message || 'Signup failed', 'error');
            }
        } catch (error) {
            showNotification('Network error', 'error');
        }
    }
    
    logout() {
        window.location.href = 'logout.php';
    }
}

// Initialize auth manager
window.auth = new AuthManager();