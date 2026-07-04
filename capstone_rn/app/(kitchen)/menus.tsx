import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, SectionList, RefreshControl, TouchableOpacity, ActivityIndicator, Alert } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Spacing, BorderRadius, FontSize } from '../../constants/Colors';
import { kitchenService } from '../../services/kitchen';
import { KitchenCategory, KitchenMenu } from '../../types';
import { StatusBar } from 'expo-status-bar';

export default function KitchenMenusScreen() {
  const [categories, setCategories] = useState<KitchenCategory[]>([]);
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);

  const loadData = async () => {
    try {
      const data = await kitchenService.getOrders();
      setCategories(data.categories);
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Gagal memuat menu');
    } finally {
      setLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadData();
    setRefreshing(false);
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleToggle = async (menuId: number) => {
    try {
      const updated = await kitchenService.toggleMenu(menuId);
      
      // Update local state
      const newCats = categories.map(cat => ({
        ...cat,
        menus: cat.menus.map(m => m.id === menuId ? { ...m, is_available: updated.is_available } : m)
      }));
      setCategories(newCats);
    } catch (error: any) {
      Alert.alert('Error', 'Gagal mengubah status stok');
    }
  };

  // Convert categories to SectionList format
  const sections = categories.map(c => ({
    title: c.name,
    data: c.menus,
  }));

  const renderItem = ({ item }: { item: KitchenMenu }) => (
    <View style={styles.menuItem}>
      <Text style={styles.menuName}>{item.name}</Text>
      
      <TouchableOpacity 
        style={[styles.toggleBtn, item.is_available ? styles.toggleActive : styles.toggleInactive]}
        onPress={() => handleToggle(item.id)}
      >
        <Text style={item.is_available ? styles.toggleActiveText : styles.toggleInactiveText}>
          {item.is_available ? 'Tersedia' : 'Habis'}
        </Text>
      </TouchableOpacity>
    </View>
  );

  const renderSectionHeader = ({ section: { title } }: any) => (
    <View style={styles.sectionHeader}>
      <Text style={styles.sectionTitle}>{title}</Text>
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
        <SectionList
          sections={sections}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderItem}
          renderSectionHeader={renderSectionHeader}
          contentContainerStyle={styles.list}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={Colors.primary} />}
          ListEmptyComponent={
            <View style={styles.empty}>
              <MaterialIcons name="fastfood" size={64} color={Colors.textMuted} />
              <Text style={styles.emptyText}>Tidak ada data menu.</Text>
            </View>
          }
        />
      )}
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
  sectionHeader: {
    backgroundColor: Colors.surface,
    padding: Spacing.md,
    borderRadius: BorderRadius.md,
    marginTop: Spacing.md,
    marginBottom: Spacing.xs,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  sectionTitle: {
    fontSize: FontSize.md,
    fontWeight: 'bold',
    color: Colors.text,
  },
  menuItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: Colors.background,
    padding: Spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  menuName: {
    fontSize: FontSize.md,
    color: Colors.text,
    flex: 1,
    marginRight: Spacing.md,
  },
  toggleBtn: {
    paddingHorizontal: Spacing.md,
    paddingVertical: 6,
    borderRadius: BorderRadius.full,
    minWidth: 90,
    alignItems: 'center',
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
  empty: {
    alignItems: 'center',
    marginTop: Spacing.xxxl * 2,
  },
  emptyText: {
    marginTop: Spacing.md,
    color: Colors.textMuted,
    fontSize: FontSize.md,
  },
});
