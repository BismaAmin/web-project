// Form Validation Utilities
const Validators = {
    // Email validation
    email: (email) => {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },
    
    // Password strength
    password: (password) => {
        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[$@#&!]+/)) strength++;
        return { strength, isStrong: strength >= 4 };
    },
    
    // Username validation
    username: (username) => {
        const regex = /^[a-zA-Z0-9_]{3,20}$/;
        return regex.test(username);
    },
    
    // Required field
    required: (value) => {
        return value !== null && value !== undefined && value.trim() !== '';
    },
    
    // Min length
    minLength: (value, length) => {
        return value.length >= length;
    },
    
    // Max length
    maxLength: (value, length) => {
        return value.length <= length;
    }
};

// Form validation helper
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        const rule = rules[input.name];
        if (rule) {
            const value = input.value;
            let fieldValid = true;
            
            if (rule.required && !Validators.required(value)) {
                showFieldError(input, 'This field is required');
                fieldValid = false;
            } else if (rule.email && !Validators.email(value)) {
                showFieldError(input, 'Invalid email address');
                fieldValid = false;
            } else if (rule.minLength && value.length < rule.minLength) {
                showFieldError(input, `Minimum ${rule.minLength} characters`);
                fieldValid = false;
            } else {
                clearFieldError(input);
            }
            
            if (!fieldValid) isValid = false;
        }
    });
    
    return isValid;
}

function showFieldError(input, message) {
    input.classList.add('error');
    let errorDiv = input.parentElement.querySelector('.error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        input.parentElement.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

function clearFieldError(input) {
    input.classList.remove('error');
    const errorDiv = input.parentElement.querySelector('.error-message');
    if (errorDiv) errorDiv.remove();
}