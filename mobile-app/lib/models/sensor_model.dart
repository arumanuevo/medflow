class SensorModel {
  final int id;
  final String identifier;
  final String name;
  final String groupName;
  final double? lastValue;
  final String mainFieldName;
  final String measurementType;
  final String measurementUnit;
  final String measurementIcon;

  SensorModel({
    required this.id,
    required this.identifier,
    required this.name,
    required this.groupName,
    this.lastValue,
    required this.mainFieldName,
    required this.measurementType,
    required this.measurementUnit,
    required this.measurementIcon,
  });

  factory SensorModel.fromJson(Map<String, dynamic> json) {
    return SensorModel(
      id: json['id'],
      identifier: json['identifier'] ?? '',
      name: json['name'] ?? '',
      groupName: json['group_name'] ?? 'Sin grupo',
      lastValue: json['last_value'] != null ? double.tryParse(json['last_value'].toString()) : null,
      mainFieldName: json['main_field_name'] ?? 'valor',
      measurementType: json['measurement_type'] ?? '',
      measurementUnit: json['measurement_unit'] ?? '',
      measurementIcon: json['measurement_icon'] ?? 'fa-solid fa-circle',
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'identifier': identifier,
      'name': name,
      'group_name': groupName,
      'last_value': lastValue,
      'main_field_name': mainFieldName,
      'measurement_type': measurementType,
      'measurement_unit': measurementUnit,
      'measurement_icon': measurementIcon,
    };
  }

  factory SensorModel.fromMap(Map<String, dynamic> map) {
    return SensorModel(
      id: map['id'],
      identifier: map['identifier'],
      name: map['name'],
      groupName: map['group_name'],
      lastValue: map['last_value'],
      mainFieldName: map['main_field_name'],
      measurementType: map['measurement_type'],
      measurementUnit: map['measurement_unit'],
      measurementIcon: map['measurement_icon'],
    );
  }
}
