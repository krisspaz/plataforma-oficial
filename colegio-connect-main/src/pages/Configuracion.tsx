import { Sidebar } from "@/components/Sidebar";
import { Card } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Switch } from "@/components/ui/switch";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";

const Configuracion = () => {
  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      
      <main className="flex-1 ml-64 p-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-foreground mb-2">Configuración</h1>
          <p className="text-muted-foreground">Personaliza tu experiencia educativa</p>
        </div>

        <Tabs defaultValue="perfil" className="w-full">
          <TabsList className="mb-6">
            <TabsTrigger value="perfil">Perfil</TabsTrigger>
            <TabsTrigger value="notificaciones">Notificaciones</TabsTrigger>
            <TabsTrigger value="privacidad">Privacidad</TabsTrigger>
          </TabsList>

          <TabsContent value="perfil">
            <div className="max-w-2xl space-y-6">
              <Card className="p-6">
                <h3 className="text-lg font-semibold text-foreground mb-4">Información Personal</h3>
                
                <div className="space-y-4">
                  <div className="flex items-center gap-6 mb-6">
                    <div className="w-20 h-20 rounded-full bg-gradient-to-br from-primary to-accent flex items-center justify-center text-2xl font-bold text-primary-foreground">
                      JD
                    </div>
                    <Button variant="outline">Cambiar foto</Button>
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <Label htmlFor="nombre">Nombre</Label>
                      <Input id="nombre" defaultValue="Juan" />
                    </div>
                    <div>
                      <Label htmlFor="apellido">Apellido</Label>
                      <Input id="apellido" defaultValue="Díaz" />
                    </div>
                  </div>

                  <div>
                    <Label htmlFor="email">Correo Electrónico</Label>
                    <Input id="email" type="email" defaultValue="juan.diaz@estudiante.edu" />
                  </div>

                  <div>
                    <Label htmlFor="telefono">Teléfono</Label>
                    <Input id="telefono" defaultValue="+34 600 123 456" />
                  </div>

                  <Button className="w-full">Guardar Cambios</Button>
                </div>
              </Card>

              <Card className="p-6">
                <h3 className="text-lg font-semibold text-foreground mb-4">Cambiar Contraseña</h3>
                
                <div className="space-y-4">
                  <div>
                    <Label htmlFor="password-actual">Contraseña Actual</Label>
                    <Input id="password-actual" type="password" />
                  </div>

                  <div>
                    <Label htmlFor="password-nueva">Nueva Contraseña</Label>
                    <Input id="password-nueva" type="password" />
                  </div>

                  <div>
                    <Label htmlFor="password-confirmar">Confirmar Contraseña</Label>
                    <Input id="password-confirmar" type="password" />
                  </div>

                  <Button className="w-full">Actualizar Contraseña</Button>
                </div>
              </Card>
            </div>
          </TabsContent>

          <TabsContent value="notificaciones">
            <div className="max-w-2xl">
              <Card className="p-6">
                <h3 className="text-lg font-semibold text-foreground mb-4">Preferencias de Notificaciones</h3>
                
                <div className="space-y-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium text-foreground">Notificaciones de Tareas</p>
                      <p className="text-sm text-muted-foreground">Recibe alertas sobre nuevas tareas y fechas límite</p>
                    </div>
                    <Switch defaultChecked />
                  </div>

                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium text-foreground">Recordatorios de Clases</p>
                      <p className="text-sm text-muted-foreground">Avisos antes de que comience una clase</p>
                    </div>
                    <Switch defaultChecked />
                  </div>

                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium text-foreground">Mensajes de Comunidad</p>
                      <p className="text-sm text-muted-foreground">Notificaciones de nuevas publicaciones y comentarios</p>
                    </div>
                    <Switch />
                  </div>

                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium text-foreground">Actualizaciones de Calificaciones</p>
                      <p className="text-sm text-muted-foreground">Alertas cuando se publiquen nuevas calificaciones</p>
                    </div>
                    <Switch defaultChecked />
                  </div>

                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium text-foreground">Newsletter Educativo</p>
                      <p className="text-sm text-muted-foreground">Recursos y consejos de estudio semanales</p>
                    </div>
                    <Switch />
                  </div>

                  <Button className="w-full">Guardar Preferencias</Button>
                </div>
              </Card>
            </div>
          </TabsContent>

          <TabsContent value="privacidad">
            <div className="max-w-2xl">
              <Card className="p-6">
                <h3 className="text-lg font-semibold text-foreground mb-4">Configuración de Privacidad</h3>
                
                <div className="space-y-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium text-foreground">Perfil Público</p>
                      <p className="text-sm text-muted-foreground">Permite que otros estudiantes vean tu perfil</p>
                    </div>
                    <Switch defaultChecked />
                  </div>

                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium text-foreground">Mostrar Progreso Académico</p>
                      <p className="text-sm text-muted-foreground">Muestra tus logros y calificaciones en tu perfil</p>
                    </div>
                    <Switch />
                  </div>

                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium text-foreground">Permitir Mensajes Directos</p>
                      <p className="text-sm text-muted-foreground">Otros usuarios pueden enviarte mensajes privados</p>
                    </div>
                    <Switch defaultChecked />
                  </div>

                  <div className="flex items-center justify-between">
                    <div>
                      <p className="font-medium text-foreground">Compartir Horario de Clases</p>
                      <p className="text-sm text-muted-foreground">Permite a compañeros ver tu horario</p>
                    </div>
                    <Switch />
                  </div>

                  <Button className="w-full">Guardar Configuración</Button>
                </div>
              </Card>
            </div>
          </TabsContent>
        </Tabs>
      </main>
    </div>
  );
};

export default Configuracion;
