import { api } from './api';
import type {
    Payment,
    PaymentPlan,
    Debtor,
    Enrollment,
    Contract,
    DailyReport,
} from '@/types/modules.types';

export const secretariaService = {
    // Pagos
    createPayment: async (payment: Omit<Payment, 'id'>): Promise<Payment> => {
        return api.post<Payment>('/secretaria/payments', payment);
    },

    createPaymentPlan: async (plan: Omit<PaymentPlan, 'id'>): Promise<PaymentPlan> => {
        return api.post<PaymentPlan>('/secretaria/payment-plans', plan);
    },

    getDebtors: async (): Promise<Debtor[]> => {
        return api.get<Debtor[]>('/secretaria/debtors');
    },

    getDailyReport: async (date: string): Promise<DailyReport> => {
        return api.get<DailyReport>(`/secretaria/daily-report?date=${date}`);
    },

    // Inscripciones
    createEnrollment: async (enrollment: Omit<Enrollment, 'id'>): Promise<Enrollment> => {
        return api.post<Enrollment>('/secretaria/enrollments', enrollment);
    },

    matriculateStudent: async (studentId: number, gradeId: number, sectionId: number): Promise<Enrollment> => {
        return api.post<Enrollment>('/secretaria/matriculate', { studentId, gradeId, sectionId });
    },

    // Contratos
    generateContract: async (contractData: Omit<Contract, 'id' | 'generatedDate'>): Promise<{ pdfUrl: string; contract: Contract }> => {
        return api.post<{ pdfUrl: string; contract: Contract }>('/secretaria/contracts/generate', contractData);
    },

    uploadSignedContract: async (contractId: number, file: File): Promise<Contract> => {
        const formData = new FormData();
        formData.append('file', file);

        const response = await fetch(`${import.meta.env.VITE_API_URL}/secretaria/contracts/${contractId}/upload`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
            body: formData,
        });

        if (!response.ok) {
            throw new Error('Error al subir el contrato');
        }

        return response.json();
    },

    getContracts: async (): Promise<Contract[]> => {
        return api.get<Contract[]>('/secretaria/contracts');
    },
};
