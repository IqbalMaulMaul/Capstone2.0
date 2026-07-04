import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, TextInput, TouchableOpacity, ScrollView, ActivityIndicator, Alert, KeyboardAvoidingView, Platform } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Spacing, BorderRadius, FontSize, Shadows } from '../../constants/Colors';
import { adminService } from '../../services/admin';
import { RoomFormData } from '../../types';

export default function RoomFormScreen() {
  const params = useLocalSearchParams();
  const router = useRouter();
  const isEditing = !!params.id;

  const [loading, setLoading] = useState(false);
  const [initialLoading, setInitialLoading] = useState(true);

  const [roomNumber, setRoomNumber] = useState('');
  const [floor, setFloor] = useState('1');
  const [isActive, setIsActive] = useState(true);
  const [regenerateToken, setRegenerateToken] = useState(false);

  useEffect(() => {
    const init = async () => {
      try {
        if (isEditing) {
          const rooms = await adminService.getRooms();
          const room = rooms.find(r => r.id === Number(params.id));
          if (room) {
            setRoomNumber(room.room_number);
            setFloor(room.floor.toString());
            setIsActive(room.is_active);
          }
        }
      } catch (e: any) {
        Alert.alert('Error', 'Gagal memuat data kamar');
      } finally {
        setInitialLoading(false);
      }
    };
    init();
  }, [params.id]);

  const handleSave = async () => {
    if (!roomNumber.trim() || !floor) {
      Alert.alert('Error', 'Nomor kamar dan lantai wajib diisi');
      return;
    }

    setLoading(true);
    try {
      const data: RoomFormData = {
        room_number: roomNumber.trim(),
        floor: Number(floor),
        is_active: isActive,
        regenerate_token: regenerateToken,
      };

      if (isEditing) {
        await adminService.updateRoom(Number(params.id), data);
        Alert.alert('Sukses', 'Kamar berhasil diupdate', [{ text: 'OK', onPress: () => router.back() }]);
      } else {
        await adminService.createRoom(data);
        Alert.alert('Sukses', 'Kamar berhasil ditambahkan', [{ text: 'OK', onPress: () => router.back() }]);
      }
    } catch (e: any) {
      Alert.alert('Error', e.message || 'Gagal menyimpan kamar');
    } finally {
      setLoading(false);
    }
  };

  if (initialLoading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color={Colors.primary} />
      </View>
    );
  }

  return (
    <KeyboardAvoidingView 
      style={styles.container} 
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <MaterialIcons name="arrow-back" size={24} color={Colors.text} />
        </TouchableOpacity>
        <Text style={styles.title}>{isEditing ? 'Edit Kamar' : 'Tambah Kamar Baru'}</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scroll}>
        <View style={styles.formGroup}>
          <Text style={styles.label}>Nomor Kamar</Text>
          <TextInput
            style={styles.input}
            placeholder="Misal: 101"
            placeholderTextColor={Colors.textMuted}
            value={roomNumber}
            onChangeText={setRoomNumber}
          />
        </View>

        <View style={styles.formGroup}>
          <Text style={styles.label}>Lantai</Text>
          <TextInput
            style={styles.input}
            placeholder="Misal: 1"
            placeholderTextColor={Colors.textMuted}
            keyboardType="numeric"
            value={floor}
            onChangeText={setFloor}
          />
        </View>

        <View style={styles.switchGroup}>
          <Text style={styles.label}>Kamar Aktif</Text>
          <TouchableOpacity 
            style={[styles.switch, isActive ? styles.switchOn : styles.switchOff]}
            onPress={() => setIsActive(!isActive)}
          >
            <View style={[styles.switchThumb, isActive ? styles.switchThumbOn : styles.switchThumbOff]} />
          </TouchableOpacity>
        </View>

        {isEditing && (
          <View style={styles.switchGroup}>
            <View>
              <Text style={styles.label}>Generate Ulang QR Code</Text>
              <Text style={styles.helperText}>Akan mereset URL menu untuk kamar ini</Text>
            </View>
            <TouchableOpacity 
              style={[styles.switch, regenerateToken ? styles.switchOn : styles.switchOff]}
              onPress={() => setRegenerateToken(!regenerateToken)}
            >
              <View style={[styles.switchThumb, regenerateToken ? styles.switchThumbOn : styles.switchThumbOff]} />
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>

      <View style={styles.footer}>
        <TouchableOpacity style={styles.saveBtn} onPress={handleSave} disabled={loading}>
          {loading ? (
            <ActivityIndicator color={Colors.white} />
          ) : (
            <Text style={styles.saveBtnText}>Simpan Kamar</Text>
          )}
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
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
    backgroundColor: Colors.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: Spacing.lg,
    backgroundColor: Colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
    paddingTop: Platform.OS === 'ios' ? 60 : Spacing.lg,
  },
  backBtn: {
    padding: Spacing.sm,
  },
  title: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
  },
  scroll: {
    padding: Spacing.lg,
  },
  formGroup: {
    marginBottom: Spacing.lg,
  },
  label: {
    fontSize: FontSize.sm,
    fontWeight: '600',
    color: Colors.textSecondary,
    marginBottom: Spacing.sm,
  },
  input: {
    backgroundColor: Colors.surface,
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: BorderRadius.md,
    padding: Spacing.md,
    color: Colors.text,
    fontSize: FontSize.md,
  },
  switchGroup: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.xl,
    backgroundColor: Colors.surface,
    padding: Spacing.md,
    borderRadius: BorderRadius.md,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  helperText: {
    fontSize: 12,
    color: Colors.textMuted,
    marginTop: 2,
  },
  switch: {
    width: 50,
    height: 28,
    borderRadius: 14,
    justifyContent: 'center',
    padding: 2,
  },
  switchOn: {
    backgroundColor: Colors.primary,
  },
  switchOff: {
    backgroundColor: Colors.surfaceLight,
  },
  switchThumb: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: Colors.white,
  },
  switchThumbOn: {
    alignSelf: 'flex-end',
  },
  switchThumbOff: {
    alignSelf: 'flex-start',
  },
  footer: {
    padding: Spacing.lg,
    backgroundColor: Colors.surface,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    paddingBottom: Platform.OS === 'ios' ? 32 : Spacing.lg,
  },
  saveBtn: {
    backgroundColor: Colors.primary,
    height: 52,
    borderRadius: BorderRadius.md,
    justifyContent: 'center',
    alignItems: 'center',
    ...Shadows.glow(Colors.primary),
  },
  saveBtnText: {
    color: Colors.white,
    fontSize: FontSize.lg,
    fontWeight: 'bold',
  },
});
