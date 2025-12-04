import { api } from './api';

export interface RiskFactor {
    factor: string;
    score: number;
    weight: number;
    contribution: number;
}

export interface AIRiskScore {
    id: number;
    riskLevel: 'low' | 'medium' | 'high' | 'critical';
    riskPercentage: number;
    factors: RiskFactor[];
    predictions: string[];
    calculatedAt: string;
    student?: {
        id: number;
        firstName: string;
        lastName: string;
    };
}

export interface BatchRiskResponse {
    results: AIRiskScore[];
    processed: number;
    failed: number;
}

export const aiService = {
    getStudentRisk: async (studentId: number): Promise<AIRiskScore> => {
        return api.get<AIRiskScore>(`/ai/risk/student/${studentId}`);
    },

    getHighRiskStudents: async (): Promise<{ students: AIRiskScore[], count: number }> => {
        return api.get<{ students: AIRiskScore[], count: number }>('/ai/risk/high-risk');
    },

    calculateBatchRisk: async (studentIds: number[]): Promise<BatchRiskResponse> => {
        return api.post<BatchRiskResponse>('/ai/risk/batch-calculate', { studentIds });
    }
};
