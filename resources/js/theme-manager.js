/**
 * Theme Manager for Filament Admin Panel
 * Handles theme switching and persistence
 */
class ThemeManager {
    constructor() {
        this.themes = {
            'blue': { name: 'Blue', primary: '#3b82f6' },
            'green': { name: 'Green', primary: '#10b981' },
            'purple': { name: 'Purple', primary: '#8b5cf6' },
            'orange': { name: 'Orange', primary: '#f59e0b' },
            'red': { name: 'Red', primary: '#ef4444' },
            'indigo': { name: 'Indigo', primary: '#6366f1' },
            'pink': { name: 'Pink', primary: '#ec4899' },
            'teal': { name: 'Teal', primary: '#14b8a6' },
        };

        this.currentTheme = this.getCurrentTheme();
        this.initializeTheme();
        this.bindEvents();
    }

    /**
     * Initialize theme on page load
     */
    initializeTheme() {
        // Try to get theme from server first, then localStorage, then system preference
        this.loadThemeFromServer()
            .then(serverTheme => {
                if (serverTheme && this.themes[serverTheme]) {
                    this.applyTheme(serverTheme);
                    this.saveTheme(serverTheme); // Sync localStorage with server
                } else {
                    this.fallbackThemeInitialization();
                }
            })
            .catch(() => {
                this.fallbackThemeInitialization();
            });
    }

    /**
     * Fallback theme initialization when server is unavailable
     */
    fallbackThemeInitialization() {
        const savedTheme = this.getSavedTheme();
        if (savedTheme && this.themes[savedTheme]) {
            this.applyTheme(savedTheme);
        } else {
            // Fallback to system preference or default
            const systemTheme = this.getSystemTheme();
            this.applyTheme(systemTheme);
        }
    }

    /**
     * Load theme preference from server
     */
    async loadThemeFromServer() {
        try {
            const response = await fetch('/api/admin/theme', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const result = await response.json();
                return result.success ? result.data.theme : null;
            }
        } catch (error) {
            console.warn('Failed to load theme from server:', error);
        }
        return null;
    }

    /**
     * Get current theme from localStorage or server
     */
    getCurrentTheme() {
        return localStorage.getItem('theme') || 'blue';
    }

    /**
     * Get saved theme from localStorage
     */
    getSavedTheme() {
        return localStorage.getItem('theme');
    }

