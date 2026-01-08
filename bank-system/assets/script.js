// Form validation and interactive features
document.addEventListener('DOMContentLoaded', function() {
    
    // Password confirmation validation
    const passwordForms = document.querySelectorAll('form');
    
    passwordForms.forEach(form => {
        if (form.querySelector('input[name="password"]') && 
            form.querySelector('input[name="confirm_password"]')) {
            
            form.addEventListener('submit', function(e) {
                const password = form.querySelector('input[name="password"]').value;
                const confirmPassword = form.querySelector('input[name="confirm_password"]').value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    showAlert('Passwords do not match!', 'error');
                    form.querySelector('input[name="confirm_password"]').focus();
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    showAlert('Password must be at least 6 characters long!', 'error');
                }
            });
        }
    });
    
    // Amount validation for credit/debit
    const amountForms = document.querySelectorAll('form[action*="credit"], form[action*="debit"]');
    
    amountForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const amountInput = form.querySelector('input[name="amount"]');
            if (amountInput) {
                const amount = parseFloat(amountInput.value);
                
                if (isNaN(amount) || amount <= 0) {
                    e.preventDefault();
                    showAlert('Please enter a valid amount greater than 0!', 'error');
                    amountInput.focus();
                }
                
                // For debit forms, check if amount is too large
                if (form.action.includes('debit')) {
                    const currentBalance = parseFloat(form.querySelector('input[name="current_balance"]')?.value || 0);
                    if (amount > currentBalance) {
                        e.preventDefault();
                        showAlert('Debit amount cannot exceed current balance!', 'error');
                        amountInput.focus();
                    }
                }
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Search field auto-focus
    const searchField = document.querySelector('input[name="account_number"][placeholder*="Search"]');
    if (searchField) {
        searchField.focus();
    }
    
    // Format currency inputs
    const currencyInputs = document.querySelectorAll('input[type="number"][name="amount"]');
    currencyInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
        
        input.addEventListener('input', function() {
            // Allow only numbers and one decimal point
            this.value = this.value.replace(/[^0-9.]/g, '');
            const parts = this.value.split('.');
            if (parts.length > 2) {
                this.value = parts[0] + '.' + parts.slice(1).join('');
            }
            if (parts[1] && parts[1].length > 2) {
                this.value = parts[0] + '.' + parts[1].substring(0, 2);
            }
        });
    });
});

// Show alert function
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        alertDiv.style.transition = 'opacity 0.5s ease';
        setTimeout(() => alertDiv.remove(), 500);
    }, 5000);
}

// Add CSS for animation
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
`;
document.head.appendChild(style);

// Confirm before logout
document.addEventListener('DOMContentLoaded', function() {
    const logoutLinks = document.querySelectorAll('a[href*="logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
    });
});

// Copy account number to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Account number copied to clipboard!', 'success');
    }).catch(err => {
        showAlert('Failed to copy: ' + err, 'error');
    });
}

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.querySelector(`[onclick="togglePassword('${inputId}')"]`);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = '👁️';
    } else {
        input.type = 'password';
        icon.textContent = '👁️‍🗨️';
    }
}