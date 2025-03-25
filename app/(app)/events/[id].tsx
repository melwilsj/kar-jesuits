import React from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator } from 'react-native';
import { useLocalSearchParams, Stack } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import { useColorScheme } from 'react-native';
import ScreenContainer from '@/components/ScreenContainer';
import { useEvent } from '@/hooks/useDataUtils';

export default function EventScreen() {
  const { id } = useLocalSearchParams();
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
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
          <ActivityIndicator size="large" color={Colors.primary} />
        </View>
      </ScreenContainer>
    );
  }

  if (!event) {
    return (
      <ScreenContainer>
        <View style={[styles.container, styles.centerContent]}>
          <Text style={{ color: isDark ? Colors.gray[300] : Colors.gray[700] }}>
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
          { backgroundColor: isDark ? Colors.gray[800] : Colors.white }
        ]}>
          <View style={styles.eventTypeChip}>
            <MaterialIcons 
              name={getEventTypeIcon()} 
              size={16} 
              color={Colors.white} 
            />
            <Text style={styles.eventTypeText}>
              {event.event_type.charAt(0).toUpperCase() + event.event_type.slice(1)}
            </Text>
          </View>
          
          <Text style={[
            styles.title,
            { color: isDark ? Colors.gray[100] : Colors.text }
          ]}>
            {event.title}
          </Text>
        </View>

        {/* Details Card */}
        <View style={[
          styles.card,
          { backgroundColor: isDark ? Colors.gray[800] : Colors.white }
        ]}>
          <View style={styles.infoRow}>
            <MaterialIcons 
              name="description" 
              size={20} 
              color={Colors.primary} 
              style={styles.infoIcon}
            />
            <View style={styles.infoContent}>
              <Text style={[
                styles.infoLabel,
                { color: isDark ? Colors.gray[400] : Colors.gray[600] }
              ]}>
                Description
              </Text>
              <Text style={[
                styles.infoValue,
                { color: isDark ? Colors.gray[200] : Colors.text }
              ]}>
                {event.description || 'No description available'}
              </Text>
            </View>
          </View>

          <View style={styles.infoRow}>
            <MaterialIcons 
              name="date-range" 
              size={20} 
              color={Colors.primary} 
              style={styles.infoIcon}
            />
            <View style={styles.infoContent}>
              <Text style={[
                styles.infoLabel,
                { color: isDark ? Colors.gray[400] : Colors.gray[600] }
              ]}>
                Date
              </Text>
              <Text style={[
                styles.infoValue,
                { color: isDark ? Colors.gray[200] : Colors.text }
              ]}>
                {formatDate(event.start_datetime)}
              </Text>
            </View>
          </View>

          <View style={styles.infoRow}>
            <MaterialIcons 
              name="access-time" 
              size={20} 
              color={Colors.primary} 
              style={styles.infoIcon}
            />
            <View style={styles.infoContent}>
              <Text style={[
                styles.infoLabel,
                { color: isDark ? Colors.gray[400] : Colors.gray[600] }
              ]}>
                Time
              </Text>
              <Text style={[
                styles.infoValue,
                { color: isDark ? Colors.gray[200] : Colors.text }
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
                color={Colors.primary} 
                style={styles.infoIcon}
              />
              <View style={styles.infoContent}>
                <Text style={[
                  styles.infoLabel,
                  { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                ]}>
                  Venue
                </Text>
                <Text style={[
                  styles.infoValue,
                  { color: isDark ? Colors.gray[200] : Colors.text }
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
                color={Colors.primary} 
                style={styles.infoIcon}
              />
              <View style={styles.infoContent}>
                <Text style={[
                  styles.infoLabel,
                  { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                ]}>
                  Community
                </Text>
                <Text style={[
                  styles.infoValue,
                  { color: isDark ? Colors.gray[200] : Colors.text }
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
    backgroundColor: Colors.primary,
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 16,
    marginBottom: 12,
  },
  eventTypeText: {
    color: Colors.white,
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