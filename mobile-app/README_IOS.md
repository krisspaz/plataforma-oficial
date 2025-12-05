# 🍎 KPixelCraft Mobile - Versión iOS

## Inicio Rápido

### Prerrequisitos
- macOS Catalina o superior
- Xcode 14+
- Node.js 18+
- CocoaPods

### Instalación automática
```bash
npm run setup:ios
```

### Instalación manual
```bash
# 1. Instalar dependencias
npm install

# 2. Verificar configuración
xcodebuild -version

# 3. Ejecutar en simulador
npm run ios
```

## 📱 Comandos Disponibles

| Comando | Descripción |
|---------|-------------|
| `npm run ios` | Ejecutar en simulador iOS |
| `npm run ios:device` | Ejecutar en dispositivo físico |
| `npm run ios:simulator` | Ejecutar en simulador (explícito) |
| `npm run build:ios:dev` | Build de desarrollo |
| `npm run build:ios:preview` | Build de preview |
| `npm run build:ios:prod` | Build de producción |
| `npm run setup:ios` | Configurar entorno iOS |

## 🔧 Configuración

### Bundle Identifier
Editar en `app.json`:
```json
{
  "ios": {
    "bundleIdentifier": "com.tuempresa.tuapp"
  }
}
```

### Permisos
Los permisos están configurados en `app.json` bajo `ios.infoPlist`:
- ✅ Cámara
- ✅ Galería de fotos
- ✅ Micrófono

### Variables de entorno
Crear `.env.ios` con tus configuraciones:
```bash
BUNDLE_ID=com.kpixelcraft.mobile
DEVELOPMENT_TEAM=XXXXXXXXXX
```

## 🚀 Deployment

### TestFlight
```bash
# 1. Configurar EAS
eas build:configure

# 2. Build para TestFlight
eas build --platform ios --profile production

# 3. Submit a App Store Connect
eas submit --platform ios
```

### App Store
1. Build con perfil de producción
2. Upload a App Store Connect
3. Completar metadata
4. Submit para revisión

## 🎯 Características iOS

- ✅ Face ID / Touch ID
- ✅ Haptic Feedback
- ✅ Share Sheet nativo
- ✅ Dark Mode
- ✅ Safe Area optimizado
- ✅ Gestos nativos
- ✅ Push Notifications

## 📚 Documentación

- [Configuración Completa](./IOS_SETUP.md)
- [Optimizaciones iOS](./IOS_OPTIMIZATIONS.md)

## 🐛 Troubleshooting

### Simulador no inicia
```bash
xcrun simctl erase all
open -a Simulator
```

### Error de pods
```bash
cd ios
pod deintegrate
pod install
cd ..
```

### Error de firma
1. Abre Xcode
2. Preferences → Accounts
3. Agrega tu Apple ID

## 📞 Soporte

- Documentación: [docs/ios/](./docs/ios/)
- Issues: GitHub Issues
- Email: soporte@kpixelcraft.com
