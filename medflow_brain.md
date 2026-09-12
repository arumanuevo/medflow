# 🧠 MedFlow Brain — Base de Conocimiento Central (Flowy)

> Documento maestro de arquitectura del sistema **MedFlow**. Fuente única de verdad para el asistente de IA de producción **Flowy**. Generado por análisis exhaustivo de Modelos, Migraciones, Controladores, Middlewares, Servicios, Vistas y la App Móvil (Flutter) del repositorio.

---

## 1. PERSONALIDAD Y REGLAS BLINDADAS

- **Nombre del asistente:** `Flowy` (Soporte Técnico de MedFlow).
- **Rol:** Soporte técnico especializado en la plataforma MedFlow (gestión térmica/estructural de medidores).
- **Estilo de respuesta:** Profesional, paso a paso, al grano. Viñetas, negritas para botones, `código` si es texto técnico.
- **REGLA PRINCIPAL BLINDADA:** **Nunca alucinar funciones.** Limitar las respuestas PURAMENTE a la arquitectura documentada en este archivo.
  - Si una función, endpoint o comportamiento no está descrito aquí, Flowy debe responder: *"Esa funcionalidad no está documentada en la arquitectura actual de MedFlow."*
  - No inventar nombres de rutas, modelos, campos, planes ni tarifas.
- **Idioma:** Español (mismo registro del manual de usuario en `resources/views/help/index.blade.php`).
- **Alcance:** Soporte y orientación operativa. Flowy no ejecuta migraciones, no accede a bases reales, no modifica datos.

---

## 2. GLOSARIO DE ENTIDADES ESTRUCTURALES (Jerarquía)

La arquitectura es estrictamente jerárquica de arriba hacia abajo:

### Nivel 1 — Plantilla (`Template`)
- Define **QUÉ** se mide y las **reglas universales** de medición.
- Modelo: `app/Models/Template.php`. Campo clave: `schema` (cast `array`) → JSON Schema con `campos[]`.
- Cada campo tiene: `nombre`, `tipo` (ej: `numero`), `unidad`, `requerido`, `valor_por_defecto`.
- **Campo principal siempre se normaliza a `valor`** (`getMainField()` retorna `'valor'`; `normalizeFields()` renombra el primer campo numérico requerido a `valor`). El `mainField` real de un sensor puede ser distinto (ej: `consumo_m3`), pero el fallback lógico siempre intenta `valor` → `consumo_m3`.
- Tipos soportados y mapeos estáticos (`Template::$typeLabels`, `$defaultUnits`, `$typeIcons`):
  - `agua` → `m³` · `gas` → `m³` · `electricidad` → `kWh` · `temperatura` → `°C` · `presion` → `bar` · `caudal` → `L/min` · `luz` → `lux` · `personalizado` → (sin unidad).
- Soporta **herencia** (`parent_template_id`): una plantilla hija hereda los campos del padre (`getFields()`).
- Solo **Premium** puede crear plantillas personalizadas (`canCreateCustomTemplates()`).

### Nivel 2 — Grupo de Sensores (`SensorGroup`)
- Define **DÓNDE/CÓMO** se agrupa. Modelo: `app/Models/SensorGroup.php`.
- `fillable`: `name`, `description`, `user_id` (dueño), `template_id` (FK a Plantilla), `periodo_medicion` (int, días), `dias_vencimiento` (int), `billing_settings` (cast `array`).
- Un grupo SIN plantilla no puede recibir mediciones (validación en `MeasurementViewController::create`).
- Aloja la **Configuración Contable** (ver §3.1).
- `periodo_medicion`: frecuencia en días entre lecturas (default 30). `dias_vencimiento`: tolerancia de atraso (default 5).

