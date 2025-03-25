import React, { useState, useEffect } from "react";
import { View, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator } from "react-native";
import ScreenContainer from '@/components/ScreenContainer';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';
import { Stack } from 'expo-router';
import ScaledText from '@/components/ScaledText';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface CalendarEvent {
  id: string;
  title: string;
  description: string;
  location: string;
  startTime: number; // timestamp
  endTime: number; // timestamp
  type: 'community' | 'personal' | 'ministry';
  reminder: boolean;
}

export default function Calendar() {
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const [events, setEvents] = useState<CalendarEvent[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedDate, setSelectedDate] = useState(new Date());
  const [calendarDays, setCalendarDays] = useState<Date[]>([]);
  
  useEffect(() => {
    loadEvents();
  }, []);
  
  useEffect(() => {
    // Generate calendar days for the week view
    const days: Date[] = [];
    const firstDay = new Date(selectedDate);
    
    // Start with the previous Sunday
    firstDay.setDate(selectedDate.getDate() - selectedDate.getDay());
    
    // Generate 14 days (2 weeks)
    for (let i = 0; i < 14; i++) {
      const day = new Date(firstDay);
      day.setDate(firstDay.getDate() + i);
      days.push(day);
    }
    
    setCalendarDays(days);
  }, [selectedDate]);

  const loadEvents = async () => {
    try {
      setIsLoading(true);
      const stored = await AsyncStorage.getItem('calendar_events');
      if (stored) {
        setEvents(JSON.parse(stored));
      } else {
        // Create sample events if none exist
        const sampleEvents = generateSampleEvents();
        setEvents(sampleEvents);
        await AsyncStorage.setItem('calendar_events', JSON.stringify(sampleEvents));
      }
    } catch (error) {
      console.error('Error loading events:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const generateSampleEvents = (): CalendarEvent[] => {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    
    const nextWeek = new Date(today);
    nextWeek.setDate(today.getDate() + 7);
    
    return [
      {
        id: '1',
        title: 'Community Mass',
        description: 'Daily community celebration of the Eucharist',
        location: 'Main Chapel',
        startTime: new Date(today.setHours(7, 0, 0, 0)).getTime(),
        endTime: new Date(today.setHours(8, 0, 0, 0)).getTime(),
        type: 'community',
        reminder: true
      },
      {
        id: '2',
        title: 'Spiritual Direction',
        description: 'Monthly spiritual direction session with Fr. James',
        location: 'Spiritual Direction Room',
        startTime: new Date(today.setHours(14, 0, 0, 0)).getTime(),
        endTime: new Date(today.setHours(15, 0, 0, 0)).getTime(),
        type: 'personal',
        reminder: true
      },
      {
        id: '3',
        title: 'Parish Council Meeting',
        description: 'Monthly parish council meeting to discuss upcoming events',
        location: 'Parish Hall',
        startTime: new Date(tomorrow.setHours(19, 0, 0, 0)).getTime(),
        endTime: new Date(tomorrow.setHours(21, 0, 0, 0)).getTime(),
        type: 'ministry',
        reminder: true
      },
      {
        id: '4',
        title: 'Community Retreat Planning',
        description: 'Planning session for the upcoming community retreat',
        location: 'Conference Room',
        startTime: new Date(nextWeek.setHours(10, 0, 0, 0)).getTime(),
        endTime: new Date(nextWeek.setHours(12, 0, 0, 0)).getTime(),
        type: 'community',
        reminder: false
      }
    ];
  };
  
  const selectDay = (day: Date) => {
    setSelectedDate(day);
  };
  
  const isToday = (day: Date) => {
    const today = new Date();
    return day.getDate() === today.getDate() && 
           day.getMonth() === today.getMonth() && 
           day.getFullYear() === today.getFullYear();
  };
  
  const formatWeekday = (day: Date) => {
    return day.toLocaleDateString('en-US', { weekday: 'short' });
  };
  
  const getEventsForSelectedDate = () => {
    const startOfDay = new Date(selectedDate);
    startOfDay.setHours(0, 0, 0, 0);
    
    const endOfDay = new Date(selectedDate);
    endOfDay.setHours(23, 59, 59, 999);
    
    return events.filter(event => 
      (event.startTime >= startOfDay.getTime() && event.startTime <= endOfDay.getTime()) ||
      (event.endTime >= startOfDay.getTime() && event.endTime <= endOfDay.getTime()) ||
      (event.startTime <= startOfDay.getTime() && event.endTime >= endOfDay.getTime())
    );
  };
  
  const formatEventTime = (timestamp: number) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };
  
  const getEventTypeColor = (type: string) => {
    switch(type) {
      case 'community': return Colors.blue[500];
      case 'personal': return Colors.green[500];
      case 'ministry': return Colors.orange[500];
      default: return Colors.gray[500];
    }
  };
  
  const getEventTypeIcon = (type: string) => {
    switch(type) {
      case 'community': return 'people';
      case 'personal': return 'person';
      case 'ministry': return 'work';
      default: return 'event';
    }
  };
  
  const selectedDayEvents = getEventsForSelectedDate();
  const formattedDate = selectedDate.toLocaleDateString('en-US', { 
    weekday: 'long', 
    month: 'long', 
    day: 'numeric' 
  });

  return (
    <ScreenContainer>
      <Stack.Screen options={{ title: 'Calendar' }} />
      
      <View style={[
        styles.container,
        { backgroundColor: isDark ? Colors.gray[900] : Colors.background }
      ]}>
        {/* Calendar Header */}
        <View style={[
          styles.header,
          { backgroundColor: isDark ? Colors.gray[800] : Colors.gray[100] }
        ]}>
          <TouchableOpacity 
            style={styles.headerButton}
            onPress={() => {
              const prevMonth = new Date(selectedDate);
              prevMonth.setMonth(prevMonth.getMonth() - 1);
              setSelectedDate(prevMonth);
            }}
          >
            <MaterialIcons 
              name="chevron-left" 
              size={24} 
              color={isDark ? Colors.gray[300] : Colors.gray[600]} 
            />
          </TouchableOpacity>
          
          <ScaledText style={[
            styles.headerTitle,
            { color: isDark ? Colors.gray[200] : Colors.gray[800] }
          ]}>
            {selectedDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}
          </ScaledText>
          
          <TouchableOpacity 
            style={styles.headerButton}
            onPress={() => {
              const nextMonth = new Date(selectedDate);
              nextMonth.setMonth(nextMonth.getMonth() + 1);
              setSelectedDate(nextMonth);
            }}
          >
            <MaterialIcons 
              name="chevron-right" 
              size={24} 
              color={isDark ? Colors.gray[300] : Colors.gray[600]} 
            />
          </TouchableOpacity>
        </View>
        
        {/* Calendar Days */}
        <ScrollView 
          horizontal 
          showsHorizontalScrollIndicator={false}
          style={[
            styles.calendarScroll,
            { backgroundColor: isDark ? Colors.gray[900] : Colors.white }
          ]}
          contentContainerStyle={styles.calendarScrollContent}
        >
          {calendarDays.map((day, index) => (
            <TouchableOpacity
              key={index}
              style={[
                styles.dayItem,
                isToday(day) && styles.todayItem,
                day.getDate() === selectedDate.getDate() && 
                day.getMonth() === selectedDate.getMonth() && 
                styles.selectedDayItem,
                { 
                  backgroundColor: isDark 
                    ? (day.getDate() === selectedDate.getDate() && day.getMonth() === selectedDate.getMonth()
                        ? Colors.primary 
                        : isToday(day) 
                          ? Colors.gray[700] 
                          : 'transparent')
                    : (day.getDate() === selectedDate.getDate() && day.getMonth() === selectedDate.getMonth()
                        ? Colors.primary + '20'
                        : isToday(day) 
                          ? Colors.gray[200] 
                          : 'transparent')
                }
              ]}
              onPress={() => selectDay(day)}
            >
              <ScaledText style={[
                styles.weekdayText,
                { 
                  color: isDark 
                    ? (day.getDate() === selectedDate.getDate() && day.getMonth() === selectedDate.getMonth()
                        ? Colors.white 
                        : Colors.gray[400])
                    : (day.getDate() === selectedDate.getDate() && day.getMonth() === selectedDate.getMonth()
                        ? Colors.primary
                        : Colors.gray[600])
                }
              ]}>
                {formatWeekday(day)}
              </ScaledText>
              
              <View style={[
                styles.dateCircle,
                isToday(day) && styles.todayCircle,
                day.getDate() === selectedDate.getDate() && 
                day.getMonth() === selectedDate.getMonth() && 
                styles.selectedDateCircle,
                { 
                  backgroundColor: isDark 
                    ? (day.getDate() === selectedDate.getDate() && day.getMonth() === selectedDate.getMonth()
                        ? Colors.white 
                        : isToday(day) 
                          ? Colors.primary 
                          : Colors.gray[800])
                    : (day.getDate() === selectedDate.getDate() && day.getMonth() === selectedDate.getMonth()
                        ? Colors.primary
                        : isToday(day) 
                          ? Colors.primary + '20' 
                          : Colors.gray[200])
                }
              ]}>
                <ScaledText style={[
                  styles.dateText,
                  { 
                    color: isDark 
                      ? (day.getDate() === selectedDate.getDate() && day.getMonth() === selectedDate.getMonth()
                          ? Colors.primary
                          : isToday(day) 
                            ? Colors.white 
                            : Colors.gray[400])
                      : (day.getDate() === selectedDate.getDate() && day.getMonth() === selectedDate.getMonth()
                          ? Colors.white
                          : isToday(day) 
                            ? Colors.primary 
                            : Colors.gray[800])
                  }
                ]}>
                  {day.getDate()}
                </ScaledText>
              </View>
              
              {/* Event indicator */}
              {events.some(event => {
                const eventDate = new Date(event.startTime);
                return eventDate.getDate() === day.getDate() && 
                       eventDate.getMonth() === day.getMonth() &&
                       eventDate.getFullYear() === day.getFullYear();
              }) && (
                <View style={[
                  styles.eventIndicator,
                  { backgroundColor: isDark ? Colors.primary : Colors.primary }
                ]} />
              )}
            </TouchableOpacity>
          ))}
        </ScrollView>
        
        {/* Selected Day Events */}
        <View style={styles.eventsContainer}>
          <ScaledText style={[
            styles.selectedDateText,
            { color: isDark ? Colors.gray[200] : Colors.gray[800] }
          ]}>
            {formattedDate}
          </ScaledText>
          
          {isLoading ? (
            <View style={styles.loadingContainer}>
              <ActivityIndicator color={Colors.primary} size="large" />
            </View>
          ) : selectedDayEvents.length > 0 ? (
            <ScrollView style={styles.eventsList}>
              {selectedDayEvents.map(event => (
                <View 
                  key={event.id} 
                  style={[
                    styles.eventItem,
                    { backgroundColor: isDark ? Colors.gray[800] : Colors.white }
                  ]}
                >
                  <View style={[
                    styles.eventTimeBar,
                    { backgroundColor: getEventTypeColor(event.type) }
                  ]} />
                  
                  <View style={styles.eventContent}>
                    <View style={styles.eventHeader}>
                      <View style={[
                        styles.eventIconContainer,
                        { backgroundColor: getEventTypeColor(event.type) + '20' }
                      ]}>
                        <MaterialIcons 
                          name={getEventTypeIcon(event.type)} 
                          size={18} 
                          color={getEventTypeColor(event.type)} 
                        />
                      </View>
                      
                      <ScaledText 
                        style={[
                          styles.eventTitle,
                          { color: isDark ? Colors.white : Colors.gray[900] }
                        ]}
                        numberOfLines={1}
                      >
                        {event.title}
                      </ScaledText>
                    </View>
                    
                    <ScaledText style={[
                      styles.eventTime,
                      { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                    ]}>
                      {formatEventTime(event.startTime)} - {formatEventTime(event.endTime)}
                    </ScaledText>
                    
                    {event.location && (
                      <View style={styles.eventDetailRow}>
                        <MaterialIcons 
                          name="location-on" 
                          size={16} 
                          color={isDark ? Colors.gray[500] : Colors.gray[600]} 
                          style={styles.eventDetailIcon}
                        />
                        <ScaledText 
                          style={[
                            styles.eventLocation,
                            { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                          ]}
                          numberOfLines={1}
                        >
                          {event.location}
                        </ScaledText>
                      </View>
                    )}
                    
                    {event.description && (
                      <ScaledText 
                        style={[
                          styles.eventDescription,
                          { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                        ]}
                        numberOfLines={2}
                      >
                        {event.description}
                      </ScaledText>
                    )}
                  </View>
                </View>
              ))}
            </ScrollView>
          ) : (
            <View style={styles.emptyContainer}>
              <MaterialIcons 
                name="event-busy" 
                size={48} 
                color={isDark ? Colors.gray[600] : Colors.gray[400]} 
              />
              <ScaledText style={[
                styles.emptyText,
                { color: isDark ? Colors.gray[400] : Colors.gray[600] }
              ]}>
                No events for this day
              </ScaledText>
            </View>
          )}
        </View>
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
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderBottomWidth: 1,
    borderBottomColor: Colors.gray[300],
  },
  headerButton: {
    padding: 8,
  },
  headerTitle: {
    fontSize: 16,
    fontWeight: '600',
  },
  calendarScroll: {
    maxHeight: 90,
    borderBottomWidth: 1,
    borderBottomColor: Colors.gray[300],
  },
  calendarScrollContent: {
    paddingVertical: 8,
    paddingHorizontal: 4,
  },
  dayItem: {
    width: 60,
    height: 74,
    justifyContent: 'center',
    alignItems: 'center',
    marginHorizontal: 4,
    borderRadius: 8,
  },
  todayItem: {
    borderWidth: 1,
    borderColor: Colors.primary,
  },
  selectedDayItem: {
    // Styles applied inline due to color scheme
  },
  weekdayText: {
    fontSize: 12,
    marginBottom: 4,
  },
  dateCircle: {
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 4,
  },
  todayCircle: {
    // Styles applied inline due to color scheme
  },
  selectedDateCircle: {
    // Styles applied inline due to color scheme
  },
  dateText: {
    fontSize: 14,
    fontWeight: '600',
  },
  eventIndicator: {
    width: 4,
    height: 4,
    borderRadius: 2,
    backgroundColor: Colors.primary,
  },
  eventsContainer: {
    flex: 1,
    padding: 16,
  },
  selectedDateText: {
    fontSize: 20,
    fontWeight: '600',
    marginBottom: 16,
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
  },
  emptyText: {
    fontSize: 16,
    marginTop: 16,
  },
  eventsList: {
    flex: 1,
  },
  eventItem: {
    flexDirection: 'row',
    borderRadius: 8,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 1,
    elevation: 1,
    overflow: 'hidden',
  },
  eventTimeBar: {
    width: 4,
  },
  eventContent: {
    flex: 1,
    padding: 12,
  },
  eventHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 6,
  },
  eventIconContainer: {
    width: 28,
    height: 28,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 8,
  },
  eventTitle: {
    fontSize: 16,
    fontWeight: '600',
    flex: 1,
  },
  eventTime: {
    fontSize: 14,
    marginBottom: 6,
  },
  eventDetailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 4,
  },
  eventDetailIcon: {
    marginRight: 4,
  },
  eventLocation: {
    fontSize: 14,
    flex: 1,
  },
  eventDescription: {
    fontSize: 14,
    marginTop: 6,
  },
}); 