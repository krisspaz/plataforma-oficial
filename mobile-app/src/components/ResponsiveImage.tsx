import React from 'react';
import { Image, ImageStyle, StyleSheet, ImageSourcePropType } from 'react-native';
import { useResponsiveDimensions } from '../hooks/useResponsiveDimensions';

interface ResponsiveImageProps {
  source: ImageSourcePropType;
  aspectRatio?: number;
  maxWidth?: number;
  style?: ImageStyle;
  resizeMode?: 'cover' | 'contain' | 'stretch' | 'repeat' | 'center';
}

/**
 * Imagen que escala responsivamente según el dispositivo
 * Mantiene aspect ratio y se adapta al ancho de pantalla
 */
export const ResponsiveImage: React.FC<ResponsiveImageProps> = ({
  source,
  aspectRatio = 16 / 9,
  maxWidth,
  style,
  resizeMode = 'cover',
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
        style,
      ]}
      resizeMode={resizeMode}
    />
  );
};

const styles = StyleSheet.create({
  image: {
    borderRadius: 12,
  },
});

export default ResponsiveImage;
