import 'dart:io';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String keyToken = 'auth_token';
  static const String keyUserRole = 'user_role';
  static const String keyUserName = 'user_name';
  static const String keyUserEmail = 'user_email';
  static const String keyBaseUrl = 'api_base_url';

  // Default IP for local development
  // 10.0.2.2 is the localhost loopback for Android Emulator
  static const String defaultBaseUrl = 'http://10.0.2.2:8000/api';

  static Future<String> getBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(keyBaseUrl) ?? defaultBaseUrl;
  }

  static Future<void> setBaseUrl(String url) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(keyBaseUrl, url);
  }

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(keyToken);
  }

  static Future<Map<String, String>> _headers() async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  static Future<Map<String, dynamic>> login(String email, String password, {String? baseUrl}) async {
    if (baseUrl != null) {
      await setBaseUrl(baseUrl);
    }
    final url = await getBaseUrl();
    
    final response = await http.post(
      Uri.parse('$url/auth/login'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'password': password,
        'device_name': 'flutter-mobile',
      }),
    );

    final data = jsonDecode(response.body);
    if (response.statusCode == 200 && data['status'] == 'success') {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(keyToken, data['data']['token']);
      await prefs.setString(keyUserRole, data['data']['user']['role']);
      await prefs.setString(keyUserName, data['data']['user']['name']);
      await prefs.setString(keyUserEmail, data['data']['user']['email']);
      return data;
    } else {
      throw Exception(data['message'] ?? 'Gagal login. Periksa kembali email dan password.');
    }
  }

  static Future<void> logout() async {
    final url = await getBaseUrl();
    try {
      await http.post(
        Uri.parse('$url/auth/logout'),
        headers: await _headers(),
      );
    } catch (_) {}
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(keyToken);
    await prefs.remove(keyUserRole);
    await prefs.remove(keyUserName);
    await prefs.remove(keyUserEmail);
  }

  static Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null;
  }

  static Future<String?> getUserRole() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(keyUserRole);
  }

  static Future<String?> getUserName() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(keyUserName);
  }

  // ─── Owner/Admin APIs ─────────────────────────────────
  
  static Future<Map<String, dynamic>> getAdminDashboard() async {
    final url = await getBaseUrl();
    final response = await http.get(
      Uri.parse('$url/admin/dashboard'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    }
    throw Exception('Gagal memuat dashboard admin');
  }

  static Future<List<dynamic>> getCategories() async {
    final url = await getBaseUrl();
    final response = await http.get(
      Uri.parse('$url/admin/categories'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body)['data'];
    }
    throw Exception('Gagal memuat kategori');
  }

 static Future<List<dynamic>> getMenus() async {
    final url = await getBaseUrl();
    final response = await http.get(
      Uri.parse('$url/admin/menus'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body)['data'];
    }
    throw Exception('Gagal memuat daftar menu');
  }

static Future<void> updateMenu({
  required int id,
  required String name,
  required int categoryId,
  required String description,
  required int price,
  File? image,
}) async {
  final url = await getBaseUrl();
  final token = await getToken();

  var request = http.MultipartRequest(
    'POST',
    Uri.parse('$url/admin/menus/$id'),
  );

  request.headers.addAll({
    'Accept': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  });

  request.fields['name'] = name;
  request.fields['category_id'] = categoryId.toString();
  request.fields['description'] = description;
  request.fields['price'] = price.toString();

  if (image != null) {
    request.files.add(
      await http.MultipartFile.fromPath(
        'image',
        image.path,
      ),
    );
  }

  final response = await request.send();

  if (response.statusCode != 200) {
    final body = await response.stream.bytesToString();
    throw Exception(body);
  }
}

static Future<void> addMenu({
  required String name,
  required int categoryId,
  required String description,
  required int price,
  File? image,
}) async {
  final url = await getBaseUrl();
  final token = await getToken();

  var request = http.MultipartRequest(
    'POST',
    Uri.parse('$url/admin/menus'),
  );

  request.headers.addAll({
    'Accept': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  });

  request.fields['name'] = name;
  request.fields['category_id'] = categoryId.toString();
  request.fields['description'] = description;
  request.fields['price'] = price.toString();

  if (image != null) {
    request.files.add(
      await http.MultipartFile.fromPath(
        'image',
        image.path,
      ),
    );
  }

  final response = await request.send();

  if (response.statusCode != 200 &&
      response.statusCode != 201) {
    final body = await response.stream.bytesToString();
    throw Exception(body);
  }
}

static Future<void> deleteMenu(int id) async {
  final url = await getBaseUrl();

  final response = await http.delete(
    Uri.parse('$url/admin/menus/$id'),
    headers: await _headers(),
  );

  if (response.statusCode != 200) {
    throw Exception("Gagal menghapus menu");
  }
}

  static Future<List<dynamic>> getRooms() async {
    final url = await getBaseUrl();
    final response = await http.get(
      Uri.parse('$url/admin/rooms'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body)['data'];
    }
    throw Exception('Gagal memuat daftar kamar');
  }

  static Future<void> addRoom({
    required String roomNumber,
    required int floor,
    bool isActive = true,
  }) async {
    final url = await getBaseUrl();
    final response = await http.post(
      Uri.parse('$url/admin/rooms'),
      headers: await _headers(),
      body: jsonEncode({
        'room_number': roomNumber,
        'floor': floor,
        'is_active': isActive,
      }),
    );
    if (response.statusCode != 200 && response.statusCode != 201) {
      throw Exception(response.body);
    }
  }

  static Future<void> updateRoom({
    required int id,
    required String roomNumber,
    required int floor,
    required bool isActive,
  }) async {
    final url = await getBaseUrl();
    final response = await http.put(
      Uri.parse('$url/admin/rooms/$id'),
      headers: await _headers(),
      body: jsonEncode({
        'room_number': roomNumber,
        'floor': floor,
        'is_active': isActive,
      }),
    );
    if (response.statusCode != 200) {
      throw Exception(response.body);
    }
  }

  static Future<void> deleteRoom(int id) async {
    final url = await getBaseUrl();
    final response = await http.delete(
      Uri.parse('$url/admin/rooms/$id'),
      headers: await _headers(),
    );
    if (response.statusCode != 200) {
      throw Exception('Gagal menghapus kamar');
    }
  }

  // ─── Kitchen APIs ─────────────────────────────────────

  static Future<Map<String, dynamic>> getKitchenOrders() async {
    final url = await getBaseUrl();
    final response = await http.get(
      Uri.parse('$url/kitchen/orders'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body)['data'];
    }
    throw Exception('Gagal memuat order dapur');
  }

  static Future<void> updateOrderStatus(int orderId, String status) async {
    final url = await getBaseUrl();
    final response = await http.patch(
      Uri.parse('$url/kitchen/orders/$orderId/status'),
      headers: await _headers(),
      body: jsonEncode({'status': status}),
    );
    if (response.statusCode != 200) {
      throw Exception('Gagal mengubah status pesanan');
    }
  }

  static Future<void> toggleMenuAvailability(int menuId) async {
    final url = await getBaseUrl();
    final response = await http.patch(
      Uri.parse('$url/kitchen/menus/$menuId/toggle'),
      headers: await _headers(),
    );
    if (response.statusCode != 200) {
      throw Exception('Gagal mengubah ketersediaan menu');
    }
  }

  // ─── Finance APIs ─────────────────────────────────────

  static Future<Map<String, dynamic>> getFinanceDashboard() async {
    final url = await getBaseUrl();
    final response = await http.get(
      Uri.parse('$url/finance/dashboard'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      return jsonDecode(response.body)['data'];
    }
    throw Exception('Gagal memuat dashboard keuangan');
  }
}