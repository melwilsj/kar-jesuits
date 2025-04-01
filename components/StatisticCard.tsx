import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import {Color} from '@/constants/Colors';

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
  // Generate background Color based on statistic type
  const getBackgroundColor = () => {
    if (statistic.color) return statistic.color + '20'; // Use provided color with opacity
    
    // Default Color based on type
    switch (type) {
      case 'age_distribution':
        return Color.blue[500];
      case 'formation_stages':
        return Color.green[500];
      case 'geographical':
        return Color.purple[500];
      case 'ministry_types':
        return Color.orange[500];
      case 'yearly_trends':
        return Color.teal[500];
      default:
        return Color.gray[500];
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
    backgroundColor: Color.white,
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
    color: Color.gray[700],
    marginBottom: 8,
  },
  value: {
    fontSize: 24,
    fontWeight: '600',
    color: Color.text,
    marginBottom: 8,
  },
  percentageContainer: {
    marginTop: 8,
  },
  percentageBar: {
    height: 8,
    backgroundColor: Color.primary,
    borderRadius: 4,
    marginBottom: 4,
  },
  percentageText: {
    fontSize: 12,
    color: Color.gray[600],
    textAlign: 'right',
  },
}); 