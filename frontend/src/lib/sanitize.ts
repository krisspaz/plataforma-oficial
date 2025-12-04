/**
 * Input Sanitization Utility
 * 
 * Provides functions to sanitize user inputs and prevent XSS attacks.
 * All user inputs should be sanitized before being displayed or stored.
 */

/**
 * Sanitizes HTML content to prevent XSS attacks
 * @param input - The HTML string to sanitize
 * @returns Sanitized string safe for display
 */
export const sanitizeHtml = (input: string): string => {
    const div = document.createElement('div');
    div.textContent = input;
    return div.innerHTML;
};

/**
 * Sanitizes input for use in SQL-like queries (for API requests)
 * @param input - The input string to sanitize
 * @returns Sanitized string
 */
export const sanitizeInput = (input: string): string => {
    return input
        .replace(/[<>]/g, '') // Remove < and >
        .replace(/javascript:/gi, '') // Remove javascript: protocol
        .replace(/on\w+=/gi, '') // Remove event handlers
        .trim();
};

/**
 * Validates and sanitizes email addresses
 * @param email - Email address to validate
 * @returns Sanitized email or null if invalid
 */
export const sanitizeEmail = (email: string): string | null => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const sanitized = email.toLowerCase().trim();
    return emailRegex.test(sanitized) ? sanitized : null;
};

/**
 * Sanitizes phone numbers (Guatemala format)
 * @param phone - Phone number to sanitize
 * @returns Sanitized phone number
 */
export const sanitizePhone = (phone: string): string => {
    return phone.replace(/[^\d+]/g, '');
};

/**
 * Sanitizes file names to prevent directory traversal
 * @param filename - File name to sanitize
 * @returns Safe filename
 */
export const sanitizeFilename = (filename: string): string => {
    return filename
        .replace(/[^a-zA-Z0-9._-]/g, '_')
        .replace(/\.{2,}/g, '.')
        .substring(0, 255);
};

/**
 * Validates and sanitizes URLs
 * @param url - URL to validate
 * @returns Sanitized URL or null if invalid
 */
export const sanitizeUrl = (url: string): string | null => {
    try {
        const parsed = new URL(url);
        // Only allow http and https protocols
        if (!['http:', 'https:'].includes(parsed.protocol)) {
            return null;
        }
        return parsed.toString();
    } catch {
        return null;
    }
};

/**
 * Escapes special characters for safe display
 * @param text - Text to escape
 * @returns Escaped text
 */
export const escapeHtml = (text: string): string => {
    const map: Record<string, string> = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#x27;',
        '/': '&#x2F;',
    };
    return text.replace(/[&<>"'/]/g, (char) => map[char]);
};

/**
 * Validates DPI (Guatemalan ID) format
 * @param dpi - DPI number to validate
 * @returns true if valid format
 */
export const validateDPI = (dpi: string): boolean => {
    const cleaned = dpi.replace(/\s/g, '');
    return /^\d{13}$/.test(cleaned);
};

/**
 * Sanitizes numeric input
 * @param input - Input to sanitize
 * @param allowDecimals - Whether to allow decimal points
 * @returns Sanitized number string
 */
export const sanitizeNumeric = (input: string, allowDecimals = false): string => {
    const regex = allowDecimals ? /[^\d.]/g : /[^\d]/g;
    let sanitized = input.replace(regex, '');

    // Ensure only one decimal point
    if (allowDecimals) {
        const parts = sanitized.split('.');
        if (parts.length > 2) {
            sanitized = parts[0] + '.' + parts.slice(1).join('');
        }
    }

    return sanitized;
};
