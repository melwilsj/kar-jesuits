import AsyncStorage from '@react-native-async-storage/async-storage';
import { CommunitiesResponse, ProvinceJesuitsResponse, InstitutionsResponse } from '../types/api';

const STORAGE_KEYS = {
  JESUITS: 'jesuits_data',
  COMMUNITIES: 'communities_data',
  INSTITUTIONS: 'institutions_data',
  LAST_SYNC: 'last_sync_timestamp',
  PAGINATED_RESULTS: 'paginated_results',
};

export const DataStorage = {
  async saveJesuits(data: ProvinceJesuitsResponse['data']) {    
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.JESUITS, JSON.stringify(data));
    } catch (error) {
      console.error('Error saving jesuits:', error);
      throw error;
    }
  },

  async saveCommunities(data: CommunitiesResponse['data']) {
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.COMMUNITIES, JSON.stringify(data));
    } catch (error) {
      console.error('Error saving communities:', error);
    }
  },

  async saveInstitutions(data: InstitutionsResponse['data']) {
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.INSTITUTIONS, JSON.stringify(data));
    } catch (error) {
      console.error('Error saving institutions:', error);
    }
  },

  async getJesuits() {
    try {
      const data = await AsyncStorage.getItem(STORAGE_KEYS.JESUITS);
      return data ? JSON.parse(data) : null;
    } catch (error) {
      console.error('Error getting jesuits:', error);
      return null;
    }
  },

  async getCommunities() {
    try {
      const data = await AsyncStorage.getItem(STORAGE_KEYS.COMMUNITIES);
      return data ? JSON.parse(data) : null;
    } catch (error) {
      console.error('Error getting communities:', error);
      return null;
    }
  },

  async getInstitutions() {
    try {
      const data = await AsyncStorage.getItem(STORAGE_KEYS.INSTITUTIONS);
      return data ? JSON.parse(data).data as InstitutionsResponse['data'] : null;
    } catch (error) {
      console.error('Error getting institutions:', error);
      return null;
    }
  },

  async updateLastSync() {
    try {
      await AsyncStorage.setItem(STORAGE_KEYS.LAST_SYNC, Date.now().toString());
    } catch (error) {
      console.error('Error updating last sync:', error);
    }
  },

  async getLastSync() {
    try {
      const timestamp = await AsyncStorage.getItem(STORAGE_KEYS.LAST_SYNC);
      return timestamp ? parseInt(timestamp) : null;
    } catch (error) {
      console.error('Error getting last sync:', error);
      return null;
    }
  },

  async clearAll() {
    try {
      await Promise.all([
        AsyncStorage.removeItem(STORAGE_KEYS.JESUITS),
        AsyncStorage.removeItem(STORAGE_KEYS.COMMUNITIES),
        AsyncStorage.removeItem(STORAGE_KEYS.LAST_SYNC),
      ]);
    } catch (error) {
      console.error('Error clearing storage:', error);
    }
  },

  async savePaginatedResults(key: string, data: any) {
    try {
      // Get existing cached results
      const existingCache = await this.getPaginatedResults();
      const updatedCache = {
        ...existingCache,
        [key]: {
          data,
          timestamp: Date.now(),
        },
      };
      
      await AsyncStorage.setItem(
        STORAGE_KEYS.PAGINATED_RESULTS, 
        JSON.stringify(updatedCache)
      );
    } catch (error) {
      console.error('Error saving paginated results:', error);
    }
  },
  
  async getPaginatedResults() {
    try {
      const data = await AsyncStorage.getItem(STORAGE_KEYS.PAGINATED_RESULTS);
      return data ? JSON.parse(data) : {};
    } catch (error) {
      console.error('Error getting paginated results:', error);
      return {};
    }
  },
  
  async getPaginatedResultsByKey(key: string) {
    try {
      const allResults = await this.getPaginatedResults();
      return allResults[key] || null;
    } catch (error) {
      console.error('Error getting paginated results by key:', error);
      return null;
    }
  },
  
  async clearOldPaginatedResults(maxAgeInHours = 24) {
    try {
      const allResults = await this.getPaginatedResults();
      const now = Date.now();
      const maxAge = maxAgeInHours * 60 * 60 * 1000;
      
      const filteredResults = Object.entries(allResults).reduce((acc, [key, value]) => {
        if (now - (value as {timestamp: number}).timestamp < maxAge) {
          acc[key as keyof typeof acc] = value;
        }
        return acc;
      }, {} as Record<string, any>);
      
      await AsyncStorage.setItem(
        STORAGE_KEYS.PAGINATED_RESULTS, 
        JSON.stringify(filteredResults)
      );
    } catch (error) {
      console.error('Error clearing old paginated results:', error);
    }
  }
}; 