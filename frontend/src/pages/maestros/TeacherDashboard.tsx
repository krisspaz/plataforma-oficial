import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import {
    BookOpen,
    Users,
    Calendar,
    GraduationCap,
    Bell,
    Clock,
    TrendingUp
} from 'lucide-react';
import { GradeEntry } from '@/components/grades/GradeEntry';

export const TeacherDashboard: React.FC = () => {
    const [activeTab, setActiveTab] = useState('overview');

    // Mock data - in real app, fetch from API
    const teacherData = {
        name: 'María García',
        subjects: ['Matemáticas', 'Física'],
        sections: ['1ro A', '1ro B', '2do A'],
        totalStudents: 75,
        pendingGrades: 12,
        upcomingClasses: 3,
    };

    const todaySchedule = [
        { time: '8:00 - 9:00', subject: 'Matemáticas', section: '1ro A', room: 'Aula 101' },
        { time: '9:00 - 10:00', subject: 'Matemáticas', section: '1ro B', room: 'Aula 102' },
        { time: '10:30 - 11:30', subject: 'Física', section: '2do A', room: 'Lab 1' },
    ];

    const recentAnnouncements = [
        { title: 'Reunión de maestros', date: 'Hoy 15:00', type: 'meeting' },
        { title: 'Entrega de notas 2do Bimestre', date: '15 Dic', type: 'deadline' },
    ];

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
            <div className="max-w-7xl mx-auto space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-slate-900">
                            ¡Bienvenido, {teacherData.name}!
                        </h1>
                        <p className="text-slate-500">Panel de Maestro</p>
                    </div>
                    <div className="flex gap-2">
                        {teacherData.subjects.map((subject) => (
                            <Badge key={subject} variant="secondary">{subject}</Badge>
                        ))}
                    </div>
                </div>

                {/* Quick Stats */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="bg-gradient-to-br from-blue-500 to-blue-600 text-white border-0">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium opacity-90">Mis Estudiantes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{teacherData.totalStudents}</span>
                                <Users className="w-8 h-8 opacity-80" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-br from-green-500 to-green-600 text-white border-0">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium opacity-90">Secciones</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{teacherData.sections.length}</span>
                                <BookOpen className="w-8 h-8 opacity-80" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-br from-orange-500 to-orange-600 text-white border-0">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium opacity-90">Notas Pendientes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{teacherData.pendingGrades}</span>
                                <GraduationCap className="w-8 h-8 opacity-80" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-gradient-to-br from-purple-500 to-purple-600 text-white border-0">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium opacity-90">Clases Hoy</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{teacherData.upcomingClasses}</span>
                                <Clock className="w-8 h-8 opacity-80" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
                    <TabsList className="bg-white shadow-sm border p-1">
                        <TabsTrigger value="overview" className="data-[state=active]:bg-blue-50 data-[state=active]:text-blue-700">
                            Resumen
                        </TabsTrigger>
                        <TabsTrigger value="grades" className="data-[state=active]:bg-blue-50 data-[state=active]:text-blue-700">
                            <GraduationCap className="w-4 h-4 mr-2" />
                            Calificaciones
                        </TabsTrigger>
                        <TabsTrigger value="schedule" className="data-[state=active]:bg-blue-50 data-[state=active]:text-blue-700">
                            <Calendar className="w-4 h-4 mr-2" />
                            Horario
                        </TabsTrigger>
                        <TabsTrigger value="students" className="data-[state=active]:bg-blue-50 data-[state=active]:text-blue-700">
                            <Users className="w-4 h-4 mr-2" />
                            Estudiantes
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="overview">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Today's Schedule */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Clock className="w-5 h-5" />
                                        Horario de Hoy
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {todaySchedule.map((item, i) => (
                                            <div key={i} className="flex items-center gap-4 p-3 bg-slate-50 rounded-lg">
                                                <div className="text-sm font-medium text-blue-600 w-24">
                                                    {item.time}
                                                </div>
                                                <div className="flex-1">
                                                    <p className="font-medium">{item.subject}</p>
                                                    <p className="text-sm text-slate-500">{item.section} • {item.room}</p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Announcements */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Bell className="w-5 h-5" />
                                        Anuncios Recientes
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {recentAnnouncements.map((item, i) => (
                                            <div key={i} className="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                                <div>
                                                    <p className="font-medium">{item.title}</p>
                                                    <p className="text-sm text-slate-500">{item.date}</p>
                                                </div>
                                                <Badge variant="outline">{item.type === 'meeting' ? 'Reunión' : 'Fecha límite'}</Badge>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Performance Overview */}
                            <Card className="lg:col-span-2">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <TrendingUp className="w-5 h-5" />
                                        Rendimiento por Sección
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        {teacherData.sections.map((section) => (
                                            <div key={section} className="p-4 border rounded-lg">
                                                <h4 className="font-medium">{section}</h4>
                                                <div className="mt-2 flex items-center gap-2">
                                                    <div className="flex-1 h-2 bg-slate-200 rounded-full">
                                                        <div
                                                            className="h-2 bg-green-500 rounded-full"
                                                            style={{ width: `${Math.random() * 30 + 70}%` }}
                                                        />
                                                    </div>
                                                    <span className="text-sm font-medium text-green-600">
                                                        {(Math.random() * 10 + 75).toFixed(1)}%
                                                    </span>
                                                </div>
                                                <p className="text-xs text-slate-500 mt-1">Promedio de aprobación</p>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <TabsContent value="grades">
                        <GradeEntry />
                    </TabsContent>

                    <TabsContent value="schedule">
                        <Card>
                            <CardHeader>
                                <CardTitle>Mi Horario Semanal</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-6 gap-2 text-center">
                                    {['Hora', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'].map((day) => (
                                        <div key={day} className="font-medium p-2 bg-slate-100 rounded">
                                            {day}
                                        </div>
                                    ))}
                                    {/* Mock schedule grid */}
                                    {['8:00-9:00', '9:00-10:00', '10:30-11:30', '11:30-12:30'].map((time) => (
                                        <React.Fragment key={time}>
                                            <div className="p-2 text-sm text-slate-600">{time}</div>
                                            {[1, 2, 3, 4, 5].map((day) => (
                                                <div
                                                    key={day}
                                                    className={`p-2 text-xs rounded ${Math.random() > 0.5
                                                            ? 'bg-blue-100 text-blue-800'
                                                            : 'bg-slate-50'
                                                        }`}
                                                >
                                                    {Math.random() > 0.5 ? 'MAT 1A' : ''}
                                                </div>
                                            ))}
                                        </React.Fragment>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="students">
                        <Card>
                            <CardHeader>
                                <CardTitle>Mis Estudiantes</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-slate-500">Lista de estudiantes por sección...</p>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    );
};

export default TeacherDashboard;
