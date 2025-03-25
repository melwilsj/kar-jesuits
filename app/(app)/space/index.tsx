import React from "react";
import { View, StyleSheet, ScrollView, TouchableOpacity } from "react-native";
import ScreenContainer from '@/components/ScreenContainer';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import { router } from 'expo-router';
import { useColorScheme } from '@/hooks/useSettings';
import { Stack } from 'expo-router';
import ScaledText from '@/components/ScaledText';
import { useAuth } from '@/hooks/useAuth';

export default function Space() {
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const { currentJesuit } = useAuth();
  
  const spaceOptions = [
    { 
      id: 'documents', 
      title: 'Documents', 
      icon: 'folder',
      description: 'Store and access your personal documents',
      route: '/space/documents'
    },
    { 
      id: 'notifications', 
      title: 'Notifications', 
      icon: 'notifications',
      description: 'View all your notification history',
      route: '/space/notifications'
    },
    { 
      id: 'forms', 
      title: 'Submit Forms', 
      icon: 'description',
      description: 'Send corrections or requests to administrators',
      route: '/space/forms'
    },
    { 
      id: 'calendar', 
      title: 'Personal Calendar', 
      icon: 'event',
      description: 'View your schedule and community events',
      route: '/space/calendar'
    }
  ];

  return (
    <ScreenContainer>
      <Stack.Screen options={{ title: 'Personal Space' }} />
      
      <ScrollView style={styles.container}>
        <View style={styles.header}>
          <ScaledText style={[
            styles.greeting, 
            { color: isDark ? Colors.gray[100] : Colors.gray[800] }
          ]}>
            Hello, {currentJesuit?.name || 'User'}
          </ScaledText>
          <ScaledText style={[
            styles.subtitle, 
            { color: isDark ? Colors.gray[300] : Colors.gray[600] }
          ]}>
            Your personal space for documents and requests
          </ScaledText>
        </View>
        
        <View style={styles.optionsGrid}>
          {spaceOptions.map(option => (
            <TouchableOpacity
              key={option.id}
              style={[
                styles.optionCard,
                { backgroundColor: isDark ? Colors.gray[800] : Colors.white }
              ]}
              onPress={() => router.push(option.route as any)}
            >
              <View style={[
                styles.iconContainer, 
                { backgroundColor: isDark ? Colors.gray[700] : Colors.gray[100] }
              ]}>
                <MaterialIcons
                  name={option.icon as any}
                  size={24}
                  color={Colors.primary}
                />
              </View>
              <ScaledText style={[
                styles.optionTitle,
                { color: isDark ? Colors.white : Colors.gray[800] }
              ]}>
                {option.title}
              </ScaledText>
              <ScaledText style={[
                styles.optionDescription,
                { color: isDark ? Colors.gray[400] : Colors.gray[600] }
              ]}>
                {option.description}
              </ScaledText>
            </TouchableOpacity>
          ))}
        </View>
      </ScrollView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    padding: 16,
    marginBottom: 8,
  },
  greeting: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 16,
  },
  optionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 8,
  },
  optionCard: {
    width: '45%',
    marginHorizontal: '2.5%',
    marginBottom: 16,
    padding: 16,
    borderRadius: 12,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
  },
  iconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  optionTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 4,
  },
  optionDescription: {
    fontSize: 12,
    lineHeight: 16,
  },
});
