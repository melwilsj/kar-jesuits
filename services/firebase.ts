import { getAuth, signInWithCredential } from '@react-native-firebase/auth';
import PhoneAuthProvider from '@react-native-firebase/auth';
import GoogleAuthProvider from '@react-native-firebase/auth';
import messaging from '@react-native-firebase/messaging';
import * as Notifications from 'expo-notifications';
import { Platform } from 'react-native';
import { API_CONFIG } from '../constants/Config';
import {
  GoogleSignin
} from '@react-native-google-signin/google-signin';
import auth, { FirebaseAuthTypes } from '@react-native-firebase/auth';
import { authAPI } from './api';
import { useAuth } from '@/hooks/useAuth';

let currentFcmToken: string | null = null;

export const FirebaseService = {
  async init() {
    try {
      if (!process.env.EXPO_PUBLIC_FIREBASE_WEB_CLIENT_ID) {
        throw new Error('Firebase Web Client ID is not configured');
      }
      
      // Initialize Google Sign In
      await GoogleSignin.configure({
        webClientId: process.env.EXPO_PUBLIC_FIREBASE_WEB_CLIENT_ID,
        offlineAccess: true,
      });

      // Request Notification Permissions on init
      await this.requestNotificationPermission();

      // Set up FCM listeners
      this.setupFcmListeners();

      return true;
    } catch (error) {
      console.error('Firebase initialization error:', error);
      throw error;
    }
  },

  async requestNotificationPermission(): Promise<boolean> {
    if (Platform.OS === 'ios') {
        const { status: existingStatus } = await Notifications.getPermissionsAsync();
        let finalStatus = existingStatus;
        if (existingStatus !== 'granted') {
            const { status } = await Notifications.requestPermissionsAsync();
            finalStatus = status;
        }
        if (finalStatus !== 'granted') {
            console.log('Failed to get push token for push notification!');
            return false;
        }
        // For iOS, also request permission via Firebase messaging
        const authStatus = await messaging().requestPermission();
        const enabled =
            authStatus === messaging.AuthorizationStatus.AUTHORIZED ||
            authStatus === messaging.AuthorizationStatus.PROVISIONAL;

        if (enabled) {
            console.log('Authorization status:', authStatus);
            return true;
        } else {
            console.log('Notification permission denied on iOS.');
            return false;
        }
    } else { // Android permissions are handled differently (usually granted by default)
        // Check if permissions are granted using expo-notifications
         const { status } = await Notifications.getPermissionsAsync();
         if (status === 'granted') {
             console.log('Notification permissions already granted on Android.');
             return true;
         }
         // If not granted, request them (though often not needed explicitly unless targeting specific Android versions/features)
         const { status: newStatus } = await Notifications.requestPermissionsAsync();
         if (newStatus === 'granted') {
             console.log('Notification permissions granted on Android.');
             return true;
         } else {
             console.log('Notification permissions denied on Android.');
             return false;
         }
    }
  },

  async getFcmToken(): Promise<string | null> {
    try {
        // Ensure permissions first
        const hasPermission = await this.requestNotificationPermission();
        if (!hasPermission) {
            console.log("No notification permission, cannot get FCM token.");
            return null;
        }

        // Check if token already exists
        let token = await messaging().getToken();
        if (token) {
            console.log('FCM Token:', token);
            currentFcmToken = token; // Store locally
            return token;
        } else {
            console.log('Could not get FCM token.');
            return null;
        }
    } catch (error) {
        console.error('Error getting FCM token:', error);
        return null;
    }
  },

  setupFcmListeners() {
    // Listener for token refresh
    messaging().onTokenRefresh(async (newToken) => {
      console.log('FCM Token refreshed:', newToken);
      currentFcmToken = newToken; // Update local store
      // Re-register the new token with your backend if user is logged in
      const state = useAuth.getState();
      if (state.isAuthenticated && state.token) {
        try {
          await authAPI.registerFcmToken(newToken);
        } catch (error) {
          console.error('Failed to re-register refreshed FCM token:', error);
        }
      }
    });

    // Listener for foreground messages (optional but recommended)
    messaging().onMessage(async remoteMessage => {
      console.log('FCM Message Received in Foreground:', JSON.stringify(remoteMessage));
      // Use expo-notifications to display the notification while app is in foreground
      Notifications.presentNotificationAsync({
        title: remoteMessage.notification?.title,
        body: remoteMessage.notification?.body,
        data: remoteMessage.data, // Pass data along
      });
    });

    // Listener for background/quit state messages (optional)
    messaging().setBackgroundMessageHandler(async remoteMessage => {
      console.log('Message handled in the background!', remoteMessage);
      // You can add background task logic here if needed
    });

    // Listener for when a user taps on a notification (optional)
     Notifications.addNotificationResponseReceivedListener(response => {
       console.log('Notification tapped:', response.notification.request.content.data);
       // Handle navigation or other actions based on notification data
       // e.g., router.push('/notifications');
     });
  },

  getCurrentFcmToken(): string | null {
      return currentFcmToken;
  },

  async checkPhoneExists(phoneNumber: string): Promise<boolean> {
    try {
      const response = await fetch(`${API_CONFIG.baseURL}/auth/check-phone`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ phone_number: phoneNumber }),
      });
      const data = await response.json();
      return data.exists;
    } catch (error) {
      console.error('Error checking phone:', error);
      throw error;
    }
  },

  async sendVerificationCode(phoneNumber: string): Promise<string> {
    try {
      // First check if phone exists in database
      const exists = await this.checkPhoneExists(phoneNumber);
      if (!exists) {
        throw new Error('Phone number not registered');
      }

      const confirmation = await getAuth().signInWithPhoneNumber(phoneNumber);
      if (!confirmation.verificationId) {
        throw new Error('Failed to get verification ID');
      }
      return confirmation.verificationId;
    } catch (error) {
      console.error('Send code error:', error);
      throw error;
    }
  },

  async verifyPhoneCode(verificationId: string, code: string): Promise<{ token: string }> {
    try {
      const auth = getAuth();
      const credential = PhoneAuthProvider.PhoneAuthProvider.credential(verificationId, code);
      const result = await signInWithCredential(auth, credential);
      const token = await result.user.getIdToken();
      return { token };
    } catch (error) {
      console.error('Code verification error:', error);
      throw error;
    }
  },

  async signInWithGoogle(): Promise<{ token: string }> {
    try {
      await GoogleSignin.hasPlayServices();
      const userInfo = await GoogleSignin.signIn();
      
      if (!userInfo.data?.idToken) {
        throw new Error('No ID token received from Google Sign-In');
      }
      
      // Create Firebase credential
      const credential = GoogleAuthProvider.GoogleAuthProvider.credential(userInfo.data?.idToken);
      const result = await getAuth().signInWithCredential(credential);
      const firebaseToken = await result.user.getIdToken();
      
      return { token: firebaseToken };
    } catch (error) {
      console.error('Google sign in error:', error);
      throw error;
    }
  },

  async signOut() {
    try {
      // Always try to sign out from Google first
      try {
        await GoogleSignin.signOut();
      } catch (error) {
        console.log('Google sign out error (ignored):', error);
      }
      
      // Then try Firebase signout if there's a current user
      const auth = getAuth();
      if (auth.currentUser) {
        await auth.signOut();
      }
    } catch (error) {
      console.error('Sign out error:', error);
      // Don't throw the error, just log it
    }
  },

  onAuthStateChanged(callback: (user: FirebaseAuthTypes.User | null) => void) {
    return getAuth().onAuthStateChanged(callback);
  },

  async getCurrentUser() {
    return getAuth().currentUser;
  },

  async getIdToken(forceRefresh = false) {
    const user = await this.getCurrentUser();
    if (!user) throw new Error('No authenticated user');
    return user.getIdToken(forceRefresh);
  }
};