import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Plus, FileText, Download } from "lucide-react";

export default function MaterialesPage() {
    const materiales = [
        { nombre: "Guía de Matemáticas - Álgebra", tipo: "PDF", tamaño: "2.5 MB", fecha: "10 Dic 2025" },
        { nombre: "Presentación Ciencias", tipo: "PowerPoint", tamaño: "8.2 MB", fecha: "08 Dic 2025" },
        { nombre: "Ejercicios de Gramática", tipo: "PDF", tamaño: "1.8 MB", fecha: "05 Dic 2025" },
    ];

    return (
        <div className="p-8">
            <div className="mb-8 flex items-center justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-slate-900">Materiales</h1>
                    <p className="text-slate-600 mt-2">Recursos didácticos para tus clases</p>
                </div>
                <Button>
                    <Plus className="h-4 w-4 mr-2" />
                    Subir Material
                </Button>
            </div>

            <div className="grid gap-4">
                {materiales.map((material, index) => (
                    <Card key={index}>
                        <CardHeader>
                            <div className="flex items-start justify-between">
                                <div className="flex items-center gap-3">
                                    <FileText className="h-6 w-6 text-primary" />
                                    <div>
                                        <CardTitle>{material.nombre}</CardTitle>
                                        <CardDescription>
                                            {material.tipo} • {material.tamaño} • {material.fecha}
                                        </CardDescription>
                                    </div>
                                </div>
                                <Button variant="outline" size="sm">
                                    <Download className="h-4 w-4 mr-2" />
                                    Descargar
                                </Button>
                            </div>
                        </CardHeader>
                    </Card>
                ))}
            </div>
        </div>
    );
}