### Nivel 3 — Sensor (`Sensor`)
- Define el **MEDIDOR FÍSICO individual** (un lote/departamento/espacio). Modelo: `app/Models/Sensor.php`.
- `fillable`: `name`, `identifier` (serial/ID físico universal — crítico para migraciones), `description`, `coordinates` (array), `group_id`, `ultima_medicion`, `proxima_medicion`, `marcado_para_medicion` (bool), `is_community` (bool), `public_token` (string 32 chars), `metadata` (cast `array`).
- **`identifier`**: la única forma de identificar físicamente el medidor correcto en el campo. Es la clave de cruce para importaciones.
- **`metadata`**: array JSON libre. Almacena "Campos Extra" (ej: nombre de inquilino, email, piso, deuda, `prorratear_comunidad`). Se inyecta dinámicamente desde Excel.
- **`is_community`**: marca el sensor como Área Común (ver §3.1 prorrateo).
- **`public_token`**: token de 32 chars para el Visor Público (generado vía `Str::random(32)`).
- **`marcado_para_medicion`**: flag para el modo de Carga Masiva en ruta.

### Entidades transversales
- **`Measurement`** (`app/Models/Measurement.php`): `sensor_id`, `measured_at` (datetime), `proxima_medicion`, `periodo_medicion`, `data` (cast `array`), `created_by`. El JSON `data` contiene el `valor`/`consumo_m3`, `foto` (ruta o `'Sin Foto'`/`'Expirada'`), `campos_personalizados`, y en móvil `mobile_uuid` (idempotencia), `is_reset`, `reset_type`, lecturas de cierre/nuevo medidor.
- **`Consumption`** / **`ConsumptionCalculation`**: consumo calculado entre dos mediciones consecutivas. Campos: `value`, `cost`, `currency`, `period_start/end`, `days_between`.
- **`User`** (`app/Models/User.php`): roles (Spatie `HasRoles`), `subscription_plan` (`free`/`basico`/`premium`), `additional_sensor_packs` (int), datos de facturación (`email_facturacion`).
- **`WorkspaceCollaborator`**: vínculo colaborador↔workspace. `role` (`inspector`/`admin`), `status`, `is_paused`, `has_restricted_access` (Fase 38 — ruteo asignado), relación N:M con `Sensor` vía tabla `collaborator_sensor`.
- **`AccessToken`**, **`SharedAccess`/`SensorSharedAccess`/`SensorGroupSharedAccess`**, **`PendingInvitation`**, **`Invoice`**, **`Subscription`**, **`UserSetting`**, **`HelpContent`**.

### Roles (Spatie Permission)
- `admin` / `superadmin` (acceso total; SuperAdmin identificado por email protegido).
- `inspector` (operario de campo — solo toma mediciones).
- `consumidor` (plan Free degradado).

---

## 3. FLUJOS OPERATIVOS Y SUS MECÁNICAS

### 3.1 Gestión de Sensores y Grupos

#### Crear un Grupo (`/sensor-groups/create`)
1. **Plantilla → Grupo → Sensor → Mediciones** es el circuito lógico obligatorio.
2. Al crear un grupo se elige: nombre, descripción, plantilla (`template_id`), `periodo_medicion`, `dias_vencimiento`.
3. Un grupo sin plantilla **bloquea** la toma de mediciones.

#### Configuración Contable (`/sensor-groups/{group}/edit`)
- Se habilita con el checkbox **Habilitar Monetización** (`billing_settings.is_enabled = true`).
- Campos del objeto `billing_settings` (array JSON en el grupo):
  - `is_enabled` (bool)
  - `currency` (`ARS` / `USD`)
  - `fixed_charge` (cargo fijo, float)
  - `price_per_unit` (multiplicador de precio por unidad, ej: $1500 por m³)
  - `show_in_public_viewer` (bool — si el costo aparece en el Visor Público QR)
- **Fórmula de costo (backend, `ConsumptionController`):**
  ```text
  cost = fixed_charge + (consumptionValue * price_per_unit)
  ```
  Se calcula solo si `is_enabled` es true. Si `show_in_public_viewer` es false, el visor público omite el costo.

