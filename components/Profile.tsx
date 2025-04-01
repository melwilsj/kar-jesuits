import React from 'react';
import { View, Text, StyleSheet, ScrollView, Image, TouchableOpacity, Linking, Alert } from 'react-native';
import { useAuth } from '@/hooks/useAuth';
import Colors, { Color } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';
import LoadingProgress from '@/components/ui/LoadingProgress';
import { MaterialIcons, FontAwesome } from '@expo/vector-icons';
import { Jesuit, CurrentJesuit } from '@/types/api';

interface ProfileProps {
  jesuit: Jesuit | CurrentJesuit;
  currentJesuit?: boolean;  
}

// Function to handle opening URLs safely
const openURL = async (url: string, errorMessage: string = 'Could not open the link.') => {
  try {
    await Linking.openURL(url);
  } catch (error) {
    Alert.alert('Error', errorMessage);
  }
};

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
  const colorScheme = useColorScheme();
  if (!jesuit && !user) {
    return <LoadingProgress />;
  }

  const email = jesuit?.email || user?.email;
  const phoneNumber = jesuit?.phone_number || user?.phone_number;

  const handleEmailPress = () => {
    if (email) {
      openURL(`mailto:${email}`, 'Could not open email app.');
    }
  };

  const handlePhonePress = () => {
    if (phoneNumber) {
      // Basic cleanup for tel: link (remove spaces, etc.) - adjust if needed based on your phone number format
      const cleanedPhoneNumber = phoneNumber.replace(/\s+/g, '');
      openURL(`tel:${cleanedPhoneNumber}`, 'Could not initiate phone call.');
    }
  };

  const handleWhatsAppPress = () => {
    if (phoneNumber) {
      // Basic cleanup for WhatsApp link - adjust if needed based on your phone number format and country codes
      const cleanedPhoneNumber = phoneNumber.replace(/[^0-9]/g, ''); // Keep only digits
      // You might need to prepend a country code if it's not always present
      openURL(`https://wa.me/${phoneNumber}`, 'Could not open WhatsApp. Make sure it is installed.');
    }
  };

  return (
    <ScrollView style={[{flex: 1, backgroundColor: Colors[`${colorScheme}`].background }]}>
      <View style={[styles.header, { backgroundColor: Colors[`${colorScheme}`].primary }]}>
        <Image 
          source={{ uri: jesuit?.photo_url || defaultImage }} 
          style={styles.profileImage}
        />
        <Text style={[styles.name, { color: Colors[`${colorScheme}`].text }]}>{jesuit?.name || user?.name}</Text>
        {jesuit?.code && (
          <Text style={[styles.subtitle, { color: Colors[`${colorScheme}`].textSecondary }]}>{jesuit.code}</Text>
        )}
      </View>

      <View style={styles.content}>
        <View style={[styles.infoCard, { backgroundColor: Colors[`${colorScheme}`].card }]}>
          <View style={styles.infoRow}>
            <MaterialIcons name="email" size={20} color={Colors[`${colorScheme}`].icon} />
            <View style={styles.infoContent}>
              <Text style={styles.label}>Email</Text>
              <TouchableOpacity onPress={handleEmailPress} disabled={!email}>
                <Text style={[styles.value, !email && styles.notProvided]}>
                  {email || 'Not provided'}
                </Text>
              </TouchableOpacity>
            </View>
          </View>

          <View style={styles.infoRow}>
            <TouchableOpacity onPress={handlePhonePress} style={styles.touchableRow} disabled={!phoneNumber}>
              <MaterialIcons name="phone" size={20} color={Colors[`${colorScheme}`].icon} />
              <View style={styles.infoContent}>
                <Text style={styles.label}>Phone</Text>
                <Text style={[styles.value, !phoneNumber && styles.notProvided]}>
                  {phoneNumber || 'Not provided'}
                </Text>
              </View>
            </TouchableOpacity>
            {phoneNumber && (
              <TouchableOpacity onPress={handleWhatsAppPress} style={styles.iconButton}>
                <FontAwesome name="whatsapp" size={24} color="#25D366" />
              </TouchableOpacity>
            )}
          </View>

          {jesuit && (
            <>
              <View style={styles.infoRow}>
                <MaterialIcons name="location-city" size={20} color={Colors[`${colorScheme}`].icon} />
                <View style={styles.infoContent}>
                  <Text style={styles.label}>Province</Text>
                  <Text style={styles.value}>{jesuit.region || jesuit.province || 'Not assigned'}</Text>
                </View>
              </View>

              <View style={styles.infoRow}>
                <MaterialIcons name="home" size={20} color={Colors[`${colorScheme}`].icon} />
                <View style={styles.infoContent}>
                  <Text style={styles.label}>Community</Text>
                  <Text style={styles.value}>{jesuit.current_community || 'Not assigned'}</Text>
                </View>
              </View>

              <View style={styles.infoRow}>
                <MaterialIcons name="cake" size={20} color={Colors[`${colorScheme}`].icon} />
                <View style={styles.infoContent}>
                  <Text style={styles.label}>Date of Birth</Text>
                  <Text style={styles.value}>{formatDate(jesuit.dob)}</Text>
                </View>
              </View>

              <View style={styles.infoRow}>
                <MaterialIcons name="church" size={20} color={Colors[`${colorScheme}`].icon} />
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
  header: {
    alignItems: 'center',
    padding: 20,
  },
  profileImage: {
    width: 120,
    height: 120,
    borderRadius: 60,
    marginBottom: 16,
    borderWidth: 3,
  },
  name: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 16
  },
  content: {
    padding: 16,
  },
  infoCard: {
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
    borderBottomColor: Color.border,
  },
  touchableRow: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  infoContent: {
    marginLeft: 12,
    flex: 1,
  },
  label: {
    fontSize: 14,
    color: Color.gray[600],
    marginBottom: 2,
  },
  value: {
    fontSize: 16,
    color: Color.text,
    fontWeight: '500',
  },
  iconButton: {
    paddingLeft: 10,
    paddingVertical: 5,
  },
  notProvided: {
    color: Color.gray[500],
    fontStyle: 'italic',
  }
}); 