import { api } from './api';

export interface Contract {
    id: number;
    contract_number: string;
    resolution_number: string | null;
    status: string;
    total_amount: number;
    installments: number;
    student_name: string;
    grade: string;
    has_generated_pdf: boolean;
    has_signed_pdf: boolean;
    is_signed: boolean;
    signature_metadata: any;
    created_at: string;
}

export interface GenerateContractRequest {
    enrollment_id: number;
    parent_id?: number;
    template_name?: string;
    custom_data?: {
        total_amount?: number;
        installments?: number;
        [key: string]: any;
    };
}

export interface SignContractRequest {
    signer_name: string;
    signer_email: string;
    signature_image?: string; // base64
    metadata?: Record<string, any>;
}

class ContractService {
    private baseUrl = '/api/contracts';

    async generateContract(data: GenerateContractRequest): Promise<{
        contract_id: number;
        contract_number: string;
        pdf_filename: string;
        status: string;
    }> {
        const response = await api.post<{ success: boolean; data: any }>(
            `${this.baseUrl}/generate`,
            data
        );
        return response.data.data;
    }

    async signContract(contractId: number, data: SignContractRequest): Promise<void> {
        await api.post(`${this.baseUrl}/${contractId}/sign`, data);
    }

    async getContract(contractId: number): Promise<Contract> {
        const response = await api.get<{ success: boolean; data: Contract }>(
            `${this.baseUrl}/${contractId}`
        );
        return response.data.data;
    }

    async getContractsByEnrollment(enrollmentId: number): Promise<Contract[]> {
        const response = await api.get<{ success: boolean; data: Contract[] }>(
            `${this.baseUrl}/enrollment/${enrollmentId}`
        );
        return response.data.data;
    }

    async downloadContract(contractId: number): Promise<void> {
        const response = await api.get(`${this.baseUrl}/${contractId}/download`, {
            responseType: 'blob',
        });

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `contrato_${contractId}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    }

    getStatusBadge(status: string): { label: string; color: string } {
        const statuses: Record<string, { label: string; color: string }> = {
            pending: { label: 'Pendiente', color: 'bg-yellow-100 text-yellow-800' },
            signed: { label: 'Firmado', color: 'bg-green-100 text-green-800' },
            active: { label: 'Activo', color: 'bg-blue-100 text-blue-800' },
            cancelled: { label: 'Cancelado', color: 'bg-red-100 text-red-800' },
        };

        return statuses[status] || { label: status, color: 'bg-gray-100 text-gray-800' };
    }

    formatCurrency(amount: number): string {
        return new Intl.NumberFormat('es-GT', {
            style: 'currency',
            currency: 'GTQ',
        }).format(amount);
    }
}

export const contractService = new ContractService();
