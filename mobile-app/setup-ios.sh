#!/bin/bash

# Script de configuración para desarrollo iOS
# KPixelCraft Mobile

echo "🍎 Configurando entorno iOS para KPixelCraft Mobile..."

# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Verificar si estamos en macOS
if [[ "$OSTYPE" != "darwin"* ]]; then
    echo -e "${RED}❌ Error: Este script solo funciona en macOS${NC}"
    exit 1
fi

# Verificar Node.js
echo -e "${BLUE}📦 Verificando Node.js...${NC}"
if ! command -v node &> /dev/null; then
    echo -e "${RED}❌ Node.js no está instalado${NC}"
    echo "Por favor instala Node.js desde: https://nodejs.org"
    exit 1
fi
echo -e "${GREEN}✅ Node.js $(node -v) encontrado${NC}"

# Verificar Xcode
echo -e "${BLUE}🔨 Verificando Xcode...${NC}"
if ! command -v xcodebuild &> /dev/null; then
    echo -e "${RED}❌ Xcode no está instalado${NC}"
    echo "Por favor instala Xcode desde el App Store"
    exit 1
fi
echo -e "${GREEN}✅ Xcode encontrado${NC}"

# Verificar Xcode Command Line Tools
echo -e "${BLUE}🛠️  Verificando Xcode Command Line Tools...${NC}"
if ! xcode-select -p &> /dev/null; then
    echo -e "${RED}❌ Xcode Command Line Tools no instalados${NC}"
    echo "Instalando..."
    xcode-select --install
    echo "Espera a que termine la instalación y ejecuta este script nuevamente"
    exit 1
fi
echo -e "${GREEN}✅ Xcode Command Line Tools instalados${NC}"

# Verificar CocoaPods
echo -e "${BLUE}🥥 Verificando CocoaPods...${NC}"
if ! command -v pod &> /dev/null; then
    echo -e "${RED}⚠️  CocoaPods no está instalado${NC}"
    echo "Instalando CocoaPods..."
    sudo gem install cocoapods
fi
echo -e "${GREEN}✅ CocoaPods $(pod --version) encontrado${NC}"

# Verificar Watchman (opcional)
echo -e "${BLUE}👀 Verificando Watchman...${NC}"
if ! command -v watchman &> /dev/null; then
    echo -e "${RED}⚠️  Watchman no está instalado (opcional)${NC}"
    if command -v brew &> /dev/null; then
        echo "¿Quieres instalar Watchman? (mejora el rendimiento) [y/N]"
        read -r response
        if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
            brew install watchman
        fi
    fi
else
    echo -e "${GREEN}✅ Watchman encontrado${NC}"
fi

# Instalar dependencias npm
echo -e "${BLUE}📦 Instalando dependencias npm...${NC}"
npm install

# Verificar si existe ios/ directory (bare workflow)
if [ -d "ios" ]; then
    echo -e "${BLUE}📱 Instalando pods de iOS...${NC}"
    cd ios
    pod install
    cd ..
    echo -e "${GREEN}✅ Pods instalados${NC}"
else
    echo -e "${BLUE}ℹ️  Usando Expo managed workflow (no requiere pods)${NC}"
fi

# Verificar simuladores disponibles
echo -e "${BLUE}📱 Simuladores de iOS disponibles:${NC}"
xcrun simctl list devices available | grep "iPhone"

# Mostrar siguiente paso
echo ""
echo -e "${GREEN}✅ Configuración completada!${NC}"
echo ""
echo -e "${BLUE}Para ejecutar la app:${NC}"
echo "  npm run ios              # Abre en simulador"
echo "  npm start                # Inicia Expo DevTools"
echo ""
echo -e "${BLUE}Para abrir el simulador manualmente:${NC}"
echo "  open -a Simulator"
echo ""
echo -e "${BLUE}Para ejecutar en dispositivo físico:${NC}"
echo "  npm start -- --ios --device"
echo ""
