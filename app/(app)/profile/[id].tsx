import { useLocalSearchParams } from 'expo-router';
import { useAuth } from '@/hooks/useAuth';
import Profile from '@/components/Profile';
import { useJesuit } from '@/hooks/useDataUtils';
import ScreenContainer from '@/components/ScreenContainer';


export default function ProfilePage() {
  const { id } = useLocalSearchParams();
  const { currentJesuit } = useAuth();
  
  // Always call hooks at the top level, regardless of condition
  const { jesuit } = useJesuit(id === 'me' ? -1 : Number(id)); // Use -1 as a dummy ID for 'me'
  
  // Determine which data to display after hooks are called
  if (id === 'me') {
    if (!currentJesuit) return null;
    return <ScreenContainer><Profile jesuit={currentJesuit} currentJesuit={true} /></ScreenContainer>;
  }

  // For other jesuits
  if (!jesuit) return null;
  return <ScreenContainer><Profile jesuit={jesuit} /></ScreenContainer>;
}
