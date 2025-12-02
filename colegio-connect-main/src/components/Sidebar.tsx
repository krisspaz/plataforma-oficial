import { Home, BookOpen, Calendar, CheckSquare, Users, Settings, GraduationCap } from "lucide-react";
import { NavLink } from "@/components/NavLink";
import { cn } from "@/lib/utils";

const navItems = [
  { icon: Home, label: "Inicio", path: "/" },
  { icon: BookOpen, label: "Mis Cursos", path: "/cursos" },
  { icon: Calendar, label: "Calendario", path: "/calendario" },
  { icon: CheckSquare, label: "Tareas", path: "/tareas" },
  { icon: Users, label: "Comunidad", path: "/comunidad" },
  { icon: Settings, label: "Configuración", path: "/configuracion" },
];

export const Sidebar = () => {
  return (
    <aside className="fixed left-0 top-0 h-screen w-64 bg-sidebar border-r border-sidebar-border flex flex-col">
      <div className="p-6 border-b border-sidebar-border">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-accent flex items-center justify-center shadow-accent-glow">
            <GraduationCap className="w-6 h-6 text-primary-foreground" />
          </div>
          <div>
            <h1 className="text-lg font-bold text-sidebar-foreground">EduPlat</h1>
            <p className="text-xs text-sidebar-foreground/60">Portal Estudiantil</p>
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
        <div className="flex items-center gap-3 p-3 rounded-lg bg-sidebar-accent">
          <div className="w-10 h-10 rounded-full bg-gradient-to-br from-accent to-primary flex items-center justify-center text-sm font-semibold text-primary-foreground">
            JD
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-medium text-sidebar-foreground truncate">Juan Díaz</p>
            <p className="text-xs text-sidebar-foreground/60 truncate">Estudiante</p>
          </div>
        </div>
      </div>
    </aside>
  );
};
