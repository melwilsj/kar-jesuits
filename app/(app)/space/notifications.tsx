import React, { useState, useEffect, useCallback } from "react";
import { View, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl } from "react-native";
import ScreenContainer from '@/components/ScreenContainer';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';
import { Stack } from 'expo-router';
import ScaledText from '@/components/ScaledText';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { dataAPI } from '@/services/api';

// Define notification type based on API response
interface Notification {
  id: number;
  title: string;
  content: string;
  type: string;
  event_id: number | null;
  sent_at: string;
  is_read: boolean;
  metadata: any | null;
  event: any | null;
}

export default function Notifications() {
  const colorScheme = useColorScheme();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    // On initial load, try to get cached notifications
    loadCachedNotifications();
  }, []);

  const loadCachedNotifications = async () => {
    try {
      const stored = await AsyncStorage.getItem('user_notifications');
      if (stored) {
        setNotifications(JSON.parse(stored));
      }
    } catch (error) {
      console.error('Error loading cached notifications:', error);
    }
  };

  const fetchNotifications = async (forceRefresh = false) => {
    try {
      setIsLoading(true);
      
      // Only fetch if we don't have notifications or force refresh is requested
      if (forceRefresh || notifications.length === 0) {
        const response = await dataAPI.fetchNotifications();
        if (response.data) {
          setNotifications(response.data.data);
          // Cache the notifications
          await AsyncStorage.setItem('user_notifications', JSON.stringify(response.data.data));
        }
      }
    } catch (error) {
      console.error('Error fetching notifications:', error);
    } finally {
      setIsLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchNotifications(true);
  }, []);

  const markAsRead = async (id: number) => {
    try {
      // Try to mark as read via API
      await dataAPI.markNotificationAsRead(id);
      
      // Update local state
      const updatedNotifications = notifications.map(notification => 
        notification.id === id 
          ? { ...notification, is_read: true } 
          : notification
      );
      
      setNotifications(updatedNotifications);
      await AsyncStorage.setItem('user_notifications', JSON.stringify(updatedNotifications));
    } catch (error) {
      console.error('Error marking notification as read:', error);
      // Still update local state even if API fails
      const updatedNotifications = notifications.map(notification => 
        notification.id === id 
          ? { ...notification, is_read: true } 
          : notification
      );
      
      setNotifications(updatedNotifications);
      await AsyncStorage.setItem('user_notifications', JSON.stringify(updatedNotifications));
    }
  };

  const formatTimestamp = (timestamp: string) => {
    const now = new Date();
    const notificationDate = new Date(timestamp);
    
    // If it's today, show time
    if (now.toDateString() === notificationDate.toDateString()) {
      return notificationDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    // If it's within the last week, show day name
    const diffDays = Math.round((now.getTime() - notificationDate.getTime()) / (1000 * 60 * 60 * 24));
    if (diffDays < 7) {
      return notificationDate.toLocaleDateString([], { weekday: 'long' });
    }
    
    // Otherwise show date
    return notificationDate.toLocaleDateString();
  };

  const getNotificationIcon = (type: string) => {
    switch (type) {
      case 'announcement':
        return 'volume-up';
      case 'warning':
        return 'warning';
      case 'error':
        return 'error';
      case 'announcement':
        return 'campaign';
      case 'info':
      default:
        return 'info';
    }
  };

  const getNotificationColor = (type: string) => {
    switch (type) {
      case 'success':
        return Colors[`${colorScheme}`].success;
      case 'warning':
        return Colors[`${colorScheme}`].warning;
      case 'error':
        return Colors[`${colorScheme}`].error;
      case 'announcement':
        return Colors[`${colorScheme}`].primary;
      case 'info':
      default:
        return Colors[`${colorScheme}`].primary;
    }
  };

  return (
    <ScreenContainer>
      <Stack.Screen options={{ title: 'Notifications' }} />
      
      <View style={styles.container}>
        <View style={styles.header}>
          <ScaledText style={[
            styles.title, 
            { color: Colors[`${colorScheme}`].text }
          ]}>
            Notifications
          </ScaledText>
        </View>
        
        <TouchableOpacity 
          style={[
            styles.refreshButton,
            { backgroundColor: Colors[`${colorScheme}`].background }
          ]}
          onPress={() => fetchNotifications(true)}
          disabled={isLoading}
        >
          <MaterialIcons 
            name="refresh" 
            size={20} 
            color={Colors[`${colorScheme}`].icon} 
          />
          <ScaledText style={[
            styles.refreshButtonText,
            { color: Colors[`${colorScheme}`].textSecondary }
          ]}>
            Refresh
          </ScaledText>
        </TouchableOpacity>
        
        {isLoading && notifications.length === 0 ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color={Colors[`${colorScheme}`].primary} />
          </View>
        ) : notifications.length > 0 ? (
          <FlatList
            data={notifications}
            keyExtractor={(item) => item.id.toString()}
            contentContainerStyle={styles.notificationsList}
            refreshControl={
              <RefreshControl
                refreshing={refreshing}
                onRefresh={onRefresh}
                colors={[Colors[`${colorScheme}`].primary]}
                tintColor={Colors[`${colorScheme}`].primary}
              />
            }
            renderItem={({ item }) => (
              <TouchableOpacity
                style={[
                  styles.notificationItem,
                  { backgroundColor: Colors[`${colorScheme}`].background },
                  item.is_read ? styles.readNotification : null
                ]}
                onPress={() => markAsRead(item.id)}
              >
                <View 
                  style={[
                    styles.iconContainer,
                    { backgroundColor: getNotificationColor(item.type) + '20' }
                  ]}
                >
                  <MaterialIcons 
                    name={getNotificationIcon(item.type)} 
                    size={24} 
                    color={getNotificationColor(item.type)} 
                  />
                </View>
                
                <View style={styles.contentContainer}>
                  <View style={styles.titleRow}>
                    <ScaledText 
                      style={[
                        styles.notificationTitle,
                        { 
                          color: Colors[`${colorScheme}`].text,
                          fontWeight: item.is_read ? '400' : '600'
                        }
                      ]}
                      numberOfLines={1}
                    >
                      {item.title}
                    </ScaledText>
                    
                    <ScaledText 
                      style={[
                        styles.timestamp,
                        { color: Colors[`${colorScheme}`].textSecondary }
                      ]}
                    >
                      {formatTimestamp(item.sent_at)}
                    </ScaledText>
                    
                    {!item.is_read && (
                      <View style={styles.unreadIndicator} />
                    )}
                  </View>
                  
                  <ScaledText 
                    style={[
                      styles.message,
                      { color: Colors[`${colorScheme}`].textSecondary }
                    ]}
                    numberOfLines={2}
                  >
                    {item.content}
                  </ScaledText>
                </View>
              </TouchableOpacity>
            )}
          />
        ) : (
          <View style={styles.emptyContainer}>
            <MaterialIcons 
              name="notifications-off" 
              size={48} 
              color={Colors[`${colorScheme}`].textSecondary} 
              style={styles.emptyIcon}
            />
            <ScaledText style={[
              styles.emptyText,
              { color: Colors[`${colorScheme}`].textSecondary }
            ]}>
              No notifications
            </ScaledText>
            <ScaledText style={[
              styles.emptySubtext,
              { color: Colors[`${colorScheme}`].textSecondary }
            ]}>
              Tap refresh to check for new notifications
            </ScaledText>
          </View>
        )}
      </View>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
  },
  clearButton: {
    padding: 8,
  },
  clearButtonText: {
    color: Color.primary,
    fontSize: 14,
  },
  refreshButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    marginHorizontal: 16,
    marginBottom: 12,
    borderRadius: 8,
  },
  refreshButtonText: {
    fontSize: 14,
    marginLeft: 8,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 24,
  },
  emptyIcon: {
    marginBottom: 16,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: '500',
  },
  emptySubtext: {
    fontSize: 14,
    textAlign: 'center',
    marginTop: 8,
  },
  notificationsList: {
    paddingHorizontal: 16,
    paddingBottom: 16,
  },
  notificationItem: {
    flexDirection: 'row',
    marginBottom: 12,
    padding: 12,
    borderRadius: 8,
    borderLeftWidth: 4,
    elevation: 1,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 1,
  },
  readNotification: {
    opacity: 0.8,
  },
  iconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  contentContainer: {
    flex: 1,
  },
  titleRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  notificationTitle: {
    fontSize: 16,
    flex: 1,
  },
  timestamp: {
    fontSize: 12,
    marginLeft: 8,
  },
  message: {
    fontSize: 14,
  },
  unreadIndicator: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: Color.primary,
    marginLeft: 8,
  },
}); 