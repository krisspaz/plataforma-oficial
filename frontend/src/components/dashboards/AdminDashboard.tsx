import React from 'react';
import { useAuth } from '../../hooks/useAuth';
import { StatCard } from '../StatCard';
import { CardSkeleton, TableSkeleton } from '../ui/LazyComponent';

interface DashboardStats {
    totalStudents: number;
    activeEnrollments: number;
    pendingPayments: number;
    totalTeachers: number;
}

interface AdminDashboardProps {
    stats?: DashboardStats;
    isLoading?: boolean;
}

/**
 * Admin Dashboard - Full access dashboard for administrators
 */
export const AdminDashboard: React.FC<AdminDashboardProps> = ({
    stats,
    isLoading = false,
}) => {
    if (isLoading) {
        return (
            <div className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {[...Array(4)].map((_, i) => (
                        <CardSkeleton key={i} />
                    ))}
                </div>
                <TableSkeleton rows={5} />
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <header className="flex justify-between items-center">
                <h1 className="text-2xl font-bold text-gray-900">Dashboard Administrativo</h1>
                <div className="text-sm text-gray-500">
                    {new Date().toLocaleDateString('es-GT', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                    })}
                </div>
            </header>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard
                    title="Estudiantes"
                    value={stats?.totalStudents ?? 0}
                    icon="👨‍🎓"
                    trend={{ value: 5, isPositive: true }}
                    color="blue"
                />
                <StatCard
                    title="Inscripciones Activas"
                    value={stats?.activeEnrollments ?? 0}
                    icon="📚"
                    color="green"
                />
                <StatCard
                    title="Pagos Pendientes"
                    value={stats?.pendingPayments ?? 0}
                    icon="💳"
                    trend={{ value: 12, isPositive: false }}
                    color="yellow"
                />
                <StatCard
                    title="Maestros"
                    value={stats?.totalTeachers ?? 0}
                    icon="👩‍🏫"
                    color="purple"
                />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <section className="bg-white rounded-lg shadow p-6">
                    <h2 className="text-lg font-semibold mb-4">Actividad Reciente</h2>
                    <div className="space-y-3">
                        {/* Recent activity placeholder */}
                        <p className="text-gray-500">No hay actividad reciente</p>
                    </div>
                </section>

                <section className="bg-white rounded-lg shadow p-6">
                    <h2 className="text-lg font-semibold mb-4">Alertas del Sistema</h2>
                    <div className="space-y-3">
                        {stats?.pendingPayments && stats.pendingPayments > 0 ? (
                            <div className="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p className="text-yellow-800">
                                    ⚠️ {stats.pendingPayments} pagos pendientes requieren atención
                                </p>
                            </div>
                        ) : (
                            <p className="text-gray-500">Sin alertas activas</p>
                        )}
                    </div>
                </section>
            </div>
        </div>
    );
};

export default AdminDashboard;
