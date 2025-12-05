import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Calendar as CalendarIcon, Clock } from "lucide-react";

export default function CalendarioMaestrosPage() {
    const eventos = [
        { titulo: "Clase de Matemáticas", hora: "08:00 - 09:30", grado: "5to Primaria", fecha: "Lunes" },
        { titulo: "Clase de Ciencias", hora: "10:00 - 11:30", grado: "6to Primaria", fecha: "Lunes" },
        { titulo: "Reunión de Maestros", hora: "14:00 - 15:00", grado: "Todos", fecha: "Miércoles" },
        { titulo: "Entrega de Notas", hora: "Todo el día", grado: "Todos", fecha: "Viernes" },
    ];

    return (
        <div className="p-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-slate-900">Calendario</h1>
                <p className="text-slate-600 mt-2">Tus clases y eventos programados</p>
            </div>

            <div className="grid gap-4">
                {eventos.map((evento, index) => (
                    <Card key={index}>
                        <CardHeader>
                            <div className="flex items-start justify-between">
                                <div className="flex items-center gap-3">
                                    <CalendarIcon className="h-6 w-6 text-primary" />
                                    <div>
                                        <CardTitle>{evento.titulo}</CardTitle>
                                        <CardDescription className="flex items-center gap-2">
                                            <Clock className="h-3 w-3" />
                                            {evento.hora} • {evento.grado}
                                        </CardDescription>
                                    </div>
                                </div>
                                <span className="text-sm font-medium text-slate-600">{evento.fecha}</span>
                            </div>
                        </CardHeader>
                    </Card>
                ))}
            </div>

            <Card className="mt-8">
                <CardHeader>
                    <CardTitle>Vista de Calendario</CardTitle>
                    <CardDescription>
                        Visualización mensual de tus actividades
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <p className="text-slate-600">
                        En desarrollo: Calendario interactivo con vista mensual y semanal.
                    </p>
                </CardContent>
            </Card>
        </div>
    );
}
