import { useEffect, useState } from 'react';
import { Sidebar } from "@/components/Sidebar";
import { StatCard } from "@/components/StatCard";
import { Users, CreditCard, FileText, AlertCircle } from "lucide-react";
import { useAuth } from "@/context/AuthContext";
import { api } from "@/services/api";
import { Loader2 } from 'lucide-react';
import { Button } from "@/components/ui/button";

interface ParentStats {
    childrenCount: number;
    pendingPayments: number;
    totalDue: number;
    activeContracts: number;
}

interface Child {
    id: number;
    firstName: string;
    lastName: string;
    grade: string;
}

export const ParentDashboard = () => {
    const { user } = useAuth();
    const [stats, setStats] = useState<ParentStats | null>(null);
    const [children, setChildren] = useState<Child[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchData = async () => {
            try {
                // Fetch children
                const childrenData = await api.get<{ children: any[], count: number }>('/parents/my-children');

                // Fetch payments summary
                const paymentsData = await api.get<{ summary: any }>('/parents/my-payments');

                // Fetch contracts
                const contractsData = await api.get<{ count: number }>('/parents/my-contracts');

                setChildren(childrenData.children.map((child: any) => ({
                    id: child.id,
                    firstName: child.user.firstName,
                    lastName: child.user.lastName,
                    grade: 'Grado Actual' // Need to fetch enrollment to get grade
                })));

                setStats({
                    childrenCount: childrenData.count,
                    pendingPayments: paymentsData.summary.count, // This should be pending count
                    totalDue: paymentsData.summary.total_pending,
                    activeContracts: contractsData.count
                });
            } catch (error) {
                console.error('Failed to fetch parent data', error);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, []);

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
                    <h1 className="text-3xl font-bold text-foreground mb-1">
                        Panel de Padres
                    </h1>
                    <p className="text-muted-foreground">
                        Bienvenido, {user?.firstName} {user?.lastName}
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <StatCard
                        icon={Users}
                        title="Hijos Inscritos"
                        value={stats?.childrenCount.toString() || '0'}
                        subtitle="Ciclo actual"
                        variant="primary"
                    />
                    <StatCard
                        icon={CreditCard}
                        title="Saldo Pendiente"
                        value={`Q${stats?.totalDue.toFixed(2) || '0.00'}`}
                        subtitle={stats?.totalDue && stats.totalDue > 0 ? "Requiere atención" : "Al día"}
                        variant={stats?.totalDue && stats.totalDue > 0 ? "warning" : "success"}
                    />
                    <StatCard
                        icon={FileText}
                        title="Contratos"
                        value={stats?.activeContracts.toString() || '0'}
                        subtitle="Documentos firmados"
                        variant="accent"
                    />
                    <StatCard
                        icon={AlertCircle}
                        title="Notificaciones"
                        value="3"
                        subtitle="Nuevos avisos"
                        variant="warning"
                    />
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div className="bg-card rounded-lg border p-6">
                        <h3 className="text-lg font-semibold mb-4">Mis Hijos</h3>
                        <div className="space-y-4">
                            {children.map((child) => (
                                <div key={child.id} className="flex items-center justify-between p-4 bg-accent/5 rounded-lg">
                                    <div className="flex items-center gap-4">
                                        <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                            {child.firstName.charAt(0)}
                                        </div>
                                        <div>
                                            <h4 className="font-medium">{child.firstName} {child.lastName}</h4>
                                            <p className="text-sm text-muted-foreground">{child.grade}</p>
                                        </div>
                                    </div>
                                    <Button variant="outline" size="sm">Ver Detalles</Button>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
};
