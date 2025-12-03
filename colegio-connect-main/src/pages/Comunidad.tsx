import { useEffect, useState, useRef } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { Card } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { chatService, ChatRoom, ChatMessage } from "@/services/chat.service";
import { useAuth } from "@/context/AuthContext";
import { Send, Search, MoreVertical, Phone, Video } from "lucide-react";
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

const Comunidad = () => {
  const { user } = useAuth();
  const [rooms, setRooms] = useState<ChatRoom[]>([]);
  const [selectedRoom, setSelectedRoom] = useState<ChatRoom | null>(null);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [newMessage, setNewMessage] = useState('');
  const scrollRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    fetchRooms();
    // Poll for new rooms/unread counts every 30s
    const interval = setInterval(fetchRooms, 30000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (selectedRoom) {
      fetchMessages(selectedRoom.id);
      // Poll for new messages every 5s
      const interval = setInterval(() => fetchMessages(selectedRoom.id), 5000);
      return () => clearInterval(interval);
    }
  }, [selectedRoom]);

  useEffect(() => {
    if (scrollRef.current) {
      scrollRef.current.scrollIntoView({ behavior: 'smooth' });
    }
  }, [messages]);

  const fetchRooms = async () => {
    try {
      const data = await chatService.getRooms();
      setRooms(data);
    } catch (error) {
      console.error('Failed to fetch rooms', error);
    }
  };

  const fetchMessages = async (roomId: number) => {
    try {
      const data = await chatService.getMessages(roomId);
      setMessages(data);
    } catch (error) {
      console.error('Failed to fetch messages', error);
    }
  };

  const handleSendMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedRoom || !newMessage.trim()) return;

    try {
      const message = await chatService.sendMessage(selectedRoom.id, newMessage);
      setMessages([...messages, message]);
      setNewMessage('');
    } catch (error) {
      console.error('Failed to send message', error);
    }
  };

  return (
    <div className="flex h-screen bg-background overflow-hidden">
      <Sidebar />

      <main className="flex-1 ml-64 flex">
        {/* Chat List */}
        <div className="w-80 border-r bg-card flex flex-col">
          <div className="p-4 border-b">
            <h1 className="text-xl font-bold mb-4">Mensajes</h1>
            <div className="relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <Input placeholder="Buscar conversación..." className="pl-9" />
            </div>
          </div>

          <ScrollArea className="flex-1">
            <div className="p-2 space-y-2">
              {rooms.map((room) => (
                <div
                  key={room.id}
                  onClick={() => setSelectedRoom(room)}
                  className={`flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors ${selectedRoom?.id === room.id ? 'bg-accent' : 'hover:bg-accent/50'
                    }`}
                >
                  <Avatar>
                    <AvatarFallback>
                      {room.name.substring(0, 2).toUpperCase()}
                    </AvatarFallback>
                  </Avatar>
                  <div className="flex-1 min-w-0">
                    <div className="flex justify-between items-start">
                      <h3 className="font-medium truncate">{room.name}</h3>
                      {room.lastMessage && (
                        <span className="text-xs text-muted-foreground">
                          {format(new Date(room.lastMessage.createdAt), 'HH:mm')}
                        </span>
                      )}
                    </div>
                    <p className="text-sm text-muted-foreground truncate">
                      {room.lastMessage?.content || 'Sin mensajes'}
                    </p>
                  </div>
                  {room.unreadCount > 0 && (
                    <div className="w-5 h-5 rounded-full bg-primary text-primary-foreground text-xs flex items-center justify-center">
                      {room.unreadCount}
                    </div>
                  )}
                </div>
              ))}
            </div>
          </ScrollArea>
        </div>

        {/* Chat Area */}
        <div className="flex-1 flex flex-col bg-background">
          {selectedRoom ? (
            <>
              {/* Chat Header */}
              <div className="p-4 border-b flex items-center justify-between bg-card">
                <div className="flex items-center gap-3">
                  <Avatar>
                    <AvatarFallback>
                      {selectedRoom.name.substring(0, 2).toUpperCase()}
                    </AvatarFallback>
                  </Avatar>
                  <div>
                    <h2 className="font-bold">{selectedRoom.name}</h2>
                    <p className="text-xs text-muted-foreground">
                      {selectedRoom.participants.length} participantes
                    </p>
                  </div>
                </div>
                <div className="flex items-center gap-2">
                  <Button variant="ghost" size="icon"><Phone className="w-5 h-5" /></Button>
                  <Button variant="ghost" size="icon"><Video className="w-5 h-5" /></Button>
                  <Button variant="ghost" size="icon"><MoreVertical className="w-5 h-5" /></Button>
                </div>
              </div>

              {/* Messages */}
              <ScrollArea className="flex-1 p-4">
                <div className="space-y-4">
                  {messages.map((msg) => {
                    const isMe = msg.sender.id === user?.id;
                    return (
                      <div
                        key={msg.id}
                        className={`flex ${isMe ? 'justify-end' : 'justify-start'}`}
                      >
                        <div
                          className={`max-w-[70%] rounded-2xl p-3 ${isMe
                              ? 'bg-primary text-primary-foreground rounded-tr-none'
                              : 'bg-accent rounded-tl-none'
                            }`}
                        >
                          {!isMe && (
                            <p className="text-xs font-medium mb-1 opacity-70">
                              {msg.sender.firstName}
                            </p>
                          )}
                          <p>{msg.content}</p>
                          <p className={`text-[10px] mt-1 text-right ${isMe ? 'text-primary-foreground/70' : 'text-muted-foreground'
                            }`}>
                            {format(new Date(msg.createdAt), 'HH:mm')}
                          </p>
                        </div>
                      </div>
                    );
                  })}
                  <div ref={scrollRef} />
                </div>
              </ScrollArea>

              {/* Input Area */}
              <div className="p-4 bg-card border-t">
                <form onSubmit={handleSendMessage} className="flex gap-2">
                  <Input
                    value={newMessage}
                    onChange={(e) => setNewMessage(e.target.value)}
                    placeholder="Escribe un mensaje..."
                    className="flex-1"
                  />
                  <Button type="submit" size="icon" disabled={!newMessage.trim()}>
                    <Send className="w-5 h-5" />
                  </Button>
                </form>
              </div>
            </>
          ) : (
            <div className="flex-1 flex flex-col items-center justify-center text-muted-foreground">
              <div className="w-16 h-16 bg-accent rounded-full flex items-center justify-center mb-4">
                <Search className="w-8 h-8" />
              </div>
              <h3 className="text-xl font-bold mb-2">Selecciona una conversación</h3>
              <p>Elige un chat de la lista para comenzar a escribir</p>
            </div>
          )}
        </div>
      </main>
    </div>
  );
};

export default Comunidad;
