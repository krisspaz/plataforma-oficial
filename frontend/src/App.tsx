import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Routes, Route } from "react-router-dom";
import { AuthProvider } from "@/context/AuthContext";
import { PrivateRoute } from "@/components/PrivateRoute";
import Login from "@/pages/Login";
import Index from "./pages/Index";
import Cursos from "./pages/Cursos";
import Calendario from "./pages/Calendario";
import Tareas from "./pages/Tareas";
import Comunidad from "./pages/Comunidad";
import Configuracion from "./pages/Configuracion";
import NotFound from "./pages/NotFound";
import { ErrorBoundary } from "@/components/ErrorBoundary";

// Secretaría
import { NuevoPago } from "./pages/secretaria/NuevoPago";
import { ReporteDeudores } from "./pages/secretaria/ReporteDeudores";
import { NuevaInscripcion } from "./pages/secretaria/NuevaInscripcion";
import { GenerarContrato } from "./pages/secretaria/GenerarContrato";

// Coordinación
import { NuevoAnuncio } from "./pages/coordinacion/NuevoAnuncio";

// Maestros
import { NuevaActividad } from "./pages/maestros/NuevaActividad";

const queryClient = new QueryClient();

const App = () => (
  <ErrorBoundary>
    <QueryClientProvider client={queryClient}>
      <TooltipProvider>
        <AuthProvider>
          <Toaster />
          <Sonner />
          <BrowserRouter>
            <Routes>
              <Route path="/login" element={<Login />} />

              <Route element={<PrivateRoute />}>
                <Route path="/" element={<Index />} />
                <Route path="/dashboard" element={<Index />} />
                <Route path="/cursos" element={<Cursos />} />
                <Route path="/calendario" element={<Calendario />} />
                <Route path="/tareas" element={<Tareas />} />
                <Route path="/comunidad" element={<Comunidad />} />
                <Route path="/configuracion" element={<Configuracion />} />

                {/* Secretaría Routes */}
                <Route path="/secretaria" element={<Index />} />
                <Route path="/secretaria/pagos/nuevo" element={<NuevoPago />} />
                <Route path="/secretaria/pagos/deudores" element={<ReporteDeudores />} />
                <Route path="/secretaria/inscripciones/nueva" element={<NuevaInscripcion />} />
                <Route path="/secretaria/contratos/generar" element={<GenerarContrato />} />

                {/* Coordinación Routes */}
                <Route path="/coordinacion" element={<Index />} />
                <Route path="/coordinacion/anuncios/nuevo" element={<NuevoAnuncio />} />

                {/* Maestros Routes */}
                <Route path="/maestros" element={<Index />} />
                <Route path="/maestros/actividades/nueva" element={<NuevaActividad />} />

                {/* Padres Routes */}
                <Route path="/padres" element={<Index />} />

                {/* Administración Routes */}
                <Route path="/administracion" element={<Index />} />
              </Route>

              <Route path="*" element={<NotFound />} />
            </Routes>
          </BrowserRouter>
        </AuthProvider>
      </TooltipProvider>
    </QueryClientProvider>
  </ErrorBoundary>
);

export default App;
