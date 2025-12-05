# Optimizaciones específicas para iOS
# KPixelCraft Mobile

## 🎨 Diseño iOS-Native

### 1. Navigation Bar (iOS-style)
```typescript
import { Platform } from 'react-native';

const navigationOptions = {
  headerStyle: {
    backgroundColor: Platform.OS === 'ios' ? '#f8f8f8' : '#fff',
  },
  headerTitleStyle: {
    fontSize: Platform.OS === 'ios' ? 17 : 20,
    fontWeight: Platform.OS === 'ios' ? '600' : 'bold',
  },
};
```

### 2. Gestos nativos de iOS
```bash
npm install react-native-gesture-handler
```

### 3. Haptic Feedback (vibración táctil)
```typescript
import * as Haptics from 'expo-haptics';

// En iOS
const handlePress = async () => {
  await Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
};
```

## ⚡ Performance

### 1. Optimizar imágenes
- Usa formato WebP para iOS 14+
- Implementa lazy loading
- Usa FastImage:
```bash
npm install react-native-fast-image
```

### 2. Hermes Engine
Ya habilitado en tu configuración. Mejora:
- Tiempo de inicio: ~50% más rápido
- Tamaño del app: ~40% más pequeño
- Uso de memoria: ~30% menos

### 3. Native Driver para animaciones
```typescript
import { Animated } from 'react-native';

Animated.timing(fadeAnim, {
  toValue: 1,
  duration: 300,
  useNativeDriver: true, // ✅ Importante para iOS
}).start();
```

## 📱 Características específicas de iOS

### 1. Face ID / Touch ID
```bash
npm install expo-local-authentication
```

```typescript
import * as LocalAuthentication from 'expo-local-authentication';

const authenticate = async () => {
  const hasHardware = await LocalAuthentication.hasHardwareAsync();
  const isEnrolled = await LocalAuthentication.isEnrolledAsync();
  
  if (hasHardware && isEnrolled) {
    const result = await LocalAuthentication.authenticateAsync({
      promptMessage: 'Autenticar con Face ID',
      fallbackLabel: 'Usar código',
    });
    return result.success;
  }
  return false;
};
```

### 2. Share Sheet nativo
```typescript
import { Share, Platform } from 'react-native';

const shareContent = async () => {
  await Share.share(
    {
      message: 'Compartir desde KPixelCraft',
      url: 'https://example.com',
      title: 'Título del share',
    },
    {
      subject: 'Email Subject', // Solo iOS
      dialogTitle: 'Compartir via', // Solo Android
    }
  );
};
```

### 3. Push Notifications
```bash
npm install expo-notifications
```

Configurar en `app.json`:
```json
{
  "ios": {
    "config": {
      "usesNonExemptEncryption": false
    },
    "infoPlist": {
      "UIBackgroundModes": ["remote-notification"]
    }
  }
}
```

## 🔒 Seguridad iOS

### 1. Keychain (almacenamiento seguro)
```bash
npm install expo-secure-store
```

```typescript
import * as SecureStore from 'expo-secure-store';

// Guardar token
await SecureStore.setItemAsync('userToken', token);

// Recuperar token
const token = await SecureStore.getItemAsync('userToken');
```

### 2. App Transport Security (ATS)
Si necesitas HTTP (no recomendado):
```json
{
  "ios": {
    "infoPlist": {
      "NSAppTransportSecurity": {
        "NSAllowsArbitraryLoads": false,
        "NSExceptionDomains": {
          "tu-api.com": {
            "NSExceptionAllowsInsecureHTTPLoads": true
          }
        }
      }
    }
  }
}
```

## 🎯 UI/UX iOS

### 1. System Fonts
```typescript
import { Platform, StyleSheet } from 'react-native';

const styles = StyleSheet.create({
  text: {
    fontFamily: Platform.select({
      ios: 'System',
      android: 'Roboto',
    }),
  },
});
```

### 2. iOS Shadow
```typescript
const shadowStyle = Platform.select({
  ios: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
  },
  android: {
    elevation: 5,
  },
});
```

### 3. Swipe Gestures
```bash
npm install react-native-gesture-handler
```

```typescript
import { Swipeable } from 'react-native-gesture-handler';

<Swipeable
  renderRightActions={() => <DeleteButton />}
  onSwipeableRightOpen={() => handleDelete()}
>
  <ListItem />
</Swipeable>
```

## 🧪 Testing en iOS

### 1. Detox (E2E Testing)
```bash
npm install -D detox
```

### 2. Jest con React Native
```bash
npm install -D @testing-library/react-native
```

### 3. XCTest (tests nativos)
Ubicación: `ios/KPixelCraftTests/`

## 📊 Analytics

### 1. Firebase Analytics
```bash
npm install @react-native-firebase/app
npm install @react-native-firebase/analytics
```

### 2. App Store Analytics
- Configurar en App Store Connect
- Eventos automáticos de instalación
- Conversiones de campaña

## 🚀 Optimizaciones de Build

### 1. Reducir tamaño del IPA
En `app.json`:
```json
{
  "ios": {
    "bitcode": true,
    "buildNumber": "1"
  }
}
```

### 2. Modo Release optimizado
```bash
npx expo build:ios --release-channel production
```

### 3. Bundle Splitting (código bajo demanda)
```typescript
const AnalyticsScreen = React.lazy(() => import('./screens/Analytics'));
```

## 📱 Características modernas de iOS

### 1. Widgets (iOS 14+)
Requiere native module custom

### 2. App Clips
Configurar en Xcode directamente

### 3. Live Activities (iOS 16+)
Para notificaciones en tiempo real

## 🎨 Dark Mode
```typescript
import { useColorScheme } from 'react-native';

function MyComponent() {
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  
  return (
    <View style={{ 
      backgroundColor: isDark ? '#000' : '#fff' 
    }}>
      <Text style={{ color: isDark ? '#fff' : '#000' }}>
        Texto que respeta el tema del sistema
      </Text>
    </View>
  );
}
```

## 🔧 Configuraciones avanzadas

### 1. Info.plist personalizado
```json
{
  "ios": {
    "infoPlist": {
      "CFBundleAllowMixedLocalizations": true,
      "UIViewControllerBasedStatusBarAppearance": true,
      "ITSAppUsesNonExemptEncryption": false
    }
  }
}
```

### 2. Capabilities en Xcode
- iCloud
- Push Notifications
- Apple Pay
- HealthKit
- etc.

## 📖 Recursos útiles

- [Apple Human Interface Guidelines](https://developer.apple.com/design/human-interface-guidelines/)
- [iOS App Store Review Guidelines](https://developer.apple.com/app-store/review/guidelines/)
- [Expo iOS Documentation](https://docs.expo.dev/workflow/ios-simulator/)
