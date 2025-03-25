import React from 'react';
import { Text, TextProps } from 'react-native';
import { useAppFontSize } from '@/context/FontSizeContext';

interface ScaledTextProps extends TextProps {
  size?: number;
}

export default function ScaledText({ style, size = 16, ...props }: ScaledTextProps) {
  const fontScale = useAppFontSize();
  const scaledSize = size * fontScale;
  
  return (
    <Text style={[{ fontSize: scaledSize }, style]} {...props} />
  );
} 