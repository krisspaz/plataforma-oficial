import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { StatCard } from "@/components/StatCard";
import { Users, CreditCard, GraduationCap, AlertCircle, Bell } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/context/AuthContext";
import { api } from "@/services/api";
import { Loader2 } from 'lucide-react';
import { AIDashboard } from "@/components/AIDashboard";

interface DashboardStats {
    enrollments: {
        total: number;
        by_grade: Record<string, number>;
    };
    active_enrollments: number;
    payments: {
        pending: number;
        overdue: number;
        daily_total: number;
    };
}

export const AdminDashboard = () => {
    const { user } = useAuth();
    const [stats, setStats] = useState<DashboardStats | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const data = await api.get<DashboardStats>('/dashboard/stats');
                setStats(data);
            } catch (error) {
                console.error('Failed to fetch stats', error);
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
                    <div className="flex items-center justify-between mb-2">
                        <div>
                            <h1 className="text-3xl font-bold text-foreground mb-1">
                                Panel de Administración
                            </h1>
                            <p className="text-muted-foreground">
                                Bienvenido, {user?.firstName} {user?.lastName}
                            </p>
                        </div>
                        <Button variant="outline" size="icon" className="relative">
                            <Bell className="w-5 h-5" />
                        </Button>
                    </div>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <StatCard
                        icon={Users}
                        title="Estudiantes Activos"
                        value={stats?.active_enrollments.toString() || '0'}
                        subtitle="Inscritos este ciclo"
                        variant="primary"
                    />
                    <StatCard
                        icon={CreditCard}
                        title="Cobros del Día"
                        value={`Q${stats?.payments.daily_total.toFixed(2) || '0.00'}`}
                        subtitle="Total recaudado hoy"
                        variant="success"
                    />
                    <StatCard
                        icon={AlertCircle}
                        title="Pagos Pendientes"
                        value={stats?.payments.pending.toString() || '0'}
                        subtitle="Necesitan seguimiento"
                        variant="warning"
                    />
                    <StatCard
                        icon={GraduationCap}
                        title="Total Inscripciones"
                        value={stats?.enrollments.total.toString() || '0'}
                        subtitle="Ciclo actual"
                        variant="accent"
                    />
                </div>

                {/* AI Analysis Section */}
                <div className="mb-8">
                    <h2 className="text-xl font-bold mb-4">Análisis Predictivo IA</h2>
                    <AIDashboard />
                </div>

                {/* Enrollment by Grade */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div className="bg-card rounded-lg border p-6">
                        <h3 className="text-lg font-semibold mb-4">Inscripciones por Grado</h3>
                        <div className="space-y-4">
                            {stats?.enrollments.by_grade && Object.entries(stats.enrollments.by_grade).map(([grade, count]) => (
                                <div key={grade} className="flex items-center justify-between">
                                    <span className="text-sm font-medium">{grade}</span>
                                    <div className="flex items-center gap-2">
                                        <div className="h-2 w-32 bg-secondary rounded-full overflow-hidden">
                                            <div
                                                className="h-full bg-primary"
                                                style={{ width: `${(count / stats.enrollments.total) * 100}%` }}
                                            />
                                        </div>
                                        <span className="text-sm text-muted-foreground w-8 text-right">{count}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
};
