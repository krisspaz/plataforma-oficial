/**
 * Security Utility Functions
 * 
 * Provides security-related utilities for the application including
 * token management, CSRF protection, and secure storage.
 */

/**
 * Generates a random CSRF token
 * @returns CSRF token string
 */
export const generateCSRFToken = (): string => {
    const array = new Uint8Array(32);
    crypto.getRandomValues(array);
    return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
};

/**
 * Stores CSRF token in sessionStorage
 * @param token - CSRF token to store
 */
export const storeCSRFToken = (token: string): void => {
    sessionStorage.setItem('csrf_token', token);
};

/**
 * Retrieves CSRF token from sessionStorage
 * @returns CSRF token or null
 */
export const getCSRFToken = (): string | null => {
    return sessionStorage.getItem('csrf_token');
};

/**
 * Secure token storage with encryption (basic implementation)
 * In production, consider using a more robust encryption library
 */
export const secureStorage = {
    setItem: (key: string, value: string): void => {
        try {
            const encoded = btoa(value);
            localStorage.setItem(key, encoded);
        } catch (error) {
            console.error('Failed to store item securely:', error);
        }
    },

    getItem: (key: string): string | null => {
        try {
            const encoded = localStorage.getItem(key);
            if (!encoded) return null;
            return atob(encoded);
        } catch (error) {
            console.error('Failed to retrieve item securely:', error);
            return null;
        }
    },

    removeItem: (key: string): void => {
        localStorage.removeItem(key);
    },

    clear: (): void => {
        localStorage.clear();
        sessionStorage.clear();
    },
};

/**
 * Validates JWT token structure
 */
export const isValidJWT = (token: string): boolean => {
    const parts = token.split('.');
    return parts.length === 3;
};

/**
 * Checks if JWT token is expired
 */
export const isTokenExpired = (token: string): boolean => {
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const exp = payload.exp * 1000;
        return Date.now() >= exp;
    } catch {
        return true;
    }
};

/**
 * Rate limiting helper
 */
class RateLimiter {
    private requests: Map<string, number[]> = new Map();
    private maxRequests: number;
    private windowMs: number;

    constructor(maxRequests: number = 10, windowMs: number = 60000) {
        this.maxRequests = maxRequests;
        this.windowMs = windowMs;
    }

    isAllowed(key: string): boolean {
        const now = Date.now();
        const requests = this.requests.get(key) || [];
        const validRequests = requests.filter(time => now - time < this.windowMs);

        if (validRequests.length >= this.maxRequests) {
            return false;
        }

        validRequests.push(now);
        this.requests.set(key, validRequests);
        return true;
    }

    reset(key: string): void {
        this.requests.delete(key);
    }
}

export const rateLimiter = new RateLimiter(10, 60000);

/**
 * Password strength validator
 */
export const validatePasswordStrength = (password: string): { score: number; feedback: string[] } => {
    const feedback: string[] = [];
    let score = 0;

    if (password.length >= 8) score++;
    else feedback.push('La contraseña debe tener al menos 8 caracteres');

    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    else feedback.push('Debe incluir mayúsculas y minúsculas');

    if (/\d/.test(password)) score++;
    else feedback.push('Debe incluir al menos un número');

    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) score++;
    else feedback.push('Debe incluir al menos un carácter especial');

    return { score, feedback };
};

/**
 * Prevents clickjacking
 */
export const preventClickjacking = (): void => {
    if (window.self !== window.top) {
        window.top!.location.href = window.self.location.href;
    }
};
