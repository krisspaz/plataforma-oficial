import { api } from './api';

export interface PaymentPlan {
    id: string;
    enrollment_id: number;
    student_name: string;
    grade: string;
    total_amount: number;
    number_of_installments: number;
    installment_amount: number;
    day_of_month: number;
    status: string;
    total_paid: number;
    total_pending: number;
    progress: number;
    has_overdue: boolean;
    paid_installments: number;
    pending_installments: number;
    created_at: string;
    completed_at: string | null;
    installments: Installment[];
}

export interface Installment {
    id: string;
    number: number;
    total: number;
    formatted_number: string;
    amount: number;
    due_date: string;
    status: string;
    is_paid: boolean;
    is_overdue: boolean;
    days_overdue: number;
    overdue_level: 'current' | 'warning' | 'danger' | 'critical';
    paid_at: string | null;
    payment_method: string | null;
    receipt_number: string | null;
}

export interface Debtor {
    student_id: number;
    student_name: string;
    grade: string;
    section: string;
    total_overdue: number;
    days_overdue: number;
    level: 'warning' | 'danger' | 'critical';
    installments: {
        number: string;
        amount: number;
        due_date: string;
        days_overdue: number;
        level: string;
    }[];
    installments_count: number;
}

export interface DebtorReport {
    summary: {
        total_debtors: number;
        total_amount: number;
        critical_count: number;
    };
    debtors: Debtor[];
    generated_at: string;
}

export interface DailyClosure {
    date: string;
    total_collected: number;
    payment_count: number;
    by_method: Record<string, { count: number; total: number }>;
    payments: {
        student_name: string;
        amount: number;
        method: string;
        receipt: string;
        paid_at: string;
        installment: string;
    }[];
}

export interface CreatePaymentPlanRequest {
    enrollment_id: number;
    total_amount: number;
    installments: number;
    day_of_month?: number;
    currency?: string;
    metadata?: Record<string, unknown>;
}

export interface RecordPaymentRequest {
    payment_method: string;
    receipt_number?: string;
    metadata?: Record<string, unknown>;
}

class PaymentService {
    private baseUrl = '/api/payments';

    async createPaymentPlan(data: CreatePaymentPlanRequest): Promise<PaymentPlan> {
        const response = await api.post<{ success: boolean; data: PaymentPlan }>(
            `${this.baseUrl}/plans`,
            data
        );
        return response.data.data;
    }

    async recordPayment(installmentId: string, data: RecordPaymentRequest): Promise<{ receipt_number: string; paid_at: string }> {
        const response = await api.post<{ success: boolean; data: { receipt_number: string; paid_at: string } }>(
            `${this.baseUrl}/installments/${installmentId}/pay`,
            data
        );
        return response.data.data;
    }

    async getDebtors(filters?: {
        grade_id?: number;
        level?: string;
        min_days?: number;
    }): Promise<DebtorReport> {
        const params = new URLSearchParams();
        if (filters?.grade_id) params.append('grade_id', filters.grade_id.toString());
        if (filters?.level) params.append('level', filters.level);
        if (filters?.min_days) params.append('min_days', filters.min_days.toString());

        const response = await api.get<{ success: boolean; data: DebtorReport }>(
            `${this.baseUrl}/debtors?${params.toString()}`
        );
        return response.data.data;
    }

    async getDailyClosure(date?: string): Promise<DailyClosure> {
        const params = date ? `?date=${date}` : '';
        const response = await api.get<{ success: boolean; data: DailyClosure }>(
            `${this.baseUrl}/daily-closure${params}`
        );
        return response.data.data;
    }

    async getPaymentHistory(studentId: number): Promise<PaymentPlan[]> {
        const response = await api.get<{ success: boolean; data: { payments: PaymentPlan[] } }>(
            `${this.baseUrl}/history/${studentId}`
        );
        return response.data.data.payments;
    }

    // Helper methods
    getOverdueLevelColor(level: string): string {
        switch (level) {
            case 'warning': return 'text-yellow-600 bg-yellow-100';
            case 'danger': return 'text-orange-600 bg-orange-100';
            case 'critical': return 'text-red-600 bg-red-100';
            default: return 'text-green-600 bg-green-100';
        }
    }

    formatCurrency(amount: number, currency = 'GTQ'): string {
        return new Intl.NumberFormat('es-GT', {
            style: 'currency',
            currency,
        }).format(amount);
    }
}

export const paymentService = new PaymentService();
