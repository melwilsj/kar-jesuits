import React from 'react';
import { 
  DrawerContentScrollView,
  DrawerItemList,
  DrawerItem,
  DrawerContentComponentProps 
} from '@react-navigation/drawer';
import { View, Text, Image, StyleSheet, TouchableOpacity } from 'react-native';
import { useAuth } from '@/hooks/useAuth';
import { router } from 'expo-router';
import Colors from '@/constants/Colors';
import { useColorScheme } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';

export default function CustomDrawer(props: DrawerContentComponentProps) {
  const { currentJesuit, logout } = useAuth();
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const defaultImage = 'https://placehold.co/600x400.png';

  // Define which routes should be visible in the drawer
  const visibleRoutes = ['home', 'filter/index', 'space', 'settings'];

  // Filter out any undefined routes and only include visible routes
  const filteredProps = {
    ...props,
    state: {
      ...props.state,
      routes: props.state.routes
        .filter(route => route !== undefined)
        .filter(route => {
          const routeName = route.name.replace('(app)/', '');
          return visibleRoutes.some(visibleRoute => 
            routeName === visibleRoute || 
            routeName.startsWith(visibleRoute + '/')
          );
        }),
    },
  };

  // Also ensure indexes are consistent
  filteredProps.state.index = Math.min(
    filteredProps.state.index, 
    filteredProps.state.routes.length - 1
  );

  const handleLogout = async () => {
    try {
      await logout();
      router.replace('/(auth)/login');
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  React.useEffect(() => {
    // Check if any route is undefined or missing a key property
    props.state.routes.forEach((route, index) => {
      if (!route) {
        console.warn(`Route at index ${index} is undefined`);
      } else if (!route.key) {
        console.warn(`Route at index ${index} is missing a key property`, route);
      }
    });
  }, [props.state.routes]);

  return (
    <DrawerContentScrollView 
      {...filteredProps}
      style={[
        styles.container,
        { backgroundColor: isDark ? Colors.gray[900] : Colors.background }
      ]}
    >
      <TouchableOpacity 
        style={styles.profileSection}
        onPress={() => router.push('/profile/me')}
      >
        <Image 
          source={{ uri: currentJesuit?.photo_url || defaultImage }} 
          style={styles.profileImage}
        />
        <View style={styles.profileInfo}>
          <Text style={[
            styles.name, 
            { color: isDark ? Colors.gray[100] : Colors.text }
          ]}>
            {currentJesuit?.name}
          </Text>
          <Text style={[
            styles.community,
            { color: isDark ? Colors.gray[300] : Colors.gray[600] }
          ]}>
            {currentJesuit?.current_community}
          </Text>
        </View>
      </TouchableOpacity>

      {/* Use the filtered props */}
      <DrawerItemList {...filteredProps} />

      <TouchableOpacity 
        style={styles.logoutButton}
        onPress={handleLogout}
      >
        <MaterialIcons 
          name="logout" 
          size={24} 
          color={isDark ? Colors.gray[100] : Colors.gray[900]} 
        />
        <Text style={[
          styles.logoutText,
          { color: isDark ? Colors.gray[100] : Colors.gray[900] }
        ]}>
          Logout
        </Text>
      </TouchableOpacity>
    </DrawerContentScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  profileSection: {
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: Colors.gray[200],
    marginBottom: 8,
    alignItems: 'center',
  },
  profileImage: {
    width: 64,
    height: 64,
    borderRadius: 32,
    marginBottom: 12,
  },
  profileInfo: {
    alignItems: 'center',
  },
  name: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 4,
    textAlign: 'center',
  },
  community: {
    fontSize: 14,
    textAlign: 'center',
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    marginTop: 'auto',
    borderTopWidth: 1,
    borderTopColor: Colors.gray[200],
  },
  logoutText: {
    marginLeft: 32,
    fontSize: 16,
    fontWeight: '500',
  },
});
