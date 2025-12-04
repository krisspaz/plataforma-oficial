import { api } from './api';
import type {
    ParentAccount,
    StudentTask,
    Contract,
    Payment,
} from '@/types/modules.types';

export const padresService = {
    // Cuenta
    getMyAccounts: async (): Promise<ParentAccount[]> => {
        return api.get<ParentAccount[]>('/padres/accounts');
    },

    getPaymentHistory: async (studentId: number): Promise<Payment[]> => {
        return api.get<Payment[]>(`/padres/payments/history?studentId=${studentId}`);
    },

    // Tareas
    getChildrenTasks: async (): Promise<StudentTask[]> => {
        return api.get<StudentTask[]>('/padres/tasks');
    },

    getTasksByStudent: async (studentId: number): Promise<StudentTask[]> => {
        return api.get<StudentTask[]>(`/padres/tasks?studentId=${studentId}`);
    },

    // Contratos
    getContracts: async (): Promise<Contract[]> => {
        return api.get<Contract[]>('/padres/contracts');
    },

    downloadContract: async (contractId: number): Promise<{ pdfUrl: string }> => {
        return api.get<{ pdfUrl: string }>(`/padres/contracts/${contractId}/download`);
    },
};
