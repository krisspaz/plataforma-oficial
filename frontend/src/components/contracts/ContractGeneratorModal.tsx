import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Loader2, FileText, CheckCircle } from 'lucide-react';
import { contractService, GenerateContractRequest } from '@/services/contract.service';
import { toast } from 'sonner';

interface ContractGeneratorModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSuccess: () => void;
    enrollment: {
        id: number;
        studentName: string;
        grade: string;
        totalAmount: number;
    };
}

export function ContractGeneratorModal({
    isOpen,
    onClose,
    onSuccess,
    enrollment
}: ContractGeneratorModalProps) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { register, handleSubmit, formState: { errors } } = useForm({
        defaultValues: {
            installments: 10,
            totalAmount: enrollment.totalAmount
        }
    });

    const onSubmit = async (data: any) => {
        setIsSubmitting(true);
        try {
            const request: GenerateContractRequest = {
                enrollment_id: enrollment.id,
                custom_data: {
                    total_amount: parseFloat(data.totalAmount),
                    installments: parseInt(data.installments)
                }
            };

            await contractService.generateContract(request);
            toast.success('Contrato generado exitosamente');
            onSuccess();
            onClose();
        } catch (error) {
            toast.error('Error al generar el contrato');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <FileText className="w-5 h-5" />
                        Generar Contrato
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
                    <div className="bg-slate-50 p-4 rounded-lg space-y-2">
                        <div>
                            <span className="text-xs text-slate-500 uppercase">Estudiante</span>
                            <p className="font-medium">{enrollment.studentName}</p>
                        </div>
                        <div>
                            <span className="text-xs text-slate-500 uppercase">Grado</span>
                            <p className="font-medium">{enrollment.grade}</p>
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="totalAmount">Monto Total del Contrato (Q)</Label>
                        <Input
                            id="totalAmount"
                            type="number"
                            step="0.01"
                            {...register('totalAmount', { required: true, min: 0 })}
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="installments">Número de Cuotas</Label>
                        <Input
                            id="installments"
                            type="number"
                            min="1"
                            max="12"
                            {...register('installments', { required: true, min: 1, max: 12 })}
                        />
                        <p className="text-xs text-slate-500">
                            El monto se dividirá en el número de cuotas especificado.
                        </p>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={isSubmitting}>
                            {isSubmitting && <Loader2 className="w-4 h-4 mr-2 animate-spin" />}
                            Generar Documento
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
