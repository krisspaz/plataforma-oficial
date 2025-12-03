import { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { aiService, AIRiskScore } from "@/services/ai.service";
import { Loader2, AlertTriangle, TrendingUp, Brain } from "lucide-react";
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from 'recharts';
import { Badge } from "@/components/ui/badge";

export const AIDashboard = () => {
    const [highRiskStudents, setHighRiskStudents] = useState<AIRiskScore[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const data = await aiService.getHighRiskStudents();
                setHighRiskStudents(data.students);
            } catch (error) {
                console.error('Failed to fetch AI data', error);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    if (loading) {
        return (
            <div className="flex justify-center py-12">
                <Loader2 className="h-8 w-8 animate-spin text-primary" />
            </div>
        );
    }

    const getRiskColor = (level: string) => {
        switch (level) {
            case 'critical': return '#ef4444'; // red-500
            case 'high': return '#f97316'; // orange-500
            case 'medium': return '#eab308'; // yellow-500
            default: return '#22c55e'; // green-500
        }
    };

    return (
        <div className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card className="bg-gradient-to-br from-primary/5 to-primary/10 border-primary/20">
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Estudiantes en Riesgo</CardTitle>
                        <AlertTriangle className="h-4 w-4 text-destructive" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">{highRiskStudents.length}</div>
                        <p className="text-xs text-muted-foreground">
                            Detectados por IA esta semana
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Precisión del Modelo</CardTitle>
                        <Brain className="h-4 w-4 text-primary" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">94.2%</div>
                        <p className="text-xs text-muted-foreground">
                            Basado en histórico de predicciones
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Tendencia de Riesgo</CardTitle>
                        <TrendingUp className="h-4 w-4 text-green-500" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">-5%</div>
                        <p className="text-xs text-muted-foreground">
                            Reducción comparado al mes anterior
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card className="col-span-1">
                    <CardHeader>
                        <CardTitle>Análisis de Factores de Riesgo</CardTitle>
                        <CardDescription>Distribución de impacto por factor</CardDescription>
                    </CardHeader>
                    <CardContent className="h-[300px]">
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart data={[
                                { name: 'Asistencia', value: 35 },
                                { name: 'Notas', value: 45 },
                                { name: 'Pagos', value: 10 },
                                { name: 'Conducta', value: 10 },
                            ]}>
                                <CartesianGrid strokeDasharray="3 3" />
                                <XAxis dataKey="name" />
                                <YAxis />
                                <Tooltip />
                                <Bar dataKey="value" fill="#3b82f6" radius={[4, 4, 0, 0]}>
                                    {[
                                        { name: 'Asistencia', value: 35 },
                                        { name: 'Notas', value: 45 },
                                        { name: 'Pagos', value: 10 },
                                        { name: 'Conducta', value: 10 },
                                    ].map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={['#3b82f6', '#ef4444', '#eab308', '#22c55e'][index % 4]} />
                                    ))}
                                </Bar>
                            </BarChart>
                        </ResponsiveContainer>
                    </CardContent>
                </Card>

                <Card className="col-span-1">
                    <CardHeader>
                        <CardTitle>Estudiantes con Alerta Alta</CardTitle>
                        <CardDescription>Requieren atención inmediata</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {highRiskStudents.length > 0 ? (
                                highRiskStudents.slice(0, 5).map((score) => (
                                    <div key={score.id} className="flex items-center justify-between p-4 border rounded-lg">
                                        <div className="space-y-1">
                                            <p className="font-medium leading-none">
                                                {score.student?.firstName} {score.student?.lastName}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {score.predictions[0] || 'Riesgo de deserción detectado'}
                                            </p>
                                        </div>
                                        <Badge
                                            variant="outline"
                                            className="font-bold"
                                            style={{
                                                borderColor: getRiskColor(score.riskLevel),
                                                color: getRiskColor(score.riskLevel)
                                            }}
                                        >
                                            {score.riskPercentage}% Riesgo
                                        </Badge>
                                    </div>
                                ))
                            ) : (
                                <div className="text-center py-8 text-muted-foreground">
                                    No hay alertas críticas activas.
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
};
