import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity, ActivityIndicator } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Spacing, BorderRadius, FontSize, Shadows } from '../../constants/Colors';
import { adminService } from '../../services/admin';
import { OrderListItem } from '../../types';
import { useRouter } from 'expo-router';
import { ORDER_STATUS_COLORS, ORDER_STATUS_ICONS, OrderStatusType } from '../../constants/OrderStatus';

export default function OrdersScreen() {
  const [orders, setOrders] = useState<OrderListItem[]>([]);
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [statusFilter, setStatusFilter] = useState('all');
  
  const router = useRouter();

  const loadOrders = async (pageNumber = 1, shouldRefresh = false) => {
    try {
      if (pageNumber === 1 && !shouldRefresh) setLoading(true);
      
      const data = await adminService.getOrders(pageNumber, statusFilter);
      
      if (pageNumber === 1 || shouldRefresh) {
        setOrders(data.data);
      } else {
        setOrders(prev => [...prev, ...data.data]);
      }
      
      setHasMore(data.current_page < data.last_page);
      setPage(data.current_page);
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    loadOrders(1, true);
  };

  const loadMore = () => {
    if (!loading && hasMore && !refreshing) {
      loadOrders(page + 1);
    }
  };

  useEffect(() => {
    loadOrders(1);
  }, [statusFilter]);

  const FilterTab = ({ label, value }: { label: string, value: string }) => (
    <TouchableOpacity 
      style={[styles.filterTab, statusFilter === value && styles.filterTabActive]}
      onPress={() => setStatusFilter(value)}
    >
      <Text style={[styles.filterText, statusFilter === value && styles.filterTextActive]}>
        {label}
      </Text>
    </TouchableOpacity>
  );

  const renderItem = ({ item }: { item: OrderListItem }) => {
    const statusStyle = ORDER_STATUS_COLORS[item.status as OrderStatusType] || ORDER_STATUS_COLORS.pending_payment;
    const statusIcon = ORDER_STATUS_ICONS[item.status as OrderStatusType] || 'info';

    return (
      <TouchableOpacity 
        style={styles.card}
        onPress={() => router.push(`/(admin)/orders/${item.id}`)}
      >
        <View style={styles.cardHeader}>
          <View>
            <Text style={styles.orderNumber}>{item.order_number}</Text>
            <Text style={styles.roomNumber}>Kamar {item.room_number} • {item.guest_name || 'Guest'}</Text>
          </View>
          <View style={[styles.badge, { backgroundColor: statusStyle.bg }]}>
            <MaterialIcons name={statusIcon as any} size={14} color={statusStyle.text} />
            <Text style={[styles.badgeText, { color: statusStyle.text }]}>{item.status_label}</Text>
          </View>
        </View>
        
        <View style={styles.cardFooter}>
          <View style={styles.footerInfo}>
            <MaterialIcons name="schedule" size={16} color={Colors.textMuted} />
            <Text style={styles.timeText}>{item.created_at_human}</Text>
          </View>
          <Text style={styles.totalText}>{item.formatted_total}</Text>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <View style={styles.container}>
      <View style={styles.filters}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.filterScroll}>
          <FilterTab label="Semua" value="all" />
          <FilterTab label="Aktif" value="paid" />
          <FilterTab label="Proses" value="processing" />
          <FilterTab label="Selesai" value="completed" />
        </ScrollView>
      </View>

      {loading && page === 1 ? (
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
          onEndReached={loadMore}
          onEndReachedThreshold={0.3}
          ListFooterComponent={
            hasMore && !refreshing ? (
              <ActivityIndicator style={{ margin: Spacing.md }} color={Colors.primary} />
            ) : null
          }
          ListEmptyComponent={
            <View style={styles.empty}>
              <MaterialIcons name="receipt-long" size={64} color={Colors.textMuted} />
              <Text style={styles.emptyText}>Tidak ada pesanan.</Text>
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
  filters: {
    backgroundColor: Colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  filterScroll: {
    padding: Spacing.md,
    gap: Spacing.sm,
  },
  filterTab: {
    paddingHorizontal: Spacing.lg,
    paddingVertical: 8,
    borderRadius: BorderRadius.full,
    backgroundColor: Colors.surfaceLight,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  filterTabActive: {
    backgroundColor: Colors.primary,
    borderColor: Colors.primary,
  },
  filterText: {
    color: Colors.textSecondary,
    fontWeight: '600',
  },
  filterTextActive: {
    color: Colors.white,
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
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: Spacing.md,
  },
  orderNumber: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
  },
  roomNumber: {
    fontSize: FontSize.sm,
    color: Colors.textSecondary,
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
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: Spacing.md,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  footerInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  timeText: {
    fontSize: FontSize.sm,
    color: Colors.textMuted,
  },
  totalText: {
    fontSize: FontSize.md,
    fontWeight: 'bold',
    color: Colors.text,
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
