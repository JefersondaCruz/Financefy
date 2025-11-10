import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/environment.dart';
import '../exceptions/api_exception.dart';

class ApiClient {
  final _baseUrl = Environment.apiUrl;

  Future<Map<String, dynamic>> post(String endpoint, Map<String, dynamic> data) async {
    final url = Uri.parse('$_baseUrl/$endpoint');

    final response = await http.post(
      url,
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(data),
    );

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return jsonDecode(response.body);
    } else {
      throw ApiException('Erro: ${response.statusCode}', response.body);
    }
  }
}
