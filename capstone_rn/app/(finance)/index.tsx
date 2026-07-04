import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, RefreshControl, TouchableOpacity, ActivityIndicator, Dimensions } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Shadows, Spacing, BorderRadius, FontSize } from '../../constants/Colors';
import { financeService } from '../../services/finance';
import { FinanceDashboard } from '../../types';
import { StatusBar } from 'expo-status-bar';
import { LineChart } from 'react-native-chart-kit';

export default function FinanceDashboardScreen() {
  const [data, setData] = useState<FinanceDashboard | null>(null);
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const loadData = async () => {
    try {
      setError(null);
      const res = await financeService.getDashboard();
      setData(res);
    } catch (err: any) {
      setError(err.message || 'Gagal memuat data finance');
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

  if (loading && !data) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color={Colors.primary} />
      </View>
    );
  }

  if (error && !data) {
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
      <StatusBar style="light" />

      {/* Revenue Cards */}
      <View style={styles.revenueCards}>
        <View style={styles.mainRevenueCard}>
          <Text style={styles.revenueLabel}>Pendapatan Hari Ini</Text>
          <Text style={styles.mainRevenueValue}>{formatRupiah(data?.todayRevenue || 0)}</Text>
        </View>

        <View style={styles.subRevenueRow}>
          <View style={styles.subRevenueCard}>
            <Text style={styles.subRevenueLabel}>Minggu Ini</Text>
            <Text style={styles.subRevenueValue}>{formatRupiah(data?.weekRevenue || 0)}</Text>
          </View>
          <View style={styles.subRevenueCard}>
            <Text style={styles.subRevenueLabel}>Bulan Ini</Text>
            <Text style={styles.subRevenueValue}>{formatRupiah(data?.monthRevenue || 0)}</Text>
          </View>
        </View>
      </View>

      {/* Chart Section */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Trend Pendapatan (7 Hari Terakhir)</Text>
        <View style={styles.chartCard}>
          {data?.chart && data.chart.labels.length > 0 ? (
            <LineChart
              data={{
                labels: data.chart.labels,
                datasets: [{ data: data.chart.data }]
              }}
              width={Dimensions.get('window').width - Spacing.lg * 2 - Spacing.md * 2} // from react-native
              height={220}
              yAxisLabel="Rp "
              yAxisSuffix=""
              chartConfig={{
                backgroundColor: Colors.surface,
                backgroundGradientFrom: Colors.surface,
                backgroundGradientTo: Colors.surface,
                decimalPlaces: 0,
                color: (opacity = 1) => `rgba(99, 102, 241, ${opacity})`,
                labelColor: (opacity = 1) => `rgba(148, 163, 184, ${opacity})`, // Colors.textSecondary
                style: {
                  borderRadius: 16
                },
                propsForDots: {
                  r: "4",
                  strokeWidth: "2",
                  stroke: Colors.primary
                }
              }}
              bezier
              style={{
                marginVertical: 8,
                borderRadius: 16
              }}
            />
          ) : (
            <Text style={styles.noDataText}>Belum ada data pendapatan</Text>
          )}
        </View>
      </View>

      {/* Stats Grid */}
      <View style={styles.statsGrid}>
        <View style={styles.statsItem}>
          <Text style={styles.statsValue}>{data?.totalTransactions || 0}</Text>
          <Text style={styles.statsLabel}>Total Trx</Text>
        </View>
        <View style={styles.statsItem}>
          <Text style={styles.statsValue}>{data?.pendingTransactions || 0}</Text>
          <Text style={styles.statsLabel}>Pending Trx</Text>
        </View>
        <View style={styles.statsItem}>
          <Text style={styles.statsValue}>{formatRupiah(data?.averageTransaction || 0)}</Text>
          <Text style={styles.statsLabel}>Rata-rata Trx</Text>
        </View>
      </View>

      {/* Top Menus */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Menu Terlaris</Text>
        <View style={styles.card}>
          {data?.topMenus.map((menu, idx) => (
            <View key={menu.menu_id} style={[styles.topMenuItem, idx > 0 && styles.borderTop]}>
              <Text style={styles.rankText}>#{idx + 1}</Text>
              <View style={styles.topMenuInfo}>
                <Text style={styles.topMenuName}>{menu.menu_name}</Text>
                <Text style={styles.topMenuQty}>{menu.total_qty} terjual</Text>
              </View>
              <Text style={styles.topMenuRevenue}>{menu.formatted_revenue}</Text>
            </View>
          ))}
          {(!data?.topMenus || data.topMenus.length === 0) && (
            <Text style={styles.noDataText}>Belum ada penjualan</Text>
          )}
        </View>
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
  revenueCards: {
    padding: Spacing.lg,
    paddingTop: Spacing.xl,
  },
  mainRevenueCard: {
    backgroundColor: Colors.primary,
    borderRadius: BorderRadius.xl,
    padding: Spacing.xxl,
    alignItems: 'center',
    marginBottom: Spacing.md,
    ...Shadows.glow(Colors.primary),
  },
  revenueLabel: {
    color: 'rgba(255,255,255,0.8)',
    fontSize: FontSize.md,
    marginBottom: Spacing.xs,
  },
  mainRevenueValue: {
    color: Colors.white,
    fontSize: FontSize.display,
    fontWeight: 'bold',
  },
  subRevenueRow: {
    flexDirection: 'row',
    gap: Spacing.md,
  },
  subRevenueCard: {
    flex: 1,
    backgroundColor: Colors.surface,
    padding: Spacing.lg,
    borderRadius: BorderRadius.lg,
    borderWidth: 1,
    borderColor: Colors.border,
    alignItems: 'center',
  },
  subRevenueLabel: {
    color: Colors.textSecondary,
    fontSize: FontSize.sm,
    marginBottom: 4,
  },
  subRevenueValue: {
    color: Colors.text,
    fontSize: FontSize.lg,
    fontWeight: 'bold',
  },
  section: {
    padding: Spacing.lg,
    paddingTop: 0,
  },
  sectionTitle: {
    fontSize: FontSize.md,
    fontWeight: 'bold',
    color: Colors.text,
    marginBottom: Spacing.md,
  },
  chartCard: {
    backgroundColor: Colors.surface,
    borderRadius: BorderRadius.lg,
    padding: Spacing.md,
    borderWidth: 1,
    borderColor: Colors.border,
    alignItems: 'center',
    justifyContent: 'center',
    minHeight: 200,
  },
  noDataText: {
    color: Colors.textMuted,
    fontStyle: 'italic',
    padding: Spacing.lg,
  },
  statsGrid: {
    flexDirection: 'row',
    paddingHorizontal: Spacing.lg,
    marginBottom: Spacing.lg,
    gap: Spacing.md,
  },
  statsItem: {
    flex: 1,
    backgroundColor: Colors.surfaceLight,
    padding: Spacing.md,
    borderRadius: BorderRadius.md,
    alignItems: 'center',
  },
  statsValue: {
    color: Colors.text,
    fontSize: FontSize.md,
    fontWeight: 'bold',
  },
  statsLabel: {
    color: Colors.textSecondary,
    fontSize: 10,
    marginTop: 2,
    textAlign: 'center',
  },
  card: {
    backgroundColor: Colors.surface,
    borderRadius: BorderRadius.lg,
    borderWidth: 1,
    borderColor: Colors.border,
  },
  topMenuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: Spacing.md,
  },
  borderTop: {
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  rankText: {
    fontSize: FontSize.lg,
    fontWeight: 'bold',
    color: Colors.primary,
    width: 32,
  },
  topMenuInfo: {
    flex: 1,
  },
  topMenuName: {
    color: Colors.text,
    fontSize: FontSize.md,
    fontWeight: '500',
  },
  topMenuQty: {
    color: Colors.textSecondary,
    fontSize: FontSize.xs,
    marginTop: 2,
  },
  topMenuRevenue: {
    color: Colors.success,
    fontSize: FontSize.md,
    fontWeight: 'bold',
  },
});
