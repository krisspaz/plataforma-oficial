import { toast } from 'sonner';

interface ErrorOptions {
    title?: string;
    description: string;
    duration?: number;
}

export const errorHandler = {
    // Show error toast to user
    showError: ({ title = 'Error', description, duration = 5000 }: ErrorOptions) => {
        toast.error(title, {
            description,
            duration,
        });
    },

    // Handle API errors
    handleApiError: (error: unknown, customMessage?: string) => {
        let message = customMessage || 'Ha ocurrido un error. Por favor, intenta de nuevo.';

        if (error instanceof Error) {
            message = error.message;
        }

        errorHandler.showError({
            description: message,
        });
    },

    // Log error (can be extended to send to logging service)
    logError: (error: unknown, context?: string) => {
        if (process.env.NODE_ENV === 'development') {
            console.error(`[Error${context ? ` - ${context}` : ''}]:`, error);
        }
        // In production, send to error tracking service (e.g., Sentry)
    },
};
