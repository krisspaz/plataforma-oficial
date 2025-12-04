#!/bin/bash

# Script de inicio para la Plataforma Escolar
# Levanta todos los servicios con Docker Compose

set -e

echo "🚀 Iniciando Plataforma Escolar..."
echo "=================================="

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar que Docker esté corriendo
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Error: Docker no está corriendo${NC}"
    echo "Por favor inicia Docker Desktop y vuelve a intentar"
    exit 1
fi

echo -e "${GREEN}✓ Docker está corriendo${NC}"

# Detener contenedores existentes
echo ""
echo -e "${YELLOW}🛑 Deteniendo contenedores existentes...${NC}"
docker-compose down 2>/dev/null || true

# Limpiar volúmenes si se solicita
if [ "$1" == "--clean" ]; then
    echo -e "${YELLOW}🧹 Limpiando volúmenes...${NC}"
    docker-compose down -v
fi

# Construir imágenes
echo ""
echo -e "${YELLOW}🔨 Construyendo imágenes Docker...${NC}"
docker-compose build --no-cache

# Levantar servicios
echo ""
echo -e "${YELLOW}🚀 Levantando servicios...${NC}"
docker-compose up -d

# Esperar a que los servicios estén listos
echo ""
echo -e "${YELLOW}⏳ Esperando a que los servicios estén listos...${NC}"
sleep 5

# Verificar estado de servicios
echo ""
echo -e "${GREEN}📊 Estado de servicios:${NC}"
docker-compose ps

# Verificar salud de servicios
echo ""
echo -e "${YELLOW}🏥 Verificando salud de servicios...${NC}"

# PostgreSQL
if docker-compose exec -T database pg_isready -U app > /dev/null 2>&1; then
    echo -e "${GREEN}✓ PostgreSQL: Listo${NC}"
else
    echo -e "${RED}✗ PostgreSQL: No disponible${NC}"
fi

# Redis
if docker-compose exec -T redis redis-cli -a redis_password ping > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Redis: Listo${NC}"
else
    echo -e "${RED}✗ Redis: No disponible${NC}"
fi

# Backend (esperar un poco más)
sleep 3
if curl -s http://localhost:8000/api > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Backend (Symfony): Listo${NC}"
else
    echo -e "${YELLOW}⚠ Backend (Symfony): Iniciando...${NC}"
fi

# Frontend
if curl -s http://localhost:5173 > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Frontend (React): Listo${NC}"
else
    echo -e "${YELLOW}⚠ Frontend (React): Iniciando...${NC}"
fi

# AI Service
if curl -s http://localhost:8001/health > /dev/null 2>&1; then
    echo -e "${GREEN}✓ AI Service (Python): Listo${NC}"
else
    echo -e "${YELLOW}⚠ AI Service (Python): Iniciando...${NC}"
fi

# Ejecutar migraciones
echo ""
echo -e "${YELLOW}🔄 Ejecutando migraciones de base de datos...${NC}"
docker-compose exec -T backend php bin/console doctrine:migrations:migrate --no-interaction || true

# Cargar fixtures (solo en desarrollo)
if [ "$1" == "--fixtures" ]; then
    echo -e "${YELLOW}📦 Cargando datos de prueba...${NC}"
    docker-compose exec -T backend php bin/console doctrine:fixtures:load --no-interaction
fi

# Mostrar URLs
echo ""
echo -e "${GREEN}=================================="
echo "✅ Plataforma Escolar iniciada correctamente"
echo "==================================${NC}"
echo ""
echo "📍 URLs de acceso:"
echo "   Frontend:    http://localhost:5173"
echo "   Backend API: http://localhost:8000/api"
echo "   AI Service:  http://localhost:8001"
echo "   AI Docs:     http://localhost:8001/docs"
echo ""
echo "🗄️  Base de datos:"
echo "   Host:     localhost"
echo "   Puerto:   5432"
echo "   Usuario:  app"
echo "   Password: !ChangeMe!"
echo "   Database: app"
echo ""
echo "📝 Comandos útiles:"
echo "   Ver logs:           docker-compose logs -f"
echo "   Ver logs backend:   docker-compose logs -f backend"
echo "   Ver logs AI:        docker-compose logs -f ai-service"
echo "   Detener:            docker-compose down"
echo "   Reiniciar:          ./start.sh"
echo "   Limpiar todo:       ./start.sh --clean"
echo "   Con fixtures:       ./start.sh --fixtures"
echo ""
echo -e "${YELLOW}💡 Tip: Espera 30-60 segundos para que todos los servicios terminen de iniciar${NC}"
echo ""
