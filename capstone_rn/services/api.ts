import axios, { AxiosInstance, InternalAxiosRequestConfig, AxiosError } from 'axios';
import * as SecureStore from 'expo-secure-store';
import { Config } from '../constants/Config';
import { Alert } from 'react-native';

const TOKEN_KEY = 'auth_token';
const BASE_URL_KEY = 'api_base_url';

class ApiClient {
  private instance: AxiosInstance;

  constructor() {
    this.instance = axios.create({
      baseURL: Config.API_BASE_URL,
      timeout: 15000,
      headers: {
        'Accept': 'application/json',
      },
    });

    // Request interceptor — attach auth token
    this.instance.interceptors.request.use(
      async (config: InternalAxiosRequestConfig) => {
        // Check for custom base URL
        const customUrl = await SecureStore.getItemAsync(BASE_URL_KEY);
        if (customUrl) {
          config.baseURL = customUrl;
        }

        const token = await SecureStore.getItemAsync(TOKEN_KEY);
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
      },
      (error) => Promise.reject(error)
    );

    // Response interceptor — handle errors
    this.instance.interceptors.response.use(
      (response) => response,
      (error: AxiosError<any>) => {
        if (error.response?.status === 401) {
          // Token expired or invalid — will be handled by auth store
          SecureStore.deleteItemAsync(TOKEN_KEY);
        }

        // Extract error message
        const message =
          error.response?.data?.message ||
          error.response?.data?.errors?.[Object.keys(error.response?.data?.errors || {})[0]]?.[0] ||
          error.message ||
          'Terjadi kesalahan. Coba lagi.';

        return Promise.reject(new Error(message));
      }
    );
  }

  get client(): AxiosInstance {
    return this.instance;
  }

  // Token management
  async setToken(token: string): Promise<void> {
    await SecureStore.setItemAsync(TOKEN_KEY, token);
  }

  async getToken(): Promise<string | null> {
    return await SecureStore.getItemAsync(TOKEN_KEY);
  }

  async removeToken(): Promise<void> {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
  }

  // Base URL management
  async setBaseUrl(url: string): Promise<void> {
    await SecureStore.setItemAsync(BASE_URL_KEY, url);
    this.instance.defaults.baseURL = url;
  }

  async getBaseUrl(): Promise<string> {
    const url = await SecureStore.getItemAsync(BASE_URL_KEY);
    return url || Config.API_BASE_URL;
  }

  async resetBaseUrl(): Promise<void> {
    await SecureStore.deleteItemAsync(BASE_URL_KEY);
    this.instance.defaults.baseURL = Config.API_BASE_URL;
  }
}

export const apiClient = new ApiClient();
export const api = apiClient.client;
export default api;
