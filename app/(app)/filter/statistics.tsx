import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Dimensions } from 'react-native';
import { Stack, useRouter } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import ScreenContainer from '@/components/ScreenContainer';
import { useDataSync } from '@/hooks/useDataSync';
import { useFilteredData } from '@/hooks/useFilteredData';
import StatisticCard from '@/components/StatisticCard';
import StatisticSkeleton from '@/components/ui/skeletons/StatisticSkeleton';

export default function StatisticsFilterScreen() {
  const router = useRouter();
  const { results, isLoading, error, applyFilters } = useFilteredData();
  const [activeFilter, setActiveFilter] = useState<string | null>(null);
  const [dimensions, setDimensions] = useState(Dimensions.get('window'));
  
  // Filter options
  const filterOptions = [
    { id: 'age_distribution', label: 'Age Distribution', icon: 'bar-chart' },
    { id: 'formation_stages', label: 'Formation Stages', icon: 'school' },
    { id: 'geographical', label: 'Geographical Distribution', icon: 'public' },
    { id: 'ministry_types', label: 'Ministry Types', icon: 'work' },
    { id: 'yearly_trends', label: 'Yearly Trends', icon: 'trending-up' },
  ];
  
  useEffect(() => {
    const subscription = Dimensions.addEventListener(
      'change',
      ({ window }) => {
        setDimensions(window);
      }
    );
    return () => subscription?.remove();
  }, []);
  
  const handleFilterSelect = async (filterId: string) => {
    setActiveFilter(filterId);
    
    // Apply filter
    await applyFilters({
      category: 'province_statistics',
      subcategory: filterId,
      options: {}
    });
  };
  
  return (
    <ScreenContainer>
      <Stack.Screen options={{ title: 'Province Statistics' }} />
      
      <View style={[
        styles.container,
        (dimensions.width < 600 || dimensions.height > dimensions.width) && 
          styles.containerVertical
      ]}>
        {/* Left panel - Filter options */}
        <View style={[
          styles.filterPanel,
          (dimensions.width < 600 || dimensions.height > dimensions.width) && 
            styles.filterPanelVertical
        ]}>
          <Text style={styles.filterPanelTitle}>View Statistics</Text>
          {filterOptions.map(option => (
            <TouchableOpacity
              key={option.id}
              style={[
                styles.filterOption,
                activeFilter === option.id && styles.activeFilterOption
              ]}
              onPress={() => handleFilterSelect(option.id)}
            >
              <MaterialIcons 
                name={option.icon} 
                size={24} 
                color={activeFilter === option.id ? Colors.white : Colors.gray[700]} 
              />
              <Text style={[
                styles.filterOptionText,
                activeFilter === option.id && styles.activeFilterOptionText
              ]}>
                {option.label}
              </Text>
            </TouchableOpacity>
          ))}
        </View>
        
        {/* Right panel - Results */}
        <View style={styles.resultsPanel}>
          {isLoading ? (
            <View style={styles.loadingContainer}>
              <StatisticSkeleton />
            </View>
          ) : error ? (
            <View style={styles.errorContainer}>
              <Text style={styles.errorText}>{error}</Text>
            </View>
          ) : !activeFilter ? (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>
                Select a category to view province statistics
              </Text>
            </View>
          ) : (
            <View style={styles.resultsContainer}>
              <Text style={styles.resultsTitle}>
                {filterOptions.find(f => f.id === activeFilter)?.label || ''}
              </Text>
              <ScrollView style={styles.resultsList}>
                {results.map((stat, index) => (
                  <StatisticCard
                    key={index}
                    statistic={stat}
                    type={activeFilter}
                  />
                ))}
              </ScrollView>
            </View>
          )}
        </View>
      </View>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    flexDirection: 'row',
    backgroundColor: Colors.background,
  },
  containerVertical: {
    flexDirection: 'column',
  },
  filterPanel: {
    width: 250,
    paddingHorizontal: 16,
    paddingVertical: 20,
    borderRightWidth: 1,
    borderRightColor: Colors.gray[200],
  },
  filterPanelVertical: {
    width: '100%',
    borderRightWidth: 0,
    borderBottomWidth: 1,
    borderBottomColor: Colors.gray[200],
    paddingBottom: 10,
  },
  filterPanelTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 16,
    color: Colors.gray[800],
  },
  filterOption: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 8,
    marginBottom: 8,
  },
  activeFilterOption: {
    backgroundColor: Colors.primary,
  },
  filterOptionText: {
    fontSize: 15,
    marginLeft: 12,
    color: Colors.gray[700],
  },
  activeFilterOptionText: {
    color: Colors.white,
    fontWeight: '500',
  },
  resultsPanel: {
    flex: 1,
    padding: 20,
  },
  resultsContainer: {
    flex: 1,
  },
  resultsTitle: {
    fontSize: 18,
    fontWeight: '600',
    marginBottom: 16,
    color: Colors.gray[800],
  },
  resultsList: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: Colors.gray[600],
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  errorText: {
    fontSize: 14,
    color: Colors.error,
    textAlign: 'center',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 14,
    color: Colors.gray[600],
    textAlign: 'center',
  },
});
