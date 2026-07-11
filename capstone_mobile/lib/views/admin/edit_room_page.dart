import 'package:flutter/material.dart';
import 'package:capstone_mobile/services/api_service.dart';

class EditRoomPage extends StatefulWidget {
  final Map<String, dynamic> room;

  const EditRoomPage({super.key, required this.room});

  @override
  State<EditRoomPage> createState() => _EditRoomPageState();
}

class _EditRoomPageState extends State<EditRoomPage> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _roomNumberController;
  late final TextEditingController _floorController;
  late bool _isActive;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _roomNumberController = TextEditingController(text: (widget.room['room_number'] ?? '').toString());
    _floorController = TextEditingController(text: (widget.room['floor'] ?? '').toString());
    _isActive = widget.room['is_active'] == true || widget.room['is_active'] == 1 || widget.room['is_active'] == '1';
  }

  Future<void> _handleSave() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);
    try {
      await ApiService.updateRoom(
        id: widget.room['id'],
        roomNumber: _roomNumberController.text.trim(),
        floor: int.parse(_floorController.text.trim()),
        isActive: _isActive,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Kamar berhasil diperbarui!')),
      );
      Navigator.pop(context, true);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memperbarui kamar: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Edit Kamar', style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.black87),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              TextFormField(
                controller: _roomNumberController,
                decoration: const InputDecoration(labelText: 'Nomor Kamar', border: OutlineInputBorder()),
                validator: (v) => (v == null || v.trim().isEmpty) ? 'Nomor kamar wajib diisi' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _floorController,
                decoration: const InputDecoration(labelText: 'Lantai', border: OutlineInputBorder()),
                keyboardType: TextInputType.number,
                validator: (v) {
                  if (v == null || v.trim().isEmpty) return 'Lantai wajib diisi';
                  if (int.tryParse(v.trim()) == null) return 'Lantai harus berupa angka';
                  return null;
                },
              ),
              const SizedBox(height: 16),
              SwitchListTile(
                contentPadding: EdgeInsets.zero,
                title: const Text('Kamar Aktif'),
                value: _isActive,
                activeThumbColor: Colors.green,
                onChanged: (v) => setState(() => _isActive = v),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isSaving ? null : _handleSave,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.indigoAccent,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: _isSaving
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : const Text('Simpan Perubahan', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}