import { Platform, Dimensions, PixelRatio } from 'react-native';

/**
 * Información del dispositivo actual
 */
export class DeviceInfo {
  private static dimensions = Dimensions.get('window');

  static get width(): number {
    return this.dimensions.width;
  }

  static get height(): number {
    return this.dimensions.height;
  }

  static get pixelRatio(): number {
    return PixelRatio.get();
  }

  /**
   * Detecta si el dispositivo es un iPad
   */
  static isIPad(): boolean {
    return Platform.OS === 'ios' && Platform.isPad;
  }

  /**
   * Detecta si el dispositivo es una tablet (iPad o Android tablet)
   */
  static isTablet(): boolean {
    const { width, height } = this.dimensions;
    const aspectRatio = height / width;
    return (
      this.isIPad() ||
      (width >= 768 && aspectRatio < 1.6)
    );
  }

  /**
   * Detecta si el dispositivo es un iPhone pequeño (SE, 8, etc)
   */
  static isSmallDevice(): boolean {
    return this.width < 375;
  }

  /**
   * Detecta si el dispositivo tiene notch (iPhone X y posteriores)
   */
  static hasNotch(): boolean {
    return (
      Platform.OS === 'ios' &&
      !this.isIPad() &&
      (this.height >= 812 || this.width >= 812)
    );
  }

  /**
   * Detecta si el dispositivo tiene Dynamic Island (iPhone 14 Pro+)
   */
  static hasDynamicIsland(): boolean {
    return (
      Platform.OS === 'ios' &&
      !this.isIPad() &&
      this.height >= 852
    );
  }

  /**
   * Retorna el tipo de dispositivo
   */
  static getDeviceType(): 'iphone-se' | 'iphone' | 'iphone-pro' | 'ipad-mini' | 'ipad' | 'ipad-pro' | 'unknown' {
    if (Platform.OS !== 'ios') return 'unknown';

    if (this.isIPad()) {
      if (this.width >= 1024) return 'ipad-pro';
      if (this.width >= 820) return 'ipad';
      return 'ipad-mini';
    }

    if (this.width < 375) return 'iphone-se';
    if (this.hasDynamicIsland()) return 'iphone-pro';
    return 'iphone';
  }

  /**
   * Safe area insets estimados
   */
  static getSafeAreaInsets() {
    const insets = {
      top: 0,
      bottom: 0,
      left: 0,
      right: 0,
    };

    if (Platform.OS === 'ios') {
      if (this.hasNotch()) {
        insets.top = 44;
        insets.bottom = 34;
      } else {
        insets.top = 20;
        insets.bottom = 0;
      }

      // Landscape en iPhone con notch
      if (this.hasNotch() && this.width > this.height) {
        insets.left = 44;
        insets.right = 44;
      }
    }

    return insets;
  }

  /**
   * Actualiza las dimensiones
   */
  static updateDimensions() {
    this.dimensions = Dimensions.get('window');
  }
}

/**
 * Función de escalado base
 */
const SCREEN_WIDTH = DeviceInfo.width;
const BASE_WIDTH = 375; // iPhone 11 Pro, X, XS

export const scale = (size: number): number => {
  return (SCREEN_WIDTH / BASE_WIDTH) * size;
};

/**
 * Escalado vertical
 */
export const verticalScale = (size: number): number => {
  const SCREEN_HEIGHT = DeviceInfo.height;
  const BASE_HEIGHT = 812; // iPhone 11 Pro, X, XS
  return (SCREEN_HEIGHT / BASE_HEIGHT) * size;
};

/**
 * Escalado moderado (para fuentes y espaciados)
 * factor: 0 = sin escalar, 1 = escalar completamente
 */
export const moderateScale = (size: number, factor = 0.5): number => {
  return size + (scale(size) - size) * factor;
};

/**
 * Tamaños de fuente responsivos
 */
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

/**
 * Espaciado responsivo según dispositivo
 */
export const getResponsiveSpacing = () => {
  const width = DeviceInfo.width;

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

export const ResponsiveSpacing = getResponsiveSpacing();

/**
 * Breakpoints para responsive design
 */
export const Breakpoints = {
  small: 375,      // iPhone SE
  medium: 390,     // iPhone standard
  large: 428,      // iPhone Pro Max
  tablet: 744,     // iPad mini
  tabletLarge: 834, // iPad Air / Pro 11"
  desktop: 1024,   // iPad Pro 12.9"
};

/**
 * Utilidad para seleccionar valores según el tamaño de pantalla
 */
export const selectByScreenSize = <T>(values: {
  small?: T;
  medium?: T;
  large?: T;
  tablet?: T;
  desktop?: T;
  default: T;
}): T => {
  const width = DeviceInfo.width;

  if (width >= Breakpoints.desktop && values.desktop) return values.desktop;
  if (width >= Breakpoints.tablet && values.tablet) return values.tablet;
  if (width >= Breakpoints.large && values.large) return values.large;
  if (width >= Breakpoints.medium && values.medium) return values.medium;
  if (width >= Breakpoints.small && values.small) return values.small;

  return values.default;
};

export default DeviceInfo;
