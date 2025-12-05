import React from 'react';
import { StatCard } from '../StatCard';
import { CardSkeleton } from '../ui/LazyComponent';

interface ParentStats {
    children: Array<{
        id: number;
        name: string;
        grade: string;
        section: string;
    }>;
    pendingPayments: number;
    totalPaid: number;
    upcomingEvents: number;
}

interface ParentDashboardProps {
    stats?: ParentStats;
    isLoading?: boolean;
}

/**
 * Parent Dashboard - Dashboard for parents with children info and payments
 */
export const ParentDashboard: React.FC<ParentDashboardProps> = ({
    stats,
    isLoading = false,
}) => {
    if (isLoading) {
        return (
            <div className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {[...Array(3)].map((_, i) => (
                        <CardSkeleton key={i} />
                    ))}
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <header className="flex justify-between items-center">
                <h1 className="text-2xl font-bold text-gray-900">Portal de Padres</h1>
                <div className="text-sm text-gray-500">
                    {new Date().toLocaleDateString('es-GT', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                    })}
                </div>
            </header>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <StatCard
                    title="Mis Hijos"
                    value={stats?.children?.length ?? 0}
                    icon="👨‍👧‍👦"
                    color="blue"
                />
                <StatCard
                    title="Pagos Pendientes"
                    value={stats?.pendingPayments ?? 0}
                    icon="💳"
                    trend={stats?.pendingPayments ? { value: stats.pendingPayments, isPositive: false } : undefined}
                    color="yellow"
                />
                <StatCard
                    title="Próximos Eventos"
                    value={stats?.upcomingEvents ?? 0}
                    icon="📅"
                    color="purple"
                />
            </div>

            {/* Children Cards */}
            <section className="space-y-4">
                <h2 className="text-lg font-semibold">Mis Hijos</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {stats?.children?.map((child) => (
                        <div
                            key={child.id}
                            className="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow"
                        >
                            <div className="flex items-center space-x-4">
                                <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span className="text-2xl">👨‍🎓</span>
                                </div>
                                <div>
                                    <h3 className="font-semibold text-gray-900">{child.name}</h3>
                                    <p className="text-sm text-gray-500">
                                        {child.grade} - Sección {child.section}
                                    </p>
                                </div>
                            </div>
                            <div className="mt-4 flex space-x-2">
                                <button className="flex-1 text-sm bg-blue-50 text-blue-600 px-3 py-2 rounded hover:bg-blue-100 transition-colors">
                                    Ver Notas
                                </button>
                                <button className="flex-1 text-sm bg-green-50 text-green-600 px-3 py-2 rounded hover:bg-green-100 transition-colors">
                                    Asistencia
                                </button>
                            </div>
                        </div>
                    )) ?? (
                            <p className="text-gray-500">No se encontraron hijos registrados</p>
                        )}
                </div>
            </section>

            {/* Payment Alert */}
            {stats?.pendingPayments && stats.pendingPayments > 0 && (
                <section className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-3">
                            <span className="text-2xl">⚠️</span>
                            <div>
                                <h3 className="font-semibold text-yellow-800">Pagos Pendientes</h3>
                                <p className="text-sm text-yellow-700">
                                    Tienes {stats.pendingPayments} pago(s) pendiente(s) por realizar
                                </p>
                            </div>
                        </div>
                        <button className="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors">
                            Ver Pagos
                        </button>
                    </div>
                </section>
            )}
        </div>
    );
};

export default ParentDashboard;
