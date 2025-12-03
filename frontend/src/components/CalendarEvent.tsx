import { Card } from "@/components/ui/card";
import { Calendar, MapPin } from "lucide-react";

interface CalendarEventProps {
  title: string;
  date: string;
  time: string;
  location?: string;
  color: string;
}

export const CalendarEvent = ({ 
  title, 
  date, 
  time, 
  location,
  color 
}: CalendarEventProps) => {
  return (
    <Card className="p-4 border hover:shadow-md transition-smooth">
      <div className="flex gap-3">
        <div 
          className="w-1 rounded-full flex-shrink-0"
          style={{ backgroundColor: color }}
        />
        
        <div className="flex-1 min-w-0">
          <h4 className="font-semibold text-foreground mb-2 text-sm">
            {title}
          </h4>
          
          <div className="space-y-1">
            <div className="flex items-center gap-2 text-xs text-muted-foreground">
              <Calendar className="w-3 h-3" />
              <span>{date} • {time}</span>
            </div>
            
            {location && (
              <div className="flex items-center gap-2 text-xs text-muted-foreground">
                <MapPin className="w-3 h-3" />
                <span>{location}</span>
              </div>
            )}
          </div>
        </div>
      </div>
    </Card>
  );
};
