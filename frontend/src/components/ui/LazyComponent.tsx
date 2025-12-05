import React, { Suspense, lazy, ComponentType } from 'react';
import { Skeleton } from './Skeleton';

interface LazyComponentProps {
    fallback?: React.ReactNode;
    children?: React.ReactNode;
}

/**
 * Creates a lazy-loaded component with automatic code splitting
 * @param importFn - Dynamic import function
 * @param fallback - Optional custom fallback component
 */
export function createLazyComponent<T extends ComponentType<any>>(
    importFn: () => Promise<{ default: T }>,
    fallback?: React.ReactNode
) {
    const LazyComponent = lazy(importFn);

    return function WrappedLazyComponent(props: React.ComponentProps<T>) {
        return (
            <Suspense fallback={fallback || <ComponentSkeleton />}>
                <LazyComponent {...props} />
            </Suspense>
        );
    };
}

/**
 * Default skeleton for lazy-loaded components
 */
const ComponentSkeleton: React.FC = () => (
    <div className="animate-pulse space-y-4 p-4">
        <div className="h-4 bg-gray-200 rounded w-3/4"></div>
        <div className="h-4 bg-gray-200 rounded w-1/2"></div>
        <div className="h-32 bg-gray-200 rounded"></div>
    </div>
);

/**
 * Page-level skeleton for lazy-loaded pages
 */
export const PageSkeleton: React.FC = () => (
    <div className="animate-pulse p-6 space-y-6">
        {/* Header skeleton */}
        <div className="flex justify-between items-center">
            <div className="h-8 bg-gray-200 rounded w-48"></div>
            <div className="h-10 bg-gray-200 rounded w-32"></div>
        </div>

        {/* Stats cards skeleton */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            {[...Array(4)].map((_, i) => (
                <div key={i} className="h-24 bg-gray-200 rounded-lg"></div>
            ))}
        </div>

        {/* Content skeleton */}
        <div className="h-96 bg-gray-200 rounded-lg"></div>
    </div>
);

/**
 * Table skeleton for lazy-loaded tables
 */
export const TableSkeleton: React.FC<{ rows?: number }> = ({ rows = 5 }) => (
    <div className="animate-pulse space-y-3">
        {/* Table header */}
        <div className="h-10 bg-gray-200 rounded"></div>

        {/* Table rows */}
        {[...Array(rows)].map((_, i) => (
            <div key={i} className="h-12 bg-gray-100 rounded"></div>
        ))}
    </div>
);

/**
 * Card skeleton for lazy-loaded cards
 */
export const CardSkeleton: React.FC = () => (
    <div className="animate-pulse bg-white rounded-lg shadow p-4 space-y-3">
        <div className="h-4 bg-gray-200 rounded w-3/4"></div>
        <div className="h-4 bg-gray-200 rounded w-1/2"></div>
        <div className="h-20 bg-gray-200 rounded"></div>
    </div>
);

export default createLazyComponent;
