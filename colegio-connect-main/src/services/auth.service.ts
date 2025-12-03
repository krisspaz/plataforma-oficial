import { api } from './api';
import { LoginResponse, User } from '../types/auth.types';

export const authService = {
    login: async (credentials: { email: string; password: string }): Promise<LoginResponse> => {
        return api.post<LoginResponse>('/login_check', credentials);
    },

    getCurrentUser: async (): Promise<User> => {
        return api.get<User>('/me');
    },

    logout: () => {
        localStorage.removeItem('token');
    },
};
