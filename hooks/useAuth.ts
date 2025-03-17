import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { FirebaseService } from '../services/firebase';
import { useEffect } from 'react';
import { usePathname, useRouter } from 'expo-router';
import { DataStorage } from '@/services/storage';
import { CurrentJesuit } from '@/types/api';

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
  logout: () => void;
  setAuthData: (data: { token: string; user: User; jesuit?: CurrentJesuit }) => void;
}

export const useAuth = create<AuthState>()(
  persist(
    (set) => ({
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
        isAuthenticated: true
      }),
      logout: async () => {
        try {
          await FirebaseService.signOut();
          await DataStorage.clearAll();
        } finally {
          set({ token: null, user: null, currentJesuit: null, isAuthenticated: false });
        }
      },
    }),
    {
      name: 'auth-storage',
      storage: createJSONStorage(() => AsyncStorage),
    }
  )
);

export const useAuthGuard = () => {
  const { isAuthenticated, isLoading } = useAuth();
  const pathname = usePathname();
  const router = useRouter();
  
  useEffect(() => {
    if (!isLoading) {
      const isAuthRoute = pathname.includes('(auth)');
      const isVerifyRoute = pathname.includes('verify');
      
      // Allow verify route even when not authenticated
      if (!isAuthenticated && !isAuthRoute && !isVerifyRoute) {
        router.replace('/(auth)/login');
      } else if (isAuthenticated && isAuthRoute) {
        setTimeout(() => {
          router.replace('/(app)/home');
        }, 0);
      }
    }
  }, [isAuthenticated, isLoading, pathname]);
};

export const useInitAuth = () => {
  const useDataSync = require('./useDataSync').useDataSync;
  const { setToken, setLoading, user } = useAuth();
  const { syncData } = useDataSync();
  const router = useRouter();
  const pathname = usePathname();

  // Firebase auth state listener
  useEffect(() => {
    const unsubscribe = FirebaseService.onAuthStateChanged(async (firebaseUser) => {
      try {
        if (!firebaseUser) {
          setToken(null);
        }
      } catch (error) {
        console.error('Auth state change error:', error);
        setToken(null);
      } finally {
        setLoading(false);
      }
    });

    return () => unsubscribe();
  }, []);

  // Background sync when authenticated
  useEffect(() => {
    if (user) {
      // Use setTimeout to run sync in the background after auth is confirmed
      setTimeout(() => {
        syncData(true, true).catch((err: any) => {
          console.error('Background sync failed:', err);
        });
      }, 500);
    }
  }, [user]);
}; 