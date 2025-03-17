import { View, StyleSheet, Keyboard } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { DrawerActions } from '@react-navigation/native';
import { useNavigation } from 'expo-router';
import { usePathname } from 'expo-router';
import SearchBar from './SearchBar';
import { useColorScheme } from 'react-native';
import Colors from '@/constants/Colors';
import { useState, useEffect, useRef } from 'react';
import SearchResultsDropdown from './SearchResultsDropdown';
import { TextInput } from 'react-native';

export default function SearchHeader() {
  const navigation = useNavigation();
  const pathname = usePathname();
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const [isSearchVisible, setIsSearchVisible] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const searchInputRef = useRef<TextInput>(null);
  const lastPathRef = useRef(pathname);

  // Handle keyboard dismiss to close search results
  useEffect(() => {
    const keyboardDidHideListener = Keyboard.addListener(
      'keyboardDidHide',
      () => {
        if (searchQuery.length === 0) {
          setIsSearchVisible(false);
        }
      }
    );

    return () => {
      keyboardDidHideListener.remove();
    };
  }, [searchQuery]);

  // Watch for path changes to detect navigation
  useEffect(() => {
    // If path changed, we navigated
    if (pathname !== lastPathRef.current) {
      if (searchInputRef.current) {
        // Explicitly blur the input
        searchInputRef.current.blur();
        // Dismiss keyboard and hide search results
        Keyboard.dismiss();
        setIsSearchVisible(false);
        // Clear the search query when navigating away
        setSearchQuery('');
      }
      // Update the last path
      lastPathRef.current = pathname;
    }
  }, [pathname]);

  const handleSearchFocus = () => {
    setIsSearchVisible(true);
  };

  const handleSearchChange = (text: string) => {
    setSearchQuery(text);
    // Show results as soon as user starts typing
    if (!isSearchVisible && text.length > 0) {
      setIsSearchVisible(true);
    }
  };

  const handleCloseSearch = () => {
    // Also blur the input when closing search
    if (searchInputRef.current) {
      searchInputRef.current.blur();
      Keyboard.dismiss();
    }
    setIsSearchVisible(false);
  };

  return (
    <View style={styles.container}>
      <MaterialIcons 
        name="menu" 
        size={24} 
        color={isDark ? Colors.gray[100] : Colors.gray[900]}
        onPress={() => navigation.dispatch(DrawerActions.openDrawer())}
        style={styles.menuIcon}
      />
      <SearchBar 
        onFocus={handleSearchFocus}
        onChangeText={handleSearchChange}
        value={searchQuery}
        ref={searchInputRef}
      />
      
      {/* Search Results Dropdown */}
      <SearchResultsDropdown 
        visible={isSearchVisible}
        onClose={handleCloseSearch}
        searchQuery={searchQuery}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    width: '100%',
    paddingVertical: 8,
    paddingHorizontal: 16,
    zIndex: 100, // Ensure the container is above other elements
  },
  menuIcon: {
    marginRight: 12,
  },
});
