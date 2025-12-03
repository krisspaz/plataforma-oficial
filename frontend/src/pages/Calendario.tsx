import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { Calendar } from "@/components/ui/calendar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { scheduleService, Schedule } from "@/services/schedule.service";
import { academicService } from "@/services/academic.service";
import { Loader2, Clock, MapPin, User } from "lucide-react";
import { useAuth } from "@/context/AuthContext";

const Calendario = () => {
  const { user } = useAuth();
  const [date, setDate] = useState<Date | undefined>(new Date());
  const [schedules, setSchedules] = useState<Schedule[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchSchedule = async () => {
      try {
        // First get enrollments to find section ID
        // In a real app we might let user select which enrollment/section to view
        const enrollments = await academicService.getMyEnrollments();

        if (enrollments.length > 0) {
          const sectionId = enrollments[0].section.id;
          const data = await scheduleService.getMySchedule(sectionId);
          setSchedules(data);
        }
      } catch (error) {
        console.error('Failed to fetch schedule', error);
      } finally {
        setLoading(false);
      }
    };

    fetchSchedule();
  }, [user]);

  // Helper to filter schedules for selected date
  const getSchedulesForDate = (selectedDate: Date) => {
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const dayName = days[selectedDate.getDay()];

    return schedules.filter(s => s.dayOfWeek === dayName)
      .sort((a, b) => a.startTime.localeCompare(b.startTime));
  };

  const selectedSchedules = date ? getSchedulesForDate(date) : [];

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />

      <main className="flex-1 ml-64 p-8">
        <h1 className="text-3xl font-bold text-foreground mb-8">Calendario Académico</h1>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <Card className="lg:col-span-1">
            <CardHeader>
              <CardTitle>Seleccionar Fecha</CardTitle>
            </CardHeader>
            <CardContent>
              <Calendar
                mode="single"
                selected={date}
                onSelect={setDate}
                className="rounded-md border"
              />
            </CardContent>
          </Card>

          <Card className="lg:col-span-2">
            <CardHeader>
              <CardTitle>
                Horario para {date?.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
              </CardTitle>
            </CardHeader>
            <CardContent>
              {loading ? (
                <div className="flex justify-center py-8">
                  <Loader2 className="h-8 w-8 animate-spin text-primary" />
                </div>
              ) : selectedSchedules.length > 0 ? (
                <div className="space-y-4">
                  {selectedSchedules.map((schedule) => (
                    <div
                      key={schedule.id}
                      className="flex items-start p-4 rounded-lg border bg-card hover:bg-accent/5 transition-colors"
                    >
                      <div className="mr-4 flex flex-col items-center justify-center min-w-[80px] text-center">
                        <span className="text-sm font-bold text-primary">
                          {schedule.startTime.substring(0, 5)}
                        </span>
                        <span className="text-xs text-muted-foreground">-</span>
                        <span className="text-sm text-muted-foreground">
                          {schedule.endTime.substring(0, 5)}
                        </span>
                      </div>

                      <div className="flex-1">
                        <h3 className="font-semibold text-lg text-foreground">
                          {schedule.subject.name}
                        </h3>
                        <div className="flex flex-wrap gap-4 mt-2 text-sm text-muted-foreground">
                          <div className="flex items-center gap-1">
                            <User className="w-4 h-4" />
                            <span>{schedule.teacher.firstName} {schedule.teacher.lastName}</span>
                          </div>
                          <div className="flex items-center gap-1">
                            <MapPin className="w-4 h-4" />
                            <span>{schedule.classroom || 'Aula por asignar'}</span>
                          </div>
                          <div className="flex items-center gap-1">
                            <Clock className="w-4 h-4" />
                            <span>{parseInt(schedule.endTime) - parseInt(schedule.startTime)} min</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div className="text-center py-12 text-muted-foreground">
                  No hay clases programadas para este día.
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </main>
    </div>
  );
};

export default Calendario;
