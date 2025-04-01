import React, { useState, useEffect, useMemo } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Dimensions } from 'react-native';
import { Stack, useRouter } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';
import ScreenContainer from '@/components/ScreenContainer';
import { useDataSync } from '@/hooks/useDataSync';
import { useFilteredData } from '@/hooks/useFilteredData';
import InstitutionItem from '@/components/InstitutionItem';
import InstitutionSkeleton from '@/components/ui/skeletons/InstitutionSkeleton';

export default function InstitutionsFilterScreen() {
  const colorScheme = useColorScheme();
  const router = useRouter();
  const { communities } = useDataSync();
  const { 
    results = [], 
    isLoading = false, 
    error = null, 
    pagination = null, 
    applyFilters = async () => {}, 
    loadNextPage = async () => {}, 
    loadPrevPage = async () => {},
    clearCache = () => {}
  } = useFilteredData() || {};
  
  const [activeFilter, setActiveFilter] = useState<string | null>(null);
  const [selectedOption, setSelectedOption] = useState<string | null>(null);
  const [dimensions, setDimensions] = useState(Dimensions.get('window'));
  
  // Filter options
  const filterOptions = [
    { id: 'educational', label: 'Educational Institutes', icon: 'school' },
    { id: 'social_center', label: 'Social Centers', icon: 'people' },
    { id: 'retreat_center', label: 'Retreat Centers', icon: 'location-city' },
    { id: 'parish', label: 'Parishes', icon: 'church' },
    { id: 'diocese', label: 'Diocese', icon: 'location-city' },
  ];
  
  // Get all unique dioceses
  const dioceseOptions = useMemo(() => {
    return [...new Set(communities?.map(i => i.diocese).filter(Boolean))].sort() || [];
  }, [communities]);
  
  // Handle window dimension changes
  useEffect(() => {
    const subscription = Dimensions.addEventListener('change', ({ window }) => {
      setDimensions(window);
    });
    return () => subscription?.remove();
  }, []);
  
  // This effect will apply filters when activeFilter changes for direct filter types
  useEffect(() => {
    // For filters that don't need additional options, apply immediately
    const applyDirectFilter = async () => {
      if (!activeFilter) return;
      
      if (activeFilter === 'educational') {
        await applyFilters({
          category: 'province_institutions',
          subcategory: 'educational',
          options: { types: ['school', 'college', 'university', 'hostel', 'community_college', 'iti'] }
        });
      } else if (activeFilter === 'social_center') {
        await applyFilters({
          category: 'province_institutions',
          subcategory: 'social_center',
          options: { types: ['social_center', 'ngo'] }
        });
      } else if (activeFilter === 'retreat_center') {
        await applyFilters({
          category: 'province_institutions',
          subcategory: 'retreat_center',
          options: { types: ['retreat_center'] }
        });
      } else if (activeFilter === 'parish') {
        await applyFilters({
          category: 'province_institutions',
          subcategory: 'parish',
          options: { types: ['parish'] }
        });
      }
      // Don't handle diocese here - it needs a selected option first
    };
    
    if (['educational', 'social_center', 'retreat_center', 'parish'].includes(activeFilter || '')) {
      applyDirectFilter();
    }
  }, [activeFilter, applyFilters]);
  
  // This effect will apply filters when selectedOption changes for diocese filter
  useEffect(() => {
    const applyDioceseFilter = async () => {
      if (activeFilter === 'diocese' && selectedOption) {
        await applyFilters({
          category: 'province_institutions',
          subcategory: 'diocese',
          options: { diocese: selectedOption }
        });
      }
    };
    
    if (activeFilter === 'diocese' && selectedOption) {
      applyDioceseFilter();
    }
  }, [activeFilter, selectedOption, applyFilters]);
  
  const handleFilterSelect = (filterId: string) => {
    // If selecting a new filter, clear selected option
    if (activeFilter !== filterId) {
      setSelectedOption(null);
    }
    
    // Set the active filter
    setActiveFilter(filterId);
    
    // The useEffect hooks will handle applying the filters based on the updated state
  };
  
  const handleDioceseSelect = (diocese: string) => {
    // Only update if it's different from current selection
    if (diocese !== selectedOption) {
      setSelectedOption(diocese);
      // The useEffect hook will handle applying the filter
    }
  };
  
  // Calculate filter chips width based on screen size
  const filterItemWidth = useMemo(() => {
    const paddings = 24; // Total horizontal padding
    const spacing = 8; // Spacing between items
    const columns = dimensions.width > 600 ? 3 : 2; // Use 3 columns on larger screens
    return (dimensions.width - paddings - (spacing * (columns - 1))) / columns;
  }, [dimensions.width]);
  
  return (
    <ScreenContainer>
      <Stack.Screen 
        options={{ 
          title: 'Institutions', 
          headerBackTitle: 'Home'
        }} 
      />
      
      <View style={styles.container}>
        {/* Filter Section */}
        <View style={styles.filterSection}>
          <Text style={styles.sectionTitle}>
            Choose a category
          </Text>
          
          <View style={styles.filterOptionsContainer}>
            {filterOptions.map(option => (
              <TouchableOpacity
                key={option.id}
                style={[
                  styles.filterChip,
                  activeFilter === option.id && styles.selectedFilterChip,
                  { width: filterItemWidth }
                ]}
                onPress={() => handleFilterSelect(option.id)}
              >
                <MaterialIcons
                  name={option.icon as any}
                  size={22}
                  color={activeFilter === option.id ? Colors[`${colorScheme}`].primary : Colors[`${colorScheme}`].secondary}
                />
                <Text 
                  style={[
                    styles.filterChipText,
                    activeFilter === option.id && styles.selectedFilterChipText
                  ]}
                  numberOfLines={1}
                >
                  {option.label}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
          
          {/* Secondary Options */}
          {activeFilter === 'diocese' && (
            <>
              <Text style={[styles.sectionTitle, { marginTop: 16 }]}>
                Select a Diocese
              </Text>
              
              <ScrollView 
                horizontal 
                showsHorizontalScrollIndicator={false}
                contentContainerStyle={styles.optionChipsContainer}
              >
                {dioceseOptions.length > 0 ? (
                  dioceseOptions.map(diocese => (
                    <TouchableOpacity
                      key={diocese}
                      style={[
                        styles.optionChip,
                        selectedOption === diocese && styles.selectedOptionChip
                      ]}
                      onPress={() => handleDioceseSelect(diocese)}
                    >
                      <Text 
                        style={[
                          styles.optionChipText,
                          selectedOption === diocese && styles.selectedOptionChipText
                        ]}
                      >
                        {diocese}
                      </Text>
                    </TouchableOpacity>
                  ))
                ) : (
                  <Text style={styles.infoText}>No dioceses available</Text>
                )}
              </ScrollView>
            </>
          )}
        </View>
        
        {/* Results Section */}
        <View style={styles.resultsContainer}>
          {isLoading ? (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color={Colors[`${colorScheme}`].primary} />
              <Text style={styles.loadingText}>Loading institutions...</Text>
            </View>
          ) : error ? (
            <View style={styles.errorContainer}>
              <MaterialIcons name="error-outline" size={32} color={Colors[`${colorScheme}`].error} />
              <Text style={[
                styles.errorText,
                { color: Colors[`${colorScheme}`].text }
              ]}>{error}</Text>
            </View>
          ) : results.length > 0 ? (
            <>
              <Text style={[
                styles.resultsTitle,
                { color: Colors[`${colorScheme}`].text }
              ]}>
                Found {results.length} {results.length === 1 ? 'institution' : 'institutions'}
              </Text>
              
              <ScrollView style={styles.resultsList}>
                {results.map((institution, index) => (
                  <InstitutionItem
                    key={`institution-${institution.id}-${index}`}
                    institution={institution}
                    onPress={() => router.push(`/institution/${institution.id}`)}
                  />
                ))}
              </ScrollView>
            </>
          ) : activeFilter ? (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>No institutions found</Text>
            </View>
          ) : (
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>Select a category to view institutions</Text>
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
    padding: 12,
  },
  filterSection: {
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
    color: Color.gray[800],
  },
  filterOptionsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    gap: 8,
  },
  filterChip: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 8,
    backgroundColor: Color.gray[100],
    marginBottom: 8,
  },
  selectedFilterChip: {
    backgroundColor: Color.primary + '15',
    borderWidth: 1,
    borderColor: Color.primary + '40',
  },
  filterChipText: {
    fontSize: 14,
    marginLeft: 8,
    color: Color.gray[800],
  },
  selectedFilterChipText: {
    color: Color.primary,
    fontWeight: '500',
  },
  optionChipsContainer: {
    paddingVertical: 8,
    gap: 8,
  },
  optionChip: {
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 16,
    backgroundColor: Color.gray[100],
    marginRight: 8,
  },
  selectedOptionChip: {
    backgroundColor: Color.primary + '15',
    borderWidth: 1,
    borderColor: Color.primary + '40',
  },
  optionChipText: {
    fontSize: 14,
    color: Color.gray[800],
  },
  selectedOptionChipText: {
    color: Color.primary,
    fontWeight: '500',
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
    paddingTop: 12,
    marginTop: 8,
  },
  loadingContainer: {
    paddingTop: 16,
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: Color.gray[600],
  },
  resultsList: {
    flex: 1,
  },
  resultsTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
    color: Color.gray[800],
  },
  errorContainer: {
    padding: 16,
    alignItems: 'center',
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
  paginationControls: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 16,
    marginTop: 8,
    borderTopWidth: 1,
    borderTopColor: Color.gray[200],
  },
  paginationButton: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 8,
  },
  paginationButtonText: {
    color: Color.primary,
    fontWeight: '500',
    marginHorizontal: 4,
  },
  paginationInfo: {
    color: Color.gray[600],
  },
});
