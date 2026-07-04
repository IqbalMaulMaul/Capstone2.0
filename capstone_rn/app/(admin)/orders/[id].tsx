import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, Alert, TouchableOpacity, Platform, Image } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Spacing, BorderRadius, FontSize } from '../../../constants/Colors';
import { adminService } from '../../../services/admin';
import { OrderDetail } from '../../../types';
import { ORDER_STATUS_COLORS, ORDER_STATUS_ICONS, OrderStatusType } from '../../../constants/OrderStatus';

export default function OrderDetailScreen() {
  const { id } = useLocalSearchParams();
  const router = useRouter();
  const [order, setOrder] = useState<OrderDetail | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDetail = async () => {
      try {
        const data = await adminService.getOrder(Number(id));
        setOrder(data);
      } catch (error: any) {
        Alert.alert('Error', error.message || 'Gagal memuat detail pesanan');
      } finally {
        setLoading(false);
      }
    };
    if (id) fetchDetail();
  }, [id]);

  if (loading || !order) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color={Colors.primary} />
      </View>
    );
  }

  const statusStyle = ORDER_STATUS_COLORS[order.status as OrderStatusType] || ORDER_STATUS_COLORS.pending_payment;
  const statusIcon = ORDER_STATUS_ICONS[order.status as OrderStatusType] || 'info';

  const formatRupiah = (number: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
  };

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <MaterialIcons name="arrow-back" size={24} color={Colors.text} />
        </TouchableOpacity>
        <Text style={styles.title}>Detail Pesanan</Text>
        <View style={{ width: 24 }} />
      </View>

      <ScrollView contentContainerStyle={styles.scroll}>
        {/* Header Card */}
        <View style={styles.card}>
          <View style={styles.orderHeaderRow}>
            <View>
              <Text style={styles.orderNumber}>{order.order_number}</Text>
              <Text style={styles.orderTime}>{order.created_at_human} • {new Date(order.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}</Text>
            </View>
            <View style={[styles.badge, { backgroundColor: statusStyle.bg }]}>
              <MaterialIcons name={statusIcon as any} size={14} color={statusStyle.text} />
              <Text style={[styles.badgeText, { color: statusStyle.text }]}>{order.status_label}</Text>
            </View>
          </View>
          
          <View style={styles.divider} />
          
          <View style={styles.infoRow}>
            <MaterialIcons name="meeting-room" size={20} color={Colors.textMuted} />
            <Text style={styles.infoText}>Kamar {order.room_number}</Text>
          </View>
          {order.guest_name && (
            <View style={[styles.infoRow, { marginTop: Spacing.sm }]}>
              <MaterialIcons name="person" size={20} color={Colors.textMuted} />
              <Text style={styles.infoText}>{order.guest_name}</Text>
            </View>
          )}
        </View>

        {/* Items */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Pesanan</Text>
          {order.items.map((item, index) => (
            <View key={item.id} style={[styles.itemRow, index > 0 && styles.itemBorder]}>
              {item.image_url ? (
                <Image source={{ uri: item.image_url }} style={styles.itemImage} />
              ) : (
                <View style={styles.itemImagePlaceholder}>
                  <MaterialIcons name="restaurant" size={20} color={Colors.textMuted} />
                </View>
              )}
              <View style={styles.itemDetails}>
                <Text style={styles.itemName}>{item.menu_name}</Text>
                <Text style={styles.itemPrice}>{formatRupiah(item.menu_price)} x {item.quantity}</Text>
                {item.notes && <Text style={styles.itemNotes}>Catatan: {item.notes}</Text>}
              </View>
              <Text style={styles.itemSubtotal}>{formatRupiah(item.subtotal)}</Text>
            </View>
          ))}
        </View>

        {/* Payment & Summary */}
        <View style={styles.card}>
          <Text style={styles.sectionTitle}>Rincian Pembayaran</Text>
          
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Subtotal</Text>
            <Text style={styles.summaryValue}>{formatRupiah(order.subtotal)}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Pajak (11%)</Text>
            <Text style={styles.summaryValue}>{formatRupiah(order.tax)}</Text>
          </View>
          
          <View style={styles.divider} />
          
          <View style={styles.summaryRow}>
            <Text style={styles.totalLabel}>Total</Text>
            <Text style={styles.totalValue}>{order.formatted_total}</Text>
          </View>

          {order.payment && (
            <View style={styles.paymentInfo}>
              <View style={styles.paymentHeader}>
                <Text style={styles.paymentTitle}>Status Pembayaran</Text>
                <View style={[
                  styles.payBadge,
                  order.payment.status === 'success' ? styles.paySuccess :
                  order.payment.status === 'pending' ? styles.payPending : styles.payFailed
                ]}>
                  <Text style={[
                    styles.payBadgeText,
                    order.payment.status === 'success' ? styles.payTextSuccess :
                    order.payment.status === 'pending' ? styles.payTextPending : styles.payTextFailed
                  ]}>
                    {order.payment.status.toUpperCase()}
                  </Text>
                </View>
              </View>
              
              <View style={styles.infoRow}>
                <MaterialIcons name="payment" size={16} color={Colors.textMuted} />
                <Text style={styles.paymentText}>
                  {order.payment.method ? order.payment.method.toUpperCase() : '-'} 
                  {order.payment.payment_type ? ` (${order.payment.payment_type})` : ''}
                </Text>
              </View>
              
              {order.payment.paid_at_human && (
                <View style={[styles.infoRow, { marginTop: 4 }]}>
                  <MaterialIcons name="check-circle" size={16} color={Colors.textMuted} />
                  <Text style={styles.paymentText}>Dibayar {order.payment.paid_at_human}</Text>
                </View>
              )}
            </View>
          )}
        </View>

      </ScrollView>
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
    padding: Spacing.md,
  },
  card: {
    backgroundColor: Colors.surface,
    borderRadius: BorderRadius.lg,
    padding: Spacing.lg,
    marginBottom: Spacing.md,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  orderHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  orderNumber: {
    fontSize: FontSize.xl,
    fontWeight: 'bold',
    color: Colors.text,
  },
  orderTime: {
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
  divider: {
    height: 1,
    backgroundColor: Colors.border,
    marginVertical: Spacing.md,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.sm,
  },
  infoText: {
    fontSize: FontSize.md,
    color: Colors.text,
    fontWeight: '500',
  },
  sectionTitle: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
    marginBottom: Spacing.md,
  },
  itemRow: {
    flexDirection: 'row',
    paddingVertical: Spacing.md,
  },
  itemBorder: {
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  itemImage: {
    width: 48,
    height: 48,
    borderRadius: BorderRadius.md,
  },
  itemImagePlaceholder: {
    width: 48,
    height: 48,
    borderRadius: BorderRadius.md,
    backgroundColor: Colors.surfaceLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  itemDetails: {
    flex: 1,
    marginLeft: Spacing.md,
  },
  itemName: {
    fontSize: FontSize.md,
    fontWeight: '600',
    color: Colors.text,
  },
  itemPrice: {
    fontSize: FontSize.sm,
    color: Colors.textSecondary,
    marginTop: 2,
  },
  itemNotes: {
    fontSize: FontSize.xs,
    color: Colors.warning,
    marginTop: 2,
    fontStyle: 'italic',
  },
  itemSubtotal: {
    fontSize: FontSize.md,
    fontWeight: 'bold',
    color: Colors.text,
    marginLeft: Spacing.sm,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: Spacing.sm,
  },
  summaryLabel: {
    fontSize: FontSize.md,
    color: Colors.textSecondary,
  },
  summaryValue: {
    fontSize: FontSize.md,
    color: Colors.text,
    fontWeight: '500',
  },
  totalLabel: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.text,
  },
  totalValue: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.primary,
  },
  paymentInfo: {
    marginTop: Spacing.lg,
    paddingTop: Spacing.lg,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  paymentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: Spacing.sm,
  },
  paymentTitle: {
    fontSize: FontSize.md,
    fontWeight: '600',
    color: Colors.text,
  },
  paymentText: {
    fontSize: FontSize.sm,
    color: Colors.textSecondary,
  },
  payBadge: {
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
  },
  payBadgeText: {
    fontSize: 10,
    fontWeight: 'bold',
  },
  paySuccess: { backgroundColor: Colors.successBg },
  payTextSuccess: { color: Colors.success },
  payPending: { backgroundColor: Colors.warningBg },
  payTextPending: { color: Colors.warning },
  payFailed: { backgroundColor: Colors.errorBg },
  payTextFailed: { color: Colors.error },
});
