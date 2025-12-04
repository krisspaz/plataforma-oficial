/**
 * Performance Monitoring Utilities
 * 
 * Provides utilities for monitoring and optimizing performance
    performance.mark(`${label}-end`);
    performance.measure(label, `${label}-start`, `${label}-end`);

    const measure = performance.getEntriesByName(label)[0];
    const duration = measure?.duration || 0;

    // Store metric
    const existing = this.metrics.get(label) || [];
    existing.push(duration);
    this.metrics.set(label, existing);

    // Cleanup
    performance.clearMarks(`${label}-start`);
    performance.clearMarks(`${label}-end`);
    performance.clearMeasures(label);

    return duration;
}

/**
 * Gets average duration for a metric
 */
getAverage(label: string): number {
    const values = this.metrics.get(label) || [];
    if (values.length === 0) return 0;

    const sum = values.reduce((a, b) => a + b, 0);
    return sum / values.length;
}

/**
 * Gets all metrics
 */
getAllMetrics(): Record < string, { avg: number; count: number } > {
    const result: Record<string, { avg: number; count: number }> = { };

this.metrics.forEach((values, label) => {
    const sum = values.reduce((a, b) => a + b, 0);
    result[label] = {
        avg: sum / values.length,
        count: values.length
    };
});

return result;
  }

/**
 * Clears all metrics
 */
clear(): void {
    this.metrics.clear();
}
}

export const perfMonitor = new PerformanceMonitor();

/**
 * Debounce function for performance
 */
export function debounce<T extends (...args: unknown[]) => unknown>(
    func: T,
    wait: number
): (...args: Parameters<T>) => void {
    let timeout: NodeJS.Timeout | null = null;

    return function executedFunction(...args: Parameters<T>) {
        const later = () => {
            timeout = null;
            func(...args);
        };

        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function for performance
 */
export function throttle<T extends (...args: unknown[]) => unknown>(
    func: T,
    limit: number
): (...args: Parameters<T>) => void {
    let inThrottle: boolean;

    return function executedFunction(...args: Parameters<T>) {
        if (!inThrottle) {
            func(...args);
            inThrottle = true;
            setTimeout(() => (inThrottle = false), limit);
        }
    };
}

/**
 * Lazy load images
 */
export const lazyLoadImage = (img: HTMLImageElement): void => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target as HTMLImageElement;
                const src = target.dataset.src;

                if (src) {
                    target.src = src;
                    target.removeAttribute('data-src');
                    observer.unobserve(target);
                }
            }
        });
    });

    observer.observe(img);
};

/**
 * Preload critical resources
 */
export const preloadResource = (href: string, as: string): void => {
    const link = document.createElement('link');
    link.rel = 'preload';
    link.href = href;
    link.as = as;
    document.head.appendChild(link);
};

/**
 * Prefetch next page
 */
export const prefetchPage = (href: string): void => {
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.href = href;
    document.head.appendChild(link);
};

/**
 * Measure Web Vitals
 */
export const measureWebVitals = (): void => {
    // First Contentful Paint
    const paintEntries = performance.getEntriesByType('paint');
    const fcp = paintEntries.find(entry => entry.name === 'first-contentful-paint');

    if (fcp) {
        console.log('FCP:', fcp.startTime);
    }

    // Largest Contentful Paint
    const observer = new PerformanceObserver((list) => {
        const entries = list.getEntries();
        const lastEntry = entries[entries.length - 1];
        console.log('LCP:', lastEntry.startTime);
    });

    observer.observe({ entryTypes: ['largest-contentful-paint'] });

    // Cumulative Layout Shift
    let clsValue = 0;
    const clsObserver = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
            if (!(entry as LayoutShift).hadRecentInput) {
                clsValue += (entry as LayoutShift).value;
            }
        }
        console.log('CLS:', clsValue);
    });

    clsObserver.observe({ entryTypes: ['layout-shift'] });
};

interface LayoutShift extends PerformanceEntry {
    value: number;
    hadRecentInput: boolean;
}

/**
 * Bundle size analyzer
 */
export const analyzeBundleSize = (): void => {
    if (process.env.NODE_ENV === 'development') {
        const scripts = Array.from(document.querySelectorAll('script[src]'));

        scripts.forEach(script => {
            const src = (script as HTMLScriptElement).src;
            fetch(src)
                .then(response => response.blob())
                .then(blob => {
                    const sizeKB = (blob.size / 1024).toFixed(2);
                    console.log(`Bundle: ${src.split('/').pop()} - ${sizeKB} KB`);
                });
        });
    }
};
