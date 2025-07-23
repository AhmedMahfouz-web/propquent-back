/**
 * Theme Validator
 * Validates theme configurations and CSS properties
 */
class ThemeValidator {
    constructor() {
        this.requiredProperties = [
            '--primary-rgb',
            '--primary-500',
            '--primary-600',
            '--primary-700'
        ];

        this.requiredClasses = [
            '.fi-btn-primary',
            '.fi-input',
            '.fi-select',
            '.fi-checkbox',
            '.fi-radio'
        ];
    }

    /**
     * Validate current theme application
     */
    validateCurrentTheme() {
        const results = {
            valid: true,
            errors: [],
            warnings: [],
            properties: {},
            classes: {}
        };

        // Check CSS custom properties
        const root = document.documentElement;
        const computedStyle = getComputedStyle(root);

        this.requiredProperties.forEach(property => {
            const value = computedStyle.getPropertyValue(property).trim();
            results.properties[property] = value;

            if (!value) {
                results.valid = false;
                results.errors.push(`Missing CSS property: ${property}`);
            }
        });

        // Check if theme classes exist and have proper styles
        this.requiredClasses.forEach(className => {
            const elements = document.querySelectorAll(className);
            results.classes[className] = elements.length;

            if (elements.length === 0) {
                results.warnings.push(`No elements found with class: ${className}`);
            } else {
                // Check if primary color is applied
                const element = elements[0];
                const styles = getComputedStyle(element);
                const bgColor = styles.backgroundColor;
                const borderColor = styles.borderColor;

                if (!bgColor.includes('rgb') && !borderColor.includes('rgb')) {
                    results.warnings.push(`Class ${className} may not have theme colors applied`);
                }
            }
        });

        return results;
    }

    /**
     * Test theme switching performance
     */
    async testThemeSwitchingPerformance(themes = ['blue', 'green', 'purple']) {
        const results = {
            averageTime: 0,
            times: [],
            errors: []
        };

        for (const theme of themes) {
            try {
                const startTime = performance.now();

                if (window.themeManager) {
                    window.themeManager.applyTheme(theme);
                }

                // Wait for transitions to complete
                await new Promise(resolve => setTimeout(resolve, 350));

                const endTime = performance.now();
                const duration = endTime - startTime;

                results.times.push({ theme, duration });
            } catch (error) {
                results.errors.push({ theme, error: error.message });
            }
        }

        if (results.times.length > 0) {
            results.averageTime = results.times.reduce((sum, t) => sum + t.duration, 0) / results.times.length;
        }

        return results;
    }

    /**
     * Check for theme-related accessibility issues
     */
    checkAccessibility() {
        const results = {
            issues: [],
            recommendations: []
        };

        // Check contrast ratios
        const primaryRgb = getComputedStyle(document.documentElement)
            .getPropertyValue('--primary-rgb').trim();

        if (primaryRgb) {
            const [r, g, b] = primaryRgb.split(',').map(n => parseInt(n.trim()));
            const luminance = this.calculateLuminance(r, g, b);

            if (luminance < 0.3) {
                results.recommendations.push('Consider using a lighter primary color for better accessibility');
            }
        }

        // Check for reduced motion support
        const hasReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (hasReducedMotion) {
            const transitionElements = document.querySelectorAll('[style*="transition"]');
            if (transitionElements.length > 0) {
                results.recommendations.push('Consider disabling transitions for users with reduced motion preference');
            }
        }

        // Check focus indicators
        const focusableElements = document.querySelectorAll('button, input, select, textarea, a[href]');
        let missingFocusIndicators = 0;

        focusableElements.forEach(element => {
            const styles = getComputedStyle(element, ':focus');
            if (!styles.outline && !styles.boxShadow) {
                missingFocusIndicators++;
            }
        });

        if (missingFocusIndicators > 0) {
            results.issues.push(`${missingFocusIndicators} elements may be missing focus indicators`);
        }

        return results;
    }

    /**
     * Calculate relative luminance for accessibility
     */
    calculateLuminance(r, g, b) {
        const [rs, gs, bs] = [r, g, b].map(c => {
            c = c / 255;
            return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
        });

        return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
    }

    /**
     * Generate theme validation report
     */
    async generateReport() {
        const report = {
            timestamp: new Date().toISOString(),
            theme: window.themeManager?.currentTheme || 'unknown',
            validation: this.validateCurrentTheme(),
            accessibility: this.checkAccessibility(),
            performance: null
        };

        // Add performance test if requested
        try {
            report.performance = await this.testThemeSwitchingPerformance();
        } catch (error) {
            report.performance = { error: error.message };
        }

        return report;
    }

    /**
     * Log validation results to console
     */
    logValidationResults(results) {
        console.group('🎨 Theme Validation Results');

        if (results.valid) {
            console.log('✅ Theme validation passed');
        } else {
            console.warn('❌ Theme validation failed');
            results.errors.forEach(error => console.error('Error:', error));
        }

        if (results.warnings.length > 0) {
            console.group('⚠️ Warnings');
            results.warnings.forEach(warning => console.warn(warning));
            console.groupEnd();
        }

        console.group('📊 Properties');
        Object.entries(results.properties).forEach(([prop, value]) => {
            console.log(`${prop}: ${value || 'NOT SET'}`);
        });
        console.groupEnd();

        console.group('🏷️ Classes');
        Object.entries(results.classes).forEach(([className, count]) => {
            console.log(`${className}: ${count} elements`);
        });
        console.groupEnd();

        console.groupEnd();
    }
}

// Initialize theme validator
document.addEventListener('DOMContentLoaded', () => {
    window.themeValidator = new ThemeValidator();

    // Auto-validate on theme changes
    document.addEventListener('themeChanged', () => {
        setTimeout(() => {
            const results = window.themeValidator.validateCurrentTheme();
            if (!results.valid || results.warnings.length > 0) {
                window.themeValidator.logValidationResults(results);
            }
        }, 100);
    });
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeValidator;
}
