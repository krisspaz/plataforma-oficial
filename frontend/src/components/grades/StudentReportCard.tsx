import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Loader2, FileText, TrendingUp, TrendingDown } from 'lucide-react';
import { gradesService, GradeRecord } from '@/services/grades.service';
import { toast } from 'sonner';

interface StudentReportCardProps {
    studentId: number;
    studentName: string;
}

export const StudentReportCard: React.FC<StudentReportCardProps> = ({ studentId, studentName }) => {
    const [grades, setGrades] = useState<GradeRecord[]>([]);
    const [loading, setLoading] = useState(true);
    const [selectedYear, setSelectedYear] = useState<number>(new Date().getFullYear());

    useEffect(() => {
        loadGrades();
    }, [studentId, selectedYear]);

    const loadGrades = async () => {
        setLoading(true);
        try {
            const data = await gradesService.getStudentGrades(studentId, undefined, selectedYear);
            setGrades(data);
        } catch (error) {
            toast.error('Error al cargar calificaciones');
        } finally {
            setLoading(false);
        }
    };

    // Group grades by subject
    const gradesBySubject = grades.reduce((acc, grade) => {
        if (!acc[grade.subjectName]) {
            acc[grade.subjectName] = { bimesters: {}, subjectId: grade.subjectId };
        }
        acc[grade.subjectName].bimesters[grade.bimester] = grade;
        return acc;
    }, {} as Record<string, { bimesters: Record<number, GradeRecord>; subjectId: number }>);

    const calculateSubjectAverage = (bimesters: Record<number, GradeRecord>): number => {
        const values = Object.values(bimesters).map(g => g.grade);
        if (values.length === 0) return 0;
        return Math.round((values.reduce((a, b) => a + b, 0) / values.length) * 100) / 100;
    };

    const overallAverage = gradesService.calculateAverage(grades);

    if (loading) {
        return (
            <div className="flex justify-center items-center h-64">
                <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex justify-between items-start">
                <div>
                    <h2 className="text-2xl font-bold flex items-center gap-2">
                        <FileText className="w-6 h-6" />
                        Boleta de Calificaciones
                    </h2>
                    <p className="text-slate-500">{studentName}</p>
                </div>
                <Select value={selectedYear.toString()} onValueChange={(v) => setSelectedYear(parseInt(v))}>
                    <SelectTrigger className="w-32">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="2025">2025</SelectItem>
                        <SelectItem value="2024">2024</SelectItem>
                        <SelectItem value="2023">2023</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            {/* Overall Stats */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card className="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                    <CardContent className="pt-6">
                        <p className="text-sm opacity-80">Promedio General</p>
                        <p className="text-4xl font-bold">{overallAverage.toFixed(1)}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="pt-6">
                        <p className="text-sm text-slate-500">Materias Cursadas</p>
                        <p className="text-3xl font-bold">{Object.keys(gradesBySubject).length}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="pt-6">
                        <p className="text-sm text-slate-500">Estado</p>
                        <p className={`text-xl font-bold ${overallAverage >= 60 ? 'text-green-600' : 'text-red-600'}`}>
                            {overallAverage >= 60 ? '✅ Aprobado' : '❌ Reprobado'}
                        </p>
                    </CardContent>
                </Card>
            </div>

            {/* Grades Table */}
            <Card>
                <CardHeader>
                    <CardTitle>Calificaciones por Materia</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Materia</TableHead>
                                <TableHead className="text-center">1er Bim</TableHead>
                                <TableHead className="text-center">2do Bim</TableHead>
                                <TableHead className="text-center">3er Bim</TableHead>
                                <TableHead className="text-center">4to Bim</TableHead>
                                <TableHead className="text-center">Promedio</TableHead>
                                <TableHead className="text-center">Estado</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {Object.entries(gradesBySubject).map(([subjectName, { bimesters }]) => {
                                const avg = calculateSubjectAverage(bimesters);
                                return (
                                    <TableRow key={subjectName}>
                                        <TableCell className="font-medium">{subjectName}</TableCell>
                                        {[1, 2, 3, 4].map((bim) => (
                                            <TableCell key={bim} className="text-center">
                                                {bimesters[bim] ? (
                                                    <span className={gradesService.getGradeColor(bimesters[bim].grade)}>
                                                        {bimesters[bim].grade.toFixed(1)}
                                                    </span>
                                                ) : (
                                                    <span className="text-slate-300">—</span>
                                                )}
                                            </TableCell>
                                        ))}
                                        <TableCell className="text-center">
                                            <Badge className={gradesService.getGradeBgColor(avg)}>
                                                {avg.toFixed(1)}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-center">
                                            {avg >= 60 ? (
                                                <TrendingUp className="w-5 h-5 text-green-500 mx-auto" />
                                            ) : (
                                                <TrendingDown className="w-5 h-5 text-red-500 mx-auto" />
                                            )}
                                        </TableCell>
                                    </TableRow>
                                );
                            })}
                        </TableBody>
                    </Table>

                    {Object.keys(gradesBySubject).length === 0 && (
                        <div className="text-center py-8 text-slate-500">
                            No hay calificaciones registradas para este año
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
};

export default StudentReportCard;
