import { api } from './api';

interface CacheStats {
    grades: string;
    announcements: string;
    calendar: string;
    payments: string;
}

/**
 * Cache management service for admin operations
 */
export const cacheService = {
    /**
     * Get cache statistics
     */
    async getStats(): Promise<CacheStats> {
        const response = await api.get<{ data: CacheStats }>('/cache/stats');
        return response.data.data;
    },

    /**
     * Clear all caches
     */
    async clearAll(): Promise<void> {
        await api.post('/cache/clear');
    },

    /**
     * Clear grades cache
     */
    async clearGrades(): Promise<void> {
        await api.post('/cache/clear/grades');
    },

    /**
     * Clear announcements cache
     */
    async clearAnnouncements(): Promise<void> {
        await api.post('/cache/clear/announcements');
    },

    /**
     * Clear calendar cache
     */
    async clearCalendar(): Promise<void> {
        await api.post('/cache/clear/calendar');
    },

    /**
     * Clear payments cache
     */
    async clearPayments(): Promise<void> {
        await api.post('/cache/clear/payments');
    },
};

/**
 * Client-side cache using localStorage with TTL
 */
class LocalCache {
    private prefix = 'school_cache_';

    set<T>(key: string, data: T, ttlSeconds: number): void {
        const item = {
            data,
            expiry: Date.now() + ttlSeconds * 1000,
        };
        localStorage.setItem(this.prefix + key, JSON.stringify(item));
    }

    get<T>(key: string): T | null {
        const itemStr = localStorage.getItem(this.prefix + key);
        if (!itemStr) return null;

        try {
            const item = JSON.parse(itemStr);
            if (Date.now() > item.expiry) {
                localStorage.removeItem(this.prefix + key);
                return null;
            }
            return item.data as T;
        } catch {
            return null;
        }
    }

    remove(key: string): void {
        localStorage.removeItem(this.prefix + key);
    }

    clear(): void {
        Object.keys(localStorage)
            .filter(key => key.startsWith(this.prefix))
            .forEach(key => localStorage.removeItem(key));
    }
}

export const localCache = new LocalCache();

/**
 * React Query cache configuration
 */
export const queryCacheConfig = {
    defaultOptions: {
        queries: {
            staleTime: 5 * 60 * 1000, // 5 minutes
            cacheTime: 30 * 60 * 1000, // 30 minutes
            refetchOnWindowFocus: false,
            retry: 1,
        },
    },
};

/**
 * Cache keys for React Query
 */
export const CACHE_KEYS = {
    // Grades
    studentGrades: (studentId: number, year: number) => ['grades', 'student', studentId, year],
    subjectGrades: (subjectId: number, bimester: number) => ['grades', 'subject', subjectId, bimester],

    // Announcements
    announcements: (type?: string) => ['announcements', type ?? 'all'],

    // Calendar
    calendarEvents: (start: string, end: string) => ['calendar', start, end],

    // Payments
    debtors: (gradeId?: number) => ['debtors', gradeId ?? 'all'],
    dailyClosure: (date: string) => ['closure', date],
    paymentPlan: (planId: string) => ['payment-plan', planId],

    // User
    currentUser: () => ['user', 'current'],
    userProfile: (userId: string) => ['user', 'profile', userId],
};
