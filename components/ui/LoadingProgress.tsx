import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import Colors from '@/constants/Colors';

interface LoadingProgressProps {
  message?: string;
  progress?: number;
}

export default function LoadingProgress({ 
  message = 'Loading...', 
  progress = 0 
}: LoadingProgressProps) {
  return (
    <View style={styles.container}>
      <View style={styles.progressContainer}>
        <View 
          style={[
            styles.progressBar, 
            { width: (progress / 100) * styles.progressContainer.width }
          ]} 
        />
      </View>
      <Text style={styles.message}>{message}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: Colors.background,
  },
  progressContainer: {
    width: 300,
    height: 4,
    backgroundColor: Colors.gray[200],
    borderRadius: 2,
    overflow: 'hidden',
    marginBottom: 16,
  },
  progressBar: {
    height: '100%',
    backgroundColor: Colors.primary,
  },
  message: {
    color: Colors.text,
    fontSize: 16,
    marginTop: 8,
  },
});
