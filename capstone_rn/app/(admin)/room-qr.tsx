import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Share } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import QRCode from 'react-native-qrcode-svg';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Spacing, BorderRadius, FontSize, Shadows } from '../../constants/Colors';
import { StatusBar } from 'expo-status-bar';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

export default function RoomQRScreen() {
  const { url, room } = useLocalSearchParams();
  const router = useRouter();
  const insets = useSafeAreaInsets();

  const handleShare = async () => {
    try {
      await Share.share({
        message: `Pesan makanan untuk Kamar ${room} melalui link berikut:\n\n${url}`,
        url: url as string,
        title: `QR Code Kamar ${room}`,
      });
    } catch (error: any) {
      console.error(error.message);
    }
  };

  return (
    <View style={styles.container}>
      <StatusBar style="dark" />
      <View style={[styles.header, { paddingTop: insets.top + Spacing.md }]}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <MaterialIcons name="close" size={24} color={Colors.text} />
        </TouchableOpacity>
        <Text style={styles.title}>QR Code Kamar {room}</Text>
        <View style={{ width: 24 }} />
      </View>

      <View style={styles.content}>
        <View style={styles.qrCard}>
          <Text style={styles.scanText}>Scan untuk Memesan</Text>
          <View style={styles.qrWrapper}>
            <QRCode
              value={url as string}
              size={200}
              color={Colors.black}
              backgroundColor={Colors.white}
            />
          </View>
          <Text style={styles.roomText}>Kamar {room}</Text>
        </View>

        <TouchableOpacity style={styles.shareBtn} onPress={handleShare}>
          <MaterialIcons name="share" size={20} color={Colors.white} />
          <Text style={styles.shareText}>Bagikan Link</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: Spacing.lg,
  },
  backBtn: {
    padding: Spacing.sm,
  },
  title: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
  },
  content: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: Spacing.xl,
  },
  qrCard: {
    backgroundColor: Colors.white,
    borderRadius: BorderRadius.xl,
    padding: Spacing.xxxl,
    alignItems: 'center',
    ...Shadows.large,
  },
  scanText: {
    fontSize: FontSize.md,
    color: Colors.textInverse,
    marginBottom: Spacing.lg,
    fontWeight: '600',
  },
  qrWrapper: {
    padding: Spacing.sm,
    backgroundColor: Colors.white,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    borderRadius: BorderRadius.md,
  },
  roomText: {
    fontSize: FontSize.xxl,
    fontWeight: 'bold',
    color: Colors.textInverse,
    marginTop: Spacing.lg,
  },
  shareBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.primary,
    paddingHorizontal: Spacing.xxl,
    paddingVertical: Spacing.lg,
    borderRadius: BorderRadius.full,
    marginTop: Spacing.xxxl,
    gap: Spacing.sm,
    ...Shadows.glow(Colors.primary),
  },
  shareText: {
    color: Colors.white,
    fontSize: FontSize.md,
    fontWeight: 'bold',
  },
});
