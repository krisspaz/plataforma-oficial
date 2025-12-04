import { api } from './api';

export interface GradeRecord {
    id: string;
    studentId: number;
    studentName: string;
    subjectId: number;
    subjectName: string;
    bimester: number;
    bimesterName: string;
    grade: number;
    letterGrade: string;
    isPassing: boolean;
    comments: string | null;
    recordedAt: string;
    teacherName: string;
}

export interface RecordGradeRequest {
    student_id: number;
    subject_id: number;
    teacher_id: number;
    bimester: number;
    academic_year?: number;
    grade: number;
    comments?: string;
}

export interface BulkGradeEntry {
    student_id: number;
    grade: number;
    comments?: string;
}

export interface BulkGradeRequest {
    teacher_id: number;
    subject_id: number;
    bimester: number;
    academic_year?: number;
    grades: BulkGradeEntry[];
}

class GradesService {
    private baseUrl = '/api/grades';

    async recordGrade(data: RecordGradeRequest): Promise<void> {
        await api.post(this.baseUrl, data);
    }

    async bulkRecordGrades(data: BulkGradeRequest): Promise<{ recorded: number; errors: any[] }> {
        const response = await api.post<{ success: boolean; recorded: number; errors: any[] }>(
            `${this.baseUrl}/bulk`,
            data
        );
        return { recorded: response.data.recorded, errors: response.data.errors };
    }

    async getStudentGrades(studentId: number, bimester?: number, year?: number): Promise<GradeRecord[]> {
        const params = new URLSearchParams();
        if (bimester) params.append('bimester', bimester.toString());
        if (year) params.append('year', year.toString());

        const queryString = params.toString();
        const response = await api.get<{ success: boolean; data: GradeRecord[] }>(
            `${this.baseUrl}/student/${studentId}${queryString ? '?' + queryString : ''}`
        );
        return response.data.data;
    }

    async closeBimester(gradeId: number, bimester: number, academicYear?: number): Promise<void> {
        await api.post(`${this.baseUrl}/bimester/close`, {
            grade_id: gradeId,
            bimester,
            academic_year: academicYear
        });
    }

    // Helper methods
    getGradeColor(grade: number): string {
        if (grade >= 90) return 'text-green-600';
        if (grade >= 80) return 'text-blue-600';
        if (grade >= 70) return 'text-yellow-600';
        if (grade >= 60) return 'text-orange-600';
        return 'text-red-600';
    }

    getGradeBgColor(grade: number): string {
        if (grade >= 90) return 'bg-green-100';
        if (grade >= 80) return 'bg-blue-100';
        if (grade >= 70) return 'bg-yellow-100';
        if (grade >= 60) return 'bg-orange-100';
        return 'bg-red-100';
    }

    getBimesterLabel(bimester: number): string {
        const labels: Record<number, string> = {
            1: 'Primer Bimestre',
            2: 'Segundo Bimestre',
            3: 'Tercer Bimestre',
            4: 'Cuarto Bimestre',
        };
        return labels[bimester] || `Bimestre ${bimester}`;
    }

    calculateAverage(grades: GradeRecord[]): number {
        if (grades.length === 0) return 0;
        const sum = grades.reduce((acc, g) => acc + g.grade, 0);
        return Math.round((sum / grades.length) * 100) / 100;
    }
}

export const gradesService = new GradesService();
