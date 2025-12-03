/// <reference lib="webworker" />
import { cleanupOutdatedCaches, createHandlerBoundToURL, precacheAndRoute } from 'workbox-precaching'
import { NavigationRoute, registerRoute } from 'workbox-routing'
import { CacheFirst, NetworkFirst, StaleWhileRevalidate } from 'workbox-strategies'
import { ExpirationPlugin } from 'workbox-expiration'
import { CacheableResponsePlugin } from 'workbox-cacheable-response'

declare let self: ServiceWorkerGlobalScope

// Limpiar caches antiguos
cleanupOutdatedCaches()

// Precache de assets generados por build
precacheAndRoute(self.__WB_MANIFEST)

// Estrategia para navegación (SPA)
// Si es navegación, servir index.html desde cache, o red si falla
const handler = createHandlerBoundToURL('/index.html')
const navigationRoute = new NavigationRoute(handler, {
    // No manejar rutas de API o archivos que no sean de navegación
    denylist: [/^\/api/, /^\/admin/, /\.[a-z]+$/],
})
registerRoute(navigationRoute)

// Cache de API (StaleWhileRevalidate para datos que cambian frecuentemente)
registerRoute(
    ({ url }) => url.pathname.startsWith('/api/'),
    new NetworkFirst({
        cacheName: 'api-cache',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [0, 200],
            }),
            new ExpirationPlugin({
                maxEntries: 50,
                maxAgeSeconds: 60 * 60 * 24, // 1 día
            }),
        ],
    })
)

// Cache de imágenes (CacheFirst)
registerRoute(
    ({ request }) => request.destination === 'image',
    new CacheFirst({
        cacheName: 'images-cache',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [0, 200],
            }),
            new ExpirationPlugin({
                maxEntries: 60,
                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 días
            }),
        ],
    })
)

// Cache de fuentes (CacheFirst)
registerRoute(
    ({ request }) => request.destination === 'font',
    new CacheFirst({
        cacheName: 'fonts-cache',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [0, 200],
            }),
            new ExpirationPlugin({
                maxEntries: 30,
                maxAgeSeconds: 60 * 60 * 24 * 365, // 1 año
            }),
        ],
    })
)

// Manejo de notificaciones Push
self.addEventListener('push', (event) => {
    const data = event.data?.json() ?? { title: 'Nueva notificación', body: '' }

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: '/pwa-192x192.png',
            badge: '/badge.png',
            data: data.url
        })
    )
})

self.addEventListener('notificationclick', (event) => {
    event.notification.close()

    if (event.notification.data) {
        event.waitUntil(
            self.clients.openWindow(event.notification.data)
        )
    }
})

// Manejo de mensajes desde la app
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting()
    }
})
