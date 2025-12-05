import { useState, useEffect, useCallback } from 'react';
import { useMercure } from './useMercure';

interface ChatMessage {
    id: number;
    roomId: number;
    senderId: number;
    senderName: string;
    content: string;
    createdAt: string;
    readBy?: number[];
}

interface UseChatReturn {
    messages: ChatMessage[];
    isConnected: boolean;
    isLoading: boolean;
    error: string | null;
    sendMessage: (content: string) => Promise<void>;
    markAsRead: (messageId: number) => Promise<void>;
    loadMore: () => Promise<void>;
}

/**
 * useChat - Real-time chat hook using Mercure
 * Provides seamless real-time messaging without polling
 */
export function useChat(roomId: number): UseChatReturn {
    const [messages, setMessages] = useState<ChatMessage[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    // Subscribe to real-time updates for this room
    const { isConnected, lastMessage } = useMercure({
        topics: [`/chat/rooms/${roomId}`],
        onMessage: (msg) => {
            if (msg.type === 'new_message') {
                setMessages((prev) => [...prev, msg.data as ChatMessage]);
            } else if (msg.type === 'message_read') {
                setMessages((prev) =>
                    prev.map((m) =>
                        m.id === msg.data.messageId
                            ? { ...m, readBy: [...(m.readBy || []), msg.data.userId] }
                            : m
                    )
                );
            }
        },
    });

    // Load initial messages
    useEffect(() => {
        const loadMessages = async () => {
            try {
                setIsLoading(true);
                const response = await fetch(`/api/chat/rooms/${roomId}/messages`, {
                    credentials: 'include',
                });

                if (!response.ok) throw new Error('Failed to load messages');

                const data = await response.json();
                setMessages(data);
            } catch (err) {
                setError(err instanceof Error ? err.message : 'Failed to load messages');
            } finally {
                setIsLoading(false);
            }
        };

        loadMessages();
    }, [roomId]);

    const sendMessage = useCallback(
        async (content: string) => {
            try {
                const response = await fetch(`/api/chat/rooms/${roomId}/messages`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ content }),
                });

                if (!response.ok) throw new Error('Failed to send message');

                // Message will be added via Mercure subscription
            } catch (err) {
                setError(err instanceof Error ? err.message : 'Failed to send message');
                throw err;
            }
        },
        [roomId]
    );

    const markAsRead = useCallback(
        async (messageId: number) => {
            try {
                await fetch(`/api/chat/messages/${messageId}/read`, {
                    method: 'POST',
                    credentials: 'include',
                });
            } catch (err) {
                console.error('Failed to mark as read:', err);
            }
        },
        []
    );

    const loadMore = useCallback(async () => {
        if (messages.length === 0) return;

        const oldestMessage = messages[0];
        try {
            const response = await fetch(
                `/api/chat/rooms/${roomId}/messages?before=${oldestMessage.id}`,
                { credentials: 'include' }
            );

            if (!response.ok) throw new Error('Failed to load more messages');

            const olderMessages = await response.json();
            setMessages((prev) => [...olderMessages, ...prev]);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Failed to load more');
        }
    }, [roomId, messages]);

    return {
        messages,
        isConnected,
        isLoading,
        error,
        sendMessage,
        markAsRead,
        loadMore,
    };
}

export default useChat;
