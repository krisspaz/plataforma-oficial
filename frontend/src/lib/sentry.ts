import * as Sentry from '@sentry/react';

// Initialize Sentry for error tracking
export const initSentry = () => {
    if (import.meta.env.VITE_SENTRY_DSN) {
        Sentry.init({
            dsn: import.meta.env.VITE_SENTRY_DSN,
            environment: import.meta.env.MODE,
            tracesSampleRate: 1.0,
            replaysSessionSampleRate: 0.1,
            replaysOnErrorSampleRate: 1.0,
            integrations: [
                Sentry.browserTracingIntegration(),
                Sentry.replayIntegration(),
            ],
        });
    }
};

// Capture exception with context
export const captureException = (error: Error, context?: Record<string, unknown>) => {
    Sentry.captureException(error, {
        extra: context,
    });
};

// Set user context
export const setUser = (user: { id: string; email: string; role: string } | null) => {
    if (user) {
        Sentry.setUser({
            id: user.id,
            email: user.email,
            role: user.role,
        });
    } else {
        Sentry.setUser(null);
    }
};

// Add breadcrumb for navigation
export const addBreadcrumb = (message: string, category: string, data?: Record<string, unknown>) => {
    Sentry.addBreadcrumb({
        message,
        category,
        data,
        level: 'info',
    });
};
