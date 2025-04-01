import React, { useState } from 'react';
import { View, Text, StyleSheet, SafeAreaView, ScrollView, TouchableOpacity } from 'react-native';
import { Stack, useRouter } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import ScreenContainer from '@/components/ScreenContainer';
import { useColorScheme } from '@/hooks/useSettings';

type CategoryId = 'jesuits' | 'communities' | 'institutions' | 'commissions' | 'houses' | 'statistics';

export default function FilterScreen() {
  const colorScheme = useColorScheme();
  const router = useRouter();
  
  const categories = [
    {
      id: 'jesuits',
      title: 'Province Jesuits',
      icon: 'people',
      description: 'Filter members by location, birthdays, formation, etc.'
    },
    {
      id: 'communities',
      title: 'Province Communities',
      icon: 'home',
      description: 'Filter communities by diocese and other criteria'
    },
    {
      id: 'institutions',
      title: 'Province Institutions',
      icon: 'business',
      description: 'View educational institutes, social centers, parishes'
    },
    {
      id: 'commissions',
      title: 'Commissions',
      icon: 'groups',
      description: 'Filter by various commissions'
    },
    {
      id: 'houses',
      title: 'Directory of Houses',
      icon: 'domain',
      description: 'Filter by Assistancy, Provinces, Regions'
    },
    {
      id: 'statistics',
      title: 'Province Statistics',
      icon: 'bar-chart',
      description: 'View statistical information about the province'
    }
  ];
  
  const navigateToFilter = (categoryId: CategoryId) => {
    router.push(`/(app)/filter/${categoryId}`);
  };
  
  return (
    <ScreenContainer>
      <Stack.Screen options={{ title: 'Filter' }} />
      
      <ScrollView style={[styles.container, { backgroundColor: Colors[`${colorScheme}`].background }]}>
        <Text style={[styles.heading, { color: Colors[`${colorScheme}`].text }]}>Choose a Category</Text>
        
        {categories.map(category => (
          <TouchableOpacity
            key={category.id}
            style={styles.categoryCard}
            onPress={() => navigateToFilter(category.id as CategoryId)}
          >
            <View style={[styles.iconContainer, { backgroundColor: Colors[`${colorScheme}`].gray100 }]}>
              <MaterialIcons name={category.icon as any} size={24} color={Colors[`${colorScheme}`].primary} />
            </View>
            <View style={styles.categoryContent}>
              <Text style={styles.categoryTitle}>{category.title}</Text>
              <Text style={styles.categoryDescription}>{category.description}</Text>
            </View>
            <MaterialIcons name="chevron-right" size={24} color={Colors[`${colorScheme}`].gray400} />
          </TouchableOpacity>
        ))}
      </ScrollView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  heading: {
    fontSize: 18,
    fontWeight: '600',
    marginVertical: 16,
    marginHorizontal: 16,
  },
  categoryCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Color.white,
    borderRadius: 8,
    marginHorizontal: 16,
    marginBottom: 12,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  iconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  categoryContent: {
    flex: 1,
  },
  categoryTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: Color.text,
    marginBottom: 4,
  },
  categoryDescription: {
    fontSize: 14,
    color: Color.gray[600],
  },
}); 