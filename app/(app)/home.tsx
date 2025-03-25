import React, { useMemo } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator } from 'react-native';
import { router } from 'expo-router';
import { useAuth } from '@/hooks/useAuth';
import { useDataSync } from '@/hooks/useDataSync';
import Colors from '@/constants/Colors';
import { authAPI } from '@/services/api';
import ScreenContainer from '@/components/ScreenContainer';
import { MaterialIcons } from '@expo/vector-icons';
import { EventCard } from '@/components/EventCard';

// Helper function to check if event is today
const isToday = (dateString: string) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  const eventDate = new Date(dateString);
  eventDate.setHours(0, 0, 0, 0);
  
  return today.getTime() === eventDate.getTime();
};

export default function Home() {
  const { user, currentJesuit } = useAuth();
  const { events = [], isLoading } = useDataSync();
  const { logout } = authAPI;

  // Filter events for today and upcoming
  const todaysEvents = useMemo(() => {
    return events.filter(event => isToday(event.start_datetime));
  }, [events]);

  const upcomingEvents = useMemo(() => {
    const now = new Date();
    return events
      .filter(event => new Date(event.start_datetime) > now)
      .sort((a, b) => new Date(a.start_datetime).getTime() - new Date(b.start_datetime).getTime())
      .slice(0, 10); // Limit to first 10 upcoming events
  }, [events]);

  const handleLogout = async () => {
    try {
      logout();
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  return (
    <ScreenContainer>
      <ScrollView style={styles.scrollView}>
        <View style={styles.headerSection}>
          <Text style={styles.welcome}>
            Welcome, {user?.name}
          </Text>
          
          <TouchableOpacity 
            style={styles.profileButton}
            onPress={() => router.push('/(app)/profile/me')}
          >
            <MaterialIcons name="person" size={20} color={Colors.primary} />
            <Text style={styles.profileButtonText}>View Profile</Text>
          </TouchableOpacity>
        </View>
        
        {/* Today's Events Section */}
        <View style={styles.sectionContainer}>
          <Text style={styles.sectionTitle}>Today's Events</Text>
          
          {isLoading ? (
            <ActivityIndicator size="small" color={Colors.primary} style={styles.loader} />
          ) : todaysEvents.length > 0 ? (
            todaysEvents.map(event => (
              <EventCard key={event.id} event={event} />
            ))
          ) : (
            <Text style={styles.noEventsText}>No events scheduled for today</Text>
          )}
        </View>
        
        {/* Upcoming Events Section */}
        <View style={styles.sectionContainer}>
          <View style={styles.sectionHeaderRow}>
            <Text style={styles.sectionTitle}>Upcoming Events</Text>
            <TouchableOpacity 
              style={styles.viewAllButton}
              onPress={() => router.push('/(app)/events')}
            >
              <Text style={styles.viewAllText}>View All</Text>
              <MaterialIcons name="chevron-right" size={16} color={Colors.primary} />
            </TouchableOpacity>
          </View>
          
          {isLoading ? (
            <ActivityIndicator size="small" color={Colors.primary} style={styles.loader} />
          ) : upcomingEvents.length > 0 ? (
            <ScrollView 
              horizontal 
              showsHorizontalScrollIndicator={false}
              style={styles.horizontalScroll}
            >
              {upcomingEvents.map(event => (
                <View key={event.id} style={styles.horizontalCardContainer}>
                  <EventCard event={event} isCompact={true} />
                </View>
              ))}
            </ScrollView>
          ) : (
            <Text style={styles.noEventsText}>No upcoming events</Text>
          )}
        </View>
        
        {/* Navigation Buttons */}
        <View style={styles.buttonsContainer}>
          <TouchableOpacity
            style={styles.navButton}
            onPress={() => router.push('/(app)/filter')}
          >
            <MaterialIcons name="filter-list" size={24} color={Colors.primary} />
            <Text style={styles.navButtonText}>Search & Filter</Text>
          </TouchableOpacity>
          
          <TouchableOpacity
            style={styles.navButton}
            onPress={() => router.push('/(app)/filter/communities')}
          >
            <MaterialIcons name="home" size={24} color={Colors.primary} />
            <Text style={styles.navButtonText}>Communities</Text>
          </TouchableOpacity>
          
          <TouchableOpacity
            style={styles.navButton}
            onPress={() => router.push('/(app)/filter/institutions')}
          >
            <MaterialIcons name="business" size={24} color={Colors.primary} />
            <Text style={styles.navButtonText}>Institutions</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  scrollView: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  headerSection: {
    padding: 16,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  welcome: {
    fontSize: 22,
    fontWeight: 'bold',
    color: Colors.text,
    flex: 1,
  },
  profileButton: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 8,
  },
  profileButtonText: {
    color: Colors.primary,
    marginLeft: 4,
    fontWeight: '500',
  },
  sectionContainer: {
    marginBottom: 24,
    paddingHorizontal: 16,
  },
  sectionHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: Colors.text,
    marginBottom: 12,
  },
  viewAllButton: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  viewAllText: {
    color: Colors.primary,
    fontSize: 14,
  },
  loader: {
    marginVertical: 20,
  },
  noEventsText: {
    color: Colors.gray[500],
    textAlign: 'center',
    marginVertical: 20,
    fontStyle: 'italic',
  },
  horizontalScroll: {
    flexDirection: 'row',
  },
  horizontalCardContainer: {
    marginRight: 12,
  },
  buttonsContainer: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginBottom: 24,
    paddingHorizontal: 16,
  },
  navButton: {
    backgroundColor: Colors.white,
    borderRadius: 8,
    padding: 16,
    alignItems: 'center',
    width: '30%',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  navButtonText: {
    color: Colors.text,
    fontSize: 14,
    fontWeight: '500',
    marginTop: 8,
    textAlign: 'center',
  },
  logoutButton: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginVertical: 24,
    alignSelf: 'center',
    padding: 10,
  },
  logoutText: {
    color: Colors.error,
    fontSize: 16,
    fontWeight: '500',
    marginLeft: 6,
  },
}); 