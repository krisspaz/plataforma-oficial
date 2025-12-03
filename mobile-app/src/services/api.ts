import Constants from 'expo-constants';

// Use localhost for emulator (10.0.2.2 for Android, localhost for iOS)
// or the machine's IP address for physical devices.
const API_URL = 'http://10.0.2.2:8000/api';

export const api = {
    get: async (endpoint: string, token?: string) => {
        const headers = {
            'Content-Type': 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
        };
        const response = await fetch(`${API_URL}${endpoint}`, { headers });
        return response.json();
    },
    post: async (endpoint: string, body: any, token?: string) => {
        const headers = {
            'Content-Type': 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
        };
        const response = await fetch(`${API_URL}${endpoint}`, {
            method: 'POST',
            headers,
            body: JSON.stringify(body),
        });
        return response.json();
    }
};
