import 'package:flutter/material.dart';
import 'package:capstone_mobile/services/api_service.dart';
import 'package:capstone_mobile/views/admin/add_menu_page.dart';
import 'package:capstone_mobile/views/admin/edit_menu_page.dart';

class AdminMenusPage extends StatefulWidget {
  const AdminMenusPage({super.key});

  @override
  State<AdminMenusPage> createState() => _AdminMenusPageState();
}

class _AdminMenusPageState extends State<AdminMenusPage> {
  bool _isLoading = true;
  List<dynamic> _menus = [];

  @override
  void initState() {
    super.initState();
    _loadMenus();
  }

  Future<void> _loadMenus() async {
    setState(() => _isLoading = true);
    try {
      final data = await ApiService.getMenus();
      setState(() {
        _menus = data;
      });
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat menu: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _toggleAvailability(int menuId, int index) async {
    try {
      await ApiService.toggleMenuAvailability(menuId);
      setState(() {
        _menus[index]['is_available'] = !_menus[index]['is_available'];
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Ketersediaan menu diperbarui!')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memperbarui ketersediaan: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Daftar Menu', style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black87),
        actions: [
        IconButton(
        icon: const Icon(Icons.refresh),
        onPressed: _loadMenus,
        ),
 ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadMenus,
              child: _menus.isEmpty
                  ? const Center(child: Text('Belum ada menu.'))
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: _menus.length,
                      itemBuilder: (context, index) {
                        final menu = _menus[index];
                        final isAvailable = menu['is_available'] == true || menu['is_available'] == 1;

                        return Container(
                          margin: const EdgeInsets.only(bottom: 12),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha:0.02),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
                              ),
                            ],
                            border: Border.all(color: Colors.grey.shade100),
                          ),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(16),
                            child: Row(
                              children: [
                                // Menu image if exists
                                Container(
                                  width: 90,
                                  height: 90,
                                  color: Colors.indigo.shade50,
                                  child: menu['image_url'] != null
                                      ? Image.network(
                                          menu['image_url'],
                                          fit: BoxFit.cover,
                                          errorBuilder: (_, __, ___) => const Icon(Icons.restaurant, color: Colors.indigoAccent),
                                        )
                                      : const Icon(Icons.restaurant, color: Colors.indigoAccent, size: 28),
                                ),
                                const SizedBox(width: 16),
                                Expanded(
                                  child: Padding(
                                    padding: const EdgeInsets.symmetric(vertical: 8),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          menu['name'] ?? '',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Colors.black87),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          menu['category_name'] ?? '',
                                          style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
                                        ),
                                        const SizedBox(height: 8),
                                        Text(
                                          menu['formatted_price'] ?? '',
                                          style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.indigoAccent, fontSize: 14),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                                // Edit, Delete, and Switch for availability toggle
                                Padding(
                                  padding: const EdgeInsets.only(right: 8),
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      IconButton(
                                        icon: const Icon(Icons.edit, color: Colors.blue),
                                        onPressed: () async {
                                          final result = await Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                              builder: (context) => EditMenuPage(menu: menu),
                                            ),
                                          );
                                          if (result == true) _loadMenus();
                                        },
                                      ),
                                      IconButton(
                                        icon: const Icon(Icons.delete, color: Colors.red),
                                        onPressed: () async {
                                          await ApiService.deleteMenu(menu['id']);
                                          _loadMenus();
                                        },
                                      ),
                                      Switch(
                                        value: isAvailable,
                                        activeThumbColor: Colors.green,
                                        onChanged: (_) => _toggleAvailability(menu['id'], index),
                                      ),
                                      Text(
                                        isAvailable ? 'Tersedia' : 'Habis',
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.w600,
                                          color: isAvailable ? Colors.green : Colors.red,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
            ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const AddMenuPage()),
          );
          if (result == true) _loadMenus();
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}