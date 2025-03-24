import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Dimensions } from 'react-native';
import { Stack, useRouter } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import ScreenContainer from '@/components/ScreenContainer';
import { useDataSync } from '@/hooks/useDataSync';
import { useFilteredData } from '@/hooks/useFilteredData';
import InstitutionItem from '@/components/InstitutionItem';
import InstitutionSkeleton from '@/components/ui/skeletons/InstitutionSkeleton';

export default function InstitutionsFilterScreen() {
  const router = useRouter();
  const { institutions } = useDataSync();
  const { results, isLoading, error, applyFilters } = useFilteredData();
  const [activeFilter, setActiveFilter] = useState<string | null>(null);
  const [selectedOption, setSelectedOption] = useState<string | null>(null);
  const [dimensions, setDimensions] = useState(Dimensions.get('window'));
  
  // Filter options
  const filterOptions = [
    { id: 'educational', label: 'Educational Institutes', icon: 'school' },
    { id: 'social_centers', label: 'Social Centers', icon: 'people' },
    { id: 'parishes', label: 'Parishes', icon: 'church' },
    { id: 'diocese', label: 'Diocese', icon: 'location-city' },
  ];
  
  // Get all unique dioceses
  const dioceseOptions = [...new Set(institutions?.map(i => i.diocese).filter(Boolean))].sort() || [];
  
  const handleFilterSelect = (filterId: string) => {
    setActiveFilter(filterId);
    setSelectedOption(null);
    
    // For filters that don't need additional options
    if (['educational', 'social_centers', 'parishes'].includes(filterId)) {
      handleOptionSelect(filterId);
    }
  };
  
  const handleOptionSelect = async (option: string) => {
    setSelectedOption(option);
    
    // Apply filters based on selection
    if (activeFilter === 'diocese') {
      await applyFilters({
        category: 'province_institutions',
        subcategory: 'diocese',
        options: { diocese: option }
      });
    } else if (['educational', 'social_centers', 'parishes'].includes(activeFilter || '')) {
      await applyFilters({
        category: 'province_institutions',
        subcategory: activeFilter || '',
        options: {}
      });
    }
  };
  
  // Show options based on active filter
  const renderOptions = () => {
    if (!activeFilter) return null;
    
    if (activeFilter === 'diocese') {
      return (
        <View style={styles.optionsContainer}>
          <Text style={styles.optionsTitle}>Select Diocese</Text>
          <ScrollView style={styles.optionsList}>
            {dioceseOptions.map(diocese => (
              <TouchableOpacity
                key={diocese}
                style={[
                  styles.optionItem,
                  selectedOption === diocese && styles.selectedOption
                ]}
                onPress={() => handleOptionSelect(diocese)}
              >
                <Text style={styles.optionText}>{diocese}</Text>
                {selectedOption === diocese && (
                  <MaterialIcons name="check" size={18} color={Colors.primary} />
                )}
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
      );
    }
    
    // For other filters that don't need additional options
    return (
      <View style={styles.optionsContainer}>
        <Text style={styles.optionsTitle}>Filter Applied</Text>
        <Text style={styles.infoText}>
          Showing results for {filterOptions.find(f => f.id === activeFilter)?.label}
        </Text>
      </View>
    );
  };
  
  useEffect(() => {
    const subscription = Dimensions.addEventListener(
      'change',
      ({ window }) => {
        setDimensions(window);
      }
    );
    return () => subscription?.remove();
  }, []);
  
  return (
    <ScreenContainer>
      <Stack.Screen options={{ title: 'Filter Institutions' }} />
      
      <View style={[
        styles.container,
        (dimensions.width < 600 || dimensions.height > dimensions.width) && 
          styles.containerVertical
      ]}>
        <View style={styles.filterPanel}>
          <Text style={styles.panelTitle}>Filter By</Text>
          <ScrollView>
            {filterOptions.map(filter => (
              <TouchableOpacity
                key={filter.id}
                style={[
                  styles.filterItem,
                  activeFilter === filter.id && styles.activeFilterItem
                ]}
                onPress={() => handleFilterSelect(filter.id)}
              >
                <MaterialIcons 
                  name={filter.icon as any} 
                  size={20} 
                  color={activeFilter === filter.id ? Colors.primary : Colors.gray[600]} 
                />
                <Text style={[
                  styles.filterText,
                  activeFilter === filter.id && styles.activeFilterText
                ]}>
                  {filter.label}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
        
        <View style={styles.contentPanel}>
          {renderOptions()}
          
          <View style={styles.resultsContainer}>
            {isLoading ? (
              <View style={styles.loadingContainer}>
                <InstitutionSkeleton />
              </View>
            ) : error ? (
              <View style={styles.errorContainer}>
                <Text style={styles.errorText}>{error}</Text>
              </View>
            ) : results.length > 0 ? (
              <>
                <Text style={styles.resultsTitle}>
                  Results ({results.length})
                </Text>
                <ScrollView style={styles.resultsList}>
                  {results.map(institution => (
                    <InstitutionItem 
                      key={institution.id} 
                      institution={institution}
                      onPress={() => router.push(`/institution/${institution.id}`)}
                    />
                  ))}
                </ScrollView>
              </>
            ) : activeFilter ? (
              <View style={styles.emptyContainer}>
                <Text style={styles.emptyText}>No results found</Text>
              </View>
            ) : (
              <View style={styles.emptyContainer}>
                <Text style={styles.emptyText}>Select a filter to see results</Text>
              </View>
            )}
          </View>
        </View>
      </View>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    flexDirection: 'row',
  },
  containerVertical: {
    flexDirection: 'column',
  },
  filterPanel: {
    width: 250,
    borderRightWidth: 1,
    borderRightColor: Colors.gray[200],
    padding: 16,
  },
  contentPanel: {
    flex: 1,
    padding: 16,
  },
  panelTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 16,
    color: Colors.gray[800],
  },
  filterItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 8,
    marginBottom: 8,
  },
  activeFilterItem: {
    backgroundColor: Colors.primary + '10',
  },
  filterText: {
    marginLeft: 12,
    fontSize: 14,
    color: Colors.text,
  },
  activeFilterText: {
    color: Colors.primary,
    fontWeight: '500',
  },
  optionsContainer: {
    marginBottom: 24,
  },
  optionsTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
    color: Colors.gray[800],
  },
  optionsList: {
    maxHeight: 200,
  },
  optionItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderRadius: 8,
    backgroundColor: Colors.gray[100],
    marginBottom: 8,
  },
  selectedOption: {
    backgroundColor: Colors.primary + '10',
  },
  optionText: {
    fontSize: 14,
    color: Colors.text,
  },
  infoText: {
    fontSize: 14,
    color: Colors.gray[600],
    fontStyle: 'italic',
  },
  resultsContainer: {
    flex: 1,
    borderTopWidth: 1,
    borderTopColor: Colors.gray[200],
    paddingTop: 16,
  },
  resultsTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
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
