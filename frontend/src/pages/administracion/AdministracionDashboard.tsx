import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { StatCard } from "@/components/StatCard";
import { DollarSign, Users, TrendingUp, FileText } from "lucide-react";
import { Card } from "@/components/ui/card";
import { useAuth } from "@/context/AuthContext";
import { administracionService } from "@/services/administracion.service";
import { errorHandler } from "@/lib/errorHandler";
import { Loader2 } from 'lucide-react';
import type { FinancialSummary, StudentStatistics } from '@/types/modules.types';

export const AdministracionDashboard = () => {
    const { user } = useAuth();
    const [loading, setLoading] = useState(true);
    const [financial, setFinancial] = useState<FinancialSummary | null>(null);
    const [studentStats, setStudentStats] = useState<StudentStatistics | null>(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [financialData, statsData] = await Promise.all([
                    administracionService.getFinancialSummary(),
                    administracionService.getStudentStatistics(),
                ]);
                setFinancial(financialData);
                setStudentStats(statsData);
            } catch (error) {
                errorHandler.handleApiError(error, 'Error al cargar datos');
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    if (loading) {
        return (
            <div className="flex min-h-screen items-center justify-center">
                <Loader2 className="h-8 w-8 animate-spin text-primary" />
            </div>
        );
    }

    return (
        <div className="flex min-h-screen bg-background">
            <Sidebar />

            <main className="flex-1 ml-64 p-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold mb-1">Panel de Administración</h1>
                    <p className="text-muted-foreground">Bienvenido, {user?.firstName} {user?.lastName}</p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <StatCard
                        icon={DollarSign}
                        title="Balance"
                        value={`Q${financial?.balance.toFixed(2) || '0.00'}`}
                        subtitle="Ingresos - Egresos"
                        variant={financial && financial.balance > 0 ? "success" : "warning"}
                    />
                    <StatCard
                        icon={TrendingUp}
                        title="Ingresos Totales"
                        value={`Q${financial?.totalIncome.toFixed(2) || '0.00'}`}
                        subtitle="Este mes"
                        variant="success"
                    />
                    <StatCard
                        icon={Users}
                        title="Estudiantes Activos"
                        value={studentStats?.activeStudents.toString() || '0'}
                        subtitle={`Total: ${studentStats?.totalStudents || 0}`}
                        variant="primary"
                    />
                    <StatCard
                        icon={FileText}
                        title="Egresos"
                        value={`Q${financial?.totalExpenses.toFixed(2) || '0.00'}`}
                        subtitle="Este mes"
                        variant="warning"
                    />
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <Card className="p-6">
                        <h3 className="text-lg font-semibold mb-4">Estudiantes por Grado</h3>
                        <div className="space-y-3">
                            {studentStats?.byGrade.map((grade) => (
                                <div key={grade.gradeName} className="flex justify-between items-center">
                                    <span className="text-sm font-medium">{grade.gradeName}</span>
                                    <div className="flex items-center gap-2">
                                        <div className="h-2 w-32 bg-secondary rounded-full overflow-hidden">
                                            <div
                                                className="h-full bg-primary"
                                                style={{ width: `${(grade.count / (studentStats?.totalStudents || 1)) * 100}%` }}
                                            />
                                        </div>
                                        <span className="text-sm text-muted-foreground w-8 text-right">{grade.count}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </Card>

                    <Card className="p-6">
                        <h3 className="text-lg font-semibold mb-4">Resumen Financiero</h3>
                        <div className="space-y-4">
                            <div className="flex justify-between items-center p-3 bg-success/10 rounded">
                                <span className="text-sm font-medium">Ingresos Mensuales</span>
                                <span className="text-lg font-bold text-success">Q{financial?.monthlyIncome[0]?.toFixed(2) || '0.00'}</span>
                            </div>
                            <div className="flex justify-between items-center p-3 bg-destructive/10 rounded">
                                <span className="text-sm font-medium">Egresos Mensuales</span>
                                <span className="text-lg font-bold text-destructive">Q{financial?.monthlyExpenses[0]?.toFixed(2) || '0.00'}</span>
                            </div>
                            <div className="flex justify-between items-center p-3 bg-primary/10 rounded">
                                <span className="text-sm font-medium">Balance Neto</span>
                                <span className="text-lg font-bold text-primary">Q{financial?.balance.toFixed(2) || '0.00'}</span>
                            </div>
                        </div>
                    </Card>
                </div>

                <Card className="p-6">
                    <h3 className="text-lg font-semibold mb-4">Tendencia de Inscripciones</h3>
                    <div className="h-64 flex items-center justify-center text-muted-foreground">
                        Gráfica de tendencias (implementar con Recharts)
                    </div>
                </Card>
            </main>
        </div>
    );
};
