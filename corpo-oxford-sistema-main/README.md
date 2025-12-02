# Sistema de Gestión Escolar Oxford - Plataforma Oficial

## 🎯 Descripción

Sistema escolar moderno y escalable desarrollado con **Symfony 7.2** (backend), **React 18** (frontend web), y **React Native** (apps móviles). Incluye funcionalidades avanzadas como IA para generación de horarios, predicción de riesgo académico, chat en tiempo real, firma digital de contratos y múltiples pasarelas de pago.

## 🏗️ Arquitectura

```
plataforma-oficial/
├── corpo-oxford-sistema-main/          # Proyecto principal
│   ├── backend-symfony/                # Backend Symfony 7.2 (API REST)
│   ├── app/                            # Laravel (legacy - opcional)
│   ├── resources/                      # Recursos Laravel
│   ├── docker-compose.dev.yml          # Docker para desarrollo
│   └── ...
├── colegio-connect-main/               # Frontend React
│   ├── src/
│   │   ├── components/                 # Componentes UI
│   │   ├── pages/                      # Páginas
│   │   └── services/                   # Servicios API
│   └── ...
└── mobile/ (próximamente)              # Apps móviles React Native
```

## 🚀 Tecnologías

### Backend
- **Symfony 7.2** - Framework PHP moderno
- **API Platform** - API REST automática
- **Doctrine ORM** - Gestión de base de datos
- **Lexik JWT** - Autenticación JWT
- **PostgreSQL 16** - Base de datos principal
- **Redis** - Cache y sesiones
- **Mercure** - WebSockets para chat en tiempo real

### Frontend Web
- **React 18** - Biblioteca UI
- **Vite** - Build tool ultrarrápido
- **shadcn/ui** - Componentes UI premium
- **TanStack Query** - Gestión de estado servidor
- **React Router** - Navegación
- **Tailwind CSS** - Estilos utility-first

### Apps Móviles
- **React Native** - Framework multiplataforma
- **Expo** - Toolchain y SDK
- **React Navigation** - Navegación nativa

### DevOps
- **Docker** - Contenedorización
- **GitHub Actions** - CI/CD
- **PostgreSQL** - Base de datos
- **DataGrip** - Cliente de base de datos

## 📦 Instalación

### Prerrequisitos

- PHP 8.3+
- Composer 2.x
- Node.js 20+
- Docker & Docker Compose
- PostgreSQL 16 (o usar Docker)

### 1. Clonar el Repositorio

```bash
git clone <repository-url>
cd plataforma-oficial/corpo-oxford-sistema-main
```

### 2. Configurar Backend Symfony

```bash
cd backend-symfony

# Instalar dependencias
composer install

# Copiar archivo de entorno
cp .env.example .env.local

# Generar claves JWT
php bin/console lexik:jwt:generate-keypair

# Crear base de datos
php bin/console doctrine:database:create

# Ejecutar migraciones
php bin/console doctrine:migrations:migrate

# Cargar datos de prueba (opcional)
php bin/console doctrine:fixtures:load
```

### 3. Configurar Frontend React

```bash
cd ../../colegio-connect-main

# Instalar dependencias
npm install

# Copiar archivo de entorno
cp .env.example .env.local

# Configurar URL de API en .env.local
# VITE_API_URL=http://localhost:8000/api
```

### 4. Iniciar con Docker (Recomendado)

```bash
cd corpo-oxford-sistema-main

# Iniciar todos los servicios
docker-compose -f docker-compose.dev.yml up -d

# Ver logs
docker-compose -f docker-compose.dev.yml logs -f
```

**Servicios disponibles:**
- Backend Symfony: http://localhost:8000
- Frontend React: http://localhost:5173
- PostgreSQL: localhost:5432
- Redis: localhost:6379
- Mercure: http://localhost:3000
- pgAdmin: http://localhost:5050

### 5. Iniciar Manualmente (Sin Docker)

**Terminal 1 - Backend:**
```bash
cd backend-symfony
symfony server:start
# o
php -S localhost:8000 -t public
```

**Terminal 2 - Frontend:**
```bash
cd colegio-connect-main
npm run dev
```

**Terminal 3 - PostgreSQL:**
```bash
# Asegúrate de tener PostgreSQL corriendo
psql -U postgres
CREATE DATABASE escuela_db;
CREATE USER escuela_user WITH PASSWORD 'escuela_pass';
GRANT ALL PRIVILEGES ON DATABASE escuela_db TO escuela_user;
```

