import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Users,
    GraduationCap,
    CreditCard,
    FileText,
    Bell,
    Calendar,
    BookOpen,
    TrendingUp,
    AlertCircle
} from 'lucide-react';
import { StudentReportCard } from '@/components/grades/StudentReportCard';

export const ParentDashboard: React.FC = () => {
    const [activeTab, setActiveTab] = useState('overview');

    // Mock data - in real app, fetch from API
    const parentData = {
        name: 'Roberto García',
        children: [
            { id: 1, name: 'Ana García', grade: '2do Básico', section: 'A', average: 85.5 },
            { id: 2, name: 'Carlos García', grade: 'Kinder', section: 'B', average: 92.0 },
        ],
    };

    const [selectedChild, setSelectedChild] = useState(parentData.children[0]);

    const pendingPayments = [
        { concept: 'Colegiatura Diciembre', amount: 850, dueDate: '2025-12-15', status: 'pending' },
        { concept: 'Material Escolar', amount: 150, dueDate: '2025-12-20', status: 'pending' },
    ];

    const announcements = [
        { title: 'Clausura de año escolar', date: '20 Dic', type: 'event' },
        { title: 'Entrega de notas finales', date: '18 Dic', type: 'info' },
        { title: 'Vacaciones de Navidad', date: '21 Dic - 5 Ene', type: 'holiday' },
    ];

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
            <div className="max-w-7xl mx-auto space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-slate-900">
                            ¡Bienvenido, {parentData.name}!
                        </h1>
                        <p className="text-slate-500">Portal de Padres de Familia</p>
                    </div>
                </div>

                {/* Children Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {parentData.children.map((child) => (
                        <Card
                            key={child.id}
                            className={`cursor-pointer transition-all ${selectedChild.id === child.id
                                    ? 'ring-2 ring-blue-500 bg-blue-50'
                                    : 'hover:shadow-md'
                                }`}
                            onClick={() => setSelectedChild(child)}
                        >
                            <CardContent className="pt-6">
                                <div className="flex items-center gap-4">
                                    <div className="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                        {child.name.charAt(0)}
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-bold">{child.name}</h3>
                                        <p className="text-sm text-slate-500">{child.grade} - Sección {child.section}</p>
                                    </div>
                                    <div className="text-right">
                                        <p className="text-2xl font-bold text-blue-600">{child.average.toFixed(1)}</p>
                                        <p className="text-xs text-slate-500">Promedio</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Main Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
                    <TabsList className="bg-white shadow-sm border p-1">
                        <TabsTrigger value="overview">Resumen</TabsTrigger>
                        <TabsTrigger value="grades">
                            <GraduationCap className="w-4 h-4 mr-2" />
                            Calificaciones
                        </TabsTrigger>
                        <TabsTrigger value="payments">
                            <CreditCard className="w-4 h-4 mr-2" />
                            Pagos
                        </TabsTrigger>
                        <TabsTrigger value="documents">
                            <FileText className="w-4 h-4 mr-2" />
                            Documentos
                        </TabsTrigger>
                    </TabsList>

                    <TabsContent value="overview">
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            {/* Quick Stats for Selected Child */}
                            <Card className="lg:col-span-2">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <TrendingUp className="w-5 h-5" />
                                        Rendimiento de {selectedChild.name}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div className="p-4 bg-green-50 rounded-lg text-center">
                                            <p className="text-2xl font-bold text-green-600">{selectedChild.average}</p>
                                            <p className="text-xs text-slate-500">Promedio General</p>
                                        </div>
                                        <div className="p-4 bg-blue-50 rounded-lg text-center">
                                            <p className="text-2xl font-bold text-blue-600">95%</p>
                                            <p className="text-xs text-slate-500">Asistencia</p>
                                        </div>
                                        <div className="p-4 bg-purple-50 rounded-lg text-center">
                                            <p className="text-2xl font-bold text-purple-600">A</p>
                                            <p className="text-xs text-slate-500">Conducta</p>
                                        </div>
                                        <div className="p-4 bg-orange-50 rounded-lg text-center">
                                            <p className="text-2xl font-bold text-orange-600">3</p>
                                            <p className="text-xs text-slate-500">Actividades</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Announcements */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Bell className="w-5 h-5" />
                                        Anuncios
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {announcements.map((item, i) => (
                                            <div key={i} className="flex items-center gap-3 p-2 bg-slate-50 rounded">
                                                <Calendar className="w-4 h-4 text-slate-400" />
                                                <div className="flex-1">
                                                    <p className="text-sm font-medium">{item.title}</p>
                                                    <p className="text-xs text-slate-500">{item.date}</p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Pending Payments */}
                            <Card className="lg:col-span-3">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <CreditCard className="w-5 h-5" />
                                        Pagos Pendientes
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    {pendingPayments.length > 0 ? (
                                        <div className="space-y-3">
                                            {pendingPayments.map((payment, i) => (
                                                <div key={i} className="flex items-center justify-between p-4 border rounded-lg">
                                                    <div className="flex items-center gap-3">
                                                        <AlertCircle className="w-5 h-5 text-orange-500" />
                                                        <div>
                                                            <p className="font-medium">{payment.concept}</p>
                                                            <p className="text-sm text-slate-500">Vence: {payment.dueDate}</p>
                                                        </div>
                                                    </div>
                                                    <div className="flex items-center gap-4">
                                                        <span className="text-xl font-bold">Q{payment.amount}</span>
                                                        <Button size="sm">Pagar</Button>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <p className="text-slate-500 text-center py-4">
                                            No hay pagos pendientes
                                        </p>
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    <TabsContent value="grades">
                        <StudentReportCard
                            studentId={selectedChild.id}
                            studentName={selectedChild.name}
                        />
                    </TabsContent>

                    <TabsContent value="payments">
                        <Card>
                            <CardHeader>
                                <CardTitle>Historial de Pagos</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-slate-500">Historial de pagos y facturas...</p>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="documents">
                        <Card>
                            <CardHeader>
                                <CardTitle>Documentos</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="p-4 border rounded-lg flex items-center gap-3 hover:bg-slate-50 cursor-pointer">
                                        <FileText className="w-8 h-8 text-red-500" />
                                        <div>
                                            <p className="font-medium">Contrato de Inscripción</p>
                                            <p className="text-sm text-slate-500">PDF • Firmado</p>
                                        </div>
                                    </div>
                                    <div className="p-4 border rounded-lg flex items-center gap-3 hover:bg-slate-50 cursor-pointer">
                                        <FileText className="w-8 h-8 text-blue-500" />
                                        <div>
                                            <p className="font-medium">Constancia de Estudios</p>
                                            <p className="text-sm text-slate-500">PDF • Disponible</p>
                                        </div>
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

export default ParentDashboard;
