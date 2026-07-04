import { create } from 'zustand';
import { User } from '../types';
import { authService } from '../services/auth';
import { notificationService } from '../services/notifications';

interface AuthState {
  user: User | null;
  isLoggedIn: boolean;
  isLoading: boolean;
  pushToken: string | null;

  // Actions
  initialize: () => Promise<void>;
  login: (email: string, password: string, baseUrl?: string) => Promise<void>;
  logout: () => Promise<void>;
  setUser: (user: User) => void;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  isLoggedIn: false,
  isLoading: true,
  pushToken: null,

  initialize: async () => {
    try {
      const loggedIn = await authService.isLoggedIn();
      if (loggedIn) {
        const user = await authService.getUser();
        if (user) {
          set({ user, isLoggedIn: true, isLoading: false });

          // Register push notifications
          const token = await notificationService.registerForPushNotifications();
          if (token) {
            set({ pushToken: token });
          }
          return;
        }
      }
      set({ user: null, isLoggedIn: false, isLoading: false });
    } catch (e) {
      set({ user: null, isLoggedIn: false, isLoading: false });
    }
  },

  login: async (email: string, password: string, baseUrl?: string) => {
    const response = await authService.login(email, password, baseUrl);
    const user = response.data.user;
    set({ user, isLoggedIn: true });

    // Register push notifications after login
    try {
      const token = await notificationService.registerForPushNotifications();
      if (token) {
        set({ pushToken: token });
      }
    } catch (e) {
      console.error('Push token registration failed:', e);
    }
  },

  logout: async () => {
    const { pushToken } = get();
    await authService.logout(pushToken || undefined);
    set({ user: null, isLoggedIn: false, pushToken: null });
  },

  setUser: (user: User) => set({ user }),
}));
