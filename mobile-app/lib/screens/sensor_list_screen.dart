import 'package:flutter/material.dart';
import 'package:medflow_mobile/core/theme/app_theme.dart';
import 'package:medflow_mobile/core/db/database_helper.dart';
import 'package:medflow_mobile/models/sensor_model.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:medflow_mobile/core/constants/app_constants.dart';
import 'package:medflow_mobile/screens/login_screen.dart';
import 'package:medflow_mobile/screens/measurement_screen.dart';
import 'package:medflow_mobile/main.dart' as importMain;
import 'package:medflow_mobile/core/services/api_service.dart';
import 'dart:io';

class SensorListScreen extends StatefulWidget {
  const SensorListScreen({Key? key}) : super(key: key);

  @override
  State<SensorListScreen> createState() => _SensorListScreenState();
}

class _SensorListScreenState extends State<SensorListScreen> {
  List<SensorModel> _pendingSensors = [];
  List<SensorModel> _completedSensors = [];
  List<Map<String, dynamic>> _historyRecords = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadSensorsLocally();
  }

  Future<void> _loadSensorsLocally() async {
    setState(() => _isLoading = true);
    try {
      final allSensors = await DatabaseHelper().getSensors();
      final completedIds = await DatabaseHelper().getCompletedSensorIds();
      final history = await DatabaseHelper().getHistoryMeasurements();

      setState(() {
        _pendingSensors = allSensors.where((s) => !completedIds.contains(s.id)).toList();
        _completedSensors = allSensors.where((s) => completedIds.contains(s.id)).toList();
        _historyRecords = history;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(AppConstants.tokenKey);
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return DefaultTabController(
      length: 3,
      child: Scaffold(
        backgroundColor: theme.scaffoldBackgroundColor,
        appBar: AppBar(
          backgroundColor: theme.colorScheme.surface,
          elevation: 0,
          title: Text(
            'Sensores Asignados',
            style: TextStyle(fontWeight: FontWeight.bold, letterSpacing: 1, color: theme.colorScheme.onSurface),
          ),
          actions: [
            IconButton(
              icon: Icon(isDark ? Icons.light_mode : Icons.dark_mode, color: AppTheme.primary),
              onPressed: () {
                importMain.appThemeNotifier.value = isDark ? ThemeMode.light : ThemeMode.dark;
              },
            ),
            IconButton(
              icon: const Icon(Icons.logout, color: AppTheme.error),
              onPressed: _logout,
            )
          ],
          bottom: TabBar(
            labelColor: AppTheme.primary,
            unselectedLabelColor: theme.colorScheme.onSurface.withOpacity(0.5),
            indicatorColor: AppTheme.primary,
            indicatorWeight: 3,
            tabs: const [
              Tab(icon: Icon(Icons.pending_actions), text: 'PENDIENTES'),
              Tab(icon: Icon(Icons.done_all), text: 'TOMADOS'),
              Tab(icon: Icon(Icons.history), text: 'HISTORIAL'),
            ],
          ),
        ),
        body: _isLoading
            ? const Center(child: CircularProgressIndicator(color: AppTheme.primary))
            : TabBarView(
                children: [
                  _pendingSensors.isEmpty ? _buildEmptyStatePending(theme) : _buildList(_pendingSensors, theme, isPending: true),
                  _completedSensors.isEmpty ? _buildEmptyStateCompleted(theme) : _buildList(_completedSensors, theme, isPending: false),
                  _historyRecords.isEmpty ? _buildEmptyHistory(theme) : _buildHistoryList(_historyRecords, theme),
                ],
              ),
        floatingActionButton: FloatingActionButton.extended(
          onPressed: () async {
            final outbox = await DatabaseHelper().getOutboxMeasurements();
            if (outbox.isEmpty) {
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('No hay mediciones offline para subir.')));
              return;
            }

            setState(() => _isLoading = true);
            
            final prefs = await SharedPreferences.getInstance();
            final token = prefs.getString(AppConstants.tokenKey) ?? '';

            final api = ApiService();
            final result = await api.syncMeasurements(token, outbox);

            if (result['success'] == true) {
              // Éxito. Limpiamos caja de salida localmente
              await DatabaseHelper().clearOutbox();
              
              // Recargamos todos los sensores de la nube de nuevo para el proximo ciclo
              final refresh = await api.validateTokenAndFetchSensors(token);
              if (refresh['success'] == true) {
                final sensors = (refresh['data'] as List).map((j) => SensorModel.fromJson(j)).toList();
                await DatabaseHelper().replaceSensors(sensors);
              } else {
                // Token fue revocado exitosamente por seguridad o caducó
                await DatabaseHelper().clearOutbox(); // o empty todo?
                // En su lugar, deslogueamos.
                await prefs.remove(AppConstants.tokenKey);
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                  content: Text('¡Sincronización Exitosa! Tu token ha caducado por seguridad.'), 
                  backgroundColor: AppTheme.success
                ));
                if (mounted) {
                  Navigator.of(context).pushReplacement(
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                  );
                }
                return;
              }

              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
                content: Text('¡Sincronización Exitosa! Datos en la Nube.'), 
                backgroundColor: AppTheme.success
              ));
            } else {
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                content: Text('Error Subiendo: ${result['message']} (Necesitás buena conexión)'), 
                backgroundColor: AppTheme.error
              ));
            }
            
            _loadSensorsLocally(); // Apaga el loading y refresca la UI
          },
          backgroundColor: AppTheme.accent,
          icon: const Icon(Icons.cloud_upload, color: Colors.white),
          label: const Text('SUBIR DATOS', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        ),
      ),
    );
  }

  Widget _buildEmptyStatePending(ThemeData theme) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.check_circle_outline, size: 80, color: AppTheme.success.withOpacity(0.5)),
          const SizedBox(height: 20),
          Text(
            'Ruta Completada',
            style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: theme.colorScheme.onSurface),
          ),
          const SizedBox(height: 8),
          Text(
            'Ya mediste todos los sensores\nprogramados para hoy.',
            textAlign: TextAlign.center,
            style: TextStyle(color: theme.colorScheme.onSurface.withOpacity(0.6)),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyStateCompleted(ThemeData theme) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.inbox, size: 80, color: theme.colorScheme.onSurface.withOpacity(0.1)),
          const SizedBox(height: 20),
          Text(
            'Aún no hay mediciones',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: theme.colorScheme.onSurface),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyHistory(ThemeData theme) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.history, size: 80, color: theme.colorScheme.onSurface.withOpacity(0.1)),
          const SizedBox(height: 20),
          Text(
            'Historial Vacío',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: theme.colorScheme.onSurface),
          ),
          const SizedBox(height: 8),
          Text(
            'Tus mediciones sincronizadas\naparecerán aquí.',
            textAlign: TextAlign.center,
            style: TextStyle(color: theme.colorScheme.onSurface.withOpacity(0.6)),
          ),
        ],
      ),
    );
  }

  Widget _buildHistoryList(List<Map<String, dynamic>> records, ThemeData theme) {
    return ListView.builder(
      padding: const EdgeInsets.only(top: 16, bottom: 100),
      itemCount: records.length,
      itemBuilder: (context, index) {
        final rec = records[index];
        final bool hasPhoto = rec['photo_path'] != null;
        DateTime syncDt = DateTime.parse(rec['sync_timestamp']).toLocal();
        String formattedSync = '${syncDt.day.toString().padLeft(2, '0')}/${syncDt.month.toString().padLeft(2, '0')} ${syncDt.hour.toString().padLeft(2, '0')}:${syncDt.minute.toString().padLeft(2, '0')}';
        
        return Container(
          margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          decoration: BoxDecoration(
            color: theme.colorScheme.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: theme.colorScheme.onSurface.withOpacity(0.1)),
          ),
          child: ListTile(
            contentPadding: const EdgeInsets.all(16),
            leading: const Icon(Icons.cloud_done, color: AppTheme.success),
            title: Text(
              'Medición: ${rec['value']}',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: theme.colorScheme.onSurface),
            ),
            subtitle: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Sensor ID: ${rec['sensor_id']}'),
                Text('Sincronizado: $formattedSync'),
              ],
            ),
            trailing: hasPhoto ? const Icon(Icons.camera_alt, color: AppTheme.primary) : null,
            onTap: () {
              if (hasPhoto) {
                showDialog(
                  context: context,
                  builder: (ctx) => Dialog(
                    backgroundColor: Colors.transparent,
                    child: Stack(
                      alignment: Alignment.center,
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(12),
                          child: Image.file(File(rec['photo_path']), fit: BoxFit.contain),
                        ),
                        Positioned(
                          top: 10,
                          right: 10,
                          child: IconButton(
                            icon: const Icon(Icons.close, color: Colors.white, size: 30),
                            onPressed: () => Navigator.of(ctx).pop(),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }
            },
          ),
        );
      },
    );
  }

  Widget _buildList(List<SensorModel> sensors, ThemeData theme, {required bool isPending}) {
    return RefreshIndicator(
      onRefresh: _loadSensorsLocally,
      color: AppTheme.primary,
      backgroundColor: theme.colorScheme.surface,
      child: ListView.builder(
        padding: const EdgeInsets.only(top: 16, bottom: 100),
        itemCount: sensors.length,
        itemBuilder: (context, index) {
          final sensor = sensors[index];
          return Container(
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: BoxDecoration(
              color: theme.colorScheme.surface,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: theme.colorScheme.onSurface.withOpacity(0.1)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 8,
                  offset: const Offset(0, 4),
                )
              ],
            ),
            child: ListTile(
              contentPadding: const EdgeInsets.all(16),
              leading: Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: (isPending ? AppTheme.primary : AppTheme.success).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(isPending ? Icons.speed : Icons.check_circle, color: isPending ? AppTheme.primary : AppTheme.success),
              ),
              title: Text(
                sensor.name,
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: theme.colorScheme.onSurface),
              ),
              subtitle: Padding(
                padding: const EdgeInsets.only(top: 8.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(Icons.location_on, size: 14, color: theme.colorScheme.onSurface.withOpacity(0.5)),
                        const SizedBox(width: 4),
                        Text(sensor.groupName, style: TextStyle(color: theme.colorScheme.onSurface.withOpacity(0.6))),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Referencia: ${sensor.lastValue ?? '---'} ${sensor.measurementUnit}',
                      style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                    ),
                  ],
                ),
              ),
              trailing: ElevatedButton(
                onPressed: () async {
                  await Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => MeasurementScreen(sensor: sensor)),
                  );
                  _loadSensorsLocally(); // Recargar al volver por si se midió
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: theme.scaffoldBackgroundColor,
                  foregroundColor: isPending ? AppTheme.primary : AppTheme.success,
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  side: BorderSide(color: isPending ? AppTheme.primary : AppTheme.success, width: 1.5),
                  elevation: 0,
                ),
                child: Text(isPending ? 'MEDIR' : 'EDITAR', style: const TextStyle(fontWeight: FontWeight.bold)),
              ),
            ),
          );
        },
      ),
    );
  }
}
