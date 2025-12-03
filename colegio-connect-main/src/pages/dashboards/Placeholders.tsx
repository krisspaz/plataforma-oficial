import { Sidebar } from "@/components/Sidebar";

export const TeacherDashboard = () => (
    <div className="flex min-h-screen bg-background">
        <Sidebar />
        <main className="flex-1 ml-64 p-8">
            <h1 className="text-3xl font-bold">Panel de Maestros</h1>
            <p>Próximamente...</p>
        </main>
    </div>
);

export const ParentDashboard = () => (
    <div className="flex min-h-screen bg-background">
        <Sidebar />
        <main className="flex-1 ml-64 p-8">
            <h1 className="text-3xl font-bold">Panel de Padres</h1>
            <p>Próximamente...</p>
        </main>
    </div>
);
