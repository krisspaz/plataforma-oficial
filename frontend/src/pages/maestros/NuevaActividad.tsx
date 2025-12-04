import { useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Card } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useNavigate } from 'react-router-dom';
import { maestrosService } from "@/services/maestros.service";
import { errorHandler } from "@/lib/errorHandler";
import { toast } from 'sonner';
import { Loader2, ArrowLeft } from 'lucide-react';
import { useAuth } from "@/context/AuthContext";

export const NuevaActividad = () => {
    const navigate = useNavigate();
    const { user } = useAuth();
    const [loading, setLoading] = useState(false);
    const [formData, setFormData] = useState({
        subjectId: '',
        gradeId: '',
        sectionId: '',
        title: '',
        description: '',
        activityType: 'TAREA' as 'TAREA' | 'EXAMEN' | 'PROYECTO' | 'LABORATORIO',
        dueDate: '',
        maxGrade: '100',
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);

        try {
            await maestrosService.createActivity({
                teacherId: user?.id || 0,
                subjectId: parseInt(formData.subjectId),
                gradeId: parseInt(formData.gradeId),
                sectionId: parseInt(formData.sectionId),
                title: formData.title,
                description: formData.description,
                activityType: formData.activityType,
                dueDate: formData.dueDate,
                maxGrade: parseInt(formData.maxGrade),
            });

            toast.success('Actividad creada exitosamente');
            navigate('/maestros');
        } catch (error) {
            errorHandler.handleApiError(error, 'Error al crear la actividad');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex min-h-screen bg-background">
            <Sidebar />

            <main className="flex-1 ml-64 p-8">
                <div className="max-w-2xl mx-auto">
                    <Button variant="ghost" onClick={() => navigate('/maestros')} className="mb-4">
                        <ArrowLeft className="w-4 h-4 mr-2" />
                        Volver
                    </Button>

                    <Card className="p-6">
                        <h1 className="text-2xl font-bold mb-6">Nueva Actividad</h1>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="title">Título de la Actividad</Label>
                                <Input
                                    id="title"
                                    value={formData.title}
                                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                                    placeholder="Ej: Tarea de Matemáticas - Capítulo 5"
                                    required
                                />
                            </div>

                            <div>
                                <Label htmlFor="description">Descripción</Label>
                                <Textarea
                                    id="description"
                                    value={formData.description}
                                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                    placeholder="Describe la actividad, instrucciones, etc."
                                    rows={4}
                                    required
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="activityType">Tipo de Actividad</Label>
                                    <Select value={formData.activityType} onValueChange={(value: 'TAREA' | 'EXAMEN' | 'PROYECTO' | 'LABORATORIO') => setFormData({ ...formData, activityType: value })}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="TAREA">Tarea</SelectItem>
                                            <SelectItem value="EXAMEN">Examen</SelectItem>
                                            <SelectItem value="PROYECTO">Proyecto</SelectItem>
                                            <SelectItem value="LABORATORIO">Laboratorio</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <Label htmlFor="maxGrade">Punteo Máximo</Label>
                                    <Input
                                        id="maxGrade"
                                        type="number"
                                        value={formData.maxGrade}
                                        onChange={(e) => setFormData({ ...formData, maxGrade: e.target.value })}
                                        required
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-3 gap-4">
                                <div>
                                    <Label htmlFor="subjectId">Materia</Label>
                                    <Select value={formData.subjectId} onValueChange={(value) => setFormData({ ...formData, subjectId: value })}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccione" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">Matemáticas</SelectItem>
                                            <SelectItem value="2">Lenguaje</SelectItem>
                                            <SelectItem value="3">Ciencias</SelectItem>
                                            <SelectItem value="4">Estudios Sociales</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <Label htmlFor="gradeId">Grado</Label>
                                    <Select value={formData.gradeId} onValueChange={(value) => setFormData({ ...formData, gradeId: value })}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccione" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">Primero</SelectItem>
                                            <SelectItem value="2">Segundo</SelectItem>
                                            <SelectItem value="3">Tercero</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div>
                                    <Label htmlFor="sectionId">Sección</Label>
                                    <Select value={formData.sectionId} onValueChange={(value) => setFormData({ ...formData, sectionId: value })}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Seleccione" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">A</SelectItem>
                                            <SelectItem value="2">B</SelectItem>
                                            <SelectItem value="3">C</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div>
                                <Label htmlFor="dueDate">Fecha de Entrega</Label>
                                <Input
                                    id="dueDate"
                                    type="datetime-local"
                                    value={formData.dueDate}
                                    onChange={(e) => setFormData({ ...formData, dueDate: e.target.value })}
                                    required
                                />
                            </div>

                            <div className="flex gap-2 pt-4">
                                <Button type="submit" disabled={loading} className="flex-1">
                                    {loading ? (
                                        <>
                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                            Creando...
                                        </>
                                    ) : (
                                        'Crear Actividad'
                                    )}
                                </Button>
                                <Button type="button" variant="outline" onClick={() => navigate('/maestros')}>
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
