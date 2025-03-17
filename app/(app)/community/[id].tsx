import { View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import { useColorScheme } from 'react-native';
import { useCommunity } from '@/hooks/useDataUtils';
import { Jesuit } from '@/types/api';
import ScreenContainer from '@/components/ScreenContainer';

export default function CommunityScreen() {
  const { id } = useLocalSearchParams();
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const { community, communityMembers, loading } = useCommunity(Number(id));
  
  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <ActivityIndicator size="large" color={Colors.primary} />
      </View>
    );
  }

  if (!community) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <Text style={{ color: isDark ? Colors.gray[300] : Colors.gray[700] }}>
          Community not found
        </Text>
      </View>
    );
  }

  return (
    <ScreenContainer>
    <ScrollView
      style={[
        styles.container,
        { backgroundColor: isDark ? Colors.gray[900] : Colors.background }
      ]}
    >
      <View style={styles.headerContainer}>
        <Text style={[
          styles.title,
          { color: isDark ? Colors.gray[100] : Colors.text }
        ]}>
          {community.name}
        </Text>
        <Text style={styles.subtitle}>
          {community.is_formation_house ? 'Formation House' : 
          community.is_common_house ? 'Common House' : 
          community.is_attached_house ? 'Attached House' : 'Community'}
        </Text>
      </View>

      <View style={styles.detailsContainer}>
        <View style={styles.infoRow}>
          <MaterialIcons name="location-on" size={20} color={Colors.gray[600]} />
          <View style={styles.infoContent}>
            <Text style={styles.label}>Location</Text>
            <Text style={styles.value}>{community.address || 'Unknown location'}</Text>
          </View>
        </View>

        <View style={styles.infoRow}>
          <MaterialIcons name="church" size={20} color={Colors.gray[600]} />
          <View style={styles.infoContent}>
            <Text style={styles.label}>Diocese</Text>
            <Text style={styles.value}>{community.diocese || 'Unknown diocese'}</Text>
          </View>
        </View>

        <View style={styles.infoRow}>
          <MaterialIcons name="phone" size={20} color={Colors.gray[600]} />
          <View style={styles.infoContent}>
            <Text style={styles.label}>Contact</Text>
            <Text style={styles.value}>{community.phone || 'No contact information'}</Text>
          </View>
        </View>

        {community.email && (
          <View style={styles.infoRow}>
            <MaterialIcons name="email" size={20} color={Colors.gray[600]} />
            <View style={styles.infoContent}>
              <Text style={styles.label}>Email</Text>
              <Text style={styles.value}>{community.email}</Text>
            </View>
          </View>
        )}

        {/* Members section */}
        <View style={styles.membersSection}>
          <Text style={[
            styles.sectionTitle,
            { color: isDark ? Colors.gray[300] : Colors.gray[700] }
          ]}>
            Members ({communityMembers.length})
          </Text>
          
          {communityMembers.length > 0 ? (
            communityMembers.map((member: Jesuit)  => (
              <View 
                key={member.id}
                style={[
                  styles.memberItem,
                  { backgroundColor: isDark ? Colors.gray[800] : Colors.gray[100] }
                ]}
              >
                <TouchableOpacity 
                      key={member.id}
                      onPress={() => {
                        router.push(`/(app)/profile/${member.id}`);
                      }}
                    >
                <Text style={[
                  styles.memberName,
                  { color: isDark ? Colors.gray[100] : Colors.text }
                ]}>
                  {member.name}
                </Text>
                <Text style={styles.memberRole}>
                  {member.roles && member.roles.length > 0 ? member.roles[0].type : member.category}
                </Text>
                </TouchableOpacity>
              </View>
            ))
          ) : (
            <Text style={{ color: isDark ? Colors.gray[400] : Colors.gray[600] }}>
              No members found in this community
            </Text>
          )}
        </View>
      </View>
    </ScrollView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  centerContent: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerContainer: {
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: Colors.gray[200],
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 16,
    color: Colors.gray[500],
  },
  detailsContainer: {
    padding: 20,
  },
  infoRow: {
    flexDirection: 'row',
    marginBottom: 16,
    alignItems: 'flex-start',
  },
  infoContent: {
    marginLeft: 12,
    flex: 1,
  },
  label: {
    fontSize: 14,
    color: Colors.gray[500],
    marginBottom: 2,
  },
  value: {
    fontSize: 16,
    color: Colors.white,
  },
  membersSection: {
    marginTop: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    marginBottom: 12,
  },
  memberItem: {
    padding: 12,
    borderRadius: 8,
    marginBottom: 8,
  },
  memberName: {
    fontSize: 16,
    fontWeight: '500',
  },
  memberRole: {
    fontSize: 14,
    color: Colors.gray[500],
    marginTop: 4,
  },
});
