import 'dart:async';
import 'package:flutter/material.dart';
import 'package:capstone_mobile/services/api_service.dart';
import 'package:capstone_mobile/views/auth/login_page.dart';

class KitchenDashboardPage extends StatefulWidget {
  const KitchenDashboardPage({super.key});

  @override
  State<KitchenDashboardPage> createState() => _KitchenDashboardPageState();
}

class _KitchenDashboardPageState extends State<KitchenDashboardPage> {
  bool _isLoading = true;
  List<dynamic> _orders = [];
  List<dynamic> _categories = [];
  String _userName = '';
  Timer? _pollingTimer;
  int _currentTabIndex = 0; // 0 = Pesanan Aktif, 1 = Kelola Menu

  @override
  void initState() {
    super.initState();
    _loadData();
    // Auto-poll orders every 10 seconds to keep kitchen updated in real-time
    _pollingTimer = Timer.periodic(const Duration(seconds: 10), (_) => _pollOrdersOnly());
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadData() async {
    if (mounted) setState(() => _isLoading = true);
    try {
      final name = await ApiService.getUserName();
      final data = await ApiService.getKitchenOrders();
      if (mounted) {
        setState(() {
          _userName = name ?? 'Dapur';
          _orders = data['orders'] ?? [];
          _categories = data['categories'] ?? [];
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat pesanan dapur: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _pollOrdersOnly() async {
    try {
      final data = await ApiService.getKitchenOrders();
      if (mounted) {
        setState(() {
          _orders = data['orders'] ?? [];
          _categories = data['categories'] ?? [];
        });
      }
    } catch (_) {}
  }

  Future<void> _updateStatus(int orderId, String newStatus) async {
    try {
      await ApiService.updateOrderStatus(orderId, newStatus);
      await _pollOrdersOnly();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Status pesanan berhasil diperbarui!')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memperbarui status: $e')),
        );
      }
    }
  }

  Future<void> _toggleMenuAvailability(int menuId, int catIdx, int menuIdx) async {
    try {
      await ApiService.toggleMenuAvailability(menuId);
      setState(() {
        final currentVal = _categories[catIdx]['menus'][menuIdx]['is_available'];
        _categories[catIdx]['menus'][menuIdx]['is_available'] = (currentVal == true || currentVal == 1) ? 0 : 1;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Ketersediaan menu diperbarui!')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memperbarui menu: $e')),
        );
      }
    }
  }

  Future<void> _handleLogout() async {
    _pollingTimer?.cancel();
    await ApiService.logout();
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const LoginPage()),
    );
  }

  Widget _buildOrdersView() {
    if (_orders.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.check_circle_outline_rounded, size: 64, color: Colors.green),
              SizedBox(height: 16),
              Text(
                'Semua pesanan selesai!',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
              ),
              SizedBox(height: 8),
              Text(
                'Belum ada pesanan aktif baru yang masuk ke dapur.',
                style: TextStyle(fontSize: 13, color: Colors.grey),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _orders.length,
      itemBuilder: (context, index) {
        final order = _orders[index];
        final items = order['items'] as List<dynamic>? ?? [];
        final status = order['status'] ?? '';

        // Determine action text and next status
        String btnText = '';
        String nextStatus = '';
        Color btnColor = Colors.indigoAccent;

        if (status == 'paid') {
          btnText = 'Terima Pesanan';
          nextStatus = 'accepted';
          btnColor = Colors.blue;
        } else if (status == 'accepted') {
          btnText = 'Mulai Masak';
          nextStatus = 'processing';
          btnColor = Colors.orange;
        } else if (status == 'processing') {
          btnText = 'Siap Saji';
          nextStatus = 'ready';
          btnColor = Colors.green;
        } else if (status == 'ready') {
          btnText = 'Selesaikan Pengantaran';
          nextStatus = 'completed';
          btnColor = Colors.teal;
        }

        return Container(
          margin: const EdgeInsets.bottom(16),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey.shade100),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.03),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    order['order_number'] ?? '',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.amber.shade50,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      order['status_label'] ?? '',
                      style: TextStyle(color: Colors.amber.shade900, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Kamar ${order['room_number']}',
                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Colors.indigoAccent),
                  ),
                  Text(
                    order['created_at_human'] ?? '',
                    style: TextStyle(color: Colors.grey.shade400, fontSize: 12),
                  ),
                ],
              ),
              if (order['guest_name'] != null && order['guest_name'].toString().isNotEmpty) ...[
                const SizedBox(height: 4),
                Text(
                  'Tamu: ${order['guest_name']}',
                  style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                ),
              ],
              const Divider(height: 24),

              // Items
              const Text(
                'Daftar Menu:',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.grey),
              ),
              const SizedBox(height: 8),
              ...items.map((item) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 4),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${item['quantity']}x ',
                          style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.black87),
                        ),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                item['menu_name'] ?? '',
                                style: const TextStyle(fontWeight: FontWeight.w500, color: Colors.black87),
                              ),
                              if (item['notes'] != null && item['notes'].toString().trim().isNotEmpty)
                                Text(
                                  'Catatan: "${item['notes']}"',
                                  style: const TextStyle(fontSize: 11, color: Colors.redAccent, fontStyle: FontStyle.italic),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  )),

              if (order['notes'] != null && order['notes'].toString().trim().isNotEmpty) ...[
                const SizedBox(height: 12),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'Catatan Pesanan: "${order['notes']}"',
                    style: TextStyle(fontSize: 11, color: Colors.red.shade900),
                  ),
                ),
              ],

              // Action button
              if (btnText.isNotEmpty) ...[
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () => _updateStatus(order['id'], nextStatus),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: btnColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
                    ),
                    child: Text(
                      btnText,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                  ),
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  Widget _buildMenuManagementView() {
    if (_categories.isEmpty) {
      return const Center(child: Text('Data menu kosong.'));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _categories.length,
      itemBuilder: (context, catIdx) {
        final category = _categories[catIdx];
        final menus = category['menus'] as List<dynamic>? ?? [];

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8.0),
              child: Text(
                category['name'] ?? '',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
              ),
            ),
            ...List.generate(menus.length, (menuIdx) {
              final menu = menus[menuIdx];
              final isAvailable = menu['is_available'] == true || menu['is_available'] == 1 || menu['is_available'] == '1';

              return Container(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.shade100),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      menu['name'] ?? '',
                      style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 14),
                    ),
                    Row(
                      children: [
                        Text(
                          isAvailable ? 'Tersedia' : 'Habis',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isAvailable ? Colors.green : Colors.red,
                          ),
                        ),
                        Switch(
                          value: isAvailable,
                          activeColor: Colors.green,
                          onChanged: (_) => _toggleMenuAvailability(menu['id'], catIdx, menuIdx),
                        ),
                      ],
                    ),
                  ],
                ),
              );
            }),
            const SizedBox(height: 12),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Kitchen Display System',
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
          : _currentTabIndex == 0
              ? _buildOrdersView()
              : _buildMenuManagementView(),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentTabIndex,
        onTap: (index) => setState(() => _currentTabIndex = index),
        selectedItemColor: Colors.indigoAccent,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.soup_kitchen_rounded),
            label: 'Pesanan Dapur',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.restaurant_menu_rounded),
            label: 'Stok Menu',
          ),
        ],
      ),
    );
  }
}
