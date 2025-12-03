#!/bin/bash

# Script de Inicio Automático - Plataforma Escolar
# Bash Script para Mac/Linux

set -e

echo "🚀 Iniciando Plataforma Escolar..."
echo ""

# Verificar que Docker esté corriendo
echo "📋 Verificando Docker..."
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker no está corriendo. Por favor inicia Docker Desktop."
    exit 1
fi
echo "✅ Docker está corriendo"
echo ""

# Levantar servicios
echo "🐳 Levantando servicios con Docker Compose..."
docker-compose up -d

echo "✅ Servicios levantados"
echo ""

# Esperar a que los servicios estén listos
echo "⏳ Esperando a que los servicios estén listos (30 segundos)..."
sleep 30

# Verificar estado de servicios
echo "📊 Estado de servicios:"
docker-compose ps
echo ""

# Ejecutar migraciones
echo "🗄️  Configurando base de datos..."
docker-compose exec -T backend php bin/console doctrine:database:create --if-not-exists 2>/dev/null || true
docker-compose exec -T backend php bin/console doctrine:migrations:migrate --no-interaction

echo "✅ Base de datos configurada"
echo ""

# Cargar datos de prueba
echo "👥 Cargando datos de prueba..."
docker-compose exec -T backend php bin/console app:fixtures:load --no-interaction

echo "✅ Datos de prueba cargados"
echo ""

# Mostrar información
echo "================================================================================"
echo "🎉 ¡Plataforma lista!"
echo "================================================================================"
echo ""

echo "🌐 URLs de Acceso:"
echo "   Frontend:    http://localhost:5173"
echo "   Backend API: http://localhost:8000/api"
echo "   API Docs:    http://localhost:8000/api/docs"
echo "   GraphQL:     http://localhost:8000/api/graphql"
echo "   AI Service:  http://localhost:8001/docs"
echo ""

echo "🔐 Credenciales de Prueba:"
echo "   Admin:       admin@school.com / Admin123!"
echo "   Coordinador: coordinador@school.com / Coord123!"
echo "   Maestro:     maestro@school.com / Teacher123!"
echo "   Secretaria:  secretaria@school.com / Secret123!"
echo "   Padre:       padre@school.com / Parent123!"
echo ""

echo "📚 Comandos Útiles:"
echo "   Ver logs:    docker-compose logs -f"
echo "   Detener:     docker-compose down"
echo "   Reiniciar:   docker-compose restart"
echo ""

echo "Abriendo frontend en el navegador..."
sleep 2

# Detectar sistema operativo y abrir navegador
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    open http://localhost:5173
elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
    # Linux
    xdg-open http://localhost:5173 2>/dev/null || sensible-browser http://localhost:5173 2>/dev/null || echo "Por favor abre http://localhost:5173 en tu navegador"
fi

echo ""
echo "✨ ¡Disfruta la plataforma!"
