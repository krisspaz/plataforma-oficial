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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Loader2, Calculator, Calendar, CreditCard } from 'lucide-react';
import { paymentService, CreatePaymentPlanRequest } from '@/services/payment.service';
import { toast } from 'sonner';

interface PaymentPlanFormProps {
    isOpen: boolean;
    onClose: () => void;
    onSuccess: () => void;
    enrollment: {
        id: number;
        studentName: string;
        grade: string;
    };
}

interface FormData {
    totalAmount: string;
    installments: string;
    dayOfMonth: string;
}

export function PaymentPlanForm({ isOpen, onClose, onSuccess, enrollment }: PaymentPlanFormProps) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [preview, setPreview] = useState<{
        monthlyAmount: number;
        dates: string[];
    } | null>(null);

    const { register, handleSubmit, watch, formState: { errors }, reset } = useForm<FormData>({
        defaultValues: {
            totalAmount: '',
            installments: '1',
            dayOfMonth: '5',
        }
    });

    const watchedAmount = watch('totalAmount');
    const watchedInstallments = watch('installments');

    // Calculate preview when values change
    React.useEffect(() => {
        const amount = parseFloat(watchedAmount);
        const installments = parseInt(watchedInstallments);

        if (!isNaN(amount) && amount > 0 && !isNaN(installments) && installments > 0) {
            const monthlyAmount = amount / installments;

            // Generate dates
            const dates: string[] = [];
            const startDate = new Date();
            startDate.setMonth(startDate.getMonth() + 1);
            startDate.setDate(parseInt(watch('dayOfMonth')) || 5);

            for (let i = 0; i < installments; i++) {
                const date = new Date(startDate);
                date.setMonth(date.getMonth() + i);
                dates.push(date.toLocaleDateString('es-GT', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }));
            }

            setPreview({ monthlyAmount, dates });
        } else {
            setPreview(null);
        }
    }, [watchedAmount, watchedInstallments, watch]);

    const onSubmit = async (data: FormData) => {
        setIsSubmitting(true);

        try {
            const request: CreatePaymentPlanRequest = {
                enrollment_id: enrollment.id,
                total_amount: parseFloat(data.totalAmount),
                installments: parseInt(data.installments),
                day_of_month: parseInt(data.dayOfMonth),
            };

            await paymentService.createPaymentPlan(request);

            toast.success('Plan de pagos creado exitosamente');
            reset();
            onSuccess();
            onClose();
        } catch (error: any) {
            toast.error(error.response?.data?.error || 'Error al crear plan de pagos');
        } finally {
            setIsSubmitting(false);
        }
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('es-GT', {
            style: 'currency',
            currency: 'GTQ',
        }).format(amount);
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-2xl">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <CreditCard className="w-5 h-5" />
                        Crear Plan de Pagos
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
                    {/* Student Info */}
                    <Card className="bg-slate-50">
                        <CardContent className="pt-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <span className="text-sm text-slate-500">Estudiante</span>
                                    <p className="font-medium">{enrollment.studentName}</p>
                                </div>
                                <div>
                                    <span className="text-sm text-slate-500">Grado</span>
                                    <p className="font-medium">{enrollment.grade}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Form Fields */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="totalAmount">Monto Total (Q)</Label>
                            <div className="relative">
                                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">Q</span>
                                <Input
                                    id="totalAmount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    className="pl-8"
                                    placeholder="0.00"
                                    {...register('totalAmount', {
                                        required: 'El monto es requerido',
                                        min: { value: 50, message: 'Mínimo Q50.00' }
                                    })}
                                />
                            </div>
                            {errors.totalAmount && (
                                <p className="text-sm text-red-500">{errors.totalAmount.message}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="installments">Número de Cuotas</Label>
                            <Select
                                defaultValue="1"
                                onValueChange={(value) => {
                                    const event = { target: { value, name: 'installments' } };
                                    register('installments').onChange(event);
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1">1 (Contado)</SelectItem>
                                    <SelectItem value="2">2 cuotas</SelectItem>
                                    <SelectItem value="3">3 cuotas</SelectItem>
                                    <SelectItem value="4">4 cuotas</SelectItem>
                                    <SelectItem value="6">6 cuotas</SelectItem>
                                    <SelectItem value="10">10 cuotas</SelectItem>
                                    <SelectItem value="12">12 cuotas</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="dayOfMonth">Día de Pago</Label>
                            <Select
                                defaultValue="5"
                                onValueChange={(value) => {
                                    const event = { target: { value, name: 'dayOfMonth' } };
                                    register('dayOfMonth').onChange(event);
                                }}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Día" />
                                </SelectTrigger>
                                <SelectContent>
                                    {[1, 5, 10, 15, 20, 25].map(day => (
                                        <SelectItem key={day} value={day.toString()}>
                                            Día {day}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    {/* Preview */}
                    {preview && (
                        <Card className="border-blue-200 bg-blue-50">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm flex items-center gap-2">
                                    <Calculator className="w-4 h-4" />
                                    Vista Previa del Plan
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex justify-between items-center">
                                    <span className="text-slate-600">Cuota Mensual:</span>
                                    <Badge variant="secondary" className="text-lg">
                                        {formatCurrency(preview.monthlyAmount)}
                                    </Badge>
                                </div>

                                <div className="space-y-2">
                                    <span className="text-sm text-slate-600 flex items-center gap-2">
                                        <Calendar className="w-4 h-4" />
                                        Fechas de Vencimiento:
                                    </span>
                                    <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
                                        {preview.dates.slice(0, 6).map((date, index) => (
                                            <div
                                                key={index}
                                                className="text-xs bg-white rounded px-2 py-1 text-center"
                                            >
                                                <span className="font-medium">Cuota {index + 1}:</span> {date}
                                            </div>
                                        ))}
                                        {preview.dates.length > 6 && (
                                            <div className="text-xs text-slate-500 flex items-center justify-center">
                                                +{preview.dates.length - 6} más...
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={isSubmitting}>
                            {isSubmitting && <Loader2 className="w-4 h-4 mr-2 animate-spin" />}
                            Crear Plan de Pagos
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default PaymentPlanForm;
