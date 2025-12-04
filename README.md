# 🎓 Plataforma de Gestión Escolar

Sistema integral de gestión escolar con arquitectura hexagonal, IA avanzada y estándares enterprise.

![Version](https://img.shields.io/badge/version-2.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)
![Symfony](https://img.shields.io/badge/Symfony-7.x-000000?logo=symfony)
![React](https://img.shields.io/badge/React-18-61DAFB?logo=react)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?logo=postgresql)
![License](https://img.shields.io/badge/license-MIT-green)

---

## ✨ Características Principales

### 🏗️ Arquitectura
- ✅ **Hexagonal/DDD** - Domain-Driven Design
- ✅ **CQRS** - Command Query Responsibility Segregation
- ✅ **Event Sourcing** - Auditoría completa
- ✅ **Microservicios** - AI Service independiente

### 🔒 Seguridad (ISO 27001/27002)
- ✅ **MFA** - Autenticación Multi-Factor (TOTP)
- ✅ **Rate Limiting** - Protección contra ataques
- ✅ **Audit Logging** - Logs inmutables con firma digital
- ✅ **Encriptación** - End-to-end (TLS 1.3, Argon2id)
- ✅ **RBAC** - Control de acceso basado en roles

### 🚀 Backend (Symfony 7)
- ✅ **API REST** - Con OpenAPI/Swagger
- ✅ **GraphQL** - API Platform
- ✅ **Cache Distribuido** - Redis
- ✅ **Procesamiento Asíncrono** - Symfony Messenger
- ✅ **Value Objects** - Validación de dominio

### 💻 Frontend (React 18)
- ✅ **PWA** - Progressive Web App
- ✅ **Offline-First** - Service Workers
- ✅ **State Management** - Zustand
- ✅ **Lazy Loading** - Code Splitting
- ✅ **Web Vitals** - Monitoreo de performance

### 🤖 Inteligencia Artificial
- ✅ **OpenAI Integration** - GPT-4
- ✅ **RAG** - Retrieval Augmented Generation
- ✅ **Vector Database** - Pinecone
- ✅ **Predicción de Riesgo** - ML personalizado
- ✅ **Chat Educativo** - Asistente inteligente

### 🗄️ Base de Datos
- ✅ **PostgreSQL 16** - Con replicación
- ✅ **Índices Optimizados** - Full-text search
- ✅ **Particionamiento** - Por año académico
- ✅ **Connection Pooling** - PgBouncer
- ✅ **Backups Automáticos** - Encriptados

### 🧪 Testing
- ✅ **Unit Tests** - PHPUnit
- ✅ **Integration Tests** - KernelTestCase
- ✅ **E2E Tests** - Playwright
- ✅ **Load Tests** - K6

### 🔧 DevOps
- ✅ **CI/CD** - GitHub Actions
- ✅ **Kubernetes** - Orquestación
- ✅ **Helm Charts** - Despliegue
- ✅ **Monitoreo** - Prometheus + Grafana
- ✅ **Logging** - Structured logs (JSON)

---

## 🚀 Inicio Rápido

### Windows

```powershell
# 1. Clonar repositorio
git clone https://github.com/tu-org/plataforma-oficial.git
cd plataforma-oficial

# 2. Ejecutar script de inicio
.\start.ps1
```

### Mac/Linux

```bash
# 1. Clonar repositorio
git clone https://github.com/tu-org/plataforma-oficial.git
cd plataforma-oficial

# 2. Dar permisos y ejecutar
chmod +x start.sh
./start.sh
```

**Eso es todo!** El script automáticamente:
- Levanta Docker Compose
- Crea la base de datos
- Ejecuta migraciones
- Carga datos de prueba
- Abre el navegador

---

## 🔐 Credenciales de Prueba

| Rol | Email | Password |
|-----|-------|----------|
| Admin | `admin@school.com` | `Admin123!` |
| Coordinador | `coordinador@school.com` | `Coord123!` |
| Maestro | `maestro@school.com` | `Teacher123!` |
| Secretaria | `secretaria@school.com` | `Secret123!` |
| Padre | `padre@school.com` | `Parent123!` |

---

## 🌐 URLs

- **Frontend**: http://localhost:5173
- **API Docs**: http://localhost:8000/api/docs
- **GraphQL**: http://localhost:8000/api/graphql
- **AI Service**: http://localhost:8001/docs

---

## 📚 Documentación

- [Guía de Inicio Rápido - Windows](./INICIO-RAPIDO.md)
- [Guía de Inicio Rápido - Mac/Linux](./INICIO-RAPIDO-MAC.md)
- [Instalación Manual (sin Docker)](./INSTALACION-MANUAL.md)
- [Documentación de Seguridad ISO 27001](./docs/seguridad-iso27001.md)
- [Auditoría Fase 1](./docs/auditoria-fase1.md)
- [ADR-001: Arquitectura Hexagonal](./backend/docs/adr/ADR-001-arquitectura-hexagonal.md)

---

## 🏗️ Estructura del Proyecto

```
plataforma-oficial/
├── backend/              # Symfony 7 (PHP 8.2)
│   ├── src/
│   │   ├── Domain/      # Lógica de negocio
│   │   ├── Application/ # Casos de uso (CQRS)
│   │   └── Infrastructure/ # Implementaciones
│   ├── tests/           # Tests (Unit, Integration)
│   └── config/          # Configuración
├── frontend/            # React 18 + Vite
│   ├── src/
│   │   ├── components/  # Componentes React
│   │   ├── pages/       # Páginas
│   │   ├── store/       # Zustand stores
│   │   └── services/    # API clients
│   └── e2e/             # Tests E2E (Playwright)
├── ai-service/          # Python FastAPI
│   └── main.py          # Servicio de IA
├── k8s/                 # Kubernetes
│   ├── helm/            # Helm charts
│   └── monitoring/      # Prometheus, Grafana
├── .github/
│   └── workflows/       # CI/CD pipelines
└── docker-compose.yml   # Orquestación local
```

---

## 🛠️ Stack Tecnológico

### Backend
- **Framework**: Symfony 7.x
- **Lenguaje**: PHP 8.2
- **Base de Datos**: PostgreSQL 16
- **Cache**: Redis 7
- **Queue**: Symfony Messenger
- **API**: API Platform (REST + GraphQL)

### Frontend
- **Framework**: React 18
- **Build Tool**: Vite
- **Lenguaje**: TypeScript
- **Styling**: Tailwind CSS + Shadcn/ui
- **State**: Zustand
- **PWA**: Vite PWA Plugin

### IA
- **Framework**: FastAPI
- **Lenguaje**: Python 3.11
- **LLM**: OpenAI GPT-4
- **Vector DB**: Pinecone
- **ML**: scikit-learn, TensorFlow

### DevOps
- **Containerización**: Docker
- **Orquestación**: Kubernetes
- **CI/CD**: GitHub Actions
- **Monitoreo**: Prometheus + Grafana
- **Logging**: Monolog (JSON)

---

## 🧪 Ejecutar Tests

```bash
# Backend (PHPUnit)
docker-compose exec backend php bin/phpunit

# Frontend (Vitest)
cd frontend && npm run test

# E2E (Playwright)
cd frontend && npx playwright test

# Load Tests (K6)
k6 run load-tests/script.js
```

---

## 📦 Despliegue

### Docker Compose (Desarrollo)

```bash
docker-compose up -d
```

### Kubernetes (Producción)

```bash
# Con Helm
helm install school-platform ./k8s/helm/school-platform \
  --namespace production \
  --create-namespace

# Verificar
kubectl get pods -n production
```

---

## 🤝 Contribuir

1. Fork el proyecto
2. Crea tu feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push al branch (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver `LICENSE` para más detalles.

---

## 👥 Equipo

- **Arquitectura**: Hexagonal/DDD
- **Backend**: Symfony 7 + PostgreSQL
- **Frontend**: React 18 + Vite
- **IA**: Python + OpenAI + RAG
- **DevOps**: Kubernetes + GitHub Actions

---

## 📞 Soporte

- **Email**: support@schoolplatform.com
- **Documentación**: Ver carpeta `/docs`
- **Issues**: GitHub Issues

---

**Hecho con ❤️ para la educación**
