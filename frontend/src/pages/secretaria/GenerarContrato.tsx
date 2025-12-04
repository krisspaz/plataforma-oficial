import { useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card } from "@/components/ui/card";
import { useNavigate } from 'react-router-dom';
import { secretariaService } from "@/services/secretaria.service";
import { errorHandler } from "@/lib/errorHandler";
import { toast } from 'sonner';
import { Loader2, ArrowLeft, Download } from 'lucide-react';

export const GenerarContrato = () => {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [pdfUrl, setPdfUrl] = useState<string | null>(null);
    const [formData, setFormData] = useState({
        studentId: '',
        studentName: '',
        fatherName: '',
        fatherDPI: '',
        fatherProfession: '',
        fatherNationality: 'Guatemalteco',
        motherName: '',
        motherDPI: '',
        motherProfession: '',
        motherNationality: 'Guatemalteco',
        resolutionNumber: '',
        installments: '10',
        installmentAmount: '',
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);

        try {
            const response = await secretariaService.generateContract({
                studentId: parseInt(formData.studentId),
                studentName: formData.studentName,
                fatherName: formData.fatherName,
                fatherDPI: formData.fatherDPI,
                fatherProfession: formData.fatherProfession,
                fatherNationality: formData.fatherNationality,
                motherName: formData.motherName,
                motherDPI: formData.motherDPI,
                motherProfession: formData.motherProfession,
                motherNationality: formData.motherNationality,
                resolutionNumber: formData.resolutionNumber,
                installments: parseInt(formData.installments),
                installmentAmount: parseFloat(formData.installmentAmount),
            });

            setPdfUrl(response.pdfUrl);
            toast.success('Contrato generado exitosamente');
        } catch (error) {
            errorHandler.handleApiError(error, 'Error al generar el contrato');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex min-h-screen bg-background">
            <Sidebar />

            <main className="flex-1 ml-64 p-8">
                <div className="max-w-4xl mx-auto">
                    <Button variant="ghost" onClick={() => navigate('/secretaria')} className="mb-4">
                        <ArrowLeft className="w-4 h-4 mr-2" />
                        Volver
                    </Button>

                    <Card className="p-6">
                        <h1 className="text-2xl font-bold mb-6">Generar Contrato de Inscripción</h1>

                        {pdfUrl ? (
                            <div className="text-center py-8">
                                <div className="w-16 h-16 rounded-full bg-success/10 flex items-center justify-center mx-auto mb-4">
                                    <Download className="w-8 h-8 text-success" />
                                </div>
                                <h3 className="text-xl font-semibold mb-2">¡Contrato Generado!</h3>
                                <p className="text-muted-foreground mb-6">El contrato ha sido generado exitosamente</p>
                                <div className="flex gap-2 justify-center">
                                    <Button onClick={() => window.open(pdfUrl, '_blank')}>
                                        <Download className="w-4 h-4 mr-2" />
                                        Descargar PDF
                                    </Button>
                                    <Button variant="outline" onClick={() => setPdfUrl(null)}>
                                        Generar Otro
                                    </Button>
                                </div>
                            </div>
                        ) : (
                            <form onSubmit={handleSubmit} className="space-y-6">
                                {/* Datos del Estudiante */}
                                <div>
                                    <h3 className="text-lg font-semibold mb-4">Datos del Estudiante</h3>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label htmlFor="studentId">ID del Estudiante</Label>
                                            <Input
                                                id="studentId"
                                                type="number"
                                                value={formData.studentId}
                                                onChange={(e) => setFormData({ ...formData, studentId: e.target.value })}
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="studentName">Nombre Completo</Label>
                                            <Input
                                                id="studentName"
                                                value={formData.studentName}
                                                onChange={(e) => setFormData({ ...formData, studentName: e.target.value })}
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Datos del Padre */}
                                <div>
                                    <h3 className="text-lg font-semibold mb-4">Datos del Padre</h3>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label htmlFor="fatherName">Nombre Completo</Label>
                                            <Input
                                                id="fatherName"
                                                value={formData.fatherName}
                                                onChange={(e) => setFormData({ ...formData, fatherName: e.target.value })}
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="fatherDPI">Número de DPI</Label>
                                            <Input
                                                id="fatherDPI"
                                                value={formData.fatherDPI}
                                                onChange={(e) => setFormData({ ...formData, fatherDPI: e.target.value })}
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="fatherProfession">Profesión</Label>
                                            <Input
                                                id="fatherProfession"
                                                value={formData.fatherProfession}
                                                onChange={(e) => setFormData({ ...formData, fatherProfession: e.target.value })}
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="fatherNationality">Nacionalidad</Label>
                                            <Input
                                                id="fatherNationality"
                                                value={formData.fatherNationality}
                                                onChange={(e) => setFormData({ ...formData, fatherNationality: e.target.value })}
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Datos de la Madre */}
                                <div>
                                    <h3 className="text-lg font-semibold mb-4">Datos de la Madre</h3>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <Label htmlFor="motherName">Nombre Completo</Label>
                                            <Input
                                                id="motherName"
                                                value={formData.motherName}
                                                onChange={(e) => setFormData({ ...formData, motherName: e.target.value })}
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="motherDPI">Número de DPI</Label>
                                            <Input
                                                id="motherDPI"
                                                value={formData.motherDPI}
                                                onChange={(e) => setFormData({ ...formData, motherDPI: e.target.value })}
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="motherProfession">Profesión</Label>
                                            <Input
                                                id="motherProfession"
                                                value={formData.motherProfession}
                                                onChange={(e) => setFormData({ ...formData, motherProfession: e.target.value })}
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="motherNationality">Nacionalidad</Label>
                                            <Input
                                                id="motherNationality"
                                                value={formData.motherNationality}
                                                onChange={(e) => setFormData({ ...formData, motherNationality: e.target.value })}
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Datos del Contrato */}
                                <div>
                                    <h3 className="text-lg font-semibold mb-4">Datos del Contrato</h3>
                                    <div className="grid grid-cols-3 gap-4">
                                        <div>
                                            <Label htmlFor="resolutionNumber">Número de Resolución</Label>
                                            <Input
                                                id="resolutionNumber"
                                                value={formData.resolutionNumber}
                                                onChange={(e) => setFormData({ ...formData, resolutionNumber: e.target.value })}
                                                placeholder="Ej: RES-2024-001"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="installments">Número de Cuotas</Label>
                                            <Input
                                                id="installments"
                                                type="number"
                                                value={formData.installments}
                                                onChange={(e) => setFormData({ ...formData, installments: e.target.value })}
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor="installmentAmount">Monto por Cuota (Q)</Label>
                                            <Input
                                                id="installmentAmount"
                                                type="number"
                                                step="0.01"
                                                value={formData.installmentAmount}
                                                onChange={(e) => setFormData({ ...formData, installmentAmount: e.target.value })}
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="flex gap-2 pt-4">
                                    <Button type="submit" disabled={loading} className="flex-1">
                                        {loading ? (
                                            <>
                                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                                Generando...
                                            </>
                                        ) : (
                                            'Generar Contrato PDF'
                                        )}
                                    </Button>
                                    <Button type="button" variant="outline" onClick={() => navigate('/secretaria')}>
                                        Cancelar
                                    </Button>
                                </div>
                            </form>
                        )}
                    </Card>
                </div>
            </main>
        </div>
    );
};
