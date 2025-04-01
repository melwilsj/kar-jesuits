import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Dimensions } from 'react-native';
import { Stack, useRouter } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';
import ScreenContainer from '@/components/ScreenContainer';
import { useDataSync } from '@/hooks/useDataSync';
import { useFilteredData } from '@/hooks/useFilteredData';
import CommunityItem from '@/components/CommunityItem';
import CommunitySkeleton from '@/components/ui/skeletons/CommunitySkeleton';


export default function CommunitiesFilterScreen() {
  const router = useRouter();
  const { communities } = useDataSync();
  const { results, isLoading, error, applyFilters } = useFilteredData();
  const [activeFilter, setActiveFilter] = useState<string | null>(null);
  const [selectedOption, setSelectedOption] = useState<string | null>(null);
  const [dimensions, setDimensions] = useState(Dimensions.get('window'));
  const colorScheme = useColorScheme();
  // Filter options
  const filterOptions = [
    { id: 'diocese', label: 'Diocese', icon: 'church' },
    { id: 'region', label: 'Region', icon: 'location-on' },
    { id: 'type', label: 'Community Type', icon: 'category' },
  ];
  
  // Get all unique dioceses for diocese filter
  const dioceseOptions = [...new Set(communities.map(c => c.diocese).filter(Boolean))].sort();
  
  // Get all unique regions
  const regionOptions = [...new Set(communities.map(c => c.region).filter(Boolean))].sort();
  
  // Community types
  const typeOptions = [
    'Formation House',
    'Parish',
    'School/College',
    'Social Center',
    'Retreat House',
    'Provincial House',
    'Other'
  ];
  
  const handleFilterSelect = (filterId: string) => {
    setActiveFilter(filterId);
    setSelectedOption(null);
  };
  
  const handleOptionSelect = async (option: string) => {
    setSelectedOption(option);
    
    // Apply filters based on selection
    if (activeFilter === 'diocese') {
      await applyFilters({
        category: 'province_communities',
        subcategory: 'diocese',
        options: { diocese: option }
      });
    } else if (activeFilter === 'region') {
      await applyFilters({
        category: 'province_communities',
        subcategory: 'region',
        options: { region: option }
      });
    } else if (activeFilter === 'type') {
      await applyFilters({
        category: 'province_communities',
        subcategory: 'type',
        options: { type: option }
      });
    }
  };
  
  // Show options based on active filter
  const renderOptions = () => {
    if (!activeFilter) return null;
    
    let options: string[] = [];
    let title = '';
    
    if (activeFilter === 'diocese') {
      options = dioceseOptions;
      title = 'Select Diocese';
    } else if (activeFilter === 'region') {
      options = regionOptions.map(r => r?.code || '');
      title = 'Select Region';
    } else if (activeFilter === 'type') {
      options = typeOptions;
      title = 'Select Community Type';
    }
    
    return (
      <View style={styles.optionsContainer}>
        <Text style={styles.optionsTitle}>{title}</Text>
        <ScrollView style={styles.optionsList}>
          {options.map(option => (
            <TouchableOpacity
              key={option}
              style={[
                styles.optionItem,
                selectedOption === option && styles.selectedOption
              ]}
              onPress={() => handleOptionSelect(option)}
            >
              <Text style={styles.optionText}>{option}</Text>
              {selectedOption === option && (
                <MaterialIcons name="check" size={18} color={Colors[`${colorScheme}`].primary} />
              )}
            </TouchableOpacity>
          ))}
        </ScrollView>
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
      <Stack.Screen options={{ title: 'Filter Communities' }} />
      
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
                  color={activeFilter === filter.id ? Colors[`${colorScheme}`].primary : Colors[`${colorScheme}`].gray600} 
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
                <CommunitySkeleton />
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
                  {results.map(community => (
                    <CommunityItem 
                      key={community.id} 
                      community={community}
                      onPress={() => router.push(`/community/${community.id}`)}
                    />
                  ))}
                </ScrollView>
              </>
            ) : activeFilter && selectedOption ? (
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
    borderRightColor: Color.gray[200],
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
    color: Color.gray[800],
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
    backgroundColor: Color.primary + '10',
  },
  filterText: {
    marginLeft: 12,
    fontSize: 14,
    color: Color.text,
  },
  activeFilterText: {
    color: Color.primary,
    fontWeight: '500',
  },
  optionsContainer: {
    marginBottom: 24,
  },
  optionsTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
    color: Color.gray[800],
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
    backgroundColor: Color.gray[100],
    marginBottom: 8,
  },
  selectedOption: {
    backgroundColor: Color.primary + '10',
  },
  optionText: {
    fontSize: 14,
    color: Color.text,
  },
  infoText: {
    fontSize: 14,
    color: Color.gray[600],
    fontStyle: 'italic',
  },
  resultsContainer: {
    flex: 1,
    borderTopWidth: 1,
    borderTopColor: Color.gray[200],
    paddingTop: 16,
  },
  resultsTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
    color: Color.gray[800],
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
    color: Color.gray[600],
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  errorText: {
    fontSize: 14,
    color: Color.error,
    textAlign: 'center',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 14,
    color: Color.gray[600],
    textAlign: 'center',
  },
});
