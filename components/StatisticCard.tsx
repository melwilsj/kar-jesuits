import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import Colors from '@/constants/Colors';

// Define statistic data types
interface StatisticData {
  label: string;
  value: number | string;
  percentage?: number;
  color?: string;
}

interface StatisticCardProps {
  statistic: StatisticData;
  type: string;
}

export default function StatisticCard({ statistic, type }: StatisticCardProps) {
  // Generate background colors based on statistic type
  const getBackgroundColor = () => {
    if (statistic.color) return statistic.color + '20'; // Use provided color with opacity
    
    // Default colors based on type
    switch (type) {
      case 'age_distribution':
        return Colors.blue[100];
      case 'formation_stages':
        return Colors.green[100];
      case 'geographical':
        return Colors.purple[100];
      case 'ministry_types':
        return Colors.orange[100];
      case 'yearly_trends':
        return Colors.teal[100];
      default:
        return Colors.gray[100];
    }
  };

  return (
    <View style={[styles.container, { backgroundColor: getBackgroundColor() }]}>
      <Text style={styles.label}>{statistic.label}</Text>
      <Text style={styles.value}>{statistic.value}</Text>
      {statistic.percentage !== undefined && (
        <View style={styles.percentageContainer}>
          <View style={[styles.percentageBar, { width: `${statistic.percentage}%` }]} />
          <Text style={styles.percentageText}>{statistic.percentage}%</Text>
        </View>
      )}
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
  label: {
    fontSize: 14,
    color: Colors.gray[700],
    marginBottom: 8,
  },
  value: {
    fontSize: 24,
    fontWeight: '600',
    color: Colors.text,
    marginBottom: 8,
  },
  percentageContainer: {
    marginTop: 8,
  },
  percentageBar: {
    height: 8,
    backgroundColor: Colors.primary,
    borderRadius: 4,
    marginBottom: 4,
  },
  percentageText: {
    fontSize: 12,
    color: Colors.gray[600],
    textAlign: 'right',
  },
}); 