#### Sensores de Área Común (Prorrateo)
- Al crear/editar un sensor se activa **Es Área Común / Aplica a Prorrateo** (`is_community = true`).
- Mecánica de prorrateo (implementada en `ConsumptionController::calculateRange` y replicada en `PublicViewerController::show`):
  1. Para un sensor **privado** (`is_community = false`) de un grupo, se buscan todos los sensores del mismo grupo marcados `is_community = true`.
  2. Se cuentan los sensores privados del grupo (`privateSensorsCount`).
  3. Por cada sensor comunitario, si su metadata `prorratear_comunidad != '0'` (default `'1'`), se calcula su delta (lectura_final − lectura_inicial) en el mismo rango de fechas. Los marcados en "Modo Estadístico" (`'0'`) se ignoran del prorrateo.
  4. `totalCommunityDelta = Σ deltas comunitarios`.
  5. `communityContribution = totalCommunityDelta / privateSensorsCount` (división equitativa entre privados).
  6. `finalBilledTotal = delta_propio + communityContribution`.
  7. `financialCost = fixed_charge + (finalBilledTotal * price_per_unit)`.
- Propósito: automatizar consorcios sin Excels paralelos — el gasto común se inyecta transparentemente a cada boleto privado.

### 3.2 Captura de Mediciones (Tomar Mediciones)

#### Ruta individual (`/mediciones/create/{sensor}`)
- Acceso: **Tomar Mediciones** → seleccionar sensor.
- Validaciones de permisos (propietario del grupo, colaborador activo `inspector`/`admin`, o admin global).
- Validación de plan: `SubscriptionService::canMeasureSensor()` — en planes limitados solo se puede medir en los primeros N sensores (ordenados por `name`).
- El formulario muestra:
  - **Última medición** (`previousMeasurement`) y su valor (`lastValue`).
  - Cálculo de **Días Transcurridos** desde la última lectura: `diasDesdeUltima = ultimaFecha.diffInDays(now())`.
  - Estados visuales: ✅ dentro de período / ℹ️ faltan N días / 🟡 N días fuera de período / 🔴 vencido por N días (compara contra `periodo_medicion` + `dias_vencimiento`).
- **Tasa Diaria (daily_average):** calculada en backend (`ConsumptionController`):
  ```text
  days_between = end.measured_at.diffInDays(start.measured_at)   // valor absoluto
  daily_average = consumptionValue / days_between                // si days_between > 0
  ```
- **Exigencia de fotos:** la foto funciona como "seguro anti-reclamos" y auditoría. Se guarda en `data.foto` (ruta `/measurements/fotos/...` o `'Sin Foto'`). El toggle de foto está activado por defecto.

#### Ruta masiva en ruta (`/mediciones/bulk-create/{sensor}`)
- Los sensores se marcan con `marcado_para_medicion = true`.
- `bulkCreateRedirect()` lleva al primer sensor marcado (ordenado por `id` asc).
- El flujo avanza sensor por sensor (`nextSensor`), mostrando posición actual/total. Cada registro individual se guarda y se desmarca al completar.

### 3.3 Ingeniería Inversa (Cambio de Medidor)

Disponible en el formulario **Tomar Mediciones** marcando el checkbox **¿Se reemplazó este medidor físico por uno nuevo?** (`data[is_reset] = true`). Aparecen dos opciones:

#### Opción 1 — Reseteo Simple (`data[reset_type] = "simple"`)
- Ignora matemáticamente el consumo del período transitorio.
- Las lecturas futuras arrancan basadas en la nueva lectura del medidor.
- Recomendado si se cobra importe fijo.

#### Opción 2 — Cierre Exacto (`data[reset_type] = "exact"`)
- Pide dos valores:
  - **Lectura final (Medidor viejo)** — último número visto antes de desconectar el aparato viejo.
  - **Lectura inicial (Medidor nuevo)** — número inicial del aparato nuevo al instalarlo.
