import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { FirebaseService } from '../services/firebase';
import { useEffect } from 'react';
import { usePathname, useRouter, useRootNavigationState } from 'expo-router';
import { DataStorage } from '@/services/storage';
import { CurrentJesuit } from '@/types/api';
import { authAPI } from '@/services/api';

interface User {
  id: number;
  name: string;
  email?: string;
  phone_number?: string;
  type: 'admin' | 'staff' | 'jesuit' | 'guest';
  is_active: boolean;
}

interface AuthState {
  token: string | null;
  user: User | null;
  currentJesuit: CurrentJesuit | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  setToken: (token: string | null) => void;
  setUser: (user: User | null) => void;
  setCurrentJesuit: (jesuit: CurrentJesuit | null) => void;
  setLoading: (loading: boolean) => void;
  logout: (performApiUnregister?: boolean) => void;
  setAuthData: (data: { token: string; user: User; jesuit?: CurrentJesuit }) => void;
}

export const useAuth = create<AuthState>()(
  persist(
    (set, get) => ({
      token: null,
      user: null,
      currentJesuit: null,
      isLoading: true,
      isAuthenticated: false,
      setToken: (token) =>
        set({ token, isAuthenticated: !!token }),
      setUser: (user) =>
        set({ user }),
      setCurrentJesuit: (jesuit) =>
        set({ currentJesuit: jesuit }),
      setLoading: (loading) =>
        set({ isLoading: loading }),
      setAuthData: (data) => set({
        token: data.token,
        user: data.user,
        currentJesuit: data.jesuit,
        isAuthenticated: true,
        isLoading: false,
      }),
      logout: async (performApiUnregister = true) => {
        const fcmToken = FirebaseService.getCurrentFcmToken();
        try {
          if (performApiUnregister && fcmToken && get().token) {
            await authAPI.unregisterFcmToken(fcmToken);
          }
          await FirebaseService.signOut();
          await DataStorage.clearAll();
        } catch (error) {
          console.error("Error during logout process:", error);
        } finally {
          set({ token: null, user: null, currentJesuit: null, isAuthenticated: false, isLoading: false });
        }
      },
    }),
    {
      name: 'auth-storage',
      storage: createJSONStorage(() => AsyncStorage),
    }
  )
);

export const useAuthGuard = (rootLayoutReady: boolean) => {
  const { isAuthenticated, isLoading: isAuthLoading } = useAuth();
  const router = useRouter();
  const pathname = usePathname();
  const navigationState = useRootNavigationState();

  useEffect(() => {
    if (isAuthLoading || !rootLayoutReady || !navigationState?.key) {
      return;
    }

    const isAuthGroup = pathname.startsWith('/login') || pathname.startsWith('/verify');

    if (!isAuthenticated && !isAuthGroup) {
      router.replace('/(auth)/login');
    } else if (isAuthenticated && isAuthGroup) {
      router.replace('/(app)/home');
    }
  }, [isAuthenticated, isAuthLoading, rootLayoutReady, pathname, router, navigationState?.key]);
};

export const useInitAuth = () => {
  const useDataSync = require('./useDataSync').useDataSync;
  const { setToken, setLoading, user, isAuthenticated, token: backendToken } = useAuth();
  const { syncData } = useDataSync();
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    const unsubscribe = FirebaseService.onAuthStateChanged(async (firebaseUser) => {
      try {
        if (!firebaseUser) {
          setToken(null);
          if (useAuth.getState().isAuthenticated) {
             console.log("Firebase user logged out, clearing local state.");
             await useAuth.getState().logout(false);
          } else {
             setLoading(false);
          }
        }
      } catch (error) {
        console.error('Auth state change error:', error);
        await useAuth.getState().logout(false);
      }
    });

    return () => unsubscribe();
  }, []);

  useEffect(() => {
    if (isAuthenticated && backendToken) {
      console.log("User authenticated with backend, attempting to register FCM token.");
      FirebaseService.getFcmToken().then(fcmToken => {
        if (fcmToken) {
          authAPI.registerFcmToken(fcmToken).catch(err => {
            console.error("Failed to register FCM token after login:", err);
          });
        } else {
            console.log("Could not get FCM token after login.");
        }
      }).catch(err => console.error("Error getting FCM token after login:", err));
    }
  }, [isAuthenticated, backendToken]);

  useEffect(() => {
    if (user) {
      setTimeout(() => {
        syncData(true, true).catch((err: any) => {
          console.error('Background sync failed:', err);
        });
      }, 500);
    }
  }, [user]);
}; 