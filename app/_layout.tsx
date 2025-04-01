import { Stack, SplashScreen } from 'expo-router';
import { useState, useEffect } from 'react';
import { useAuth, useInitAuth, useAuthGuard } from '../hooks/useAuth';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { FirebaseService } from '@/services/firebase';
import LoadingProgress from '@/components/ui/LoadingProgress';
import { useDataSync } from '@/hooks/useDataSync';
import { useFontSize } from '@/hooks/useSettings';
import { FontSizeProvider } from '@/context/FontSizeContext';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import * as Notifications from 'expo-notifications';

// Keep the splash screen visible while we fetch resources
SplashScreen.preventAutoHideAsync();

// Configure notification handling (optional but recommended)
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

export default function RootLayout() {
  const { isLoading: isSyncing, progress } = useDataSync();
  const fontSizeScale = useFontSize();
  const [firebaseInitialized, setFirebaseInitialized] = useState(false);
  const { isLoading: isAuthLoading, isAuthenticated } = useAuth();

  // Initialize Firebase and Auth listeners
  useEffect(() => {
    FirebaseService.init()
      .then(() => setFirebaseInitialized(true))
      .catch(console.error);
  }, []);

  useInitAuth();

  // Determine the overall loading state for the layout
  const isLoading = !firebaseInitialized || isAuthLoading;

  // Call useAuthGuard and pass the layout readiness state
  // It should only attempt navigation when isLoading is false
  useAuthGuard(!isLoading);

  useEffect(() => {
    if (!isLoading) {
      SplashScreen.hideAsync();
    }
  }, [isLoading]);

  // Show loading indicator while initializing or syncing
  if (isLoading || isSyncing) {
    return <LoadingProgress
      message={isSyncing ? "Syncing data..." : (isLoading ? "Initializing..." : "Loading...")} // More specific message
      progress={isSyncing ? progress : (isLoading ? 0 : undefined)} // Show progress only for sync
    />;
  }

  // Render the main navigator only when not loading
  return (
    <GestureHandlerRootView style={{ flex: 1 }}>
    <FontSizeProvider value={fontSizeScale}>
      <SafeAreaProvider>
        <Stack
          screenOptions={{
            headerShown: false,
            gestureEnabled: false, // Consider enabling gestures if appropriate
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
    </GestureHandlerRootView>
  );
} 