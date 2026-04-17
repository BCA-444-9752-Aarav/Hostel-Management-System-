/**
 * Enhanced Responsive JavaScript for Aditya Boys Hostel
 * Handles mobile navigation, theme switching, and responsive interactions
 */

// Mobile Navigation Handler
class MobileNavigation {
    constructor() {
        this.sidebar = null;
        this.mainContent = null;
        this.mobileToggle = null;
        this.overlay = null;
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupElements());
        } else {
            this.setupElements();
        }
    }
    
    setupElements() {
        this.sidebar = document.querySelector('.sidebar');
        this.mainContent = document.querySelector('.main-content');
        this.mobileToggle = document.querySelector('.mobile-menu-toggle');
        
        // Create overlay if it doesn't exist
        if (!document.querySelector('.sidebar-overlay')) {
            this.createOverlay();
        } else {
            this.overlay = document.querySelector('.sidebar-overlay');
        }
        
        this.bindEvents();
        this.handleResize();
    }
    
    createOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'sidebar-overlay';
        document.body.appendChild(this.overlay);
    }
    
    bindEvents() {
        // Mobile toggle click
        if (this.mobileToggle) {
            this.mobileToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleSidebar();
            });
        }
        
        // Overlay click
        if (this.overlay) {
            this.overlay.addEventListener('click', () => {
                this.closeSidebar();
            });
        }
        
        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeSidebar();
            }
        });
        
        // Window resize
        window.addEventListener('resize', () => this.handleResize());
        
        // Handle sidebar links (close on mobile after click)
        if (this.sidebar) {
            const sidebarLinks = this.sidebar.querySelectorAll('.sidebar-menu-item');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        setTimeout(() => this.closeSidebar(), 300);
                    }
                });
            });
        }
    }
    
    toggleSidebar() {
        if (this.isOpen) {
            this.closeSidebar();
        } else {
            this.openSidebar();
        }
    }
    
    openSidebar() {
        if (this.sidebar) {
            this.sidebar.classList.add('show');
        }
        if (this.overlay) {
            this.overlay.classList.add('show');
        }
        if (this.mobileToggle) {
            this.mobileToggle.classList.add('active');
        }
        
        // Prevent body scroll on mobile
        if (window.innerWidth <= 768) {
            document.body.style.overflow = 'hidden';
        }
        
        this.isOpen = true;
    }
    
    closeSidebar() {
        if (this.sidebar) {
            this.sidebar.classList.remove('show');
        }
        if (this.overlay) {
            this.overlay.classList.remove('show');
        }
        if (this.mobileToggle) {
            this.mobileToggle.classList.remove('active');
        }
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        this.isOpen = false;
    }
    
    handleResize() {
        // Close sidebar on desktop resize
        if (window.innerWidth > 768 && this.isOpen) {
            this.closeSidebar();
        }
        
        // Adjust sidebar behavior based on screen size
        if (this.sidebar && this.mainContent) {
            if (window.innerWidth <= 768) {
                // Mobile behavior
                this.sidebar.style.transform = 'translateX(-100%)';
                this.mainContent.style.marginLeft = '0';
            } else {
                // Desktop behavior
                this.sidebar.style.transform = '';
                this.mainContent.style.marginLeft = '';
            }
        }
    }
}

// Enhanced Theme Toggle Handler
class ThemeToggle {
    constructor() {
        this.themeToggle = null;
        this.currentTheme = localStorage.getItem('theme') || 'light';
        
        this.init();
    }
    
    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupElements());
        } else {
            this.setupElements();
        }
    }
    
    setupElements() {
        this.themeToggle = document.querySelector('.theme-toggle');
        
        // Apply saved theme
        this.applyTheme(this.currentTheme);
        
        // Bind toggle event
        if (this.themeToggle) {
            this.themeToggle.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleTheme();
            });
        }
        
        // Handle system theme changes
        if (window.matchMedia) {
            const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
            darkModeQuery.addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    this.applyTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }
    
    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
        localStorage.setItem('theme', newTheme);
        this.currentTheme = newTheme;
    }
    
    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        // Update toggle switch if it exists
        const toggleInput = document.querySelector('.theme-switch input');
        if (toggleInput) {
            toggleInput.checked = theme === 'dark';
        }
    }
}

