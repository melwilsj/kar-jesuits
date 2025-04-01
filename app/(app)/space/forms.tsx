import React, { useState } from "react";
import { 
  View, 
  StyleSheet, 
  ScrollView, 
  TouchableOpacity, 
  TextInput, 
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  Alert
} from "react-native";
import ScreenContainer from '@/components/ScreenContainer';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';
import { Stack } from 'expo-router';
import ScaledText from '@/components/ScaledText';
import { useAuth } from '@/hooks/useAuth';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Form types
type FormType = 'correction' | 'request' | 'leave' | 'reimbursement' | 'other';

interface FormState {
  type: FormType;
  subject: string;
  message: string;
  urgent: boolean;
}

export default function Forms() {
  const colorScheme = useColorScheme();
  const { currentJesuit } = useAuth();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formState, setFormState] = useState<FormState>({
    type: 'correction',
    subject: '',
    message: '',
    urgent: false
  });

  const formTypes = [
    { id: 'correction', label: 'Data Correction', icon: 'edit' },
    { id: 'request', label: 'General Request', icon: 'help-outline' },
    { id: 'leave', label: 'Leave Request', icon: 'event-busy' },
    { id: 'reimbursement', label: 'Reimbursement', icon: 'receipt' },
    { id: 'other', label: 'Other', icon: 'more-horiz' }
  ];

  const handleSubmit = async () => {
    // Validate form
    if (!formState.subject.trim()) {
      Alert.alert('Error', 'Please enter a subject for your request');
      return;
    }

    if (!formState.message.trim()) {
      Alert.alert('Error', 'Please enter details for your request');
      return;
    }

    setIsSubmitting(true);

    try {
      // In a real app, this would send the form to a server
      // Here we'll just simulate a delay and save to local storage
      
      const formSubmission = {
        id: Date.now().toString(),
        ...formState,
        status: 'pending',
        submittedBy: currentJesuit?.id || 'unknown',
        submittedAt: Date.now()
      };
      
      // Save to "recent submissions" list in AsyncStorage
      const storedSubmissions = await AsyncStorage.getItem('form_submissions');
      const submissions = storedSubmissions ? JSON.parse(storedSubmissions) : [];
      submissions.unshift(formSubmission);
      
      await AsyncStorage.setItem('form_submissions', JSON.stringify(submissions));
      
      // Simulate network request delay
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Clear form
      setFormState({
        type: 'correction',
        subject: '',
        message: '',
        urgent: false
      });
      
      Alert.alert(
        'Success',
        'Your form has been submitted successfully',
        [{ text: 'OK' }]
      );
    } catch (error) {
      console.error('Error submitting form:', error);
      Alert.alert('Error', 'Failed to submit your form. Please try again later.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <ScreenContainer>
      <Stack.Screen options={{ title: 'Submit Form' }} />
      
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={styles.container}
      >
        <ScrollView style={styles.scrollContainer}>
          <View style={styles.header}>
            <ScaledText style={[
              styles.title, 
              { color: Colors[`${colorScheme}`].text }
            ]}>
              Submit a Form
            </ScaledText>
            <ScaledText style={[
              styles.subtitle,
              { color: Colors[`${colorScheme}`].textSecondary }
            ]}>
              Fill out the details to send to administrators
            </ScaledText>
          </View>
          
          <View style={styles.formContainer}>
            <View style={styles.formSection}>
              <ScaledText style={[
                styles.sectionTitle,
                { color: Colors[`${colorScheme}`].text }
              ]}>
                Form Type
              </ScaledText>
              <View style={styles.typeContainer}>
                {formTypes.map(type => (
                  <TouchableOpacity
                    key={type.id}
                    style={[
                      styles.typeButton,
                      formState.type === type.id && styles.selectedTypeButton,
                      {
                        backgroundColor: formState.type === type.id ? Colors[`${colorScheme}`].primary : Colors[`${colorScheme}`].background
                      }
                    ]}
                    onPress={() => setFormState({...formState, type: type.id as FormType})}
                  >
                    <MaterialIcons
                      name={type.icon as any}
                      size={20}
                      color={formState.type === type.id 
                        ? Colors[`${colorScheme}`].primary 
                        : Colors[`${colorScheme}`].primary }
                    />
                    <ScaledText style={[
                      styles.typeButtonText,
                      formState.type === type.id && styles.selectedTypeButtonText,
                      {
                        color: formState.type === type.id 
                          ? Colors[`${colorScheme}`].primary 
                          : Colors[`${colorScheme}`].textSecondary
                      }
                    ]}>
                      {type.label}
                    </ScaledText>
                  </TouchableOpacity>
                ))}
              </View>
            </View>
            
            <View style={styles.formSection}>
              <ScaledText style={[
                styles.sectionTitle,
                { color: Colors[`${colorScheme}`].textSecondary }
              ]}>
                Subject
              </ScaledText>
              <TextInput
                style={[
                  styles.textInput,
                  { 
                    backgroundColor: Colors[`${colorScheme}`].background,
                    color: Colors[`${colorScheme}`].text,
                    borderColor: Colors[`${colorScheme}`].border
                  }
                ]}
                placeholder="Enter subject"
                placeholderTextColor={Colors[`${colorScheme}`].textSecondary}
                value={formState.subject}
                onChangeText={(text) => setFormState({...formState, subject: text})}
              />
            </View>
            
            <View style={styles.formSection}>
              <ScaledText style={[
                styles.sectionTitle,
                { color: Colors[`${colorScheme}`].textSecondary }
              ]}>
                Details
              </ScaledText>
              <TextInput
                style={[
                  styles.textInput,
                  styles.multilineInput,
                  { 
                    backgroundColor: Colors[`${colorScheme}`].background,
                    color: Colors[`${colorScheme}`].text,
                    borderColor: Colors[`${colorScheme}`].border
                  }
                ]}
                placeholder="Describe your request in detail"
                placeholderTextColor={Colors[`${colorScheme}`].textSecondary}
                multiline
                numberOfLines={6}
                textAlignVertical="top"
                value={formState.message}
                onChangeText={(text) => setFormState({...formState, message: text})}
              />
            </View>
            
            <View style={styles.formSection}>
              <TouchableOpacity
                style={styles.urgentCheckbox}
                onPress={() => setFormState({...formState, urgent: !formState.urgent})}
              >
                <View style={[
                  styles.checkbox,
                  {
                    backgroundColor: formState.urgent 
                      ? Colors[`${colorScheme}`].primary 
                      : 'transparent',
                    borderColor: formState.urgent 
                      ? Colors[`${colorScheme}`].primary 
                      : Colors[`${colorScheme}`].textSecondary
                  }
                ]}>
                  {formState.urgent && (
                    <MaterialIcons name="check" size={16} color="white" />
                  )}
                </View>
                <ScaledText style={[
                  styles.checkboxLabel,
                  { color: Colors[`${colorScheme}`].textSecondary }
                ]}>
                  Mark as urgent
                </ScaledText>
              </TouchableOpacity>
            </View>
            
            <TouchableOpacity
              style={[
                styles.submitButton,
                { opacity: isSubmitting ? 0.7 : 1 }
              ]}
              onPress={handleSubmit}
              disabled={isSubmitting}
            >
              {isSubmitting ? (
                <ActivityIndicator color="white" size="small" />
              ) : (
                <>
                  <MaterialIcons name="send" size={20} color="white" />
                  <ScaledText style={styles.submitButtonText}>
                    Submit Form
                  </ScaledText>
                </>
              )}
            </TouchableOpacity>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  scrollContainer: {
    flex: 1,
  },
  header: {
    padding: 16,
    marginBottom: 8,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 16,
  },
  formContainer: {
    padding: 16,
  },
  formSection: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 8,
  },
  typeContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginHorizontal: -4,
  },
  typeButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 6,
    marginHorizontal: 4,
    marginBottom: 8,
  },
  selectedTypeButton: {
    borderWidth: 1,
    borderColor: Color.primary,
  },
  typeButtonText: {
    marginLeft: 6,
    fontSize: 14,
  },
  selectedTypeButtonText: {
    fontWeight: '600',
  },
  textInput: {
    borderWidth: 1,
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 16,
  },
  multilineInput: {
    height: 120,
    paddingTop: 12,
  },
  urgentCheckbox: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  checkbox: {
    width: 20,
    height: 20,
    borderRadius: 4,
    borderWidth: 2,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 8,
  },
  checkboxLabel: {
    fontSize: 16,
  },
  submitButton: {
    backgroundColor: Color.primary,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 12,
    borderRadius: 8,
    marginTop: 20,
  },
  submitButtonText: {
    color: 'white',
    fontWeight: '600',
    fontSize: 16,
    marginLeft: 8,
  },
}); 