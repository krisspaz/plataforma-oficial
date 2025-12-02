import { Sidebar } from "@/components/Sidebar";
import { UpcomingTask } from "@/components/UpcomingTask";
import { Button } from "@/components/ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Plus } from "lucide-react";

const Tareas = () => {
  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      
      <main className="flex-1 ml-64 p-8">
        <div className="mb-8">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-foreground mb-2">Tareas</h1>
              <p className="text-muted-foreground">Gestiona y completa tus asignaciones</p>
            </div>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              Nueva Tarea
            </Button>
          </div>
        </div>

        <Tabs defaultValue="pendientes" className="w-full">
          <TabsList className="mb-6">
            <TabsTrigger value="pendientes">Pendientes</TabsTrigger>
            <TabsTrigger value="en-progreso">En Progreso</TabsTrigger>
            <TabsTrigger value="completadas">Completadas</TabsTrigger>
          </TabsList>

          <TabsContent value="pendientes" className="space-y-4">
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
            <UpcomingTask
              title="Laboratorio de Química"
              course="Química Orgánica"
              dueDate="5 días"
              priority="medium"
              type="Laboratorio"
            />
            <UpcomingTask
              title="Proyecto Final de Programación"
              course="Programación Avanzada"
              dueDate="2 semanas"
              priority="high"
              type="Proyecto"
            />
          </TabsContent>

          <TabsContent value="en-progreso" className="space-y-4">
            <UpcomingTask
              title="Análisis literario - García Lorca"
              course="Literatura Española"
              dueDate="4 días"
              priority="medium"
              type="Análisis"
            />
            <UpcomingTask
              title="Experimento de Física"
              course="Física Cuántica"
              dueDate="1 semana"
              priority="medium"
              type="Experimento"
            />
          </TabsContent>

          <TabsContent value="completadas" className="space-y-4">
            <UpcomingTask
              title="Quiz de Historia - Edad Media"
              course="Historia Mundial"
              dueDate="Completado hace 2 días"
              priority="low"
              type="Quiz"
            />
            <UpcomingTask
              title="Ejercicios de Álgebra Lineal"
              course="Matemáticas Avanzadas"
              dueDate="Completado hace 5 días"
              priority="low"
              type="Ejercicios"
            />
            <UpcomingTask
              title="Presentación de Química"
              course="Química Orgánica"
              dueDate="Completado hace 1 semana"
              priority="low"
              type="Presentación"
            />
          </TabsContent>
        </Tabs>
      </main>
    </div>
  );
};

export default Tareas;