// Responsive Form Handler
class ResponsiveForms {
    constructor() {
        this.init();
    }
    
    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupForms());
        } else {
            this.setupForms();
        }
    }
    
    setupForms() {
        // Handle form field responsiveness
        const formRows = document.querySelectorAll('.form-row');
        formRows.forEach(row => {
            this.handleFormRow(row);
        });
        
        // Handle input focus on mobile
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            this.handleInputFocus(input);
        });
        
        // Handle file inputs
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            this.handleFileInput(input);
        });
    }
    
    handleFormRow(row) {
        const formGroups = row.querySelectorAll('.form-group');
        
        // Adjust layout based on screen size
        const updateLayout = () => {
            if (window.innerWidth <= 768) {
                row.style.flexDirection = 'column';
                formGroups.forEach(group => {
                    group.style.minWidth = '100%';
                    group.style.marginBottom = '0.75rem';
                });
            } else {
                row.style.flexDirection = '';
                formGroups.forEach(group => {
                    group.style.minWidth = '';
                    group.style.marginBottom = '';
                });
            }
        };
        
        updateLayout();
        window.addEventListener('resize', updateLayout);
    }
    
    handleInputFocus(input) {
        input.addEventListener('focus', () => {
            // On mobile, scroll input into view
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        });
    }
    
    handleFileInput(input) {
        input.addEventListener('change', (e) => {
            const files = e.target.files;
            if (files.length > 0) {
                const fileName = Array.from(files).map(f => f.name).join(', ');
                
                // Update file display if exists
                const fileDisplay = input.parentElement.querySelector('.file-name-display');
                if (fileDisplay) {
                    fileDisplay.textContent = fileName;
                }
                
                // Show file size warning on mobile for large files
                if (window.innerWidth <= 768) {
                    const totalSize = Array.from(files).reduce((sum, file) => sum + file.size, 0);
                    if (totalSize > 5 * 1024 * 1024) { // 5MB
                        this.showFileSizeWarning(totalSize);
                    }
                }
            }
        });
    }
    
    showFileSizeWarning(size) {
        const sizeMB = (size / (1024 * 1024)).toFixed(2);
        const warning = document.createElement('div');
        warning.className = 'alert alert-warning alert-dismissible fade show';
        warning.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            Large file detected (${sizeMB}MB). Upload may take longer on mobile.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.content-area, .container');
        if (container) {
            container.insertBefore(warning, container.firstChild);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (warning.parentNode) {
                    warning.remove();
                }
            }, 5000);
        }
    }
}

// Responsive Table Handler
class ResponsiveTables {
    constructor() {
        this.init();
    }
    
    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupTables());
        } else {
            this.setupTables();
        }
    }
    
    setupTables() {
        const tableContainers = document.querySelectorAll('.table-responsive');
        tableContainers.forEach(container => {
            this.enhanceTable(container);
        });
    }
    
    enhanceTable(container) {
        const table = container.querySelector('table');
        if (!table) return;
        
        // Add horizontal scroll indicator on mobile
        const addScrollIndicator = () => {
            if (window.innerWidth <= 768 && table.scrollWidth > container.clientWidth) {
                container.classList.add('has-horizontal-scroll');
                
                // Add scroll hint if not already present
                if (!container.querySelector('.scroll-hint')) {
                    const hint = document.createElement('div');
                    hint.className = 'scroll-hint';
                    hint.innerHTML = '<i class="fas fa-arrow-right me-2"></i>Swipe to see more';
                    container.appendChild(hint);
                    
                    // Hide hint after user scrolls
                    let scrollTimer;
                    container.addEventListener('scroll', () => {
                        hint.style.display = 'none';
                        clearTimeout(scrollTimer);
                        scrollTimer = setTimeout(() => {
                            hint.style.display = '';
                        }, 3000);
                    });
                }
            } else {
                container.classList.remove('has-horizontal-scroll');
                const hint = container.querySelector('.scroll-hint');
                if (hint) hint.remove();
            }
        };
        
        addScrollIndicator();
        window.addEventListener('resize', addScrollIndicator);
        
        // Add table sorting indicators for mobile
        if (window.innerWidth <= 768) {
            this.addMobileSorting(table);
        }
    }
    
    addMobileSorting(table) {
        const headers = table.querySelectorAll('thead th');
        headers.forEach((header, index) => {
            if (header.textContent.trim()) {
                header.style.position = 'relative';
                header.style.cursor = 'pointer';
                
                // Add sort indicator
                if (!header.querySelector('.sort-indicator')) {
                    const indicator = document.createElement('span');
                    indicator.className = 'sort-indicator';
                    indicator.innerHTML = ' <i class="fas fa-sort text-muted"></i>';
                    header.appendChild(indicator);
                }
            }
        });
    }
}

