import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'package:intl/intl.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  String _aajKiTithi = "लोड हो रहा है...";
  List<dynamic> _feedItems = [];

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  void _loadData() async {
    final panchang = await ApiService().getPanchang(DateFormat('yyyy-MM-dd').format(DateTime.now()));
    final feed = await ApiService().getFeed();
    setState(() {
      _aajKiTithi = panchang['formatted'] ?? "अज्ञात";
      _feedItems = feed;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('परिवार — डैशबोर्ड')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              color: const Color(0xFFB5470B),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('आज का पंचांग', style: TextStyle(color: Colors.white70)),
                    const SizedBox(height: 8),
                    Text(
                      _aajKiTithi,
                      style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            const Text('परिवार फ़ीड', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            ..._feedItems.map((item) => Card(
              margin: const EdgeInsets.only(bottom: 12),
              child: ListTile(
                title: Text(item['user_naam'] ?? 'अज्ञात', style: const TextStyle(fontWeight: FontWeight.bold)),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(item['sandesh'] ?? ''),
                    const SizedBox(height: 4),
                    Text(item['banaya_at'] ?? '', style: const TextStyle(fontSize: 12, color: Colors.grey)),
                  ],
                ),
              ),
            )).toList(),
          ],
        ),
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: 0,
        selectedItemColor: const Color(0xFFB5470B),
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.dashboard), label: 'मुख्य'),
          BottomNavigationBarItem(icon: Icon(Icons.account_tree), label: 'वंश वृक्ष'),
          BottomNavigationBarItem(icon: Icon(Icons.settings), label: 'सेटिंग्स'),
        ],
      ),
    );
  }
}
