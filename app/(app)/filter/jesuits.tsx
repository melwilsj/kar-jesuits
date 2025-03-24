import React, { useState, useEffect, useMemo } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Dimensions } from 'react-native';
import { Stack, useRouter } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import ScreenContainer from '@/components/ScreenContainer';
import { useDataSync } from '@/hooks/useDataSync';
import { useFilteredData } from '@/hooks/useFilteredData';
import JesuitItem from '@/components/JesuitItem';
import JesuitSkeleton from '@/components/ui/skeletons/JesuitSkeleton';

export default function JesuitsFilterScreen() {
  const router = useRouter();
  const { members = [], communities = [] } = useDataSync() || {};
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
    { id: 'location', label: 'Location', icon: 'place' },
    { id: 'birthdays', label: 'Birthdays', icon: 'cake' },
    { id: 'formation', label: 'In Formation', icon: 'school' },
    { id: 'common_houses', label: 'Province Members in Common Houses', icon: 'home' },
    { id: 'other_provinces', label: 'Province Members in Other Provinces', icon: 'swap-horiz' },
    { id: 'outside_india', label: 'Province Members Outside India', icon: 'public' },
    { id: 'other_residing', label: 'Members of Other Province Residing', icon: 'person-pin' },
  ];
  
  // Get all unique communities for location filter
  const locationOptions = useMemo(() => {
    return (communities || [])
      .map(c => c?.name)
      .filter(Boolean)
      .sort();
  }, [communities]);
  
  // Month options for birthdays filter
  const monthOptions = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  
  // Get all unique dioceses for location filter
  const dioceseOptions = useMemo(() => {
    const dioceses = (communities || [])
      .map(c => c?.diocese)
      .filter(Boolean);
    return [...new Set(dioceses)].sort();
  }, [communities]);
  
  // Handle filter selection
  const handleFilterSelect = (filterId: string) => {
    // Clear results when changing filters
    if (activeFilter !== filterId) {
      setSelectedOption(null);
    }
    setActiveFilter(filterId);
  };
  
  // Handle option selection
  const handleOptionSelect = async (option: string) => {
    // If same option clicked again, don't reapply filter
    if (option === selectedOption) return;
    
    setSelectedOption(option);
    
    if (activeFilter === 'location') {
      await applyFilters({
        category: 'province_jesuits',
        subcategory: 'location',
        options: { 
          diocese: option 
        }
      });
    } else if (activeFilter === 'birthdays') {
      // Get month index (0-11)
      const monthIndex = monthOptions.indexOf(option);
      if (monthIndex !== -1) {
        await applyFilters({
          category: 'province_jesuits',
          subcategory: 'birthdays',
          options: { 
            birthdayFilter: {
              type: 'month',
              month: monthIndex
            }
          }
        });
      }
    } else if (activeFilter) {
      // For filters that need API calls
      await applyFilters({
        category: 'province_jesuits',
        subcategory: activeFilter,
        options: {}
      });
    }
  };
  
  // Show options based on active filter
  const renderOptions = () => {
    if (!activeFilter) return null;
    
    if (activeFilter === 'location') {
      return (
        <View style={styles.optionsContainer}>
          <Text style={styles.optionsTitle}>Select Diocese</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.horizontalOptionsList}>
            {dioceseOptions.map(diocese => (
              <TouchableOpacity
                key={diocese}
                style={[
                  styles.optionChip,
                  selectedOption === diocese && styles.selectedOptionChip
                ]}
                onPress={() => handleOptionSelect(diocese)}
              >
                <Text style={[
                  styles.optionChipText,
                  selectedOption === diocese && styles.selectedOptionChipText
                ]}>
                  {diocese}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
      );
    }
    
    if (activeFilter === 'birthdays') {
      return (
        <View style={styles.optionsContainer}>
          <Text style={styles.optionsTitle}>Select Month</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.horizontalOptionsList}>
            {monthOptions.map(month => (
              <TouchableOpacity
                key={month}
                style={[
                  styles.optionChip,
                  selectedOption === month && styles.selectedOptionChip
                ]}
                onPress={() => handleOptionSelect(month)}
              >
                <Text style={[
                  styles.optionChipText,
                  selectedOption === month && styles.selectedOptionChipText
                ]}>
                  {month}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
      );
    }
    
    // For other filters that don't need additional options
    return (
      <View style={styles.optionsContainer}>
        <Text style={styles.infoText}>
          Showing results for {filterOptions.find(f => f.id === activeFilter)?.label}
        </Text>
      </View>
    );
  };
  
  // Effect to automatically apply filter for options that don't need sub-selections
  useEffect(() => {
    if (activeFilter && 
        !['location', 'birthdays'].includes(activeFilter)) {
      handleOptionSelect(activeFilter);
    }
  }, [activeFilter]);
  
  useEffect(() => {
    const subscription = Dimensions.addEventListener(
      'change',
      ({ window }) => {
        setDimensions(window);
      }
    );
    return () => subscription?.remove();
  }, []);

  
  // Render the pagination controls separately to improve readability
  const renderPaginationControls = () => {
    if (!pagination || pagination.lastPage <= 1) {
      return null;
    }
    
    return (
      <View style={styles.paginationControls}>
        {pagination.hasPrevPage && (
          <TouchableOpacity 
            style={styles.paginationButton}
            onPress={loadPrevPage}
          >
            <MaterialIcons name="chevron-left" size={24} color={Colors.primary} />
            <Text style={styles.paginationButtonText}>Previous</Text>
          </TouchableOpacity>
        )}
        
        <Text style={styles.paginationInfo}>
          Page {pagination.currentPage} of {pagination.lastPage}
        </Text>
        
        {pagination.hasNextPage && (
          <TouchableOpacity 
            style={styles.paginationButton}
            onPress={loadNextPage}
          >
            <Text style={styles.paginationButtonText}>Next</Text>
            <MaterialIcons name="chevron-right" size={24} color={Colors.primary} />
          </TouchableOpacity>
        )}
      </View>
    );
  };

  
  return (
    <ScreenContainer>
      <Stack.Screen options={{ title: 'Filter Jesuits' }} />
      
      <View style={styles.container}>
        <View style={styles.filtersRow}>
          <Text style={styles.panelTitle}>Filter By</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false}>
            {filterOptions.map(filter => (
              <TouchableOpacity
                key={filter.id}
                style={[
                  styles.filterChip,
                  activeFilter === filter.id && styles.activeFilterChip
                ]}
                onPress={() => handleFilterSelect(filter.id)}
              >
                <MaterialIcons 
                  name={filter.icon as any} 
                  size={20} 
                  color={activeFilter === filter.id ? Colors.white : Colors.gray[600]} 
                />
                <Text style={[
                  styles.filterChipText,
                  activeFilter === filter.id && styles.activeFilterChipText
                ]}>
                  {filter.label}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
        
        {renderOptions()}
        
        
        <View style={styles.resultsContainer}>
          {isLoading ? (
            <View style={styles.loadingContainer}>
              {[...Array(5)].map((_, index) => (
                <JesuitSkeleton key={`skeleton-${index}`} />
              ))}
            </View>
          ) : error ? (
            <View style={styles.errorContainer}>
              <Text style={styles.errorText}>Error: {error}</Text>
            </View>
          ) : results && results.length > 0 ? (
            <>
              <Text style={styles.resultsTitle}>
                Results {pagination ? `(${results.length} of ${pagination.total})` : `(${results.length})`}
              </Text>
              <ScrollView style={styles.resultsList}>
                {results.map((jesuit, index) => (
                  jesuit && jesuit.id ? (
                    <JesuitItem 
                      key={`jesuit-${jesuit.id}-${index}`} 
                      jesuit={jesuit}
                      onPress={() => router.push(`/profile/${jesuit.id}`)}
                    />
                  ) : null
                ))}
                
                {renderPaginationControls()}
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
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 12,
  },
  filtersRow: {
    marginBottom: 16,
  },
  panelTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
    color: Colors.gray[800],
  },
  filterChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.gray[100],
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 20,
    marginRight: 8,
  },
  activeFilterChip: {
    backgroundColor: Colors.primary,
  },
  filterChipText: {
    marginLeft: 6,
    fontSize: 14,
    color: Colors.gray[800],
  },
  activeFilterChipText: {
    color: Colors.white,
    fontWeight: '500',
  },
  optionsContainer: {
    marginBottom: 16,
  },
  optionsTitle: {
    fontSize: 14,
    fontWeight: '600',
    marginBottom: 12,
    color: Colors.gray[800],
  },
  horizontalOptionsList: {
    flexDirection: 'row',
  },
  optionChip: {
    backgroundColor: Colors.gray[100],
    paddingVertical: 6,
    paddingHorizontal: 12,
    borderRadius: 16,
    marginRight: 8,
  },
  selectedOptionChip: {
    backgroundColor: Colors.primary[100],
    borderWidth: 1,
    borderColor: Colors.primary,
  },
  optionChipText: {
    fontSize: 14,
    color: Colors.gray[800],
  },
  selectedOptionChipText: {
    color: Colors.primary,
    fontWeight: '500',
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
    paddingTop: 12,
    marginTop: 8,
  },
  loadingContainer: {
    paddingTop: 16,
  },
  resultsList: {
    flex: 1,
  },
  resultsTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
    color: Colors.gray[800],
  },
  errorContainer: {
    padding: 16,
    alignItems: 'center',
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
  paginationControls: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 16,
    marginTop: 8,
    borderTopWidth: 1,
    borderTopColor: Colors.gray[200],
  },
  paginationButton: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 8,
  },
  paginationButtonText: {
    color: Colors.primary,
    fontWeight: '500',
    marginHorizontal: 4,
  },
  paginationInfo: {
    color: Colors.gray[600],
  },
  debugButton: {
    alignSelf: 'flex-end',
    backgroundColor: Colors.gray[100],
    padding: 6,
    borderRadius: 4,
    marginBottom: 8,
  },
  debugButtonText: {
    fontSize: 12,
    color: Colors.gray[700],
  },
}); 