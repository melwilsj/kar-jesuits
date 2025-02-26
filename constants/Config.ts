const FIREBASE_CONFIG = {
  apiKey: process.env.EXPO_PUBLIC_FIREBASE_API_KEY ?? "AIzaSyBe90_lFh9Q30PqedmIfrYAvXjW5g9Xj7c",
  authDomain: process.env.EXPO_PUBLIC_FIREBASE_AUTH_DOMAIN ?? "kpap-sj.firebaseapp.com",
  projectId: process.env.EXPO_PUBLIC_FIREBASE_PROJECT_ID ?? "kpap-sj",
  appId: process.env.EXPO_PUBLIC_FIREBASE_APP_ID ?? "1:397973729528:web:3d00143386cc02bff14b0d",
};

const API_CONFIG = {
  baseURL: process.env.EXPO_PUBLIC_API_URL ?? 'http://localhost:8000/api/v1',
  timeout: 10000,
};

export { FIREBASE_CONFIG, API_CONFIG }; 