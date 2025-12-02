import { Sidebar } from "@/components/Sidebar";
import { CourseCard } from "@/components/CourseCard";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Search, Filter } from "lucide-react";

const Cursos = () => {
  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      
      <main className="flex-1 ml-64 p-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-foreground mb-2">Mis Cursos</h1>
          <p className="text-muted-foreground">Explora y gestiona todos tus cursos activos</p>
        </div>

        <div className="flex gap-4 mb-8">
          <div className="relative flex-1">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
            <Input 
              placeholder="Buscar cursos..." 
              className="pl-10"
            />
          </div>
          <Button variant="outline">
            <Filter className="w-4 h-4 mr-2" />
            Filtros
          </Button>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
          <CourseCard
            title="Química Orgánica"
            teacher="Prof. Laura Sánchez"
            progress={55}
            nextClass="Jueves, 1:00 PM"
            students={26}
            color="hsl(280, 70%, 55%)"
          />
          <CourseCard
            title="Programación Avanzada"
            teacher="Prof. Roberto Silva"
            progress={92}
            nextClass="Viernes, 10:30 AM"
            students={35}
            color="hsl(160, 60%, 45%)"
          />
        </div>
      </main>
    </div>
  );
};

export default Cursos;
