import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { FileText, Download, Calendar } from "lucide-react";

export default function ReportesPage() {
    const reportes = [
        { nombre: "Reporte Financiero Mensual", descripcion: "Ingresos, egresos y balance", fecha: "Diciembre 2025" },
        { nombre: "Reporte de Asistencia", descripcion: "Asistencia de estudiantes por grado", fecha: "Última semana" },
        { nombre: "Reporte Académico", descripcion: "Promedios y rendimiento por curso", fecha: "Bimestre actual" },
        { nombre: "Reporte de Morosidad", descripcion: "Estudiantes con pagos pendientes", fecha: "Al día de hoy" },
    ];

    return (
        <div className="p-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-slate-900">Reportes</h1>
                <p className="text-slate-600 mt-2">Generación y descarga de informes institucionales</p>
            </div>

            <div className="grid gap-4">
                {reportes.map((reporte, index) => (
                    <Card key={index}>
                        <CardHeader>
                            <div className="flex items-start justify-between">
                                <div className="flex items-center gap-3">
                                    <FileText className="h-6 w-6 text-primary" />
                                    <div>
                                        <CardTitle>{reporte.nombre}</CardTitle>
                                        <CardDescription>{reporte.descripcion}</CardDescription>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Button variant="outline" size="sm">
                                        <Calendar className="h-4 w-4 mr-2" />
                                        {reporte.fecha}
                                    </Button>
                                    <Button size="sm">
                                        <Download className="h-4 w-4 mr-2" />
                                        Descargar
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>
                ))}
            </div>

            <Card className="mt-8">
                <CardHeader>
                    <CardTitle>Reportes Personalizados</CardTitle>
                    <CardDescription>
                        Genera reportes personalizados según tus necesidades
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <p className="text-slate-600">
                        En desarrollo: Generador de reportes personalizados con filtros avanzados.
                    </p>
                </CardContent>
            </Card>
        </div>
    );
}
