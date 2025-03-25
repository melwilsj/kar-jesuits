import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { router } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import React from 'react';
import { useColorScheme } from 'react-native';
import { Event } from '@/types/api';
// Helper function to format date
const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

// Helper to check if event is in the past
const isPastEvent = (endDateString: string) => {
  const now = new Date();
  const endDate = new Date(endDateString);
  return endDate < now;
};

export const EventCard = ({ event, isCompact = false }: { event: Event, isCompact?: boolean }) => {
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const pastEvent = isPastEvent(event.end_datetime);
  
  const eventTypeIcon = () => {
    switch (event.event_type) {
      case 'birthday': return 'cake';
      case 'meeting': return 'groups';
      case 'conference': return 'event';
      case 'liturgical': return 'church';
      default: return 'event-note';
    }
  };

  return (
    <TouchableOpacity 
      style={[
        styles.eventCard, 
        isCompact && styles.compactEventCard,
        pastEvent && styles.pastEventCard,
        isDark && styles.darkEventCard
      ]}
      onPress={() => router.push(`/(app)/events/${event.id}`)}
    >
      <View style={[
        styles.eventIconContainer,
        isDark && styles.darkIconContainer,
        pastEvent && styles.pastIconContainer
      ]}>
        <MaterialIcons 
          name={eventTypeIcon()} 
          size={isCompact ? 20 : 24} 
          color={pastEvent ? Colors.gray[400] : Colors.primary} 
        />
      </View>
      <View style={styles.eventContent}>
        <Text 
          style={[
            styles.eventTitle, 
            isDark && styles.darkText,
            pastEvent && styles.pastText
          ]} 
          numberOfLines={1}
        >
          {event.title}
        </Text>
        {!isCompact && (
          <Text 
            style={[
              styles.eventDescription, 
              isDark && styles.darkDescriptionText,
              pastEvent && styles.pastDescriptionText
            ]} 
            numberOfLines={2}
          >
            {event.description}
          </Text>
        )}
        <View style={styles.eventMeta}>
          <MaterialIcons 
            name="access-time" 
            size={14} 
            color={isDark ? Colors.gray[400] : Colors.gray[500]} 
          />
          <Text 
            style={[
              styles.eventMetaText,
              isDark && styles.darkMetaText
            ]}
          >
            {formatDate(event.start_datetime)}
          </Text>
          {event.venue && (
            <>
              <MaterialIcons 
                name="location-on" 
                size={14} 
                color={isDark ? Colors.gray[400] : Colors.gray[500]} 
                style={styles.metaIcon} 
              />
              <Text 
                style={[
                  styles.eventMetaText,
                  isDark && styles.darkMetaText
                ]}
              >
                {event.venue}
              </Text>
            </>
          )}
        </View>
      </View>
    </TouchableOpacity>
  );
};
  
const styles = StyleSheet.create({
  eventCard: {
    backgroundColor: Colors.white,
    borderRadius: 8,
    marginBottom: 12,
    padding: 16,
    flexDirection: 'row',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  darkEventCard: {
    backgroundColor: Colors.gray[800],
    shadowColor: '#000',
    shadowOpacity: 0.2,
  },
  pastEventCard: {
    opacity: 0.7,
  },
  compactEventCard: {
    minWidth: 250,
    padding: 12,
  },
  eventIconContainer: {
    width: 40,
    height: 40,
    backgroundColor: Colors.gray[100],
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  darkIconContainer: {
    backgroundColor: Colors.gray[700],
  },
  pastIconContainer: {
    backgroundColor: Colors.gray[200],
  },
  eventContent: {
    flex: 1,
  },
  eventTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: Colors.text,
    marginBottom: 4,
  },
  darkText: {
    color: Colors.gray[100],
  },
  pastText: {
    color: Colors.gray[600],
  },
  eventDescription: {
    fontSize: 14,
    color: Colors.gray[600],
    marginBottom: 8,
  },
  darkDescriptionText: {
    color: Colors.gray[400],
  },
  pastDescriptionText: {
    color: Colors.gray[500],
  },
  eventMeta: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  eventMetaText: {
    fontSize: 12,
    color: Colors.gray[500],
    marginLeft: 4,
    marginRight: 8,
  },
  darkMetaText: {
    color: Colors.gray[400],
  },
  metaIcon: {
    marginLeft: 4,
  },
});