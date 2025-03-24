import React from 'react';
import { View, StyleSheet } from 'react-native';
import Colors from '@/constants/Colors';

export default function StatisticSkeleton() {
  return (
    <View style={styles.container}>
      <View style={styles.labelSkeleton} />
      <View style={styles.valueSkeleton} />
      <View style={styles.percentageContainer}>
        <View style={styles.percentageBarSkeleton} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: Colors.white,
    borderRadius: 8,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  labelSkeleton: {
    height: 14,
    width: '70%',
    backgroundColor: Colors.gray[200],
    borderRadius: 4,
    marginBottom: 16,
  },
  valueSkeleton: {
    height: 24,
    width: '40%',
    backgroundColor: Colors.gray[200],
    borderRadius: 4,
    marginBottom: 16,
  },
  percentageContainer: {
    marginTop: 8,
  },
  percentageBarSkeleton: {
    height: 8,
    width: '60%',
    backgroundColor: Colors.gray[200],
    borderRadius: 4,
  },
}); 