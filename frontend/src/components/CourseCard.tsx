import { Card } from "@/components/ui/card";
import { Progress } from "@/components/ui/progress";
import { Button } from "@/components/ui/button";
import { BookOpen, Clock, Users } from "lucide-react";

interface CourseCardProps {
  title: string;
  teacher: string;
  progress: number;
  nextClass?: string;
  students: number;
  color: string;
}

export const CourseCard = ({ 
  title, 
  teacher, 
  progress, 
  nextClass,
  students,
  color 
}: CourseCardProps) => {
  return (
    <Card className="overflow-hidden border shadow-elegant hover:shadow-lg transition-smooth group">
      <div 
        className="h-32 relative overflow-hidden"
        style={{ background: `linear-gradient(135deg, ${color}, ${color}dd)` }}
      >
        <div className="absolute inset-0 bg-gradient-to-br from-transparent to-black/20" />
        <div className="absolute bottom-4 left-4 right-4">
          <h3 className="text-xl font-bold text-white mb-1 group-hover:scale-105 transition-smooth">
            {title}
          </h3>
          <p className="text-sm text-white/90">{teacher}</p>
        </div>
      </div>

      <div className="p-5 space-y-4">
        <div>
          <div className="flex items-center justify-between text-sm mb-2">
            <span className="text-muted-foreground">Progreso del curso</span>
            <span className="font-semibold text-foreground">{progress}%</span>
          </div>
          <Progress value={progress} className="h-2" />
        </div>

        <div className="flex items-center gap-4 text-sm text-muted-foreground">
          <div className="flex items-center gap-1">
            <Users className="w-4 h-4" />
            <span>{students}</span>
          </div>
          {nextClass && (
            <div className="flex items-center gap-1">
              <Clock className="w-4 h-4" />
              <span>{nextClass}</span>
            </div>
          )}
        </div>

        <Button className="w-full" variant="default">
          <BookOpen className="w-4 h-4 mr-2" />
          Continuar aprendiendo
        </Button>
      </div>
    </Card>
  );
};
