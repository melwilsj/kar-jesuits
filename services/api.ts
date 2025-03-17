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
      useAuth.getState().logout();
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
    try {
      await api.post('/auth/logout');
    } finally {
      // Always clear auth state, even if API call fails
      useAuth.getState().logout();
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
}; 