import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { FirebaseService } from '../services/firebase';
import { useEffect } from 'react';

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
  isLoading: boolean;
  isAuthenticated: boolean;
  setToken: (token: string | null) => void;
  setUser: (user: User | null) => void;
  setLoading: (loading: boolean) => void;
  logout: () => void;
}

export const useAuth = create<AuthState>()(
  persist(
    (set) => ({
      token: null,
      user: null,
      isLoading: true,
      isAuthenticated: false,
      setToken: (token) => 
        set({ token, isAuthenticated: !!token }),
      setUser: (user) => 
        set({ user }),
      setLoading: (loading) => 
        set({ isLoading: loading }),
      logout: async () => {
        await FirebaseService.signOut();
        set({ token: null, user: null, isAuthenticated: false });
      },
    }),
    {
      name: 'auth-storage',
      storage: createJSONStorage(() => AsyncStorage),
    }
  )
);

// Hook to initialize auth state
export const useAuthInit = () => {
  const { setToken, setUser, setLoading } = useAuth();

  useEffect(() => {
    const unsubscribe = FirebaseService.onAuthStateChanged(async (firebaseUser) => {
      setLoading(true);
      if (firebaseUser) {
        try {
          const token = await firebaseUser.getIdToken();
          setToken(token);
          // You might want to fetch user details from your backend here
          // and call setUser with the response
        } catch (error) {
          console.error('Error getting user token:', error);
          setToken(null);
          setUser(null);
        }
      } else {
        setToken(null);
        setUser(null);
      }
      setLoading(false);
    });

    return () => unsubscribe();
  }, []);
}; 