import React, { useState, useMemo } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, ScrollView } from 'react-native';
import { router, Stack } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import { useColorScheme } from 'react-native';
import { useDataSync } from '@/hooks/useDataSync';
import ScreenContainer from '@/components/ScreenContainer';
import { EventCard } from '@/components/EventCard';
import { Event } from '@/types/api';
export default function EventsScreen() {
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const { events = [], isLoading } = useDataSync();
  const [filter, setFilter] = useState('upcoming'); // 'all', 'today', 'upcoming', 'past'
  
  // Helper to check if event is today
  const isToday = (dateString: string) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const eventDate = new Date(dateString);
    eventDate.setHours(0, 0, 0, 0);
    
    return today.getTime() === eventDate.getTime();
  };

  // Filter events based on selection
  const filteredEvents = useMemo(() => {
    if (!events || events.length === 0) return [];
    
    const now = new Date();
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    switch (filter) {
      case 'today':
        return events.filter(event => isToday(event.start_datetime));
      
      case 'upcoming':
        return events
          .filter(event => new Date(event.start_datetime) >= today)
          .sort((a, b) => new Date(a.start_datetime).getTime() - new Date(b.start_datetime).getTime());
      
      case 'past':
        return events
          .filter(event => new Date(event.end_datetime) < today)
          .sort((a, b) => new Date(b.end_datetime).getTime() - new Date(a.end_datetime).getTime());
      
      case 'all':
      default:
        return [...events].sort((a, b) => 
          new Date(a.start_datetime).getTime() - new Date(b.start_datetime).getTime()
        );
    }
  }, [events, filter]);

  // Render item using our existing EventCard component
  const renderEventItem = ({ item }: { item: Event }) => (
    <EventCard event={item} />
  );

  return (
    <ScreenContainer>
      <Stack.Screen options={{ 
        title: 'Events',
        headerBackVisible: true
      }} />
      
      <View style={[
        styles.container,
        { backgroundColor: isDark ? Colors.gray[900] : Colors.background }
      ]}>
        {/* Filter tabs */}
        <View style={styles.filterContainer}>
          <ScrollView 
            horizontal 
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.filtersContent}
          >
            <TouchableOpacity
              style={[
                styles.filterButton,
                filter === 'upcoming' && styles.activeFilter
              ]}
              onPress={() => setFilter('upcoming')}
            >
              <Text 
                style={[
                  styles.filterText,
                  filter === 'upcoming' && styles.activeFilterText
                ]}
              >
                Upcoming
              </Text>
            </TouchableOpacity>
            
            <TouchableOpacity
              style={[
                styles.filterButton,
                filter === 'today' && styles.activeFilter
              ]}
              onPress={() => setFilter('today')}
            >
              <Text 
                style={[
                  styles.filterText,
                  filter === 'today' && styles.activeFilterText
                ]}
              >
                Today
              </Text>
            </TouchableOpacity>
            
            <TouchableOpacity
              style={[
                styles.filterButton,
                filter === 'past' && styles.activeFilter
              ]}
              onPress={() => setFilter('past')}
            >
              <Text 
                style={[
                  styles.filterText,
                  filter === 'past' && styles.activeFilterText
                ]}
              >
                Past
              </Text>
            </TouchableOpacity>
            
            <TouchableOpacity
              style={[
                styles.filterButton,
                filter === 'all' && styles.activeFilter
              ]}
              onPress={() => setFilter('all')}
            >
              <Text 
                style={[
                  styles.filterText,
                  filter === 'all' && styles.activeFilterText
                ]}
              >
                All Events
              </Text>
            </TouchableOpacity>
          </ScrollView>
        </View>
        
        {/* Events list */}
        {isLoading ? (
          <View style={styles.centerContent}>
            <ActivityIndicator size="large" color={Colors.primary} />
          </View>
        ) : filteredEvents.length > 0 ? (
          <FlatList
            data={filteredEvents}
            renderItem={renderEventItem}
            keyExtractor={item => item.id.toString()}
            contentContainerStyle={styles.eventsList}
          />
        ) : (
          <View style={styles.centerContent}>
            <MaterialIcons 
              name="event-busy" 
              size={48} 
              color={Colors.gray[400]} 
              style={styles.noEventsIcon}
            />
            <Text style={[
              styles.noEventsText,
              { color: isDark ? Colors.gray[300] : Colors.gray[600] }
            ]}>
              No {filter === 'all' ? '' : filter} events found
            </Text>
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
  centerContent: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  filterContainer: {
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: Colors.gray[200],
  },
  filtersContent: {
    paddingHorizontal: 16,
    flexDirection: 'row'
  },
  filterButton: {
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 20,
    marginRight: 8,
    backgroundColor: Colors.gray[100]
  },
  activeFilter: {
    backgroundColor: Colors.primary
  },
  filterText: {
    color: Colors.gray[700],
    fontWeight: '500'
  },
  activeFilterText: {
    color: Colors.white
  },
  eventsList: {
    padding: 16
  },
  noEventsIcon: {
    marginBottom: 16
  },
  noEventsText: {
    fontSize: 16
  }
});
