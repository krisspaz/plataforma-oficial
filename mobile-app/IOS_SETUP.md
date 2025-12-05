# Configuración para iOS - KPixelCraft Mobile

## 🍎 Requisitos Previos

### Necesitas tener instalado:
1. **macOS** (Catalina o superior)
2. **Xcode** (versión 14 o superior)
   - Instalar desde App Store
3. **Node.js** (versión 18 o superior)
4. **CocoaPods** 
   ```bash
   sudo gem install cocoapods
   ```
5. **Watchman** (opcional pero recomendado)
   ```bash
   brew install watchman
   ```

## 📱 Instalación

### 1. Instalar dependencias
```bash
cd mobile-app
npm install
```

### 2. Instalar pods de iOS (si usas bare workflow)
```bash
npx pod-install
```

## 🚀 Ejecutar en iOS

### Opción 1: Expo Go (Desarrollo rápido)
```bash
npm run ios
```
Esto abrirá el simulador de iOS automáticamente.

### Opción 2: Simulador específico
```bash
npx expo start --ios
# Luego presiona 'i' para abrir en iOS
```

### Opción 3: Dispositivo físico
1. Conecta tu iPhone/iPad
2. Asegúrate de tener confianza en el dispositivo
3. Ejecuta:
   ```bash
   npx expo start --ios --device
   ```

## 🔧 Configuración de desarrollo

### Cambiar Bundle Identifier
Edita `app.json`:
```json
{
  "ios": {
    "bundleIdentifier": "com.tuempresa.tuapp"
  }
}
```

### Configurar iconos y splash screen
Los assets deben estar en:
- `assets/icon.png` - 1024x1024px
- `assets/splash-icon.png` - 1284x2778px
- `assets/adaptive-icon.png` - 1024x1024px

## 📦 Build para Production

### Usando EAS (Expo Application Services)
```bash
# Instalar EAS CLI
npm install -g eas-cli

# Login
eas login

# Configurar proyecto
eas build:configure

# Build para iOS
eas build --platform ios
```

### Usando Xcode directamente
```bash
# Generar proyecto nativo
npx expo prebuild

# Abrir en Xcode
open ios/*.xcworkspace

# Configurar signing y build desde Xcode
```

## 🔐 Firma de código (Code Signing)

### Para desarrollo:
1. Abre Xcode
2. Ve a Preferences → Accounts
3. Agrega tu Apple ID
4. Selecciona tu equipo en el proyecto

### Para App Store:
Necesitas:
- Apple Developer Account ($99/año)
- Certificados de distribución
- Provisioning profiles

## 📝 Notas importantes

### Permisos
Los permisos están configurados en `app.json` bajo `ios.infoPlist`:
- Cámara
- Galería de fotos
- Micrófono

### Compatibilidad
- iOS 13.4 o superior
- Soporta iPhone y iPad
- Dark mode opcional

### Testing
```bash
# Ejecutar tests
npm test

# Linting
npm run lint
```

## 🐛 Troubleshooting

### Error: "No devices found"
```bash
# Listar simuladores disponibles
xcrun simctl list devices

# Abrir simulador manualmente
open -a Simulator
```

### Error: "Command PhaseScriptExecution failed"
```bash
cd ios
pod deintegrate
pod install
cd ..
npm run ios
```

### Error: "Unable to boot device"
```bash
# Reset del simulador
xcrun simctl erase all
```

## 📚 Recursos

- [Expo iOS Documentation](https://docs.expo.dev/workflow/ios-simulator/)
- [React Native iOS Guide](https://reactnative.dev/docs/running-on-device)
- [Apple Developer](https://developer.apple.com)
