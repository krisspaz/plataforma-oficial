import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { StatCard } from "@/components/StatCard";
import { DollarSign, FileText, BookOpen, AlertCircle } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import { useAuth } from "@/context/AuthContext";
import { padresService } from "@/services/padres.service";
import { errorHandler } from "@/lib/errorHandler";
import { Loader2 } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import type { ParentAccount, StudentTask } from '@/types/modules.types';

export const PadresDashboard = () => {
    const { user } = useAuth();
    const navigate = useNavigate();
    const [loading, setLoading] = useState(true);
    const [accounts, setAccounts] = useState<ParentAccount[]>([]);
    const [tasks, setTasks] = useState<StudentTask[]>([]);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [accountsData, tasksData] = await Promise.all([
                    padresService.getMyAccounts(),
                    padresService.getChildrenTasks(),
                ]);
                setAccounts(accountsData);
                setTasks(tasksData);
            } catch (error) {
                errorHandler.handleApiError(error, 'Error al cargar datos');
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    const totalDebt = accounts.reduce((sum, acc) => sum + acc.pendingAmount, 0);
    const pendingTasks = tasks.filter(t => t.status === 'PENDIENTE').length;

    if (loading) {
        return (
            <div className="flex min-h-screen items-center justify-center">
                <Loader2 className="h-8 w-8 animate-spin text-primary" />
            </div>
        );
    }

    return (
        <div className="flex min-h-screen bg-background">
            <Sidebar />

            <main className="flex-1 ml-64 p-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold mb-1">Portal de Padres</h1>
                    <p className="text-muted-foreground">Bienvenido, {user?.firstName} {user?.lastName}</p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <StatCard
                        icon={DollarSign}
                        title="Saldo Pendiente"
                        value={`Q${totalDebt.toFixed(2)}`}
                        subtitle={totalDebt > 0 ? "Requiere pago" : "Al día"}
                        variant={totalDebt > 0 ? "warning" : "success"}
                    />
                    <StatCard
                        icon={BookOpen}
                        title="Tareas Pendientes"
                        value={pendingTasks.toString()}
                        subtitle="De sus hijos"
                        variant="primary"
                    />
                    <StatCard
                        icon={FileText}
                        title="Contratos"
                        value={accounts.length.toString()}
                        subtitle="Firmados"
                        variant="accent"
                    />
                    <StatCard
                        icon={AlertCircle}
                        title="Notificaciones"
                        value="3"
                        subtitle="Nuevas"
                        variant="warning"
                    />
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    {accounts.map((account) => (
                        <Card key={account.studentId} className="p-6">
                            <div className="flex justify-between items-start mb-4">
                                <div>
                                    <h3 className="text-lg font-semibold">{account.studentName}</h3>
                                    <p className="text-sm text-muted-foreground">Estado de Cuenta</p>
                                </div>
                                <Button size="sm" variant="outline" onClick={() => navigate(`/padres/cuenta/${account.studentId}`)}>
                                    Ver Detalle
                                </Button>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-muted-foreground">Saldo Pendiente</p>
                                    <p className="text-xl font-bold text-destructive">Q{account.pendingAmount.toFixed(2)}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-muted-foreground">Pagado</p>
                                    <p className="text-xl font-bold text-success">Q{account.paidAmount.toFixed(2)}</p>
                                </div>
                            </div>

                            {account.nextPaymentDate && (
                                <div className="mt-4 p-3 bg-warning/10 rounded">
                                    <p className="text-sm font-medium">Próximo Pago</p>
                                    <p className="text-sm text-muted-foreground">
                                        Q{account.nextPaymentAmount.toFixed(2)} - {new Date(account.nextPaymentDate).toLocaleDateString()}
                                    </p>
                                </div>
                            )}
                        </Card>
                    ))}
                </div>

                <Card className="p-6">
                    <div className="flex justify-between items-center mb-4">
                        <h3 className="text-lg font-semibold">Tareas Recientes</h3>
                        <Button variant="outline" onClick={() => navigate('/padres/tareas')}>Ver Todas</Button>
                    </div>

                    <div className="space-y-3">
                        {tasks.slice(0, 5).map((task) => (
                            <div key={task.id} className="flex justify-between items-center p-3 bg-muted/50 rounded">
                                <div>
                                    <p className="font-medium">{task.title}</p>
                                    <p className="text-sm text-muted-foreground">{task.subjectName} - {task.teacherName}</p>
                                </div>
                                <div className="text-right">
                                    <p className="text-sm font-medium">{task.status}</p>
                                    <p className="text-xs text-muted-foreground">{new Date(task.dueDate).toLocaleDateString()}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </Card>
            </main>
        </div>
    );
};
