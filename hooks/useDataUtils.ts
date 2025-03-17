import { useState, useEffect } from 'react';
import { DataStorage } from '../services/storage';
import { Jesuit, Community } from '../types/api';
import { useAuth } from './useAuth';

// Hook for fetching a single Jesuit by ID
export const useJesuit = (id: number) => {
  const { user } = useAuth();
  const [jesuit, setJesuit] = useState<Jesuit | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Reset state when ID changes
    setJesuit(null);
    setLoading(true);
    
    const loadJesuit = async () => {
      try {
        const jesuits = await DataStorage.getJesuits();
        
        if (jesuits?.members) {            
          // Direct structure
          const found = jesuits.members.find((j: Jesuit) => j.id === id);
          setJesuit(found || null);
        } else if (jesuits?.data?.members) {
          // Nested structure
          const found = jesuits.data.members.find((j: Jesuit) => j.id === id);
          setJesuit(found || null);
        } else {
          console.error('Invalid jesuits data structure', jesuits);
          setJesuit(null);
        }
      } catch (error) {
        console.error('Error loading jesuit:', error);
        setJesuit(null);
      } finally {
        setLoading(false);
      }
    };

    if (user && id) {
      loadJesuit();
    } else {
      setLoading(false);
    }
  }, [id, user]);

  return { jesuit, loading };
};

// Hook for fetching a single Community by ID
export const useCommunity = (id: number) => {
  const { user } = useAuth();
  const [community, setCommunity] = useState<Community | null>(null);
  const [communityMembers, setCommunityMembers] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Reset state when ID changes
    setCommunity(null);
    setCommunityMembers([]);
    setLoading(true);
    
    const loadCommunity = async () => {
      try {
        const communities = await DataStorage.getCommunities();
        const jesuits = await DataStorage.getJesuits();
        
        // Find the community
        const foundCommunity = communities?.find(
          (c: Community) => c.id === id
        ) || null;
        
        setCommunity(foundCommunity);
        
        // Find members of this community if community exists
        if (foundCommunity) {
          if (jesuits?.members) {
            const membersInCommunity = jesuits.members.filter(
              (member: Jesuit) => member.current_community === foundCommunity.name
            );
            setCommunityMembers(membersInCommunity);
          } else if (jesuits?.data?.members) {
            const membersInCommunity = jesuits.data.members.filter(
              (member: Jesuit) => member.current_community === foundCommunity.name
            );
            setCommunityMembers(membersInCommunity);
          }
        }
      } catch (error) {
        console.error('Error loading community:', error);
      } finally {
        setLoading(false);
      }
    };

    if (user && id) {
      loadCommunity();
    } else {
      setLoading(false);
    }
  }, [id, user]);

  return { community, communityMembers, loading };
};

// Utility functions for direct data access
export const getJesuitById = async (id: number) => {
  try {
    const jesuits = await DataStorage.getJesuits();
    
    // Handle different possible data structures
    if (jesuits?.members) {
      return jesuits.members.find((j: Jesuit) => j.id === id) || null;
    } else if (jesuits?.data?.members) {
      return jesuits.data.members.find((j: Jesuit) => j.id === id) || null;
    }
    
    return null;
  } catch (error) {
    console.error('Error fetching jesuit:', error);
    return null;
  }
};

export const getCommunityById = async (id: number) => {
  try {
    const communities = await DataStorage.getCommunities();
    return communities?.find((c: Community) => c.id === id) || null;
  } catch (error) {
    console.error('Error fetching community:', error);
    return null;
  }
};