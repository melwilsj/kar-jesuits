import { getAuth, signInWithCredential } from '@react-native-firebase/auth';
import PhoneAuthProvider from '@react-native-firebase/auth';
import GoogleAuthProvider from '@react-native-firebase/auth';
import { API_CONFIG } from '../constants/Config';
import {
  GoogleSignin
} from '@react-native-google-signin/google-signin';


export class FirebaseService {
  static async init() {
    try {
      if (!process.env.EXPO_PUBLIC_FIREBASE_WEB_CLIENT_ID) {
        throw new Error('Firebase Web Client ID is not configured');
      }
      
      // Initialize Google Sign In
      await GoogleSignin.configure({
        webClientId: process.env.EXPO_PUBLIC_FIREBASE_WEB_CLIENT_ID,
        offlineAccess: true,
      });

      return true;
    } catch (error) {
      console.error('Firebase initialization error:', error);
      throw error;
    }
  }

  static async checkPhoneExists(phoneNumber: string): Promise<boolean> {
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
  }

  static async sendVerificationCode(phoneNumber: string): Promise<string> {
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
  }

  static async signInWithPhone(verificationId: string, code: string): Promise<{ token: string }> {
    try {
      const auth = getAuth();
      const credential = PhoneAuthProvider.PhoneAuthProvider.credential(verificationId, code);
      const result = await signInWithCredential(auth, credential);
      const token = await result.user.getIdToken();
      return { token };
    } catch (error) {
      console.error('Phone verification error:', error);
      throw error;
    }
  }

  static async signInWithGoogle(): Promise<{ token: string }> {
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
  }

  static async signOut(): Promise<void> {
    try {
      const auth = getAuth();
      if (auth.currentUser) {
        await auth.signOut();
        await GoogleSignin.signOut();
      }
    } catch (error) {
      console.error('Sign out error:', error);
      throw error;
  }
}

  // Add auth state listener
  static onAuthStateChanged(callback: (user: any) => void) {
    return getAuth().onAuthStateChanged(callback);
  }

  // Get current user
  static getCurrentUser() {
    return getAuth().currentUser;
  }
}