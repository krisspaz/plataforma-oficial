import { Sidebar } from "@/components/Sidebar";
import { Card } from "@/components/ui/card";
import { Avatar } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { MessageSquare, ThumbsUp, Share2, Users } from "lucide-react";

const Comunidad = () => {
  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      
      <main className="flex-1 ml-64 p-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-foreground mb-2">Comunidad</h1>
          <p className="text-muted-foreground">Conecta con compañeros y profesores</p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2 space-y-6">
            {/* Post 1 */}
            <Card className="p-6">
              <div className="flex gap-4 mb-4">
                <div className="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-accent flex items-center justify-center text-primary-foreground font-semibold">
                  AG
                </div>
                <div className="flex-1">
                  <h3 className="font-semibold text-foreground">Ana García</h3>
                  <p className="text-sm text-muted-foreground">Profesora • Hace 2 horas</p>
                </div>
              </div>
              
              <p className="text-foreground mb-4">
                ¡Hola estudiantes! Les comparto algunos recursos adicionales sobre cálculo integral. 
                Estos ejercicios les ayudarán a prepararse mejor para el examen final. 📚
              </p>

              <div className="flex items-center gap-4 text-sm text-muted-foreground">
                <button className="flex items-center gap-2 hover:text-primary transition-smooth">
                  <ThumbsUp className="w-4 h-4" />
                  <span>24</span>
                </button>
                <button className="flex items-center gap-2 hover:text-primary transition-smooth">
                  <MessageSquare className="w-4 h-4" />
                  <span>8 comentarios</span>
                </button>
                <button className="flex items-center gap-2 hover:text-primary transition-smooth">
                  <Share2 className="w-4 h-4" />
                  <span>Compartir</span>
                </button>
              </div>
            </Card>

            {/* Post 2 */}
            <Card className="p-6">
              <div className="flex gap-4 mb-4">
                <div className="w-12 h-12 rounded-full bg-gradient-to-br from-accent to-success flex items-center justify-center text-accent-foreground font-semibold">
                  ML
                </div>
                <div className="flex-1">
                  <h3 className="font-semibold text-foreground">María López</h3>
                  <p className="text-sm text-muted-foreground">Estudiante • Hace 5 horas</p>
                </div>
              </div>
              
              <p className="text-foreground mb-4">
                ¿Alguien más está trabajando en el proyecto de Física? Me gustaría formar un grupo 
                de estudio para las sesiones de la próxima semana. 🤝
              </p>

              <div className="flex items-center gap-4 text-sm text-muted-foreground">
                <button className="flex items-center gap-2 hover:text-primary transition-smooth">
                  <ThumbsUp className="w-4 h-4" />
                  <span>15</span>
                </button>
                <button className="flex items-center gap-2 hover:text-primary transition-smooth">
                  <MessageSquare className="w-4 h-4" />
                  <span>12 comentarios</span>
                </button>
                <button className="flex items-center gap-2 hover:text-primary transition-smooth">
                  <Share2 className="w-4 h-4" />
                  <span>Compartir</span>
                </button>
              </div>
            </Card>

            {/* Post 3 */}
            <Card className="p-6">
              <div className="flex gap-4 mb-4">
                <div className="w-12 h-12 rounded-full bg-gradient-to-br from-warning to-destructive flex items-center justify-center text-warning-foreground font-semibold">
                  CR
                </div>
                <div className="flex-1">
                  <h3 className="font-semibold text-foreground">Carlos Ruiz</h3>
                  <p className="text-sm text-muted-foreground">Profesor • Hace 1 día</p>
                </div>
              </div>
              
              <p className="text-foreground mb-4">
                Recordatorio: La fecha límite para la entrega del ensayo sobre la Generación del 98 
                es este viernes. No olviden incluir la bibliografía completa. ✍️
              </p>

              <div className="flex items-center gap-4 text-sm text-muted-foreground">
                <button className="flex items-center gap-2 hover:text-primary transition-smooth">
                  <ThumbsUp className="w-4 h-4" />
                  <span>42</span>
                </button>
                <button className="flex items-center gap-2 hover:text-primary transition-smooth">
                  <MessageSquare className="w-4 h-4" />
                  <span>5 comentarios</span>
                </button>
                <button className="flex items-center gap-2 hover:text-primary transition-smooth">
                  <Share2 className="w-4 h-4" />
                  <span>Compartir</span>
                </button>
              </div>
            </Card>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            <Card className="p-6">
              <h3 className="font-bold text-foreground mb-4">Grupos Activos</h3>
              <div className="space-y-4">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <Users className="w-5 h-5 text-primary" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-sm text-foreground truncate">Matemáticas 2025</p>
                    <p className="text-xs text-muted-foreground">156 miembros</p>
                  </div>
                </div>

                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center">
                    <Users className="w-5 h-5 text-accent" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-sm text-foreground truncate">Club de Física</p>
                    <p className="text-xs text-muted-foreground">89 miembros</p>
                  </div>
                </div>

                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                    <Users className="w-5 h-5 text-success" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="font-semibold text-sm text-foreground truncate">Literatura y Debate</p>
                    <p className="text-xs text-muted-foreground">124 miembros</p>
                  </div>
                </div>
              </div>
              
              <Button variant="outline" className="w-full mt-4">
                Ver todos los grupos
              </Button>
            </Card>

            <Card className="p-6">
              <h3 className="font-bold text-foreground mb-4">Sugerencias</h3>
              <p className="text-sm text-muted-foreground mb-4">
                Conecta con más estudiantes y profesores de tu institución
              </p>
              <Button className="w-full">
                Explorar perfiles
              </Button>
            </Card>
          </div>
        </div>
      </main>
    </div>
  );
};

export default Comunidad;
