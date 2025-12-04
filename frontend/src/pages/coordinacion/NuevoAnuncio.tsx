import { useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Card } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useNavigate } from 'react-router-dom';
import { coordinacionService } from "@/services/coordinacion.service";
import { errorHandler } from "@/lib/errorHandler";
import { toast } from 'sonner';
import { Loader2, ArrowLeft } from 'lucide-react';
import { useAuth } from "@/context/AuthContext";

export const NuevoAnuncio = () => {
    const navigate = useNavigate();
    const { user } = useAuth();
    const [loading, setLoading] = useState(false);
    const [formData, setFormData] = useState({
        title: '',
        content: '',
        targetAudience: 'ALL' as 'ALL' | 'TEACHERS' | 'PARENTS' | 'STUDENTS',
        expiryDate: '',
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);

        try {
            await coordinacionService.createAnnouncement({
                title: formData.title,
                content: formData.content,
                targetAudience: formData.targetAudience,
                publishDate: new Date().toISOString(),
                expiryDate: formData.expiryDate || undefined,
                authorId: user?.id || 0,
            });

            toast.success('Anuncio publicado exitosamente');
            navigate('/coordinacion');
        } catch (error) {
            errorHandler.handleApiError(error, 'Error al publicar el anuncio');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex min-h-screen bg-background">
            <Sidebar />

            <main className="flex-1 ml-64 p-8">
                <div className="max-w-2xl mx-auto">
                    <Button variant="ghost" onClick={() => navigate('/coordinacion')} className="mb-4">
                        <ArrowLeft className="w-4 h-4 mr-2" />
                        Volver
                    </Button>

                    <Card className="p-6">
                        <h1 className="text-2xl font-bold mb-6">Nuevo Anuncio</h1>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="title">Título del Anuncio</Label>
                                <Input
                                    id="title"
                                    value={formData.title}
                                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                                    placeholder="Ej: Reunión de Profesores"
                                    required
                                />
                            </div>

                            <div>
                                <Label htmlFor="content">Contenido</Label>
                                <Textarea
                                    id="content"
                                    value={formData.content}
                                    onChange={(e) => setFormData({ ...formData, content: e.target.value })}
                                    placeholder="Escribe el contenido del anuncio..."
                                    rows={6}
                                    required
                                />
                            </div>

                            <div>
                                <Label htmlFor="targetAudience">Dirigido a</Label>
                                <Select value={formData.targetAudience} onValueChange={(value: 'ALL' | 'TEACHERS' | 'PARENTS' | 'STUDENTS') => setFormData({ ...formData, targetAudience: value })}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="ALL">Todos</SelectItem>
                                        <SelectItem value="TEACHERS">Profesores</SelectItem>
                                        <SelectItem value="PARENTS">Padres de Familia</SelectItem>
                                        <SelectItem value="STUDENTS">Estudiantes</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label htmlFor="expiryDate">Fecha de Expiración (Opcional)</Label>
                                <Input
                                    id="expiryDate"
                                    type="date"
                                    value={formData.expiryDate}
                                    onChange={(e) => setFormData({ ...formData, expiryDate: e.target.value })}
                                />
                                <p className="text-sm text-muted-foreground mt-1">
                                    El anuncio se ocultará automáticamente después de esta fecha
                                </p>
                            </div>

                            <div className="flex gap-2 pt-4">
                                <Button type="submit" disabled={loading} className="flex-1">
                                    {loading ? (
                                        <>
                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                            Publicando...
                                        </>
                                    ) : (
                                        'Publicar Anuncio'
                                    )}
                                </Button>
                                <Button type="button" variant="outline" onClick={() => navigate('/coordinacion')}>
                                    Cancelar
                                </Button>
                            </div>
                        </form>
                    </Card>
                </div>
            </main>
        </div>
    );
};
