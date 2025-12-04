# Frontend - Plataforma Escolar

Sistema de gestión escolar moderno y completo con interfaces específicas para cada rol de usuario.

## 📋 Tabla de Contenidos

- [Características](#características)
- [Tecnologías](#tecnologías)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Módulos por Rol](#módulos-por-rol)
- [Seguridad](#seguridad)
- [API](#api)
- [Testing](#testing)
- [Despliegue](#despliegue)

## ✨ Características

### Generales
- ✅ TypeScript strict mode
- ✅ Autenticación JWT
- ✅ Routing protegido por roles
- ✅ Error boundaries
- ✅ Loading states
- ✅ Toast notifications
- ✅ Responsive design
- ✅ Dark mode ready

### Seguridad
- ✅ CSRF protection
- ✅ XSS prevention
- ✅ Input sanitization
- ✅ Rate limiting
- ✅ Secure token storage
- ✅ Password strength validation

## 🛠️ Tecnologías

- **React 18** - Framework UI
- **TypeScript** - Type safety
- **Vite** - Build tool
- **Tailwind CSS** - Styling
- **Shadcn UI** - Component library
- **React Router** - Routing
- **React Query** - Data fetching
- **React Hook Form** - Form handling
- **Zod** - Validation
- **date-fns** - Date utilities

## 📁 Estructura del Proyecto

```
frontend/
├── src/
│   ├── components/          # Componentes reutilizables
│   │   ├── ui/             # Componentes Shadcn UI
│   │   ├── Sidebar.tsx     # Navegación lateral
│   │   ├── StatCard.tsx    # Tarjetas de estadísticas
│   │   └── ErrorBoundary.tsx
│   │
│   ├── pages/              # Páginas principales
│   │   ├── dashboards/     # Dashboards por rol
│   │   ├── secretaria/     # Módulo Secretaría
│   │   ├── coordinacion/   # Módulo Coordinación
│   │   ├── maestros/       # Módulo Maestros
│   │   ├── padres/         # Módulo Padres
│   │   └── administracion/ # Módulo Administración
│   │
│   ├── services/           # Servicios API
│   │   ├── api.ts          # Cliente API base
│   │   ├── auth.service.ts
│   │   ├── secretaria.service.ts
│   │   ├── coordinacion.service.ts
│   │   ├── maestros.service.ts
│   │   ├── padres.service.ts
│   │   └── administracion.service.ts
│   │
│   ├── types/              # TypeScript types
│   │   ├── auth.types.ts
│   │   └── modules.types.ts
│   │
│   ├── lib/                # Utilidades
│   │   ├── errorHandler.ts
│   │   ├── security.ts
│   │   ├── sanitize.ts
│   │   └── utils.ts
│   │
│   ├── context/            # React Context
│   │   └── AuthContext.tsx
│   │
│   └── App.tsx             # Componente principal
│
├── public/                 # Archivos estáticos
├── .env.example           # Variables de entorno ejemplo
└── package.json
```

## 🚀 Instalación

```bash
# Clonar repositorio
git clone <repository-url>
cd frontend

# Instalar dependencias
npm install

# Copiar variables de entorno
cp .env.example .env

# Iniciar servidor de desarrollo
npm run dev
```

## ⚙️ Configuración

### Variables de Entorno

```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=Plataforma Escolar
VITE_ENABLE_ANALYTICS=false
```

### TypeScript

El proyecto usa TypeScript en modo estricto:
- `strict: true`
- `noImplicitAny: true`
- `strictNullChecks: true`

## 👥 Módulos por Rol

### 🏢 Secretaría
**Funcionalidades:**
- Registro de pagos (contado/crédito)
- Generación de contratos PDF
- Inscripción de estudiantes
- Reporte de deudores
- Corte del día

**Rutas:**
- `/secretaria` - Dashboard
- `/secretaria/pagos/nuevo` - Nuevo pago
- `/secretaria/pagos/deudores` - Reporte deudores
- `/secretaria/inscripciones/nueva` - Nueva inscripción
- `/secretaria/contratos/generar` - Generar contrato

### 📚 Coordinación
**Funcionalidades:**
- Gestión de anuncios
- Base de datos de profesores
- Asignación de materias
- Gestión de notas y boletas
- Cierre de bimestre

**Rutas:**
- `/coordinacion` - Dashboard
- `/coordinacion/anuncios/nuevo` - Nuevo anuncio
- `/coordinacion/profesores` - Gestión profesores
- `/coordinacion/notas` - Gestión notas

### 👨‍🏫 Maestros
**Funcionalidades:**
- Crear actividades (tareas/exámenes)
- Cargar notas
- Subir materiales
- Ver calendario
- Notas finales

**Rutas:**
- `/maestros` - Dashboard
- `/maestros/actividades/nueva` - Nueva actividad
- `/maestros/notas/cargar` - Cargar notas
- `/maestros/materiales` - Materiales

### 👨‍👩‍👧‍👦 Padres
**Funcionalidades:**
- Ver saldo y pagos
- Tareas de hijos
- Descargar contratos
- Historial de pagos

**Rutas:**
- `/padres` - Dashboard
- `/padres/cuenta` - Mi cuenta
- `/padres/tareas` - Tareas hijos

### 💼 Administración
**Funcionalidades:**
- Resumen financiero
- Estadísticas estudiantes
- Reportes
- Corte del día

**Rutas:**
- `/administracion` - Dashboard
- `/administracion/finanzas` - Finanzas
- `/administracion/estadisticas` - Estadísticas

## 🔒 Seguridad

### Implementaciones de Seguridad

#### 1. Sanitización de Inputs
```typescript
import { sanitizeHtml, sanitizeInput } from '@/lib/sanitize';

const cleanInput = sanitizeInput(userInput);
```

#### 2. CSRF Protection
```typescript
import { generateCSRFToken, storeCSRFToken } from '@/lib/security';

const token = generateCSRFToken();
storeCSRFToken(token);
```

#### 3. Rate Limiting
```typescript
import { rateLimiter } from '@/lib/security';

if (!rateLimiter.isAllowed(endpoint)) {
  throw new Error('Too many requests');
}
```

#### 4. Secure Storage
```typescript
import { secureStorage } from '@/lib/security';

secureStorage.setItem('key', 'value');
const value = secureStorage.getItem('key');
```

### Best Practices

- ✅ Todos los inputs son sanitizados
- ✅ Tokens JWT validados y verificados
- ✅ CSRF tokens en requests mutables
- ✅ Rate limiting en API calls
- ✅ Validación de contraseñas
- ✅ Prevención de clickjacking
- ✅ Content Security Policy

## 📡 API

### Cliente API

El cliente API (`services/api.ts`) incluye:
- Manejo automático de tokens
- CSRF protection
- Rate limiting
- Error handling
- Logging

### Ejemplo de Uso

```typescript
import { api } from '@/services/api';

// GET request
const data = await api.get<User[]>('/users');

// POST request
const newUser = await api.post<User>('/users', {
  name: 'John Doe',
  email: 'john@example.com'
});
```

### Servicios por Módulo

Cada módulo tiene su propio servicio:
- `secretaria.service.ts`
- `coordinacion.service.ts`
- `maestros.service.ts`
- `padres.service.ts`
- `administracion.service.ts`

## 🧪 Testing

```bash
# Ejecutar tests
npm run test

# Tests con coverage
npm run test:coverage

# Tests E2E
npm run test:e2e
```

## 📦 Despliegue

### Build de Producción

```bash
# Crear build
npm run build

# Preview del build
npm run preview
```

### Variables de Entorno Producción

```env
VITE_API_URL=https://api.production.com
VITE_APP_NAME=Plataforma Escolar
VITE_ENABLE_ANALYTICS=true
```

### Optimizaciones

- Code splitting automático
- Lazy loading de rutas
- Tree shaking
- Minificación
- Compresión gzip

## 📝 Scripts Disponibles

```bash
npm run dev          # Servidor desarrollo
npm run build        # Build producción
npm run preview      # Preview build
npm run lint         # Linter
npm run type-check   # Verificar tipos
```

## 🤝 Contribución

1. Fork el proyecto
2. Crea tu rama (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto es privado y confidencial.

## 👨‍💻 Soporte

Para soporte, contacta al equipo de desarrollo.
