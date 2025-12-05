import { useState, useEffect } from 'react';
import { Dimensions, ScaledSize } from 'react-native';
import DeviceInfo from '../utils/deviceInfo';

interface ResponsiveDimensions {
  window: ScaledSize;
  screen: ScaledSize;
  isPortrait: boolean;
  isLandscape: boolean;
  isTablet: boolean;
  isSmall: boolean;
  isIPad: boolean;
  hasNotch: boolean;
  hasDynamicIsland: boolean;
}

/**
 * Hook para obtener dimensiones responsivas
 * Actualiza automáticamente cuando cambia el tamaño o rotación
 */
export const useResponsiveDimensions = (): ResponsiveDimensions => {
  const [dimensions, setDimensions] = useState({
    window: Dimensions.get('window'),
    screen: Dimensions.get('screen'),
  });

  useEffect(() => {
    const subscription = Dimensions.addEventListener(
      'change',
      ({ window, screen }) => {
        setDimensions({ window, screen });
        DeviceInfo.updateDimensions();
      }
    );

    return () => subscription?.remove();
  }, []);

  const isPortrait = dimensions.window.height > dimensions.window.width;
  const isLandscape = !isPortrait;
  const isTablet = DeviceInfo.isTablet();
  const isSmall = DeviceInfo.isSmallDevice();
  const isIPad = DeviceInfo.isIPad();
  const hasNotch = DeviceInfo.hasNotch();
  const hasDynamicIsland = DeviceInfo.hasDynamicIsland();

  return {
    window: dimensions.window,
    screen: dimensions.screen,
    isPortrait,
    isLandscape,
    isTablet,
    isSmall,
    isIPad,
    hasNotch,
    hasDynamicIsland,
  };
};

export default useResponsiveDimensions;
