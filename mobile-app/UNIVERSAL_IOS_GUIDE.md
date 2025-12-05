# 📱 Guía de Compatibilidad Universal iOS - iPhone & iPad
# KPixelCraft Mobile

## 🎯 Objetivo
Crear una app que funcione perfectamente en TODOS los dispositivos Apple sin bugs:
- iPhone (todos los modelos desde iPhone SE hasta iPhone 15 Pro Max)
- iPad (todos los modelos desde iPad mini hasta iPad Pro 12.9")
- Diferentes orientaciones (portrait y landscape)
- Diferentes tamaños de pantalla
- Notches, Dynamic Island, y bordes redondeados

## 📐 Estrategia de Diseño Responsivo

### 1. Detección de Dispositivo
```typescript
import { Platform, Dimensions, PixelRatio } from 'react-native';

export const DeviceInfo = {
  // Dimensiones de pantalla
  width: Dimensions.get('window').width,
  height: Dimensions.get('window').height,
  
  // Es tablet (iPad)
  isTablet: () => {
    const { width, height } = Dimensions.get('window');
    const aspectRatio = height / width;
    return (
      Platform.OS === 'ios' &&
      !Platform.isPad &&
      (width >= 768 || aspectRatio < 1.6)
    );
  },
  
  // Es iPad específicamente
  isIPad: Platform.isPad,
  
  // Pixel ratio
  pixelRatio: PixelRatio.get(),
  
  // Es iPhone pequeño (SE, 8, etc)
  isSmallDevice: () => {
    const { width } = Dimensions.get('window');
    return width < 375;
  },
  
  // Es iPhone con notch
  hasNotch: () => {
    const { height, width } = Dimensions.get('window');
    return (
      Platform.OS === 'ios' &&
      !Platform.isPad &&
      (height >= 812 || width >= 812)
    );
  },
  
  // Es Dynamic Island (iPhone 14 Pro+)
  hasDynamicIsland: () => {
    const { height } = Dimensions.get('window');
    return (
      Platform.OS === 'ios' &&
      height >= 852 // iPhone 14 Pro and newer
    );
  },
};
```

### 2. Hook para Dimensiones Responsivas
```typescript
import { useState, useEffect } from 'react';
import { Dimensions, ScaledSize } from 'react-native';

export const useResponsiveDimensions = () => {
  const [dimensions, setDimensions] = useState({
    window: Dimensions.get('window'),
    screen: Dimensions.get('screen'),
  });

  useEffect(() => {
    const subscription = Dimensions.addEventListener(
      'change',
      ({ window, screen }) => {
        setDimensions({ window, screen });
      }
    );

    return () => subscription?.remove();
  }, []);

  const isPortrait = dimensions.window.height > dimensions.window.width;
  const isLandscape = !isPortrait;
  const isTablet = dimensions.window.width >= 768;
  const isSmall = dimensions.window.width < 375;

  return {
    ...dimensions,
    isPortrait,
    isLandscape,
    isTablet,
    isSmall,
  };
};
```

### 3. Escalado Responsivo de Fuentes
```typescript
import { Dimensions, PixelRatio } from 'react-native';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

// Base width (iPhone 11 Pro, X, XS)
const baseWidth = 375;

export const scale = (size: number) => {
  return (SCREEN_WIDTH / baseWidth) * size;
};

export const verticalScale = (size: number) => {
  const { height: SCREEN_HEIGHT } = Dimensions.get('window');
  const baseHeight = 812;
  return (SCREEN_HEIGHT / baseHeight) * size;
};

export const moderateScale = (size: number, factor = 0.5) => {
  return size + (scale(size) - size) * factor;
};

// Fuentes responsivas
export const FontSize = {
  xs: moderateScale(10),
  sm: moderateScale(12),
  md: moderateScale(14),
  lg: moderateScale(16),
  xl: moderateScale(18),
  xxl: moderateScale(20),
  xxxl: moderateScale(24),
  huge: moderateScale(32),
};
```

## 🖥️ Layouts Adaptativos

### 1. Componente Container Responsivo
```typescript
import React from 'react';
import { View, StyleSheet, Platform, ViewStyle } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useResponsiveDimensions } from '../hooks/useResponsiveDimensions';

interface ResponsiveContainerProps {
  children: React.ReactNode;
  style?: ViewStyle;
  maxWidth?: number;
}

export const ResponsiveContainer: React.FC<ResponsiveContainerProps> = ({
  children,
  style,
  maxWidth = 768, // Max width for content on iPad
}) => {
  const { isTablet, window } = useResponsiveDimensions();

  return (
    <SafeAreaView style={[styles.safeArea, style]} edges={['top', 'bottom']}>
      <View
        style={[
          styles.container,
          isTablet && {
            maxWidth,
            alignSelf: 'center',
            width: '100%',
          },
        ]}
      >
        {children}
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
  },
  container: {
    flex: 1,
    width: '100%',
  },
});
```

### 2. Grid Responsivo
```typescript
import React from 'react';
import { View, StyleSheet, ViewStyle } from 'react-native';
import { useResponsiveDimensions } from '../hooks/useResponsiveDimensions';

interface ResponsiveGridProps {
  children: React.ReactNode;
  spacing?: number;
  minColumnWidth?: number;
}

export const ResponsiveGrid: React.FC<ResponsiveGridProps> = ({
  children,
  spacing = 16,
  minColumnWidth = 150,
}) => {
  const { window } = useResponsiveDimensions();
  
  const columns = Math.floor(window.width / minColumnWidth);
  const columnWidth = (window.width - spacing * (columns + 1)) / columns;

  return (
    <View style={[styles.grid, { padding: spacing }]}>
      {React.Children.map(children, (child) => (
        <View
          style={[
            styles.gridItem,
            {
              width: columnWidth,
              margin: spacing / 2,
            },
          ]}
        >
          {child}
        </View>
      ))}
    </View>
  );
};

const styles = StyleSheet.create({
  grid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    alignItems: 'flex-start',
  },
  gridItem: {
    marginBottom: 16,
  },
});
```

### 3. Layout Split para iPad
```typescript
import React from 'react';
import { View, StyleSheet } from 'react-native';
import { useResponsiveDimensions } from '../hooks/useResponsiveDimensions';

interface SplitLayoutProps {
  sidebar: React.ReactNode;
  content: React.ReactNode;
  sidebarWidth?: number;
}

export const SplitLayout: React.FC<SplitLayoutProps> = ({
  sidebar,
  content,
  sidebarWidth = 300,
}) => {
  const { isTablet, isLandscape } = useResponsiveDimensions();

  // En iPad landscape, mostrar sidebar
  const showSidebar = isTablet && isLandscape;

  if (showSidebar) {
    return (
      <View style={styles.splitContainer}>
        <View style={[styles.sidebar, { width: sidebarWidth }]}>
          {sidebar}
        </View>
        <View style={styles.content}>{content}</View>
      </View>
    );
  }

  // En iPhone o iPad portrait, solo mostrar content
  return <View style={styles.fullContent}>{content}</View>;
};

const styles = StyleSheet.create({
  splitContainer: {
    flex: 1,
    flexDirection: 'row',
  },
  sidebar: {
    borderRightWidth: StyleSheet.hairlineWidth,
    borderRightColor: '#e0e0e0',
  },
  content: {
    flex: 1,
  },
  fullContent: {
    flex: 1,
  },
});
```

## 📱 Orientación y Rotación

### 1. Hook para Orientación
```typescript
import { useState, useEffect } from 'react';
import { Dimensions } from 'react-native';

type Orientation = 'portrait' | 'landscape';

export const useOrientation = (): Orientation => {
  const [orientation, setOrientation] = useState<Orientation>(
    getOrientation()
  );

  function getOrientation(): Orientation {
    const { width, height } = Dimensions.get('window');
    return width > height ? 'landscape' : 'portrait';
  }

  useEffect(() => {
    const subscription = Dimensions.addEventListener('change', () => {
      setOrientation(getOrientation());
    });

    return () => subscription?.remove();
  }, []);

  return orientation;
};
```

### 2. Componente que Maneja Orientación
```typescript
import React from 'react';
import { View, StyleSheet } from 'react-native';
import { useOrientation } from '../hooks/useOrientation';

interface OrientationAwareViewProps {
  children: React.ReactNode;
  portraitStyle?: object;
  landscapeStyle?: object;
}

export const OrientationAwareView: React.FC<OrientationAwareViewProps> = ({
  children,
  portraitStyle,
  landscapeStyle,
}) => {
  const orientation = useOrientation();

  return (
    <View
      style={[
        styles.container,
        orientation === 'portrait' ? portraitStyle : landscapeStyle,
      ]}
    >
      {children}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});
```

## 🎨 Estilos Adaptativos

### 1. Sistema de Espaciado Responsivo
```typescript
import { Dimensions } from 'react-native';

const { width } = Dimensions.get('window');

const getResponsiveSpacing = () => {
  if (width >= 768) {
    // iPad
    return {
      xs: 6,
      sm: 12,
      md: 24,
      lg: 32,
      xl: 48,
      xxl: 64,
    };
  } else if (width >= 375) {
    // iPhone standard
    return {
      xs: 4,
      sm: 8,
      md: 16,
      lg: 24,
      xl: 32,
      xxl: 48,
    };
  } else {
    // iPhone SE
    return {
      xs: 3,
      sm: 6,
      md: 12,
      lg: 18,
      xl: 24,
      xxl: 36,
    };
  }
};

export const Spacing = getResponsiveSpacing();
```

### 2. Padding Dinámico según Dispositivo
```typescript
import { Platform, StatusBar } from 'react-native';

export const getSafeAreaInsets = () => {
  const insets = {
    top: 0,
    bottom: 0,
    left: 0,
    right: 0,
  };

  if (Platform.OS === 'ios') {
    // iPhone X y posteriores
    if (DeviceInfo.hasNotch()) {
      insets.top = 44;
      insets.bottom = 34;
    } else {
      insets.top = 20;
      insets.bottom = 0;
    }
  }

  return insets;
};
```

## 🖼️ Imágenes y Assets Responsivos

### 1. Imágenes que Escalan
```typescript
import React from 'react';
import { Image, ImageStyle, StyleSheet } from 'react-native';
import { useResponsiveDimensions } from '../hooks/useResponsiveDimensions';

interface ResponsiveImageProps {
  source: any;
  aspectRatio?: number;
  maxWidth?: number;
}

export const ResponsiveImage: React.FC<ResponsiveImageProps> = ({
  source,
  aspectRatio = 16 / 9,
  maxWidth,
}) => {
  const { window } = useResponsiveDimensions();
  
  const imageWidth = maxWidth
    ? Math.min(window.width, maxWidth)
    : window.width;
  const imageHeight = imageWidth / aspectRatio;

  return (
    <Image
      source={source}
      style={[
        styles.image,
        {
          width: imageWidth,
          height: imageHeight,
        },
      ]}
      resizeMode="cover"
    />
  );
};

const styles = StyleSheet.create({
  image: {
    borderRadius: 12,
  },
});
```

### 2. Asset Selection según Dispositivo
```typescript
export const getAssetForDevice = () => {
  const { isTablet, isSmall } = useResponsiveDimensions();
  
  if (isTablet) {
    return {
      logo: require('../assets/logo-large.png'),
      iconSize: 32,
      fontSize: 20,
    };
  } else if (isSmall) {
    return {
      logo: require('../assets/logo-small.png'),
      iconSize: 20,
      fontSize: 14,
    };
  } else {
    return {
      logo: require('../assets/logo.png'),
      iconSize: 24,
      fontSize: 16,
    };
  }
};
```

## 📋 Testing en Múltiples Dispositivos

### Dispositivos a Probar

#### iPhone
- ✅ iPhone SE (3rd gen) - 4.7" - 375x667
- ✅ iPhone 13/14 - 6.1" - 390x844
- ✅ iPhone 13/14 Pro - 6.1" - 393x852 (Dynamic Island)
- ✅ iPhone 13/14 Pro Max - 6.7" - 428x926
- ✅ iPhone 15 - 6.1" - 393x852
- ✅ iPhone 15 Pro Max - 6.7" - 430x932

#### iPad
- ✅ iPad mini - 8.3" - 744x1133
- ✅ iPad Air - 10.9" - 820x1180
- ✅ iPad Pro 11" - 834x1194
- ✅ iPad Pro 12.9" - 1024x1366

### Script de Testing
```bash
#!/bin/bash

echo "🧪 Testing en múltiples dispositivos iOS..."

devices=(
  "iPhone SE (3rd generation)"
  "iPhone 14"
  "iPhone 14 Pro"
  "iPhone 14 Pro Max"
  "iPhone 15"
  "iPhone 15 Pro Max"
  "iPad mini (6th generation)"
  "iPad Air (5th generation)"
  "iPad Pro (11-inch) (4th generation)"
  "iPad Pro (12.9-inch) (6th generation)"
)

for device in "${devices[@]}"; do
  echo "📱 Testing en: $device"
  npx expo start --ios --device="$device"
  sleep 5
done

echo "✅ Testing completado!"
```

## 🔧 Configuración app.json para Universal

```json
{
  "expo": {
    "name": "KPixelCraft Mobile",
    "slug": "kpixelcraft-mobile",
    "version": "1.0.0",
    "orientation": "default",
    "icon": "./assets/icon.png",
    "userInterfaceStyle": "automatic",
    "splash": {
      "image": "./assets/splash-icon.png",
      "resizeMode": "contain",
      "backgroundColor": "#ffffff"
    },
    "ios": {
      "supportsTablet": true,
      "bundleIdentifier": "com.kpixelcraft.mobile",
      "buildNumber": "1.0.0",
      "requireFullScreen": false,
      "infoPlist": {
        "UIRequiresFullScreen": false,
        "UIStatusBarStyle": "UIStatusBarStyleDefault",
        "UIViewControllerBasedStatusBarAppearance": true,
        "UISupportedInterfaceOrientations": [
          "UIInterfaceOrientationPortrait",
          "UIInterfaceOrientationLandscapeLeft",
          "UIInterfaceOrientationLandscapeRight"
        ],
        "UISupportedInterfaceOrientations~ipad": [
          "UIInterfaceOrientationPortrait",
          "UIInterfaceOrientationPortraitUpsideDown",
          "UIInterfaceOrientationLandscapeLeft",
          "UIInterfaceOrientationLandscapeRight"
        ]
      }
    }
  }
}
```

## 🎯 Checklist de Compatibilidad

### UI/UX
- [ ] Textos legibles en todos los tamaños
- [ ] Botones tienen tamaño mínimo de 44x44pt
- [ ] Espaciado proporcional al tamaño de pantalla
- [ ] Imágenes escalan correctamente
- [ ] Layouts adaptativos (grid en iPad)

### SafeArea
- [ ] Respeta notch en iPhone X+
- [ ] Respeta Dynamic Island en iPhone 14 Pro+
- [ ] Respeta home indicator
- [ ] No corta contenido en esquinas redondeadas

### Orientación
- [ ] Portrait funciona en iPhone
- [ ] Landscape funciona en iPhone
- [ ] Portrait funciona en iPad
- [ ] Landscape funciona en iPad
- [ ] Rotación suave sin bugs

### Funcionalidad
- [ ] Teclado no cubre inputs
- [ ] Scroll funciona correctamente
- [ ] Gestos funcionan en todos los dispositivos
- [ ] Navegación funciona correctamente
- [ ] No hay overflow de contenido

### Performance
- [ ] Animaciones a 60fps
- [ ] Imágenes optimizadas para cada dispositivo
- [ ] No hay memory leaks
- [ ] Carga rápida en todos los dispositivos

## 🚀 Comandos de Testing

```bash
# Probar en iPhone específico
npx expo start --ios --device="iPhone 15 Pro"

# Probar en iPad específico  
npx expo start --ios --device="iPad Pro (12.9-inch)"

# Listar todos los simuladores disponibles
xcrun simctl list devices available

# Abrir simulador específico
open -a Simulator --args -CurrentDeviceUDID <UDID>

# Probar en modo landscape
# (rotar desde el simulador: Cmd + → o Cmd + ←)

# Probar dark mode
# Settings → Developer → Dark Appearance
```

## 📊 Breakpoints Recomendados

```typescript
export const Breakpoints = {
  // iPhone SE
  small: 375,
  
  // iPhone standard
  medium: 390,
  
  // iPhone Pro Max
  large: 428,
  
  // iPad mini
  tablet: 744,
  
  // iPad Air / Pro 11"
  tabletLarge: 834,
  
  // iPad Pro 12.9"
  desktop: 1024,
};

export const useBreakpoint = () => {
  const { window } = useResponsiveDimensions();
  
  return {
    isSmall: window.width < Breakpoints.medium,
    isMedium: window.width >= Breakpoints.medium && window.width < Breakpoints.large,
    isLarge: window.width >= Breakpoints.large && window.width < Breakpoints.tablet,
    isTablet: window.width >= Breakpoints.tablet && window.width < Breakpoints.desktop,
    isDesktop: window.width >= Breakpoints.desktop,
  };
};
```

---

✅ **Con esta guía, tu app funcionará perfectamente en TODOS los dispositivos Apple!**
