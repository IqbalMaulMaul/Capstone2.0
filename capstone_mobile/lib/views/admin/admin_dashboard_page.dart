import 'package:flutter/material.dart';
import 'package:capstone_mobile/services/api_service.dart';
import 'package:capstone_mobile/views/auth/login_page.dart';
import 'package:capstone_mobile/views/admin/admin_menus_page.dart';
import 'package:capstone_mobile/views/admin/admin_rooms_page.dart';
import 'package:intl/intl.dart';

class AdminDashboardPage extends StatefulWidget {
  const AdminDashboardPage({super.key});

  @override
  State<AdminDashboardPage> createState() => _AdminDashboardPageState();
}

class _AdminDashboardPageState extends State<AdminDashboardPage> {
  bool _isLoading = true;
  Map<String, dynamic> _stats = {};
  List<dynamic> _recentOrders = [];
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
      final response = await ApiService.getAdminDashboard();
      setState(() {
        _userName = name ?? 'Owner';
        _stats = response['data'];
        _recentOrders = response['data']['recentOrders'] ?? [];
      });
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat data: $e')),
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

  Widget _buildStatCard({
    required String title,
    required String value,
    required IconData icon,
    required Color iconColor,
    required Color bgColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
        border: Border.all(color: Colors.grey.shade100),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: iconColor, size: 24),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    color: Colors.grey.shade500,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.black87,
                  ),
                ),
              ],
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
      backgroundColor: const Color(0xFFF8FAFC), // Slate 50
      appBar: AppBar(
        title: const Text(
          'Owner Dashboard',
          style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold, fontSize: 18),
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black87),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
          ),
        ],
      ),
      drawer: Drawer(
        child: Column(
          children: [
            UserAccountsDrawerHeader(
              decoration: const BoxDecoration(
                color: Color(0xFF1E1B4B), // Indigo 950
              ),
              currentAccountPicture: const CircleAvatar(
                backgroundColor: Colors.white24,
                child: Icon(Icons.person, color: Colors.white, size: 40),
              ),
              accountName: Text(_userName, style: const TextStyle(fontWeight: FontWeight.bold)),
              accountEmail: const Text('Role: Owner / Admin'),
            ),
            ListTile(
              leading: const Icon(Icons.dashboard_outlined),
              title: const Text('Dashboard'),
              selected: true,
              onTap: () => Navigator.pop(context),
            ),
            ListTile(
              leading: const Icon(Icons.restaurant_menu_rounded),
              title: const Text('Kelola Menu'),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(context, MaterialPageRoute(builder: (_) => const AdminMenusPage()));
              },
            ),
            ListTile(
              leading: const Icon(Icons.meeting_room_outlined),
              title: const Text('Kelola Kamar'),
              onTap: () {
                Navigator.pop(context);
                Navigator.push(context, MaterialPageRoute(builder: (_) => const AdminRoomsPage()));
              },
            ),
            const Divider(),
            const Spacer(),
            ListTile(
              leading: const Icon(Icons.logout_rounded, color: Colors.red),
              title: const Text('Logout', style: TextStyle(color: Colors.red)),
              onTap: _handleLogout,
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header welcome
                    Text(
                      'Halo, $_userName',
                      style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.black87),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Berikut adalah ringkasan aktivitas hari ini.',
                      style: TextStyle(fontSize: 13, color: Colors.grey),
                    ),
                    const SizedBox(height: 20),

                    // Stats Grid layout
                    GridView.count(
                      crossAxisCount: 1,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      childAspectRatio: 4,
                      mainAxisSpacing: 12,
                      children: [
                        _buildStatCard(
                          title: 'Pesanan Hari Ini',
                          value: '${_stats['todayOrders'] ?? 0}',
                          icon: Icons.shopping_bag_outlined,
                          iconColor: Colors.blue.shade600,
                          bgColor: Colors.blue.shade50,
                        ),
                        _buildStatCard(
                          title: 'Menunggu Diproses',
                          value: '${_stats['pendingOrders'] ?? 0}',
                          icon: Icons.hourglass_empty_rounded,
                          iconColor: Colors.amber.shade700,
                          bgColor: Colors.amber.shade50,
                        ),
                        _buildStatCard(
                          title: 'Aktivitas Dapur',
                          value: '${_stats['kitchenActive'] ?? 0}',
                          icon: Icons.local_fire_department_outlined,
                          iconColor: Colors.orange.shade700,
                          bgColor: Colors.orange.shade50,
                        ),
                        _buildStatCard(
                          title: 'Omzet Hari Ini',
                          value: currencyFormatter.format(_stats['todayRevenue'] ?? 0),
                          icon: Icons.monetization_on_outlined,
                          iconColor: Colors.green.shade600,
                          bgColor: Colors.green.shade50,
                        ),
                      ],
                    ),

                    const SizedBox(height: 28),

                    // Recent Orders Title
                    const Text(
                      'Pesanan Terbaru',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.black87),
                    ),
                    const SizedBox(height: 12),

                    // Recent Orders List (representing the Table layout in a mobile-friendly style)
                    if (_recentOrders.isEmpty)
                      const Center(
                        child: Padding(
                          padding: EdgeInsets.symmetric(vertical: 32),
                          child: Text('Belum ada pesanan hari ini.', style: TextStyle(color: Colors.grey)),
                        ),
                      )
                    else
                      ListView.separated(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: _recentOrders.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 10),
                        itemBuilder: (context, index) {
                          final order = _recentOrders[index];
                          
                          // Determine status badge color
                          Color badgeColor = Colors.grey.shade100;
                          Color textColor = Colors.grey.shade800;
                          final status = order['status'] ?? '';
                          
                          if (status == 'pending_payment') {
                            badgeColor = Colors.grey.shade100;
                            textColor = Colors.grey.shade800;
                          } else if (status == 'paid') {
                            badgeColor = Colors.blue.shade50;
                            textColor = Colors.blue.shade800;
                          } else if (status == 'accepted' || status == 'processing') {
                            badgeColor = Colors.orange.shade50;
                            textColor = Colors.orange.shade800;
                          } else if (status == 'ready' || status == 'delivered' || status == 'completed') {
                            badgeColor = Colors.green.shade50;
                            textColor = Colors.green.shade800;
                          } else if (status == 'cancelled') {
                            badgeColor = Colors.red.shade50;
                            textColor = Colors.red.shade800;
                          }

                          return Container(
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
                                      order['order_number'] ?? '',
                                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: badgeColor,
                                        borderRadius: BorderRadius.circular(20),
                                      ),
                                      child: Text(
                                        order['status_label'] ?? '',
                                        style: TextStyle(color: textColor, fontSize: 10, fontWeight: FontWeight.w600),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 12),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      'Kamar ${order['room_number']}',
                                      style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                                    ),
                                    Text(
                                      order['formatted_total'] ?? '',
                                      style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.indigoAccent),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.start,
                                  children: [
                                    Icon(Icons.access_time, size: 12, color: Colors.grey.shade400),
                                    const SizedBox(width: 4),
                                    Text(
                                      order['created_at_human'] ?? '',
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
