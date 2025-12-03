import { useRegisterSW } from 'virtual:pwa-register/react'
import { Button } from '@/components/ui/button'
import { ToastAction } from '@/components/ui/toast'
import { useToast } from '@/components/ui/use-toast'
import { useEffect } from 'react'

export function PWAReloadPrompt() {
    const {
        offlineReady: [offlineReady, setOfflineReady],
        needRefresh: [needRefresh, setNeedRefresh],
        updateServiceWorker,
    } = useRegisterSW({
        onRegistered(r) {
            console.log('SW Registered: ' + r)
        },
        onRegisterError(error) {
            console.log('SW registration error', error)
        },
    })

    const { toast } = useToast()

    useEffect(() => {
        if (offlineReady) {
            toast({
                title: "App lista para trabajar offline",
                description: "La aplicación ha sido guardada en caché.",
            })
            setOfflineReady(false)
        }
    }, [offlineReady, setOfflineReady, toast])

    useEffect(() => {
        if (needRefresh) {
            toast({
                title: "Nueva versión disponible",
                description: "Hay una nueva versión de la aplicación. Haz clic para actualizar.",
                action: (
                    <ToastAction altText="Actualizar" onClick={() => updateServiceWorker(true)}>
                        Actualizar
                    </ToastAction>
                ),
                duration: Infinity,
            })
        }
    }, [needRefresh, updateServiceWorker, toast])

    return null
}
