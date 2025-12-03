import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Clock, FileText } from "lucide-react";
import { cn } from "@/lib/utils";

interface UpcomingTaskProps {
  title: string;
  course: string;
  dueDate: string;
  priority: "high" | "medium" | "low";
  type: string;
}

export const UpcomingTask = ({ 
  title, 
  course, 
  dueDate, 
  priority,
  type 
}: UpcomingTaskProps) => {
  const priorityStyles = {
    high: "bg-destructive/10 text-destructive border-destructive/20",
    medium: "bg-warning/10 text-warning border-warning/20",
    low: "bg-success/10 text-success border-success/20",
  };

  const priorityLabels = {
    high: "Alta",
    medium: "Media",
    low: "Baja",
  };

  return (
    <Card className="p-4 border hover:shadow-md transition-smooth">
      <div className="flex items-start gap-3">
        <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
          <FileText className="w-5 h-5 text-primary" />
        </div>
        
        <div className="flex-1 min-w-0">
          <div className="flex items-start justify-between gap-2 mb-2">
            <h4 className="font-semibold text-foreground text-sm leading-tight">
              {title}
            </h4>
            <Badge 
              variant="outline" 
              className={cn("text-xs flex-shrink-0", priorityStyles[priority])}
            >
              {priorityLabels[priority]}
            </Badge>
          </div>
          
          <p className="text-sm text-muted-foreground mb-2">{course}</p>
          
          <div className="flex items-center gap-4 text-xs text-muted-foreground">
            <div className="flex items-center gap-1">
              <Clock className="w-3 h-3" />
              <span>{dueDate}</span>
            </div>
            <span className="px-2 py-0.5 rounded-full bg-muted text-muted-foreground">
              {type}
            </span>
          </div>
        </div>
      </div>
    </Card>
  );
};
