import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, RefreshControl, TouchableOpacity, ActivityIndicator } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Spacing, BorderRadius, FontSize } from '../../constants/Colors';
import { adminService } from '../../services/admin';
import { PaymentListItem } from '../../types';
import { useRouter } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

export default function PaymentsScreen() {
  const [payments, setPayments] = useState<PaymentListItem[]>([]);
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const router = useRouter();
  const insets = useSafeAreaInsets();

  const loadData = async (pageNumber = 1, shouldRefresh = false) => {
    try {
      if (pageNumber === 1 && !shouldRefresh) setLoading(true);
      
      const data = await adminService.getPayments(pageNumber);
      
      if (pageNumber === 1 || shouldRefresh) {
        setPayments(data.data);
      } else {
        setPayments(prev => [...prev, ...data.data]);
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
    loadData(1, true);
  };

  const loadMore = () => {
    if (!loading && hasMore && !refreshing) {
      loadData(page + 1);
    }
  };

  useEffect(() => {
    loadData(1);
  }, []);

  const renderItem = ({ item }: { item: PaymentListItem }) => {
    const isSuccess = item.status === 'success';
    const isPending = item.status === 'pending';

    return (
      <TouchableOpacity 
        style={styles.card}
        onPress={() => router.push(`/(admin)/orders/${item.order_id}`)}
      >
        <View style={styles.cardHeader}>
          <View style={styles.headerLeft}>
            <View style={[styles.iconCircle, { backgroundColor: isSuccess ? Colors.successBg : isPending ? Colors.warningBg : Colors.errorBg }]}>
              <MaterialIcons 
                name={isSuccess ? "check-circle" : isPending ? "hourglass-empty" : "cancel"} 
                size={20} 
                color={isSuccess ? Colors.success : isPending ? Colors.warning : Colors.error} 
              />
            </View>
            <View style={styles.info}>
              <Text style={styles.orderNumber}>{item.order_number}</Text>
              <Text style={styles.roomNumber}>Kamar {item.room_number}</Text>
            </View>
          </View>
          <Text style={styles.amount}>{item.formatted_amount}</Text>
        </View>

        <View style={styles.cardFooter}>
          <Text style={styles.method}>
            {item.method ? item.method.toUpperCase() : '-'} {item.payment_type ? `(${item.payment_type})` : ''}
          </Text>
          <Text style={styles.time}>{item.paid_at_human || new Date(item.created_at).toLocaleDateString('id-ID')}</Text>
        </View>
      </TouchableOpacity>
    );
  };

  return (
    <View style={styles.container}>
      <StatusBar style="dark" />
      <View style={[styles.header, { paddingTop: insets.top + Spacing.md }]}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <MaterialIcons name="arrow-back" size={24} color={Colors.text} />
        </TouchableOpacity>
        <Text style={styles.title}>Riwayat Pembayaran</Text>
        <View style={{ width: 24 }} />
      </View>

      {loading && page === 1 ? (
        <View style={styles.center}>
          <ActivityIndicator size="large" color={Colors.primary} />
        </View>
      ) : (
        <FlatList
          data={payments}
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
              <MaterialIcons name="payment" size={64} color={Colors.textMuted} />
              <Text style={styles.emptyText}>Belum ada riwayat pembayaran.</Text>
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
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: Spacing.lg,
    backgroundColor: Colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  backBtn: {
    padding: Spacing.sm,
  },
  title: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
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
  headerLeft: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  iconCircle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  info: {
    marginLeft: Spacing.md,
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
  amount: {
    fontSize: FontSize.md,
    fontWeight: 'bold',
    color: Colors.text,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: Spacing.sm,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  method: {
    fontSize: FontSize.sm,
    color: Colors.textSecondary,
    fontWeight: '500',
  },
  time: {
    fontSize: FontSize.sm,
    color: Colors.textMuted,
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
