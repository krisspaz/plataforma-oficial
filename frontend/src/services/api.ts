import { errorHandler } from '@/lib/errorHandler';
import { getCSRFToken, rateLimiter, isTokenExpired } from '@/lib/security';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

interface FetchOptions extends RequestInit {
    headers?: Record<string, string>;
}

/**
 * API Service
 * 
 * Centralized API client with security features:
 * - Automatic token management
 * - CSRF protection
 * - Rate limiting
 * - Error handling
 * - Request/response logging
 */
export const api = {
    /**
     * GET request
     * @param endpoint - API endpoint
     * @param options - Fetch options
     * @returns Promise with typed response
     */
    get: async <T>(endpoint: string, options: FetchOptions = {}): Promise<T> => {
        return request<T>(endpoint, { ...options, method: 'GET' });
    },

    /**
     * POST request
     * @param endpoint - API endpoint
     * @param body - Request body
     * @param options - Fetch options
     * @returns Promise with typed response
     */
    post: async <T>(endpoint: string, body: unknown, options: FetchOptions = {}): Promise<T> => {
        return request<T>(endpoint, {
            ...options,
            method: 'POST',
            body: JSON.stringify(body),
        });
    },

    /**
     * PUT request
     * @param endpoint - API endpoint
     * @param body - Request body
     * @param options - Fetch options
     * @returns Promise with typed response
     */
    put: async <T>(endpoint: string, body: unknown, options: FetchOptions = {}): Promise<T> => {
        return request<T>(endpoint, {
            ...options,
            method: 'PUT',
            body: JSON.stringify(body),
        });
    },

    /**
     * DELETE request
     * @param endpoint - API endpoint
     * @param options - Fetch options
     * @returns Promise with typed response
     */
    delete: async <T>(endpoint: string, options: FetchOptions = {}): Promise<T> => {
        return request<T>(endpoint, { ...options, method: 'DELETE' });
    },
};

/**
 * Internal request handler with security features
 */
async function request<T>(endpoint: string, options: FetchOptions): Promise<T> {
    // Rate limiting check
    if (!rateLimiter.isAllowed(endpoint)) {
        throw new Error('Demasiadas solicitudes. Por favor, espera un momento.');
    }

    const token = localStorage.getItem('token');

    // Check token expiration
    if (token && isTokenExpired(token)) {
        localStorage.removeItem('token');
        window.location.href = '/login';
        throw new Error('Sesión expirada. Por favor, inicia sesión nuevamente.');
    }

    const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...options.headers,
    };

    // Add CSRF token for state-changing requests
    if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(options.method || '')) {
        const csrfToken = getCSRFToken();
        if (csrfToken) {
            headers['X-CSRF-Token'] = csrfToken;
        }
    }

    const config: RequestInit = {
        ...options,
        headers,
        credentials: 'include', // Include cookies for CSRF
    };

    try {
        const response = await fetch(`${API_URL}${endpoint}`, config);

        if (response.status === 401) {
            // Token expired or invalid
            localStorage.removeItem('token');
            window.location.href = '/login';
            throw new Error('Sesión expirada. Por favor, inicia sesión nuevamente.');
        }

        if (response.status === 403) {
            throw new Error('No tienes permisos para realizar esta acción.');
        }

        if (response.status === 429) {
            throw new Error('Demasiadas solicitudes. Por favor, intenta más tarde.');
        }

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            const errorMessage = errorData.message || errorData.error || `Error ${response.status}: ${response.statusText}`;
            throw new Error(errorMessage);
        }

        return response.json();
    } catch (error) {
        errorHandler.logError(error, `API ${options.method} ${endpoint}`);
        throw error;
    }
}
