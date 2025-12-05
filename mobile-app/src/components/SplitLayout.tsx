import React from 'react';
import { View, StyleSheet } from 'react-native';
import { useResponsiveDimensions } from '../hooks/useResponsiveDimensions';

interface SplitLayoutProps {
  sidebar: React.ReactNode;
  content: React.ReactNode;
  sidebarWidth?: number | string;
  showSidebarInPortrait?: boolean;
}

/**
 * Layout de dos paneles para iPad
 * 
 * - iPad landscape: Muestra sidebar + content lado a lado
 * - iPad portrait: Solo content (o ambos si showSidebarInPortrait=true)
 * - iPhone: Solo content
 */
export const SplitLayout: React.FC<SplitLayoutProps> = ({
  sidebar,
  content,
  sidebarWidth = 300,
  showSidebarInPortrait = false,
}) => {
  const { isTablet, isLandscape } = useResponsiveDimensions();

  // Mostrar sidebar si:
  // - Es tablet Y landscape
  // - O es tablet Y portrait Y showSidebarInPortrait=true
  const showSidebar = isTablet && (isLandscape || showSidebarInPortrait);

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

  // Solo content en iPhone o iPad portrait (sin sidebar)
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
    backgroundColor: '#f8f8f8',
  },
  content: {
    flex: 1,
  },
  fullContent: {
    flex: 1,
  },
});

export default SplitLayout;
