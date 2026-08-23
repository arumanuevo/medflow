import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:medflow_mobile/core/theme/app_theme.dart';
import 'package:medflow_mobile/core/constants/app_constants.dart';
import 'package:medflow_mobile/core/services/api_service.dart';
import 'package:medflow_mobile/core/db/database_helper.dart';
import 'package:medflow_mobile/models/sensor_model.dart';
import 'package:medflow_mobile/screens/sensor_list_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _tokenController = TextEditingController(); 
  bool _isLoading = false;
  final ApiService _apiService = ApiService();

  @override
  void initState() {
    super.initState();
    _checkExistingToken();
  }

  Future<void> _checkExistingToken() async {
    final prefs = await SharedPreferences.getInstance();
    final savedToken = prefs.getString(AppConstants.tokenKey);
    if (savedToken != null && savedToken.isNotEmpty) {
      _tokenController.text = savedToken;
      // Podríamos auto-sincronizar aquí. Por ahora, solo lo pegamos en el campo.
    }
  }

  Future<void> _handleSync() async {
    final token = _tokenController.text.trim();
    if (token.isEmpty) {
      _showSnackbar('Por favor, pegá un Token válido.', isError: true);
      return;
    }

    setState(() => _isLoading = true);

    final result = await _apiService.validateTokenAndFetchSensors(token);

    setState(() => _isLoading = false);

    if (result['success'] == true) {
      // Guardar Token de forma permanente
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(AppConstants.tokenKey, token);

      final rawList = result['data'] as List;
      final int sensorsCount = rawList.length;
      
      try {
        // Hydrate Models
        final sensors = rawList.map((j) => SensorModel.fromJson(j)).toList();
        // Guardar físicamente offline en SQLite
        await DatabaseHelper().replaceSensors(sensors);
      } catch (e) {
        // Ignorar si estamos en modo Web, ya definimos un Fallback en la clase padre
      }

      _showSnackbar('¡Sincronizado! Se bajaron $sensorsCount sensores al dispositivo.', isError: false);
      
      if (mounted) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const SensorListScreen())
        );
      }
    } else {
      _showSnackbar(result['message'] ?? 'Error desconocido', isError: true);
    }
  }

  void _showSnackbar(String message, {required bool isError}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? AppTheme.error : AppTheme.success,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: theme.scaffoldBackgroundColor,
      body: Stack(
        children: [
          // Background Gradient Circles for Glassmorphism Effect
          Positioned(
            top: -100,
            right: -100,
            child: Container(
              width: 300,
              height: 300,
              decoration: BoxDecoration(
                color: AppTheme.accent.withOpacity(0.15),
                shape: BoxShape.circle,
              ),
            ),
          ),
          Positioned(
            bottom: -50,
            left: -50,
            child: Container(
              width: 250,
              height: 250,
              decoration: BoxDecoration(
                color: AppTheme.primary.withOpacity(0.15),
                shape: BoxShape.circle,
              ),
            ),
          ),
          
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24.0),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Glassmorphism Card
                    ClipRRect(
                      borderRadius: BorderRadius.circular(24.0),
                      child: BackdropFilter(
                        filter: ImageFilter.blur(sigmaX: 15, sigmaY: 15),
                        child: Container(
                          padding: const EdgeInsets.all(32.0),
                          decoration: BoxDecoration(
                            color: isDark ? const Color(0xFF1a1d2e).withOpacity(0.6) : Colors.white.withOpacity(0.7),
                            borderRadius: BorderRadius.circular(24.0),
                            border: Border.all(
                              color: isDark ? Colors.white.withOpacity(0.1) : Colors.black.withOpacity(0.05),
                              width: 1.5,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.05),
                                blurRadius: 20,
                                spreadRadius: -5,
                              )
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              const Icon(
                                Icons.lock_outline,
                                size: 50,
                                color: AppTheme.primary,
                              ),
                              const SizedBox(height: 24),
                              Text(
                                'Autorización',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  fontSize: 24,
                                  fontWeight: FontWeight.bold,
                                  color: theme.colorScheme.onSurface,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                'Ingresá tu Token de Inspector o usá el Enlace Mágico enviado a tu correo.',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  fontSize: 14,
                                  color: theme.colorScheme.onSurface.withOpacity(0.6),
                                ),
                              ),
                              const SizedBox(height: 32),
                              
                              TextField(
                                controller: _tokenController,
                                style: TextStyle(color: theme.colorScheme.onSurface),
                                decoration: InputDecoration(
                                  hintText: 'Pegá el Token aquí...',
                                  prefixIcon: Icon(Icons.key, color: theme.colorScheme.onSurface.withOpacity(0.4)),
                                ),
                              ),
                              
                              const SizedBox(height: 24),
                              
                              SizedBox(
                                height: 50,
                                child: ElevatedButton(
                                  onPressed: _isLoading ? null : _handleSync,
                                  child: _isLoading 
                                    ? const SizedBox(
                                        height: 20, width: 20, 
                                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)
                                      )
                                    : const Text('Sincronizar Dispositivo'),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