    /**
     * Get system theme preference
     */
    getSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'blue'; // Default to blue for dark mode
        }
        return 'blue'; // Default theme
    }

    /**
     * Apply theme to the document
     */
    applyTheme(theme) {
        if (!this.themes[theme]) {
            console.warn(`Theme '${theme}' not found. Using default.`);
            theme = 'blue';
        }

        // Update CSS custom properties for Filament
        const root = document.documentElement;
        const themeConfig = this.themes[theme];

        // Set primary color
        root.style.setProperty('--primary-50', this.lighten(themeConfig.primary, 95));
        root.style.setProperty('--primary-100', this.lighten(themeConfig.primary, 90));
        root.style.setProperty('--primary-200', this.lighten(themeConfig.primary, 80));
        root.style.setProperty('--primary-300', this.lighten(themeConfig.primary, 70));
        root.style.setProperty('--primary-400', this.lighten(themeConfig.primary, 60));
        root.style.setProperty('--primary-500', themeConfig.primary);
        root.style.setProperty('--primary-600', this.darken(themeConfig.primary, 10));
        root.style.setProperty('--primary-700', this.darken(themeConfig.primary, 20));
        root.style.setProperty('--primary-800', this.darken(themeConfig.primary, 30));
        root.style.setProperty('--primary-900', this.darken(themeConfig.primary, 40));

        // Update data attribute for Filament
        root.setAttribute('data-theme', theme);

        // Update current theme
        this.currentTheme = theme;

        // Update theme icon if exists
        this.updateThemeIcon(theme);

        // Dispatch theme change event
        this.dispatchThemeChangeEvent(theme);
    }

    /**
     * Switch to a specific theme
     */
    switchTheme(theme) {
        if (!this.themes[theme]) {
            console.error(`Theme '${theme}' not found`);
            this.handleThemeError(new Error(`Theme '${theme}' not found`));
            return false;
        }

        try {
            // Apply theme immediately for better UX
            this.applyTheme(theme);
            this.saveTheme(theme);

            // Sync with server (async, don't block UI)
            this.syncThemeWithServer(theme);

            return true;
        } catch (error) {
            this.handleThemeError(error);
            return false;
        }
    }

    /**
     * Toggle between light and dark variants (if available)
     */
    toggleTheme() {
        // For now, just cycle through available themes
        const themeKeys = Object.keys(this.themes);
        const currentIndex = themeKeys.indexOf(this.currentTheme);
        const nextIndex = (currentIndex + 1) % themeKeys.length;
        const nextTheme = themeKeys[nextIndex];

        this.switchTheme(nextTheme);
    }

    /**
     * Save theme to localStorage
     */
    saveTheme(theme) {
        localStorage.setItem('theme', theme);
    }

    /**
     * Sync theme with server
     */
    async syncThemeWithServer(theme) {
        try {
            // Get CSRF token for Laravel
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                console.warn('CSRF token not found, skipping server sync');
                return;
            }

            const response = await fetch('/admin/api/theme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ theme })
            });

            if (!response.ok) {
                throw new Error(`Server responded with ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            if (result.success) {
                console.log(`Theme '${theme}' synced successfully with server`);

                // Show success notification if Filament notifications are available
                if (window.Filament?.notifications) {
                    window.Filament.notifications.send({
                        title: 'Theme Updated',
                        body: `Theme switched to ${this.themes[theme]?.name || theme}`,
                        type: 'success',
                        duration: 3000
                    });
                }
            } else {
                throw new Error(result.message || 'Unknown server error');
            }
        } catch (error) {
            console.error('Failed to sync theme with server:', error);

            // Show error notification
            if (window.Filament?.notifications) {
                window.Filament.notifications.send({
                    title: 'Theme Sync Failed',
                    body: 'Theme was applied locally but could not be saved to server',
                    type: 'warning',
                    duration: 5000
                });
            }

            // Still allow local theme change even if server sync fails
        }
    }

    /**
     * Update theme icon in UI
     */
    updateThemeIcon(theme) {
        const themeButtons = document.querySelectorAll('[data-theme-button]');
        themeButtons.forEach(button => {
            const buttonTheme = button.getAttribute('data-theme');
            if (buttonTheme === theme) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Listen for theme button clicks
        document.addEventListener('click', (e) => {
            const themeButton = e.target.closest('[data-theme-button]');
            if (themeButton) {
                const theme = themeButton.getAttribute('data-theme');
                if (theme) {
                    this.switchTheme(theme);
                }
            }
        });

        // Listen for system theme changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!this.getSavedTheme()) {
                    // Only auto-switch if user hasn't manually selected a theme
                    const systemTheme = this.getSystemTheme();
                    this.applyTheme(systemTheme);
                }
            });
        }

        // Listen for storage changes (theme changes in other tabs)
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme' && e.newValue) {
                this.handleThemeConflict(e.newValue, 'another tab');
            }
        });
    }

    /**
     * Dispatch theme change event
     */
    dispatchThemeChangeEvent(theme) {
        const event = new CustomEvent('themeChanged', {
            detail: { theme, themeConfig: this.themes[theme] }
        });
        document.dispatchEvent(event);
    }

    /**
     * Lighten a hex color
     */
    lighten(hex, percent) {
        const num = parseInt(hex.replace('#', ''), 16);
        const amt = Math.round(2.55 * percent);
        const R = (num >> 16) + amt;
        const G = (num >> 8 & 0x00FF) + amt;
        const B = (num & 0x0000FF) + amt;
        return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
            (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
    }

    /**
     * Darken a hex color
     */
    darken(hex, percent) {
        const num = parseInt(hex.replace('#', ''), 16);
        const amt = Math.round(2.55 * percent);
        const R = (num >> 16) - amt;
        const G = (num >> 8 & 0x00FF) - amt;
        const B = (num & 0x0000FF) - amt;
        return '#' + (0x1000000 + (R > 255 ? 255 : R < 0 ? 0 : R) * 0x10000 +
            (G > 255 ? 255 : G < 0 ? 0 : G) * 0x100 +
            (B > 255 ? 255 : B < 0 ? 0 : B)).toString(16).slice(1);
    }

    /**
     * Get available themes
     */
    getAvailableThemes() {
        return this.themes;
    }

    /**
     * Handle theme loading errors
     */
    handleThemeError(error) {
        console.error('Theme loading error:', error);

        // Fallback to default theme
        this.applyTheme('blue');

        // Show user notification if possible
        if (window.Filament && window.Filament.notifications) {
            window.Filament.notifications.send({
                title: 'Theme Loading Error',
                body: 'There was an issue loading your theme. Falling back to default.',
                type: 'warning'
            });
        }
    }

    /**
     * Sync localStorage with server theme
     */
    async syncLocalStorageWithServer() {
        try {
            const serverTheme = await this.loadThemeFromServer();
            const localTheme = this.getSavedTheme();

            if (serverTheme && localTheme && serverTheme !== localTheme) {
                // Server theme takes precedence
                this.applyTheme(serverTheme);
                this.saveTheme(serverTheme);

                console.log(`Theme synced: ${localTheme} -> ${serverTheme}`);
            }
        } catch (error) {
            console.warn('Failed to sync theme with server:', error);
        }
    }

    /**
     * Handle theme conflicts between tabs
     */
    handleThemeConflict(newTheme, source = 'unknown') {
        if (this.currentTheme !== newTheme) {
            console.log(`Theme conflict detected from ${source}: ${this.currentTheme} -> ${newTheme}`);

            // Apply the new theme
            this.applyTheme(newTheme);

            // Show notification about the change
            if (window.Filament?.notifications) {
                window.Filament.notifications.send({
                    title: 'Theme Synchronized',
                    body: `Theme updated to ${this.themes[newTheme]?.name || newTheme} from another tab`,
                    type: 'info',
                    duration: 3000
                });
            }
        }
    }
}

// Initialize theme manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    try {
        window.themeManager = new ThemeManager();
    } catch (error) {
        console.error('Failed to initialize theme manager:', error);
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
}
