import axios from 'axios';
import { API_CONFIG } from '../constants/Config';
import { useAuth } from '../hooks/useAuth';

const api = axios.create({
  baseURL: API_CONFIG.baseURL,
  timeout: API_CONFIG.timeout,
});

// Add auth token to requests
api.interceptors.request.use((config) => {
  const token = useAuth.getState().token;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export const authAPI = {
  phoneLogin: async (phoneNumber: string, verificationCode: string, idToken: string) => {
    const response = await api.post('/auth/phone/login', {
      phone_number: phoneNumber,
      verification_code: verificationCode,
      id_token: idToken,
    });
    return response.data;
  },

  googleLogin: async (idToken: string) => {
    try {
      const response = await api.post('/auth/google/login', {
        id_token: idToken,
        provider: 'google'
      });
      console.log('API Response:', response.data);
      return response;
    } catch (error:any) {
      console.error('API Error:', error.response?.data || error);
      throw error;
    }
  },

  logout: async () => {
    const response = await api.post('/auth/logout');
    return response.data;
  },
}; 