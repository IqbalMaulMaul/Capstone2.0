import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity, Alert, ActivityIndicator } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Spacing, BorderRadius, FontSize, Shadows } from '../../constants/Colors';
import { kitchenService } from '../../services/kitchen';
import { KitchenOrder } from '../../types';
import { StatusBar } from 'expo-status-bar';
import { KITCHEN_STATUS_ACTIONS, KITCHEN_STATUS_TRANSITIONS, ORDER_STATUS_COLORS, ORDER_STATUS_ICONS, OrderStatusType } from '../../constants/OrderStatus';

export default function KitchenDashboard() {
  const [orders, setOrders] = useState<KitchenOrder[]>([]);
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);

  const loadData = async () => {
    try {
      const data = await kitchenService.getOrders();
      setOrders(data.orders);
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Gagal memuat pesanan');
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
    
    // Auto refresh every 30 seconds
    const interval = setInterval(() => {
      loadData();
    }, 30000);
    return () => clearInterval(interval);
  }, []);

  const handleUpdateStatus = async (orderId: number, currentStatus: string) => {
    const nextStatus = KITCHEN_STATUS_TRANSITIONS[currentStatus];
    if (!nextStatus) return;

    const actionName = KITCHEN_STATUS_ACTIONS[currentStatus];

    Alert.alert('Update Status', `Apakah Anda yakin ingin melakukan: "${actionName}"?`, [
      { text: 'Batal', style: 'cancel' },
      { 
        text: 'Ya, Update', 
        onPress: async () => {
          try {
            await kitchenService.updateOrderStatus(orderId, nextStatus);
            // Refresh local state without full reload
            setOrders(orders.map(o => o.id === orderId ? { ...o, status: nextStatus, status_label: ORDER_STATUS_LABELS[nextStatus as OrderStatusType] } : o));
            
            // If it's delivered, we can optionally remove it from the list after a delay
            if (nextStatus === 'delivered') {
              setTimeout(() => {
                setOrders(prev => prev.filter(o => o.id !== orderId));
              }, 2000);
            }
          } catch (e: any) {
            Alert.alert('Error', e.message);
          }
        }
      }
    ]);
  };

  const renderItem = ({ item }: { item: KitchenOrder }) => {
    const statusStyle = ORDER_STATUS_COLORS[item.status as OrderStatusType] || ORDER_STATUS_COLORS.pending_payment;
    const statusIcon = ORDER_STATUS_ICONS[item.status as OrderStatusType] || 'info';
    
    const nextStatus = KITCHEN_STATUS_TRANSITIONS[item.status];
    const actionName = KITCHEN_STATUS_ACTIONS[item.status];

    return (
      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <View>
            <Text style={styles.orderNumber}>{item.order_number}</Text>
            <Text style={styles.roomNumber}>Kamar {item.room_number}</Text>
          </View>
          <View style={[styles.badge, { backgroundColor: statusStyle.bg }]}>
            <MaterialIcons name={statusIcon as any} size={14} color={statusStyle.text} />
            <Text style={[styles.badgeText, { color: statusStyle.text }]}>{item.status_label}</Text>
          </View>
        </View>

        <View style={styles.timeRow}>
          <MaterialIcons name="schedule" size={16} color={Colors.textMuted} />
          <Text style={styles.timeText}>Dipesan {item.created_at_human}</Text>
        </View>

        <View style={styles.divider} />

        <View style={styles.itemsList}>
          {item.items.map((menuItem, idx) => (
            <View key={idx} style={styles.menuItemRow}>
              <View style={styles.qtyBadge}>
                <Text style={styles.qtyText}>{menuItem.quantity}x</Text>
              </View>
              <View style={styles.menuItemDetails}>
                <Text style={styles.menuItemName}>{menuItem.menu_name}</Text>
                {menuItem.notes && <Text style={styles.menuItemNotes}>{menuItem.notes}</Text>}
              </View>
            </View>
          ))}
        </View>

        {item.notes && (
          <View style={styles.orderNotes}>
            <MaterialIcons name="speaker-notes" size={16} color={Colors.warning} />
            <Text style={styles.orderNotesText}>Catatan: {item.notes}</Text>
          </View>
        )}

        {actionName && (
          <TouchableOpacity 
            style={[styles.actionBtn, { backgroundColor: statusStyle.text }]}
            onPress={() => handleUpdateStatus(item.id, item.status)}
          >
            <Text style={styles.actionBtnText}>{actionName}</Text>
            <MaterialIcons name="arrow-forward" size={16} color={Colors.white} />
          </TouchableOpacity>
        )}
      </View>
    );
  };

  return (
    <View style={styles.container}>
      <StatusBar style="light" />
      {loading ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={Colors.primary} />
        </View>
      ) : (
        <FlatList
          data={orders}
          keyExtractor={(item) => item.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.list}
          refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={Colors.primary} />}
          ListEmptyComponent={
            <View style={styles.empty}>
              <MaterialIcons name="check-circle-outline" size={64} color={Colors.success} />
              <Text style={styles.emptyText}>Tidak ada pesanan aktif.</Text>
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
  card: {
    backgroundColor: Colors.surface,
    borderRadius: BorderRadius.lg,
    padding: Spacing.md,
    marginBottom: Spacing.md,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadows.small,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: Spacing.sm,
  },
  orderNumber: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
  },
  roomNumber: {
    fontSize: FontSize.md,
    fontWeight: '600',
    color: Colors.secondary,
    marginTop: 2,
  },
  badge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: BorderRadius.full,
    gap: 4,
  },
  badgeText: {
    fontSize: 12,
    fontWeight: '600',
  },
  timeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginBottom: Spacing.sm,
  },
  timeText: {
    fontSize: FontSize.sm,
    color: Colors.textMuted,
  },
  divider: {
    height: 1,
    backgroundColor: Colors.border,
    marginVertical: Spacing.sm,
  },
  itemsList: {
    marginBottom: Spacing.md,
  },
  menuItemRow: {
    flexDirection: 'row',
    marginBottom: Spacing.sm,
    alignItems: 'flex-start',
  },
  qtyBadge: {
    backgroundColor: Colors.surfaceLight,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: BorderRadius.sm,
    minWidth: 32,
    alignItems: 'center',
  },
  qtyText: {
    fontSize: FontSize.sm,
    fontWeight: 'bold',
    color: Colors.text,
  },
  menuItemDetails: {
    flex: 1,
    marginLeft: Spacing.sm,
  },
  menuItemName: {
    fontSize: FontSize.md,
    fontWeight: '600',
    color: Colors.text,
  },
  menuItemNotes: {
    fontSize: FontSize.xs,
    color: Colors.warning,
    marginTop: 2,
    fontStyle: 'italic',
  },
  orderNotes: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: Colors.warningBg,
    padding: Spacing.sm,
    borderRadius: BorderRadius.md,
    marginBottom: Spacing.md,
    gap: Spacing.xs,
  },
  orderNotesText: {
    fontSize: FontSize.sm,
    color: Colors.warning,
    flex: 1,
  },
  actionBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.md,
    borderRadius: BorderRadius.md,
    gap: Spacing.sm,
  },
  actionBtnText: {
    color: Colors.white,
    fontWeight: 'bold',
    fontSize: FontSize.md,
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
