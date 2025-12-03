import { Sidebar } from "@/components/Sidebar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CheckCircle2, Clock, AlertCircle } from "lucide-react";

export default function Tareas() {
  // Mock data - in a real app this would come from an API
  const tasks = [
    {
      id: 1,
      title: "Ensayo sobre Revolución Industrial",
      course: "Historia Mundial",
      dueDate: "2024-03-25",
      status: "pending",
      priority: "high"
    },
    {
      id: 2,
      title: "Ejercicios de Cálculo Cap. 4",
      course: "Matemáticas Avanzadas",
      dueDate: "2024-03-28",
      status: "in_progress",
      priority: "medium"
    },
    {
      id: 3,
      title: "Reporte de Laboratorio Física",
      course: "Física Fundamental",
      dueDate: "2024-03-30",
      status: "completed",
      priority: "low"
    }
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed': return 'bg-green-500';
      case 'in_progress': return 'bg-blue-500';
      default: return 'bg-yellow-500';
    }
  };

  const getPriorityIcon = (priority: string) => {
    switch (priority) {
      case 'high': return <AlertCircle className="w-4 h-4 text-red-500" />;
      case 'medium': return <Clock className="w-4 h-4 text-yellow-500" />;
      default: return <CheckCircle2 className="w-4 h-4 text-green-500" />;
    }
  };

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />

      <main className="flex-1 ml-64 p-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-foreground mb-2">Tareas y Actividades</h1>
          <p className="text-muted-foreground">Gestiona tus entregas y pendientes</p>
        </div>

        <div className="grid gap-4">
          {tasks.map((task) => (
            <Card key={task.id} className="hover:shadow-md transition-shadow">
              <CardContent className="p-6 flex items-center justify-between">
                <div className="flex items-start gap-4">
                  <div className={`w-2 h-16 rounded-full ${getStatusColor(task.status)}`} />
                  <div>
                    <h3 className="font-semibold text-lg">{task.title}</h3>
                    <p className="text-sm text-muted-foreground">{task.course}</p>
                    <div className="flex items-center gap-2 mt-2">
                      <Badge variant="outline" className="text-xs">
                        Vence: {task.dueDate}
                      </Badge>
                      <div className="flex items-center gap-1 text-xs text-muted-foreground">
                        {getPriorityIcon(task.priority)}
                        <span className="capitalize">{task.priority} Prioridad</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div className="flex gap-2">
                  <Badge className={
                    task.status === 'completed' ? 'bg-green-100 text-green-800 hover:bg-green-100' :
                      task.status === 'in_progress' ? 'bg-blue-100 text-blue-800 hover:bg-blue-100' :
                        'bg-yellow-100 text-yellow-800 hover:bg-yellow-100'
                  }>
                    {task.status === 'completed' ? 'Completada' :
                      task.status === 'in_progress' ? 'En Progreso' : 'Pendiente'}
                  </Badge>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </main>
    </div>
  );
}
