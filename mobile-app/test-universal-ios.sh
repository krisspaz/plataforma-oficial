#!/bin/bash

# Script para probar la app en múltiples dispositivos iOS
# KPixelCraft Mobile - Universal Testing

echo "🧪 Testing Universal iOS App en múltiples dispositivos..."
echo ""

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Array de dispositivos a probar
declare -a IPHONE_DEVICES=(
  "iPhone SE (3rd generation)"
  "iPhone 14"
  "iPhone 14 Pro"
  "iPhone 14 Pro Max"
  "iPhone 15"
  "iPhone 15 Pro"
  "iPhone 15 Pro Max"
)

declare -a IPAD_DEVICES=(
  "iPad mini (6th generation)"
  "iPad Air (5th generation)"
  "iPad Pro (11-inch) (4th generation)"
  "iPad Pro (12.9-inch) (6th generation)"
)

# Función para listar dispositivos disponibles
list_devices() {
  echo -e "${BLUE}📱 Dispositivos iOS disponibles:${NC}"
  echo ""
  xcrun simctl list devices available | grep "iPhone\|iPad" | grep -v "unavailable"
  echo ""
}

# Función para abrir un dispositivo específico
open_device() {
  local device_name="$1"
  echo -e "${BLUE}▶️  Abriendo: ${device_name}${NC}"
  
  # Obtener UDID del dispositivo
  local udid=$(xcrun simctl list devices available | grep "${device_name}" | grep -Eo '[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}' | head -1)
  
  if [ -z "$udid" ]; then
    echo -e "${RED}❌ Error: Dispositivo no encontrado${NC}"
    return 1
  fi
  
  # Boot el dispositivo si no está running
  xcrun simctl boot "$udid" 2>/dev/null
  
  # Abrir Simulator con el dispositivo
  open -a Simulator --args -CurrentDeviceUDID "$udid"
  
  echo -e "${GREEN}✅ Dispositivo abierto${NC}"
  sleep 3
}

# Función para probar en un dispositivo
test_device() {
  local device_name="$1"
  echo ""
  echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
  echo -e "${BLUE}📱 Testing en: ${device_name}${NC}"
  echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
  
  open_device "$device_name"
  
  # Ejecutar la app
  npx expo start --ios &
  EXPO_PID=$!
  
  echo ""
  echo -e "${GREEN}✅ App ejecutándose en ${device_name}${NC}"
  echo -e "${YELLOW}⚠️  Verifica la app en el simulador y presiona ENTER para continuar...${NC}"
  read -r
  
  # Matar el proceso de Expo
  kill $EXPO_PID 2>/dev/null
  
  # Cerrar el simulador
  # xcrun simctl shutdown "$device_name" 2>/dev/null
}

# Función para probar orientación
test_orientation() {
  local device_name="$1"
  echo ""
  echo -e "${BLUE}🔄 Probando orientaciones en: ${device_name}${NC}"
  
  open_device "$device_name"
  
  echo -e "${YELLOW}📱 Orientación PORTRAIT${NC}"
  echo "Verifica que la UI se vea correcta en modo vertical"
  echo "Presiona ENTER para probar landscape..."
  read -r
  
  echo ""
  echo -e "${YELLOW}📱 Rotando a LANDSCAPE (Cmd + → en el simulador)${NC}"
  echo "Por favor rota el simulador y verifica la UI"
  echo "Presiona ENTER para continuar..."
  read -r
}

# Menu principal
show_menu() {
  echo ""
  echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
  echo -e "${GREEN}║  Testing Universal iOS App             ║${NC}"
  echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
  echo ""
  echo "Selecciona una opción:"
  echo ""
  echo "  1) Listar todos los dispositivos disponibles"
  echo "  2) Probar en todos los iPhones"
  echo "  3) Probar en todos los iPads"
  echo "  4) Probar en un dispositivo específico"
  echo "  5) Probar orientaciones (Portrait/Landscape)"
  echo "  6) Test rápido (1 iPhone + 1 iPad)"
  echo "  7) Salir"
  echo ""
  echo -n "Opción: "
}

# Test rápido
quick_test() {
  echo -e "${BLUE}⚡ Test Rápido${NC}"
  echo ""
  
  echo "Testing en iPhone 15..."
  test_device "iPhone 15"
  
  echo ""
  echo "Testing en iPad Pro 12.9..."
  test_device "iPad Pro (12.9-inch) (6th generation)"
  
  echo ""
  echo -e "${GREEN}✅ Test rápido completado!${NC}"
}

# Probar todos los iPhones
test_all_iphones() {
  echo -e "${BLUE}📱 Probando todos los modelos de iPhone...${NC}"
  echo ""
  
  for device in "${IPHONE_DEVICES[@]}"; do
    test_device "$device"
  done
  
  echo ""
  echo -e "${GREEN}✅ Tests de iPhone completados!${NC}"
}

# Probar todos los iPads
test_all_ipads() {
  echo -e "${BLUE}📱 Probando todos los modelos de iPad...${NC}"
  echo ""
  
  for device in "${IPAD_DEVICES[@]}"; do
    test_device "$device"
  done
  
  echo ""
  echo -e "${GREEN}✅ Tests de iPad completados!${NC}"
}

# Probar dispositivo específico
test_specific_device() {
  echo ""
  echo -e "${BLUE}Ingresa el nombre del dispositivo (ej: iPhone 15 Pro):${NC}"
  read -r device_name
  
  test_device "$device_name"
}

# Loop principal
while true; do
  show_menu
  read -r option
  
  case $option in
    1)
      list_devices
      ;;
    2)
      test_all_iphones
      ;;
    3)
      test_all_ipads
      ;;
    4)
      test_specific_device
      ;;
    5)
      echo ""
      echo -e "${BLUE}Selecciona dispositivo para probar orientaciones:${NC}"
      echo "1) iPhone 15"
      echo "2) iPad Pro 12.9"
      read -r device_option
      
      case $device_option in
        1)
          test_orientation "iPhone 15"
          ;;
        2)
          test_orientation "iPad Pro (12.9-inch) (6th generation)"
          ;;
        *)
          echo -e "${RED}Opción inválida${NC}"
          ;;
      esac
      ;;
    6)
      quick_test
      ;;
    7)
      echo ""
      echo -e "${GREEN}👋 Adiós!${NC}"
      exit 0
      ;;
    *)
      echo -e "${RED}❌ Opción inválida${NC}"
      ;;
  esac
done
