import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity, Image, Alert, ActivityIndicator } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Spacing, BorderRadius, FontSize, Shadows } from '../../constants/Colors';
import { adminService } from '../../services/admin';
import { Menu } from '../../types';
import { useRouter } from 'expo-router';
import { StatusBar } from 'expo-status-bar';

export default function MenusScreen() {
  const [menus, setMenus] = useState<Menu[]>([]);
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  const loadMenus = async () => {
    try {
      const data = await adminService.getMenus();
      setMenus(data);
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Gagal memuat menu');
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadMenus();
    setRefreshing(false);
  };

  useEffect(() => {
    loadMenus();
  }, []);

  const handleToggle = async (id: number) => {
    try {
      const updated = await adminService.toggleMenu(id);
      setMenus(menus.map(m => m.id === updated.id ? { ...m, is_available: updated.is_available } : m));
    } catch (error: any) {
      Alert.alert('Error', 'Gagal mengubah status menu');
    }
  };

  const renderItem = ({ item }: { item: Menu }) => (
    <View style={styles.card}>
      <View style={styles.cardContent}>
        {item.image_url ? (
          <Image source={{ uri: item.image_url }} style={styles.image} />
        ) : (
          <View style={styles.imagePlaceholder}>
            <MaterialIcons name="restaurant" size={32} color={Colors.textMuted} />
          </View>
        )}
        <View style={styles.info}>
          <Text style={styles.name}>{item.name}</Text>
          <Text style={styles.category}>{item.category_name}</Text>
          <Text style={styles.price}>{item.formatted_price}</Text>
        </View>
      </View>
      <View style={styles.actions}>
        <TouchableOpacity 
          style={[styles.toggleBtn, item.is_available ? styles.toggleActive : styles.toggleInactive]}
          onPress={() => handleToggle(item.id)}
        >
          <Text style={item.is_available ? styles.toggleActiveText : styles.toggleInactiveText}>
            {item.is_available ? 'Tersedia' : 'Habis'}
          </Text>
        </TouchableOpacity>
        <View style={{ flexDirection: 'row', gap: Spacing.sm }}>
          <TouchableOpacity 
            style={styles.iconBtn}
            onPress={() => router.push({ pathname: '/(admin)/menu-form', params: { id: item.id } })}
          >
            <MaterialIcons name="edit" size={20} color={Colors.primary} />
          </TouchableOpacity>
          <TouchableOpacity 
            style={styles.iconBtn}
            onPress={() => {
              Alert.alert('Hapus Menu', `Yakin ingin menghapus ${item.name}?`, [
                { text: 'Batal', style: 'cancel' },
                { 
                  text: 'Hapus', 
                  style: 'destructive',
                  onPress: async () => {
                    try {
                      await adminService.deleteMenu(item.id);
                      setMenus(menus.filter(m => m.id !== item.id));
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
          data={menus}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.list}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={Colors.primary} />}
          ListEmptyComponent={
            <View style={styles.empty}>
              <MaterialIcons name="restaurant-menu" size={64} color={Colors.textMuted} />
              <Text style={styles.emptyText}>Belum ada menu.</Text>
            </View>
          }
        />
      )}
      <TouchableOpacity 
        style={styles.fab}
        onPress={() => router.push('/(admin)/menu-form')}
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
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: Colors.border,
  },
  cardContent: {
    flexDirection: 'row',
    padding: Spacing.md,
  },
  image: {
    width: 80,
    height: 80,
    borderRadius: BorderRadius.md,
  },
  imagePlaceholder: {
    width: 80,
    height: 80,
    borderRadius: BorderRadius.md,
    backgroundColor: Colors.surfaceLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  info: {
    flex: 1,
    marginLeft: Spacing.md,
    justifyContent: 'center',
  },
  name: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
  },
  category: {
    fontSize: FontSize.sm,
    color: Colors.textSecondary,
    marginTop: 2,
  },
  price: {
    fontSize: FontSize.md,
    fontWeight: '600',
    color: Colors.primary,
    marginTop: 4,
  },
  actions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: Spacing.sm,
    backgroundColor: Colors.surfaceLight,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  toggleBtn: {
    paddingHorizontal: Spacing.md,
    paddingVertical: 6,
    borderRadius: BorderRadius.full,
  },
  toggleActive: {
    backgroundColor: Colors.successBg,
  },
  toggleInactive: {
    backgroundColor: Colors.errorBg,
  },
  toggleActiveText: {
    color: Colors.success,
    fontWeight: '600',
    fontSize: FontSize.sm,
  },
  toggleInactiveText: {
    color: Colors.error,
    fontWeight: '600',
    fontSize: FontSize.sm,
  },
  iconBtn: {
    padding: Spacing.sm,
    backgroundColor: Colors.surface,
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
