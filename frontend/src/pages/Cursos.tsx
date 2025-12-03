import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { CourseCard } from "@/components/CourseCard";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Search, Filter, Loader2 } from "lucide-react";
import { academicService, Enrollment } from "@/services/academic.service";
import { useAuth } from "@/context/AuthContext";

const Cursos = () => {
  const { user } = useAuth();
  const [enrollments, setEnrollments] = useState<Enrollment[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    const fetchCourses = async () => {
      try {
        // If user is student, get their enrollments
        // If teacher, get their assigned sections (need to implement in backend/service)
        // For now assuming student view for simplicity
        const data = await academicService.getMyEnrollments();
        setEnrollments(data);
      } catch (error) {
        console.error('Failed to fetch courses', error);
      } finally {
        setLoading(false);
      }
    };

    fetchCourses();
  }, [user]);

  const filteredEnrollments = enrollments.filter(enrollment =>
    enrollment.section.grade.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    enrollment.section.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  // Generate a consistent color based on string
  const stringToColor = (str: string) => {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }
    const h = hash % 360;
    return `hsl(${h}, 70%, 50%)`;
  };

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
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
          <Button variant="outline">
            <Filter className="w-4 h-4 mr-2" />
            Filtros
          </Button>
        </div>

        {loading ? (
          <div className="flex justify-center py-12">
            <Loader2 className="h-8 w-8 animate-spin text-primary" />
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredEnrollments.length > 0 ? (
              filteredEnrollments.map((enrollment) => (
                <CourseCard
                  key={enrollment.id}
                  title={`${enrollment.section.grade.name} - Sección ${enrollment.section.name}`}
                  teacher="Profesor Asignado" // Backend needs to send teacher info
                  progress={Math.floor(Math.random() * 100)} // Placeholder for real progress
                  nextClass="Ver Horario"
                  students={enrollment.section.capacity} // Using capacity as proxy for now
                  color={stringToColor(enrollment.section.grade.name)}
                />
              ))
            ) : (
              <div className="col-span-full text-center py-12 text-muted-foreground">
                No se encontraron cursos activos.
              </div>
            )}
          </div>
        )}
      </main>
    </div>
  );
};

export default Cursos;
