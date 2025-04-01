import React, { useState, useEffect } from "react";
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Switch } from "react-native";
import ScreenContainer from '@/components/ScreenContainer';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';
import { Stack } from 'expo-router';
import { useSettingsStore } from '@/hooks/useSettings';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Constants from 'expo-constants';
import { useDataSync } from '@/hooks/useDataSync';
import { DataStorage } from '@/services/storage';

export default function Settings() {
  const colorScheme = useColorScheme();
  const { syncData } = useDataSync();
  const [syncTime, setSyncTime] = useState<number | null>(null);
  const [cacheSize, setCacheSize] = useState('0 MB');
  
  // Settings store
  const { 
    themeMode, 
    setThemeMode, 
    fontSize, 
    setFontSize,
    syncFrequency,
    setSyncFrequency,
    showNotifications,
    setShowNotifications
  } = useSettingsStore();

  useEffect(() => {
    // Load the last sync time
    const loadSyncTime = async () => {
      try {
        const time = await DataStorage.getLastSync();
        setSyncTime(time);
      } catch (error) {
        console.error('Error loading sync time:', error);
      }
    };
    
    loadSyncTime();
    calculateCacheSize();
  }, []);

  // Calculate local storage cache size
  const calculateCacheSize = async () => {
    try {
      const keys = await AsyncStorage.getAllKeys();
      let totalSize = 0;
      
      for (const key of keys) {
        const item = await AsyncStorage.getItem(key);
        if (item) {
          totalSize += new Blob([item]).size;
        }
      }
      
      // Convert to MB with 2 decimal places
      const sizeMB = (totalSize / (1024 * 1024)).toFixed(2);
      setCacheSize(`${sizeMB} MB`);
    } catch (error) {
      console.error('Error calculating cache size:', error);
      setCacheSize('Unable to calculate');
    }
  };

  const clearCache = async () => {
    try {
      // Get all keys except for critical ones
      const keys = await AsyncStorage.getAllKeys();
      const nonCriticalKeys = keys.filter(key => 
        !key.includes('auth') && !key.includes('settings')
      );
      
      await AsyncStorage.multiRemove(nonCriticalKeys);
      setCacheSize('0 MB');
    } catch (error) {
      console.error('Error clearing cache:', error);
    }
  };

  const appVersion = Constants.expoConfig?.version || '1.0.0';
  
  const handleSyncNow = async () => {
    try {
      await syncData();
      // Reload the sync time after syncing
      const time = await DataStorage.getLastSync();
      setSyncTime(time);
      // Show a success message or toast
    } catch (error) {
      console.error('Sync error:', error);
      // Show an error message or toast
    }
  };

  const formatSyncTime = () => {
    if (!syncTime) return 'Never';
    
    try {
      const date = new Date(syncTime);
      return date.toLocaleString();
    } catch (error) {
      return 'Unknown';
    }
  };

  return (
    <ScreenContainer>
      <Stack.Screen options={{ title: 'Settings' }} />
      
      <ScrollView style={styles.scrollView}>
        {/* Appearance Section */}
        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: Colors[`${colorScheme}`].text }]}>
            Appearance
          </Text>
          
          {/* Theme Setting */}
          <View style={[
            styles.settingItem, 
            { backgroundColor: Colors[`${colorScheme}`].background }
          ]}>
            <View style={styles.settingContent}>
              <MaterialIcons 
                name="dark-mode" 
                size={22} 
                color={Colors[`${colorScheme}`].icon} 
              />
              <Text style={[
                styles.settingText, 
                { color: Colors[`${colorScheme}`].text }
              ]}>
                Dark Mode
              </Text>
            </View>
            <Switch
              value={themeMode !== 'light'}
              onValueChange={(value) => {
                if (value) {
                  setThemeMode('dark');
                } else {
                  setThemeMode('light');
                }
              }}
              trackColor={{ false: Colors[`${colorScheme}`].gray300, true: Colors[`${colorScheme}`].primary }}
            />
          </View>
          
          {/* Font Size Setting */}
          <View style={[
            styles.settingItem, 
            { backgroundColor: Colors[`${colorScheme}`].background }
          ]}>
            <View style={styles.settingContent}>
              <MaterialIcons 
                name="format-size" 
                size={22} 
                color={Colors[`${colorScheme}`].icon} 
              />
              <Text style={[
                styles.settingText, 
                { color: Colors[`${colorScheme}`].text }
              ]}>
                Font Size
              </Text>
            </View>
            <View style={styles.fontSizeOptions}>
              <TouchableOpacity 
                style={[
                  styles.fontSizeOption,
                  fontSize === 'small' && styles.selectedFontSize,
                  fontSize === 'small' && { backgroundColor: Colors[`${colorScheme}`].primary }
                ]}
                onPress={() => setFontSize('small')}
              >
                <Text style={[
                  styles.fontSizeText,
                  { fontSize: 12 },
                  fontSize === 'small' && { color: Colors[`${colorScheme}`].primary }
                ]}>
                  S
                </Text>
              </TouchableOpacity>
              <TouchableOpacity 
                style={[
                  styles.fontSizeOption,
                  fontSize === 'medium' && styles.selectedFontSize,
                  fontSize === 'medium' && { backgroundColor: Colors[`${colorScheme}`].primary }
                ]}
                onPress={() => setFontSize('medium')}
              >
                <Text style={[
                  styles.fontSizeText,
                  { fontSize: 15 },
                  fontSize === 'medium' && { color: Colors[`${colorScheme}`].primary }
                ]}>
                  M
                </Text>
              </TouchableOpacity>
              <TouchableOpacity 
                style={[
                  styles.fontSizeOption,
                  fontSize === 'large' && styles.selectedFontSize,
                  fontSize === 'large' && { backgroundColor: Colors[`${colorScheme}`].primary }
                ]}
                onPress={() => setFontSize('large')}
              >
                <Text style={[
                  styles.fontSizeText,
                  { fontSize: 18 },
                  fontSize === 'large' && { color: Colors[`${colorScheme}`].primary }
                ]}>
                  L
                </Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
        
        {/* Data Section */}
        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: Colors[`${colorScheme}`].text }]}>
            Data & Sync
          </Text>
          
          {/* Sync Frequency */}
          <View style={[
            styles.settingItem, 
            { backgroundColor: Colors[`${colorScheme}`].background }
          ]}>
            <View style={styles.settingContent}>
              <MaterialIcons 
                name="sync" 
                size={22} 
                color={Colors[`${colorScheme}`].icon} 
              />
              <Text style={[
                styles.settingText, 
                { color: Colors[`${colorScheme}`].text }
              ]}>
                Sync Frequency
              </Text>
            </View>
            <View style={styles.syncOptions}>
              <TouchableOpacity
                style={[
                  styles.syncOption,
                  syncFrequency === 'manual' && { backgroundColor: Colors[`${colorScheme}`].primary }
                ]}
                onPress={() => setSyncFrequency('manual')}
              >
                <Text style={[
                  styles.syncOptionText,
                  syncFrequency === 'manual' && { color: Colors[`${colorScheme}`].primary }
                ]}>
                  Manual
                </Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[
                  styles.syncOption,
                  syncFrequency === 'daily' && { backgroundColor: Colors[`${colorScheme}`].primary }
                ]}
                onPress={() => setSyncFrequency('daily')}
              >
                <Text style={[
                  styles.syncOptionText,
                  syncFrequency === 'daily' && { color: Colors[`${colorScheme}`].primary }
                ]}>
                  Daily
                </Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[
                  styles.syncOption,
                  syncFrequency === 'always' && { backgroundColor: Colors[`${colorScheme}`].primary }
                ]}
                onPress={() => setSyncFrequency('always')}
              >
                <Text style={[
                  styles.syncOptionText,
                  syncFrequency === 'always' && { color: Colors[`${colorScheme}`].primary }
                ]}>
                  Always
                </Text>
              </TouchableOpacity>
            </View>
          </View>
          
          {/* Manual Sync Button */}
          <TouchableOpacity 
            style={[
              styles.settingItem, 
              { backgroundColor: Colors[`${colorScheme}`].background }
            ]}
            onPress={handleSyncNow}
          >
            <View style={styles.settingContent}>
              <MaterialIcons 
                name="sync" 
                size={22} 
                color={Colors[`${colorScheme}`].icon} 
              />
              <View>
                <Text style={[
                  styles.settingText, 
                  { color: Colors[`${colorScheme}`].text }
                ]}>
                  Sync Data Now
                </Text>
                <Text style={styles.settingSubtext}>
                  Last synced: {formatSyncTime()}
                </Text>
              </View>
            </View>
            <MaterialIcons 
              name="chevron-right" 
              size={22} 
              color={Colors[`${colorScheme}`].icon} 
            />
          </TouchableOpacity>
          
          {/* Cache Management */}
          <TouchableOpacity 
            style={[
              styles.settingItem, 
              { backgroundColor: Colors[`${colorScheme}`].background }
            ]}
            onPress={clearCache}
          >
            <View style={styles.settingContent}>
              <MaterialIcons 
                name="cleaning-services" 
                size={22} 
                color={Colors[`${colorScheme}`].icon} 
              />
              <View>
                <Text style={[
                  styles.settingText, 
                  { color: Colors[`${colorScheme}`].text }
                ]}>
                  Clear Cache
                </Text>
                <Text style={styles.settingSubtext}>
                  Current cache size: {cacheSize}
                </Text>
              </View>
            </View>
            <MaterialIcons 
              name="chevron-right" 
              size={22} 
              color={Colors[`${colorScheme}`].icon} 
            />
          </TouchableOpacity>
        </View>
        
        {/* Notifications Section */}
        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: Colors[`${colorScheme}`].text }]}>
            Notifications
          </Text>
          
          <View style={[
            styles.settingItem, 
            { backgroundColor: Colors[`${colorScheme}`].background }
          ]}>
            <View style={styles.settingContent}>
              <MaterialIcons 
                name="notifications" 
                size={22} 
                color={Colors[`${colorScheme}`].icon} 
              />
              <Text style={[
                styles.settingText, 
                { color: Colors[`${colorScheme}`].text }
              ]}>
                Enable Notifications
              </Text>
            </View>
            <Switch
              value={showNotifications}
              onValueChange={setShowNotifications}
              trackColor={{ false: Colors[`${colorScheme}`].gray300, true: Colors[`${colorScheme}`].primary }}
            />
          </View>
        </View>
        
        {/* About Section */}
        <View style={styles.section}>
          <Text style={[styles.sectionTitle, { color: Colors[`${colorScheme}`].text }]}>
            About
          </Text>
          
          <View style={[
            styles.settingItem, 
            { backgroundColor: Colors[`${colorScheme}`].background }
          ]}>
            <View style={styles.settingContent}>
              <MaterialIcons 
                name="info" 
                size={22} 
                color={Colors[`${colorScheme}`].icon} 
              />
              <Text style={[
                styles.settingText, 
                { color: Colors[`${colorScheme}`].text }
              ]}>
                App Version
              </Text>
            </View>
            <Text style={styles.versionText}>{appVersion}</Text>
          </View>
          
          <TouchableOpacity style={[
            styles.settingItem, 
            { backgroundColor: Colors[`${colorScheme}`].background }
          ]}>
            <View style={styles.settingContent}>
              <MaterialIcons 
                name="description" 
                size={22} 
                color={Colors[`${colorScheme}`].icon} 
              />
              <Text style={[
                styles.settingText, 
                { color: Colors[`${colorScheme}`].text }
              ]}>
                Terms of Service
              </Text>
            </View>
            <MaterialIcons 
              name="chevron-right" 
              size={22} 
              color={Colors[`${colorScheme}`].icon} 
            />
          </TouchableOpacity>
          
          <TouchableOpacity style={[
            styles.settingItem, 
            { backgroundColor: Colors[`${colorScheme}`].background }
          ]}>
            <View style={styles.settingContent}>
              <MaterialIcons 
                name="privacy-tip" 
                size={22} 
                color={Colors[`${colorScheme}`].icon} 
              />
              <Text style={[
                styles.settingText, 
                { color: Colors[`${colorScheme}`].text }
              ]}>
                Privacy Policy
              </Text>
            </View>
            <MaterialIcons 
              name="chevron-right" 
              size={22} 
              color={Colors[`${colorScheme}`].icon} 
            />
          </TouchableOpacity>
        </View>
      </ScrollView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  scrollView: {
    flex: 1,
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    marginLeft: 16,
    marginBottom: 8,
    marginTop: 8,
  },
  settingItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 16,
    paddingHorizontal: 16,
    marginBottom: 1,
  },
  settingContent: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  settingText: {
    fontSize: 16,
    marginLeft: 12,
  },
  settingSubtext: {
    fontSize: 12,
    color: Color.gray[500],
    marginLeft: 12,
    marginTop: 2,
  },
  versionText: {
    fontSize: 14,
    color: Color.gray[500],
  },
  fontSizeOptions: {
    flexDirection: 'row',
  },
  fontSizeOption: {
    width: 32,
    height: 32,
    justifyContent: 'center',
    alignItems: 'center',
    borderRadius: 16,
    backgroundColor: Color.gray[200],
    marginLeft: 8,
  },
  selectedFontSize: {
    borderWidth: 1,
    borderColor: Color.primary,
  },
  fontSizeText: {
    fontWeight: '600',
    color: Color.gray[700],
  },
  syncOptions: {
    flexDirection: 'row',
  },
  syncOption: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 4,
    backgroundColor: Color.gray[200],
    marginLeft: 8,
  },
  syncOptionText: {
    fontSize: 12,
    color: Color.gray[700],
  },
});