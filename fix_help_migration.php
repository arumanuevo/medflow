<?php
$file = 'k:\desarrollo\medflow\resources\views\help\index.blade.php';
$content = file_get_contents($file);

$newModule = <<<EOT
            <!-- Módulo 7: Migración e Importación Masiva (Excel) -->
            <div class="col help-card">
                <div class="card h-100 border-0 shadow-sm rounded-4" style="border-left: 4px solid #6f42c1 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-purple-subtle rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 45px; height: 45px; background: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                                <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                            </div>
                            <h5 class="fw-bold mb-0">Importación y Migraciones</h5>
                        </div>
                        <ul class="list-unstyled text-muted mb-0">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="La Filososfía de Empate (Migrando a MedFlow)"
                                    data-content="Al transicionar desde sistemas arcaicos o archivos Excel aislados de tu empresa, el paso más delicado es la <strong>normalización</strong>. MedFlow no exige que 'destruyas' tu orden de columnas viejas; su sistema de importación inteligente te permitirá cargar tu <code>.xlsx</code> o <code>.csv</code> y solicitará que <strong>empatrues o enlaces (mapees)</strong> tus columnas antiguas con los campos requeridos por MedFlow (Ej: 'Nro de Medidor' se unirá a 'Identificador Físico')."
                                    data-steps="Disponible dentro del menú <strong>Sensores > Importar Sensores</strong>.">
                                    <i class="bi bi-diagram-3-fill me-1" style="color: #6f42c1;"></i> ¿Cómo empato mi sistema viejo?</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Migración de Sensores Físicos (Base)"
                                    data-content="Al subir tu archivo Excel de Sensores, asegúrate de contar al menos con dos columnas madre: <strong>Un Identificador Único Universal (ID Físico/Serial del aparato)</strong> y <strong>El Nombre o Dirección</strong> del lote. Si tu excel antiguo tenía columnas adicionales como 'Nombre de Inquilino', 'Deuda Previa' o 'Piso', ¡no las descartes! MedFlow las convertirá automáticamente en <strong>Metadatos (Campos Extra)</strong> que quedarán inyectados permanentemente en el ecosistema, dándole contexto al inspector en campo."
                                    data-steps="1. Limpia tu Excel de celdas vacías en la cabecera.<br>2. Súbelo a <strong>Importar Sensores</strong>.<br>3. Mapea la columna serial y decide qué columnas extra guardar como Metadata.">
                                    <i class="bi bi-hdd-network-fill me-1" style="color: #6f42c1;"></i> Construyendo la base de aparatos</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Migración del Histórico de Consumos"
                                    data-content="Si deseas que los gráficos de MedFlow reaccionen al instante mostrando toda tu historia de consumos del último año, necesitas inyectar un histórico. Para que el motor asocie cada lectura pasada a su respectivo sensor, nuestro importador buscará <strong>únicamente el Identificador Físico</strong>. Tu Excel de Historial debe tener tres columnas excluyentes: <code>1) Identificador del Medidor</code>, <code>2) Fecha de la Medición (ideal en formato Año-Mes-Día YYYY-MM-DD)</code> y <code>3) El Valor numérico capturado</code>."
                                    data-steps="Deberás realizar esto <strong>DESPUÉS</strong> de haber importado los sensores primero. MedFlow rastreará todos los Identificadores coincidiendo y anidará tus historias fotográficas o datos numéricos sin colapsar.">
                                    <i class="bi bi-clock-history me-1" style="color: #6f42c1;"></i> Inyectar mediciones del pasado</a>
                            </li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted help-item"
                                    data-bs-toggle="modal" data-bs-target="#helpModal"
                                    data-title="Resolución de Conflictos de Fechas (Excel)"
                                    data-content="El error de migración más grave sucede cuando Excel transforma ocultamente tus fechas en 'números de serie'. <strong>Antes de exportar y subir tu Excel</strong>: Selecciona la columna de fechas de tus mediciones históricas, presiona click derecho -> Formato de Celdas, y asegúrate que esté seteada explícitamente en formato de Texto (AÑO-MES-DIA) para evitar que hojas de cálculo antiguas rompan la línea de tiempo temporal (Time-series) del Analizador de Tasa Diaria del servidor."
                                    data-steps="Aplica esta revisión en tu Excel nativo previo a entrar en <strong>Importar Mediciones Masivas</strong>.">
                                    <i class="bi bi-exclamation-triangle-fill me-1" style="color: #6f42c1;"></i> Regla vital contra fallos de tiempos</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
EOT;

// Insert securely before "App Móvil e Inspectores"
$search = '            <!-- Módulo 7: App Móvil e Inspectores -->';
$replace = $newModule . "\n" . '            <!-- Módulo 8: App Móvil e Inspectores -->';
$content = str_replace($search, $replace, $content);

file_put_contents($file, $content);
echo "Knowledge base module injected!\n";
