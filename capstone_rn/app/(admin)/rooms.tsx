import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity, Alert, ActivityIndicator } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Spacing, BorderRadius, FontSize, Shadows } from '../../constants/Colors';
import { adminService } from '../../services/admin';
import { Room } from '../../types';
import { useRouter } from 'expo-router';
import { StatusBar } from 'expo-status-bar';

export default function RoomsScreen() {
  const [rooms, setRooms] = useState<Room[]>([]);
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  const loadRooms = async () => {
    try {
      const data = await adminService.getRooms();
      setRooms(data);
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Gagal memuat kamar');
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadRooms();
    setRefreshing(false);
  };

  useEffect(() => {
    loadRooms();
  }, []);

  const renderItem = ({ item }: { item: Room }) => (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <View style={styles.roomInfo}>
          <Text style={styles.roomNumber}>Kamar {item.room_number}</Text>
          <Text style={styles.floor}>Lantai {item.floor}</Text>
        </View>
        <View style={[styles.statusBadge, item.is_active ? styles.statusActive : styles.statusInactive]}>
          <Text style={item.is_active ? styles.statusActiveText : styles.statusInactiveText}>
            {item.is_active ? 'Aktif' : 'Nonaktif'}
          </Text>
        </View>
      </View>

      <View style={styles.cardActions}>
        <TouchableOpacity 
          style={styles.qrBtn}
          onPress={() => router.push({ pathname: '/(admin)/room-qr', params: { url: item.qr_url, room: item.room_number } })}
        >
          <MaterialIcons name="qr-code-2" size={20} color={Colors.white} />
          <Text style={styles.qrBtnText}>Lihat QR Code</Text>
        </TouchableOpacity>

        <View style={styles.actionButtons}>
          <TouchableOpacity 
            style={styles.iconBtn}
            onPress={() => router.push({ pathname: '/(admin)/room-form', params: { id: item.id } })}
          >
            <MaterialIcons name="edit" size={20} color={Colors.primary} />
          </TouchableOpacity>
          <TouchableOpacity 
            style={styles.iconBtn}
            onPress={() => {
              Alert.alert('Hapus Kamar', `Yakin ingin menghapus Kamar ${item.room_number}?`, [
                { text: 'Batal', style: 'cancel' },
                { 
                  text: 'Hapus', 
                  style: 'destructive',
                  onPress: async () => {
                    try {
                      await adminService.deleteRoom(item.id);
                      setRooms(rooms.filter(r => r.id !== item.id));
                    } catch (e: any) {
                      Alert.alert('Error', e.message);
                    }
                  }
                }
              ]);
            }}
          >
            <MaterialIcons name="delete" size={20} color={Colors.error} />
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );

  return (
    <View style={styles.container}>
      <StatusBar style="light" />
      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={Colors.primary} />
        </View>
      ) : (
        <FlatList
          data={rooms}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.list}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={Colors.primary} />}
          ListEmptyComponent={
            <View style={styles.empty}>
              <MaterialIcons name="meeting-room" size={64} color={Colors.textMuted} />
              <Text style={styles.emptyText}>Belum ada kamar.</Text>
            </View>
          }
        />
      )}
      <TouchableOpacity 
        style={styles.fab}
        onPress={() => router.push('/(admin)/room-form')}
      >
        <MaterialIcons name="add" size={24} color={Colors.white} />
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  center: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  list: {
    padding: Spacing.md,
  },
  card: {
    backgroundColor: Colors.surface,
    borderRadius: BorderRadius.lg,
    marginBottom: Spacing.md,
    borderWidth: 1,
    borderColor: Colors.border,
    padding: Spacing.md,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.md,
  },
  roomInfo: {},
  roomNumber: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
  },
  floor: {
    fontSize: FontSize.sm,
    color: Colors.textSecondary,
    marginTop: 2,
  },
  statusBadge: {
    paddingHorizontal: Spacing.sm,
    paddingVertical: 4,
    borderRadius: BorderRadius.md,
  },
  statusActive: {
    backgroundColor: Colors.successBg,
  },
  statusInactive: {
    backgroundColor: Colors.errorBg,
  },
  statusActiveText: {
    color: Colors.success,
    fontSize: 12,
    fontWeight: '600',
  },
  statusInactiveText: {
    color: Colors.error,
    fontSize: 12,
    fontWeight: '600',
  },
  cardActions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    paddingTop: Spacing.md,
  },
  qrBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.primary,
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    borderRadius: BorderRadius.md,
    gap: Spacing.xs,
  },
  qrBtnText: {
    color: Colors.white,
    fontWeight: '600',
    fontSize: FontSize.sm,
  },
  actionButtons: {
    flexDirection: 'row',
    gap: Spacing.sm,
  },
  iconBtn: {
    padding: Spacing.sm,
    backgroundColor: Colors.surfaceLight,
    borderRadius: BorderRadius.full,
  },
  empty: {
    alignItems: 'center',
    marginTop: Spacing.xxxl * 2,
  },
  emptyText: {
    marginTop: Spacing.md,
    color: Colors.textMuted,
    fontSize: FontSize.md,
  },
  fab: {
    position: 'absolute',
    bottom: Spacing.xxl,
    right: Spacing.xxl,
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: Colors.primary,
    justifyContent: 'center',
    alignItems: 'center',
    ...Shadows.glow(Colors.primary),
  },
});
