import { useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useNavigate } from 'react-router-dom';
import { secretariaService } from "@/services/secretaria.service";
import { errorHandler } from "@/lib/errorHandler";
import { toast } from 'sonner';
import { Loader2, ArrowLeft } from 'lucide-react';

export const NuevoPago = () => {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [formData, setFormData] = useState({
        studentId: '',
        amount: '',
        paymentType: 'CONTADO' as 'CONTADO' | 'CREDITO',
        paymentMethod: 'EFECTIVO' as 'EFECTIVO' | 'TARJETA' | 'TRANSFERENCIA',
        description: '',
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);

        try {
            await secretariaService.createPayment({
                studentId: parseInt(formData.studentId),
                amount: parseFloat(formData.amount),
                paymentType: formData.paymentType,
                paymentMethod: formData.paymentMethod,
                description: formData.description,
                date: new Date().toISOString(),
                receiptNumber: `REC-${Date.now()}`,
            });

            toast.success('Pago registrado exitosamente');
            navigate('/secretaria');
        } catch (error) {
            errorHandler.handleApiError(error, 'Error al registrar el pago');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex min-h-screen bg-background">
            <Sidebar />

            <main className="flex-1 ml-64 p-8">
                <div className="max-w-2xl mx-auto">
                    <Button variant="ghost" onClick={() => navigate('/secretaria')} className="mb-4">
                        <ArrowLeft className="w-4 h-4 mr-2" />
                        Volver
                    </Button>

                    <Card className="p-6">
                        <h1 className="text-2xl font-bold mb-6">Registrar Nuevo Pago</h1>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="studentId">ID del Estudiante</Label>
                                <Input
                                    id="studentId"
                                    type="number"
                                    value={formData.studentId}
                                    onChange={(e) => setFormData({ ...formData, studentId: e.target.value })}
                                    required
                                />
                            </div>

                            <div>
                                <Label htmlFor="amount">Monto (Q)</Label>
                                <Input
                                    id="amount"
                                    type="number"
                                    step="0.01"
                                    value={formData.amount}
                                    onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
                                    required
                                />
                            </div>

                            <div>
                                <Label htmlFor="paymentType">Tipo de Pago</Label>
                                <Select value={formData.paymentType} onValueChange={(value: 'CONTADO' | 'CREDITO') => setFormData({ ...formData, paymentType: value })}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="CONTADO">Contado</SelectItem>
                                        <SelectItem value="CREDITO">Crédito</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label htmlFor="paymentMethod">Método de Pago</Label>
                                <Select value={formData.paymentMethod} onValueChange={(value: 'EFECTIVO' | 'TARJETA' | 'TRANSFERENCIA') => setFormData({ ...formData, paymentMethod: value })}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="EFECTIVO">Efectivo</SelectItem>
                                        <SelectItem value="TARJETA">Tarjeta</SelectItem>
                                        <SelectItem value="TRANSFERENCIA">Transferencia</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label htmlFor="description">Descripción</Label>
                                <Input
                                    id="description"
                                    value={formData.description}
                                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                    placeholder="Concepto del pago"
                                />
                            </div>

                            <div className="flex gap-2 pt-4">
                                <Button type="submit" disabled={loading} className="flex-1">
                                    {loading ? (
                                        <>
                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                            Procesando...
                                        </>
                                    ) : (
                                        'Registrar Pago'
                                    )}
                                </Button>
                                <Button type="button" variant="outline" onClick={() => navigate('/secretaria')}>
                                    Cancelar
                                </Button>
                            </div>
                        </form>
                    </Card>
                </div>
            </main>
        </div>
    );
};
