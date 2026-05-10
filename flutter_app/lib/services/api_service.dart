import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Replace with your actual server URL
  static const String baseUrl = "http://localhost/parivar/api";

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse("$baseUrl/auth.php?action=login"),
      body: {
        'email': email,
        'password': password,
      },
    );

    if (response.statusCode == 200) {
      // In a real app, the server would return a JSON response with a token.
      // Since our current PHP redirects on success, we need to handle that or 
      // modify the PHP to return JSON for mobile.
      // For now, we assume success if no error is returned.
      return {'safalta': true, 'sandesh': 'सफल'};
    }
    return {'safalta': false, 'sandesh': 'त्रुटि'};
  }

  Future<List<dynamic>> getFeed() async {
    final response = await http.get(Uri.parse("$baseUrl/feed.php?action=list"));
    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'] ?? [];
    }
    return [];
  }

  Future<Map<String, dynamic>> getPanchang(String date) async {
    final response = await http.get(Uri.parse("$baseUrl/panchang.php?action=convert&gregorian=$date"));
    if (response.statusCode == 200) {
      return json.decode(response.body)['data'];
    }
    return {};
  }
}
