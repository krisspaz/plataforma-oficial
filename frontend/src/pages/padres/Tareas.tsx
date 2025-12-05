import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { CheckSquare, Calendar, Clock } from "lucide-react";
import { Badge } from "@/components/ui/badge";

export default function TareasPage() {
    const tareas = [
        { titulo: "Proyecto de Ciencias", materia: "Ciencias Naturales", fecha: "15 Dic 2025", estado: "Pendiente" },
        { titulo: "Ensayo de Historia", materia: "Estudios Sociales", fecha: "18 Dic 2025", estado: "En progreso" },
        { titulo: "Ejercicios de Matemáticas", materia: "Matemáticas", fecha: "12 Dic 2025", estado: "Completado" },
    ];

    return (
        <div className="p-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-slate-900">Tareas</h1>
                <p className="text-slate-600 mt-2">Tareas y actividades de tus hijos</p>
            </div>

            <div className="grid gap-4">
                {tareas.map((tarea, index) => (
                    <Card key={index}>
                        <CardHeader>
                            <div className="flex items-start justify-between">
                                <div className="flex items-center gap-3">
                                    <CheckSquare className="h-6 w-6 text-primary" />
                                    <div>
                                        <CardTitle>{tarea.titulo}</CardTitle>
                                        <CardDescription className="flex items-center gap-2">
                                            {tarea.materia}
                                        </CardDescription>
                                    </div>
                                </div>
                                <div className="flex flex-col items-end gap-2">
                                    <Badge variant={
                                        tarea.estado === "Completado" ? "default" :
                                            tarea.estado === "En progreso" ? "secondary" : "outline"
                                    }>
                                        {tarea.estado}
                                    </Badge>
                                    <span className="text-sm text-slate-600 flex items-center gap-1">
                                        <Calendar className="h-3 w-3" />
                                        {tarea.fecha}
                                    </span>
                                </div>
                            </div>
                        </CardHeader>
                    </Card>
                ))}
            </div>

            <Card className="mt-8">
                <CardHeader>
                    <CardTitle>Resumen de Tareas</CardTitle>
                    <CardDescription>
                        Estadísticas de tareas de tus hijos
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p className="text-2xl font-bold">1</p>
                            <p className="text-sm text-slate-600">Completadas</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold">1</p>
                            <p className="text-sm text-slate-600">En progreso</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold">1</p>
                            <p className="text-sm text-slate-600">Pendientes</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
