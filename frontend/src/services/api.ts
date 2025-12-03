const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

interface FetchOptions extends RequestInit {
    headers?: Record<string, string>;
}

export const api = {
    get: async <T>(endpoint: string, options: FetchOptions = {}): Promise<T> => {
        return request<T>(endpoint, { ...options, method: 'GET' });
    },
    post: async <T>(endpoint: string, body: any, options: FetchOptions = {}): Promise<T> => {
        return request<T>(endpoint, {
            ...options,
            method: 'POST',
            body: JSON.stringify(body),
        });
    },
    put: async <T>(endpoint: string, body: any, options: FetchOptions = {}): Promise<T> => {
        return request<T>(endpoint, {
            ...options,
            method: 'PUT',
            body: JSON.stringify(body),
        });
    },
    delete: async <T>(endpoint: string, options: FetchOptions = {}): Promise<T> => {
        return request<T>(endpoint, { ...options, method: 'DELETE' });
    },
};

async function request<T>(endpoint: string, options: FetchOptions): Promise<T> {
    const token = localStorage.getItem('token');

    const headers = {
        'Content-Type': 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...options.headers,
    };

    const config: RequestInit = {
        ...options,
        headers,
    };

    const response = await fetch(`${API_URL}${endpoint}`, config);

    if (response.status === 401) {
        // Token expired or invalid
        localStorage.removeItem('token');
        window.location.href = '/login';
        throw new Error('Unauthorized');
    }

    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || errorData.error || 'API Error');
    }

    return response.json();
}
