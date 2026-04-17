</div>
        <!-- Content Area -->
    </div>
    <!-- Main Content -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/responsive.js"></script>
    
    <!-- Student-specific responsive enhancements -->
    <script>
        // Initialize student-specific responsive features
        document.addEventListener('DOMContentLoaded', function() {
            // Handle mobile menu toggle
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const studentSidebar = document.getElementById('studentSidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (mobileMenuToggle && studentSidebar) {
                mobileMenuToggle.addEventListener('click', function() {
                    studentSidebar.classList.toggle('show');
                    if (mainContent) {
                        mainContent.classList.toggle('expanded');
                    }
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!studentSidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                        studentSidebar.classList.remove('show');
                        if (mainContent) {
                            mainContent.classList.remove('expanded');
                        }
                    }
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    studentSidebar.classList.remove('show');
                    if (mainContent) {
                        mainContent.classList.remove('expanded');
                    }
                }
            });
            
            // Handle responsive dashboard cards
            const dashboardStats = document.querySelector('.dashboard-stats');
            if (dashboardStats) {
                const updateCardLayout = function() {
                    const width = window.innerWidth;
                    if (width <= 576) {
                        dashboardStats.style.gridTemplateColumns = '1fr';
                    } else if (width <= 768) {
                        dashboardStats.style.gridTemplateColumns = 'repeat(2, 1fr)';
                    } else if (width <= 992) {
                        dashboardStats.style.gridTemplateColumns = 'repeat(3, 1fr)';
                    } else {
                        dashboardStats.style.gridTemplateColumns = 'repeat(auto-fit, minmax(250px, 1fr))';
                    }
                };
                
                updateCardLayout();
                window.addEventListener('resize', updateCardLayout);
            }
            
            // Handle responsive tables
            const tables = document.querySelectorAll('.modern-table table');
            tables.forEach(function(table) {
                if (!table.closest('.table-responsive')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'table-responsive';
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                }
            });
            
            // Handle form responsiveness on mobile
            if (window.innerWidth <= 768) {
                const forms = document.querySelectorAll('form');
                forms.forEach(function(form) {
                    const formRows = form.querySelectorAll('.form-row');
                    formRows.forEach(function(row) {
                        row.style.flexDirection = 'column';
                        const formGroups = row.querySelectorAll('.form-group');
                        formGroups.forEach(function(group) {
                            group.style.width = '100%';
                            group.style.marginBottom = '0.75rem';
                        });
                    });
                });
            }
            
            // Handle modal responsiveness
            const modals = document.querySelectorAll('.modal');
            modals.forEach(function(modal) {
                modal.addEventListener('show.bs.modal', function() {
                    if (window.innerWidth <= 576) {
                        const dialog = modal.querySelector('.modal-dialog');
                        if (dialog) {
                            dialog.style.margin = '0.5rem';
                            dialog.style.maxWidth = 'calc(100vw - 1rem)';
                        }
                    }
                });
            });
            
            // Add touch-friendly interactions
            if ('ontouchstart' in window) {
                const buttons = document.querySelectorAll('.btn');
                buttons.forEach(function(button) {
                    button.addEventListener('touchstart', function() {
                        this.style.transform = 'scale(0.95)';
                    });
                    
                    button.addEventListener('touchend', function() {
                        this.style.transform = '';
                    });
                });
            }
            
            // Handle notification badge updates
            const notificationBadge = document.querySelector('.notification-badge');
            if (notificationBadge) {
                // Simulate notification count update
                setInterval(function() {
                    const count = Math.floor(Math.random() * 10);
                    if (count > 0) {
                        notificationBadge.textContent = count;
                        notificationBadge.style.display = 'inline-block';
                    } else {
                        notificationBadge.style.display = 'none';
                    }
                }, 30000); // Check every 30 seconds
            }
            
            console.log('Student responsive features initialized');
        });
        
        // Utility functions
        window.showAlert = function(message, type) {
            type = type || 'info';
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-' + type;
            alertDiv.textContent = message;
            
            const contentArea = document.querySelector('.content-area');
            if (contentArea) {
                contentArea.insertBefore(alertDiv, contentArea.firstChild);
                
                setTimeout(function() {
                    alertDiv.style.opacity = '0';
                    setTimeout(function() {
                        if (alertDiv.parentNode) {
                            alertDiv.parentNode.removeChild(alertDiv);
                        }
                    }, 500);
                }, 5000);
            }
        };
        
        window.confirmAction = function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        };
        
        // Format currency
        window.formatCurrency = function(amount) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                minimumFractionDigits: 2
            }).format(amount);
        };
        
        // Format date
        window.formatDate = function(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-IN', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        };
        
        // Handle orientation changes
        window.addEventListener('orientationchange', function() {
            setTimeout(function() {
                // Adjust layout after orientation change
                const event = new CustomEvent('orientation-update');
                document.dispatchEvent(event);
            }, 100);
        });
        
        // Handle window resize with debouncing
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                const event = new CustomEvent('responsive-update');
                document.dispatchEvent(event);
            }, 250);
        });
    </script>
</body>
</html>
