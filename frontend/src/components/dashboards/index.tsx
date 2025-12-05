// Role-based dashboard exports
export { AdminDashboard } from './AdminDashboard';
export { TeacherDashboard } from './TeacherDashboard';
export { ParentDashboard } from './ParentDashboard';

// Dashboard selector based on user role
import React from 'react';
import { useAuth } from '../../hooks/useAuth';
import { AdminDashboard } from './AdminDashboard';
import { TeacherDashboard } from './TeacherDashboard';
import { ParentDashboard } from './ParentDashboard';

interface DashboardSelectorProps {
    isLoading?: boolean;
    stats?: any;
}

/**
 * DashboardSelector - Renders the appropriate dashboard based on user role
 */
export const DashboardSelector: React.FC<DashboardSelectorProps> = ({
    isLoading = false,
    stats,
}) => {
    const { user } = useAuth();

    if (!user) {
        return null;
    }

    const role = user.role?.toLowerCase() ?? '';

    switch (role) {
        case 'admin':
        case 'administrador':
        case 'coordinacion':
        case 'secretaria':
            return <AdminDashboard stats={stats} isLoading={isLoading} />;

        case 'teacher':
        case 'maestro':
            return <TeacherDashboard stats={stats} isLoading={isLoading} />;

        case 'parent':
        case 'padre':
        case 'tutor':
            return <ParentDashboard stats={stats} isLoading={isLoading} />;

        default:
            // Default to a simple welcome message
            return (
                <div className="p-6">
                    <h1 className="text-2xl font-bold text-gray-900">Bienvenido</h1>
                    <p className="mt-2 text-gray-600">
                        Tu rol ({user.role}) no tiene un dashboard específico configurado.
                    </p>
                </div>
            );
    }
};

export default DashboardSelector;
