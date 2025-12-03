import { api } from './api';

export interface Schedule {
    id: number;
    dayOfWeek: string;
    startTime: string;
    endTime: string;
    subject: {
        name: string;
        code: string;
    };
    teacher: {
        firstName: string;
        lastName: string;
    };
    classroom: string;
}

export const scheduleService = {
    getMySchedule: async (sectionId: number): Promise<Schedule[]> => {
        return api.get<Schedule[]>(`/schedules/section/${sectionId}`);
    },

    getTeacherSchedule: async (teacherId: number): Promise<Schedule[]> => {
        return api.get<Schedule[]>(`/schedules/teacher/${teacherId}`);
    }
};
