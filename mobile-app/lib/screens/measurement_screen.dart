import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:uuid/uuid.dart';
import 'package:medflow_mobile/models/sensor_model.dart';
import 'package:medflow_mobile/core/theme/app_theme.dart';
import 'package:medflow_mobile/core/db/database_helper.dart';

class MeasurementScreen extends StatefulWidget {
  final SensorModel sensor;

  const MeasurementScreen({Key? key, required this.sensor}) : super(key: key);

  @override
  State<MeasurementScreen> createState() => _MeasurementScreenState();
}

class _MeasurementScreenState extends State<MeasurementScreen> {
  final _valueController = TextEditingController();
  File? _imageFile;
  final ImagePicker _picker = ImagePicker();
  String? _localTimestamp;

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    // 1. Cargar el valor referencial del servidor
    if (widget.sensor.lastValue != null) {
      _valueController.text = widget.sensor.lastValue.toString();
    }
    
    // 2. Si ya lo medimos (está en outbox), pisar con nuestro dato local offline
    final offlineRow = await DatabaseHelper().getOfflineMeasurement(widget.sensor.id);
    if (offlineRow != null && mounted) {
      setState(() {
        _valueController.text = offlineRow['value'].toString();
        final dt = DateTime.tryParse(offlineRow['timestamp'].toString());
        if (dt != null) {
          _localTimestamp = '${dt.day.toString().padLeft(2,'0')}/${dt.month.toString().padLeft(2,'0')}/${dt.year} ${dt.hour.toString().padLeft(2,'0')}:${dt.minute.toString().padLeft(2,'0')}';
        }
      });
    }
  }

  Future<void> _takePhoto() async {
    final ImageSource source = (Platform.isWindows || Platform.isLinux || Platform.isMacOS)
        ? ImageSource.gallery
        : ImageSource.camera;

    // Limitamos a 720x1280 (vertical) y 70% calidad para cuidar el payload en rutas rurales
    final XFile? photo = await _picker.pickImage(
      source: source, 
      imageQuality: 70,
      maxWidth: 720,
      maxHeight: 1280,
    );
    if (photo != null) {
      setState(() {
        _imageFile = File(photo.path);
      });
    }
  }

  Future<void> _saveMeasurement() async {
    final val = double.tryParse(_valueController.text);
    if (val == null) {
       ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Por favor, ingresá un valor numérico.'), backgroundColor: AppTheme.error));
       return;
    }

    final double lastVal = widget.sensor.lastValue ?? 0;
    if (val < lastVal) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Error: La medición actual ($val) no puede ser MENOR a la anterior ($lastVal).'), 
        backgroundColor: AppTheme.error
      ));
      return;
    }

    final String mobileUuid = const Uuid().v4();
    
    // Inserción Real Offline
    await DatabaseHelper().saveOfflineMeasurement(mobileUuid, widget.sensor.id, val, photoPath: _imageFile?.path);
    
    if (!mounted) return;
    Navigator.of(context).pop();
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Medición guardada OFFLINE.'), backgroundColor: AppTheme.success,));
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      backgroundColor: theme.scaffoldBackgroundColor,
      appBar: AppBar(
        title: Text(widget.sensor.name, style: TextStyle(color: theme.colorScheme.onSurface)),
        backgroundColor: theme.colorScheme.surface,
        iconTheme: IconThemeData(color: theme.colorScheme.onSurface),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: theme.colorScheme.surface,
                borderRadius: BorderRadius.circular(12),
                border: const Border(left: BorderSide(color: AppTheme.primary, width: 4)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Ruta: ${widget.sensor.groupName}', style: TextStyle(color: theme.colorScheme.onSurface.withOpacity(0.6))),
                  const SizedBox(height: 8),
                  Text('Tipo: ${widget.sensor.measurementType.toUpperCase()}', style: TextStyle(fontWeight: FontWeight.bold, color: theme.colorScheme.onSurface)),
                  const Divider(height: 24),
                  Row(
                    children: [
                      const Icon(Icons.history, size: 16, color: AppTheme.primary),
                      const SizedBox(width: 8),
                      Text(
                        'Última Medición de Servidor:', 
                        style: TextStyle(color: theme.colorScheme.onSurface.withOpacity(0.8), fontSize: 13)
                      ),
                      const Spacer(),
                      Text(
                        '${widget.sensor.lastValue ?? 0} ${widget.sensor.measurementUnit}',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppTheme.primary),
                      ),
                    ],
                  ),
                  if (_localTimestamp != null) ...[
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.save, size: 16, color: AppTheme.accent),
                        const SizedBox(width: 8),
                        Text(
                          'Pendiente de Subida:', 
                          style: TextStyle(color: AppTheme.accent, fontWeight: FontWeight.bold, fontSize: 13)
                        ),
                        const Spacer(),
                        Text(
                          _localTimestamp!,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: AppTheme.textHint),
                        ),
                      ],
                    ),
                  ]
                ],
              ),
            ),
            const SizedBox(height: 30),
            
            TextField(
              controller: _valueController,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: theme.colorScheme.onSurface),
              textAlign: TextAlign.center,
              decoration: InputDecoration(
                labelText: 'Ingresar ${widget.sensor.mainFieldName}',
                suffixText: widget.sensor.measurementUnit,
                alignLabelWithHint: true,
              ),
            ),
            
            const SizedBox(height: 30),
            
            GestureDetector(
              onTap: _takePhoto,
              child: Container(
                height: 200,
                decoration: BoxDecoration(
                  color: theme.colorScheme.surface,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: _imageFile == null ? theme.colorScheme.onSurface.withOpacity(0.2) : AppTheme.primary, width: 2),
                ),
                child: _imageFile != null
                    ? ClipRRect(
                        borderRadius: BorderRadius.circular(14),
                        child: Image.file(_imageFile!, fit: BoxFit.cover, width: double.infinity),
                      )
                    : Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.camera_alt, size: 50, color: AppTheme.primary),
                          const SizedBox(height: 10),
                          Text(
                            (Platform.isWindows || Platform.isLinux || Platform.isMacOS) 
                              ? 'Adjuntar Foto (PC)' 
                              : 'Tomar Foto del Medidor', 
                            style: TextStyle(color: theme.colorScheme.onSurface, fontWeight: FontWeight.bold)
                          ),
                        ],
                      ),
              ),
            ),
            
            const SizedBox(height: 40),
            
            ElevatedButton.icon(
              onPressed: _saveMeasurement,
              icon: const Icon(Icons.save),
              label: const Text('GUARDAR OFFLINE'),
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 20),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
