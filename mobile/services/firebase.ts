import { initializeApp } from 'firebase/app';
import { 
  initializeAuth,
  getReactNativePersistence,
  PhoneAuthProvider, 
  signInWithCredential,
  GoogleAuthProvider,
  signInWithPopup 
} from 'firebase/auth';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { FIREBASE_CONFIG } from '../constants/Config';

// Initialize Firebase
const app = initializeApp(FIREBASE_CONFIG);

// Initialize Auth with persistence
const auth = initializeAuth(app, {
  persistence: getReactNativePersistence(AsyncStorage)
});

export class FirebaseService {
  static async signInWithPhone(verificationId: string, code: string) {
    try {
      const credential = PhoneAuthProvider.credential(verificationId, code);
      const result = await signInWithCredential(auth, credential);
      const token = await result.user.getIdToken();
      return { user: result.user, token };
    } catch (error) {
      console.error('Phone sign in error:', error);
      throw error;
    }
  }

  static async signInWithGoogle() {
    try {
      const provider = new GoogleAuthProvider();
      const result = await signInWithPopup(auth, provider);
      const token = await result.user.getIdToken();
      return { user: result.user, token };
    } catch (error) {
      console.error('Google sign in error:', error);
      throw error;
    }
  }

  static async sendVerificationCode(phoneNumber: string) {
    try {
      const provider = new PhoneAuthProvider(auth);
      // For React Native, we need to use the reCAPTCHA verifier differently
      // We'll use Firebase Phone Auth for React Native
      return await provider.verifyPhoneNumber(
        phoneNumber,
        // The RecaptchaVerifier is handled differently in React Native
        // We'll use Firebase Phone Auth for React Native's built-in verification
        undefined as any
      );
    } catch (error) {
      console.error('Send verification code error:', error);
      throw error;
    }
  }

  static async signOut() {
    try {
      await auth.signOut();
    } catch (error) {
      console.error('Sign out error:', error);
      throw error;
    }
  }

  // Add auth state listener
  static onAuthStateChanged(callback: (user: any) => void) {
    return auth.onAuthStateChanged(callback);
  }

  // Get current user
  static getCurrentUser() {
    return auth.currentUser;
  }
} 