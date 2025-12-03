import { api } from './api';

export interface ChatMessage {
    id: number;
    content: string;
    sender: {
        id: number;
        firstName: string;
        lastName: string;
    };
    createdAt: string;
    isRead: boolean;
}

export interface ChatRoom {
    id: number;
    name: string;
    type: string;
    lastMessage?: ChatMessage;
    participants: {
        id: number;
        firstName: string;
        lastName: string;
    }[];
    unreadCount: number;
}

export const chatService = {
    getRooms: async (): Promise<ChatRoom[]> => {
        return api.get<ChatRoom[]>('/chat/rooms');
    },

    getMessages: async (roomId: number): Promise<ChatMessage[]> => {
        return api.get<ChatMessage[]>(`/chat/rooms/${roomId}/messages`);
    },

    sendMessage: async (roomId: number, content: string): Promise<ChatMessage> => {
        return api.post<ChatMessage>(`/chat/rooms/${roomId}/messages`, { content });
    },

    markAsRead: async (messageId: number): Promise<void> => {
        return api.post(`/chat/messages/${messageId}/read`, {});
    }
};
