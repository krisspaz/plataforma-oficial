import React from 'react';
import { Platform, StatusBar, StyleSheet, View } from 'react-native';
import { SafeAreaView as RNSafeAreaView } from 'react-native-safe-area-context';

interface SafeAreaWrapperProps {
  children: React.ReactNode;
  backgroundColor?: string;
}

/**
 * Wrapper para SafeArea optimizado para iOS
 * Maneja correctamente los notches y home indicators
 */
export const SafeAreaWrapper: React.FC<SafeAreaWrapperProps> = ({ 
  children, 
  backgroundColor = '#ffffff' 
}) => {
  return (
    <RNSafeAreaView 
      style={[styles.container, { backgroundColor }]}
      edges={['top', 'bottom']}
    >
      {Platform.OS === 'ios' && (
        <StatusBar 
          barStyle="dark-content" 
          backgroundColor={backgroundColor}
        />
      )}
      {children}
    </RNSafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});

export default SafeAreaWrapper;
