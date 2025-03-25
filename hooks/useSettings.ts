import { create } from 'zustand';
import { createJSONStorage, persist } from 'zustand/middleware';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useColorScheme as useDeviceColorScheme } from 'react-native';
import { useEffect } from 'react';

type ThemeMode = 'system' | 'light' | 'dark';
type FontSize = 'small' | 'medium' | 'large';
type SyncFrequency = 'manual' | 'daily' | 'always';

interface SettingsState {
  themeMode: ThemeMode;
  fontSize: FontSize;
  syncFrequency: SyncFrequency;
  showNotifications: boolean;
  
  // Actions
  setThemeMode: (mode: ThemeMode) => void;
  setFontSize: (size: FontSize) => void;
  setSyncFrequency: (frequency: SyncFrequency) => void;
  setShowNotifications: (show: boolean) => void;
}

// Create the store with persistence
export const useSettingsStore = create<SettingsState>()(
  persist(
    (set) => ({
      // Default values
      themeMode: 'system',
      fontSize: 'medium',
      syncFrequency: 'daily',
      showNotifications: true,
      
      // Actions
      setThemeMode: (mode) => set({ themeMode: mode }),
      setFontSize: (size) => set({ fontSize: size }),
      setSyncFrequency: (frequency) => set({ syncFrequency: frequency }),
      setShowNotifications: (show) => set({ showNotifications: show }),
    }),
    {
      name: 'settings-storage',
      storage: createJSONStorage(() => AsyncStorage),
    }
  )
);

// Custom hook that combines device theme with user preferences
export function useColorScheme() {
  const deviceColorScheme = useDeviceColorScheme();
  const { themeMode } = useSettingsStore();
  
  if (themeMode === 'system') {
    return deviceColorScheme;
  }
  
  return themeMode;
}

// Custom hook that returns the font size multiplier
export function useFontSize() {
  const { fontSize } = useSettingsStore();
  
  switch (fontSize) {
    case 'small':
      return 0.85;
    case 'large':
      return 1.15;
    case 'medium':
    default:
      return 1;
  }
} 