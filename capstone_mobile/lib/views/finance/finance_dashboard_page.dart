import 'package:flutter/material.dart';
import 'package:capstone_mobile/services/api_service.dart';
import 'package:capstone_mobile/views/auth/login_page.dart';
import 'package:intl/intl.dart';

class FinanceDashboardPage extends StatefulWidget {
  const FinanceDashboardPage({super.key});

  @override
  State<FinanceDashboardPage> createState() => _FinanceDashboardPageState();
}

class _FinanceDashboardPageState extends State<FinanceDashboardPage> {
  bool _isLoading = true;
  Map<String, dynamic> _data = {};
  List<dynamic> _recentPayments = [];
  List<dynamic> _topMenus = [];
  Map<String, dynamic> _chartData = {};
  String _userName = '';

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final name = await ApiService.getUserName();
      final response = await ApiService.getFinanceDashboard();
      setState(() {
        _userName = name ?? 'Finance';
        _data = response;
        _recentPayments = response['recentPayments'] ?? [];
        _topMenus = response['topMenus'] ?? [];
        _chartData = response['chart'] ?? {};
      });
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat dashboard keuangan: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _handleLogout() async {
    await ApiService.logout();
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const LoginPage()),
    );
  }

  Widget _buildRevenueCard({
    required String title,
    required double amount,
    required Color accentColor,
  }) {
    final currencyFormatter = NumberFormat.currency(locale: 'id', symbol: 'Rp', decimalDigits: 0);
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
        border: Border.all(color: Colors.grey.shade100),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: TextStyle(fontSize: 12, color: Colors.grey.shade500, fontWeight: FontWeight.w500),
          ),
          const SizedBox(height: 8),
          Text(
            currencyFormatter.format(amount),
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: accentColor),
          ),
        ],
      ),
    );
  }

  Widget _buildSimpleBarChart() {
    final labels = _chartData['labels'] as List<dynamic>? ?? [];
    final values = _chartData['data'] as List<dynamic>? ?? [];

    if (labels.isEmpty || values.isEmpty) {
      return const Center(child: Text('Data grafik tidak tersedia.'));
    }

    double maxVal = 0;
    for (var val in values) {
      final v = (val as num).toDouble();
      if (v > maxVal) maxVal = v;
    }
    if (maxVal == 0) maxVal = 1;

    final currencyFormatter = NumberFormat.compact(locale: 'id');

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.shade100),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Tren Omzet 7 Hari Terakhir',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.black87),
          ),
          const SizedBox(height: 24),
          SizedBox(
            height: 150,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: List.generate(labels.length, (index) {
                final val = (values[index] as num).toDouble();
                final label = labels[index].toString();
                final pct = val / maxVal;
                final barHeight = (pct * 100).clamp(5.0, 100.0);

                return Expanded(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Text(
                        currencyFormatter.format(val),
                        style: TextStyle(fontSize: 9, color: Colors.grey.shade600, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 4),
                      Container(
                        height: barHeight,
                        margin: const EdgeInsets.symmetric(horizontal: 4),
                        decoration: BoxDecoration(
                          color: Colors.indigoAccent,
                          borderRadius: BorderRadius.circular(4),
                          gradient: LinearGradient(
                            begin: Alignment.bottomCenter,
                            end: Alignment.topCenter,
                            colors: [
                              Colors.indigo.shade800,
                              Colors.indigoAccent,
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        label,
                        style: TextStyle(fontSize: 10, color: Colors.grey.shade500),
                      ),
                    ],
                  ),
                );
              }),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final currencyFormatter = NumberFormat.currency(locale: 'id', symbol: 'Rp', decimalDigits: 0);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Laporan Keuangan',
              style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold, fontSize: 16),
            ),
            Text(
              'Halo, $_userName',
              style: const TextStyle(color: Colors.grey, fontSize: 11),
            ),
          ],
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black87),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
          ),
          IconButton(
            icon: const Icon(Icons.logout_rounded, color: Colors.redAccent),
            onPressed: _handleLogout,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Omzet cards
                    GridView.count(
                      crossAxisCount: 2,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      crossAxisSpacing: 12,
                      mainAxisSpacing: 12,
                      childAspectRatio: 1.5,
                      children: [
                        _buildRevenueCard(
                          title: 'Hari Ini',
                          amount: (_data['todayRevenue'] as num?)?.toDouble() ?? 0.0,
                          accentColor: Colors.green.shade700,
                        ),
                        _buildRevenueCard(
                          title: 'Minggu Ini',
                          amount: (_data['weekRevenue'] as num?)?.toDouble() ?? 0.0,
                          accentColor: Colors.blue.shade700,
                        ),
                        _buildRevenueCard(
                          title: 'Bulan Ini',
                          amount: (_data['monthRevenue'] as num?)?.toDouble() ?? 0.0,
                          accentColor: Colors.purple.shade700,
                        ),
                        _buildRevenueCard(
                          title: 'Rata-rata / Trx',
                          amount: (_data['averageTransaction'] as num?)?.toDouble() ?? 0.0,
                          accentColor: Colors.orange.shade700,
                        ),
                      ],
                    ),

                    const SizedBox(height: 20),

                    // Chart
                    _buildSimpleBarChart(),

                    const SizedBox(height: 24),

                    // Top Menus Section
                    const Text(
                      'Menu Terlaris (Berdasarkan Omzet)',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Colors.black87),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.grey.shade100),
                      ),
                      child: ListView.separated(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _topMenus.length,
                        separatorBuilder: (_, __) => const Divider(height: 1),
                        itemBuilder: (context, index) {
                          final menu = _topMenus[index];
                          return ListTile(
                            leading: CircleAvatar(
                              backgroundColor: Colors.indigo.shade50,
                              child: Text(
                                '${index + 1}',
                                style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.indigoAccent),
                              ),
                            ),
                            title: Text(
                              menu['menu_name'] ?? '',
                              style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                            ),
                            subtitle: Text('${menu['total_qty']} porsi terjual'),
                            trailing: Text(
                              menu['formatted_revenue'] ?? '',
                              style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.black87),
                            ),
                          );
                        },
                      ),
                    ),

                    const SizedBox(height: 24),

                    // Recent payments
                    const Text(
                      'Transaksi Pembayaran Terbaru',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Colors.black87),
                    ),
                    const SizedBox(height: 12),
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: _recentPayments.length,
                      itemBuilder: (context, index) {
                        final payment = _recentPayments[index];

                        return Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.grey.shade100),
                          ),
                          child: Column(
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    payment['order_number'] ?? '',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                  ),
                                  Text(
                                    payment['formatted_amount'] ?? '',
                                    style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.green),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    'Kamar ${payment['room_number']}',
                                    style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                                  ),
                                  Text(
                                    payment['payment_type']?.toString().toUpperCase() ?? '-',
                                    style: TextStyle(color: Colors.grey.shade500, fontSize: 11, fontWeight: FontWeight.w600),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.start,
                                children: [
                                  Icon(Icons.access_time, size: 12, color: Colors.grey.shade400),
                                  const SizedBox(width: 4),
                                  Text(
                                    payment['paid_at_human'] ?? '',
                                    style: TextStyle(color: Colors.grey.shade400, fontSize: 11),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
