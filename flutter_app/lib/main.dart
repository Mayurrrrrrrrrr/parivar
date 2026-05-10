import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'screens/splash_screen.dart';
import 'screens/webview_screen.dart';

void main() {
  runApp(const ParivarApp());
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
          seedColor: const Color(0xFFFF8C00),
          primary: const Color(0xFFFF8C00),
          secondary: const Color(0xFFE67E00),
        ),
        textTheme: GoogleFonts.notoSansDevanagariTextTheme(),
      ),
      initialRoute: '/',
      routes: {
        '/': (context) => const SplashScreen(),
        '/home': (context) => const WebViewScreen(),
      },
    );
  }
}
