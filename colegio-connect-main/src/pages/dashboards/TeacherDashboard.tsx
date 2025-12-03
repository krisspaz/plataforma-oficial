import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { StatCard } from "@/components/StatCard";
import { Users, BookOpen, Calendar as CalendarIcon, Clock } from "lucide-react";
import { useAuth } from "@/context/AuthContext";
import { api } from "@/services/api";
import { Loader2 } from 'lucide-react';

interface TeacherStats {
    totalStudents: number;
    activeCourses: number;
    upcomingClasses: number;
}

export const TeacherDashboard = () => {
    const { user } = useAuth();
    const [stats, setStats] = useState<TeacherStats | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // In a real implementation, we would fetch specific teacher stats
        // For now, simulating data loading
        setTimeout(() => {
            setStats({
                totalStudents: 125,
                activeCourses: 4,
                upcomingClasses: 2
            });
            setLoading(false);
        }, 1000);
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
                    <h1 className="text-3xl font-bold text-foreground mb-1">
                        Panel de Maestro
                    </h1>
                    <p className="text-muted-foreground">
                        Bienvenido, Prof. {user?.firstName} {user?.lastName}
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <StatCard
                        icon={Users}
                        title="Total Estudiantes"
                        value={stats?.totalStudents.toString() || '0'}
                        subtitle="En todos los cursos"
                        variant="primary"
                    />
                    <StatCard
                        icon={BookOpen}
                        title="Cursos Activos"
                        value={stats?.activeCourses.toString() || '0'}
                        subtitle="Ciclo actual"
                        variant="accent"
                    />
                    <StatCard
                        icon={CalendarIcon}
                        title="Clases Hoy"
                        value={stats?.upcomingClasses.toString() || '0'}
                        subtitle="Próxima en 2 horas"
                        variant="warning"
                    />
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div className="bg-card rounded-lg border p-6">
                        <h3 className="text-lg font-semibold mb-4">Próximas Clases</h3>
                        <div className="space-y-4">
                            <div className="flex items-center justify-between p-4 bg-accent/5 rounded-lg">
                                <div className="flex items-center gap-4">
                                    <div className="p-2 bg-primary/10 rounded-full text-primary">
                                        <Clock className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h4 className="font-medium">Matemáticas - 1ro Básico</h4>
                                        <p className="text-sm text-muted-foreground">10:00 AM - Aula 201</p>
                                    </div>
                                </div>
                                <span className="text-sm font-medium text-primary">En 2 horas</span>
                            </div>

                            <div className="flex items-center justify-between p-4 bg-accent/5 rounded-lg">
                                <div className="flex items-center gap-4">
                                    <div className="p-2 bg-primary/10 rounded-full text-primary">
                                        <Clock className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h4 className="font-medium">Física - 3ro Básico</h4>
                                        <p className="text-sm text-muted-foreground">02:00 PM - Laboratorio 1</p>
                                    </div>
                                </div>
                                <span className="text-sm font-medium text-muted-foreground">Esta tarde</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
};
