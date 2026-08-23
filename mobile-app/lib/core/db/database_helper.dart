import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import 'package:medflow_mobile/models/sensor_model.dart';
import 'package:flutter/foundation.dart' show kIsWeb;

class DatabaseHelper {
  static final DatabaseHelper _instance = DatabaseHelper._internal();
  factory DatabaseHelper() => _instance;
  DatabaseHelper._internal();

  Database? _db;

  Future<Database> get database async {
    if (_db != null) return _db!;
    
    // Fallback: Si estamos en web temporalmente evitamos levantar Local DB real.
    // Sqflite crashea en web sin FFI, por ende para depurar la UI web no abrimos BD.
    if (kIsWeb) {
      throw UnsupportedError('SQLite no está soportado nativamente en Web para Flutter sin FFI.');
    }

    _db = await _initDB();
    return _db!;
  }

  Future<Database> _initDB() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'medflow_offline.db');

    return await openDatabase(
      path,
      version: 2,
      onCreate: (db, version) async {
        await db.execute('''
          CREATE TABLE sensors (
            id INTEGER PRIMARY KEY,
            identifier TEXT,
            name TEXT,
            group_name TEXT,
            last_value REAL,
            main_field_name TEXT,
            measurement_type TEXT,
            measurement_unit TEXT,
            measurement_icon TEXT
          )
        ''');

        await db.execute('''
          CREATE TABLE outbox (
            mobile_uuid TEXT PRIMARY KEY,
            sensor_id INTEGER,
            value REAL,
            timestamp TEXT,
            photo_path TEXT
          )
        ''');
      },
      onUpgrade: (db, oldVersion, newVersion) async {
        if (oldVersion < 2) {
          await db.execute('ALTER TABLE outbox ADD COLUMN photo_path TEXT;');
        }
      },
    );
  }

  /// Refresca la lista de sensores. Borra los actuales e inserta el nuevo payload.
  Future<void> replaceSensors(List<SensorModel> sensors) async {
    if (kIsWeb) {
      // Fallback para Google Chrome Web: Guardar el JSON en cache local del navegador
      final prefs = await SharedPreferences.getInstance();
      final jsonString = jsonEncode(sensors.map((s) => s.toMap()).toList());
      await prefs.setString('web_db_sensors', jsonString);
      return;
    }
    
    final db = await database;
    await db.transaction((txn) async {
      await txn.delete('sensors'); // Limpiar bandeja
      for (var sensor in sensors) {
        await txn.insert('sensors', sensor.toMap(), conflictAlgorithm: ConflictAlgorithm.replace);
      }
    });
  }

  Future<List<SensorModel>> getSensors() async {
    if (kIsWeb) {
      // Leer el JSON guardado artificialmente en Chrome Web
      final prefs = await SharedPreferences.getInstance();
      final jsonString = prefs.getString('web_db_sensors');
      if (jsonString != null && jsonString.isNotEmpty) {
        final List<dynamic> raw = jsonDecode(jsonString);
        return raw.map((map) => SensorModel.fromMap(map as Map<String, dynamic>)).toList();
      }
      return [];
    }
    
    final db = await database;
    final List<Map<String, dynamic>> maps = await db.query('sensors');
    return List.generate(maps.length, (i) => SensorModel.fromMap(maps[i]));
  }

  Future<List<int>> getCompletedSensorIds() async {
    if (kIsWeb) return []; // Fallback 
    final db = await database;
    final List<Map<String, dynamic>> maps = await db.query('outbox', columns: ['sensor_id']);
    return maps.map((row) => row['sensor_id'] as int).toList();
  }

  Future<void> saveOfflineMeasurement(String mobileUuid, int sensorId, double value, {String? photoPath}) async {
    if (kIsWeb) return;
    final db = await database;
    await db.insert(
      'outbox',
      {
        'mobile_uuid': mobileUuid,
        'sensor_id': sensorId,
        'value': value,
        'timestamp': DateTime.now().toIso8601String(),
        'photo_path': photoPath,
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<Map<String, dynamic>?> getOfflineMeasurement(int sensorId) async {
    if (kIsWeb) return null;
    final db = await database;
    final List<Map<String, dynamic>> maps = await db.query(
      'outbox',
      where: 'sensor_id = ?',
      whereArgs: [sensorId],
      limit: 1,
    );
    if (maps.isNotEmpty) {
      return maps.first;
    }
    return null;
  }

  Future<List<Map<String, dynamic>>> getOutboxMeasurements() async {
    if (kIsWeb) return [];
    final db = await database;
    return await db.query('outbox');
  }

  Future<void> clearOutbox() async {
    if (kIsWeb) return;
    final db = await database;
    await db.delete('outbox');
  }
}
