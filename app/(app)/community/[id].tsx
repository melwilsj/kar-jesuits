import { View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity } from 'react-native';
import { router, useLocalSearchParams } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';
import { useCommunity } from '@/hooks/useDataUtils';
import { Jesuit } from '@/types/api';
import ScreenContainer from '@/components/ScreenContainer';

export default function CommunityScreen() {
  const { id } = useLocalSearchParams();
  const colorScheme = useColorScheme();
  const { community, communityMembers, loading } = useCommunity(Number(id));
  
  if (loading) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <ActivityIndicator size="large" color={Colors[`${colorScheme}`].primary} />
      </View>
    );
  }

  if (!community) {
    return (
      <View style={[styles.container, styles.centerContent]}>
        <Text style={{ color: Colors[`${colorScheme}`].text }}>
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
        { backgroundColor: Colors[`${colorScheme}`].background }
      ]}
    >
      <View style={styles.headerContainer}>
        <Text style={[
          styles.title,
          { color: Colors[`${colorScheme}`].text }
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
          <MaterialIcons name="location-on" size={20} color={Colors[`${colorScheme}`].gray600} />
          <View style={styles.infoContent}>
            <Text style={styles.label}>Location</Text>
            <Text style={{ color: Colors[`${colorScheme}`].text }}>{community.address || 'Unknown location'}</Text>
          </View>
        </View>

        <View style={styles.infoRow}>
          <MaterialIcons name="church" size={20} color={Colors[`${colorScheme}`].gray600} />
          <View style={styles.infoContent}>
            <Text style={styles.label}>Diocese</Text>
            <Text style={{ color: Colors[`${colorScheme}`].text }}>{community.diocese || 'Unknown diocese'}</Text>
          </View>
        </View>

        <View style={styles.infoRow}>
          <MaterialIcons name="phone" size={20} color={Colors[`${colorScheme}`].gray600} />
          <View style={styles.infoContent}>
            <Text style={styles.label}>Contact</Text>
            <Text style={{ color: Colors[`${colorScheme}`].text }}>{community.phone || 'No contact information'}</Text>
          </View>
        </View>

        {community.email && (
          <View style={styles.infoRow}>
            <MaterialIcons name="email" size={20} color={Colors[`${colorScheme}`].gray600} />
            <View style={styles.infoContent}>
              <Text style={styles.label}>Email</Text>
              <Text style={{ color: Colors[`${colorScheme}`].text }}>{community.email}</Text>
            </View>
          </View>
        )}

        {/* Members section */}
        <View style={styles.membersSection}>
          <Text style={[
            styles.sectionTitle,
            { color: Colors[`${colorScheme}`].text }
          ]}>
            Members ({communityMembers.length})
          </Text>
          
          {communityMembers.length > 0 ? (
            communityMembers.map((member: Jesuit)  => (
              <View 
                key={member.id}
                style={[
                  styles.memberItem,
                  { backgroundColor: Colors[`${colorScheme}`].background,
                    borderColor: Colors[`${colorScheme}`].border,
                    borderWidth: 2,
                  }
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
                  { color: Colors[`${colorScheme}`].text }
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
            <Text style={{ color: Colors[`${colorScheme}`].textSecondary }}>
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
    borderBottomColor: Color.gray[200],
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 16,
    color: Color.gray[500],
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
    color: Color.gray[500],
    marginBottom: 2,
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
    color: Color.gray[500],
    marginTop: 4,
  },
});
