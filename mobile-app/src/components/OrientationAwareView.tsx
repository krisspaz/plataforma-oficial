import React from 'react';
import { View, StyleSheet, ViewStyle } from 'react-native';
import { useOrientation, Orientation } from '../hooks/useOrientation';

interface OrientationAwareViewProps {
  children: React.ReactNode;
  portraitStyle?: ViewStyle;
  landscapeStyle?: ViewStyle;
  style?: ViewStyle;
}

/**
 * View que cambia sus estilos según la orientación
 * Útil para layouts que necesitan adaptarse a portrait/landscape
 */
export const OrientationAwareView: React.FC<OrientationAwareViewProps> = ({
  children,
  portraitStyle,
  landscapeStyle,
  style,
}) => {
  const orientation = useOrientation();

  return (
    <View
      style={[
        styles.container,
        style,
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

export default OrientationAwareView;
