import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, TextInput, TouchableOpacity } from 'react-native';
import { useLocalSearchParams, router } from 'expo-router';
import { FirebaseService } from '@/services/firebase';
import { authAPI, dataAPI } from '@/services/api';
import { useAuth } from '@/hooks/useAuth';
import Colors, { Color } from '@/constants/Colors';
import ErrorMessage from '@/components/ui/ErrorMessage';
import LoadingProgress from '@/components/ui/LoadingProgress';

export default function Verify() {
  const { verificationId, phoneNumber } = useLocalSearchParams();
  const [code, setCode] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [timeLeft, setTimeLeft] = useState(30);
  const { setToken, setUser } = useAuth();

  useEffect(() => {
    if (timeLeft > 0) {
      const timer = setTimeout(() => setTimeLeft(timeLeft - 1), 1000);
      return () => clearTimeout(timer);
    }
  }, [timeLeft]);

  const handleResendCode = async () => {
    try {
      setLoading(true);
      setError(null);
      await FirebaseService.sendVerificationCode(phoneNumber as string);
      setTimeLeft(30);
    } catch (error) {
      setError('Failed to resend code. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleVerify = async () => {
    if (code.length !== 6) {
      setError('Please enter a valid 6-digit code');
      return;
    }

    try {
      setLoading(true);
      setError(null);
      const { token } = await FirebaseService.verifyPhoneCode(
        verificationId as string,
        code
      );
      const response = await authAPI.phoneLogin(
        phoneNumber as string,
        code,
        token
      );
      setToken(response.data.token);
      const jesuitResponse = await dataAPI.fetchCurrentJesuit();
      useAuth.getState().setAuthData({
        token: response.data.token,
        user: response.data.user,
        jesuit: jesuitResponse.data.data
      });
      router.replace('/(app)/home');
    } catch (error) {
      setError('Invalid verification code. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <LoadingProgress />;
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Verify Phone</Text>
      <Text style={styles.subtitle}>
        Enter the code sent to {phoneNumber}
      </Text>
      
      <TextInput
        style={[styles.input, error && additionalStyles.inputError]}
        value={code}
        onChangeText={(text) => {
          setCode(text);
          setError(null);
        }}
        placeholder="Enter verification code"
        keyboardType="number-pad"
        maxLength={6}
      />
      
      {error && <ErrorMessage message={error} />}
      
      <TouchableOpacity
        style={[styles.button, loading && styles.buttonDisabled]}
        onPress={handleVerify}
        disabled={loading || code.length !== 6}
      >
        <Text style={styles.buttonText}>
          {loading ? 'Verifying...' : 'Verify'}
        </Text>
      </TouchableOpacity>

      <TouchableOpacity
        style={[additionalStyles.resendButton, timeLeft > 0 && styles.buttonDisabled]}
        onPress={handleResendCode}
        disabled={timeLeft > 0}
      >
        <Text style={additionalStyles.resendText}>
          {timeLeft > 0 
            ? `Resend code in ${timeLeft}s` 
            : 'Resend code'}
        </Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Color.background,
    padding: 16,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: Color.text,
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    color: Color.gray[500],
    marginBottom: 24,
  },
  input: {
    borderWidth: 1,
    borderColor: Color.border,
    borderRadius: 8,
    padding: 16,
    fontSize: 18,
    textAlign: 'center',
    letterSpacing: 4,
    marginBottom: 24,
  },
  button: {
    backgroundColor: Color.primary,
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
  },
  buttonDisabled: {
    opacity: 0.5,
  },
  buttonText: {
    color: Color.background,
    fontSize: 16,
    fontWeight: '600',
  },
});

const additionalStyles = StyleSheet.create({
  inputError: {
    borderColor: Color.error,
  },
  resendButton: {
    marginTop: 16,
    alignItems: 'center',
  },
  resendText: {
    color: Color.primary,
    fontSize: 14,
    fontWeight: '500',
  },
}); 