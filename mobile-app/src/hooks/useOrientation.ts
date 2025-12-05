import { useState, useEffect } from 'react';
import { Dimensions } from 'react-native';

export type Orientation = 'portrait' | 'landscape';

/**
 * Hook para detectar la orientación del dispositivo
 * Se actualiza automáticamente cuando el dispositivo rota
 */
export const useOrientation = (): Orientation => {
  const [orientation, setOrientation] = useState<Orientation>(
    getOrientation()
  );

  function getOrientation(): Orientation {
    const { width, height } = Dimensions.get('window');
    return width > height ? 'landscape' : 'portrait';
  }

  useEffect(() => {
    const subscription = Dimensions.addEventListener('change', () => {
      setOrientation(getOrientation());
    });

    return () => subscription?.remove();
  }, []);

  return orientation;
};

export default useOrientation;
