import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, RefreshControl, TouchableOpacity } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Shadows, Spacing, BorderRadius, FontSize } from '../../constants/Colors';
import { adminService } from '../../services/admin';
import { DashboardData } from '../../types';
import { LinearGradient } from 'expo-linear-gradient';
import { ORDER_STATUS_COLORS, ORDER_STATUS_LABELS, ORDER_STATUS_ICONS, OrderStatusType } from '../../constants/OrderStatus';
import { useRouter } from 'expo-router';

export default function AdminDashboard() {
  const [data, setData] = useState<DashboardData | null>(null);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();

  const loadData = async () => {
    try {
      setError(null);
      const res = await adminService.getDashboard();
      setData(res);
    } catch (err: any) {
      setError(err.message || 'Gagal memuat dashboard');
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

  if (error) {
    return (
      <View style={styles.center}>
        <Text style={styles.errorText}>{error}</Text>
        <TouchableOpacity style={styles.retryBtn} onPress={onRefresh}>
          <Text style={styles.retryText}>Coba Lagi</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const formatRupiah = (number: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
  };

  return (
    <ScrollView 
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={Colors.primary} />}
    >
      {/* Stats Grid */}
      <View style={styles.statsGrid}>
        <View style={styles.statsCard}>
          <View style={[styles.iconWrapper, { backgroundColor: Colors.infoBg }]}>
            <MaterialIcons name="receipt" size={24} color={Colors.info} />
          </View>
          <Text style={styles.statsValue}>{data?.todayOrders || 0}</Text>
          <Text style={styles.statsLabel}>Order Hari Ini</Text>
        </View>
        <View style={styles.statsCard}>
          <View style={[styles.iconWrapper, { backgroundColor: Colors.warningBg }]}>
            <MaterialIcons name="hourglass-empty" size={24} color={Colors.warning} />
          </View>
          <Text style={styles.statsValue}>{data?.pendingOrders || 0}</Text>
          <Text style={styles.statsLabel}>Pending Payment</Text>
        </View>
        <View style={styles.statsCard}>
          <View style={[styles.iconWrapper, { backgroundColor: 'rgba(99, 102, 241, 0.15)' }]}>
            <MaterialIcons name="restaurant" size={24} color={Colors.primary} />
          </View>
          <Text style={styles.statsValue}>{data?.kitchenActive || 0}</Text>
          <Text style={styles.statsLabel}>Proses Kitchen</Text>
        </View>
        <View style={styles.statsCard}>
          <View style={[styles.iconWrapper, { backgroundColor: Colors.successBg }]}>
            <MaterialIcons name="monetization-on" size={24} color={Colors.success} />
          </View>
          <Text style={styles.statsValue}>{formatRupiah(data?.todayRevenue || 0)}</Text>
          <Text style={styles.statsLabel}>Pendapatan Hari Ini</Text>
        </View>
      </View>

      {/* Recent Orders */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Order Terbaru</Text>
          <TouchableOpacity onPress={() => router.push('/(admin)/orders')}>
            <Text style={styles.seeAll}>Lihat Semua</Text>
          </TouchableOpacity>
        </View>

        {data?.recentOrders.map((order) => {
          const statusStyle = ORDER_STATUS_COLORS[order.status as OrderStatusType] || ORDER_STATUS_COLORS.pending_payment;
          const statusIcon = ORDER_STATUS_ICONS[order.status as OrderStatusType] || 'info';

          return (
            <TouchableOpacity 
              key={order.id} 
              style={styles.orderCard}
              onPress={() => router.push(`/(admin)/orders/${order.id}`)}
            >
              <View style={styles.orderHeader}>
                <View>
                  <Text style={styles.orderNumber}>{order.order_number}</Text>
                  <Text style={styles.roomNumber}>Kamar {order.room_number}</Text>
                </View>
                <View style={[styles.badge, { backgroundColor: statusStyle.bg }]}>
                  <MaterialIcons name={statusIcon as any} size={14} color={statusStyle.text} />
                  <Text style={[styles.badgeText, { color: statusStyle.text }]}>{order.status_label}</Text>
                </View>
              </View>
              <View style={styles.orderFooter}>
                <Text style={styles.orderTime}>{order.created_at_human}</Text>
                <Text style={styles.orderTotal}>{order.formatted_total}</Text>
              </View>
            </TouchableOpacity>
          );
        })}

        {data?.recentOrders.length === 0 && (
          <View style={styles.emptyState}>
            <MaterialIcons name="receipt-long" size={48} color={Colors.textMuted} />
            <Text style={styles.emptyText}>Belum ada order hari ini</Text>
          </View>
        )}
      </View>
    </ScrollView>
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
  errorText: {
    color: Colors.error,
    marginBottom: Spacing.md,
  },
  retryBtn: {
    padding: Spacing.sm,
    backgroundColor: Colors.surface,
    borderRadius: BorderRadius.sm,
  },
  retryText: {
    color: Colors.primary,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: Spacing.sm,
  },
  statsCard: {
    width: '50%',
    padding: Spacing.sm,
  },
  iconWrapper: {
    width: 48,
    height: 48,
    borderRadius: BorderRadius.lg,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: Spacing.sm,
  },
  statsValue: {
    fontSize: FontSize.xl,
    fontWeight: 'bold',
    color: Colors.text,
  },
  statsLabel: {
    fontSize: FontSize.sm,
    color: Colors.textSecondary,
  },
  section: {
    padding: Spacing.lg,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.md,
  },
  sectionTitle: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
  },
  seeAll: {
    color: Colors.primary,
    fontWeight: '600',
  },
  orderCard: {
    backgroundColor: Colors.surface,
    borderRadius: BorderRadius.lg,
    padding: Spacing.md,
    marginBottom: Spacing.md,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  orderHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: Spacing.sm,
  },
  orderNumber: {
    fontSize: FontSize.md,
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
  orderFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: Spacing.sm,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  orderTime: {
    fontSize: 12,
    color: Colors.textMuted,
  },
  orderTotal: {
    fontSize: FontSize.md,
    fontWeight: 'bold',
    color: Colors.text,
  },
  emptyState: {
    alignItems: 'center',
    padding: Spacing.xxxl,
  },
  emptyText: {
    marginTop: Spacing.sm,
    color: Colors.textMuted,
  },
});
