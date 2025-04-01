import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { Community } from '@/types/api';
import { useColorScheme } from '@/hooks/useSettings';

interface CommunityItemProps {
  community: Community;
  onPress: () => void;
}

export default function CommunityItem({ community, onPress }: CommunityItemProps) {
  const colorScheme = useColorScheme();
  return (
    <TouchableOpacity style={[styles.container, { backgroundColor: Colors[`${colorScheme}`].background }]} onPress={onPress}>
      <View style={[styles.iconContainer, { backgroundColor: Colors[`${colorScheme}`].background }]}>
        <MaterialIcons name="home" size={24} color={Colors[`${colorScheme}`].primary} />
      </View>
      <View style={styles.content}>
        <Text style={[styles.name, { color: Colors[`${colorScheme}`].text }]}>{community.name}</Text>
        <View style={styles.details}>
          {community.diocese && (
            <View style={styles.detailItem}>
              <MaterialIcons name="church" size={14} color={Colors[`${colorScheme}`].icon} />
              <Text style={styles.detailText}>{community.diocese}</Text>
            </View>
          )}
          {community.region && (
            <View style={styles.detailItem}>
              <MaterialIcons name="place" size={14} color={Colors[`${colorScheme}`].icon} />
              <Text style={styles.detailText}>{community.region.name}</Text>
            </View>
          )}
        </View>
      </View>
      <MaterialIcons name="chevron-right" size={20} color={Colors[`${colorScheme}`].icon} />
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
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
    color: Color.gray[600],
    marginLeft: 4,
  },
}); 