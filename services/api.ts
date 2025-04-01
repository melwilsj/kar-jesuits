import axios from 'axios';
import { API_CONFIG } from '../constants/Config';
import { useAuth } from '../hooks/useAuth';
import { FirebaseService } from './firebase';
import { handleApiError } from '../utils/error';

export const api = axios.create({
  baseURL: API_CONFIG.baseURL
});

// Add auth token to requests
api.interceptors.request.use((config) => {
  const token = useAuth.getState().token;
  if (token) {
    console.log('Bearer Token:', token);
    console.log('Config:', config.url);
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Add response interceptor for unauthorized handling
api.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      // On 401, just logout
      // Avoid calling logout directly here to prevent loops if the unregister call fails
      // The logout logic in useAuth should handle clearing state
      console.warn('API request unauthorized (401). Logging out.');
      useAuth.getState().logout(false); // Pass false to prevent recursive unregister call
      return Promise.reject(new Error('Session expired. Please login again.'));
    }
    return Promise.reject(error);
  }
);

export const authAPI = {
  phoneLogin: async (phoneNumber: string, verificationCode: string, idToken: string) => {
    const response = await api.post('/auth/phone/login', {
      phone_number: phoneNumber,
      verification_code: verificationCode,
      id_token: idToken,
    });
    return response;
  },

  googleLogin: async (idToken: string) => {
    try {
      const response = await api.post('/auth/google/login', {
        id_token: idToken,
        provider: 'google'
      });
      return response;
    } catch (error:any) {
      console.error('API Error:', error.response?.data || error);
      throw error;
    }
  },

  logout: async () => {
    // Logout API call is now handled within useAuth.logout to ensure FCM unregistration
    // This function can be kept for potential direct calls if needed, but the primary flow is via useAuth
    try {
        await api.post('/auth/logout');
    } catch (error) {
        console.error("API Logout error:", error);
        // Don't re-throw here, let the main logout flow continue
    }
  },

  // Add FCM token registration/unregistration functions
  registerFcmToken: async (fcmToken: string) => {
    try {
      console.log('Registering FCM token:', fcmToken);
      const response = await api.post('/fcm/register', { fcm_token: fcmToken });
      console.log('FCM Token registered successfully');
      return response.data;
    } catch (error) {
      console.error('Error registering FCM token:', handleApiError(error));
      throw error; // Re-throw to handle it in the calling function if needed
    }
  },

  unregisterFcmToken: async (fcmToken: string) => {
    try {
      console.log('Unregistering FCM token:', fcmToken);
      const response = await api.post('/fcm/unregister', { fcm_token: fcmToken });
      console.log('FCM Token unregistered successfully');
      return response.data;
    } catch (error) {
      // Log the error but don't prevent logout if unregistration fails
      console.error('Error unregistering FCM token:', handleApiError(error));
    }
  },
};

const queue = new Set<Promise<any>>();
const MAX_RETRIES = 3;

// Define which HTTP status codes should trigger a retry
const RETRYABLE_STATUS_CODES = new Set([
  408, // Request Timeout
  429, // Too Many Requests
  500, // Internal Server Error
  502, // Bad Gateway
  503, // Service Unavailable
  504  // Gateway Timeout
]);

// Define network error types that should trigger a retry
const RETRYABLE_ERROR_CODES = new Set([
  'ECONNRESET',
  'ETIMEDOUT',
  'ECONNREFUSED',
  'NETWORK_ERROR'
]);

const shouldRetry = (error: any): boolean => {
  // Don't retry if it's a client error (except specific cases)
  if (error.response?.status && error.response.status < 500) {
    return RETRYABLE_STATUS_CODES.has(error.response.status);
  }

  // Retry on network errors
  if (axios.isAxiosError(error)) {
    if (error.code) {
      return RETRYABLE_ERROR_CODES.has(error.code);
    }
    // Retry if there's no response (network error)
    return !error.response;
  }

  return false;
};

export const executeWithRetry:any = async (request: () => Promise<any>, retries = 0) => {
  try {
    const promise = request();
    queue.add(promise);
    
    const response = await promise;
    queue.delete(promise);
    return response;
  } catch (error) {
    queue.delete(request());
    
    if (retries < MAX_RETRIES && shouldRetry(error)) {
      await new Promise(resolve => setTimeout(resolve, 1000 * (retries + 1)));
      return executeWithRetry(request, retries + 1);
    }
    throw handleApiError(error);
  }
};

export const dataAPI = {
  fetchJesuits: () => executeWithRetry(() => api.get('/province-jesuits')),
  fetchCommunities: () => executeWithRetry(() => api.get('/province-communities')),
  fetchCurrentJesuit: () => executeWithRetry(() => api.get(`/current-jesuit`)),
  fetchJesuitsInFormation: (page: number) => executeWithRetry(() => api.get(`/province/jesuits/formation?page=${page}`)),
  fetchJesuitsInCommonHouses: (page: number) => executeWithRetry(() => api.get(`/province/jesuits/common-houses?page=${page}`)),
  fetchJesuitsInOtherProvinces: (page: number) => executeWithRetry(() => api.get(`/province/jesuits/other-provinces?page=${page}`)),
  fetchJesuitsOutsideIndia: (page: number) => executeWithRetry(() => api.get(`/province/jesuits/outside-india?page=${page}`)),
  fetchOtherProvinceJesuitsResiding: (page: number) => executeWithRetry(() => api.get(`/province/jesuits/other-residing?page=${page}`)),
  fetchInstitutions: () => executeWithRetry(() => api.get('/province/institutions')),
  fetchEducationalInstitutions: () => executeWithRetry(() => api.get('/province/institutions/educational')),
  fetchSocialCenters: () => executeWithRetry(() => api.get('/province/institutions/social-centers')),
  fetchParishes: () => executeWithRetry(() => api.get('/province/institutions/parishes')),
  fetchAllCommissions: () => executeWithRetry(() => api.get('/province/commissions')),
  fetchCommissionsByType: (type: string) => executeWithRetry(() => api.get(`/province/commissions/${type}`)),
  fetchAgeDistributionStats: () => executeWithRetry(() => api.get('/province/statistics/age-distribution')),
  fetchFormationStats: () => executeWithRetry(() => api.get('/province/statistics/formation')),
  fetchGeographicalStats: () => executeWithRetry(() => api.get('/province/statistics/geographical')),
  fetchMinistryStats: () => executeWithRetry(() => api.get('/province/statistics/ministry')),
  fetchYearlyTrendsStats: () => executeWithRetry(() => api.get('/province/statistics/yearly-trends')),
  fetchUpcomingEvents: () => executeWithRetry(() => api.get('/events/upcoming')),
  fetchPastEvents: () => executeWithRetry(() => api.get('/events/past')),
  fetchNotifications: () => executeWithRetry(() => api.get('/notifications')),
  markNotificationAsRead: (id: number) => executeWithRetry(() => api.post(`/notifications/${id}/read`)),

}; 
