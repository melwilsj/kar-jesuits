import React, { useEffect, useState } from 'react';
import { 
  View, 
  Text, 
  StyleSheet, 
  ScrollView, 
  TouchableOpacity, 
  Switch, 
  ActivityIndicator,
  Dimensions,
  Animated,
  TouchableWithoutFeedback
} from 'react-native';
import Colors from '@/constants/Colors';
import { useColorScheme } from 'react-native';
import { useDataSync } from '@/hooks/useDataSync';
import { Community } from '@/types/api';
import { router } from 'expo-router';

interface SearchResultsDropdownProps {
  visible: boolean;
  onClose: () => void;
  searchQuery: string;
}

export default function SearchResultsDropdown({ 
  visible, 
  onClose, 
  searchQuery
}: SearchResultsDropdownProps) {
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const [includeRegionProvince, setIncludeRegionProvince] = useState(false);
  const { isLoading, error, members, communities, currentJesuit } = useDataSync();
  const [dropdownHeight] = useState(new Animated.Value(0));
  const handleToggle = () => {
    setIncludeRegionProvince(!includeRegionProvince);
  };
  
  // Animate dropdown height when visibility changes
  useEffect(() => {
    // Use a larger percentage of screen height to ensure enough room for scrolling
    const maxHeight = Dimensions.get('window').height * 0.85;
    
    Animated.timing(dropdownHeight, {
      toValue: visible ? maxHeight : 0,
      duration: 300,
      useNativeDriver: false,
    }).start();
  }, [visible]);

  if (!visible) return null;

  // Filter Jesuits based on improved region/province logic
  const filteredMembers = members?.filter(member => {
    const matchesSearch = 
      member.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      member.code.toLowerCase().includes(searchQuery.toLowerCase());

    if (!matchesSearch) return false;
    
    // Include region logic
    if (includeRegionProvince) {
      // Show all matching members regardless of province_only status
      return true;
    } else {
      // If current user is province-only (region is null), show province-only members
      if (currentJesuit && currentJesuit.region === null) {
        return member.province_only === true;
      } else {
        // If current user has a region, show non-province-only members
        return member.province_only === false;
      }
    }
  }) || [];

  // Filter Communities with the same region/province logic - fixed the logic bug
  const filteredCommunities = communities?.filter((community: Community) => {
    const matchesSearch = 
      community.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      community.code.toLowerCase().includes(searchQuery.toLowerCase());

    if (!matchesSearch) return false;
    
    if (includeRegionProvince) {
      // Show all matching communities
      return true;
    } else {
      if (currentJesuit && currentJesuit.region === null) {
        // If current user has no region (is province-only), show province-only communities
        return community.province_only === true;
      } else {
        // If current user has a region, show non-province-only communities
        return community.province_only === false;
      }
    }
  }) || [];

  return (
    <Animated.View 
      style={[
        styles.dropdown,
        { 
          height: dropdownHeight,
          backgroundColor: isDark ? Colors.gray[900] : Colors.background,
        }
      ]}
    >
      {/* Fixed Header with Toggle */}
      <View style={styles.toggleContainer}>
        <Text style={[
          styles.toggleLabel,
          { color: isDark ? Colors.gray[300] : Colors.gray[700] }
        ]}>Include Region/Province Members</Text>
        
        <TouchableWithoutFeedback
          onPress={handleToggle}
          hitSlop={{ top: 20, bottom: 20, left: 300, right: 20 }}
        >
          <View>
            <Switch
              value={includeRegionProvince}
              onValueChange={handleToggle}
              trackColor={{ false: Colors.gray[300], true: Colors.primary }}
              thumbColor={isDark ? Colors.gray[100] : '#fff'}
            />
          </View>
        </TouchableWithoutFeedback>
      </View>

      {isLoading ? (
        <View style={styles.centerContent}>
          <ActivityIndicator size="large" color={Colors.primary} />
        </View>
      ) : error ? (
        <View style={styles.centerContent}>
          <Text style={styles.errorText}>{error}</Text>
        </View>
      ) : (
        <ScrollView 
          style={styles.mainScrollContainer}
          keyboardShouldPersistTaps="handled"
          contentContainerStyle={styles.mainScrollContent}
          showsVerticalScrollIndicator={true}
        >
          <View style={styles.horizontalSections}>
            {/* Jesuits Section */}
            <View style={styles.columnSection}>
              <Text style={[
                styles.sectionTitle,
                { color: isDark ? Colors.gray[300] : Colors.gray[700] }
              ]}>Jesuits ({filteredMembers.length})</Text>
              
              <ScrollView 
                style={styles.sectionScroll}
                keyboardShouldPersistTaps="handled"
                showsVerticalScrollIndicator={true}
                nestedScrollEnabled={true}
              >
                {filteredMembers.map(member => (
                  <TouchableOpacity 
                    key={member.id}
                    style={[
                      styles.resultItem,
                      { backgroundColor: isDark ? Colors.gray[800] : Colors.gray[100] }
                    ]}
                    onPress={() => {
                      router.push(`/(app)/profile/${member.id}`);
                      onClose();
                    }}
                  >
                    <Text 
                      style={[
                        styles.itemTitle,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}
                      numberOfLines={1} // Add ellipsis
                      ellipsizeMode="tail"
                    >
                     {member.roles.length > 0 ? member.roles[0].type : member.category} {member.name}
                    </Text>
                  </TouchableOpacity>
                ))}
              </ScrollView>
            </View>

            {/* Communities Section */}
            <View style={styles.columnSection}>
              <Text style={[
                styles.sectionTitle,
                { color: isDark ? Colors.gray[300] : Colors.gray[700] }
              ]}>Communities ({filteredCommunities.length})</Text>
              
              <ScrollView 
                style={styles.sectionScroll}
                keyboardShouldPersistTaps="handled"
                showsVerticalScrollIndicator={true}
                nestedScrollEnabled={true}
              >
                {filteredCommunities.map(community => (
                  <TouchableOpacity 
                    key={community.id}
                    style={[
                      styles.resultItem,
                      { backgroundColor: isDark ? Colors.gray[800] : Colors.gray[100] }
                    ]}
                    onPress={() => {
                      router.push(`/(app)/community/${community.id}`);
                      onClose();
                    }}
                  >
                    <Text 
                      style={[
                        styles.itemTitle,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}
                      numberOfLines={1} // Add ellipsis
                      ellipsizeMode="tail"
                    >
                      {community.name}
                    </Text>
                    <Text 
                      style={styles.itemDetails}
                      numberOfLines={1} // Add ellipsis
                      ellipsizeMode="tail"
                    >
                      {community.diocese}
                    </Text>
                  </TouchableOpacity>
                ))}
              </ScrollView>
            </View>
          </View>
        </ScrollView>
      )}
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  dropdown: {
    position: 'absolute',
    top: 50, // Position below header
    left: 0,
    right: 0,
    zIndex: 1000,
    elevation: 5,
    borderTopLeftRadius: 12,
    borderTopRightRadius: 12,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    maxHeight: Dimensions.get('window').height * 0.85,
  },
  toggleContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: Colors.gray[200],
  },
  toggleLabel: {
    fontSize: 14,
    fontWeight: '500',
  },
  mainScrollContainer: {
    flex: 1,
  },
  mainScrollContent: {
    paddingBottom: 20, // Add space at the bottom
  },
  horizontalSections: {
    flexDirection: 'row',
    padding: 10,
  },
  columnSection: {
    flex: 1,
    marginHorizontal: 5,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
    marginLeft: 5,
  },
  sectionScroll: {
    flex: 1,
    maxHeight: 400, // Limit initial height, but can scroll
  },
  resultItem: {
    padding: 12,
    borderRadius: 8,
    marginBottom: 8,
  },
  itemTitle: {
    fontSize: 16,
    fontWeight: '500',
  },
  itemSubtitle: {
    fontSize: 14,
    color: Colors.gray[500],
    marginTop: 4,
  },
  itemDetails: {
    fontSize: 12,
    color: Colors.gray[500],
    marginTop: 2,
  },
  centerContent: {
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
    minHeight: 200,
  },
  errorText: {
    color: Colors.error,
    textAlign: 'center',
  },
}); 