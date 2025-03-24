import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';

// Add this to your types/api.ts file if not already there
export interface Commission {
  id: number;
  name: string;
  type: string;
  coordinator?: string;
  description?: string;
  members?: number;
}

interface CommissionItemProps {
  commission: Commission;
  onPress: () => void;
}

export default function CommissionItem({ commission, onPress }: CommissionItemProps) {
  const getIconByType = () => {
    switch (commission.type) {
      case 'education':
        return 'school';
      case 'social':
        return 'people';
      case 'formation':
        return 'psychology';
      case 'pastoral':
        return 'church';
      default:
        return 'groups';
    }
  };

  return (
    <TouchableOpacity style={styles.container} onPress={onPress}>
      <View style={styles.iconContainer}>
        <MaterialIcons name={getIconByType()} size={24} color={Colors.primary} />
      </View>
      <View style={styles.content}>
        <Text style={styles.name}>{commission.name}</Text>
        {commission.coordinator && (
          <View style={styles.details}>
            <View style={styles.detailItem}>
              <MaterialIcons name="person" size={14} color={Colors.gray[500]} />
              <Text style={styles.detailText}>Coordinator: {commission.coordinator}</Text>
            </View>
          </View>
        )}
        {commission.members && (
          <View style={styles.detailItem}>
            <MaterialIcons name="groups" size={14} color={Colors.gray[500]} />
            <Text style={styles.detailText}>{commission.members} members</Text>
          </View>
        )}
      </View>
      <MaterialIcons name="chevron-right" size={20} color={Colors.gray[400]} />
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.white,
    borderRadius: 8,
    marginBottom: 10,
    padding: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 1,
    elevation: 1,
  },
  iconContainer: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: Colors.primary + '10',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  content: {
    flex: 1,
  },
  name: {
    fontSize: 16,
    fontWeight: '500',
    color: Colors.text,
  },
  details: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginTop: 4,
  },
  detailItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 12,
    marginTop: 4,
  },
  detailText: {
    fontSize: 12,
    color: Colors.gray[600],
    marginLeft: 4,
  },
}); 