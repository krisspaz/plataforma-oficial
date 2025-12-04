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
    /**
     * Stores a value securely in localStorage
     * @param key - Storage key
     * @param value - Value to store
     */
    setItem: (key: string, value: string): void => {
        try {
            // In production, encrypt the value before storing
            const encoded = btoa(value);
            localStorage.setItem(key, encoded);
        } catch (error) {
            console.error('Failed to store item securely:', error);
        }
    },

    /**
     * Retrieves a value from secure storage
     * @param key - Storage key
     * @returns Decrypted value or null
     */
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

    /**
     * Removes an item from secure storage
     * @param key - Storage key
     */
    removeItem: (key: string): void => {
        localStorage.removeItem(key);
    },

    /**
     * Clears all items from secure storage
     */
    clear: (): void => {
        localStorage.clear();
        sessionStorage.clear();
    },
};

/**
 * Validates JWT token structure (basic validation)
 * @param token - JWT token to validate
 * @returns true if token structure is valid
 */
export const isValidJWT = (token: string): boolean => {
    const parts = token.split('.');
    return parts.length === 3;
};

/**
 * Checks if JWT token is expired
 * @param token - JWT token to check
 * @returns true if token is expired
 */
export const isTokenExpired = (token: string): boolean => {
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const exp = payload.exp * 1000; // Convert to milliseconds
        return Date.now() >= exp;
    } catch {
        return true;
    }
};

/**
 * Rate limiting helper for API calls
 */
class RateLimiter {
    private requests: Map<string, number[]> = new Map();
    private maxRequests: number;
    private windowMs: number;

    /**
     * Creates a rate limiter
     * @param maxRequests - Maximum requests allowed
     * @param windowMs - Time window in milliseconds
     */
    constructor(maxRequests: number = 10, windowMs: number = 60000) {
        this.maxRequests = maxRequests;
        this.windowMs = windowMs;
    }

    /**
     * Checks if a request is allowed
     * @param key - Unique identifier for the request
     * @returns true if request is allowed
     */
    isAllowed(key: string): boolean {
        const now = Date.now();
        const requests = this.requests.get(key) || [];

        // Remove old requests outside the window
        const validRequests = requests.filter(time => now - time < this.windowMs);

        if (validRequests.length >= this.maxRequests) {
            return false;
        }

        validRequests.push(now);
        this.requests.set(key, validRequests);
        return true;
    }

    /**
     * Resets rate limit for a key
     * @param key - Key to reset
     */
    reset(key: string): void {
        this.requests.delete(key);
    }
}

export const rateLimiter = new RateLimiter(10, 60000); // 10 requests per minute

/**
 * Content Security Policy headers helper
 */
export const CSP_DIRECTIVES = {
    'default-src': ["'self'"],
    'script-src': ["'self'", "'unsafe-inline'"], // Consider removing unsafe-inline in production
    'style-src': ["'self'", "'unsafe-inline'"],
    'img-src': ["'self'", 'data:', 'https:'],
    'font-src': ["'self'", 'data:'],
    'connect-src': ["'self'", process.env.VITE_API_URL || 'http://localhost:8000'],
    'frame-ancestors': ["'none'"],
    'base-uri': ["'self'"],
    'form-action': ["'self'"],
};

/**
 * Generates CSP header string
 * @returns CSP header value
 */
export const generateCSPHeader = (): string => {
    return Object.entries(CSP_DIRECTIVES)
        .map(([key, values]) => `${key} ${values.join(' ')}`)
        .join('; ');
};

/**
 * Password strength validator
 * @param password - Password to validate
 * @returns Strength score (0-4) and feedback
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
    /**
     * Stores a value securely in localStorage
     * @param key - Storage key
     * @param value - Value to store
     */
    setItem: (key: string, value: string): void => {
        try {
            // In production, encrypt the value before storing
            const encoded = btoa(value);
            localStorage.setItem(key, encoded);
        } catch (error) {
            console.error('Failed to store item securely:', error);
        }
    },

    /**
     * Retrieves a value from secure storage
     * @param key - Storage key
     * @returns Decrypted value or null
     */
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

    /**
     * Removes an item from secure storage
     * @param key - Storage key
     */
    removeItem: (key: string): void => {
        localStorage.removeItem(key);
    },

    /**
     * Clears all items from secure storage
     */
    clear: (): void => {
        localStorage.clear();
        sessionStorage.clear();
    },
};

/**
 * Validates JWT token structure (basic validation)
 * @param token - JWT token to validate
 * @returns true if token structure is valid
 */
export const isValidJWT = (token: string): boolean => {
    const parts = token.split('.');
    return parts.length === 3;
};

/**
 * Checks if JWT token is expired
 * @param token - JWT token to check
 * @returns true if token is expired
 */
export const isTokenExpired = (token: string): boolean => {
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const exp = payload.exp * 1000; // Convert to milliseconds
        return Date.now() >= exp;
    } catch {
        return true;
    }
};

/**
 * Rate limiting helper for API calls
 */
class RateLimiter {
    private requests: Map<string, number[]> = new Map();
    private maxRequests: number;
    private windowMs: number;

    /**
     * Creates a rate limiter
     * @param maxRequests - Maximum requests allowed
     * @param windowMs - Time window in milliseconds
     */
    constructor(maxRequests: number = 10, windowMs: number = 60000) {
        this.maxRequests = maxRequests;
        this.windowMs = windowMs;
    }

    /**
     * Checks if a request is allowed
     * @param key - Unique identifier for the request
     * @returns true if request is allowed
     */
    isAllowed(key: string): boolean {
        const now = Date.now();
        const requests = this.requests.get(key) || [];

        // Remove old requests outside the window
        const validRequests = requests.filter(time => now - time < this.windowMs);

        if (validRequests.length >= this.maxRequests) {
            return false;
        }

        validRequests.push(now);
        this.requests.set(key, validRequests);
        return true;
    }

    /**
     * Resets rate limit for a key
     * @param key - Key to reset
     */
    reset(key: string): void {
        this.requests.delete(key);
    }
}

export const rateLimiter = new RateLimiter(10, 60000); // 10 requests per minute

/**
 * Content Security Policy headers helper
 */
export const CSP_DIRECTIVES = {
    'default-src': ["'self'"],
    'script-src': ["'self'", "'unsafe-inline'"], // Consider removing unsafe-inline in production
    'style-src': ["'self'", "'unsafe-inline'"],
    'img-src': ["'self'", 'data:', 'https:'],
    'font-src': ["'self'", 'data:'],
    'connect-src': ["'self'", process.env.VITE_API_URL || 'http://localhost:8000'],
    'frame-ancestors': ["'none'"],
    'base-uri': ["'self'"],
    'form-action': ["'self'"],
};

/**
 * Generates CSP header string
 * @returns CSP header value
 */
export const generateCSPHeader = (): string => {
    return Object.entries(CSP_DIRECTIVES)
        .map(([key, values]) => `${key} ${values.join(' ')}`)
        .join('; ');
};

/**
 * Password strength validator
 * @param password - Password to validate
 * @returns Strength score (0-4) and feedback
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
 * Prevents clickjacking by checking if app is in iframe
 */
export const preventClickjacking = (): void => {
    if (window.self !== window.top) {
        window.top!.location.href = window.self.location.href;
    }
};
