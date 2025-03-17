import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { router } from 'expo-router';
import PhoneLogin from '@/components/auth/PhoneLogin';
import GoogleLogin from '@/components/auth/GoogleLogin';
import { FirebaseService } from '@/services/firebase';
import { authAPI } from '@/services/api';
import { useAuth } from '@/hooks/useAuth';
import Colors from '@/constants/Colors';
import ErrorMessage from '@/components/ui/ErrorMessage';
import LoadingProgress from '@/components/ui/LoadingProgress';
import { useDataSync } from '@/hooks/useDataSync';
import { dataAPI } from '@/services/api';

export default function Login() {
  const { setToken, setUser } = useAuth();
  const { syncData } = useDataSync();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handlePhoneLogin = async (phoneNumber: string) => {
    try {
      setLoading(true);
      setError(null);
      const verificationId = await FirebaseService.sendVerificationCode(phoneNumber);
      if (verificationId) {
        router.push({
          pathname: '/verify',
          params: { verificationId, phoneNumber }
        });
      }
    } catch (error: any) {
      setError(error.message || 'Phone login failed');
    } finally {
      setLoading(false);
    }
  };

  const handleGoogleLogin = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const { token } = await FirebaseService.signInWithGoogle();
      const response = await authAPI.googleLogin(token);
      
      // Fetch current jesuit data
      useAuth.getState().setToken(response.data.token);
      const jesuitResponse = await dataAPI.fetchCurrentJesuit();
      
      useAuth.getState().setAuthData({
        token: response.data.token,
        user: response.data.user,
        jesuit: jesuitResponse.data.data
      });
      
      router.replace('/(app)/home');
      
      // Then trigger background sync for search data
      setTimeout(() => {
        syncData(true).catch(console.error);
      }, 100);
      
    } catch (error: any) {
      setError(error.message || 'Google login failed');
      if (error.response) {
        await FirebaseService.signOut();
      }
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <LoadingProgress />;
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Welcome</Text>
      <Text style={styles.subtitle}>Sign in to continue</Text>
      
      {error && <ErrorMessage message={error} />}
      
      <PhoneLogin onSendCode={handlePhoneLogin} />
      
      <View style={styles.divider}>
        <View style={styles.line} />
        <Text style={styles.dividerText}>or</Text>
        <View style={styles.line} />
      </View>
      
      <GoogleLogin onPress={handleGoogleLogin} />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
    padding: 16,
    justifyContent: 'center',
  },
  title: {
    fontSize: 32,
    fontWeight: 'bold',
    color: Colors.text,
    textAlign: 'center',
    marginTop: 48,
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    color: Colors.gray[500],
    textAlign: 'center',
    marginBottom: 32,
  },
  divider: {
    flexDirection: 'row',
    alignItems: 'center',
    marginVertical: 24,
  },
  line: {
    flex: 1,
    height: 1,
    backgroundColor: Colors.border,
  },
  dividerText: {
    color: Colors.gray[500],
    paddingHorizontal: 16,
  },
}); 