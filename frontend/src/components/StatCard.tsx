import { LucideIcon } from "lucide-react";
import { Card } from "@/components/ui/card";
import { cn } from "@/lib/utils";

interface StatCardProps {
  icon: LucideIcon;
  title: string;
  value: string | number;
  subtitle?: string;
  trend?: "up" | "down" | "neutral";
  variant?: "primary" | "accent" | "success" | "warning";
}

export const StatCard = ({ 
  icon: Icon, 
  title, 
  value, 
  subtitle,
  trend = "neutral",
  variant = "primary" 
}: StatCardProps) => {
  const variantStyles = {
    primary: "from-primary/10 to-primary/5 border-primary/20",
    accent: "from-accent/10 to-accent/5 border-accent/20",
    success: "from-success/10 to-success/5 border-success/20",
    warning: "from-warning/10 to-warning/5 border-warning/20",
  };

  const iconVariantStyles = {
    primary: "bg-primary text-primary-foreground",
    accent: "bg-accent text-accent-foreground",
    success: "bg-success text-success-foreground",
    warning: "bg-warning text-warning-foreground",
  };

  return (
    <Card className={cn(
      "p-6 border bg-gradient-to-br transition-smooth hover:shadow-elegant",
      variantStyles[variant]
    )}>
      <div className="flex items-start justify-between">
        <div className="flex-1">
          <p className="text-sm text-muted-foreground mb-1">{title}</p>
          <p className="text-3xl font-bold text-foreground mb-2">{value}</p>
          {subtitle && (
            <p className="text-xs text-muted-foreground">{subtitle}</p>
          )}
        </div>
        <div className={cn(
          "w-12 h-12 rounded-xl flex items-center justify-center shadow-md",
          iconVariantStyles[variant]
        )}>
          <Icon className="w-6 h-6" />
        </div>
      </div>
    </Card>
  );
};