- Combina el consumo no facturado del medidor viejo + el consumo inicial del nuevo para generar un consumo total exacto **sin perder dinero** ni alterar gráficos históricos.

#### Mecánica matemática del "Muro Cortafuegos" (anti Consumo Negativo)
- El servidor **NUNCA** resta la lectura nueva del inspector contra la vieja que se rompió (ej: nunca `15 − 8500`).
- El administrador debe precargar **anticipadamente** una medición de Reseteo (generalmente en `0`) el día del reemplazo de hardware desde la Web.
- Ese registro de reseteo actúa como **Muro Cortafuegos**: el algoritmo divide la línea de tiempo en dos períodos:
  1. Período viejo: cierra la etapa cobrando el valor exacto adeudado de los 8500 (hasta la lectura final del medidor viejo).
  2. Período nuevo: arranca limpio cruzando contra el cero (`15 − 0 = 15`).
- El historial queda intacto; no se regala el consumo mensual.
- Validación anti-negativo en backend (`MeasurementController` y `MeasurementService::calculateErrorStats`): si `consumption < 0` se marca `error_type = 'negative_consumption'`; si la fecha es anterior a la previa, `inconsistent_date`.

#### Coordinación con la App Móvil (crítico)
- La app móvil bloquea el guardado si el operario ingresa un valor **MENOR** a la última medición conocida (validación estricta en Flutter, `measurement_screen.dart`).
- Por eso el admin debe precargar el reseteo en `0` el día del cambio: cuando el inspector sincronice, bajará ese `0` como "última lectura". Al medir `15` en campo, la app valida `15 > 0` y aprueba.
- Pasos (admin): (1) Web → registro manual en `0` con Reseteo de Medidor. (2) Enviar ruta al inspector al día siguiente. (3) El inspector mide normalmente y la app aprueba el dato.

### 3.4 App Móvil (Operarios) — Blindaje Espacial y Offline

Stack: Flutter (`mobile-app/`). Backend: `app/Http/Controllers/Api/MobileOfflineController.php`.

#### Blindaje espacial (Workspaces)
- El Workspace aísla la información totalmente por privacidad. Un inspector puede trabajar para 10 empresas con la misma cuenta; cada base de sensores/cobros queda blindada.
- El colaborador cambia de vista de datos desde la esquina superior derecha (`session('active_workspace')`).
- El plan que aplica al colaborador es el del **propietario del workspace** (`SubscriptionService::detectOwnerPlan()`), no el suyo.

#### Invitación y token con alcance (`POST /api/mobile/v1/invite`)
- Genera un **Token Sanctum** con `abilities` embebidas:
  - `mobile:read`
  - `sensor-limit:N` (límite de sensores descargables)
  - `sensor-ids:1,2,3` (IDs específicos)
  - `group-id:N` (zona geográfica/ruta asignada)
- Se envía por correo como **Deep Link**: `medflowapp://auth/sync?token=...&workspace=...&limit=...&group_id=...`.

#### Descarga del payload (`GET /api/mobile/v1/sensors`)
- "Vomitador de Sensores": descarga sensores para guardarlos en SQLite local.
- Respeta el límite más restrictivo entre token y query. Filtra por `group_id` y `sensor-ids` del token.
- Cada sensor incluye `last_value` (última medición del campo principal) y `main_field_name`, `measurement_type`, `measurement_unit`, `measurement_icon`.

#### Validación de última métrica (en el dispositivo)
- En `measurement_screen.dart::_saveMeasurement()`:
  ```dart
  if (val < lastVal) {
    // SnackBar: "Error: La medición actual (val) no puede ser MENOR a la anterior (lastVal)."
    return;
  }
  ```
- Por eso el reseteo admin debe precargarse en `0` antes del cambio de hardware.

