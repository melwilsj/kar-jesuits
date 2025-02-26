import React, { useState, useEffect } from 'react';
import { View, TextInput, TouchableOpacity, Text, StyleSheet } from 'react-native';
import Colors from '../../constants/Colors';
import ErrorMessage from '../ui/ErrorMessage';

interface PhoneLoginProps {
  onSendCode: (phoneNumber: string) => Promise<void>;
}

export default function PhoneLogin({ onSendCode }: PhoneLoginProps) {
  const [phoneNumber, setPhoneNumber] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isValid, setIsValid] = useState(false);

  useEffect(() => {
    validatePhoneNumber(phoneNumber);
  }, [phoneNumber]);

  const validatePhoneNumber = (number: string) => {
    // Basic phone number validation
    const phoneRegex = /^\+[1-9]\d{1,14}$/;
    setIsValid(phoneRegex.test(number));
    setError(null);
  };

  const handleSendCode = async () => {
    if (!isValid) {
      setError('Please enter a valid phone number');
      return;
    }

    try {
      setLoading(true);
      setError(null);
      await onSendCode(phoneNumber);
    } catch (error) {
      setError('Failed to send verification code. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.label}>Phone Number</Text>
      <TextInput
        style={[
          styles.input,
          error && styles.inputError
        ]}
        placeholder="+911234567890"
        value={phoneNumber}
        onChangeText={setPhoneNumber}
        keyboardType="phone-pad"
        autoComplete="tel"
        editable={!loading}
      />
      {error && <ErrorMessage message={error} />}
      <TouchableOpacity 
        style={[
          styles.button,
          (!isValid || loading) && styles.buttonDisabled
        ]}
        onPress={handleSendCode}
        disabled={!isValid || loading}
      >
        <Text style={styles.buttonText}>
          {loading ? 'Sending...' : 'Send Code'}
        </Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    padding: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: '500',
    color: Colors.gray[700],
    marginBottom: 8,
  },
  input: {
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: 8,
    padding: 12,
    marginBottom: 8,
    fontSize: 16,
  },
  inputError: {
    borderColor: Colors.error,
  },
  button: {
    backgroundColor: Colors.primary,
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 8,
  },
  buttonDisabled: {
    opacity: 0.5,
  },
  buttonText: {
    color: Colors.background,
    fontWeight: '600',
    fontSize: 16,
  },
}); 