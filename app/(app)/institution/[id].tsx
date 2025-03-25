import React from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity } from 'react-native';
import { useLocalSearchParams, Stack, useRouter } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import Colors from '@/constants/Colors';
import { useColorScheme } from 'react-native';
import { useInstitution } from '@/hooks/useDataUtils';
import ScreenContainer from '@/components/ScreenContainer';

export default function InstitutionScreen() {
  const { id } = useLocalSearchParams();
  const router = useRouter();
  const colorScheme = useColorScheme();
  const isDark = colorScheme === 'dark';
  const { institution, institutionJesuits, loading } = useInstitution(Number(id));
  
  if (loading) {
    return (
      <ScreenContainer>
        <View style={[styles.container, styles.centerContent]}>
          <ActivityIndicator size="large" color={Colors.primary} />
        </View>
      </ScreenContainer>
    );
  }

  if (!institution) {
    return (
      <ScreenContainer>
        <View style={[styles.container, styles.centerContent]}>
          <Text style={{ color: isDark ? Colors.gray[300] : Colors.gray[700] }}>
            Institution not found
          </Text>
        </View>
      </ScreenContainer>
    );
  }

  // Helper function to get icon based on institution type
  const getInstitutionTypeIcon = () => {
    switch (institution.type) {
      case 'school':
      case 'college':
      case 'university':
      case 'hostel':
      case 'community_college':
      case 'iti':
        return 'school';
      case 'social_center':
      case 'ngo':
        return 'people';
      case 'parish':
        return 'church';
      case 'retreat_center':
        return 'location-city';
      case 'farm':
        return 'terrain';
      default:
        return 'business';
    }
  };

  // Helper function to get formatted type name
  const getInstitutionTypeName = () => {
    switch (institution.type) {
      case 'school':
      case 'college':
      case 'university':
      case 'hostel':
      case 'community_college':
      case 'iti':
        return 'Educational Institution';
      case 'social_center':
      case 'ngo':
        return 'Social Center';
      case 'parish':
        return 'Parish';
      case 'retreat_center':
        return 'Retreat Center';
      case 'farm':
        return 'Farm';
      default:
        return 'Other Institution';
    }
  };

  return (
    <ScreenContainer>
      <Stack.Screen options={{ 
        title: institution.name,
        headerBackVisible: true
      }} />

      <ScrollView
        style={styles.container}
        contentContainerStyle={styles.contentContainer}
      >
        {/* Header Card */}
        <View style={[
          styles.card,
          { backgroundColor: isDark ? Colors.gray[800] : Colors.white }
        ]}>
          <Text style={[
            styles.title,
            { color: isDark ? Colors.gray[100] : Colors.text }
          ]}>
            {institution.name}
          </Text>
          
          <View style={styles.typeChip}>
            <MaterialIcons 
              name={getInstitutionTypeIcon()} 
              size={16} 
              color={Colors.white} 
            />
            <Text style={styles.typeChipText}>
              {getInstitutionTypeName()}
            </Text>
          </View>
        </View>

        {/* Location & Contact Card */}
        <View style={[
          styles.card,
          { backgroundColor: isDark ? Colors.gray[800] : Colors.white }
        ]}>
          <Text style={[
            styles.cardTitle,
            { color: isDark ? Colors.gray[200] : Colors.gray[800] }
          ]}>
            Location & Contact
          </Text>
          
          {institution.diocese && (
            <View style={styles.infoRow}>
              <MaterialIcons 
                name="location-city" 
                size={20} 
                color={Colors.primary} 
                style={styles.infoIcon}
              />
              <View>
                <Text style={[
                  styles.infoLabel,
                  { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                ]}>
                  Diocese
                </Text>
                <Text style={[
                  styles.infoValue,
                  { color: isDark ? Colors.gray[100] : Colors.text }
                ]}>
                  {institution.diocese}
                </Text>
              </View>
            </View>
          )}

          {institution.address && (
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
                  Address
                </Text>
                <Text style={[
                  styles.infoValue,
                  { color: isDark ? Colors.gray[100] : Colors.text }
                ]}>
                  {institution.address}
                  {institution.district && `, ${institution.district}`}
                  {institution.state && `, ${institution.state}`}
                </Text>
              </View>
            </View>
          )}

          {institution.contact_details && (
            <View style={styles.infoRow}>
              <MaterialIcons 
                name="call" 
                size={20} 
                color={Colors.primary} 
                style={styles.infoIcon}
              />
              <View style={styles.infoContent}>
                <Text style={[
                  styles.infoLabel,
                  { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                ]}>
                  Contact
                </Text>
                {institution.contact_details.phones && institution.contact_details.phones.length > 0 && (
                  <Text style={[
                    styles.infoValue,
                    { color: isDark ? Colors.gray[100] : Colors.text }
                  ]}>
                    {institution.contact_details.phones.join(', ')}
                  </Text>
                )}
                {institution.contact_details.emails && institution.contact_details.emails.length > 0 && (
                  <Text style={[
                    styles.infoValue,
                    { color: isDark ? Colors.gray[100] : Colors.text, marginTop: 2 }
                  ]}>
                    {institution.contact_details.emails.join(', ')}
                  </Text>
                )}
                {institution.contact_details.website && (
                  <Text style={[
                    styles.infoValue,
                    { color: Colors.primary, marginTop: 2, textDecorationLine: 'underline' }
                  ]}>
                    {institution.contact_details.website}
                  </Text>
                )}
              </View>
            </View>
          )}

          {institution.community && (
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
                  Associated Community
                </Text>
                <TouchableOpacity onPress={() => router.push(`/community/${institution.community?.id}`)}>
                  <Text style={[
                    styles.infoValue,
                    { color: Colors.primary, textDecorationLine: 'underline' }
                  ]}>
                    {institution.community.name}
                  </Text>
                </TouchableOpacity>
              </View>
            </View>
          )}
        </View>

        {/* Demographics Card */}
        {(institution.student_demographics || institution.staff_demographics) && (
          <View style={[
            styles.card,
            { backgroundColor: isDark ? Colors.gray[800] : Colors.white }
          ]}>
            <Text style={[
              styles.cardTitle,
              { color: isDark ? Colors.gray[200] : Colors.gray[800] }
            ]}>
              Demographics
            </Text>
            
            {institution.student_demographics && (
              <View style={styles.demographicsSection}>
                <View style={styles.demographicsHeader}>
                  <MaterialIcons name="school" size={18} color={Colors.primary} />
                  <Text style={[
                    styles.demographicsSectionTitle,
                    { color: isDark ? Colors.gray[200] : Colors.gray[800] }
                  ]}>
                    Students
                  </Text>
                </View>

                <View style={styles.demographicsGrid}>
                  <View style={styles.demographicsItem}>
                    <Text style={[
                      styles.demographicsValue,
                      { color: isDark ? Colors.gray[100] : Colors.text }
                    ]}>
                      {institution.student_demographics.boys + institution.student_demographics.girls}
                    </Text>
                    <Text style={[
                      styles.demographicsLabel,
                      { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                    ]}>
                      Total
                    </Text>
                  </View>
                  
                  {institution.student_demographics.boys !== undefined && (
                    <View style={styles.demographicsItem}>
                      <Text style={[
                        styles.demographicsValue,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}>
                        {institution.student_demographics.boys}
                      </Text>
                      <Text style={[
                        styles.demographicsLabel,
                        { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                      ]}>
                        Boys
                      </Text>
                    </View>
                  )}
                  
                  {institution.student_demographics.girls !== undefined && (
                    <View style={styles.demographicsItem}>
                      <Text style={[
                        styles.demographicsValue,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}>
                        {institution.student_demographics.girls}
                      </Text>
                      <Text style={[
                        styles.demographicsLabel,
                        { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                      ]}>
                        Girls
                      </Text>
                    </View>
                  )}
                </View>

                <View style={styles.demographicsGrid}>
                  {institution.student_demographics.catholics !== undefined && (
                    <View style={styles.demographicsItem}>
                      <Text style={[
                        styles.demographicsValue,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}>
                        {institution.student_demographics.catholics}
                      </Text>
                      <Text style={[
                        styles.demographicsLabel,
                        { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                      ]}>
                        Catholics
                      </Text>
                    </View>
                  )}
                  
                  {institution.student_demographics.other_christians !== undefined && (
                    <View style={styles.demographicsItem}>
                      <Text style={[
                        styles.demographicsValue,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}>
                        {institution.student_demographics.other_christians}
                      </Text>
                      <Text style={[
                        styles.demographicsLabel,
                        { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                      ]}>
                        Other Christians
                      </Text>
                    </View>
                  )}
                  
                  {institution.student_demographics.non_christians !== undefined && (
                    <View style={styles.demographicsItem}>
                      <Text style={[
                        styles.demographicsValue,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}>
                        {institution.student_demographics.non_christians}
                      </Text>
                      <Text style={[
                        styles.demographicsLabel,
                        { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                      ]}>
                        Non-Christians
                      </Text>
                    </View>
                  )}
                </View>
              </View>
            )}
            
            {institution.staff_demographics && (
              <View style={styles.demographicsSection}>
                <View style={styles.demographicsHeader}>
                  <MaterialIcons name="people" size={18} color={Colors.primary} />
                  <Text style={[
                    styles.demographicsSectionTitle, 
                    { color: isDark ? Colors.gray[200] : Colors.gray[800] }
                  ]}>
                    Staff
                  </Text>
                </View>

                <View style={styles.demographicsGrid}>
                  <View style={styles.demographicsItem}>
                    <Text style={[
                      styles.demographicsValue,
                      { color: isDark ? Colors.gray[100] : Colors.text }
                    ]}>
                      {institution.staff_demographics.total}
                    </Text>
                    <Text style={[
                      styles.demographicsLabel,
                      { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                    ]}>
                      Total
                    </Text>
                  </View>
                  
                  {institution.staff_demographics.jesuits !== undefined && (
                    <View style={styles.demographicsItem}>
                      <Text style={[
                        styles.demographicsValue,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}>
                        {institution.staff_demographics.jesuits}
                      </Text>
                      <Text style={[
                        styles.demographicsLabel,
                        { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                      ]}>
                        Jesuits
                      </Text>
                    </View>
                  )}
                  
                  {institution.staff_demographics.other_religious !== undefined && (
                    <View style={styles.demographicsItem}>
                      <Text style={[
                        styles.demographicsValue,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}>
                        {institution.staff_demographics.other_religious}
                      </Text>
                      <Text style={[
                        styles.demographicsLabel,
                        { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                      ]}>
                        Other Religious
                      </Text>
                    </View>
                  )}
                </View>

                <View style={styles.demographicsGrid}>
                  {institution.staff_demographics.catholics !== undefined && (
                    <View style={styles.demographicsItem}>
                      <Text style={[
                        styles.demographicsValue,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}>
                        {institution.staff_demographics.catholics}
                      </Text>
                      <Text style={[
                        styles.demographicsLabel,
                        { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                      ]}>
                        Catholics
                      </Text>
                    </View>
                  )}
                  
                  {institution.staff_demographics.others !== undefined && (
                    <View style={styles.demographicsItem}>
                      <Text style={[
                        styles.demographicsValue,
                        { color: isDark ? Colors.gray[100] : Colors.text }
                      ]}>
                        {institution.staff_demographics.others}
                      </Text>
                      <Text style={[
                        styles.demographicsLabel,
                        { color: isDark ? Colors.gray[400] : Colors.gray[600] }
                      ]}>
                        Non-Catholics
                      </Text>
                    </View>
                  )}
                </View>
              </View>
            )}
          </View>
        )}

        {/* Jesuits Card */}
        {institutionJesuits && institutionJesuits.length > 0 && (
          <View style={[
            styles.card,
            { backgroundColor: isDark ? Colors.gray[800] : Colors.white }
          ]}>
            <Text style={[
              styles.cardTitle,
              { color: isDark ? Colors.gray[200] : Colors.gray[800] }
            ]}>
              Associated Jesuits ({institutionJesuits.length})
            </Text>
            
            {institutionJesuits.map(jesuit => (
              <TouchableOpacity 
                key={jesuit.id}
                style={[
                  styles.jesuitItem,
                  { 
                    backgroundColor: isDark ? Colors.gray[700] : Colors.gray[50],
                    borderColor: isDark ? Colors.gray[600] : Colors.gray[200],
                  }
                ]}
                onPress={() => router.push(`/profile/${jesuit.id}`)}
              >
                <Text style={[
                  styles.jesuitName,
                  { color: isDark ? Colors.gray[100] : Colors.text }
                ]}>
                  {jesuit.name}
                </Text>
                
                <View style={styles.roleChip}>
                  <Text style={styles.roleChipText}>
                    {jesuit.roles?.find(r => r.institution === institution.name)?.type || 'Member'}
                  </Text>
                </View>
              </TouchableOpacity>
            ))}
          </View>
        )}
      </ScrollView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.gray[50],
  },
  contentContainer: {
    padding: 12,
    paddingBottom: 24,
  },
  centerContent: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  card: {
    borderRadius: 12,
    marginBottom: 12,
    padding: 16,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.1,
    shadowRadius: 2,
    elevation: 2,
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    marginBottom: 8,
  },
  typeChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.primary,
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderRadius: 16,
    alignSelf: 'flex-start',
  },
  typeChipText: {
    fontSize: 13,
    color: Colors.white,
    marginLeft: 4,
    fontWeight: '500',
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: '600',
    marginBottom: 16,
  },
  infoRow: {
    flexDirection: 'row', 
    marginBottom: 16,
    alignItems: 'flex-start',
  },
  infoIcon: {
    marginTop: 2,
    marginRight: 12,
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
    fontWeight: '500',
  },
  demographicsSection: {
    marginBottom: 16,
  },
  demographicsHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  demographicsSectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginLeft: 6,
  },
  demographicsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 8,
  },
  demographicsItem: {
    width: '33%',
    marginBottom: 12,
  },
  demographicsValue: {
    fontSize: 18,
    fontWeight: '600',
  },
  demographicsLabel: {
    fontSize: 13,
    marginTop: 2,
  },
  jesuitItem: {
    padding: 14,
    borderRadius: 8,
    marginBottom: 8,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderWidth: 1,
  },
  jesuitName: {
    fontSize: 16,
    fontWeight: '500',
    flex: 1,
  },
  roleChip: {
    backgroundColor: Colors.primary + '20',
    paddingVertical: 4,
    paddingHorizontal: 8,
    borderRadius: 12,
  },
  roleChipText: {
    fontSize: 12,
    color: Colors.primary,
    fontWeight: '500',
  },
}); 