import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Megaphone, Plus, Loader2, Clock, User, X } from 'lucide-react';
import { coordinationService, Announcement } from '@/services/coordination.service';
import { toast } from 'sonner';
import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';

export const AnnouncementBoard: React.FC = () => {
    const [announcements, setAnnouncements] = useState<Announcement[]>([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [filterType, setFilterType] = useState<string>('');

    const [formData, setFormData] = useState({
        title: '',
        content: '',
        type: 'general',
        expiresAt: '',
    });

    useEffect(() => {
        loadAnnouncements();
    }, [filterType]);

    const loadAnnouncements = async () => {
        setLoading(true);
        try {
            const data = await coordinationService.getAnnouncements(filterType || undefined);
            setAnnouncements(data);
        } catch (error) {
            toast.error('Error al cargar anuncios');
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async () => {
        if (!formData.title || !formData.content) {
            toast.error('Título y contenido son requeridos');
            return;
        }

        setSubmitting(true);
        try {
            await coordinationService.createAnnouncement({
                title: formData.title,
                content: formData.content,
                type: formData.type,
                expires_at: formData.expiresAt || undefined,
            });

            toast.success('Anuncio publicado exitosamente');
            setShowModal(false);
            setFormData({ title: '', content: '', type: 'general', expiresAt: '' });
            loadAnnouncements();
        } catch (error) {
            toast.error('Error al publicar anuncio');
        } finally {
            setSubmitting(false);
        }
    };

    const getTypeBadgeColor = (type: string) => {
        const colors: Record<string, string> = {
            general: 'bg-blue-100 text-blue-800',
            teachers: 'bg-green-100 text-green-800',
            parents: 'bg-purple-100 text-purple-800',
            students: 'bg-orange-100 text-orange-800',
            specific_grade: 'bg-pink-100 text-pink-800',
        };
        return colors[type] || 'bg-gray-100 text-gray-800';
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold flex items-center gap-2">
                        <Megaphone className="w-6 h-6" />
                        Tablero de Anuncios
                    </h2>
                    <p className="text-slate-500">Comunícate con maestros, padres y estudiantes</p>
                </div>
                <Button onClick={() => setShowModal(true)}>
                    <Plus className="w-4 h-4 mr-2" />
                    Nuevo Anuncio
                </Button>
            </div>

            {/* Filters */}
            <div className="flex gap-4">
                <Select value={filterType} onValueChange={setFilterType}>
                    <SelectTrigger className="w-48">
                        <SelectValue placeholder="Todos los tipos" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Todos</SelectItem>
                        <SelectItem value="general">General</SelectItem>
                        <SelectItem value="teachers">Maestros</SelectItem>
                        <SelectItem value="parents">Padres</SelectItem>
                        <SelectItem value="students">Estudiantes</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            {/* Announcements Grid */}
            {loading ? (
                <div className="flex justify-center items-center h-48">
                    <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
                </div>
            ) : announcements.length === 0 ? (
                <Card className="text-center py-12">
                    <CardContent>
                        <Megaphone className="w-12 h-12 mx-auto mb-4 text-slate-300" />
                        <p className="text-slate-500">No hay anuncios disponibles</p>
                    </CardContent>
                </Card>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {announcements.map((announcement) => (
                        <Card key={announcement.id} className="flex flex-col">
                            <CardHeader className="pb-2">
                                <div className="flex justify-between items-start">
                                    <Badge className={getTypeBadgeColor(announcement.type)}>
                                        {coordinationService.getAnnouncementTypeLabel(announcement.type)}
                                    </Badge>
                                    {announcement.expiresAt && (
                                        <Badge variant="outline" className="text-xs">
                                            <Clock className="w-3 h-3 mr-1" />
                                            Expira
                                        </Badge>
                                    )}
                                </div>
                                <CardTitle className="text-lg mt-2">{announcement.title}</CardTitle>
                            </CardHeader>
                            <CardContent className="flex-1">
                                <p className="text-slate-600 text-sm line-clamp-3">{announcement.content}</p>
                            </CardContent>
                            <CardFooter className="pt-0 text-xs text-slate-400 flex items-center gap-2">
                                <User className="w-3 h-3" />
                                <span>{announcement.authorName}</span>
                                <span>•</span>
                                <span>
                                    {formatDistanceToNow(new Date(announcement.createdAt), {
                                        addSuffix: true,
                                        locale: es,
                                    })}
                                </span>
                            </CardFooter>
                        </Card>
                    ))}
                </div>
            )}

            {/* Create Announcement Modal */}
            <Dialog open={showModal} onOpenChange={setShowModal}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Nuevo Anuncio</DialogTitle>
                    </DialogHeader>

                    <div className="space-y-4 py-4">
                        <div className="space-y-2">
                            <Label>Título</Label>
                            <Input
                                value={formData.title}
                                onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                                placeholder="Título del anuncio"
                            />
                        </div>

                        <div className="space-y-2">
                            <Label>Contenido</Label>
                            <Textarea
                                value={formData.content}
                                onChange={(e) => setFormData({ ...formData, content: e.target.value })}
                                placeholder="Escribe el contenido del anuncio..."
                                rows={4}
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label>Audiencia</Label>
                                <Select
                                    value={formData.type}
                                    onValueChange={(val) => setFormData({ ...formData, type: val })}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="general">General</SelectItem>
                                        <SelectItem value="teachers">Solo Maestros</SelectItem>
                                        <SelectItem value="parents">Solo Padres</SelectItem>
                                        <SelectItem value="students">Solo Estudiantes</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <Label>Fecha de Expiración (Opcional)</Label>
                                <Input
                                    type="date"
                                    value={formData.expiresAt}
                                    onChange={(e) => setFormData({ ...formData, expiresAt: e.target.value })}
                                />
                            </div>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowModal(false)}>
                            Cancelar
                        </Button>
                        <Button onClick={handleSubmit} disabled={submitting}>
                            {submitting && <Loader2 className="w-4 h-4 mr-2 animate-spin" />}
                            Publicar Anuncio
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
};
