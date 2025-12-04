import { api } from './api';

export interface Assignment {
    id: string;
    teacherId: number;
    teacherName: string;
    subjectId: number;
    subjectName: string;
    gradeId: number;
    gradeName: string;
    sectionId: number;
    sectionName: string;
    academicYear: number;
}

export interface Announcement {
    id: string;
    title: string;
    content: string;
    type: 'general' | 'teachers' | 'parents' | 'students' | 'specific_grade';
    authorName: string;
    createdAt: string;
    expiresAt: string | null;
    targetIds: number[] | null;
}

export interface CalendarEvent {
    id: string;
    title: string;
    description: string | null;
    startDate: string;
    endDate: string;
    type: 'holiday' | 'exam' | 'activity' | 'meeting';
    isAllDay: boolean;
}

export interface CreateAssignmentRequest {
    teacher_id: number;
    subject_id: number;
    grade_id: number;
    section_id: number;
    academic_year?: number;
}

export interface CreateAnnouncementRequest {
    title: string;
    content: string;
    type: string;
    target_ids?: number[];
    expires_at?: string;
}

export interface CreateCalendarEventRequest {
    title: string;
    start_date: string;
    end_date: string;
    type: string;
    academic_year?: number;
    is_all_day?: boolean;
    description?: string;
}

class CoordinationService {
    private baseUrl = '/api/coordination';

    // Assignments
    async createAssignment(data: CreateAssignmentRequest): Promise<void> {
        await api.post(`${this.baseUrl}/assignments`, data);
    }

    async getTeacherAssignments(teacherId: number, year?: number): Promise<Assignment[]> {
        const params = year ? `?year=${year}` : '';
        const response = await api.get<{ success: boolean; data: Assignment[] }>(
            `${this.baseUrl}/assignments/teacher/${teacherId}${params}`
        );
        return response.data.data;
    }

    // Announcements
    async createAnnouncement(data: CreateAnnouncementRequest): Promise<void> {
        await api.post(`${this.baseUrl}/announcements`, data);
    }

    async getAnnouncements(type?: string): Promise<Announcement[]> {
        const params = type ? `?type=${type}` : '';
        const response = await api.get<{ success: boolean; data: Announcement[] }>(
            `${this.baseUrl}/announcements${params}`
        );
        return response.data.data;
    }

    // Calendar
    async createCalendarEvent(data: CreateCalendarEventRequest): Promise<void> {
        await api.post(`${this.baseUrl}/calendar`, data);
    }

    async getCalendarEvents(startDate: string, endDate: string): Promise<CalendarEvent[]> {
        const response = await api.get<{ success: boolean; data: CalendarEvent[] }>(
            `${this.baseUrl}/calendar?start_date=${startDate}&end_date=${endDate}`
        );
        return response.data.data;
    }

    // Helpers
    getAnnouncementTypeLabel(type: string): string {
        const types: Record<string, string> = {
            general: 'General',
            teachers: 'Maestros',
            parents: 'Padres',
            students: 'Estudiantes',
            specific_grade: 'Grado Específico',
        };
        return types[type] || type;
    }

    getEventTypeColor(type: string): string {
        const colors: Record<string, string> = {
            holiday: 'bg-red-500',
            exam: 'bg-yellow-500',
            activity: 'bg-green-500',
            meeting: 'bg-blue-500',
        };
        return colors[type] || 'bg-gray-500';
    }

    getEventTypeLabel(type: string): string {
        const types: Record<string, string> = {
            holiday: 'Feriado',
            exam: 'Examen',
            activity: 'Actividad',
            meeting: 'Reunión',
        };
        return types[type] || type;
    }
}

export const coordinationService = new CoordinationService();
