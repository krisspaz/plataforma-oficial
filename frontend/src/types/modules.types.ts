// Secretaría Types
export interface Payment {
    id: number;
    studentId: number;
    amount: number;
    paymentType: 'CONTADO' | 'CREDITO';
    paymentMethod: 'EFECTIVO' | 'TARJETA' | 'TRANSFERENCIA';
    description: string;
    date: string;
    receiptNumber: string;
}

export interface PaymentPlan {
    id: number;
    studentId: number;
    totalAmount: number;
    numberOfInstallments: number;
    installmentAmount: number;
    startDate: string;
    status: 'ACTIVE' | 'COMPLETED' | 'CANCELLED';
}

export interface Debtor {
    studentId: number;
    studentName: string;
    grade: string;
    totalDebt: number;
    overdueAmount: number;
    lastPaymentDate: string;
}

export interface Enrollment {
    id: number;
    studentId: number;
    gradeId: number;
    sectionId: number;
    academicYear: number;
    enrollmentDate: string;
    status: 'ACTIVE' | 'INACTIVE';
}

export interface Contract {
    id: number;
    studentId: number;
    studentName: string;
    fatherName: string;
    fatherDPI: string;
    fatherProfession: string;
    fatherNationality: string;
    motherName: string;
    motherDPI: string;
    motherProfession: string;
    motherNationality: string;
    resolutionNumber: string;
    installments: number;
    installmentAmount: number;
    generatedDate: string;
    signedDate?: string;
    signedDocumentUrl?: string;
}

// Coordinación Types
export interface Announcement {
    id: number;
    title: string;
    content: string;
    targetAudience: 'ALL' | 'TEACHERS' | 'PARENTS' | 'STUDENTS';
    publishDate: string;
    expiryDate?: string;
    authorId: number;
}

export interface Teacher {
    id: number;
    firstName: string;
    lastName: string;
    email: string;
    phone: string;
    birthDate: string;
    specialty: string;
    hireDate: string;
    status: 'ACTIVE' | 'INACTIVE';
}

export interface SubjectAssignment {
    id: number;
    teacherId: number;
    subjectId: number;
    gradeId: number;
    sectionId: number;
    academicYear: number;
}

export interface GradeReport {
    studentId: number;
    studentName: string;
    grades: {
        subjectId: number;
        subjectName: string;
        bimester1: number;
        bimester2: number;
        bimester3: number;
        bimester4: number;
        average: number;
    }[];
}

export interface ReportCard {
    studentId: number;
    studentName: string;
    grade: string;
    section: string;
    bimester: number;
    subjects: {
        name: string;
        grade: number;
        percentage: number;
    }[];
    average: number;
    hasDebt: boolean;
}

// Maestros Types
export interface Activity {
    id: number;
    teacherId: number;
    subjectId: number;
    gradeId: number;
    sectionId: number;
    title: string;
    description: string;
    activityType: 'TAREA' | 'EXAMEN' | 'PROYECTO' | 'LABORATORIO';
    dueDate: string;
    maxGrade: number;
    attachments?: string[];
}

export interface StudentGrade {
    id: number;
    activityId: number;
    studentId: number;
    grade: number;
    comments?: string;
    submittedDate?: string;
}

export interface CourseMaterial {
    id: number;
    teacherId: number;
    subjectId: number;
    title: string;
    description: string;
    materialType: 'PDF' | 'VIDEO' | 'LINK' | 'DOCUMENT';
    url: string;
    uploadDate: string;
}

// Padres Types
export interface ParentAccount {
    studentId: number;
    studentName: string;
    totalDebt: number;
    paidAmount: number;
    pendingAmount: number;
    nextPaymentDate: string;
    nextPaymentAmount: number;
}

export interface StudentTask {
    id: number;
    studentId: number;
    subjectName: string;
    teacherName: string;
    title: string;
    description: string;
    dueDate: string;
    status: 'PENDIENTE' | 'ENTREGADA' | 'CALIFICADA';
    grade?: number;
}

// Administración Types
export interface FinancialSummary {
    totalIncome: number;
    totalExpenses: number;
    balance: number;
    monthlyIncome: number[];
    monthlyExpenses: number[];
}

export interface StudentStatistics {
    totalStudents: number;
    activeStudents: number;
    inactiveStudents: number;
    byGrade: {
        gradeName: string;
        count: number;
    }[];
    enrollmentTrend: {
        month: string;
        count: number;
    }[];
}

export interface DailyReport {
    date: string;
    totalPayments: number;
    totalAmount: number;
    paymentsByMethod: {
        method: string;
        count: number;
        amount: number;
    }[];
    payments: Payment[];
}