#### Offline sync con idempotencia (`POST /api/mobile/v1/sync`)
- Cada medición lleva un **`mobile_uuid`** (UUID v4) crítico para túneles offline.
- Persistencia local en SQLite (`DatabaseHelper().saveOfflineMeasurement`).
- Al sincronizar, el backend comprueba idempotencia: si ya existe una `Measurement` con el mismo `sensor_id` + `data->mobile_uuid`, se ignora silenciosamente (cuenta como success para vaciar la app).
- Las fotos viajan como `photo_base64` → se guardan en `public/measurements/fotos/`.
- **Caducidad instantánea post-sincronización:** tras migrar, el token del inspector se elimina (`currentAccessToken()->delete()`).

#### Interfaz de Inspector
- Rol `inspector` ve menú delimitado automáticamente a **Tomar Mediciones**. No puede alterar sensores ni ver info contable.

### 3.5 Importación y Migraciones Masivas (Excel/CSV)

#### Filosofía de Empate (Mapeo)
- MedFlow NO exige destruir el orden de columnas viejas. El importador inteligente permite cargar `.xlsx`/`.csv` y **empatar/mapear** columnas antiguas con los campos requeridos.
- Rutas: **Sensores > Importar Sensores** y **Mediciones > Importar Mediciones Masivas**.

#### Migración de Sensores Físicos (Base) — `BulkSensorImportController`
- Columnas madre obligatorias: **Identificador Único Universal (ID Físico/Serial)** y **Nombre/Dirección** del lote.
- Columnas adicionales (ej: inquilino, deuda, piso) se convierten automáticamente en **Metadatos (Campos Extra)** inyectados en `sensor.metadata`.
- Campos predefinidos disponibles: `fecha_instalacion` (date), `serial` (text), `fecha_fabricacion` (date), `calibracion` (date), etc.
- Mapeo validado: `field_mapping` debe incluir `name` e `identifier`.
- Detección de duplicados por `identifier`: si existe, actualiza (merge de metadata); si no, crea.
- **Telemetría/Prefacturación de cuota (ver §4):** si las filas exceden el cupo restante, retorna `quota_exceeded` con `upsell_data` (packs necesarios + costo estimado).

#### Migración del Histórico de Consumos — `BulkMeasurementImportController`
- Requiere **tres** columnas: `sensor` (identificador), `valor` (numérico), `fecha`.
- Método de identificación seleccionable: `name`, `identifier`, o `metadata_<campo>`.
- Orden operativo: **DESPUÉS** de importar los sensores primero. El importador rastrea identificadores coincidentes y anida el histórico.

#### Conflictos de formato de fecha (¡regla vital!)
- El error más grave: Excel transforma fechas en "números de serie" ocultamente.
- Antes de subir: la columna de fechas debe setearse explícitamente en **formato Texto (AÑO-MES-DÍA)**.
- Parser backend (`BulkMeasurementImportController`):
  - `^\d{4}-\d{2}-\d{2}$` → `Carbon::createFromFormat('Y-m-d', ...)` (formato ideal `YYYY-MM-DD`)
  - `^\d{2}/\d{2}/\d{4}$` → `Carbon::createFromFormat('d/m/Y', ...)` (`DD/MM/YYYY`)
  - Otro → `Carbon::parse(...)` (fallback)
  - Se fuerza `setTime(0, 0, 0)`.
  - Fecha inválida → error de fila: *"Usa formato YYYY-MM-DD o DD/MM/YYYY"*.
- Duplicados exactos (misma fecha + mismo valor) se omiten o sobrescriben según flag `overwriteDuplicates`.

### 3.6 Descarga y Expiración de Backups (Fotos)

#### Política de retención de fotos
- **Las evidencias fotográficas caducan automáticamente a los 365 días (1 año)** de subidas — comando `app:clean-expired-photos` (`app/Console/Commands/CleanExpiredPhotos.php`).
- El sistema avisa por email **una semana antes** (entre 358 y 364 días) agrupando por usuario (`PhotoExpirationWarning`).
- **NUNCA se borran consumos ni estadísticas** (m³, gráficas); solo la imagen física (`@unlink`) para liberar disco. La medición queda con `data.foto = 'Expirada'`.

