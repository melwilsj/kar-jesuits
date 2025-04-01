import React from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator } from 'react-native';
import { useLocalSearchParams, Stack } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';
import ScreenContainer from '@/components/ScreenContainer';
import { useEvent } from '@/hooks/useDataUtils';

export default function EventScreen() {
  const { id } = useLocalSearchParams();
  const colorScheme = useColorScheme();
  const { event, loading } = useEvent(Number(id));
  
  // Helper function to format date
  const formatDate = (dateString: string) => {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'long',
      month: 'long',
      day: 'numeric',
      year: 'numeric',
    });
  };

  // Helper function to format time
  const formatTime = (dateString: string) => {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
    });
  };
  
  if (loading) {
    return (
      <ScreenContainer>
        <View style={[styles.container, styles.centerContent]}>
          <ActivityIndicator size="large" color={Colors[`${colorScheme}`].primary} />
        </View>
      </ScreenContainer>
    );
  }

  if (!event) {
    return (
      <ScreenContainer>
        <View style={[styles.container, styles.centerContent]}>
          <Text style={{ color: Colors[`${colorScheme}`].textSecondary }}>
            Event not found
          </Text>
        </View>
      </ScreenContainer>
    );
  }

  // Get event type icon
  const getEventTypeIcon = () => {
    switch (event.event_type) {
      case 'birthday': return 'cake';
      case 'meeting': return 'groups';
      case 'conference': return 'event';
      case 'liturgical': return 'church';
      default: return 'event-note';
    }
  };

  return (
    <ScreenContainer>
      <Stack.Screen options={{ 
        title: event.title,
        headerBackVisible: true
      }} />

      <ScrollView
        style={styles.container}
        contentContainerStyle={styles.contentContainer}
      >
        {/* Header Section */}
        <View style={[
          styles.card,
          { backgroundColor: Colors[`${colorScheme}`].background }
        ]}>
          <View style={styles.eventTypeChip}>
            <MaterialIcons 
              name={getEventTypeIcon()} 
              size={16} 
              color={Colors[`${colorScheme}`].primary} 
            />
            <Text style={styles.eventTypeText}>
              {event.event_type.charAt(0).toUpperCase() + event.event_type.slice(1)}
            </Text>
          </View>
          
          <Text style={[
            styles.title,
            { color: Colors[`${colorScheme}`].text }
          ]}>
            {event.title}
          </Text>
        </View>

        {/* Details Card */}
        <View style={[
          styles.card,
          { backgroundColor: Colors[`${colorScheme}`].background }
        ]}>
          <View style={styles.infoRow}>
            <MaterialIcons 
              name="description" 
              size={20} 
              color={Colors[`${colorScheme}`].primary} 
              style={styles.infoIcon}
            />
            <View style={styles.infoContent}>
              <Text style={[
                styles.infoLabel,
                { color: Colors[`${colorScheme}`].textSecondary }
              ]}>
                Description
              </Text>
              <Text style={[
                styles.infoValue,
                { color: Colors[`${colorScheme}`].text }
              ]}>
                {event.description || 'No description available'}
              </Text>
            </View>
          </View>

          <View style={styles.infoRow}>
            <MaterialIcons 
              name="date-range" 
              size={20} 
              color={Colors[`${colorScheme}`].primary} 
              style={styles.infoIcon}
            />
            <View style={styles.infoContent}>
              <Text style={[
                styles.infoLabel,
                { color: Colors[`${colorScheme}`].textSecondary }
              ]}>
                Date
              </Text>
              <Text style={[
                styles.infoValue,
                { color: Colors[`${colorScheme}`].text }
              ]}>
                {formatDate(event.start_datetime)}
              </Text>
            </View>
          </View>

          <View style={styles.infoRow}>
            <MaterialIcons 
              name="access-time" 
              size={20} 
              color={Colors[`${colorScheme}`].primary} 
              style={styles.infoIcon}
            />
            <View style={styles.infoContent}>
              <Text style={[
                styles.infoLabel,
                { color: Colors[`${colorScheme}`].textSecondary }
              ]}>
                Time
              </Text>
              <Text style={[
                styles.infoValue,
                { color: Colors[`${colorScheme}`].text }
              ]}>
                {formatTime(event.start_datetime)} - {formatTime(event.end_datetime)}
              </Text>
            </View>
          </View>

          {event.venue && (
            <View style={styles.infoRow}>
              <MaterialIcons 
                name="location-on" 
                size={20} 
                color={Colors[`${colorScheme}`].primary} 
                style={styles.infoIcon}
              />
              <View style={styles.infoContent}>
                <Text style={[
                  styles.infoLabel,
                  { color: Colors[`${colorScheme}`].textSecondary }
                ]}>
                  Venue
                </Text>
                <Text style={[
                  styles.infoValue,
                  { color: Colors[`${colorScheme}`].text }
                ]}>
                  {event.venue}
                </Text>
              </View>
            </View>
          )}

          {event.community && (
            <View style={styles.infoRow}>
              <MaterialIcons 
                name="home" 
                size={20} 
                color={Colors[`${colorScheme}`].primary} 
                style={styles.infoIcon}
              />
              <View style={styles.infoContent}>
                <Text style={[
                  styles.infoLabel,
                  { color: Colors[`${colorScheme}`].textSecondary }
                ]}>
                  Community
                </Text>
                <Text style={[
                  styles.infoValue,
                  { color: Colors[`${colorScheme}`].text }
                ]}>
                  {event.community}
                </Text>
              </View>
            </View>
          )}
        </View>
      </ScrollView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  contentContainer: {
    padding: 16,
  },
  centerContent: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  card: {
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  eventTypeChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Color.primary,
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 16,
    marginBottom: 12,
  },
  eventTypeText: {
    color: Color.white,
    fontSize: 12,
    fontWeight: '500',
    marginLeft: 4,
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    marginBottom: 8,
  },
  infoRow: {
    flexDirection: 'row',
    marginBottom: 16,
    alignItems: 'flex-start',
  },
  infoIcon: {
    marginRight: 12,
    marginTop: 2,
  },
  infoContent: {
    flex: 1,
  },
  infoLabel: {
    fontSize: 14,
    marginBottom: 2,
  },
  infoValue: {
    fontSize: 16,
  },
}); 