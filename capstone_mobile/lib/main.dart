import 'package:flutter/material.dart';
import 'package:capstone_mobile/services/api_service.dart';
import 'package:capstone_mobile/views/auth/login_page.dart';
import 'package:capstone_mobile/views/admin/admin_dashboard_page.dart';
import 'package:capstone_mobile/views/kitchen/kitchen_dashboard_page.dart';
import 'package:capstone_mobile/views/finance/finance_dashboard_page.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Capstone Native Admin',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF4F46E5), // Indigo 600
          primary: const Color(0xFF4F46E5),
          secondary: const Color(0xFF0EA5E9), // Sky 500
          background: const Color(0xFFF8FAFC),
        ),
        fontFamily: 'Roboto',
      ),
      home: const AuthSessionChecker(),
    );
  }
}

class AuthSessionChecker extends StatefulWidget {
  const AuthSessionChecker({super.key});

  @override
  State<AuthSessionChecker> createState() => _AuthSessionCheckerState();
}

class _AuthSessionCheckerState extends State<AuthSessionChecker> {
  @override
  void initState() {
    super.initState();
    _checkSession();
  }

  Future<void> _checkSession() async {
    final loggedIn = await ApiService.isLoggedIn();
    if (!loggedIn) {
      _redirectTo(const LoginPage());
      return;
    }

    final role = await ApiService.getUserRole();
    if (role == 'owner') {
      _redirectTo(const AdminDashboardPage());
    } else if (role == 'kitchen') {
      _redirectTo(const KitchenDashboardPage());
    } else if (role == 'finance') {
      _redirectTo(const FinanceDashboardPage());
    } else {
      // Invalide role, force logout
      await ApiService.logout();
      _redirectTo(const LoginPage());
    }
  }

  void _redirectTo(Widget page) {
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => page),
    );
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(
        child: CircularProgressIndicator(),
      ),
    );
  }
}
