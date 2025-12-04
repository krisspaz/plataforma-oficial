import { Home, BookOpen, Calendar, CheckSquare, Users, Settings, GraduationCap, LogOut, FileText, DollarSign, UserCheck } from "lucide-react";
import { NavLink } from "@/components/NavLink";
import { cn } from "@/lib/utils";
import { useAuth } from "@/context/AuthContext";
import { Button } from "./ui/button";

export const Sidebar = () => {
  const { user, logout } = useAuth();

  const getNavItems = () => {
    // Administración y Admin de Sistemas
    if (user?.roles.includes('ROLE_ADMIN') || user?.roles.includes('ROLE_ADMIN_SISTEMAS')) {
      return [
        { icon: Home, label: "Dashboard", path: "/dashboard" },
        { icon: DollarSign, label: "Finanzas", path: "/administracion/finanzas" },
        { icon: Users, label: "Estadísticas", path: "/administracion/estadisticas" },
        { icon: FileText, label: "Reportes", path: "/administracion/reportes" },
        { icon: Settings, label: "Configuración", path: "/configuracion" },
      ];
    }

    // Secretaría
    if (user?.roles.includes('ROLE_SECRETARIA')) {
      return [
        { icon: Home, label: "Dashboard", path: "/dashboard" },
        { icon: DollarSign, label: "Pagos", path: "/secretaria/pagos" },
        { icon: Users, label: "Inscripciones", path: "/secretaria/inscripciones" },
        { icon: FileText, label: "Contratos", path: "/secretaria/contratos" },
        { icon: Settings, label: "Configuración", path: "/configuracion" },
      ];
    }

    // Coordinación
    if (user?.roles.includes('ROLE_COORDINACION')) {
      return [
        { icon: Home, label: "Dashboard", path: "/dashboard" },
        { icon: Users, label: "Profesores", path: "/coordinacion/profesores" },
        { icon: BookOpen, label: "Materias", path: "/coordinacion/materias" },
        { icon: GraduationCap, label: "Notas", path: "/coordinacion/notas" },
        { icon: Settings, label: "Configuración", path: "/configuracion" },
      ];
    }

    // Maestros
    if (user?.roles.includes('ROLE_MAESTRO')) {
      return [
        { icon: Home, label: "Inicio", path: "/" },
        { icon: BookOpen, label: "Actividades", path: "/maestros/actividades" },
        { icon: CheckSquare, label: "Notas", path: "/maestros/notas" },
        { icon: FileText, label: "Materiales", path: "/maestros/materiales" },
        { icon: Calendar, label: "Calendario", path: "/maestros/calendario" },
        { icon: Settings, label: "Configuración", path: "/configuracion" },
      ];
    }

    // Padres
    if (user?.roles.includes('ROLE_PADRE_FAMILIA')) {
      return [
        { icon: Home, label: "Inicio", path: "/" },
        { icon: DollarSign, label: "Mi Cuenta", path: "/padres/cuenta" },
        { icon: CheckSquare, label: "Tareas", path: "/padres/tareas" },
        { icon: FileText, label: "Contratos", path: "/padres/contratos" },
        { icon: Settings, label: "Configuración", path: "/configuracion" },
      ];
    }

    // Estudiantes (default)
    return [
      { icon: Home, label: "Inicio", path: "/" },
      { icon: BookOpen, label: "Mis Cursos", path: "/cursos" },
      { icon: Calendar, label: "Calendario", path: "/calendario" },
      { icon: CheckSquare, label: "Tareas", path: "/tareas" },
      { icon: Users, label: "Comunidad", path: "/comunidad" },
      { icon: Settings, label: "Configuración", path: "/configuracion" },
    ];
  };

  const navItems = getNavItems();

  return (
    <aside className="fixed left-0 top-0 h-screen w-64 bg-sidebar border-r border-sidebar-border flex flex-col">
      <div className="p-6 border-b border-sidebar-border">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-accent flex items-center justify-center shadow-accent-glow">
            <GraduationCap className="w-6 h-6 text-primary-foreground" />
          </div>
          <div>
            <h1 className="text-lg font-bold text-sidebar-foreground">Oxford</h1>
            <p className="text-xs text-sidebar-foreground/60">Portal Educativo</p>
          </div>
        </div>
      </div>

      <nav className="flex-1 p-4 space-y-2">
        {navItems.map((item) => (
          <NavLink
            key={item.path}
            to={item.path}
            className={cn(
              "flex items-center gap-3 px-4 py-3 rounded-lg transition-smooth",
              "text-sidebar-foreground/70 hover:text-sidebar-foreground",
              "hover:bg-sidebar-accent"
            )}
            activeClassName="bg-sidebar-primary text-sidebar-primary-foreground font-medium shadow-primary-glow"
          >
            <item.icon className="w-5 h-5" />
            <span>{item.label}</span>
          </NavLink>
        ))}
      </nav>

      <div className="p-4 border-t border-sidebar-border">
        <div className="flex items-center gap-3 p-3 rounded-lg bg-sidebar-accent mb-2">
          <div className="w-10 h-10 rounded-full bg-gradient-to-br from-accent to-primary flex items-center justify-center text-sm font-semibold text-primary-foreground">
            {user?.firstName?.charAt(0)}{user?.lastName?.charAt(0)}
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-medium text-sidebar-foreground truncate">
              {user?.firstName} {user?.lastName}
            </p>
            <p className="text-xs text-sidebar-foreground/60 truncate">
              {user?.roles.includes('ROLE_ADMIN') ? 'Administrador' :
                user?.roles.includes('ROLE_MAESTRO') ? 'Maestro' : 'Estudiante'}
            </p>
          </div>
        </div>
        <Button
          variant="ghost"
          className="w-full justify-start text-red-500 hover:text-red-600 hover:bg-red-50"
          onClick={logout}
        >
          <LogOut className="w-4 h-4 mr-2" />
          Cerrar Sesión
        </Button>
      </div>
    </aside>
  );
};
