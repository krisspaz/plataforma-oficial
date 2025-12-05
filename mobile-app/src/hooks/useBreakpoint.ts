import { useState, useEffect } from 'react';
import DeviceInfo, { Breakpoints } from '../utils/deviceInfo';
import { Dimensions } from 'react-native';

interface BreakpointState {
  isSmall: boolean;
  isMedium: boolean;
  isLarge: boolean;
  isTablet: boolean;
  isTabletLarge: boolean;
  isDesktop: boolean;
  currentBreakpoint: 'small' | 'medium' | 'large' | 'tablet' | 'tabletLarge' | 'desktop';
}

/**
 * Hook para manejar breakpoints responsive
 * Similar a media queries en CSS
 */
export const useBreakpoint = (): BreakpointState => {
  const [breakpoint, setBreakpoint] = useState<BreakpointState>(
    getBreakpoint()
  );

  function getBreakpoint(): BreakpointState {
    const width = DeviceInfo.width;

    const isDesktop = width >= Breakpoints.desktop;
    const isTabletLarge = width >= Breakpoints.tabletLarge && width < Breakpoints.desktop;
    const isTablet = width >= Breakpoints.tablet && width < Breakpoints.tabletLarge;
    const isLarge = width >= Breakpoints.large && width < Breakpoints.tablet;
    const isMedium = width >= Breakpoints.medium && width < Breakpoints.large;
    const isSmall = width < Breakpoints.medium;

    let currentBreakpoint: BreakpointState['currentBreakpoint'] = 'small';
    if (isDesktop) currentBreakpoint = 'desktop';
    else if (isTabletLarge) currentBreakpoint = 'tabletLarge';
    else if (isTablet) currentBreakpoint = 'tablet';
    else if (isLarge) currentBreakpoint = 'large';
    else if (isMedium) currentBreakpoint = 'medium';

    return {
      isSmall,
      isMedium,
      isLarge,
      isTablet,
      isTabletLarge,
      isDesktop,
      currentBreakpoint,
    };
  }

  useEffect(() => {
    const subscription = Dimensions.addEventListener('change', () => {
      DeviceInfo.updateDimensions();
      setBreakpoint(getBreakpoint());
    });

    return () => subscription?.remove();
  }, []);

  return breakpoint;
};

export default useBreakpoint;
