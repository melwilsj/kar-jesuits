import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { Jesuit } from '@/types/api';
import { useColorScheme } from '@/hooks/useSettings';

interface JesuitItemProps {
  jesuit: Jesuit;
  onPress: () => void;
}

export default function JesuitItem({ jesuit, onPress }: JesuitItemProps) {
  const defaultImage = 'https://placehold.co/600x400.png';
  const colorScheme = useColorScheme();

  return (
    <TouchableOpacity style={[styles.container, { backgroundColor: Colors[`${colorScheme}`].background }]} onPress={onPress}>
      <Image 
        source={{ uri: jesuit.photo_url || defaultImage }} 
        style={styles.avatar} 
      />
      <View style={styles.content}>
        <Text style={[styles.name, { color: Colors[`${colorScheme}`].text }]}>{jesuit.name}</Text>
        {jesuit.code && (
          <Text style={[styles.code, { color: Colors[`${colorScheme}`].textSecondary }]}>{jesuit.code}</Text>
        )}
        <View style={styles.details}>
          {jesuit.current_community && (
            <View style={styles.detailItem}>
              <MaterialIcons name="home" size={14} color={Colors[`${colorScheme}`].icon} />
              <Text style={[styles.detailText, { color: Colors[`${colorScheme}`].textSecondary }]}>{jesuit.current_community}</Text>
            </View>
          )}
          {jesuit.region && (
            <View style={styles.detailItem}>
              <MaterialIcons name="location-city" size={14} color={Colors[`${colorScheme}`].icon} />
              <Text style={[styles.detailText, { color: Colors[`${colorScheme}`].textSecondary }]}>{jesuit.region}</Text>
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
  avatar: {
    width: 50,
    height: 50,
    borderRadius: 25,
    marginRight: 12,
  },
  content: {
    flex: 1,
  },
  name: {
    fontSize: 16,
    fontWeight: '500',
  },
  code: {
    fontSize: 13,
    marginBottom: 4,
  },
  details: {
    flexDirection: 'row',
    flexWrap: 'wrap',
  },
  detailItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 12,
    marginTop: 4,
  },
  detailText: {
    fontSize: 12,
    marginLeft: 4,
  },
}); 