import { Stack, SplashScreen } from 'expo-router';
import { useEffect } from 'react';
import { useAuth, useAuthInit, useAuthGuard } from '../hooks/useAuth';
import LoadingSpinner from '../components/ui/LoadingSpinner';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { FirebaseService } from '../services/firebase';

// Keep the splash screen visible while we fetch resources
SplashScreen.preventAutoHideAsync();

export default function RootLayout() {
  const { isLoading, isAuthenticated } = useAuth();
  
  useEffect(() => {
    const init = async () => {
      try {
        await FirebaseService.init();
      } catch (error) {
        console.error('Initialization error:', error);
      } finally {
        // Hide splash screen after initialization
        SplashScreen.hideAsync();
      }
    };
    
    init();
  }, []);

  // Initialize auth state
  useAuthInit();
  useAuthGuard();

  if (isLoading) {
    return <LoadingSpinner />;
  }

  return (
    <SafeAreaProvider>
      <Stack
        screenOptions={{
          headerShown: false,
          gestureEnabled: false,
        }}
      >
        {isAuthenticated ? (
          <Stack.Screen 
            name="(app)" 
            options={{
              headerShown: false,
            }} 
          />
        ) : (
          <Stack.Screen 
            name="(auth)" 
            options={{
              headerShown: false,
            }} 
          />
        )}
      </Stack>
    </SafeAreaProvider>
  );
} 