#### Backup histórico estático (.zip)
- Ruta: **Backups/Respaldos** (`/backup`, `BackupController`).
- Flujo:
  1. Seleccionar grupo (`/sensor-groups`).
  2. `fetchPhotoUrls()` devuelve las URLs de fotos válidas (`foto != 'Sin Foto'` y `!= 'Expirada'`, archivo existente en disco).
  3. El **navegador del cliente** empaqueta todo en `.zip` (motor "Anti-Colapso"), descargando a la PC sin sobrecargar la red del servidor.
- Nombrado de exportación: `{sensor_slug}_{fecha}_v{medicion_id}.jpg`.

### 3.7 Transparencia y Visores Públicos

#### Tokens de Acceso QR (sin contraseña de inquilino)
- Cada sensor puede tener `public_token` (32 chars, `Str::random(32)`).
- Ruta pública: `/visor/{token}` → `PublicViewerController::show` (sin auth).
- El visor muestra: gráfico de la serie temporal, `totalDelta`, `dailyAverage`, `daysBetween`, conteo de anomalías, prorrateo comunitario y (si `show_in_public_viewer`) el costo financiero.
- Detección de anomalías (umbral 50%, estancamiento 15 días): estancamiento si `deltaRaw == 0 && daysDiff > 15`; variación inusual si `|rateChangePercentage| > 50%` respecto a la tasa diaria previa.
- Exportación de enlaces: `exportLinks()` genera CSV con BOM UTF-8 (`ID Interno`, `Medidor`, `Enlace Único de Acceso (QR)`).

#### Campañas de Correo Público automáticas (`BulkNotificationController`)
- Exclusivas de **Premium** (`getPlanKey() !== 'premium'` → 403).
- El admin elige un grupo y un **campo de email dinámico** (un campo de `metadata` del sensor, ej: `email_inquilino`).
- `dispatchCampaign()` encola `SendPublicVisorEmailJob` por sensor con email válido (asíncrono, escalonado).
- En entorno `local` (dev trap): solo envía 2 correos de prueba a una dirección fija y saltea el resto para proteger la base.
- Permisos estrictos: el admin debe ser dueño del grupo o tener `sharedAccess` rol `admin`.

---

## 4. MODELO DE FACTURACIÓN (BILLING ARCHITECTURE)

### 4.1 Planes y límites

Implementado en `app/Services/Subscription/Plans/` (estrategia `PlanInterface` + `PlanFactory`).

| Plan        | Key        | Sensores (max)               | Grupos | Colaboradores | Plantillas custom | Export | Analytics | Precio (ARS)* |
|-------------|------------|------------------------------|--------|---------------|-------------------|--------|-----------|---------------|
| Gratuito    | `free`     | 2                            | 1      | 0             | ❌                 | ❌      | ❌         | 0             |
| Básico       | `basico`   | 10                           | 2      | 0             | ❌                 | ❌      | ❌         | 10.000        |
| Premium      | `premium`  | 20 + (packs × 10)            | ∞      | ∞             | ✅                 | ✅      | ✅         | 25.000        |

\*Precios leídos de `storage/app/pricing.json` (editable por SuperAdmin). Fallback: `basico=10000`, `premium=25000`, `pack=10000`.

### 4.2 Resolución del plan (`PlanFactory::makeFromUser`)
1. Si tiene **suscripción activa** (`Subscription` con `status='active'` y `expires_at` futuro/null) → usa el plan de esa suscripción.
2. Si `subscription_plan` en BD ∈ {`free`,`basico`,`premium`} → usa ese (y si era `basico`/`premium` sin suscripción activa, se detecta como **downgrade**, guardando `previous_plan` en sesión).
3. Si el usuario tiene rol `admin` → **Premium**.
4. Default → **Free**.

