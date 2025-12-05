import React from 'react';
import { View, StyleSheet, ViewStyle } from 'react-native';
import { useResponsiveDimensions } from '../hooks/useResponsiveDimensions';

interface ResponsiveGridProps {
  children: React.ReactNode;
  spacing?: number;
  minColumnWidth?: number;
  style?: ViewStyle;
}

/**
 * Grid responsivo que ajusta automáticamente el número de columnas
 * según el ancho de la pantalla
 * 
 * En iPhone: 1-2 columnas
 * En iPad portrait: 2-3 columnas
 * En iPad landscape: 3-4 columnas
 */
export const ResponsiveGrid: React.FC<ResponsiveGridProps> = ({
  children,
  spacing = 16,
  minColumnWidth = 150,
  style,
}) => {
  const { window } = useResponsiveDimensions();
  
  // Calcular número de columnas según el ancho disponible
  const columns = Math.max(1, Math.floor(window.width / minColumnWidth));
  const columnWidth = (window.width - spacing * (columns + 1)) / columns;

  return (
    <View style={[styles.grid, { padding: spacing }, style]}>
      {React.Children.map(children, (child, index) => (
        <View
          key={index}
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

export default ResponsiveGrid;
