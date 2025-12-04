import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { secretariaService } from "@/services/secretaria.service";
import { errorHandler } from "@/lib/errorHandler";
import { Loader2, Search, Download } from 'lucide-react';
import type { Debtor } from '@/types/modules.types';

export const ReporteDeudores = () => {
    const [debtors, setDebtors] = useState<Debtor[]>([]);
    const [filteredDebtors, setFilteredDebtors] = useState<Debtor[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        const fetchDebtors = async () => {
            try {
                const data = await secretariaService.getDebtors();
                setDebtors(data);
                setFilteredDebtors(data);
            } catch (error) {
                errorHandler.handleApiError(error, 'Error al cargar deudores');
            } finally {
                setLoading(false);
            }
        };

        fetchDebtors();
    }, []);

    useEffect(() => {
        const filtered = debtors.filter(debtor =>
            debtor.studentName.toLowerCase().includes(searchTerm.toLowerCase()) ||
            debtor.grade.toLowerCase().includes(searchTerm.toLowerCase())
        );
        setFilteredDebtors(filtered);
    }, [searchTerm, debtors]);

    const totalDebt = filteredDebtors.reduce((sum, debtor) => sum + debtor.totalDebt, 0);

    return (
        <div className="flex min-h-screen bg-background">
            <Sidebar />

            <main className="flex-1 ml-64 p-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold mb-2">Reporte de Deudores</h1>
                    <p className="text-muted-foreground">Lista de estudiantes con saldo pendiente</p>
                </div>

                <Card className="p-6 mb-6">
                    <div className="flex justify-between items-center mb-4">
                        <div className="relative flex-1 max-w-md">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
                            <Input
                                placeholder="Buscar por nombre o grado..."
                                className="pl-10"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                        </div>
                        <Button>
                            <Download className="w-4 h-4 mr-2" />
                            Exportar PDF
                        </Button>
                    </div>

                    <div className="grid grid-cols-3 gap-4 mb-6">
                        <div className="bg-muted/50 p-4 rounded-lg">
                            <p className="text-sm text-muted-foreground">Total Deudores</p>
                            <p className="text-2xl font-bold">{filteredDebtors.length}</p>
                        </div>
                        <div className="bg-destructive/10 p-4 rounded-lg">
                            <p className="text-sm text-muted-foreground">Deuda Total</p>
                            <p className="text-2xl font-bold text-destructive">Q{totalDebt.toFixed(2)}</p>
                        </div>
                        <div className="bg-warning/10 p-4 rounded-lg">
                            <p className="text-sm text-muted-foreground">Deuda Vencida</p>
                            <p className="text-2xl font-bold text-warning">
                                Q{filteredDebtors.reduce((sum, d) => sum + d.overdueAmount, 0).toFixed(2)}
                            </p>
                        </div>
                    </div>

                    {loading ? (
                        <div className="flex justify-center py-12">
                            <Loader2 className="h-8 w-8 animate-spin text-primary" />
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="text-left p-3">Estudiante</th>
                                        <th className="text-left p-3">Grado</th>
                                        <th className="text-right p-3">Deuda Total</th>
                                        <th className="text-right p-3">Deuda Vencida</th>
                                        <th className="text-left p-3">Último Pago</th>
                                        <th className="text-center p-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {filteredDebtors.map((debtor) => (
                                        <tr key={debtor.studentId} className="border-b hover:bg-muted/50">
                                            <td className="p-3">{debtor.studentName}</td>
                                            <td className="p-3">{debtor.grade}</td>
                                            <td className="p-3 text-right font-semibold">Q{debtor.totalDebt.toFixed(2)}</td>
                                            <td className="p-3 text-right text-destructive font-semibold">
                                                Q{debtor.overdueAmount.toFixed(2)}
                                            </td>
                                            <td className="p-3">{new Date(debtor.lastPaymentDate).toLocaleDateString()}</td>
                                            <td className="p-3 text-center">
                                                <Button size="sm" variant="outline">Ver Detalle</Button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </Card>
            </main>
        </div>
    );
};
