# Plataforma Escolar 🎓

Sistema integral de gestión escolar construido con Symfony, React, y React Native.

## 🚀 Características Principales

- **Backend API REST** con Symfony 7.x
- **Frontend Web** con React + Vite
- **App Móvil** con React Native (iOS/Android)
- **Servicio de IA** con Python FastAPI
- **Base de datos** PostgreSQL 16 con replicación
- **Cache** Redis para alto rendimiento
- **Autenticación** JWT con refresh tokens
- **Pagos** integrados (Stripe, PayPal, BAC)
- **Chat** en tiempo real
- **Notificaciones** push
- **Generación de contratos** con firma digital

## 📋 Requisitos Previos

- Docker Desktop 4.x+
- Docker Compose 2.x+
- Node.js 20.x+ (para desarrollo local)
- PHP 8.3+ (para desarrollo local)
- Python 3.11+ (para desarrollo local)

## 🛠️ Instalación Rápida

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/plataforma-oficial.git
cd plataforma-oficial
```

### 2. Configurar variables de entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar y configurar las variables necesarias
nano .env
```

**Variables críticas a configurar:**
- `APP_SECRET`: Generar con `openssl rand -hex 32`
- `POSTGRES_PASSWORD`: Contraseña segura para PostgreSQL
- `REDIS_PASSWORD`: Contraseña segura para Redis
- `JWT_PASSPHRASE`: Frase secreta para JWT

### 3. Generar claves JWT

```bash
# Crear directorio
mkdir -p backend/config/jwt

# Generar clave privada
openssl genpkey -out backend/config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096

# Generar clave pública
openssl pkey -in backend/config/jwt/private.pem -out backend/config/jwt/public.pem -pubout

# Establecer permisos
chmod 600 backend/config/jwt/private.pem
chmod 644 backend/config/jwt/public.pem
```

### 4. Iniciar la plataforma

```bash
# Dar permisos de ejecución
chmod +x start.sh

# Iniciar todos los servicios
./start.sh

# O con fixtures de prueba
./start.sh --fixtures
```

### 5. Acceder a la plataforma

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **API Docs**: http://localhost:8000/api/docs
- **AI Service**: http://localhost:8001

## 📚 Estructura del Proyecto

```
plataforma-oficial/
├── backend/              # Symfony API
│   ├── src/
│   │   ├── Controller/   # API Controllers
│   │   ├── Entity/       # Doctrine Entities
│   │   ├── Repository/   # Data Repositories
│   │   ├── Service/      # Business Logic
│   │   └── Domain/       # Domain Layer (DDD)
│   ├── config/           # Configuración
│   └── migrations/       # Database Migrations
├── frontend/             # React Web App
│   ├── src/
│   │   ├── components/   # React Components
│   │   ├── pages/        # Page Components
│   │   ├── services/     # API Services
│   │   └── hooks/        # Custom Hooks
│   └── public/           # Static Assets
├── mobile/               # React Native App
│   ├── src/
│   │   ├── screens/      # App Screens
│   │   ├── components/   # RN Components
│   │   └── navigation/   # Navigation Config
│   └── android/          # Android Build
│   └── ios/              # iOS Build
├── ai-service/           # Python FastAPI
│   ├── main.py           # FastAPI App
│   └── requirements.txt  # Python Dependencies
├── docker/               # Docker Configs
│   ├── postgres/         # PostgreSQL Config
│   ├── nginx/            # Nginx Config
│   └── redis/            # Redis Config
└── docker-compose.yml    # Docker Orchestration
```

## 🔧 Comandos Útiles

### Backend (Symfony)

```bash
# Entrar al contenedor
docker exec -it school_backend bash

# Ejecutar migraciones
php bin/console doctrine:migrations:migrate

# Cargar fixtures
php bin/console app:fixtures:load

# Limpiar cache
php bin/console cache:clear

# Rotar secretos de seguridad
php bin/console app:security:rotate-secrets
```

### Frontend (React)

```bash
# Entrar al contenedor
docker exec -it school_frontend bash

# Instalar dependencias
npm install

# Build de producción
npm run build
```

### Base de Datos

```bash
# Backup
docker exec school_db_primary pg_dump -U app app > backup_$(date +%Y%m%d).sql

# Restore
docker exec -i school_db_primary psql -U app app < backup_20241204.sql

# Acceder a psql
docker exec -it school_db_primary psql -U app -d app
```

## 🧪 Testing

```bash
# Tests del backend
docker exec school_backend php bin/phpunit

# Tests del frontend
docker exec school_frontend npm test

# Tests E2E
docker exec school_frontend npm run test:e2e
```

## 📦 Deployment

### Producción con Docker

```bash
# Build de imágenes de producción
docker-compose -f docker-compose.prod.yml build

# Iniciar en producción
docker-compose -f docker-compose.prod.yml up -d

# Ver logs
docker-compose -f docker-compose.prod.yml logs -f
```

### Variables de Entorno de Producción

Asegúrate de configurar:

1. `APP_ENV=prod`
2. `APP_DEBUG=0`
3. Contraseñas seguras para DB y Redis
4. Claves de API reales (Stripe, PayPal, etc.)
5. `CORS_ALLOW_ORIGIN` con tu dominio
6. Configurar HTTPS/SSL

## 🔐 Seguridad

- **JWT**: Tokens con expiración de 1 hora
- **Rate Limiting**: Protección contra fuerza bruta
- **CORS**: Configurado para dominios específicos
- **SQL Injection**: Protegido por Doctrine ORM
- **XSS**: Sanitización automática en React
- **CSRF**: Tokens en formularios
- **Passwords**: Hashing con bcrypt
- **Secrets Rotation**: Comando automatizado

## 📊 Monitoreo

- **Logs**: Centralizados en `/var/log/symfony/`
- **Health Checks**: Endpoints `/health` en cada servicio
- **Métricas**: PostgreSQL stats, Redis info
- **Sentry**: Tracking de errores (opcional)

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto es privado y propietario.

## 👥 Equipo

- **Desarrollador Principal**: Kris Paz
- **Arquitectura**: Symfony + React + React Native
- **DevOps**: Docker + PostgreSQL + Redis

## 📞 Soporte

Para soporte, contacta a: [tu-email@ejemplo.com]

---

**Hecho con ❤️ para la educación en Guatemala** 🇬🇹
