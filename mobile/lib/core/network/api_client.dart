import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/environment.dart';
import '../exceptions/api_exception.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiClient {
  final _baseUrl = Environment.apiUrl;

  Future<Map<String, dynamic>> post(String endpoint, Map<String, dynamic> data) async {
    final url = Uri.parse('$_baseUrl/$endpoint');

    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');

    final response = await http.post(
      url,
      headers: {
        'Content-Type': 'application/json', 
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
        },
      body: jsonEncode(data),
    );

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return jsonDecode(response.body);
    } else {
      throw ApiException('Erro: ${response.statusCode}', response.body);
    }
  }

  Future<List<dynamic>> get(String endpoint) async {
  final url = Uri.parse('$_baseUrl/$endpoint');

  final prefs = await SharedPreferences.getInstance();
  final token = prefs.getString('auth_token');

  final response = await http.get(
    url,
    headers: {
      'Content-Type': 'application/json', 
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    },
  );

  if (response.statusCode >= 200 && response.statusCode < 300) {
    final decoded = jsonDecode(response.body);

    if (decoded is Map<String, dynamic> && decoded.containsKey('data')) {
      return (decoded['data'] as List).cast<Map<String, dynamic>>();
    } else if (decoded is List) {
      return decoded.cast<Map<String, dynamic>>();
    } else {
      throw ApiException('Formato de resposta inesperado', response.body);
    }
  } else {
    throw ApiException('Erro: ${response.statusCode}', response.body);
  }
}
}
