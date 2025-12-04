import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Users,
    GraduationCap,
    Building,
    CreditCard,
    FileText,
    Settings,
    BarChart3,
    Shield,
    Database,
    Activity
} from 'lucide-react';

export const AdminDashboard: React.FC = () => {
    const [activeTab, setActiveTab] = useState('overview');

    // Mock data
    const systemStats = {
        totalUsers: 542,
        activeStudents: 450,
        teachers: 24,
        parents: 380,
        revenue: 425000,
        pendingPayments: 45,
    };

    const recentActivity = [
        { action: 'Nuevo estudiante registrado', user: 'Secretaría', time: 'Hace 5 min' },
        { action: 'Pago procesado Q850', user: 'Sistema', time: 'Hace 15 min' },
        { action: 'Contrato firmado', user: 'Roberto García', time: 'Hace 1 hora' },
        { action: 'Calificaciones actualizadas', user: 'María López', time: 'Hace 2 horas' },
    ];

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 p-6">
            <div className="max-w-7xl mx-auto space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-white">Panel de Administración</h1>
                        <p className="text-slate-400">Sistema de Gestión Escolar</p>
                    </div>
                    <div className="flex gap-2">
                        <Badge variant="outline" className="text-green-400 border-green-400">
                            <Activity className="w-3 h-3 mr-1" />
                            Sistema Operativo
                        </Badge>
                    </div>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="bg-slate-800 border-slate-700 text-white">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-slate-400">Total Usuarios</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{systemStats.totalUsers}</span>
                                <Users className="w-8 h-8 text-blue-400" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-slate-800 border-slate-700 text-white">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-slate-400">Estudiantes Activos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{systemStats.activeStudents}</span>
                                <GraduationCap className="w-8 h-8 text-green-400" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-slate-800 border-slate-700 text-white">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-slate-400">Ingresos del Mes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">Q{(systemStats.revenue / 1000).toFixed(0)}K</span>
                                <CreditCard className="w-8 h-8 text-yellow-400" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="bg-slate-800 border-slate-700 text-white">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm font-medium text-slate-400">Pagos Pendientes</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <span className="text-3xl font-bold">{systemStats.pendingPayments}</span>
                                <FileText className="w-8 h-8 text-red-400" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
                    <TabsList className="bg-slate-800 border-slate-700 p-1">
                        <TabsTrigger value="overview" className="data-[state=active]:bg-blue-600 text-white">
                            <BarChart3 className="w-4 h-4 mr-2" />
                            Resumen
                        </TabsTrigger>
                        <TabsTrigger value="users" className="data-[state=active]:bg-blue-600 text-white">
                            <Users className="w-4 h-4 mr-2" />
                            Usuarios
                        </TabsTrigger>
                        <TabsTrigger value="security" className="data-[state=active]:bg-blue-600 text-white">
                            <Shield className="w-4 h-4 mr-2" />
                            Seguridad
                        </TabsTrigger>
                        <TabsTrigger value="system" className="data-[state=active]:bg-blue-600 text-white">
                            <Database className="w-4 h-4 mr-2" />
                            Sistema
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="overview">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Recent Activity */}
                            <Card className="bg-slate-800 border-slate-700">
                                <CardHeader>
                                    <CardTitle className="text-white flex items-center gap-2">
                                        <Activity className="w-5 h-5" />
                                        Actividad Reciente
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {recentActivity.map((item, i) => (
                                            <div key={i} className="flex items-center gap-4 p-3 bg-slate-700/50 rounded-lg">
                                                <div className="w-2 h-2 bg-green-400 rounded-full" />
                                                <div className="flex-1">
                                                    <p className="text-white text-sm">{item.action}</p>
                                                    <p className="text-slate-400 text-xs">{item.user} • {item.time}</p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* User Distribution */}
                            <Card className="bg-slate-800 border-slate-700">
                                <CardHeader>
                                    <CardTitle className="text-white flex items-center gap-2">
                                        <Users className="w-5 h-5" />
                                        Distribución de Usuarios
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        <div className="flex items-center justify-between">
                                            <span className="text-slate-300">Estudiantes</span>
                                            <div className="flex items-center gap-2">
                                                <div className="w-32 h-2 bg-slate-700 rounded-full">
                                                    <div className="w-4/5 h-2 bg-blue-500 rounded-full" />
                                                </div>
                                                <span className="text-white font-medium">{systemStats.activeStudents}</span>
                                            </div>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-slate-300">Padres</span>
                                            <div className="flex items-center gap-2">
                                                <div className="w-32 h-2 bg-slate-700 rounded-full">
                                                    <div className="w-3/4 h-2 bg-green-500 rounded-full" />
                                                </div>
                                                <span className="text-white font-medium">{systemStats.parents}</span>
                                            </div>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-slate-300">Maestros</span>
                                            <div className="flex items-center gap-2">
                                                <div className="w-32 h-2 bg-slate-700 rounded-full">
                                                    <div className="w-1/4 h-2 bg-purple-500 rounded-full" />
                                                </div>
                                                <span className="text-white font-medium">{systemStats.teachers}</span>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <TabsContent value="users">
                        <Card className="bg-slate-800 border-slate-700">
                            <CardHeader>
                                <CardTitle className="text-white">Gestión de Usuarios</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-slate-400">Panel de administración de usuarios...</p>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="security">
                        <Card className="bg-slate-800 border-slate-700">
                            <CardHeader>
                                <CardTitle className="text-white">Configuración de Seguridad</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-slate-400">Opciones de seguridad del sistema...</p>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="system">
                        <Card className="bg-slate-800 border-slate-700">
                            <CardHeader>
                                <CardTitle className="text-white">Estado del Sistema</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div className="p-4 bg-slate-700/50 rounded-lg">
                                        <p className="text-slate-400 text-sm">Base de Datos</p>
                                        <p className="text-green-400 font-medium">Conectada</p>
                                    </div>
                                    <div className="p-4 bg-slate-700/50 rounded-lg">
                                        <p className="text-slate-400 text-sm">API Backend</p>
                                        <p className="text-green-400 font-medium">Operativo</p>
                                    </div>
                                    <div className="p-4 bg-slate-700/50 rounded-lg">
                                        <p className="text-slate-400 text-sm">Servicio AI</p>
                                        <p className="text-green-400 font-medium">Activo</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>
        </div>
    );
};

export default AdminDashboard;
