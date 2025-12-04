/**
 * Skeleton Loader Component
 * 
 * Provides loading placeholders for better perceived performance
 */

import { cn } from '@/lib/utils';

interface SkeletonProps {
    className?: string;
    variant?: 'text' | 'circular' | 'rectangular';
    width?: string | number;
    height?: string | number;
    animation?: 'pulse' | 'wave' | 'none';
}

export const Skeleton: React.FC<SkeletonProps> = ({
    className,
    variant = 'text',
    width,
    height,
    animation = 'pulse'
}) => {
    const baseClasses = 'bg-muted';

    const variantClasses = {
        text: 'rounded h-4',
        circular: 'rounded-full',
        rectangular: 'rounded-md'
    };

    const animationClasses = {
        pulse: 'animate-pulse',
        wave: 'animate-shimmer bg-gradient-to-r from-muted via-muted-foreground/10 to-muted bg-[length:200%_100%]',
        none: ''
    };

    const style = {
        width: typeof width === 'number' ? `${width}px` : width,
        height: typeof height === 'number' ? `${height}px` : height
    };

    return (
        <div
            className={cn(
                baseClasses,
                variantClasses[variant],
                animationClasses[animation],
                className
            )}
            style={style}
            aria-busy="true"
            aria-live="polite"
        />
    );
};

/**
 * Card Skeleton for dashboard cards
 */
export const CardSkeleton: React.FC = () => (
    <div className="p-6 border rounded-lg space-y-4">
        <div className="flex items-center justify-between">
            <Skeleton width={120} height={20} />
            <Skeleton variant="circular" width={40} height={40} />
        </div>
        <Skeleton width="60%" height={32} />
        <Skeleton width="40%" height={16} />
    </div>
);

/**
 * Table Skeleton for data tables
 */
export const TableSkeleton: React.FC<{ rows?: number }> = ({ rows = 5 }) => (
    <div className="space-y-3">
        {Array.from({ length: rows }).map((_, i) => (
            <div key={i} className="flex gap-4">
                <Skeleton width="20%" />
                <Skeleton width="30%" />
                <Skeleton width="25%" />
                <Skeleton width="25%" />
            </div>
        ))}
    </div>
);

/**
 * List Skeleton for lists
 */
export const ListSkeleton: React.FC<{ items?: number }> = ({ items = 3 }) => (
    <div className="space-y-4">
        {Array.from({ length: items }).map((_, i) => (
            <div key={i} className="flex items-center gap-4">
                <Skeleton variant="circular" width={48} height={48} />
                <div className="flex-1 space-y-2">
                    <Skeleton width="60%" />
                    <Skeleton width="40%" />
                </div>
            </div>
        ))}
    </div>
);
