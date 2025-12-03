import { useAuth } from "@/context/AuthContext";
import { AdminDashboard } from "./dashboards/AdminDashboard";
import { StudentDashboard } from "./dashboards/StudentDashboard";
import { TeacherDashboard } from "./dashboards/TeacherDashboard";
import { ParentDashboard } from "./dashboards/ParentDashboard";
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

  if (user.roles.includes('ROLE_ADMIN') || user.roles.includes('ROLE_ADMIN_SISTEMAS')) {
    return <AdminDashboard />;
  }

  if (user.roles.includes('ROLE_MAESTRO')) {
    return <TeacherDashboard />;
  }

  if (user.roles.includes('ROLE_PADRE_FAMILIA')) {
    return <ParentDashboard />;
  }

  return <StudentDashboard />;
};

export default Index;
