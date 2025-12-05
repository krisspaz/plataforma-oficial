import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { FileText, Download, Eye } from "lucide-react";
import { Badge } from "@/components/ui/badge";

export default function ContratosPage() {
    const contratos = [
        { nombre: "Contrato de Servicios Educativos 2025", fecha: "Enero 2025", estado: "Activo" },
        { nombre: "Contrato de Servicios Educativos 2024", fecha: "Enero 2024", estado: "Finalizado" },
    ];

    return (
        <div className="p-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-slate-900">Contratos</h1>
                <p className="text-slate-600 mt-2">Consulta y descarga tus contratos educativos</p>
            </div>

            <div className="grid gap-4">
                {contratos.map((contrato, index) => (
                    <Card key={index}>
                        <CardHeader>
                            <div className="flex items-start justify-between">
                                <div className="flex items-center gap-3">
                                    <FileText className="h-6 w-6 text-primary" />
                                    <div>
                                        <CardTitle>{contrato.nombre}</CardTitle>
                                        <CardDescription>{contrato.fecha}</CardDescription>
                                    </div>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Badge variant={contrato.estado === "Activo" ? "default" : "secondary"}>
                                        {contrato.estado}
                                    </Badge>
                                    <Button variant="outline" size="sm">
                                        <Eye className="h-4 w-4 mr-2" />
                                        Ver
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
                    <CardTitle>Información del Contrato</CardTitle>
                    <CardDescription>
                        Detalles importantes sobre tus contratos educativos
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <p className="text-slate-600">
                        Los contratos educativos establecen los términos y condiciones de los servicios educativos prestados.
                        Puedes descargar una copia para tus registros en cualquier momento.
                    </p>
                </CardContent>
            </Card>
        </div>
    );
}
