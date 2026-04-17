// Aditya Boys Hostel - JavaScript

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Add ripple effect to buttons
    addRippleEffect();
    
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
    
    // Sidebar toggle for mobile
    initializeSidebar();
    
    // Form validations
    initializeFormValidations();
    
    // Auto-hide alerts
    autoHideAlerts();
});

// Ripple Effect
function addRippleEffect() {
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.classList.add('ripple');
    });
}

// Initialize Charts
function initializeCharts() {
    // Room Occupancy Chart
    const roomOccupancyCtx = document.getElementById('roomOccupancyChart');
    if (roomOccupancyCtx) {
        new Chart(roomOccupancyCtx, {
            type: 'doughnut',
            data: {
                labels: ['Occupied', 'Available'],
                datasets: [{
                    data: [65, 35],
                    backgroundColor: [
                        'rgba(26, 115, 232, 0.8)',
                        'rgba(0, 191, 166, 0.8)'
                    ],
                    borderColor: [
                        'rgba(26, 115, 232, 1)',
                        'rgba(0, 191, 166, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // Fee Collection Chart
    const feeCollectionCtx = document.getElementById('feeCollectionChart');
    if (feeCollectionCtx) {
        new Chart(feeCollectionCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Fees Collected',
                    data: [45000, 52000, 48000, 55000, 58000, 61000],
                    backgroundColor: 'rgba(26, 115, 232, 0.8)',
                    borderColor: 'rgba(26, 115, 232, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Complaint Status Chart
    const complaintStatusCtx = document.getElementById('complaintStatusChart');
    if (complaintStatusCtx) {
        new Chart(complaintStatusCtx, {
            type: 'pie',
            data: {
                labels: ['Pending', 'In Progress', 'Resolved'],
                datasets: [{
                    data: [8, 5, 12],
                    backgroundColor: [
                        'rgba(255, 110, 199, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(0, 191, 166, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 110, 199, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(0, 191, 166, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
}

// Sidebar Toggle
function initializeSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle sidebar visibility
            sidebar.classList.toggle('show');
            
            // Add active class to toggle button
            sidebarToggle.classList.toggle('active');
            
            // Add overlay to close sidebar when clicking outside
            toggleOverlay();
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('show') && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
                sidebarToggle.classList.remove('active');
                removeOverlay();
            }
        });
        
        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                sidebarToggle.classList.remove('active');
                removeOverlay();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                sidebarToggle.classList.remove('active');
                removeOverlay();
            }
        });
    }
}

// Toggle overlay for mobile sidebar
function toggleOverlay() {
    let overlay = document.querySelector('.sidebar-overlay');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebar.classList.contains('show')) {
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                opacity: 0;
                transition: opacity 0.3s ease;
            `;
            document.body.appendChild(overlay);
            
            // Add click event to overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                document.getElementById('sidebarToggle')?.classList.remove('active');
                removeOverlay();
            });
        }
        
        // Fade in overlay
        setTimeout(() => {
            overlay.style.opacity = '1';
        }, 10);
    } else {
        removeOverlay();
    }
}

// Remove overlay
function removeOverlay() {
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.remove();
        }, 300);
    }
}

// Form Validations
function initializeFormValidations() {
    // Password confirmation
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        field.addEventListener('input', function() {
            const form = field.closest('form');
            if (form) {
                const password = form.querySelector('#password');
                const confirmPassword = form.querySelector('#confirm_password');
                
                if (password && confirmPassword) {
                    if (password.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match');
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                }
            }
        });
    });

    // Email validation
    const emailFields = document.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        field.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.value)) {
                this.setCustomValidity('Please enter a valid email address');
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // Phone number validation
    const phoneFields = document.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(field => {
        field.addEventListener('input', function() {
            const phoneRegex = /^[0-9]{10}$/;
            const value = this.value.replace(/\D/g, '');
            if (value.length !== 10) {
                this.setCustomValidity('Please enter a valid 10-digit phone number');
            } else {
                this.setCustomValidity('');
            }
        });
    });
}

// Auto-hide Alerts
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (!alert.classList.contains('alert-danger')) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        }
    });
}

// AJAX Functions
function ajaxRequest(url, method, data = null, callback = null, errorCallback = null) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            if (callback) {
                callback(xhr.responseText);
            }
        } else {
            console.error('AJAX Error:', xhr.status, xhr.statusText);
            if (errorCallback) {
                errorCallback(xhr.status + ': ' + xhr.statusText);
            }
        }
    };
    
    xhr.onerror = function() {
        console.error('AJAX Error: Network error');
        if (errorCallback) {
            errorCallback('Network error');
        }
    };
    
    if (data) {
        xhr.send(data);
    } else {
        xhr.send();
    }
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transition = 'opacity 0.5s';
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 5000);
}

// Loading Spinner
function showLoading(element) {
    element.disabled = true;
    element.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
}

function hideLoading(element, originalText) {
    element.disabled = false;
    element.innerHTML = originalText;
}

// Confirm Dialog
function confirmDialog(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// File Upload Preview
function previewImage(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewElement.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Print Function
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                    </style>
                </head>
                <body>
                    ${element.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
}

// Export to CSV
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => {
            return '"' + col.textContent.trim() + '"';
        });
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    
    window.URL.revokeObjectURL(url);
}

// Search/Filter Table
function filterTable(tableId, searchInput) {
    const table = document.getElementById(tableId);
    const input = document.getElementById(searchInput);
    
    if (!table || !input) return;
    
    input.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
}

// Number Formatting
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Smooth Scroll
function smoothScroll(target) {
    document.querySelector(target).scrollIntoView({
        behavior: 'smooth'
    });
}

// Tab Navigation
function initializeTabs() {
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function(e) {
            // Custom logic when tab is shown
        });
    });
}

// Modal Functions
function showModal(modalId) {
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
}

function hideModal(modalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
}
