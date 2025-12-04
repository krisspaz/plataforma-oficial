import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { StatCard } from "@/components/StatCard";
import { Users, BookOpen, Calendar, TrendingUp, Bell } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { useAuth } from "@/context/AuthContext";
import { coordinacionService } from "@/services/coordinacion.service";
import { errorHandler } from "@/lib/errorHandler";
import { Loader2 } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

export const CoordinacionDashboard = () => {
    const { user } = useAuth();
    const navigate = useNavigate();
    const [loading, setLoading] = useState(true);
    const [stats, setStats] = useState({
        totalTeachers: 0,
        totalAnnouncements: 0,
        upcomingBirthdays: 0,
        pendingAssignments: 0,
    });

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const [teachers, announcements, birthdays] = await Promise.all([
                    coordinacionService.getTeachers(),
                    coordinacionService.getAnnouncements(),
                    coordinacionService.getTeacherBirthdays(),
                ]);

                setStats({
                    totalTeachers: teachers.length,
                    totalAnnouncements: announcements.length,
                    upcomingBirthdays: birthdays.length,
                    pendingAssignments: 0,
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
                    <h1 className="text-3xl font-bold mb-1">Coordinación Académica</h1>
                    <p className="text-muted-foreground">Bienvenido, {user?.firstName} {user?.lastName}</p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <StatCard
                        icon={Users}
                        title="Profesores"
                        value={stats.totalTeachers.toString()}
                        subtitle="Activos"
                        variant="primary"
                    />
                    <StatCard
                        icon={Bell}
                        title="Anuncios"
                        value={stats.totalAnnouncements.toString()}
                        subtitle="Publicados"
                        variant="accent"
                    />
                    <StatCard
                        icon={Calendar}
                        title="Cumpleaños"
                        value={stats.upcomingBirthdays.toString()}
                        subtitle="Este mes"
                        variant="success"
                    />
                    <StatCard
                        icon={BookOpen}
                        title="Asignaciones Pendientes"
                        value={stats.pendingAssignments.toString()}
                        subtitle="Por completar"
                        variant="warning"
                    />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <Card className="p-6 hover:shadow-lg transition-shadow cursor-pointer" onClick={() => navigate('/coordinacion/anuncios/nuevo')}>
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center">
                                <Bell className="w-6 h-6 text-accent" />
                            </div>
                            <div>
                                <h3 className="font-semibold">Nuevo Anuncio</h3>
                                <p className="text-sm text-muted-foreground">Publicar comunicado</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6 hover:shadow-lg transition-shadow cursor-pointer" onClick={() => navigate('/coordinacion/profesores')}>
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                                <Users className="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <h3 className="font-semibold">Gestión de Profesores</h3>
                                <p className="text-sm text-muted-foreground">Ver y editar profesores</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-6 hover:shadow-lg transition-shadow cursor-pointer" onClick={() => navigate('/coordinacion/notas')}>
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center">
                                <TrendingUp className="w-6 h-6 text-success" />
                            </div>
                            <div>
                                <h3 className="font-semibold">Gestión de Notas</h3>
                                <p className="text-sm text-muted-foreground">Boletas y reportes</p>
                            </div>
                        </div>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card className="p-6">
                        <h3 className="text-lg font-semibold mb-4">Acciones Rápidas</h3>
                        <div className="space-y-2">
                            <Button variant="outline" className="w-full justify-start" onClick={() => navigate('/coordinacion/materias/asignar')}>
                                <BookOpen className="w-4 h-4 mr-2" />
                                Asignar Materias
                            </Button>
                            <Button variant="outline" className="w-full justify-start" onClick={() => navigate('/coordinacion/notas/descargar')}>
                                <TrendingUp className="w-4 h-4 mr-2" />
                                Descargar Notas
                            </Button>
                            <Button variant="outline" className="w-full justify-start" onClick={() => navigate('/coordinacion/profesores/cumpleanos')}>
                                <Calendar className="w-4 h-4 mr-2" />
                                Ver Cumpleaños
                            </Button>
                        </div>
                    </Card>

                    <Card className="p-6">
                        <h3 className="text-lg font-semibold mb-4">Anuncios Recientes</h3>
                        <div className="space-y-3 text-sm">
                            <div className="p-3 bg-muted/50 rounded">
                                <p className="font-medium">Reunión de Profesores</p>
                                <p className="text-muted-foreground">Viernes 15:00 hrs</p>
                            </div>
                            <div className="p-3 bg-muted/50 rounded">
                                <p className="font-medium">Cierre de Bimestre</p>
                                <p className="text-muted-foreground">Próximo lunes</p>
                            </div>
                        </div>
                    </Card>
                </div>
            </main>
        </div>
    );
};
