import { onCLS, onFID, onLCP, onFCP, onTTFB } from 'web-vitals'

const reportWebVitals = (onPerfEntry?: (metric: any) => void) => {
    if (onPerfEntry && onPerfEntry instanceof Function) {
        onCLS(onPerfEntry)
        onFID(onPerfEntry)
        onLCP(onPerfEntry)
        onFCP(onPerfEntry)
        onTTFB(onPerfEntry)
    }
}

export const logVitals = () => {
    reportWebVitals(console.log)
    // Aquí se podría enviar a Google Analytics o endpoint propio
    // reportWebVitals(sendToAnalytics)
}

export default reportWebVitals
