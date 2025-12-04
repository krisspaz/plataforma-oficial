import { api } from './api';
import type {
    Announcement,
    Teacher,
    SubjectAssignment,
    GradeReport,
    ReportCard,
} from '@/types/modules.types';

export const coordinacionService = {
    // Anuncios
    createAnnouncement: async (announcement: Omit<Announcement, 'id'>): Promise<Announcement> => {
        return api.post<Announcement>('/coordinacion/announcements', announcement);
    },

    getAnnouncements: async (): Promise<Announcement[]> => {
        return api.get<Announcement[]>('/coordinacion/announcements');
    },

    updateAnnouncement: async (id: number, announcement: Partial<Announcement>): Promise<Announcement> => {
        return api.put<Announcement>(`/coordinacion/announcements/${id}`, announcement);
    },

    deleteAnnouncement: async (id: number): Promise<void> => {
        return api.delete<void>(`/coordinacion/announcements/${id}`);
    },

    // Profesores
    getTeachers: async (): Promise<Teacher[]> => {
        return api.get<Teacher[]>('/coordinacion/teachers');
    },

    createTeacher: async (teacher: Omit<Teacher, 'id'>): Promise<Teacher> => {
        return api.post<Teacher>('/coordinacion/teachers', teacher);
    },

    updateTeacher: async (id: number, teacher: Partial<Teacher>): Promise<Teacher> => {
        return api.put<Teacher>(`/coordinacion/teachers/${id}`, teacher);
    },

    getTeacherBirthdays: async (): Promise<Teacher[]> => {
        return api.get<Teacher[]>('/coordinacion/teachers/birthdays');
    },

    // Asignación de materias
    assignSubject: async (assignment: Omit<SubjectAssignment, 'id'>): Promise<SubjectAssignment> => {
        return api.post<SubjectAssignment>('/coordinacion/subject-assignments', assignment);
    },

    getSubjectAssignments: async (): Promise<SubjectAssignment[]> => {
        return api.get<SubjectAssignment[]>('/coordinacion/subject-assignments');
    },

    // Notas
    downloadGrades: async (gradeId: number, bimester: number): Promise<GradeReport[]> => {
        return api.get<GradeReport[]>(`/coordinacion/grades/download?gradeId=${gradeId}&bimester=${bimester}`);
    },

    generateReportCard: async (studentId: number, bimester: number): Promise<{ pdfUrl: string }> => {
        return api.post<{ pdfUrl: string }>('/coordinacion/report-cards/generate', { studentId, bimester });
    },

    closeBimester: async (bimester: number, academicYear: number): Promise<void> => {
        return api.post<void>('/coordinacion/bimester/close', { bimester, academicYear });
    },

    requestEditPermission: async (bimester: number, reason: string): Promise<void> => {
        return api.post<void>('/coordinacion/bimester/request-permission', { bimester, reason });
    },
};
