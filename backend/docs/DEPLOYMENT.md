# Guía de Despliegue a Producción

Esta guía detalla los pasos para desplegar la plataforma KPixelCraft en un entorno de producción Linux (Ubuntu/Debian).

## Requisitos Previos
- Servidor Linux (Ubuntu 22.04 LTS recomendado)
- Docker y Docker Compose instalados
- Dominio configurado apuntando al servidor
- Certificados SSL (LetsEncrypt)

## 1. Preparación del Entorno

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/usuario/plataforma-oficial.git
   cd plataforma-oficial
   ```

2. Configurar variables de entorno:
   ```bash
   cp .env.example .env
   # Editar .env con credenciales reales de producción
   nano .env
   ```

## 2. Configuración de Docker para Producción

Asegúrate de usar el archivo `docker-compose.prod.yml` (si existe) o ajustar el `docker-compose.yml` para no exponer puertos innecesarios.

```bash
# Construir imágenes optimizadas
docker-compose -f docker-compose.yml build --no-cache
```

## 3. Base de Datos

1. Iniciar servicios:
   ```bash
   docker-compose up -d
   ```

2. Ejecutar migraciones:
   ```bash
   docker-compose exec backend php bin/console doctrine:migrations:migrate --no-interaction
   ```

## 4. Optimización Backend (Symfony)

Dentro del contenedor backend:

```bash
# Cache de configuración y rutas
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Optimizar autoloader
composer dump-autoload --optimize --no-dev --classmap-authoritative
```

## 5. Optimización Frontend

El frontend debe construirse y servirse como archivos estáticos (Nginx).

```bash
# En la máquina de build o contenedor node
cd frontend
npm ci
npm run build
# Los archivos en dist/ deben copiarse al directorio web del servidor web
```

## 6. Configuración Nginx (Reverse Proxy)

Ejemplo de configuración para `/etc/nginx/sites-available/kpixelcraft`:

```nginx
server {
    server_name kpixelcraft.com;
    root /var/www/plataforma-oficial/frontend/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

## 7. Monitoreo y Mantenimiento

- **Logs**: `docker-compose logs -f --tail=100`
- **Backups**: Configurar cronjob para `pg_dump` diario.
