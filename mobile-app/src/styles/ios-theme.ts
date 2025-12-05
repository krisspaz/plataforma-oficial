/**
 * Guía de Estilos iOS - KPixelCraft Mobile
 * 
 * Basado en Apple Human Interface Guidelines
 * https://developer.apple.com/design/human-interface-guidelines/
 */

import { Platform, StyleSheet } from 'react-native';

/**
 * Tipografía iOS
 * SF Pro Text y SF Pro Display
 */
export const Typography = {
  // Large Title
  largeTitle: {
    fontSize: 34,
    lineHeight: 41,
    fontWeight: '700' as const,
    letterSpacing: 0.37,
  },
  
  // Title 1
  title1: {
    fontSize: 28,
    lineHeight: 34,
    fontWeight: '700' as const,
    letterSpacing: 0.36,
  },
  
  // Title 2
  title2: {
    fontSize: 22,
    lineHeight: 28,
    fontWeight: '700' as const,
    letterSpacing: 0.35,
  },
  
  // Title 3
  title3: {
    fontSize: 20,
    lineHeight: 25,
    fontWeight: '600' as const,
    letterSpacing: 0.38,
  },
  
  // Headline
  headline: {
    fontSize: 17,
    lineHeight: 22,
    fontWeight: '600' as const,
    letterSpacing: -0.41,
  },
  
  // Body
  body: {
    fontSize: 17,
    lineHeight: 22,
    fontWeight: '400' as const,
    letterSpacing: -0.41,
  },
  
  // Callout
  callout: {
    fontSize: 16,
    lineHeight: 21,
    fontWeight: '400' as const,
    letterSpacing: -0.32,
  },
  
  // Subhead
  subhead: {
    fontSize: 15,
    lineHeight: 20,
    fontWeight: '400' as const,
    letterSpacing: -0.24,
  },
  
  // Footnote
  footnote: {
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '400' as const,
    letterSpacing: -0.08,
  },
  
  // Caption 1
  caption1: {
    fontSize: 12,
    lineHeight: 16,
    fontWeight: '400' as const,
    letterSpacing: 0,
  },
  
  // Caption 2
  caption2: {
    fontSize: 11,
    lineHeight: 13,
    fontWeight: '400' as const,
    letterSpacing: 0.06,
  },
};

/**
 * Colores del sistema iOS
 */
export const IOSColors = {
  // Colores principales
  systemBlue: '#007AFF',
  systemGreen: '#34C759',
  systemIndigo: '#5856D6',
  systemOrange: '#FF9500',
  systemPink: '#FF2D55',
  systemPurple: '#AF52DE',
  systemRed: '#FF3B30',
  systemTeal: '#5AC8FA',
  systemYellow: '#FFCC00',
  
  // Grises
  systemGray: '#8E8E93',
  systemGray2: '#AEAEB2',
  systemGray3: '#C7C7CC',
  systemGray4: '#D1D1D6',
  systemGray5: '#E5E5EA',
  systemGray6: '#F2F2F7',
  
  // Colores de fondo (Light Mode)
  light: {
    background: '#FFFFFF',
    secondaryBackground: '#F2F2F7',
    tertiaryBackground: '#FFFFFF',
    groupedBackground: '#F2F2F7',
    primaryText: '#000000',
    secondaryText: '#3C3C43',
    tertiaryText: '#3C3C43',
    separator: '#C6C6C8',
  },
  
  // Colores de fondo (Dark Mode)
  dark: {
    background: '#000000',
    secondaryBackground: '#1C1C1E',
    tertiaryBackground: '#2C2C2E',
    groupedBackground: '#000000',
    primaryText: '#FFFFFF',
    secondaryText: '#EBEBF5',
    tertiaryText: '#EBEBF5',
    separator: '#38383A',
  },
};

/**
 * Espaciado iOS
 */
export const Spacing = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  xxl: 48,
};

/**
 * Radios de borde iOS
 */
export const BorderRadius = {
  small: 8,
  medium: 12,
  large: 16,
  xlarge: 20,
  pill: 999,
};

/**
 * Sombras iOS
 */
export const Shadows = {
  small: {
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 1,
    },
    shadowOpacity: 0.18,
    shadowRadius: 1.0,
  },
  medium: {
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.23,
    shadowRadius: 2.62,
  },
  large: {
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.30,
    shadowRadius: 4.65,
  },
};

/**
 * Componentes comunes iOS
 */
export const IOSComponents = StyleSheet.create({
  // Botón primario estilo iOS
  primaryButton: {
    backgroundColor: IOSColors.systemBlue,
    paddingVertical: 18,
    paddingHorizontal: 24,
    borderRadius: BorderRadius.medium,
    alignItems: 'center',
    justifyContent: 'center',
  },
  
  primaryButtonText: {
    ...Typography.headline,
    color: '#FFFFFF',
  },
  
  // Botón secundario estilo iOS
  secondaryButton: {
    backgroundColor: 'transparent',
    paddingVertical: 18,
    paddingHorizontal: 24,
    borderRadius: BorderRadius.medium,
    borderWidth: 1,
    borderColor: IOSColors.systemBlue,
    alignItems: 'center',
    justifyContent: 'center',
  },
  
  secondaryButtonText: {
    ...Typography.headline,
    color: IOSColors.systemBlue,
  },
  
  // Input estilo iOS
  input: {
    ...Typography.body,
    borderWidth: 1,
    borderColor: IOSColors.systemGray4,
    borderRadius: BorderRadius.medium,
    paddingVertical: 16,
    paddingHorizontal: 16,
    backgroundColor: '#FFFFFF',
  },
  
  // Card estilo iOS
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: BorderRadius.large,
    padding: Spacing.lg,
    ...Shadows.medium,
  },
  
  // List Item estilo iOS
  listItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: Spacing.md,
    paddingHorizontal: Spacing.lg,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: IOSColors.light.separator,
  },
  
  // Navigation Bar estilo iOS
  navigationBar: {
    height: 44,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.md,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: IOSColors.light.separator,
  },
  
  navigationTitle: {
    ...Typography.headline,
  },
  
  // Tab Bar estilo iOS
  tabBar: {
    flexDirection: 'row',
    height: 50,
    backgroundColor: '#F8F8F8',
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: IOSColors.light.separator,
  },
});

/**
 * Animaciones iOS
 */
export const IOSAnimations = {
  // Duración estándar
  duration: {
    short: 200,
    medium: 300,
    long: 500,
  },
  
  // Curvas de animación iOS
  easing: {
    standard: 'ease-in-out',
    decelerate: 'ease-out',
    accelerate: 'ease-in',
  },
};

/**
 * Utilidades para iOS
 */
export const IOSUtils = {
  /**
   * Retorna estilos según el modo oscuro
   */
  getColorScheme: (isDark: boolean) => ({
    background: isDark ? IOSColors.dark.background : IOSColors.light.background,
    text: isDark ? IOSColors.dark.primaryText : IOSColors.light.primaryText,
    secondaryText: isDark ? IOSColors.dark.secondaryText : IOSColors.light.secondaryText,
    separator: isDark ? IOSColors.dark.separator : IOSColors.light.separator,
  }),
  
  /**
   * Verifica si es iOS
   */
  isIOS: Platform.OS === 'ios',
  
  /**
   * Estilos específicos de plataforma
   */
  platformSelect: <T,>(ios: T, android: T): T => 
    Platform.select({ ios, android }) as T,
};

export default {
  Typography,
  IOSColors,
  Spacing,
  BorderRadius,
  Shadows,
  IOSComponents,
  IOSAnimations,
  IOSUtils,
};
