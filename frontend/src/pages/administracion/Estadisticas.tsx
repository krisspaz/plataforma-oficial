import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Users, TrendingUp, BookOpen, GraduationCap } from "lucide-react";

export default function EstadisticasPage() {
    return (
        <div className="p-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-slate-900">Estadísticas</h1>
                <p className="text-slate-600 mt-2">Métricas y análisis de rendimiento institucional</p>
            </div>

            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Estudiantes</CardTitle>
                        <Users className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">342</div>
                        <p className="text-xs text-muted-foreground flex items-center gap-1">
                            <TrendingUp className="h-3 w-3 text-green-600" />
                            <span className="text-green-600">+8</span> este mes
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Total Maestros</CardTitle>
                        <GraduationCap className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">28</div>
                        <p className="text-xs text-muted-foreground">
                            Personal docente activo
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Cursos Activos</CardTitle>
                        <BookOpen className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">45</div>
                        <p className="text-xs text-muted-foreground">
                            En todos los grados
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Promedio General</CardTitle>
                        <TrendingUp className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">82.5</div>
                        <p className="text-xs text-muted-foreground">
                            Rendimiento académico
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Análisis Estadístico</CardTitle>
                    <CardDescription>
                        Reportes y métricas de rendimiento institucional
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <p className="text-slate-600">
                        En desarrollo: Gráficos de rendimiento por grado, asistencia, distribución de notas, tendencias históricas.
                    </p>
                </CardContent>
            </Card>
        </div>
    );
}
