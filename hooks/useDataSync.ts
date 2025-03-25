import { useState, useEffect } from 'react';
import { useAuth } from './useAuth';
import { dataAPI } from '../services/api';
import { DataStorage } from '../services/storage';
import { CommunitiesResponse, ProvinceJesuitsResponse, InstitutionsResponse, Event } from '../types/api';
import { Commission } from '../types/api';

export const useDataSync = () => {
  const { user, currentJesuit } = useAuth();
  const [isLoading, setIsLoading] = useState(false);
  const [progress, setProgress] = useState(0);
  const [error, setError] = useState<string | null>(null);
  const [provinceData, setProvinceData] = useState<ProvinceJesuitsResponse['data'] | null>(null);
  const [communities, setCommunities] = useState<CommunitiesResponse['data']>([]);
  const [institutions, setInstitutions] = useState<InstitutionsResponse['data']>([]);
  const [commissions, setCommissions] = useState<Commission[]>([]);
  const [events, setEvents] = useState<Event[]>([]);
  
  const syncData = async (force = false, background = false) => {
    if (!user) {
      // Clear local data on logout
      setProvinceData(null);
      setCommunities([]);
      setEvents([]);
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
        setProgress(10);
        
        // Set up progress simulation
        let progressInterval: NodeJS.Timeout | null = null;
        progressInterval = setInterval(() => {
          setProgress(prev => {
            // Cap progress at 80% until requests complete
            return prev < 80 ? prev + 1 : prev;
          });
        }, 2000);
        
        try {
          // Make API requests in parallel, including events
          const [jesuitResponse, communitiesResponse, institutionsResponse, eventsResponse] = await Promise.all([
            dataAPI.fetchJesuits(),
            dataAPI.fetchCommunities(),
            dataAPI.fetchInstitutions(),
            dataAPI.fetchUpcomingEvents()
          ]);
          
          // Clear interval and set progress to 80% when requests complete
          if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
          }
          setProgress(80);

          // Save all data in parallel
          await Promise.all([
            DataStorage.saveJesuits(jesuitResponse.data),
            DataStorage.saveCommunities(communitiesResponse.data.data),
            DataStorage.saveInstitutions(institutionsResponse.data),
            DataStorage.saveEvents(eventsResponse.data),
            DataStorage.updateLastSync(),
            fetchCommissions()
          ]);
          
          setProgress(100);
          setProvinceData(jesuitResponse.data.data);
          setCommunities(communitiesResponse.data.data);
          setInstitutions(institutionsResponse.data);
          setEvents(eventsResponse.data);
        } catch (error) {
          // Make sure to clear the interval if there's an error
          if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
          }
          throw error; // Re-throw to be caught by the outer try/catch
        }
      } else {
        // Only load data if we haven't already
        if (!provinceData || communities.length === 0 || events.length === 0) {
          const [storedJesuits, storedCommunities, storedInstitutions, storedEvents] = await Promise.all([
            DataStorage.getJesuits(),
            DataStorage.getCommunities(),
            DataStorage.getInstitutions(),
            DataStorage.getEvents()
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
          setInstitutions(storedInstitutions || []);
          setEvents(storedEvents || []);
        }
      }
    } catch (error) {
      console.error('Sync error:', error);
      if (!background) setError('Failed to sync data');
    } finally {
      if (!background) setIsLoading(false);
    }
  };

  const fetchCommissions = async () => {
    try {
      const commissionsResponse = await dataAPI.fetchAllCommissions();
      if (commissionsResponse?.data) {
        setCommissions(commissionsResponse.data);
      }
    } catch (error) {
      console.error('Error fetching commissions:', error);
    }
  };

  useEffect(() => {
    if (user) {
      syncData();
    } else {
      // Clear data when user logs out
      setProvinceData(null);
      setCommunities([]);
      setInstitutions([]);
      setEvents([]);
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
    institutions: institutions || [],
    events: events || [],
    currentJesuit,
    commissions
  };
}; 