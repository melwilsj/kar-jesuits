import { useState, useEffect } from 'react';
import { DataStorage } from '../services/storage';
import { Jesuit, Community, Institution, Event } from '../types/api';
import { useAuth } from './useAuth';
import { useDataSync } from './useDataSync';

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

// Hook for fetching a single Institution by ID
export const useInstitution = (id: number) => {
  const { user } = useAuth();
  const [institution, setInstitution] = useState<Institution | null>(null);
  const [institutionJesuits, setInstitutionJesuits] = useState<Jesuit[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Reset state when ID changes
    setInstitution(null);
    setInstitutionJesuits([]);
    setLoading(true);
    
    const loadInstitution = async () => {
      try {
        const institutions = await DataStorage.getInstitutions();
        const jesuits = await DataStorage.getJesuits();
        
        // Find the institution
        const foundInstitution = institutions?.find(
          (i: Institution) => i.id === id
        ) || null;
        
        setInstitution(foundInstitution);
        
        // Find jesuits associated with this institution if it exists
        if (foundInstitution && jesuits) {
          let jesuitsData = jesuits.members || jesuits.data?.members || [];
          
          // Find jesuits with roles in this institution
          const associatedJesuits = jesuitsData.filter(
            (jesuit: Jesuit) => jesuit.roles?.some(
              role => role.institution === foundInstitution.name
            )
          );
          
          setInstitutionJesuits(associatedJesuits);
        }
      } catch (error) {
        console.error('Error loading institution:', error);
      } finally {
        setLoading(false);
      }
    };

    if (user && id) {
      loadInstitution();
    } else {
      setLoading(false);
    }
  }, [id, user]);

  return { institution, institutionJesuits, loading };
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

export const getInstitutionById = async (id: number) => {
  try {
    const institutions = await DataStorage.getInstitutions();
    return institutions?.find((i: Institution) => i.id === id) || null;
  } catch (error) {
    console.error('Error fetching institution:', error);
    return null;
  }
};

export const getInstitutionsByType = async (type: string) => {
  try {
    const institutions = await DataStorage.getInstitutions();
    return institutions?.filter((i: Institution) => {
      switch (type) {
        case 'educational':
          return i.type === 'school' || i.type === 'college' || i.type === 'university' || i.type === 'hostel' || i.type === 'community_college' || i.type === 'iti';
        case 'social_center':
          return i.type === 'social_center' || i.type === 'ngo';
        case 'retreat_center':
          return i.type === 'retreat_center';
        case 'parish':
          return i.type === 'parish';
        default:
          return false;
      }
    });
  } catch (error) {
    console.error('Error fetching institutions by type:', error);
    return [];
  }
};

// Hook for fetching a single Event by ID
export const useEvent = (id: number) => {
  const { user } = useAuth();
  const [event, setEvent] = useState<Event | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Reset state when ID changes
    setEvent(null);
    setLoading(true);
    
    const loadEvent = async () => {
      try {
        const events = await DataStorage.getEvents();
        
        if (events && Array.isArray(events)) {
          const foundEvent = events.find((e: Event) => e.id === id);
          setEvent(foundEvent || null);
        } else {
          console.error('Invalid events data structure', events);
          setEvent(null);
        }
      } catch (error) {
        console.error('Error loading event:', error);
        setEvent(null);
      } finally {
        setLoading(false);
      }
    };

    if (user && id) {
      loadEvent();
    } else {
      setLoading(false);
    }
  }, [id, user]);

  return { event, loading };
};