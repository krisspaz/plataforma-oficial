import React, { useState } from 'react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, Megaphone, Calendar, GraduationCap, BookOpen, Bell } from 'lucide-react';
import { AssignmentManager } from '@/components/coordination/AssignmentManager';
import { AnnouncementBoard } from '@/components/coordination/AnnouncementBoard';
import { AcademicCalendar } from '@/components/coordination/AcademicCalendar';

export const CoordinacionDashboard: React.FC = () => {
    const [activeTab, setActiveTab] = useState('overview');

    // Mock stats - in real app, fetch from API
    const stats = {
        teachers: 24,
        students: 450,
        announcements: 8,
        upcomingEvents: 5,
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
            <div className="max-w-7xl mx-auto space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-slate-900">Panel de Coordinación</h1>
                        <p className="text-slate-500">Gestión académica y comunicación escolar</p>
                    </div>
                </div>

                {/* Quick Stats */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="bg-gradient-to-br from-blue-500 to-blue-600 text-white border-0">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium opacity-90">Maestros</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{stats.teachers}</span>
                                <Users className="w-8 h-8 opacity-80" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-br from-green-500 to-green-600 text-white border-0">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium opacity-90">Estudiantes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{stats.students}</span>
                                <GraduationCap className="w-8 h-8 opacity-80" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-br from-purple-500 to-purple-600 text-white border-0">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium opacity-90">Anuncios Activos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{stats.announcements}</span>
                                <Bell className="w-8 h-8 opacity-80" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-br from-orange-500 to-orange-600 text-white border-0">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium opacity-90">Próximos Eventos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{stats.upcomingEvents}</span>
                                <Calendar className="w-8 h-8 opacity-80" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
                    <TabsList className="bg-white shadow-sm border p-1">
                        <TabsTrigger value="overview" className="data-[state=active]:bg-blue-50 data-[state=active]:text-blue-700">
                            <BookOpen className="w-4 h-4 mr-2" />
                            Resumen
                        </TabsTrigger>
                        <TabsTrigger value="assignments" className="data-[state=active]:bg-blue-50 data-[state=active]:text-blue-700">
                            <Users className="w-4 h-4 mr-2" />
                            Asignaciones
                        </TabsTrigger>
                        <TabsTrigger value="announcements" className="data-[state=active]:bg-blue-50 data-[state=active]:text-blue-700">
                            <Megaphone className="w-4 h-4 mr-2" />
                            Anuncios
                        </TabsTrigger>
                        <TabsTrigger value="calendar" className="data-[state=active]:bg-blue-50 data-[state=active]:text-blue-700">
                            <Calendar className="w-4 h-4 mr-2" />
                            Calendario
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="overview">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Recent Announcements */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Megaphone className="w-5 h-5" />
                                        Anuncios Recientes
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {[
                                            { title: 'Reunión de padres', type: 'general', date: 'Hace 2 horas' },
                                            { title: 'Exámenes finales', type: 'students', date: 'Hace 1 día' },
                                            { title: 'Capacitación docente', type: 'teachers', date: 'Hace 2 días' },
                                        ].map((item, i) => (
                                            <div key={i} className="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                                <div>
                                                    <p className="font-medium">{item.title}</p>
                                                    <p className="text-xs text-slate-500">{item.date}</p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Upcoming Events */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Calendar className="w-5 h-5" />
                                        Próximos Eventos
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {[
                                            { title: 'Día del Maestro', date: '25 Jun', type: 'holiday' },
                                            { title: 'Examen Bimestral', date: '28 Jun', type: 'exam' },
                                            { title: 'Festival Escolar', date: '15 Jul', type: 'activity' },
                                        ].map((item, i) => (
                                            <div key={i} className="flex items-center gap-4 p-3 bg-slate-50 rounded-lg">
                                                <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 font-bold text-sm">
                                                    {item.date.split(' ')[0]}
                                                    <br />
                                                    {item.date.split(' ')[1]}
                                                </div>
                                                <div>
                                                    <p className="font-medium">{item.title}</p>
                                                    <p className="text-xs text-slate-500 capitalize">{item.type}</p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <TabsContent value="assignments">
                        <AssignmentManager />
                    </TabsContent>

                    <TabsContent value="announcements">
                        <AnnouncementBoard />
                    </TabsContent>

                    <TabsContent value="calendar">
                        <AcademicCalendar />
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    );
};

export default CoordinacionDashboard;