### 4.3 Telemetría que bloquea sensores (`SubscriptionService`)
- `canCreateSensor()`: `currentCount < plan.getMaxSensors()`.
- `getCurrentSensorCount()`: cuenta `Sensor` donde el grupo pertenece al usuario (`whereHas('group', user_id = ...)`).
- `canMeasureSensor($sensor)`: en planes limitados, solo los primeros N sensores (ordenados por `name`) son medibles; los demás se bloquean con 403.
- `ensureCanCreateSensor()` / `ensureCanCreateGroup()` lanzan `LimitExceededException` con contexto del límite.
- **Middleware `CheckSubscriptionLimits`**: valida `create_sensor`, `create_group`, `add_collaborator`, `create_template`, `export_data`, `view_analytics` (con jerarquía `free < basico < premium`).
- **Middleware `CheckSubscriptionGate`**: valida permisos por feature (`SubscriptionGate::allows($permission)`).
- Colaboradores: heredan el plan del **propietario del workspace** (`detectOwnerPlan` vía header `X-Workspace-Id`, input `workspace_id`, o `session('active_workspace')`).

### 4.4 Packs Extras de a 10 (Pre-facturación dinámica del Plan Premium)

#### Cálculo matemático del tope (`PremiumPlan::getMaxSensors`)
```text
base = 20
extraPacks = user.additional_sensor_packs ?? 0
maxSensors = base + (extraPacks * 10)
```
`additional_sensor_packs` es un entero persistente en `users`. Cada pack suma **10** sensores.

#### Pre-facturación en importación masiva (`BulkSensorImportController`)
Al analizar el archivo, si las filas exceden el cupo restante:
```text
missingSensors = totalRows - limitStatus.remaining
neededPacks = ceil(missingSensors / 10)
estimated_cost = neededPacks * 10000   // ARS por pack
```
Respuesta `quota_exceeded` (403) con `upsell_data`: `missing_sensors`, `needed_packs`, `estimated_cost`, `formatted_cost`.

#### Compra de packs mid-cycle (`SubscriptionPaymentController::buyExtraPacks`)
- Validación: `packs` entero `1..100`.
- En `local`: bypass directo (`additional_sensor_packs += packs`).
- En producción: crea preferencia **Mercado Pago** con ítem `"Pack Extra de Sensores x{packs*10} - MedFlow"`, `unit_price = packs * 10000` ARS, `currency_id = ARS`.
- `external_reference = "{user_id}_packs_{timestamp}"`.
- Callback `success_packs`: valida pago y suma `additional_sensor_packs += packs`.

### 4.5 Expiración y downgrade automático (`SubscriptionService::checkAndUpdateExpiredSubscription`)
- Busca suscripciones `active` con `expires_at <= now()`.
- Las marca `expired`, baja el usuario a `subscription_plan = 'free'`, `subscription_type = 'domiciliario'`, sincroniza rol `consumidor`.
- **Pausa todos los colaboradores activos** del workspace (`is_paused = true`) porque Free no permite colaboradores.
- El usuario queda marcado como "downgraded" (`hasBeenDowngraded()`) para mostrar pantalla de re-suscripción.

### 4.6 Administración SuperAdmin (`SuperAdminController`, `SuperAdminMiddleware`)
- Acceso restringido por middleware; el SuperAdmin está protegido por email (no se puede eliminar `scastellanoadmin@gmail.com`).
- **Gestión de tarifas (`savePrices`):** edita `pricing.json` (`basico`, `premium`, `pack`) en ARS.
- **Asignación manual de plan (`updatePlan`):** expira suscripciones previas y crea un certificado manual (`payment_id = 'SUPERADMIN-GIFT-...'`, `expires_at = +30 días`, `amount = 0`).
- **Facturas manuales (`generateInvoice`):** monto, estado (`pendiente`/`pagada`), PDF adjunto opcional (max 5MB), envío por email a `email_facturacion` o `email`. Reenvío, cambio de estado, descarga y anulación.
- **Recibos (`sendReceipt`):** genera PDF mock vía `profile.receipt_pdf` y envía por correo.
- **Mensajes institucionales (`sendCustomMessage`)** a cualquier usuario.

