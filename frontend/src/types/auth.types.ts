export interface User {
  id: number;
  email: string;
  roles: string[];
  firstName: string;
  lastName: string;
  username: string;
}

export interface LoginResponse {
  token: string;
}

export interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
}
