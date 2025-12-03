import { create } from 'zustand'
import { persist } from 'zustand/middleware'

interface User {
    id: number
    email: string
    firstName: string
    lastName: string
    roles: string[]
}

interface AuthState {
    user: User | null
    token: string | null
    isAuthenticated: boolean
    login: (user: User, token: string) => void
    logout: () => void
}

interface UIState {
    theme: 'light' | 'dark'
    sidebarOpen: boolean
    toggleTheme: () => void
    toggleSidebar: () => void
    setSidebarOpen: (open: boolean) => void
}

export const useAuthStore = create<AuthState>()(
    persist(
        (set) => ({
            user: null,
            token: null,
            isAuthenticated: false,
            login: (user, token) => set({ user, token, isAuthenticated: true }),
            logout: () => set({ user: null, token: null, isAuthenticated: false }),
        }),
        {
            name: 'auth-storage',
        }
    )
)

export const useUIStore = create<UIState>()(
    persist(
        (set) => ({
            theme: 'light',
            sidebarOpen: true,
            toggleTheme: () => set((state) => ({ theme: state.theme === 'light' ? 'dark' : 'light' })),
            toggleSidebar: () => set((state) => ({ sidebarOpen: !state.sidebarOpen })),
            setSidebarOpen: (open) => set({ sidebarOpen: open }),
        }),
        {
            name: 'ui-storage',
        }
    )
)
