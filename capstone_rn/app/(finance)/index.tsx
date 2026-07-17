import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, RefreshControl, TouchableOpacity, ActivityIndicator } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import { Colors, Shadows, Spacing, BorderRadius, FontSize } from '../../constants/Colors';
import { financeService } from '../../services/finance';
import { FinanceDashboard } from '../../types';
import { StatusBar } from 'expo-status-bar';

// ── Custom Bar Chart (pengganti react-native-chart-kit yang crash di RN 0.86) ──
const SimpleBarChart = ({ labels, values }: { labels: string[]; values: number[] }) => {
  const max = Math.max(...values, 1);
  const formatLabel = (v: number) => {
    if (v >= 1_000_000) return `${(v / 1_000_000).toFixed(1)}jt`;
    if (v >= 1_000) return `${(v / 1_000).toFixed(0)}rb`;
    return `${v}`;
  };

  return (
    <View style={{ width: '100%' }}>
      {/* Y-axis labels + bars */}
      <View style={{ flexDirection: 'row', height: 140, alignItems: 'flex-end' }}>
        {/* Y label max */}
        <Text style={{ fontSize: 8, color: Colors.textMuted, marginBottom: 0, width: 32, textAlign: 'right', paddingRight: 4 }}>
          {formatLabel(max)}
        </Text>
        {/* Bars */}
        <View style={{ flex: 1, flexDirection: 'row', alignItems: 'flex-end', gap: 4 }}>
          {values.map((val, i) => {
            const heightPct = max > 0 ? (val / max) : 0;
            return (
              <View key={i} style={{ flex: 1, alignItems: 'center' }}>
                <View
                  style={{
                    width: '80%',
                    height: Math.max(heightPct * 120, 4),
                    backgroundColor: val === max ? Colors.primary : Colors.primaryLight,
                    borderRadius: 3,
                    opacity: 0.7 + heightPct * 0.3,
                  }}
                />
              </View>
            );
          })}
        </View>
      </View>
      {/* X-axis labels */}
      <View style={{ flexDirection: 'row', paddingLeft: 36, marginTop: 6 }}>
        {labels.map((lbl, i) => (
          <View key={i} style={{ flex: 1, alignItems: 'center' }}>
            <Text style={{ fontSize: 8, color: Colors.textMuted }}>{lbl}</Text>
          </View>
        ))}
      </View>
      {/* Nilai tertinggi */}
      <View style={{ flexDirection: 'row', paddingLeft: 36, marginTop: 4 }}>
        {values.map((val, i) => (
          <View key={i} style={{ flex: 1, alignItems: 'center' }}>
            {val > 0 && (
              <Text style={{ fontSize: 7, color: Colors.primary, fontWeight: '600' }}>
                {formatLabel(val)}
              </Text>
            )}
          </View>
        ))}
      </View>
    </View>
  );
};

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
      <StatusBar style="dark" />

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
            <SimpleBarChart labels={data.chart.labels} values={data.chart.data} />
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
