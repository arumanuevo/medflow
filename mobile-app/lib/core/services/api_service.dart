import 'dart:io';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:medflow_mobile/core/constants/app_constants.dart';

class ApiService {
  /// Intenta obtener los sensores con el token provisto.
  /// Retorna un mapa con 'success' y 'message' o la data.
  Future<Map<String, dynamic>> validateTokenAndFetchSensors(String token) async {
    try {
      final url = Uri.parse('${AppConstants.apiBaseUrl}/sensors');
      
      final response = await http.get(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final decoded = jsonDecode(response.body);

      if (response.statusCode == 200) {
        return {
          'success': true,
          'data': decoded['data'] ?? [],
        };
      } else {
        return {
          'success': false,
          'message': decoded['message'] ?? 'Token inválido o expirado.',
        };
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Error de conexión: No se pudo contactar al servidor ($e)',
      };
    }
  }

  Future<Map<String, dynamic>> syncMeasurements(String token, List<Map<String, dynamic>> outbox) async {
    final url = Uri.parse('${AppConstants.apiBaseUrl}/sync');
    try {
      List<Map<String, dynamic>> payload = [];
      
      for (var m in outbox) {
        String? base64img;
        if (m['photo_path'] != null) {
          final file = File(m['photo_path']);
          if (await file.exists()) {
            final bytes = await file.readAsBytes();
            base64img = base64Encode(bytes);
          }
        }
        
        payload.add({
          'sensor_id': m['sensor_id'],
          'value': m['value'],
          'mobile_uuid': m['mobile_uuid'],
          'photo_base64': base64img,
        });
      }

      final response = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'measurements': payload
        }),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        return {'success': true};
      } else {
        return {'success': false, 'message': 'HTTP ${response.statusCode}: No se pudieron subir los datos.'};
      }
    } catch (e) {
      return {'success': false, 'message': 'Red inaccesible. Conservando en Offline.'};
    }
  }
}
