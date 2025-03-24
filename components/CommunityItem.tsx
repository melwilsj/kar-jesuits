import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import { Community } from '@/types/api';

interface CommunityItemProps {
  community: Community;
  onPress: () => void;
}

export default function CommunityItem({ community, onPress }: CommunityItemProps) {
  return (
    <TouchableOpacity style={styles.container} onPress={onPress}>
      <View style={styles.iconContainer}>
        <MaterialIcons name="home" size={24} color={Colors.primary} />
      </View>
      <View style={styles.content}>
        <Text style={styles.name}>{community.name}</Text>
        <View style={styles.details}>
          {community.diocese && (
            <View style={styles.detailItem}>
              <MaterialIcons name="church" size={14} color={Colors.gray[500]} />
              <Text style={styles.detailText}>{community.diocese}</Text>
            </View>
          )}
          {community.region && (
            <View style={styles.detailItem}>
              <MaterialIcons name="place" size={14} color={Colors.gray[500]} />
              <Text style={styles.detailText}>{community.region.name}</Text>
            </View>
          )}
        </View>
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