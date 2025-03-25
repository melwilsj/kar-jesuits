import { Stack, SplashScreen } from 'expo-router';
import { useState, useEffect } from 'react';
import { useAuth, useInitAuth } from '../hooks/useAuth';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { FirebaseService } from '@/services/firebase';
import LoadingProgress from '@/components/ui/LoadingProgress';
import { useDataSync } from '@/hooks/useDataSync';
import { useFontSize } from '@/hooks/useSettings';
import { FontSizeProvider } from '@/context/FontSizeContext';

// Keep the splash screen visible while we fetch resources
SplashScreen.preventAutoHideAsync();

export default function RootLayout() {
  const { isLoading, isAuthenticated } = useAuth();
  const { isLoading: isSyncing, progress } = useDataSync();
  const [isInitialized, setIsInitialized] = useState(false);
  const fontSizeScale = useFontSize();
  
  useInitAuth();
  
  useEffect(() => {
    const init = async () => {
      try {
        await FirebaseService.init();
        setIsInitialized(true);
      } catch (error) {
        console.error('Initialization error:', error);
        setIsInitialized(true); // Still set initialized to prevent hanging
      } finally {
        SplashScreen.hideAsync().catch(console.error);
      }
    };
    
    init();
  }, []);

  if (!isInitialized || isLoading || isSyncing) {
    return <LoadingProgress 
      message={isSyncing ? "Syncing data..." : "Loading..."} 
      progress={isSyncing ? progress : 0} 
    />;
  }

  return (
    <FontSizeProvider value={fontSizeScale}>
      <SafeAreaProvider>
        <Stack
          screenOptions={{
            headerShown: false,
            gestureEnabled: false,
          }}
        >
          {isAuthenticated ? (
            <Stack.Screen name="(app)" />
          ) : (
            <Stack.Screen name="(auth)" />
          )}
        </Stack>
      </SafeAreaProvider>
    </FontSizeProvider>
  );
} 