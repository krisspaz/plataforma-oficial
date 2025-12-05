import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { DollarSign, TrendingUp, TrendingDown, AlertCircle } from "lucide-react";

export default function FinanzasPage() {
    return (
        <div className="p-8">
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-slate-900">Finanzas</h1>
                <p className="text-slate-600 mt-2">Gestión financiera y estados de cuenta de la institución</p>
            </div>

            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Ingresos del Mes</CardTitle>
                        <DollarSign className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">Q125,430.00</div>
                        <p className="text-xs text-muted-foreground flex items-center gap-1">
                            <TrendingUp className="h-3 w-3 text-green-600" />
                            <span className="text-green-600">+12.5%</span> vs mes anterior
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Pendiente de Cobro</CardTitle>
                        <AlertCircle className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">Q45,230.00</div>
                        <p className="text-xs text-muted-foreground">
                            18 estudiantes con saldo pendiente
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Gastos del Mes</CardTitle>
                        <TrendingDown className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">Q78,920.00</div>
                        <p className="text-xs text-muted-foreground flex items-center gap-1">
                            <TrendingDown className="h-3 w-3 text-red-600" />
                            <span className="text-red-600">+5.2%</span> vs mes anterior
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Balance Neto</CardTitle>
                        <DollarSign className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold text-green-600">Q46,510.00</div>
                        <p className="text-xs text-muted-foreground">
                            Utilidad del mes actual
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Resumen Financiero</CardTitle>
                    <CardDescription>
                        Esta página mostrará gráficos e informes financieros detallados
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <p className="text-slate-600">
                        En desarrollo: Gráficos de ingresos/egresos, reportes de morosidad, proyecciones financieras.
                    </p>
                </CardContent>
            </Card>
        </div>
    );
}
