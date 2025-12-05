import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { DollarSign, Calendar, AlertCircle } from "lucide-react";
import { Badge } from "@/components/ui/badge";

export default function CuentaPage() {
    const estudiantes = [
        { nombre: "Pedro Antonio García Hernández", saldo: "Q0.00", estado: "Al día", proximoPago: "15 Ene 2026" },
    ];

    return (
        <div className="p-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-slate-900">Mi Cuenta</h1>
                <p className="text-slate-600 mt-2">Estado de cuenta y pagos de tus hijos</p>
            </div>

            <div className="grid gap-6 md:grid-cols-3 mb-8">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Saldo Total</CardTitle>
                        <DollarSign className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-green-600">Q0.00</div>
                        <p className="text-xs text-muted-foreground">
                            Sin saldo pendiente
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Próximo Pago</CardTitle>
                        <Calendar className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">15 Ene</div>
                        <p className="text-xs text-muted-foreground">
                            Mensualidad de enero
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Estudiantes</CardTitle>
                        <AlertCircle className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">1</div>
                        <p className="text-xs text-muted-foreground">
                            Estudiante activo
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Estudiantes</CardTitle>
                    <CardDescription>Estado de cuenta por estudiante</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="space-y-4">
                        {estudiantes.map((estudiante, index) => (
                            <div key={index} className="flex items-center justify-between p-4 border rounded-lg">
                                <div>
                                    <h3 className="font-semibold">{estudiante.nombre}</h3>
                                    <p className="text-sm text-slate-600">Próximo pago: {estudiante.proximoPago}</p>
                                </div>
                                <div className="text-right">
                                    <p className="text-lg font-bold">{estudiante.saldo}</p>
                                    <Badge variant={estudiante.estado === "Al día" ? "default" : "destructive"}>
                                        {estudiante.estado}
                                    </Badge>
                                </div>
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