// Performance Monitor for Mobile
class PerformanceMonitor {
    constructor() {
        this.isMobile = window.innerWidth <= 768;
        this.init();
    }
    
    init() {
        if (this.isMobile) {
            this.optimizeForMobile();
        }
        
        window.addEventListener('resize', () => {
            const wasMobile = this.isMobile;
            this.isMobile = window.innerWidth <= 768;
            
            if (wasMobile !== this.isMobile) {
                if (this.isMobile) {
                    this.optimizeForMobile();
                } else {
                    this.restoreForDesktop();
                }
            }
        });
    }
    
    optimizeForMobile() {
        // Disable heavy animations
        const animatedElements = document.querySelectorAll('.animate__animated, .floating-shape, .particle');
        animatedElements.forEach(el => {
            el.style.display = 'none';
        });
        
        // Reduce shadow complexity
        const cards = document.querySelectorAll('.card, .dashboard-card');
        cards.forEach(card => {
            card.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        });
        
        // Optimize images
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (!img.hasAttribute('loading')) {
                img.setAttribute('loading', 'lazy');
            }
        });
    }
    
    restoreForDesktop() {
        // Restore animations
        const animatedElements = document.querySelectorAll('.animate__animated, .floating-shape, .particle');
        animatedElements.forEach(el => {
            el.style.display = '';
        });
        
        // Restore shadows
        const cards = document.querySelectorAll('.card, .dashboard-card');
        cards.forEach(card => {
            card.style.boxShadow = '';
        });
    }
}

// Touch Gesture Handler
class TouchGestures {
    constructor() {
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.init();
    }
    
    init() {
        if ('ontouchstart' in window) {
            this.setupTouchListeners();
        }
    }
    
    setupTouchListeners() {
        document.addEventListener('touchstart', (e) => {
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
        }, { passive: true });
        
        document.addEventListener('touchend', (e) => {
            this.handleTouchEnd(e);
        }, { passive: true });
    }
    
    handleTouchEnd(e) {
        const touchEndX = e.changedTouches[0].clientX;
        const touchEndY = e.changedTouches[0].clientY;
        
        const deltaX = touchEndX - this.touchStartX;
        const deltaY = touchEndY - this.touchStartY;
        
        // Detect horizontal swipe for sidebar
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
            if (deltaX > 0) {
                // Swipe right - open sidebar
                this.openSidebarWithGesture();
            } else {
                // Swipe left - close sidebar
                this.closeSidebarWithGesture();
            }
        }
    }
    
    openSidebarWithGesture() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar && !sidebar.classList.contains('show')) {
            const mobileNav = window.mobileNavigation;
            if (mobileNav) {
                mobileNav.openSidebar();
            }
        }
    }
    
    closeSidebarWithGesture() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar && sidebar.classList.contains('show')) {
            const mobileNav = window.mobileNavigation;
            if (mobileNav) {
                mobileNav.closeSidebar();
            }
        }
    }
}

// Initialize all responsive components
document.addEventListener('DOMContentLoaded', () => {
    // Initialize mobile navigation
    window.mobileNavigation = new MobileNavigation();
    
    // Initialize theme toggle
    window.themeToggle = new ThemeToggle();
    
    // Initialize responsive forms
    window.responsiveForms = new ResponsiveForms();
    
    // Initialize responsive tables
    window.responsiveTables = new ResponsiveTables();
    
    // Initialize performance monitor
    window.performanceMonitor = new PerformanceMonitor();
    
    // Initialize touch gestures
    window.touchGestures = new TouchGestures();
    
    // Add responsive utility functions to window
    window.isMobile = () => window.innerWidth <= 768;
    window.isTablet = () => window.innerWidth >= 577 && window.innerWidth <= 991;
    window.isDesktop = () => window.innerWidth >= 992;
    
    console.log('Enhanced responsive system initialized');
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        MobileNavigation,
        ThemeToggle,
        ResponsiveForms,
        ResponsiveTables,
        PerformanceMonitor,
        TouchGestures
    };
}
