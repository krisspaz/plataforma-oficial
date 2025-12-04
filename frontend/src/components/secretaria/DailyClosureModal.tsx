import React, { useState, useEffect } from 'react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import {
    Loader2,
    Calendar,
    TrendingUp,
    Download,
    Printer,
    CreditCard,
    Banknote,
    ArrowRightLeft
} from 'lucide-react';
import { paymentService, DailyClosure } from '@/services/payment.service';
import { toast } from 'sonner';

interface DailyClosureModalProps {
    isOpen: boolean;
    onClose: () => void;
    date?: string; // YYYY-MM-DD format
}

const methodIcons: Record<string, React.ReactNode> = {
    cash: <Banknote className="w-4 h-4" />,
    card: <CreditCard className="w-4 h-4" />,
    transfer: <ArrowRightLeft className="w-4 h-4" />,
};

export function DailyClosureModal({ isOpen, onClose, date }: DailyClosureModalProps) {
    const [isLoading, setIsLoading] = useState(true);
    const [closure, setClosure] = useState<DailyClosure | null>(null);

    useEffect(() => {
        if (isOpen) {
            loadClosure();
        }
    }, [isOpen, date]);

    const loadClosure = async () => {
        setIsLoading(true);
        try {
            const data = await paymentService.getDailyClosure(date);
            setClosure(data);
        } catch (error) {
            toast.error('Error al cargar el corte del día');
        } finally {
            setIsLoading(false);
        }
    };

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('es-GT', {
            style: 'currency',
            currency: 'GTQ',
        }).format(amount);
    };

    const handlePrint = () => {
        window.print();
    };

    const handleExport = () => {
        // Export to PDF or Excel
        toast.info('Exportando reporte...');
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center justify-between">
                        <span className="flex items-center gap-2">
                            <Calendar className="w-5 h-5" />
                            Corte del Día
                        </span>
                        <div className="flex gap-2">
                            <Button variant="outline" size="sm" onClick={handlePrint}>
                                <Printer className="w-4 h-4 mr-2" />
                                Imprimir
                            </Button>
                            <Button variant="outline" size="sm" onClick={handleExport}>
                                <Download className="w-4 h-4 mr-2" />
                                Exportar
                            </Button>
                        </div>
                    </DialogTitle>
                </DialogHeader>

                {isLoading ? (
                    <div className="flex items-center justify-center py-12">
                        <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
                    </div>
                ) : closure ? (
                    <div className="space-y-6 print:space-y-4">
                        {/* Summary Cards */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <Card className="bg-gradient-to-br from-green-500 to-green-600 text-white">
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-sm font-medium opacity-90">
                                        Total Recaudado
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-3xl font-bold">
                                        {formatCurrency(closure.total_collected)}
                                    </p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-sm font-medium text-slate-600">
                                        Transacciones
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-3xl font-bold text-slate-900">
                                        {closure.payment_count}
                                    </p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-sm font-medium text-slate-600">
                                        Fecha
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-xl font-bold text-slate-900">
                                        {new Date(closure.date).toLocaleDateString('es-GT', {
                                            weekday: 'long',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric'
                                        })}
                                    </p>
                                </CardContent>
                            </Card>
                        </div>

                        <Separator />

                        {/* By Payment Method */}
                        <div>
                            <h3 className="text-lg font-semibold mb-4 flex items-center gap-2">
                                <TrendingUp className="w-5 h-5" />
                                Desglose por Método de Pago
                            </h3>
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                {Object.entries(closure.by_method).map(([method, data]) => (
                                    <Card key={method}>
                                        <CardContent className="pt-4">
                                            <div className="flex items-center gap-2 mb-2">
                                                {methodIcons[method] || <CreditCard className="w-4 h-4" />}
                                                <span className="font-medium capitalize">{method}</span>
                                            </div>
                                            <p className="text-2xl font-bold">{formatCurrency(data.total)}</p>
                                            <p className="text-sm text-slate-500">{data.count} transacciones</p>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        </div>

                        <Separator />

                        {/* Detailed Payments Table */}
                        <div>
                            <h3 className="text-lg font-semibold mb-4">Detalle de Pagos</h3>
                            <div className="rounded-lg border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Hora</TableHead>
                                            <TableHead>Estudiante</TableHead>
                                            <TableHead>Cuota</TableHead>
                                            <TableHead>Método</TableHead>
                                            <TableHead>Recibo</TableHead>
                                            <TableHead className="text-right">Monto</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {closure.payments.length === 0 ? (
                                            <TableRow>
                                                <TableCell colSpan={6} className="text-center py-8 text-slate-500">
                                                    No hay pagos registrados para esta fecha
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            closure.payments.map((payment, index) => (
                                                <TableRow key={index}>
                                                    <TableCell className="font-mono text-sm">
                                                        {payment.paid_at}
                                                    </TableCell>
                                                    <TableCell className="font-medium">
                                                        {payment.student_name}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge variant="outline">{payment.installment}</Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div className="flex items-center gap-2">
                                                            {methodIcons[payment.method] || <CreditCard className="w-4 h-4" />}
                                                            <span className="capitalize">{payment.method}</span>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell className="font-mono text-sm">
                                                        {payment.receipt}
                                                    </TableCell>
                                                    <TableCell className="text-right font-semibold">
                                                        {formatCurrency(payment.amount)}
                                                    </TableCell>
                                                </TableRow>
                                            ))
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        </div>

                        {/* Footer for print */}
                        <div className="hidden print:block pt-8 text-center text-sm text-slate-500">
                            <p>Generado el {new Date().toLocaleString('es-GT')}</p>
                            <p>Sistema de Gestión Escolar - KPixelCraft</p>
                        </div>
                    </div>
                ) : (
                    <div className="text-center py-12 text-slate-500">
                        No se encontró información del corte
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}

export default DailyClosureModal;
