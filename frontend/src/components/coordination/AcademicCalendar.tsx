import React, { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Calendar, Plus, Loader2, ChevronLeft, ChevronRight } from 'lucide-react';
import { coordinationService, CalendarEvent } from '@/services/coordination.service';
import { toast } from 'sonner';
import {
    format,
    startOfMonth,
    endOfMonth,
    eachDayOfInterval,
    isSameMonth,
    isSameDay,
    addMonths,
    subMonths,
    isToday,
    parseISO
} from 'date-fns';
import { es } from 'date-fns/locale';

export const AcademicCalendar: React.FC = () => {
    const [currentMonth, setCurrentMonth] = useState(new Date());
    const [events, setEvents] = useState<CalendarEvent[]>([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [selectedDate, setSelectedDate] = useState<Date | null>(null);

    const [formData, setFormData] = useState({
        title: '',
        description: '',
        startDate: '',
        endDate: '',
        type: 'activity',
        isAllDay: true,
    });

    useEffect(() => {
        loadEvents();
    }, [currentMonth]);

    const loadEvents = async () => {
        setLoading(true);
        try {
            const start = startOfMonth(currentMonth);
            const end = endOfMonth(currentMonth);

            const data = await coordinationService.getCalendarEvents(
                format(start, 'yyyy-MM-dd'),
                format(end, 'yyyy-MM-dd')
            );
            setEvents(data);
        } catch (error) {
            toast.error('Error al cargar eventos');
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async () => {
        if (!formData.title || !formData.startDate || !formData.endDate) {
            toast.error('Complete los campos requeridos');
            return;
        }

        setSubmitting(true);
        try {
            await coordinationService.createCalendarEvent({
                title: formData.title,
                description: formData.description || undefined,
                start_date: formData.startDate,
                end_date: formData.endDate,
                type: formData.type,
                is_all_day: formData.isAllDay,
            });

            toast.success('Evento creado exitosamente');
            setShowModal(false);
            setFormData({
                title: '',
                description: '',
                startDate: '',
                endDate: '',
                type: 'activity',
                isAllDay: true,
            });
            loadEvents();
        } catch (error) {
            toast.error('Error al crear evento');
        } finally {
            setSubmitting(false);
        }
    };

    const days = eachDayOfInterval({
        start: startOfMonth(currentMonth),
        end: endOfMonth(currentMonth),
    });

    const getEventsForDay = (day: Date): CalendarEvent[] => {
        return events.filter((event) => {
            const eventStart = parseISO(event.startDate);
            return isSameDay(eventStart, day);
        });
    };

    const handleDateClick = (date: Date) => {
        setSelectedDate(date);
        setFormData({
            ...formData,
            startDate: format(date, 'yyyy-MM-dd'),
            endDate: format(date, 'yyyy-MM-dd'),
        });
        setShowModal(true);
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold flex items-center gap-2">
                        <Calendar className="w-6 h-6" />
                        Calendario Académico
                    </h2>
                    <p className="text-slate-500">Gestiona eventos, feriados y actividades del año escolar</p>
                </div>
                <Button onClick={() => setShowModal(true)}>
                    <Plus className="w-4 h-4 mr-2" />
                    Nuevo Evento
                </Button>
            </div>

            {/* Calendar Navigation */}
            <Card>
                <CardHeader className="pb-2">
                    <div className="flex justify-between items-center">
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setCurrentMonth(subMonths(currentMonth, 1))}
                        >
                            <ChevronLeft className="w-4 h-4" />
                        </Button>
                        <CardTitle className="text-lg capitalize">
                            {format(currentMonth, 'MMMM yyyy', { locale: es })}
                        </CardTitle>
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setCurrentMonth(addMonths(currentMonth, 1))}
                        >
                            <ChevronRight className="w-4 h-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    {loading ? (
                        <div className="flex justify-center items-center h-64">
                            <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
                        </div>
                    ) : (
                        <>
                            {/* Day Headers */}
                            <div className="grid grid-cols-7 gap-1 mb-2">
                                {['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'].map((day) => (
                                    <div
                                        key={day}
                                        className="text-center text-sm font-medium text-slate-500 py-2"
                                    >
                                        {day}
                                    </div>
                                ))}
                            </div>

                            {/* Calendar Grid */}
                            <div className="grid grid-cols-7 gap-1">
                                {/* Empty cells for days before month starts */}
                                {Array.from({ length: startOfMonth(currentMonth).getDay() }).map((_, i) => (
                                    <div key={`empty-start-${i}`} className="h-24 bg-slate-50 rounded" />
                                ))}

                                {days.map((day) => {
                                    const dayEvents = getEventsForDay(day);
                                    return (
                                        <div
                                            key={day.toISOString()}
                                            onClick={() => handleDateClick(day)}
                                            className={`
                        h-24 p-1 border rounded cursor-pointer transition-colors
                        hover:bg-slate-50
                        ${!isSameMonth(day, currentMonth) ? 'bg-slate-100 text-slate-400' : ''}
                        ${isToday(day) ? 'border-blue-500 border-2' : 'border-slate-200'}
                      `}
                                        >
                                            <div className={`text-sm font-medium mb-1 ${isToday(day) ? 'text-blue-600' : ''}`}>
                                                {format(day, 'd')}
                                            </div>
                                            <div className="space-y-1 overflow-hidden">
                                                {dayEvents.slice(0, 2).map((event) => (
                                                    <div
                                                        key={event.id}
                                                        className={`text-xs px-1 py-0.5 rounded truncate text-white ${coordinationService.getEventTypeColor(event.type)}`}
                                                    >
                                                        {event.title}
                                                    </div>
                                                ))}
                                                {dayEvents.length > 2 && (
                                                    <div className="text-xs text-slate-400">
                                                        +{dayEvents.length - 2} más
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </>
                    )}
                </CardContent>
            </Card>

            {/* Event Legend */}
            <div className="flex gap-4 flex-wrap">
                {['holiday', 'exam', 'activity', 'meeting'].map((type) => (
                    <div key={type} className="flex items-center gap-2">
                        <div className={`w-3 h-3 rounded ${coordinationService.getEventTypeColor(type)}`} />
                        <span className="text-sm text-slate-600">
                            {coordinationService.getEventTypeLabel(type)}
                        </span>
                    </div>
                ))}
            </div>

            {/* Create Event Modal */}
            <Dialog open={showModal} onOpenChange={setShowModal}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Nuevo Evento</DialogTitle>
                    </DialogHeader>

                    <div className="space-y-4 py-4">
                        <div className="space-y-2">
                            <Label>Título</Label>
                            <Input
                                value={formData.title}
                                onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                                placeholder="Nombre del evento"
                            />
                        </div>

                        <div className="space-y-2">
                            <Label>Descripción (Opcional)</Label>
                            <Textarea
                                value={formData.description}
                                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                placeholder="Detalles del evento..."
                                rows={2}
                            />
                        </div>

                        <div className="space-y-2">
                            <Label>Tipo de Evento</Label>
                            <Select
                                value={formData.type}
                                onValueChange={(val) => setFormData({ ...formData, type: val })}
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="holiday">Feriado</SelectItem>
                                    <SelectItem value="exam">Examen</SelectItem>
                                    <SelectItem value="activity">Actividad</SelectItem>
                                    <SelectItem value="meeting">Reunión</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label>Fecha Inicio</Label>
                                <Input
                                    type="date"
                                    value={formData.startDate}
                                    onChange={(e) => setFormData({ ...formData, startDate: e.target.value })}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Fecha Fin</Label>
                                <Input
                                    type="date"
                                    value={formData.endDate}
                                    onChange={(e) => setFormData({ ...formData, endDate: e.target.value })}
                                />
                            </div>
                        </div>

                        <div className="flex items-center justify-between">
                            <Label>Todo el día</Label>
                            <Switch
                                checked={formData.isAllDay}
                                onCheckedChange={(checked) => setFormData({ ...formData, isAllDay: checked })}
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" onClick={() => setShowModal(false)}>
                            Cancelar
                        </Button>
                        <Button onClick={handleSubmit} disabled={submitting}>
                            {submitting && <Loader2 className="w-4 h-4 mr-2 animate-spin" />}
                            Crear Evento
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
};
