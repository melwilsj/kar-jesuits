import React from 'react';
import { ActivityIndicator, View, StyleSheet } from 'react-native';
import Colors from '@/constants/Colors';

interface LoadingProgressProps {
  size?: 'small' | 'large';
  color?: string;
}

export default function LoadingProgress({ 
  size = 'large', 
  color = Colors.primary 
}: LoadingProgressProps) {
  return (
    <View style={styles.container}>
      <ActivityIndicator size={size} color={color} />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
}); 