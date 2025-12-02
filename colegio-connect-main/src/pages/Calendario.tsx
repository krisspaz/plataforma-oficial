import { Sidebar } from "@/components/Sidebar";
import { CalendarEvent } from "@/components/CalendarEvent";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { ChevronLeft, ChevronRight, Plus } from "lucide-react";

const Calendario = () => {
  const daysOfWeek = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"];
  const currentMonth = "Diciembre 2025";

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      
      <main className="flex-1 ml-64 p-8">
        <div className="mb-8">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-foreground mb-2">Calendario</h1>
              <p className="text-muted-foreground">Organiza tus clases y eventos</p>
            </div>
            <Button>
              <Plus className="w-4 h-4 mr-2" />
              Nuevo Evento
            </Button>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div className="lg:col-span-2">
            <Card className="p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-foreground">{currentMonth}</h2>
                <div className="flex gap-2">
                  <Button variant="outline" size="icon">
                    <ChevronLeft className="w-4 h-4" />
                  </Button>
                  <Button variant="outline" size="icon">
                    <ChevronRight className="w-4 h-4" />
                  </Button>
                </div>
              </div>

              <div className="grid grid-cols-7 gap-2 mb-2">
                {daysOfWeek.map(day => (
                  <div key={day} className="text-center text-sm font-semibold text-muted-foreground py-2">
                    {day}
                  </div>
                ))}
              </div>

              <div className="grid grid-cols-7 gap-2">
                {Array.from({ length: 35 }, (_, i) => {
                  const day = i - 2; // Start from -2 to show previous month days
                  const isCurrentMonth = day >= 1 && day <= 31;
                  const isToday = day === 15;
                  
                  return (
                    <button
                      key={i}
                      className={`
                        aspect-square rounded-lg p-2 text-sm font-medium transition-smooth
                        ${isCurrentMonth 
                          ? 'text-foreground hover:bg-accent' 
                          : 'text-muted-foreground/50'
                        }
                        ${isToday 
                          ? 'bg-primary text-primary-foreground hover:bg-primary/90' 
                          : ''
                        }
                      `}
                    >
                      {day > 0 && day <= 31 ? day : ''}
                    </button>
                  );
                })}
              </div>
            </Card>
          </div>

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
              <CalendarEvent
                title="Entrega de Proyecto"
                date="22 Dic"
                time="11:59 PM"
                location="Plataforma Digital"
                color="hsl(35, 90%, 55%)"
              />
              <CalendarEvent
                title="Clase de Repaso"
                date="23 Dic"
                time="3:00 PM"
                location="Aula Virtual"
                color="hsl(280, 70%, 55%)"
              />
            </div>
          </div>
        </div>
      </main>
    </div>
  );
};

export default Calendario;
