import { api, apiClient } from './api';
import * as SecureStore from 'expo-secure-store';
import { LoginResponse, User } from '../types';

const USER_KEY = 'user_data';

export const authService = {
  async login(email: string, password: string, baseUrl?: string): Promise<LoginResponse> {
    if (baseUrl) {
      await apiClient.setBaseUrl(baseUrl);
    }

    const response = await api.post<LoginResponse>('/auth/login', {
      email,
      password,
      device_name: 'react-native-app',
    });

    const { token, user } = response.data.data;

    // Save token and user data
    await apiClient.setToken(token);
    await SecureStore.setItemAsync(USER_KEY, JSON.stringify(user));

    return response.data;
  },

  async logout(pushToken?: string): Promise<void> {
    try {
      await api.post('/auth/logout', {
        push_token: pushToken,
      });
    } catch (e) {
      // Ignore logout API errors — still clear local data
    }

    await apiClient.removeToken();
    await SecureStore.deleteItemAsync(USER_KEY);
  },

  async getUser(): Promise<User | null> {
    const data = await SecureStore.getItemAsync(USER_KEY);
    if (data) {
      return JSON.parse(data) as User;
    }
    return null;
  },

  async isLoggedIn(): Promise<boolean> {
    const token = await apiClient.getToken();
    return token !== null;
  },

  async getMe(): Promise<User> {
    const response = await api.get('/auth/me');
    const user = response.data.data;
    await SecureStore.setItemAsync(USER_KEY, JSON.stringify(user));
    return user;
  },

  async registerPushToken(token: string): Promise<void> {
    await api.post('/auth/push-token', {
      token,
      device_name: 'react-native-app',
    });
  },

  async removePushToken(token: string): Promise<void> {
    await api.delete('/auth/push-token', {
      data: { token },
    });
  },
};
