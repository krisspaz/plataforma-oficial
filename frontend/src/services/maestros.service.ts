import { api } from './api';
import type {
    Activity,
    StudentGrade,
    CourseMaterial,
} from '@/types/modules.types';

export const maestrosService = {
    // Actividades
    createActivity: async (activity: Omit<Activity, 'id'>): Promise<Activity> => {
        return api.post<Activity>('/maestros/activities', activity);
    },

    getMyActivities: async (): Promise<Activity[]> => {
        return api.get<Activity[]>('/maestros/activities');
    },

    updateActivity: async (id: number, activity: Partial<Activity>): Promise<Activity> => {
        return api.put<Activity>(`/maestros/activities/${id}`, activity);
    },

    deleteActivity: async (id: number): Promise<void> => {
        return api.delete<void>(`/maestros/activities/${id}`);
    },

    // Notas
    submitGrades: async (grades: Omit<StudentGrade, 'id'>[]): Promise<StudentGrade[]> => {
        return api.post<StudentGrade[]>('/maestros/grades/submit', { grades });
    },

    getActivityGrades: async (activityId: number): Promise<StudentGrade[]> => {
        return api.get<StudentGrade[]>(`/maestros/grades/activity/${activityId}`);
    },

    getFinalGrades: async (subjectId: number, gradeId: number, sectionId: number): Promise<StudentGrade[]> => {
        return api.get<StudentGrade[]>(`/maestros/grades/final?subjectId=${subjectId}&gradeId=${gradeId}&sectionId=${sectionId}`);
    },

    // Contenido
    uploadMaterial: async (material: Omit<CourseMaterial, 'id' | 'uploadDate'>): Promise<CourseMaterial> => {
        return api.post<CourseMaterial>('/maestros/materials', material);
    },

    getMaterials: async (subjectId: number): Promise<CourseMaterial[]> => {
        return api.get<CourseMaterial[]>(`/maestros/materials?subjectId=${subjectId}`);
    },

    deleteMaterial: async (id: number): Promise<void> => {
        return api.delete<void>(`/maestros/materials/${id}`);
    },
};
