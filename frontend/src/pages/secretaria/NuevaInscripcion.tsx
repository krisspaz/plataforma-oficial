import { useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useNavigate } from 'react-router-dom';
import { secretariaService } from "@/services/secretaria.service";
import { errorHandler } from "@/lib/errorHandler";
import { toast } from 'sonner';
import { Loader2, ArrowLeft } from 'lucide-react';

export const NuevaInscripcion = () => {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [formData, setFormData] = useState({
        studentId: '',
        gradeId: '',
        sectionId: '',
        academicYear: new Date().getFullYear().toString(),
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);

        try {
            await secretariaService.createEnrollment({
                studentId: parseInt(formData.studentId),
                gradeId: parseInt(formData.gradeId),
                sectionId: parseInt(formData.sectionId),
                academicYear: parseInt(formData.academicYear),
                enrollmentDate: new Date().toISOString(),
                status: 'ACTIVE',
            });

            toast.success('Inscripción creada exitosamente');
            navigate('/secretaria');
        } catch (error) {
            errorHandler.handleApiError(error, 'Error al crear la inscripción');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex min-h-screen bg-background">
            <Sidebar />

            <main className="flex-1 ml-64 p-8">
                <div className="max-w-2xl mx-auto">
                    <Button variant="ghost" onClick={() => navigate('/secretaria')} className="mb-4">
                        <ArrowLeft className="w-4 h-4 mr-2" />
                        Volver
                    </Button>

                    <Card className="p-6">
                        <h1 className="text-2xl font-bold mb-6">Nueva Inscripción</h1>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="studentId">ID del Estudiante</Label>
                                <Input
                                    id="studentId"
                                    type="number"
                                    value={formData.studentId}
                                    onChange={(e) => setFormData({ ...formData, studentId: e.target.value })}
                                    placeholder="Ingrese el ID del estudiante"
                                    required
                                />
                                <p className="text-sm text-muted-foreground mt-1">
                                    Si es un estudiante nuevo, primero debe crearlo en el sistema
                                </p>
                            </div>

                            <div>
                                <Label htmlFor="gradeId">Grado</Label>
                                <Select value={formData.gradeId} onValueChange={(value) => setFormData({ ...formData, gradeId: value })}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Seleccione un grado" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">Preprimaria</SelectItem>
                                        <SelectItem value="2">Primero Primaria</SelectItem>
                                        <SelectItem value="3">Segundo Primaria</SelectItem>
                                        <SelectItem value="4">Tercero Primaria</SelectItem>
                                        <SelectItem value="5">Cuarto Primaria</SelectItem>
                                        <SelectItem value="6">Quinto Primaria</SelectItem>
                                        <SelectItem value="7">Sexto Primaria</SelectItem>
                                        <SelectItem value="8">Primero Básico</SelectItem>
                                        <SelectItem value="9">Segundo Básico</SelectItem>
                                        <SelectItem value="10">Tercero Básico</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label htmlFor="sectionId">Sección</Label>
                                <Select value={formData.sectionId} onValueChange={(value) => setFormData({ ...formData, sectionId: value })}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Seleccione una sección" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="1">Sección A</SelectItem>
                                        <SelectItem value="2">Sección B</SelectItem>
                                        <SelectItem value="3">Sección C</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label htmlFor="academicYear">Año Académico</Label>
                                <Input
                                    id="academicYear"
                                    type="number"
                                    value={formData.academicYear}
                                    onChange={(e) => setFormData({ ...formData, academicYear: e.target.value })}
                                    required
                                />
                            </div>

                            <div className="flex gap-2 pt-4">
                                <Button type="submit" disabled={loading} className="flex-1">
                                    {loading ? (
                                        <>
                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                            Procesando...
                                        </>
                                    ) : (
                                        'Crear Inscripción'
                                    )}
                                </Button>
                                <Button type="button" variant="outline" onClick={() => navigate('/secretaria')}>
                                    Cancelar
                                </Button>
                            </div>
                        </form>
                    </Card>
                </div>
            </main>
        </div>
    );
};
