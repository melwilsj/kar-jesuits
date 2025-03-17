import { useState, useEffect } from 'react';
import { useAuth } from './useAuth';
import { dataAPI } from '../services/api';
import { DataStorage } from '../services/storage';
import { CommunitiesResponse, ProvinceJesuitsResponse } from '../types/api';
import { Community } from '../types/api';

export const useDataSync = () => {
  const { user, currentJesuit } = useAuth();
  const [isLoading, setIsLoading] = useState(false);
  const [progress, setProgress] = useState(0);
  const [error, setError] = useState<string | null>(null);
  const [provinceData, setProvinceData] = useState<ProvinceJesuitsResponse['data'] | null>(null);
  const [communities, setCommunities] = useState<CommunitiesResponse['data']>([]);
  
  const syncData = async (force = false, background = false) => {
    if (!user) {
      // Clear local data on logout
      setProvinceData(null);
      setCommunities([]);
      return;
    }
    
    if (isLoading) return;

    try {
      // Only show loading indicator if not a background sync
      if (!background) setIsLoading(true);
      setProgress(0);
      
      const lastSync = await DataStorage.getLastSync();
      const shouldSync = force || !lastSync || Date.now() - lastSync > 24 * 60 * 60 * 1000;

      if (shouldSync) {
        setProgress(20);
        const jesuitResponse = await dataAPI.fetchJesuits();
        
        setProgress(50);
        const communitiesResponse = await dataAPI.fetchCommunities();
        
        setProgress(80);
        await Promise.all([
          DataStorage.saveJesuits(jesuitResponse.data),
          DataStorage.saveCommunities(communitiesResponse.data.data),
          DataStorage.updateLastSync(),
        ]);
        
        setProgress(100);
        setProvinceData(jesuitResponse.data.data);
        setCommunities(communitiesResponse.data.data);
      } else {
        // Only load data if we haven't already
        if (!provinceData || communities.length === 0) {
          const [storedJesuits, storedCommunities] = await Promise.all([
            DataStorage.getJesuits(),
            DataStorage.getCommunities()
          ]);
          
          // Ensure the data structure is correct
          if (storedJesuits && typeof storedJesuits === 'object') {
            // Check if it's already in the correct format
            if (storedJesuits.province && Array.isArray(storedJesuits.members)) {
              setProvinceData(storedJesuits);
            } else if (storedJesuits.data && storedJesuits.data.province) {
              setProvinceData(storedJesuits.data);
            } else {
              console.error('Invalid jesuits data format', storedJesuits);
              setError('Data format error');
            }
          } else {
            console.error('No jesuits data found');
            setError('No jesuits data found');
          }
          
          setCommunities(storedCommunities || []);
        }
      }
    } catch (error) {
      console.error('Sync error:', error);
      if (!background) setError('Failed to sync data');
    } finally {
      if (!background) setIsLoading(false);
    }
  };

  useEffect(() => {
    if (user) {
      syncData();
    } else {
      // Clear data when user logs out
      setProvinceData(null);
      setCommunities([]);
    }
  }, [user]);

  return {
    syncData,
    isLoading,
    progress,
    error,
    province: provinceData?.province,
    regions: provinceData?.regions || [],
    members: provinceData?.members || [],
    communities: communities || [],
    currentJesuit
  };
}; 