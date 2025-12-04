# Deployment Guide

## Prerequisites

- Docker & Docker Compose
- kubectl (for Kubernetes deployment)
- Access to container registry

## Local Development

```bash
# Clone repository
git clone https://github.com/your-org/plataforma-oficial.git
cd plataforma-oficial

# Copy environment files
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# Start services
docker compose up -d

# Run migrations
docker compose exec backend php bin/console doctrine:migrations:migrate

# Load fixtures (development only)
docker compose exec backend php bin/console doctrine:fixtures:load

# Access application
# Frontend: http://localhost:3000
# Backend API: http://localhost:8080/api
```

## Production Deployment

### Docker Compose (Single Server)

```bash
# Build production images
docker compose -f docker-compose.prod.yml build

# Deploy
docker compose -f docker-compose.prod.yml up -d

# Run migrations
docker compose -f docker-compose.prod.yml exec backend php bin/console doctrine:migrations:migrate --no-interaction
```

### Kubernetes

```bash
# Apply configurations
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/secrets.yaml
kubectl apply -f k8s/configmaps.yaml
kubectl apply -f k8s/deployments/
kubectl apply -f k8s/services/
kubectl apply -f k8s/ingress.yaml

# Check status
kubectl get pods -n school-platform
```

## Environment Variables

### Backend

| Variable | Description | Example |
|----------|-------------|---------|
| `DATABASE_URL` | PostgreSQL connection | `postgresql://user:pass@host:5432/db` |
| `REDIS_URL` | Redis connection | `redis://localhost:6379` |
| `APP_SECRET` | Symfony secret key | Random 32-char string |
| `JWT_SECRET_KEY` | JWT private key path | `/app/config/jwt/private.pem` |
| `JWT_PUBLIC_KEY` | JWT public key path | `/app/config/jwt/public.pem` |
| `SENTRY_DSN` | Sentry error tracking | `https://...@sentry.io/...` |

### Frontend

| Variable | Description | Example |
|----------|-------------|---------|
| `VITE_API_URL` | Backend API URL | `https://api.school.com` |
| `VITE_SENTRY_DSN` | Sentry frontend DSN | `https://...@sentry.io/...` |

## Database Backups

```bash
# Create backup
docker compose exec postgres pg_dump -U school school_db > backup_$(date +%Y%m%d).sql

# Restore backup
docker compose exec -T postgres psql -U school school_db < backup_20251204.sql
```

## SSL/TLS

1. Obtain certificates from Let's Encrypt
2. Configure in nginx:

```nginx
server {
    listen 443 ssl http2;
    server_name school.example.com;
    
    ssl_certificate /etc/letsencrypt/live/school.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/school.example.com/privkey.pem;
    
    # ... rest of config
}
```

## Monitoring

- **Sentry**: Error tracking (backend + frontend)
- **Redis Commander**: Cache monitoring (dev only)
- **PostgreSQL**: Use pgAdmin or similar

## Health Checks

```bash
# Backend health
curl https://api.school.com/health

# Frontend health
curl https://school.com/
```

## Scaling

### Horizontal Scaling

```yaml
# k8s/deployments/backend.yaml
spec:
  replicas: 3  # Increase replicas
```

### Database Read Replicas

Configure read replicas in PostgreSQL for improved read performance.

## Troubleshooting

### Common Issues

1. **Database connection failed**
   - Check DATABASE_URL in .env
   - Verify PostgreSQL is running

2. **JWT errors**
   - Regenerate keys: `php bin/console lexik:jwt:generate-keypair`

3. **Cache issues**
   - Clear cache: `php bin/console cache:clear`
   - Verify Redis connection

4. **Frontend build failures**
   - Clear node_modules and reinstall
   - Check Node.js version (20+)
