// Theme Management System
class ThemeManager {
    constructor() {
        this.currentTheme = this.getStoredTheme() || 'light';
        this.init();
    }

    init() {
        // Apply stored theme on page load
        this.applyTheme(this.currentTheme);
        
        // Create and add theme toggle button
        this.createThemeToggle();
        
        // Add keyboard shortcut (Ctrl/Cmd + Shift + T)
        this.addKeyboardShortcut();
        
        // Add system theme detection
        this.detectSystemTheme();
        
        // Debug: Log initialization
        // console.log('ThemeManager initialized with theme:', this.currentTheme);
    }

    getStoredTheme() {
        return localStorage.getItem('theme') || 'light';
    }

    setStoredTheme(theme) {
        localStorage.setItem('theme', theme);
    }

    applyTheme(theme) {
        // console.log('Applying theme:', theme);
        
        // Set the data-theme attribute
        document.documentElement.setAttribute('data-theme', theme);
        this.currentTheme = theme;
        this.setStoredTheme(theme);
        
        // Force CSS variable update
        this.forceStyleUpdate();
        
        // Update theme toggle
        this.updateThemeToggle();
        
        // Notify theme change
        this.notifyThemeChange(theme);
        
        // console.log('Theme applied successfully:', theme);
    }
    
    forceStyleUpdate() {
        // Force a style recalculation
        const element = document.documentElement;
        const display = element.style.display;
        element.style.display = 'none';
        element.offsetHeight; // Trigger reflow
        element.style.display = display;
        
        // Update all theme-dependent elements
        const themeElements = document.querySelectorAll('.theme-toggle, .dashboard-card, .table-container, .login-card');
        themeElements.forEach(el => {
            el.style.transition = 'all 0.3s ease';
        });
    }

    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
    }

    createThemeToggle() {
        // Debug: Check if button already exists
        if (document.querySelector('.theme-toggle')) {
            console.log('Theme toggle button already exists');
            return;
        }

        console.log('Creating professional theme toggle button...');

        // Create professional button
        const button = document.createElement('div');
        button.className = 'theme-toggle';
        button.setAttribute('aria-label', 'Toggle theme');
        button.setAttribute('title', 'Toggle between light and dark mode');
        
        button.innerHTML = `
            <label class="theme-switch">
                <input type="checkbox" ${this.currentTheme === 'dark' ? 'checked' : ''}>
                <span class="theme-slider">
                    <div class="theme-icons">
                        <i class="fas fa-moon moon-icon"></i>
                        <i class="fas fa-sun sun-icon"></i>
                    </div>
                </span>
            </label>
        `;

        // Add event listener to the checkbox
        const checkbox = button.querySelector('input[type="checkbox"]');
        
        checkbox.addEventListener('change', (e) => {
            const newTheme = e.target.checked ? 'dark' : 'light';
            this.applyTheme(newTheme);
        });

        // Add hover effect
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translateY(0)';
        });
        
        // Add active effect
        button.addEventListener('mousedown', () => {
            button.style.transform = 'translateY(0)';
        });
        
        button.addEventListener('mouseup', () => {
            button.style.transform = 'translateY(-2px)';
        });
        
        // Add to page
        document.body.appendChild(button);
        console.log('Professional theme toggle button added to page');
        
        // Verify it was added
        setTimeout(() => {
            const addedButton = document.querySelector('.theme-toggle');
            if (addedButton) {
                console.log('Professional theme toggle button found in DOM:', addedButton);
            } else {
                console.error('Professional theme toggle button NOT found in DOM after adding!');
            }
        }, 100);
    }

    updateThemeToggle() {
        const button = document.querySelector('.theme-toggle');
        if (button) {
            console.log('Updating professional theme toggle button to:', this.currentTheme);
            
            const checkbox = button.querySelector('input[type="checkbox"]');
            
            // Update checkbox state
            checkbox.checked = this.currentTheme === 'dark';
            
            // Update tooltip
            button.setAttribute('title', `Switch to ${this.currentTheme === 'light' ? 'dark' : 'light'} mode`);
            
            console.log('Professional theme toggle button updated successfully');
        } else {
            console.log('Professional theme toggle button not found for update');
        }
    }

    addKeyboardShortcut() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Shift + T
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
                e.preventDefault();
                this.toggleTheme();
            }
        });
    }

    detectSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            // If no stored theme and system prefers dark, apply dark theme
            if (!localStorage.getItem('theme')) {
                this.applyTheme('dark');
            }
        }

        // Listen for system theme changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    this.applyTheme(e.matches ? 'dark' : 'light');
                }
            });
        }
    }

    notifyThemeChange(theme) {
        // Dispatch custom event for other components to listen
        const event = new CustomEvent('themeChanged', {
            detail: { theme: theme }
        });
        document.dispatchEvent(event);

        // Notification disabled - no longer show theme change messages
        // this.showThemeNotification(theme);
    }

    showThemeNotification(theme) {
        // Function disabled - no notifications will be shown
        return;
        
        /*
        // Create a subtle notification
        const notification = document.createElement('div');
        notification.className = 'theme-notification';
        notification.textContent = `${theme === 'light' ? 'Light' : 'Dark'} mode activated`;
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--primary-blue);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            z-index: 1002;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            pointer-events: none;
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 10);

        // Remove after 2 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 2000);
        */
    }

    // Get current theme
    getCurrentTheme() {
        return this.currentTheme;
    }

    // Check if dark mode is active
    isDarkMode() {
        return this.currentTheme === 'dark';
    }
}

// Initialize theme manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing ThemeManager...');
    window.themeManager = new ThemeManager();
    console.log('ThemeManager created:', window.themeManager);
});

// Also initialize immediately if DOM is already loaded
if (document.readyState === 'loading') {
    // DOM is still loading
    console.log('DOM is still loading...');
} else {
    // DOM is already loaded
    console.log('DOM already loaded, initializing ThemeManager immediately...');
    if (!window.themeManager) {
        window.themeManager = new ThemeManager();
        console.log('ThemeManager created immediately:', window.themeManager);
    }
}

// Make it globally available
window.ThemeManager = ThemeManager;

// Test: Log that theme.js loaded
console.log('Theme.js loaded successfully!');

// Add global test function
window.testThemeToggle = function() {
    console.log('Testing theme toggle...');
    if (window.themeManager) {
        window.themeManager.toggleTheme();
    } else {
        console.error('ThemeManager not found!');
    }
};

// Test: Try to create button immediately if DOM is ready
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    console.log('DOM is ready, creating theme toggle immediately...');
    setTimeout(() => {
        if (!window.themeManager) {
            window.themeManager = new ThemeManager();
        }
    }, 100);
}
