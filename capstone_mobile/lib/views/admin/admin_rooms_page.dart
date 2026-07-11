import 'package:flutter/material.dart';
import 'package:capstone_mobile/services/api_service.dart';
import 'package:capstone_mobile/views/admin/add_room_page.dart';
import 'package:capstone_mobile/views/admin/edit_room_page.dart';

class AdminRoomsPage extends StatefulWidget {
  const AdminRoomsPage({super.key});

  @override
  State<AdminRoomsPage> createState() => _AdminRoomsPageState();
}

class _AdminRoomsPageState extends State<AdminRoomsPage> {
  bool _isLoading = true;
  List<dynamic> _rooms = [];

  @override
  void initState() {
    super.initState();
    _loadRooms();
  }

  Future<void> _loadRooms() async {
    setState(() => _isLoading = true);
    try {
      final data = await ApiService.getRooms();
      setState(() {
        _rooms = data;
      });
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat kamar: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _deleteRoom(int id) async {
    try {
      await ApiService.deleteRoom(id);
      _loadRooms();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Kamar berhasil dihapus!')),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal menghapus kamar: $e')),
        );
      }
    }
  }

  void _confirmDelete(Map<String, dynamic> room) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Hapus Kamar'),
        content: Text('Yakin ingin menghapus Kamar ${room['room_number']}?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _deleteRoom(room['id']);
            },
            child: const Text('Hapus', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Daftar Kamar', style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black87),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadRooms,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadRooms,
              child: _rooms.isEmpty
                  ? const Center(child: Text('Belum ada data kamar.'))
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: _rooms.length,
                      itemBuilder: (context, index) {
                        final room = _rooms[index];
                        final isActive = room['is_active'] == true || room['is_active'] == 1 || room['is_active'] == '1';

                        return Container(
                          margin: const EdgeInsets.only(bottom: 12),
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
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Kamar ${room['room_number'] ?? ''}',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.black87),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Lantai ${room['floor'] ?? '-'}',
                                    style: TextStyle(color: Colors.grey.shade500, fontSize: 13),
                                  ),
                                  const SizedBox(height: 8),
                                  Row(
                                    children: [
                                      Container(
                                        width: 8,
                                        height: 8,
                                        decoration: BoxDecoration(
                                          color: isActive ? Colors.green : Colors.red,
                                          shape: BoxShape.circle,
                                        ),
                                      ),
                                      const SizedBox(width: 6),
                                      Text(
                                        isActive ? 'Aktif' : 'Nonaktif',
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: isActive ? Colors.green : Colors.red,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                              // Edit, Delete, QR Code
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  IconButton(
                                    icon: const Icon(Icons.edit, color: Colors.blue),
                                    onPressed: () async {
                                      final result = await Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (context) => EditRoomPage(room: room),
                                        ),
                                      );
                                      if (result == true) _loadRooms();
                                    },
                                  ),
                                  IconButton(
                                    icon: const Icon(Icons.delete, color: Colors.red),
                                    onPressed: () => _confirmDelete(room),
                                  ),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.end,
                                    children: [
                                      IconButton(
                                        icon: const Icon(Icons.qr_code_2_rounded, color: Colors.indigoAccent, size: 32),
                                        onPressed: () {
                                          showDialog(
                                            context: context,
                                            builder: (_) => AlertDialog(
                                              title: Text('Token Kamar ${room['room_number']}'),
                                              content: Column(
                                                mainAxisSize: MainAxisSize.min,
                                                crossAxisAlignment: CrossAxisAlignment.start,
                                                children: [
                                                  const Text('Token QR:'),
                                                  const SizedBox(height: 4),
                                                  SelectableText(
                                                    room['qr_token'] ?? '',
                                                    style: const TextStyle(fontWeight: FontWeight.bold, fontFamily: 'monospace'),
                                                  ),
                                                  const SizedBox(height: 12),
                                                  const Text('URL Masuk:'),
                                                  const SizedBox(height: 4),
                                                  SelectableText(
                                                    room['qr_url'] ?? '',
                                                    style: const TextStyle(fontSize: 12, color: Colors.blue),
                                                  ),
                                                ],
                                              ),
                                              actions: [
                                                TextButton(
                                                  onPressed: () => Navigator.pop(context),
                                                  child: const Text('Tutup'),
                                                ),
                                              ],
                                            ),
                                          );
                                        },
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ],
                          ),
                        );
                      },
                    ),
            ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const AddRoomPage()),
          );
          if (result == true) _loadRooms();
        },
        child: const Icon(Icons.add),
      ),
    );
  }
}