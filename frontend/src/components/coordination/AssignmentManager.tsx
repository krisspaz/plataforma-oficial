import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Plus, Users, BookOpen, Loader2, Trash2 } from 'lucide-react';
import { coordinationService, Assignment } from '@/services/coordination.service';
import { toast } from 'sonner';

interface Teacher {
    id: number;
    name: string;
}

interface Subject {
    id: number;
    name: string;
    code: string;
}

interface Grade {
    id: number;
    name: string;
}

interface Section {
    id: number;
    name: string;
}

export const AssignmentManager: React.FC = () => {
    const [assignments, setAssignments] = useState<Assignment[]>([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [selectedTeacher, setSelectedTeacher] = useState<number | null>(null);
    const [submitting, setSubmitting] = useState(false);

    // Mock data - in real app, fetch from API
    const [teachers] = useState<Teacher[]>([
        { id: 1, name: 'María García' },
        { id: 2, name: 'Juan López' },
        { id: 3, name: 'Ana Martínez' },
    ]);

    const [subjects] = useState<Subject[]>([
        { id: 1, name: 'Matemáticas', code: 'MAT' },
        { id: 2, name: 'Español', code: 'ESP' },
        { id: 3, name: 'Ciencias', code: 'CIE' },
    ]);

    const [grades] = useState<Grade[]>([
        { id: 1, name: 'Primero Básico' },
        { id: 2, name: 'Segundo Básico' },
        { id: 3, name: 'Tercero Básico' },
    ]);

    const [sections] = useState<Section[]>([
        { id: 1, name: 'A' },
        { id: 2, name: 'B' },
    ]);

    // Form state
    const [formData, setFormData] = useState({
        teacherId: '',
        subjectId: '',
        gradeId: '',
        sectionId: '',
    });

    useEffect(() => {
        loadAssignments();
    }, [selectedTeacher]);

    const loadAssignments = async () => {
        if (!selectedTeacher) {
            setAssignments([]);
            setLoading(false);
            return;
        }

        try {
            const data = await coordinationService.getTeacherAssignments(selectedTeacher);
            setAssignments(data);
        } catch (error) {
            toast.error('Error al cargar asignaciones');
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async () => {
        if (!formData.teacherId || !formData.subjectId || !formData.gradeId || !formData.sectionId) {
            toast.error('Complete todos los campos');
            return;
        }

        setSubmitting(true);
        try {
            await coordinationService.createAssignment({
                teacher_id: parseInt(formData.teacherId),
                subject_id: parseInt(formData.subjectId),
                grade_id: parseInt(formData.gradeId),
                section_id: parseInt(formData.sectionId),
            });

            toast.success('Asignación creada exitosamente');
            setShowModal(false);
            setFormData({ teacherId: '', subjectId: '', gradeId: '', sectionId: '' });
            loadAssignments();
        } catch (error) {
            toast.error('Error al crear asignación');
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold flex items-center gap-2">
                        <Users className="w-6 h-6" />
                        Asignaciones de Maestros
                    </h2>
                    <p className="text-slate-500">Gestiona las asignaciones de materias por grado y sección</p>
                </div>
                <Button onClick={() => setShowModal(true)}>
                    <Plus className="w-4 h-4 mr-2" />
                    Nueva Asignación
                </Button>
            </div>

            {/* Filter by Teacher */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-sm font-medium">Filtrar por Maestro</CardTitle>
                </CardHeader>
                <CardContent>
                    <Select
                        value={selectedTeacher?.toString() || ''}
                        onValueChange={(val) => setSelectedTeacher(parseInt(val))}
                    >
                        <SelectTrigger className="w-64">
                            <SelectValue placeholder="Seleccione un maestro" />
                        </SelectTrigger>
                        <SelectContent>
                            {teachers.map((teacher) => (
                                <SelectItem key={teacher.id} value={teacher.id.toString()}>
                                    {teacher.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </CardContent>
            </Card>

            {/* Assignments Table */}
            <Card>
                <CardContent className="p-0">
                    {loading ? (
                        <div className="flex justify-center items-center h-48">
                            <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
                        </div>
                    ) : !selectedTeacher ? (
                        <div className="text-center py-12 text-slate-500">
                            <BookOpen className="w-12 h-12 mx-auto mb-4 opacity-50" />
                            <p>Seleccione un maestro para ver sus asignaciones</p>
                        </div>
                    ) : assignments.length === 0 ? (
                        <div className="text-center py-12 text-slate-500">
                            <p>No hay asignaciones para este maestro</p>
                        </div>
                    ) : (
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Materia</TableHead>
                                    <TableHead>Grado</TableHead>
                                    <TableHead>Sección</TableHead>
                                    <TableHead>Año</TableHead>
                                    <TableHead className="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {assignments.map((assignment) => (
                                    <TableRow key={assignment.id}>
                                        <TableCell className="font-medium">{assignment.subjectName}</TableCell>
                                        <TableCell>{assignment.gradeName}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{assignment.sectionName}</Badge>
                                        </TableCell>
                                        <TableCell>{assignment.academicYear}</TableCell>
                                        <TableCell className="text-right">
                                            <Button variant="ghost" size="sm" className="text-red-500 hover:text-red-700">
                                                <Trash2 className="w-4 h-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </CardContent>
            </Card>

            {/* Create Assignment Modal */}
            <Dialog open={showModal} onOpenChange={setShowModal}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Nueva Asignación</DialogTitle>
                    </DialogHeader>

                    <div className="space-y-4 py-4">
                        <div className="space-y-2">
                            <Label>Maestro</Label>
                            <Select
                                value={formData.teacherId}
                                onValueChange={(val) => setFormData({ ...formData, teacherId: val })}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccione maestro" />
                                </SelectTrigger>
                                <SelectContent>
                                    {teachers.map((t) => (
                                        <SelectItem key={t.id} value={t.id.toString()}>
                                            {t.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label>Materia</Label>
                            <Select
                                value={formData.subjectId}
                                onValueChange={(val) => setFormData({ ...formData, subjectId: val })}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccione materia" />
                                </SelectTrigger>
                                <SelectContent>
                                    {subjects.map((s) => (
                                        <SelectItem key={s.id} value={s.id.toString()}>
                                            {s.code} - {s.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label>Grado</Label>
                                <Select
                                    value={formData.gradeId}
                                    onValueChange={(val) => setFormData({ ...formData, gradeId: val })}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Grado" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {grades.map((g) => (
                                            <SelectItem key={g.id} value={g.id.toString()}>
                                                {g.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label>Sección</Label>
                                <Select
                                    value={formData.sectionId}
                                    onValueChange={(val) => setFormData({ ...formData, sectionId: val })}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sección" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {sections.map((s) => (
                                            <SelectItem key={s.id} value={s.id.toString()}>
                                                {s.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowModal(false)}>
                            Cancelar
                        </Button>
                        <Button onClick={handleSubmit} disabled={submitting}>
                            {submitting && <Loader2 className="w-4 h-4 mr-2 animate-spin" />}
                            Crear Asignación
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
};
