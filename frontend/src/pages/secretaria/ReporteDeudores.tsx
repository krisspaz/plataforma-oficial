import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { paymentService, DebtorReport, Debtor } from "@/services/payment.service";
import { toast } from 'sonner';
import {
    Loader2,
    Search,
    Download,
    AlertTriangle,
    Users,
    DollarSign,
    Bell,
    ChevronDown,
    ChevronUp,
    Phone,
    Mail
} from 'lucide-react';

export const ReporteDeudores = () => {
    const [report, setReport] = useState<DebtorReport | null>(null);
    const [filteredDebtors, setFilteredDebtors] = useState<Debtor[]>([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [levelFilter, setLevelFilter] = useState<string>('all');
    const [expandedDebtor, setExpandedDebtor] = useState<number | null>(null);
    const [selectedDebtor, setSelectedDebtor] = useState<Debtor | null>(null);

    useEffect(() => {
        fetchDebtors();
    }, [levelFilter]);

    const fetchDebtors = async () => {
        setLoading(true);
        try {
            const data = await paymentService.getDebtors(
                levelFilter !== 'all' ? { level: levelFilter } : undefined
            );
            setReport(data);
            setFilteredDebtors(data.debtors);
        } catch (error) {
            toast.error('Error al cargar reporte de deudores');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (report) {
            const filtered = report.debtors.filter(debtor =>
                debtor.student_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                debtor.grade.toLowerCase().includes(searchTerm.toLowerCase())
            );
            setFilteredDebtors(filtered);
        }
    }, [searchTerm, report]);

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('es-GT', {
            style: 'currency',
            currency: 'GTQ',
        }).format(amount);
    };

    const getLevelBadge = (level: string) => {
        const styles = {
            warning: 'bg-yellow-100 text-yellow-800 border-yellow-300',
            danger: 'bg-orange-100 text-orange-800 border-orange-300',
            critical: 'bg-red-100 text-red-800 border-red-300 animate-pulse',
        };

        const labels = {
            warning: 'Atención',
            danger: 'Urgente',
            critical: 'Crítico',
        };

        return (
            <Badge className={styles[level as keyof typeof styles] || ''}>
                {labels[level as keyof typeof labels] || level}
            </Badge>
        );
    };

    const handleSendNotification = async (debtor: Debtor) => {
        toast.success(`Notificación enviada a ${debtor.student_name}`);
    };

    const handleExportPDF = () => {
        toast.info('Generando reporte PDF...');
        // Implementation would go here
    };

    const toggleDebtor = (studentId: number) => {
        setExpandedDebtor(expandedDebtor === studentId ? null : studentId);
    };

    return (
        <div className="flex min-h-screen bg-slate-50">
            <Sidebar />

            <main className="flex-1 ml-64 p-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-slate-900 mb-2">
                        Reporte de Deudores
                    </h1>
                    <p className="text-slate-600">
                        Gestión de cuentas por cobrar y seguimiento de morosidad
                    </p>
                </div>

                {/* Summary Cards */}
                {report && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <Card className="bg-gradient-to-br from-slate-600 to-slate-700 text-white">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium opacity-90 flex items-center gap-2">
                                    <Users className="w-4 h-4" />
                                    Total Deudores
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-4xl font-bold">
                                    {report.summary.total_debtors}
                                </p>
                            </CardContent>
                        </Card>

                        <Card className="bg-gradient-to-br from-red-500 to-red-600 text-white">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium opacity-90 flex items-center gap-2">
                                    <DollarSign className="w-4 h-4" />
                                    Deuda Total
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-4xl font-bold">
                                    {formatCurrency(report.summary.total_amount)}
                                </p>
                            </CardContent>
                        </Card>

                        <Card className="bg-gradient-to-br from-orange-500 to-orange-600 text-white">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium opacity-90 flex items-center gap-2">
                                    <AlertTriangle className="w-4 h-4" />
                                    Casos Críticos
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-4xl font-bold">
                                    {report.summary.critical_count}
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Filters and Table */}
                <Card>
                    <CardHeader>
                        <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div className="flex gap-4 items-center flex-1">
                                <div className="relative flex-1 max-w-md">
                                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                                    <Input
                                        placeholder="Buscar por nombre o grado..."
                                        className="pl-10"
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>
                                <Select value={levelFilter} onValueChange={setLevelFilter}>
                                    <SelectTrigger className="w-40">
                                        <SelectValue placeholder="Nivel" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Todos</SelectItem>
                                        <SelectItem value="warning">Atención</SelectItem>
                                        <SelectItem value="danger">Urgente</SelectItem>
                                        <SelectItem value="critical">Crítico</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button onClick={handleExportPDF}>
                                <Download className="w-4 h-4 mr-2" />
                                Exportar PDF
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {loading ? (
                            <div className="flex justify-center py-12">
                                <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
                            </div>
                        ) : (
                            <div className="rounded-lg border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="w-8"></TableHead>
                                            <TableHead>Estudiante</TableHead>
                                            <TableHead>Grado</TableHead>
                                            <TableHead>Cuotas Pendientes</TableHead>
                                            <TableHead className="text-right">Deuda Total</TableHead>
                                            <TableHead>Días Atraso</TableHead>
                                            <TableHead>Estado</TableHead>
                                            <TableHead className="text-center">Acciones</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filteredDebtors.length === 0 ? (
                                            <TableRow>
                                                <TableCell colSpan={8} className="text-center py-12 text-slate-500">
                                                    No se encontraron deudores
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            filteredDebtors.map((debtor) => (
                                                <>
                                                    <TableRow
                                                        key={debtor.student_id}
                                                        className={`hover:bg-slate-50 cursor-pointer ${debtor.level === 'critical' ? 'bg-red-50' : ''
                                                            }`}
                                                        onClick={() => toggleDebtor(debtor.student_id)}
                                                    >
                                                        <TableCell>
                                                            {expandedDebtor === debtor.student_id ? (
                                                                <ChevronUp className="w-4 h-4" />
                                                            ) : (
                                                                <ChevronDown className="w-4 h-4" />
                                                            )}
                                                        </TableCell>
                                                        <TableCell className="font-medium">
                                                            {debtor.student_name}
                                                        </TableCell>
                                                        <TableCell>
                                                            {debtor.grade} - {debtor.section}
                                                        </TableCell>
                                                        <TableCell>
                                                            <Badge variant="outline">
                                                                {debtor.installments_count} cuotas
                                                            </Badge>
                                                        </TableCell>
                                                        <TableCell className="text-right font-bold text-red-600">
                                                            {formatCurrency(debtor.total_overdue)}
                                                        </TableCell>
                                                        <TableCell>
                                                            <span className={`font-semibold ${debtor.days_overdue > 30 ? 'text-red-600' :
                                                                    debtor.days_overdue > 15 ? 'text-orange-600' :
                                                                        'text-yellow-600'
                                                                }`}>
                                                                {debtor.days_overdue} días
                                                            </span>
                                                        </TableCell>
                                                        <TableCell>
                                                            {getLevelBadge(debtor.level)}
                                                        </TableCell>
                                                        <TableCell className="text-center">
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={(e) => {
                                                                    e.stopPropagation();
                                                                    handleSendNotification(debtor);
                                                                }}
                                                            >
                                                                <Bell className="w-4 h-4 mr-1" />
                                                                Notificar
                                                            </Button>
                                                        </TableCell>
                                                    </TableRow>

                                                    {/* Expanded Details */}
                                                    {expandedDebtor === debtor.student_id && (
                                                        <TableRow>
                                                            <TableCell colSpan={8} className="bg-slate-50 p-4">
                                                                <div className="space-y-4">
                                                                    <h4 className="font-semibold text-slate-700">
                                                                        Detalle de Cuotas Pendientes
                                                                    </h4>
                                                                    <div className="grid grid-cols-4 gap-4">
                                                                        {debtor.installments.map((inst, idx) => (
                                                                            <div
                                                                                key={idx}
                                                                                className={`p-3 rounded-lg border ${inst.level === 'critical' ? 'bg-red-50 border-red-200' :
                                                                                        inst.level === 'danger' ? 'bg-orange-50 border-orange-200' :
                                                                                            'bg-yellow-50 border-yellow-200'
                                                                                    }`}
                                                                            >
                                                                                <div className="flex justify-between items-center mb-2">
                                                                                    <span className="font-medium">Cuota {inst.number}</span>
                                                                                    <span className="font-bold">
                                                                                        {formatCurrency(inst.amount)}
                                                                                    </span>
                                                                                </div>
                                                                                <div className="text-sm text-slate-600">
                                                                                    Vencimiento: {inst.due_date}
                                                                                </div>
                                                                                <div className="text-sm font-medium text-red-600">
                                                                                    {inst.days_overdue} días de atraso
                                                                                </div>
                                                                            </div>
                                                                        ))}
                                                                    </div>
                                                                    <div className="flex gap-2 pt-2">
                                                                        <Button size="sm" variant="outline">
                                                                            <Phone className="w-4 h-4 mr-1" />
                                                                            Llamar
                                                                        </Button>
                                                                        <Button size="sm" variant="outline">
                                                                            <Mail className="w-4 h-4 mr-1" />
                                                                            Enviar Email
                                                                        </Button>
                                                                    </div>
                                                                </div>
                                                            </TableCell>
                                                        </TableRow>
                                                    )}
                                                </>
                                            ))
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </main>
        </div>
    );
};

export default ReporteDeudores;
