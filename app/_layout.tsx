import { Stack } from 'expo-router';
import { useEffect } from 'react';
import { useAuth, useAuthInit } from '../hooks/useAuth';
import LoadingSpinner from '../components/ui/LoadingSpinner';
import { SafeAreaProvider } from 'react-native-safe-area-context';

export default function RootLayout() {
  const { isLoading, isAuthenticated } = useAuth();
  
  // Initialize auth state
  useAuthInit();

  if (isLoading) {
    return <LoadingSpinner />;
  }

  return (
    <SafeAreaProvider>
      <Stack
        screenOptions={{
          headerShown: false,
          gestureEnabled: false,
          animation: 'none',
        }}
      >
        <Stack.Screen 
          name="(auth)"
          options={{
            headerShown: false,
          }}
          redirect={isAuthenticated}
        />
        <Stack.Screen 
          name="(app)"
          options={{
            headerShown: false,
          }}
          redirect={!isAuthenticated}
        />
      </Stack>
    </SafeAreaProvider>
  );
} 