/**
 * Accessibility Utilities
 * 
 * Provides utilities for improving accessibility (WCAG 2.1 AA compliance)
 */

/**
 * Announces message to screen readers
 * @param message - Message to announce
 * @param priority - 'polite' or 'assertive'
 */
export const announceToScreenReader = (message: string, priority: 'polite' | 'assertive' = 'polite'): void => {
    const announcement = document.createElement('div');
    announcement.setAttribute('role', 'status');
    announcement.setAttribute('aria-live', priority);
    announcement.setAttribute('aria-atomic', 'true');
    announcement.className = 'sr-only';
    announcement.textContent = message;

    document.body.appendChild(announcement);

    setTimeout(() => {
        document.body.removeChild(announcement);
    }, 1000);
};

/**
 * Checks if element is focusable
 * @param element - Element to check
 * @returns true if focusable
 */
export const isFocusable = (element: HTMLElement): boolean => {
    if (element.tabIndex < 0) return false;
    if (element.hasAttribute('disabled')) return false;

    const focusableTags = ['A', 'BUTTON', 'INPUT', 'SELECT', 'TEXTAREA'];
    return focusableTags.includes(element.tagName);
};

/**
 * Traps focus within a container (for modals)
 * @param container - Container element
 * @returns Cleanup function
 */
export const trapFocus = (container: HTMLElement): (() => void) => {
    const focusableElements = container.querySelectorAll<HTMLElement>(
        'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    const handleKeyDown = (e: KeyboardEvent) => {
        if (e.key !== 'Tab') return;

        if (e.shiftKey) {
            if (document.activeElement === firstElement) {
                e.preventDefault();
                lastElement?.focus();
            }
        } else {
            if (document.activeElement === lastElement) {
                e.preventDefault();
                firstElement?.focus();
            }
        }
    };

    container.addEventListener('keydown', handleKeyDown);
    firstElement?.focus();

    return () => {
        container.removeEventListener('keydown', handleKeyDown);
    };
};

/**
 * Checks color contrast ratio
 * @param foreground - Foreground color (hex)
 * @param background - Background color (hex)
 * @returns Contrast ratio
 */
export const getContrastRatio = (foreground: string, background: string): number => {
    const getLuminance = (hex: string): number => {
        const rgb = parseInt(hex.slice(1), 16);
        const r = (rgb >> 16) & 0xff;
        const g = (rgb >> 8) & 0xff;
        const b = (rgb >> 0) & 0xff;

        const [rs, gs, bs] = [r, g, b].map(c => {
            c = c / 255;
            return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
        });

        return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
    };

    const l1 = getLuminance(foreground);
    const l2 = getLuminance(background);

    const lighter = Math.max(l1, l2);
    const darker = Math.min(l1, l2);

    return (lighter + 0.05) / (darker + 0.05);
};

/**
 * Validates WCAG contrast requirements
 * @param foreground - Foreground color
 * @param background - Background color
 * @param level - 'AA' or 'AAA'
 * @param size - 'normal' or 'large'
 * @returns true if passes
 */
export const meetsContrastRequirement = (
    foreground: string,
    background: string,
    level: 'AA' | 'AAA' = 'AA',
    size: 'normal' | 'large' = 'normal'
): boolean => {
    const ratio = getContrastRatio(foreground, background);

    const requirements = {
        AA: { normal: 4.5, large: 3 },
        AAA: { normal: 7, large: 4.5 }
    };

    return ratio >= requirements[level][size];
};

/**
 * Generates unique ID for ARIA attributes
 * @param prefix - Prefix for ID
 * @returns Unique ID
 */
let idCounter = 0;
export const generateAriaId = (prefix: string = 'aria'): string => {
    return `${prefix}-${++idCounter}-${Date.now()}`;
};

/**
 * Keyboard navigation helper
 */
export const keyboardNav = {
    /**
     * Handles arrow key navigation in lists
     */
    handleArrowKeys: (e: KeyboardEvent, items: HTMLElement[], currentIndex: number): number => {
        let newIndex = currentIndex;

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                newIndex = (currentIndex + 1) % items.length;
                break;
            case 'ArrowUp':
                e.preventDefault();
                newIndex = currentIndex === 0 ? items.length - 1 : currentIndex - 1;
                break;
            case 'Home':
                e.preventDefault();
                newIndex = 0;
                break;
            case 'End':
                e.preventDefault();
                newIndex = items.length - 1;
                break;
        }

        items[newIndex]?.focus();
        return newIndex;
    },

    /**
     * Checks if key is actionable (Enter or Space)
     */
    isActionKey: (e: KeyboardEvent): boolean => {
        return e.key === 'Enter' || e.key === ' ';
    }
};

/**
 * Skip to main content link helper
 */
export const createSkipLink = (): HTMLAnchorElement => {
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.textContent = 'Saltar al contenido principal';
    skipLink.className = 'sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary focus:text-primary-foreground focus:rounded';

    return skipLink;
};
