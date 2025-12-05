import React from 'react';
import { StatCard } from '../StatCard';
import { CardSkeleton } from '../ui/LazyComponent';

interface TeacherStats {
    totalStudents: number;
    totalClasses: number;
    pendingGrades: number;
    upcomingEvents: number;
}

interface TeacherDashboardProps {
    stats?: TeacherStats;
    isLoading?: boolean;
}

/**
 * Teacher Dashboard - Dashboard for teachers with class and grading info
 */
export const TeacherDashboard: React.FC<TeacherDashboardProps> = ({
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
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <header className="flex justify-between items-center">
                <h1 className="text-2xl font-bold text-gray-900">Mi Panel de Maestro</h1>
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
                    title="Mis Estudiantes"
                    value={stats?.totalStudents ?? 0}
                    icon="👨‍🎓"
                    color="blue"
                />
                <StatCard
                    title="Clases Asignadas"
                    value={stats?.totalClasses ?? 0}
                    icon="📖"
                    color="green"
                />
                <StatCard
                    title="Notas Pendientes"
                    value={stats?.pendingGrades ?? 0}
                    icon="📝"
                    trend={stats?.pendingGrades ? { value: stats.pendingGrades, isPositive: false } : undefined}
                    color="yellow"
                />
                <StatCard
                    title="Próximos Eventos"
                    value={stats?.upcomingEvents ?? 0}
                    icon="📅"
                    color="purple"
                />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <section className="bg-white rounded-lg shadow p-6">
                    <h2 className="text-lg font-semibold mb-4">Mis Clases de Hoy</h2>
                    <div className="space-y-3">
                        <p className="text-gray-500">No hay clases programadas para hoy</p>
                    </div>
                </section>

                <section className="bg-white rounded-lg shadow p-6">
                    <h2 className="text-lg font-semibold mb-4">Tareas Pendientes</h2>
                    <div className="space-y-3">
                        {stats?.pendingGrades && stats.pendingGrades > 0 ? (
                            <div className="p-3 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center justify-between">
                                <span className="text-yellow-800">
                                    📝 Tienes {stats.pendingGrades} notas por ingresar
                                </span>
                                <button className="text-yellow-600 hover:text-yellow-800 text-sm font-medium">
                                    Ver →
                                </button>
                            </div>
                        ) : (
                            <p className="text-gray-500">¡Todo al día! No tienes tareas pendientes</p>
                        )}
                    </div>
                </section>
            </div>
        </div>
    );
};

export default TeacherDashboard;
