import React, { useState } from 'react';
import { View, TouchableOpacity, Text, StyleSheet } from 'react-native';
import PhoneInput, { ICountry, isValidPhoneNumber } from 'react-native-international-phone-number';
import { Color } from '@/constants/Colors';
import ErrorMessage from '../ui/ErrorMessage';
import LoadingProgress from '../ui/LoadingProgress';

interface PhoneLoginProps {
  onSendCode: (phoneNumber: string) => Promise<void>;
}

export default function PhoneLogin({ onSendCode }: PhoneLoginProps) {
  const [selectedCountry, setSelectedCountry] = useState<ICountry | null>(null);
  const [inputValue, setInputValue] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleInputValue = (phoneNumber: string) => {
    setInputValue(phoneNumber);
    setError(null);
  };

  const handleSelectedCountry = (country: ICountry) => {
    setSelectedCountry(country);
    setError(null);
  };

  const handleSendCode = async () => {
    if (!selectedCountry || !isValidPhoneNumber(inputValue, selectedCountry)) {
      setError('Please enter a valid phone number');
      return;
    }

    try {
      setLoading(true);
      setError(null);
      const fullNumber = `${selectedCountry.callingCode}${inputValue.replace(/\s+/g, '')}`;
      await onSendCode(fullNumber);
    } catch (error: any) {
      setError(error.message || 'Failed to send verification code');
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <LoadingProgress />;

  return (
    <View style={styles.container}>
      <PhoneInput
        value={inputValue}
        onChangePhoneNumber={handleInputValue}
        selectedCountry={selectedCountry}
        onChangeSelectedCountry={handleSelectedCountry}
        defaultCountry="IN"
        placeholder='9595959595'
      />
      
      {error && <ErrorMessage message={error} />}
      
      <TouchableOpacity
        style={[styles.button, (!inputValue || loading) && styles.buttonDisabled]}
        onPress={handleSendCode}
        disabled={!inputValue || loading}
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
    width: '100%',
  },
  phoneContainer: {
    width: '100%',
    height: 50,
    borderWidth: 1,
    borderColor: Color.border,
    borderRadius: 8,
    backgroundColor: Color.background,
  },
  flagContainer: {
    borderTopLeftRadius: 8,
    borderBottomLeftRadius: 8,
  },
  textContainer: {
    borderTopRightRadius: 8,
    borderBottomRightRadius: 8,
    backgroundColor: Color.background,
  },
  label: {
    fontSize: 14,
    fontWeight: '500',
    color: Color.gray[700],
    marginBottom: 8,
  },
  input: {
    borderWidth: 1,
    borderColor: Color.border,
    borderRadius: 8,
    padding: 12,
    marginBottom: 8,
    fontSize: 16,
  },
  inputError: {
    borderColor: Color.error,
  },
  button: {
    backgroundColor: Color.primary,
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 8,
  },
  buttonDisabled: {
    opacity: 0.5,
  },
  buttonText: {
    color: Color.background,
    fontWeight: '600',
    fontSize: 16,
  },
}); 