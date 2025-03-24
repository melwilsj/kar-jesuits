import React from 'react';
import { View, Text, StyleSheet, ScrollView, Image } from 'react-native';
import { useAuth } from '@/hooks/useAuth';
import Colors from '@/constants/Colors';
import LoadingProgress from '@/components/ui/LoadingProgress';
import { MaterialIcons } from '@expo/vector-icons';
import { Jesuit, CurrentJesuit } from '@/types/api';

interface ProfileProps {
  jesuit: Jesuit | CurrentJesuit;
  currentJesuit?: boolean;  
}

function formatDate(dateString: string | null): string {
  if (!dateString) return 'Not provided';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

export default function Profile({ jesuit, currentJesuit }: ProfileProps) {
  const { user } = useAuth();
  const defaultImage = 'https://placehold.co/600x400.png';

  if (!jesuit && !user) {
    return <LoadingProgress />;
  }

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Image 
          source={{ uri: jesuit?.photo_url || defaultImage }} 
          style={styles.profileImage}
        />
        <Text style={styles.name}>{jesuit?.name || user?.name}</Text>
        {jesuit?.code && (
          <Text style={styles.subtitle}>{jesuit.code}</Text>
        )}
      </View>

      <View style={styles.content}>
        <View style={styles.infoCard}>
          <View style={styles.infoRow}>
            <MaterialIcons name="email" size={20} color={Colors.gray[600]} />
            <View style={styles.infoContent}>
              <Text style={styles.label}>Email</Text>
              <Text style={styles.value}>{jesuit?.email || user?.email || 'Not provided'}</Text>
            </View>
          </View>

          <View style={styles.infoRow}>
            <MaterialIcons name="phone" size={20} color={Colors.gray[600]} />
            <View style={styles.infoContent}>
              <Text style={styles.label}>Phone</Text>
              <Text style={styles.value}>{jesuit?.phone_number || user?.phone_number || 'Not provided'}</Text>
            </View>
          </View>

          {jesuit && (
            <>
              <View style={styles.infoRow}>
                <MaterialIcons name="location-city" size={20} color={Colors.gray[600]} />
                <View style={styles.infoContent}>
                  <Text style={styles.label}>Province</Text>
                  <Text style={styles.value}>{jesuit.region || jesuit.province || 'Not assigned'}</Text>
                </View>
              </View>

              <View style={styles.infoRow}>
                <MaterialIcons name="home" size={20} color={Colors.gray[600]} />
                <View style={styles.infoContent}>
                  <Text style={styles.label}>Community</Text>
                  <Text style={styles.value}>{jesuit.current_community || 'Not assigned'}</Text>
                </View>
              </View>

              <View style={styles.infoRow}>
                <MaterialIcons name="cake" size={20} color={Colors.gray[600]} />
                <View style={styles.infoContent}>
                  <Text style={styles.label}>Date of Birth</Text>
                  <Text style={styles.value}>{formatDate(jesuit.dob)}</Text>
                </View>
              </View>

              <View style={styles.infoRow}>
                <MaterialIcons name="church" size={20} color={Colors.gray[600]} />
                <View style={styles.infoContent}>
                  <Text style={styles.label}>Ordination Date</Text>
                  <Text style={styles.value}>{formatDate(jesuit.priesthood_date)}</Text>
                </View>
              </View>
            </>
          )}
        </View>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  header: {
    alignItems: 'center',
    padding: 20,
    backgroundColor: Colors.primary,
  },
  profileImage: {
    width: 120,
    height: 120,
    borderRadius: 60,
    marginBottom: 16,
    borderWidth: 3,
    borderColor: Colors.white,
  },
  name: {
    fontSize: 24,
    fontWeight: 'bold',
    color: Colors.white,
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 16,
    color: Colors.gray[200],
  },
  content: {
    padding: 16,
  },
  infoCard: {
    backgroundColor: Colors.white,
    borderRadius: 12,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: Colors.gray[200],
  },
  infoContent: {
    marginLeft: 12,
    flex: 1,
  },
  label: {
    fontSize: 14,
    color: Colors.gray[600],
    marginBottom: 2,
  },
  value: {
    fontSize: 16,
    color: Colors.text,
    fontWeight: '500',
  },
}); 