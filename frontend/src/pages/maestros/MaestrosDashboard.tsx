import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { StatCard } from "@/components/StatCard";
import { BookOpen, Calendar, FileText, TrendingUp } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { useAuth } from "@/context/AuthContext";
import { maestrosService } from "@/services/maestros.service";
import { errorHandler } from "@/lib/errorHandler";
import { Loader2 } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

export const MaestrosDashboard = () => {
    const { user } = useAuth();
    const navigate = useNavigate();
    const [loading, setLoading] = useState(true);
    const [stats, setStats] = useState({
        totalActivities: 0,
        pendingGrades: 0,
        materials: 0,
        upcomingActivities: 0,
    });

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const activities = await maestrosService.getMyActivities();
                setStats({
                    totalActivities: activities.length,
                    pendingGrades: activities.filter(a => a.activityType === 'EXAMEN').length,
                    materials: 0,
                    upcomingActivities: activities.filter(a => new Date(a.dueDate) > new Date()).length,
                });
            } catch (error) {
                errorHandler.handleApiError(error, 'Error al cargar estadísticas');
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
                <div className="mb-8">
                    <h1 className="text-3xl font-bold mb-1">Portal del Maestro</h1>
                    <p className="text-muted-foreground">Bienvenido, {user?.firstName} {user?.lastName}</p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <StatCard
                        icon={BookOpen}
                        title="Actividades"
                        value={stats.totalActivities.toString()}
                        subtitle="Total creadas"
                        variant="primary"
                    />
                    <StatCard
                        icon={TrendingUp}
                        title="Notas Pendientes"
                        value={stats.pendingGrades.toString()}
                        subtitle="Por calificar"
                        variant="warning"
                    />
                    <StatCard
                        icon={FileText}
                        title="Materiales"
                        value={stats.materials.toString()}
                        subtitle="Subidos"
                        variant="accent"
                    />
                    <StatCard
                        icon={Calendar}
                        title="Próximas Actividades"
                        value={stats.upcomingActivities.toString()}
                        subtitle="Esta semana"
                        variant="success"
                    />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <Card className="p-6 hover:shadow-lg transition-shadow cursor-pointer" onClick={() => navigate('/maestros/actividades/nueva')}>
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                <BookOpen className="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <h3 className="font-semibold">Nueva Actividad</h3>
                                <p className="text-sm text-muted-foreground">Crear tarea o examen</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6 hover:shadow-lg transition-shadow cursor-pointer" onClick={() => navigate('/maestros/notas/cargar')}>
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center">
                                <TrendingUp className="w-6 h-6 text-success" />
                            </div>
                            <div>
                                <h3 className="font-semibold">Cargar Notas</h3>
                                <p className="text-sm text-muted-foreground">Ingresar calificaciones</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6 hover:shadow-lg transition-shadow cursor-pointer" onClick={() => navigate('/maestros/materiales')}>
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center">
                                <FileText className="w-6 h-6 text-accent" />
                            </div>
                            <div>
                                <h3 className="font-semibold">Subir Material</h3>
                                <p className="text-sm text-muted-foreground">Recursos para estudiantes</p>
                            </div>
                        </div>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card className="p-6">
                        <h3 className="text-lg font-semibold mb-4">Mis Actividades Recientes</h3>
                        <div className="space-y-3 text-sm">
                            <div className="p-3 bg-muted/50 rounded flex justify-between">
                                <div>
                                    <p className="font-medium">Examen de Matemáticas</p>
                                    <p className="text-muted-foreground">Pendiente de calificar</p>
                                </div>
                                <Button size="sm" variant="outline">Ver</Button>
                            </div>
                            <div className="p-3 bg-muted/50 rounded flex justify-between">
                                <div>
                                    <p className="font-medium">Tarea de Física</p>
                                    <p className="text-muted-foreground">Vence mañana</p>
                                </div>
                                <Button size="sm" variant="outline">Ver</Button>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6">
                        <h3 className="text-lg font-semibold mb-4">Calendario</h3>
                        <div className="space-y-2">
                            <Button variant="outline" className="w-full justify-start" onClick={() => navigate('/maestros/calendario')}>
                                <Calendar className="w-4 h-4 mr-2" />
                                Ver Calendario Completo
                            </Button>
                            <Button variant="outline" className="w-full justify-start" onClick={() => navigate('/maestros/notas/finales')}>
                                <TrendingUp className="w-4 h-4 mr-2" />
                                Ver Notas Finales
                            </Button>
                        </div>
                    </Card>
                </div>
            </main>
        </div>
    );
};
