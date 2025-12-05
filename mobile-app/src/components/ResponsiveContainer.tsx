import React from 'react';
import { View, StyleSheet, ViewStyle } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useResponsiveDimensions } from '../hooks/useResponsiveDimensions';

interface ResponsiveContainerProps {
  children: React.ReactNode;
  style?: ViewStyle;
  maxWidth?: number;
  centerContent?: boolean;
  padding?: number;
}

/**
 * Container responsivo que se adapta a todos los dispositivos
 * En iPad, centra el contenido con un maxWidth
 * En iPhone, usa el ancho completo
 */
export const ResponsiveContainer: React.FC<ResponsiveContainerProps> = ({
  children,
  style,
  maxWidth = 768,
  centerContent = true,
  padding = 16,
}) => {
  const { isTablet } = useResponsiveDimensions();

  return (
    <SafeAreaView style={[styles.safeArea, style]} edges={['top', 'bottom']}>
      <View
        style={[
          styles.container,
          { padding },
          isTablet && centerContent && {
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

export default ResponsiveContainer;
