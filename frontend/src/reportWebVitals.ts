import { onCLS, onFID, onLCP, onFCP, onTTFB, type Metric } from 'web-vitals'

const reportWebVitals = (onPerfEntry?: (metric: Metric) => void) => {
    if (onPerfEntry && onPerfEntry instanceof Function) {
        onCLS(onPerfEntry)
        onFID(onPerfEntry)
        onLCP(onPerfEntry)
        onFCP(onPerfEntry)
        onTTFB(onPerfEntry)
    }
}

export const logVitals = () => {
    // Web vitals can be sent to analytics service
    // reportWebVitals(sendToAnalytics)
}

export default reportWebVitals