## 🔐 Roles y Permisos

El sistema soporta 7 roles principales:

1. **ROLE_ADMIN_SISTEMAS** - Administrador de sistemas (control total)
2. **ROLE_ADMIN** - Administración (finanzas, estadísticas)
3. **ROLE_COORDINACION** - Coordinación académica
4. **ROLE_SECRETARIA** - Secretaría (pagos, matrículas)
5. **ROLE_MAESTRO** - Maestros (notas, actividades)
6. **ROLE_PADRE** - Padres de familia (consultas)
7. **ROLE_ALUMNO** - Alumnos (tareas, notas)

## 📚 Módulos Principales

### Secretaría
- ✅ Pagos (contado, crédito, cuotas)
- ✅ Reporte de deudores
- ✅ Inscripción y matriculación
- ✅ Generación de contratos PDF
- ✅ Firma digital de contratos
- ✅ Corte del día

### Coordinación
- ✅ Publicación de anuncios
- ✅ Asignación de materias y grados
- ✅ Base de datos de profesores
- ✅ Descarga de boletas
- ✅ Cierre/reapertura de bimestres

### Administración
- ✅ Dashboard con estadísticas
- ✅ Resumen de cuentas
- ✅ Reportes por grado, sexo, rendimiento
- ✅ Control de asistencia

### Maestros
- ✅ Cargar actividades y notas
- ✅ Ver notas finales
- ✅ Calendario personal y global
- ✅ Chat con padres y alumnos

### Padres
- ✅ Ver cuenta y pagos
- ✅ Consultar tareas de hijos
- ✅ Descargar contratos
- ✅ Chat con maestros

### IA (Inteligencia Artificial)
- 🤖 Generación automática de horarios
- 📊 Predicción de riesgo académico
- 🔔 Alertas automáticas
- 💡 Recomendaciones personalizadas

## 🔧 Desarrollo

### Estructura de Entidades

```php
// Principales
User, Student, ParentEntity, Teacher

// Académicas
Grade, Section, Subject, Enrollment, GradeRecord, Attendance

// Financieras
Payment, Contract, Fee

// Comunicación
ChatRoom, ChatMessage, Notification

// IA
AIRiskScore, Schedule

// Auditoría
AuditLog
```

### API Endpoints

```
POST   /api/auth/login              # Login
GET    /api/auth/me                 # Usuario actual
GET    /api/students                # Listar estudiantes
POST   /api/enrollments             # Crear matrícula
POST   /api/payments                # Registrar pago
POST   /api/contracts/generate      # Generar contrato
GET    /api/chat/rooms              # Salas de chat
POST   /api/ai/schedule/generate    # Generar horario IA
GET    /api/ai/risk/predict/{id}    # Predecir riesgo
```

### Testing

```bash
# Backend - PHPUnit
cd backend-symfony
php bin/phpunit

# Frontend - Vitest
cd colegio-connect-main
npm run test

# E2E - Cypress
npm run test:e2e

# Tests de estrés - k6
k6 run stress-tests.js
```

## 📊 Base de Datos

### Conexión con DataGrip

1. Abrir DataGrip
2. Nueva conexión PostgreSQL
3. Configurar:
   - Host: localhost
   - Port: 5432
   - Database: escuela_db
   - User: escuela_user
   - Password: escuela_pass

### Migraciones

```bash
# Crear migración
php bin/console make:migration

# Ejecutar migraciones
php bin/console doctrine:migrations:migrate

# Revertir última migración
php bin/console doctrine:migrations:migrate prev
```

## 🚢 Despliegue

### Producción

```bash
# Build frontend
cd colegio-connect-main
npm run build

# Optimizar backend
cd backend-symfony
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod
```

### Docker Production

```bash
docker-compose -f docker-compose.prod.yml up -d
```

## 📝 Documentación API

Acceder a la documentación interactiva de la API:

- **Swagger UI**: http://localhost:8000/api/docs
- **OpenAPI JSON**: http://localhost:8000/api/docs.json

## 🤝 Contribuir

1. Fork el proyecto
2. Crear rama feature (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir Pull Request

## 📄 Licencia

Propietario - Oxford Bilingual School

## 👥 Equipo

Desarrollado para Oxford Bilingual School, Guatemala

## 📞 Soporte

Para soporte técnico, contactar a: sistemas@oxford.edu.gt

---

**Versión**: 1.0.0  
**Última actualización**: Diciembre 2025
