import React, { useEffect } from 'react';
import { Stack, useRouter, useSegments } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { View, ActivityIndicator, StyleSheet } from 'react-native';
import { useAuthStore } from '../stores/authStore';
import { Colors } from '../constants/Colors';
import { notificationService } from '../services/notifications';

export default function RootLayout() {
  const { isLoggedIn, isLoading, user, initialize } = useAuthStore();
  const segments = useSegments();
  const router = useRouter();

  useEffect(() => {
    initialize();
  }, []);

  // Handle notification taps
  useEffect(() => {
    const subscription = notificationService.addNotificationResponseListener((response) => {
      const data = response.notification.request.content.data;
      if (data?.type === 'new_order' && user?.role === 'kitchen') {
        router.replace('/(kitchen)');
      }
    });

    return () => subscription.remove();
  }, [user]);

  // Auth-based routing
  useEffect(() => {
    if (isLoading) return;

    const inAuthGroup = segments[0] === '(auth)';

    if (!isLoggedIn && !inAuthGroup) {
      router.replace('/(auth)/login');
    } else if (isLoggedIn && inAuthGroup) {
      // Redirect based on role
      if (user?.role === 'owner') {
        router.replace('/(admin)');
      } else if (user?.role === 'kitchen') {
        router.replace('/(kitchen)');
      } else if (user?.role === 'finance') {
        router.replace('/(finance)');
      }
    }
  }, [isLoggedIn, isLoading, segments]);

  if (isLoading) {
    return (
      <View style={styles.loading}>
        <ActivityIndicator size="large" color={Colors.primary} />
        <StatusBar style="light" />
      </View>
    );
  }

  return (
    <>
      <StatusBar style="light" />
      <Stack
        screenOptions={{
          headerShown: false,
          contentStyle: { backgroundColor: Colors.background },
          animation: 'fade',
        }}
      >
        <Stack.Screen name="(auth)" />
        <Stack.Screen name="(admin)" />
        <Stack.Screen name="(kitchen)" />
        <Stack.Screen name="(finance)" />
      </Stack>
    </>
  );
}

const styles = StyleSheet.create({
  loading: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: Colors.background,
  },
});
