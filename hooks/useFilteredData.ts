import { useState, useEffect, useCallback } from 'react';
import { useAuth } from './useAuth';
import { dataAPI } from '@/services/api';
import { DataStorage } from '@/services/storage';
import { FilterState } from '@/types/filter';
import { Community, Jesuit, PaginatedData } from '@/types/api';

export interface PaginationInfo {
  currentPage: number;
  lastPage: number;
  total: number;
  hasNextPage: boolean;
  hasPrevPage: boolean;
}

export const useFilteredData = () => {
  const { user } = useAuth();
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [results, setResults] = useState<any[]>([]);
  const [pagination, setPagination] = useState<PaginationInfo | null>(null);
  const [currentFilters, setCurrentFilters] = useState<FilterState | null>(null);
  const [cachedResponses, setCachedResponses] = useState<Record<string, any>>({});
  
  const getCacheKey = (filters: FilterState, page = 1) => {
    return `${filters.category}_${filters.subcategory}_${JSON.stringify(filters.options)}_page${page}`;
  };

  const applyFilters = useCallback(async (filters: FilterState, page = 1) => {
    if (!user) return;
    
    setIsLoading(true);
    setError(null);
    setCurrentFilters(filters);
    
    try {
      const cacheKey = getCacheKey(filters, page);
      
      // Check if we have cached data
      if (cachedResponses[cacheKey]) {
        const cachedData = cachedResponses[cacheKey];
        setResults(cachedData.data);
        setPagination(cachedData.pagination);
        setIsLoading(false);
        return;
      }
      
      let filteredResults: any[] = [];
      let paginationData: PaginationInfo | null = null;
      
      // Determine if we need to fetch new data or can filter locally
      const canFilterLocally = needsLocalFiltering(filters);
      
      if (canFilterLocally) {
        filteredResults = await filterLocalData(filters);
        // Local filtering doesn't have pagination
        paginationData = {
          currentPage: 1,
          lastPage: 1,
          total: filteredResults.length,
          hasNextPage: false,
          hasPrevPage: false
        };
      } else {
        // Fetch data from API with filters
        const response = await fetchFilteredData(filters, page);
        
        if (response.data && 'data' in response.data) {
          // Handle paginated response
          const paginatedData = response.data.data as PaginatedData<any>;
          filteredResults = paginatedData.data;
          paginationData = {
            currentPage: paginatedData.current_page,
            lastPage: paginatedData.last_page,
            total: paginatedData.total,
            hasNextPage: paginatedData.next_page_url !== null,
            hasPrevPage: paginatedData.prev_page_url !== null
          };
          
          // Cache the response
          setCachedResponses(prev => ({
            ...prev,
            [cacheKey]: {
              data: filteredResults,
              pagination: paginationData
            }
          }));
        } else if (Array.isArray(response.data)) {
          // Handle regular array response
          filteredResults = response.data;
          paginationData = {
            currentPage: 1, 
            lastPage: 1,
            total: filteredResults.length,
            hasNextPage: false,
            hasPrevPage: false
          };
        }
      }
      
      setResults(filteredResults);
      setPagination(paginationData);
    } catch (error) {
      console.error('Filter error:', error);
      setError('Error applying filters');
    } finally {
      setIsLoading(false);
    }
  }, [user, cachedResponses]);
  
  const loadNextPage = useCallback(async () => {
    if (!pagination?.hasNextPage || !currentFilters) return;
    
    const nextPage = pagination.currentPage + 1;
    await applyFilters(currentFilters, nextPage);
  }, [pagination, currentFilters, applyFilters]);
  
  const loadPrevPage = useCallback(async () => {
    if (!pagination?.hasPrevPage || !currentFilters) return;
    
    const prevPage = pagination.currentPage - 1;
    await applyFilters(currentFilters, prevPage);
  }, [pagination, currentFilters, applyFilters]);
  
  // Clear cache when user changes
  useEffect(() => {
    setCachedResponses({});
  }, [user]);
  
  const needsLocalFiltering = (filters: FilterState): boolean => {
    // Determine if we already have this data locally
    const { category, subcategory } = filters;
    
    // For Jesuits and Communities that are already cached
    if (category === 'province_jesuits' && 
        ['location', 'birthdays'].includes(subcategory || '')) {
      return true;
    }
    
    if (category === 'province_communities' && 
        subcategory === 'diocese') {
      return true;
    }
    
    // All other filters require API calls
    return false;
  };
  
  const filterLocalData = async (filters: FilterState) => {
    const { category, subcategory, options } = filters;
    
    // Load data from storage
    if (category === 'province_jesuits') {
      const jesuits = await DataStorage.getJesuits();
      const communities = await DataStorage.getCommunities();
      const members = jesuits?.members || jesuits?.data?.members || [];
      
      if (subcategory === 'location') {
        // Calculate matching communities once outside the filter loop
        const matchingCommunities = communities.filter((c: Community) => c.diocese === options.diocese);
        const matchingCommunityIds = new Set(matchingCommunities.map((c: Community) => c.id));
        
        return members.filter((m: Jesuit) => 
          matchingCommunityIds.has(m.current_community_id)
        );
      }
      
      if (subcategory === 'birthdays') {
        if (options.birthdayFilter?.type === 'month') {
          const month = options.birthdayFilter.month;
          return members.filter((m: Jesuit) => {
            if (!m.dob) return false;
            const dobDate = new Date(m.dob);
            return dobDate.getMonth() === month;
          });
        } else if (options.birthdayFilter?.type === 'range') {
          const { startDate, endDate } = options.birthdayFilter;
          return members.filter((m: Jesuit) => {
            if (!m.dob) return false;
            const dobDate = new Date(m.dob);
            // Compare month/day only, ignoring year
            const monthDay = new Date(0, dobDate.getMonth(), dobDate.getDate());
            const start = new Date(0, startDate?.getMonth() || 0, startDate?.getDate() || 0);
            const end = new Date(0, endDate?.getMonth() || 0, endDate?.getDate() || 0);
            return monthDay >= start && monthDay <= end;
          });
        }
      }
    }
    
    if (category === 'province_communities' && subcategory === 'diocese') {
      const storedCommunities = await DataStorage.getCommunities();
      return storedCommunities.filter((c: Community) => c.diocese === options.diocese);
    }
    
    return [];
  };
  
  const fetchFilteredData = async (filters: FilterState, page = 1) => {
    const { category, subcategory } = filters;
    
    if (!category) return { data: [] };
    
    // Jesuits filtering with pagination
    if (category === 'province_jesuits') {
      switch (subcategory) {
        case 'formation':
          return await dataAPI.fetchJesuitsInFormation(page);
        case 'common_houses':
          return await dataAPI.fetchJesuitsInCommonHouses(page);
        case 'other_provinces':
          return await dataAPI.fetchJesuitsInOtherProvinces(page);
        case 'outside_india':
          return await dataAPI.fetchJesuitsOutsideIndia(page);
        case 'other_residing':
          return await dataAPI.fetchOtherProvinceJesuitsResiding(page);
        default:
          return { data: [] };
      }
    }
    
    // Institutions filtering
    if (category === 'province_institutions') {
      switch (subcategory) {
        case 'educational':
          return await dataAPI.fetchEducationalInstitutions();
        case 'social_centers':
          return await dataAPI.fetchSocialCenters();
        case 'parishes':
          return await dataAPI.fetchParishes();
        default:
          return { data: [] };
      }
    }
    
    // Commission filtering
    if (category === 'province_commissions') {
      if (subcategory === null || subcategory === 'all') {
        // Fetch all commissions
        return await dataAPI.fetchAllCommissions();
      } else {
        // Fetch by commission type
        return await dataAPI.fetchCommissionsByType(subcategory);
      }
    }
    
    // Statistics filtering
    if (category === 'province_statistics') {
      switch (subcategory) {
        case 'age_distribution':
          return await dataAPI.fetchAgeDistributionStats();
        case 'formation_stages':
          return await dataAPI.fetchFormationStats();
        case 'geographical':
          return await dataAPI.fetchGeographicalStats();
        case 'ministry_types':
          return await dataAPI.fetchMinistryStats();
        case 'yearly_trends':
          return await dataAPI.fetchYearlyTrendsStats();
        default:
          return { data: [] };
      }
    }
    
    return { data: [] };
  };
  
  const fetchFilteredResults = async (filters: FilterState) => {
    if (!user) return [];
    
    try {
      return await applyFilters(filters);
    } catch (error) {
      console.error('Failed to fetch filtered results:', error);
      return [];
    }
  };
  
  const applyRemoteFilter = async (filters: FilterState, page = 1) => {
    try {
      let response: any;
      
      // Fetch data based on filters
      const apiResponse = await fetchFilteredData(filters, page);
      
      // Handle the new response format with pagination
      if (apiResponse.success === true && apiResponse.data) {
        // New format: success with paginated data
        if (apiResponse.data.data) {
          // This is a paginated response
          const paginatedData = apiResponse.data;
          
          // Set pagination info
          setPagination({
            currentPage: paginatedData.current_page,
            lastPage: paginatedData.last_page,
            total: paginatedData.total,
            hasNextPage: paginatedData.current_page < paginatedData.last_page,
            hasPrevPage: paginatedData.current_page > 1
          });
          
          // Cache the results
          setCachedResponses(prev => ({
            ...prev,
            [getCacheKey(filters, page)]: {
              data: paginatedData.data,
              pagination: {
                currentPage: paginatedData.current_page,
                lastPage: paginatedData.last_page,
                total: paginatedData.total,
                hasNextPage: paginatedData.current_page < paginatedData.last_page,
                hasPrevPage: paginatedData.current_page > 1
              },
              timestamp: Date.now()
            }
          }));
          
          // Save to persistent storage
          await DataStorage.savePaginatedResults(getCacheKey(filters, page), {
            data: paginatedData.data,
            pagination: {
              currentPage: paginatedData.current_page,
              lastPage: paginatedData.last_page,
              total: paginatedData.total,
              hasNextPage: paginatedData.current_page < paginatedData.last_page,
              hasPrevPage: paginatedData.current_page > 1
            }
          });
          
          return paginatedData.data;
        }
        
        // Old format (non-paginated)
        return apiResponse.data;
      } else if (apiResponse.data) {
        // Just return the data directly
        return apiResponse.data;
      }
      
      return [];
    } catch (error) {
      console.error('Failed to apply remote filter:', error);
      setError('Failed to fetch data. Please try again.');
      return [];
    }
  };
  
  return {
    results,
    isLoading,
    error,
    pagination,
    applyFilters,
    loadNextPage,
    loadPrevPage,
    clearCache: () => setCachedResponses({}),
    fetchFilteredResults
  };
}; 