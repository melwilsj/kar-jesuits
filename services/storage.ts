import AsyncStorage from '@react-native-async-storage/async-storage';
import { CommunitiesResponse, ProvinceJesuitsResponse } from '../types/api';

const STORAGE_KEYS = {
  JESUITS: 'jesuits_data',
  COMMUNITIES: 'communities_data',
  LAST_SYNC: 'last_sync_timestamp',
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
  }
}; 