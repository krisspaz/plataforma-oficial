import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Plus, BookOpen, Calendar } from "lucide-react";

export default function ActividadesPage() {
    const actividades = [
        { titulo: "Proyecto de Ciencias", materia: "Ciencias Naturales", fecha: "15 Dic 2025", estudiantes: 28 },
        { titulo: "Ensayo sobre Historia", materia: "Estudios Sociales", fecha: "18 Dic 2025", estudiantes: 28 },
        { titulo: "Laboratorio de Química", materia: "Química", fecha: "20 Dic 2025", estudiantes: 25 },
    ];

    return (
        <div className="p-8">
            <div className="mb-8 flex items-center justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-slate-900">Actividades</h1>
                    <p className="text-slate-600 mt-2">Gestiona tus actividades y tareas asignadas</p>
                </div>
                <Button>
                    <Plus className="h-4 w-4 mr-2" />
                    Nueva Actividad
                </Button>
            </div>

            <div className="grid gap-4">
                {actividades.map((actividad, index) => (
                    <Card key={index}>
                        <CardHeader>
                            <div className="flex items-start justify-between">
                                <div className="flex items-center gap-3">
                                    <BookOpen className="h-6 w-6 text-primary" />
                                    <div>
                                        <CardTitle>{actividad.titulo}</CardTitle>
                                        <CardDescription>{actividad.materia}</CardDescription>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2 text-sm text-slate-600">
                                    <Calendar className="h-4 w-4" />
                                    {actividad.fecha}
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p className="text-sm text-slate-600">
                                {actividad.estudiantes} estudiantes asignados
                            </p>
                        </CardContent>
                    </Card>
                ))}
            </div>
        </div>
    );
}
