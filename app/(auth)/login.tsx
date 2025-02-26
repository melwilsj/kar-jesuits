import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { router } from 'expo-router';
import PhoneLogin from '../../components/auth/PhoneLogin';
import GoogleLogin from '../../components/auth/GoogleLogin';
import { FirebaseService } from '../../services/firebase';
import { authAPI } from '../../services/api';
import { useAuth } from '../../hooks/useAuth';
import Colors from '../../constants/Colors';

export default function Login() {
  const { setToken, setUser } = useAuth();

  const handlePhoneLogin = async (phoneNumber: string) => {
    try {
      const verificationId = await FirebaseService.sendVerificationCode(phoneNumber);
      if (verificationId) {
        router.push({
          pathname: '/verify',
          params: { verificationId, phoneNumber }
        });
      }
    } catch (error) {
      console.error('Phone login error:', error);
    }
  };

  const handleGoogleLogin = async () => {
    try {
      const { token } = await FirebaseService.signInWithGoogle();
      const response = await authAPI.googleLogin(token);
      setToken(response.data.token);
      setUser(response.data.user);
    } catch (error) {
      console.error('Google login error:', error);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Welcome</Text>
      <Text style={styles.subtitle}>Sign in to continue</Text>
      
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