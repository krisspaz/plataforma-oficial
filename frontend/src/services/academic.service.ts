import { api } from './api';

export interface Subject {
    id: number;
    name: string;
    code: string;
    description: string;
}

export interface Grade {
    id: number;
    name: string;
    level: string;
}

export interface Section {
    id: number;
    name: string;
    grade: Grade;
    capacity: number;
    academicYear: number;
}

export interface Enrollment {
    id: number;
    student: any;
    section: Section;
    status: string;
    enrolledAt: string;
}

export const academicService = {
    getSubjects: async (): Promise<Subject[]> => {
        return api.get<Subject[]>('/subjects');
    },

    getGrades: async (): Promise<Grade[]> => {
        return api.get<Grade[]>('/grades');
    },

    getSections: async (): Promise<Section[]> => {
        return api.get<Section[]>('/sections');
    },

    getMyEnrollments: async (): Promise<Enrollment[]> => {
        // For students, this returns their enrollments
        // For parents, this might need adjustment to pass student ID
        return api.get<Enrollment[]>('/enrollments');
    }
};
