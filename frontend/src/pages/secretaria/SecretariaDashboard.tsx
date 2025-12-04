import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { StatCard } from "@/components/StatCard";
import { DollarSign, Users, FileText, AlertCircle, TrendingUp } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { useAuth } from "@/context/AuthContext";
import { secretariaService } from "@/services/secretaria.service";
import { errorHandler } from "@/lib/errorHandler";
import { Loader2 } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

interface DashboardStats {
    totalPaymentsToday: number;
    totalAmountToday: number;
    totalDebtors: number;
    pendingEnrollments: number;
    pendingContracts: number;
}

export const SecretariaDashboard = () => {
    const { user } = useAuth();
    const navigate = useNavigate();
    const [stats, setStats] = useState<DashboardStats | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const today = new Date().toISOString().split('T')[0];
                const [dailyReport, debtors] = await Promise.all([
                    secretariaService.getDailyReport(today),
                    secretariaService.getDebtors(),
                ]);

                setStats({
                    totalPaymentsToday: dailyReport.totalPayments,
                    totalAmountToday: dailyReport.totalAmount,
                    totalDebtors: debtors.length,
                    pendingEnrollments: 0, // TODO: Implement
                    pendingContracts: 0, // TODO: Implement
                });
            } catch (error) {
                errorHandler.handleApiError(error, 'No se pudieron cargar las estadísticas');
            } finally {
                setLoading(false);
            }
        };

        fetchStats();
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
                {/* Header */}
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-foreground mb-1">
                        Secretaría
                    </h1>
                    <p className="text-muted-foreground">
                        Bienvenido, {user?.firstName} {user?.lastName}
                    </p>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <StatCard
                        icon={DollarSign}
                        title="Cobros del Día"
                        value={`Q${stats?.totalAmountToday.toFixed(2) || '0.00'}`}
                        subtitle={`${stats?.totalPaymentsToday || 0} pagos`}
                        variant="success"
                    />
                    <StatCard
                        icon={AlertCircle}
                        title="Deudores"
                        value={stats?.totalDebtors.toString() || '0'}
                        subtitle="Requieren seguimiento"
                        variant="warning"
                    />
                    <StatCard
                        icon={Users}
                        title="Inscripciones Pendientes"
                        value={stats?.pendingEnrollments.toString() || '0'}
                        subtitle="Por procesar"
                        variant="primary"
                    />
                    <StatCard
                        icon={FileText}
                        title="Contratos Pendientes"
                        value={stats?.pendingContracts.toString() || '0'}
                        subtitle="Por firmar"
                        variant="accent"
                    />
                </div>

                {/* Quick Actions */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <Card className="p-6 hover:shadow-lg transition-shadow cursor-pointer" onClick={() => navigate('/secretaria/pagos/nuevo')}>
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center">
                                <DollarSign className="w-6 h-6 text-success" />
                            </div>
                            <div>
                                <h3 className="font-semibold">Registrar Pago</h3>
                                <p className="text-sm text-muted-foreground">Nuevo pago de estudiante</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6 hover:shadow-lg transition-shadow cursor-pointer" onClick={() => navigate('/secretaria/inscripciones/nueva')}>
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                <Users className="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <h3 className="font-semibold">Nueva Inscripción</h3>
                                <p className="text-sm text-muted-foreground">Inscribir nuevo estudiante</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6 hover:shadow-lg transition-shadow cursor-pointer" onClick={() => navigate('/secretaria/contratos/generar')}>
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center">
                                <FileText className="w-6 h-6 text-accent" />
                            </div>
                            <div>
                                <h3 className="font-semibold">Generar Contrato</h3>
                                <p className="text-sm text-muted-foreground">Crear contrato PDF</p>
                            </div>
                        </div>
                    </Card>
                </div>

                {/* Reports Section */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card className="p-6">
                        <h3 className="text-lg font-semibold mb-4">Acciones Rápidas</h3>
                        <div className="space-y-2">
                            <Button variant="outline" className="w-full justify-start" onClick={() => navigate('/secretaria/pagos/deudores')}>
                                <AlertCircle className="w-4 h-4 mr-2" />
                                Ver Reporte de Deudores
                            </Button>
                            <Button variant="outline" className="w-full justify-start" onClick={() => navigate('/secretaria/reportes/corte-dia')}>
                                <TrendingUp className="w-4 h-4 mr-2" />
                                Corte del Día
                            </Button>
                            <Button variant="outline" className="w-full justify-start" onClick={() => navigate('/secretaria/inscripciones/matricular')}>
                                <Users className="w-4 h-4 mr-2" />
                                Matricular Estudiante
                            </Button>
                        </div>
                    </Card>

                    <Card className="p-6">
                        <h3 className="text-lg font-semibold mb-4">Actividad Reciente</h3>
                        <div className="space-y-3 text-sm">
                            <div className="flex justify-between items-center p-2 bg-muted/50 rounded">
                                <span>Último pago registrado</span>
                                <span className="text-muted-foreground">Hace 15 min</span>
                            </div>
                            <div className="flex justify-between items-center p-2 bg-muted/50 rounded">
                                <span>Contrato generado</span>
                                <span className="text-muted-foreground">Hace 1 hora</span>
                            </div>
                            <div className="flex justify-between items-center p-2 bg-muted/50 rounded">
                                <span>Nueva inscripción</span>
                                <span className="text-muted-foreground">Hace 2 horas</span>
                            </div>
                        </div>
                    </Card>
                </div>
            </main>
        </div>
    );
};