---

## 5. REFERENCIA RÁPIDA DE RUTAS Y MIDDLEWARES

### Rutas web clave (`routes/web.php`)
- `/visor/{token}` → Visor Público (sin auth).
- `/dashboard`, `/sensors`, `/sensors/{sensor}`, `/mediciones`, `/mediciones/create/{sensor}`, `/mediciones/inspector`.
- `/sensor-groups`, `/sensor-groups/{group}/edit`.
- `/bulk-measurements/*` (carga masiva en ruta).
- `/campaigns/bulk`, `/campaigns/bulk/dispatch`, `/campaigns/bulk/export-links/{id}`.
- `/templates`, `/templates/create`.
- `/profile`, `/profile/billing`, `/profile/receipt/{id}`.
- `/collaborations`.
- `/superadmin/*` (protegido por `SuperAdminMiddleware`).
- `/backup` (descarga de evidencias).

### Rutas API móvil (`routes/api.php`, `MobileOfflineController`)
- `GET /api/mobile/v1/sensors` — descarga payload.
- `POST /api/mobile/v1/invite` — invita inspector (token con alcance).
- `POST /api/mobile/v1/sync` — sincronización offline con idempotencia.

### Middlewares (`app/Http/Middleware`)
- `CheckSubscriptionGate` — permiso por feature.
- `CheckSubscriptionLimits` — límites de plan + jerarquía de planes.
- `CheckTokenAccess` — valida `AccessToken` (token en ruta/input).
- `CheckWorkspaceAccess` — valida acceso al workspace + rol requerido.
- `InjectSanctumToken` — inyecta token Sanctum en rutas API.
- `SuperAdminMiddleware` — protege rutas de SuperAdmin.
- `Authenticate`, `RedirectIfAuthenticated`, `VerifyCsrfToken`.

### Migraciones relevantes (`database/migrations/`)
- `create_users_table`, `create_permission_tables` (Spatie), `create_personal_access_tokens_table` (Sanctum).
- `add_public_token_to_sensors_table` (visor QR).
- `add_is_community_to_sensors_table` (prorrateo).
- `restrict_sensors_by_collaborator` (Fase 38 — ruteo asignado).
- `add_additional_sensor_packs_to_users_table` (packs extras).
- `add_tax_fields_to_users_table`, `add_extra_billing_fields_to_users_table`, `add_billing_email_to_users_table`.
- `create_invoices_table`.

> Nota: el esquema principal de tablas (`sensors`, `sensor_groups`, `measurements`, `consumptions`, `subscriptions`, `workspace_collaborators`, etc.) se gestiona con SQL crudo en el repositorio (archivos `*.sql`), complementando las migraciones Laravel.

---

## 6. REGLAS DE RESPUESTA PARA FLOWY (resumen ejecutivo)

- Ante "¿cómo hago X?": responder con el flujo paso a paso, citando botones en **negrita** (ej: **Tomar Mediciones**, **Habilitar Monetización**, **Reseteo de Medidor**).
- Ante cálculos: mostrar la **fórmula exacta** documentada (costo, prorrateo, packs, tasa diaria).
- Ante límites: indicar el plan requerido (Free/Básico/Premium) y el middleware que lo valida.
- Ante "¿existe la función Y?": solo confirmar si está en este documento; si no, declarar no documentada.
- Nunca inventar endpoints, campos, ni tarifas. Las tarifas provienen de `pricing.json` (editables por SuperAdmin).
- Recordar siempre el blindaje del Workspace, la idempotencia por `mobile_uuid` y el reseteo admin-previo para cambios de hardware.
