import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    GraduationCap,
    Save,
    Loader2,
    CheckCircle,
    AlertCircle,
    BookOpen,
    Users,
    Lock
} from 'lucide-react';
import { gradesService, GradeRecord } from '@/services/grades.service';
import { toast } from 'sonner';

interface Student {
    id: number;
    name: string;
    grade?: number;
    comments?: string;
}

export const GradeEntry: React.FC = () => {
    const [selectedBimester, setSelectedBimester] = useState<number>(1);
    const [selectedSubject, setSelectedSubject] = useState<string>('');
    const [selectedSection, setSelectedSection] = useState<string>('');
    const [students, setStudents] = useState<Student[]>([]);
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [showConfirmModal, setShowConfirmModal] = useState(false);

    // Mock data - in real app, fetch from API
    const subjects = [
        { id: 1, name: 'Matemáticas', code: 'MAT' },
        { id: 2, name: 'Español', code: 'ESP' },
        { id: 3, name: 'Ciencias', code: 'CIE' },
    ];

    const sections = [
        { id: 1, grade: 'Primero Básico', section: 'A' },
        { id: 2, grade: 'Primero Básico', section: 'B' },
        { id: 3, grade: 'Segundo Básico', section: 'A' },
    ];

    // Mock students - in real app, fetch based on section
    useEffect(() => {
        if (selectedSection) {
            setStudents([
                { id: 1, name: 'Ana García López', grade: undefined, comments: '' },
                { id: 2, name: 'Carlos Martínez', grade: undefined, comments: '' },
                { id: 3, name: 'Diana Pérez', grade: undefined, comments: '' },
                { id: 4, name: 'Eduardo Rodríguez', grade: undefined, comments: '' },
                { id: 5, name: 'Fernanda Castillo', grade: undefined, comments: '' },
            ]);
        }
    }, [selectedSection]);

    const handleGradeChange = (studentId: number, value: string) => {
        const numValue = parseFloat(value);
        if (isNaN(numValue) || numValue < 0 || numValue > 100) return;

        setStudents(prev => prev.map(s =>
            s.id === studentId ? { ...s, grade: numValue } : s
        ));
    };

    const handleCommentsChange = (studentId: number, value: string) => {
        setStudents(prev => prev.map(s =>
            s.id === studentId ? { ...s, comments: value } : s
        ));
    };

    const handleSaveGrades = async () => {
        const gradesToSave = students.filter(s => s.grade !== undefined && s.grade !== null);

        if (gradesToSave.length === 0) {
            toast.error('No hay calificaciones para guardar');
            return;
        }

        setSaving(true);
        try {
            await gradesService.bulkRecordGrades({
                teacher_id: 1, // From auth context in real app
                subject_id: parseInt(selectedSubject),
                bimester: selectedBimester,
                grades: gradesToSave.map(s => ({
                    student_id: s.id,
                    grade: s.grade!,
                    comments: s.comments
                }))
            });

            toast.success(`${gradesToSave.length} calificaciones guardadas`);
        } catch (error) {
            toast.error('Error al guardar calificaciones');
        } finally {
            setSaving(false);
        }
    };

    const getInputBorderColor = (grade?: number) => {
        if (grade === undefined) return 'border-slate-200';
        if (grade >= 60) return 'border-green-400';
        return 'border-red-400';
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold flex items-center gap-2">
                        <GraduationCap className="w-6 h-6" />
                        Registro de Calificaciones
                    </h2>
                    <p className="text-slate-500">Ingresa las calificaciones por materia y bimestre</p>
                </div>
            </div>

            {/* Filters */}
            <Card>
                <CardContent className="pt-6">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="space-y-2">
                            <Label>Bimestre</Label>
                            <Select value={selectedBimester.toString()} onValueChange={(v) => setSelectedBimester(parseInt(v))}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1">Primer Bimestre</SelectItem>
                                    <SelectItem value="2">Segundo Bimestre</SelectItem>
                                    <SelectItem value="3">Tercer Bimestre</SelectItem>
                                    <SelectItem value="4">Cuarto Bimestre</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label>Materia</Label>
                            <Select value={selectedSubject} onValueChange={setSelectedSubject}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar materia" />
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

                        <div className="space-y-2">
                            <Label>Sección</Label>
                            <Select value={selectedSection} onValueChange={setSelectedSection}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar sección" />
                                </SelectTrigger>
                                <SelectContent>
                                    {sections.map((s) => (
                                        <SelectItem key={s.id} value={s.id.toString()}>
                                            {s.grade} - {s.section}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Grade Entry Table */}
            {selectedSubject && selectedSection ? (
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="flex items-center gap-2">
                            <Users className="w-5 h-5" />
                            Lista de Estudiantes
                        </CardTitle>
                        <Button onClick={handleSaveGrades} disabled={saving}>
                            {saving ? (
                                <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                            ) : (
                                <Save className="w-4 h-4 mr-2" />
                            )}
                            Guardar Calificaciones
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-12">#</TableHead>
                                    <TableHead>Estudiante</TableHead>
                                    <TableHead className="w-32">Calificación</TableHead>
                                    <TableHead className="w-16">Letra</TableHead>
                                    <TableHead>Comentarios</TableHead>
                                    <TableHead className="w-24">Estado</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {students.map((student, index) => (
                                    <TableRow key={student.id}>
                                        <TableCell className="font-medium text-slate-500">
                                            {index + 1}
                                        </TableCell>
                                        <TableCell className="font-medium">{student.name}</TableCell>
                                        <TableCell>
                                            <Input
                                                type="number"
                                                min="0"
                                                max="100"
                                                step="0.5"
                                                value={student.grade ?? ''}
                                                onChange={(e) => handleGradeChange(student.id, e.target.value)}
                                                className={`w-24 text-center ${getInputBorderColor(student.grade)}`}
                                                placeholder="0-100"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            {student.grade !== undefined && (
                                                <Badge className={gradesService.getGradeBgColor(student.grade)}>
                                                    {student.grade >= 90 ? 'A' :
                                                        student.grade >= 80 ? 'B' :
                                                            student.grade >= 70 ? 'C' :
                                                                student.grade >= 60 ? 'D' : 'F'}
                                                </Badge>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <Input
                                                value={student.comments || ''}
                                                onChange={(e) => handleCommentsChange(student.id, e.target.value)}
                                                placeholder="Observaciones..."
                                                className="text-sm"
                                            />
                                        </TableCell>
                                        <TableCell>
                                            {student.grade !== undefined && (
                                                student.grade >= 60 ? (
                                                    <span className="flex items-center gap-1 text-green-600 text-sm">
                                                        <CheckCircle className="w-4 h-4" />
                                                        Aprobado
                                                    </span>
                                                ) : (
                                                    <span className="flex items-center gap-1 text-red-600 text-sm">
                                                        <AlertCircle className="w-4 h-4" />
                                                        Reprobado
                                                    </span>
                                                )
                                            )}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Summary */}
                        <div className="mt-6 flex justify-between items-center p-4 bg-slate-50 rounded-lg">
                            <div className="flex gap-8">
                                <div>
                                    <span className="text-sm text-slate-500">Total Estudiantes</span>
                                    <p className="font-bold">{students.length}</p>
                                </div>
                                <div>
                                    <span className="text-sm text-slate-500">Calificados</span>
                                    <p className="font-bold text-blue-600">
                                        {students.filter(s => s.grade !== undefined).length}
                                    </p>
                                </div>
                                <div>
                                    <span className="text-sm text-slate-500">Promedio</span>
                                    <p className="font-bold">
                                        {gradesService.calculateAverage(
                                            students.filter(s => s.grade !== undefined).map(s => ({
                                                grade: s.grade!
                                            })) as any
                                        ).toFixed(1)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            ) : (
                <Card className="text-center py-12">
                    <CardContent>
                        <BookOpen className="w-12 h-12 mx-auto mb-4 text-slate-300" />
                        <p className="text-slate-500">
                            Selecciona una materia y sección para comenzar
                        </p>
                    </CardContent>
                </Card>
            )}
        </div>
    );
};

export default GradeEntry;
