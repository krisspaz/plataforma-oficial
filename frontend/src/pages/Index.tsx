import { useAuth } from "@/context/AuthContext";
import { AdminDashboard } from "./dashboards/AdminDashboard";
import { StudentDashboard } from "./dashboards/StudentDashboard";
import { TeacherDashboard } from "./dashboards/TeacherDashboard";
import { ParentDashboard } from "./dashboards/ParentDashboard";
import { SecretariaDashboard } from "./secretaria/SecretariaDashboard";
import { CoordinacionDashboard } from "./coordinacion/CoordinacionDashboard";
import { MaestrosDashboard } from "./maestros/MaestrosDashboard";
import { PadresDashboard } from "./padres/PadresDashboard";
import { AdministracionDashboard } from "./administracion/AdministracionDashboard";
import { Loader2 } from "lucide-react";

const Index = () => {
  const { user, isLoading } = useAuth();

  if (isLoading) {
    return (
      <div className="h-screen w-full flex items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  if (!user) return null;

  // Admin y Admin de Sistemas
  if (user.roles.includes('ROLE_ADMIN') || user.roles.includes('ROLE_ADMIN_SISTEMAS')) {
    return <AdministracionDashboard />;
  }

  // Secretaría
  if (user.roles.includes('ROLE_SECRETARIA')) {
    return <SecretariaDashboard />;
  }

  // Coordinación
  if (user.roles.includes('ROLE_COORDINACION')) {
    return <CoordinacionDashboard />;
  }

  // Maestros
  if (user.roles.includes('ROLE_MAESTRO')) {
    return <MaestrosDashboard />;
  }

  // Padres
  if (user.roles.includes('ROLE_PADRE_FAMILIA')) {
    return <PadresDashboard />;
  }

  // Estudiantes (default)
  return <StudentDashboard />;
};

export default Index;
