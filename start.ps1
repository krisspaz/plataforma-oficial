# Script de Inicio Automático - Plataforma Escolar
# PowerShell Script

Write-Host "🚀 Iniciando Plataforma Escolar..." -ForegroundColor Cyan
Write-Host ""

# Verificar que Docker esté corriendo
Write-Host "📋 Verificando Docker..." -ForegroundColor Yellow
$dockerRunning = docker info 2>&1 | Select-String "Server Version"
if (-not $dockerRunning) {
    Write-Host "❌ Docker no está corriendo. Por favor inicia Docker Desktop." -ForegroundColor Red
    exit 1
}
Write-Host "✅ Docker está corriendo" -ForegroundColor Green
Write-Host ""

# Levantar servicios
Write-Host "🐳 Levantando servicios con Docker Compose..." -ForegroundColor Yellow
docker-compose up -d

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al levantar los servicios" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Servicios levantados" -ForegroundColor Green
Write-Host ""

# Esperar a que los servicios estén listos
Write-Host "⏳ Esperando a que los servicios estén listos (30 segundos)..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Verificar estado de servicios
Write-Host "📊 Estado de servicios:" -ForegroundColor Yellow
docker-compose ps
Write-Host ""

# Ejecutar migraciones
Write-Host "🗄️  Configurando base de datos..." -ForegroundColor Yellow
docker-compose exec -T backend php bin/console doctrine:database:create --if-not-exists 2>$null
docker-compose exec -T backend php bin/console doctrine:migrations:migrate --no-interaction

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Base de datos configurada" -ForegroundColor Green
} else {
    Write-Host "⚠️  Advertencia: Error en migraciones (puede ser normal si ya existen)" -ForegroundColor Yellow
}
Write-Host ""

# Cargar datos de prueba
Write-Host "👥 Cargando datos de prueba..." -ForegroundColor Yellow
docker-compose exec -T backend php bin/console app:fixtures:load --no-interaction

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Datos de prueba cargados" -ForegroundColor Green
} else {
    Write-Host "⚠️  Advertencia: Error al cargar fixtures" -ForegroundColor Yellow
}
Write-Host ""

# Mostrar información
Write-Host "=" * 80 -ForegroundColor Cyan
Write-Host "🎉 ¡Plataforma lista!" -ForegroundColor Green
Write-Host "=" * 80 -ForegroundColor Cyan
Write-Host ""

Write-Host "🌐 URLs de Acceso:" -ForegroundColor Yellow
Write-Host "   Frontend:    http://localhost:5173" -ForegroundColor White
Write-Host "   Backend API: http://localhost:8000/api" -ForegroundColor White
Write-Host "   API Docs:    http://localhost:8000/api/docs" -ForegroundColor White
Write-Host "   GraphQL:     http://localhost:8000/api/graphql" -ForegroundColor White
Write-Host "   AI Service:  http://localhost:8001/docs" -ForegroundColor White
Write-Host ""

Write-Host "🔐 Credenciales de Prueba:" -ForegroundColor Yellow
Write-Host "   Admin:       admin@school.com / Admin123!" -ForegroundColor White
Write-Host "   Coordinador: coordinador@school.com / Coord123!" -ForegroundColor White
Write-Host "   Maestro:     maestro@school.com / Teacher123!" -ForegroundColor White
Write-Host "   Secretaria:  secretaria@school.com / Secret123!" -ForegroundColor White
Write-Host "   Padre:       padre@school.com / Parent123!" -ForegroundColor White
Write-Host ""

Write-Host "📚 Comandos Útiles:" -ForegroundColor Yellow
Write-Host "   Ver logs:    docker-compose logs -f" -ForegroundColor White
Write-Host "   Detener:     docker-compose down" -ForegroundColor White
Write-Host "   Reiniciar:   docker-compose restart" -ForegroundColor White
Write-Host ""

Write-Host "Abriendo frontend en el navegador..." -ForegroundColor Cyan
Start-Sleep -Seconds 2
Start-Process "http://localhost:5173"

Write-Host ""
Write-Host "✨ ¡Disfruta la plataforma!" -ForegroundColor Green
