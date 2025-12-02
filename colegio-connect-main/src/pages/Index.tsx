import { Sidebar } from "@/components/Sidebar";
import { StatCard } from "@/components/StatCard";
import { CourseCard } from "@/components/CourseCard";
import { UpcomingTask } from "@/components/UpcomingTask";
import { CalendarEvent } from "@/components/CalendarEvent";
import { BookOpen, Trophy, Clock, TrendingUp, Bell } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";

const Index = () => {
  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      
      <main className="flex-1 ml-64 p-8">
        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center justify-between mb-2">
            <div>
              <h1 className="text-3xl font-bold text-foreground mb-1">
                ¡Bienvenido de nuevo, Juan! 👋
              </h1>
              <p className="text-muted-foreground">
                Continúa tu viaje de aprendizaje hoy
              </p>
            </div>
            <Button variant="outline" size="icon" className="relative">
              <Bell className="w-5 h-5" />
              <span className="absolute -top-1 -right-1 w-3 h-3 bg-destructive rounded-full border-2 border-background" />
            </Button>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <StatCard
            icon={BookOpen}
            title="Cursos Activos"
            value="6"
            subtitle="2 nuevas lecciones disponibles"
            variant="primary"
          />
          <StatCard
            icon={Trophy}
            title="Promedio General"
            value="8.7"
            subtitle="+0.3 desde el mes pasado"
            variant="accent"
          />
          <StatCard
            icon={Clock}
            title="Horas de Estudio"
            value="24h"
            subtitle="Esta semana"
            variant="success"
          />
          <StatCard
            icon={TrendingUp}
            title="Tareas Completadas"
            value="12/15"
            subtitle="80% de finalización"
            variant="warning"
          />
        </div>

        {/* Main Content Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          {/* Courses Section */}
          <div className="lg:col-span-2">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-xl font-bold text-foreground">Mis Cursos</h2>
              <Button variant="ghost" size="sm">Ver todos</Button>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <CourseCard
                title="Matemáticas Avanzadas"
                teacher="Prof. Ana García"
                progress={75}
                nextClass="Hoy, 10:00 AM"
                students={32}
                color="hsl(210, 85%, 45%)"
              />
              <CourseCard
                title="Literatura Española"
                teacher="Prof. Carlos Ruiz"
                progress={60}
                nextClass="Mañana, 2:00 PM"
                students={28}
                color="hsl(175, 70%, 50%)"
              />
              <CourseCard
                title="Física Cuántica"
                teacher="Prof. María López"
                progress={45}
                nextClass="Lunes, 11:00 AM"
                students={25}
                color="hsl(145, 65%, 45%)"
              />
              <CourseCard
                title="Historia Mundial"
                teacher="Prof. Diego Martínez"
                progress={88}
                nextClass="Miércoles, 3:00 PM"
                students={30}
                color="hsl(35, 90%, 55%)"
              />
            </div>
          </div>

          {/* Sidebar Content */}
          <div className="space-y-6">
            {/* Upcoming Tasks */}
            <div>
              <h2 className="text-xl font-bold text-foreground mb-4">
                Próximas Tareas
              </h2>
              <div className="space-y-3">
                <UpcomingTask
                  title="Ensayo sobre Revolución Industrial"
                  course="Historia Mundial"
                  dueDate="Mañana, 11:59 PM"
                  priority="high"
                  type="Ensayo"
                />
                <UpcomingTask
                  title="Problemas de Cálculo Integral"
                  course="Matemáticas Avanzadas"
                  dueDate="3 días"
                  priority="medium"
                  type="Ejercicios"
                />
                <UpcomingTask
                  title="Lectura: Don Quijote Cap. 1-5"
                  course="Literatura Española"
                  dueDate="1 semana"
                  priority="low"
                  type="Lectura"
                />
              </div>
            </div>

            {/* Calendar Events */}
            <div>
              <h2 className="text-xl font-bold text-foreground mb-4">
                Eventos Próximos
              </h2>
              <div className="space-y-3">
                <CalendarEvent
                  title="Examen Final - Matemáticas"
                  date="15 Dic"
                  time="9:00 AM"
                  location="Aula 301"
                  color="hsl(210, 85%, 45%)"
                />
                <CalendarEvent
                  title="Presentación Grupal"
                  date="18 Dic"
                  time="2:00 PM"
                  location="Sala de Conferencias"
                  color="hsl(175, 70%, 50%)"
                />
                <CalendarEvent
                  title="Tutoría de Física"
                  date="20 Dic"
                  time="4:00 PM"
                  location="Virtual - Zoom"
                  color="hsl(145, 65%, 45%)"
                />
              </div>
            </div>
          </div>
        </div>

        {/* Quick Actions */}
        <Card className="p-6 bg-gradient-to-br from-primary/10 to-accent/10 border-primary/20">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="text-lg font-bold text-foreground mb-1">
                ¿Necesitas ayuda con tus estudios?
              </h3>
              <p className="text-sm text-muted-foreground">
                Consulta con nuestros tutores o accede a recursos adicionales
              </p>
            </div>
            <div className="flex gap-3">
              <Button variant="default">
                Buscar Tutor
              </Button>
              <Button variant="outline">
                Ver Recursos
              </Button>
            </div>
          </div>
        </Card>
      </main>
    </div>
  );
};

export default Index;
