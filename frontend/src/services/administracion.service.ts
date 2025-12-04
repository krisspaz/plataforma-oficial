import { api } from './api';
import type {
    FinancialSummary,
    StudentStatistics,
    DailyReport,
} from '@/types/modules.types';

export const administracionService = {
    // Finanzas
    getFinancialSummary: async (): Promise<FinancialSummary> => {
        return api.get<FinancialSummary>('/administracion/financial-summary');
    },

    getDailyReport: async (date: string): Promise<DailyReport> => {
        return api.get<DailyReport>(`/administracion/daily-report?date=${date}`);
    },

    // Estadísticas
    getStudentStatistics: async (): Promise<StudentStatistics> => {
        return api.get<StudentStatistics>('/administracion/student-statistics');
    },

    // Reportes
    exportReport: async (reportType: string, format: 'PDF' | 'EXCEL'): Promise<{ url: string }> => {
        return api.post<{ url: string }>('/administracion/reports/export', { reportType, format });
    },
};
