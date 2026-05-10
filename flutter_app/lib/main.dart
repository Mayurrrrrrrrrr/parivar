import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';

void main() {
  runApp(
    MultiProvider(
      providers: [
        // Add providers here later
      ],
      child: const ParivarApp(),
    ),
  );
}

class ParivarApp extends StatelessWidget {
  const ParivarApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'परिवार',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFFB5470B), // केसरिया
          primary: const Color(0xFFB5470B),
          secondary: const Color(0xFF8B2500),
          surface: const Color(0xFFFFF8F0), // क्रीम
        ),
        textTheme: GoogleFonts.notoSansDevanagariTextTheme(
          Theme.of(context).textTheme,
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: Color(0xFFB5470B),
          foregroundColor: Colors.white,
          centerTitle: true,
        ),
      ),
      home: const LoginScreen(),
      routes: {
        '/dashboard': (context) => const DashboardScreen(),
      },
    );
  }
}
