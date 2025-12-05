import { useState, useEffect, useCallback, useRef } from 'react';

interface MercureMessage {
    id: string;
    type: string;
    data: any;
    timestamp: string;
}

interface UseMercureOptions {
    topics: string[];
    hubUrl?: string;
    jwt?: string;
    onMessage?: (message: MercureMessage) => void;
    onError?: (error: Event) => void;
    reconnectDelay?: number;
}

/**
 * useMercure - React hook for real-time updates via Mercure/SSE
 * Replaces polling with real-time WebSocket-like connections
 */
export function useMercure({
    topics,
    hubUrl = import.meta.env.VITE_MERCURE_URL || '/.well-known/mercure',
    jwt,
    onMessage,
    onError,
    reconnectDelay = 3000,
}: UseMercureOptions) {
    const [isConnected, setIsConnected] = useState(false);
    const [lastMessage, setLastMessage] = useState<MercureMessage | null>(null);
    const [connectionError, setConnectionError] = useState<string | null>(null);
    const eventSourceRef = useRef<EventSource | null>(null);
    const reconnectTimeoutRef = useRef<number | null>(null);

    const connect = useCallback(() => {
        if (!topics.length) return;

        // Build URL with topics
        const url = new URL(hubUrl, window.location.origin);
        topics.forEach((topic) => url.searchParams.append('topic', topic));

        // Add authorization if provided
        const headers: HeadersInit = {};
        if (jwt) {
            // For cookie-based auth, no need to add header
            // For JWT, we'd need to use a different approach (fetch + EventSource)
        }

        try {
            // Close existing connection
            if (eventSourceRef.current) {
                eventSourceRef.current.close();
            }

            const eventSource = new EventSource(url.toString(), { withCredentials: true });
            eventSourceRef.current = eventSource;

            eventSource.onopen = () => {
                setIsConnected(true);
                setConnectionError(null);
                console.log('[Mercure] Connected to', url.toString());
            };

            eventSource.onmessage = (event) => {
                try {
                    const message: MercureMessage = JSON.parse(event.data);
                    setLastMessage(message);
                    onMessage?.(message);
                } catch (e) {
                    console.error('[Mercure] Failed to parse message:', e);
                }
            };

            eventSource.onerror = (error) => {
                console.error('[Mercure] Connection error:', error);
                setIsConnected(false);
                setConnectionError('Connection lost');
                onError?.(error);

                // Attempt reconnection
                if (reconnectTimeoutRef.current) {
                    clearTimeout(reconnectTimeoutRef.current);
                }
                reconnectTimeoutRef.current = window.setTimeout(() => {
                    console.log('[Mercure] Attempting to reconnect...');
                    connect();
                }, reconnectDelay);
            };
        } catch (error) {
            console.error('[Mercure] Failed to connect:', error);
            setConnectionError('Failed to connect');
        }
    }, [topics, hubUrl, jwt, onMessage, onError, reconnectDelay]);

    const disconnect = useCallback(() => {
        if (reconnectTimeoutRef.current) {
            clearTimeout(reconnectTimeoutRef.current);
        }
        if (eventSourceRef.current) {
            eventSourceRef.current.close();
            eventSourceRef.current = null;
        }
        setIsConnected(false);
    }, []);

    useEffect(() => {
        connect();

        return () => {
            disconnect();
        };
    }, [connect, disconnect]);

    return {
        isConnected,
        lastMessage,
        connectionError,
        reconnect: connect,
        disconnect,
    };
}

export default useMercure;
