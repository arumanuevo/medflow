<?php
// k:\desarrollo\medflow\app\Http\Controllers\SuperAdminController.php
$file = 'k:\desarrollo\medflow\resources\views\landing.blade.php';
$content = file_get_contents($file);

// Replace registration terms block
$oldTermReg = '<div class="p-3 border rounded mb-3 bg-light" style="max-height: 150px; overflow-y: auto; font-size: 0.8rem; color: #6c757d;">
                                    <strong>Acuerdo de Exención de Responsabilidad y Privacidad (SaaS)</strong><br><br>
                                    MedFlow se proporciona como herramienta (SaaS). Usted es responsable de sus datos e inspectores.<br><br>
                                    No recopilamos ni comercializamos datos, fotos o métricas finales. Al registrarse, exime a los desarrolladores formales por daños derivados del mal uso del servicio por parte de inspectores invitados. Usted asume responsabilidad por las actividades del sistema que provee a sus inspectores.
                                </div>';

$newTermBox = '<div class="p-3 border rounded mb-3 bg-light" style="max-height: 200px; overflow-y: auto; font-size: 0.75rem; color: #6c757d; text-align: justify; border: 1px solid #dee2e6;">
                                    <strong class="text-dark">Acuerdo de Exención de Responsabilidad Técnica y Legal (SaaS)</strong><br><br>
                                    Al utilizar la plataforma "MedFlow", usted reconoce y acepta explícitamente que el software se proporciona bajo la modalidad "Tal cual" (AS IS) y "Según disponibilidad" para funcionar exclusivamente como una herramienta informática administrativa.<br><br>
                                    <strong class="text-secondary">1. Responsabilidad sobre Datos y Terceros:</strong> Los administradores y usuarios son los únicos responsables absolutos de los datos numéricos ingresados y de la legitimidad de las fotografías. Los desarrolladores y creadores de MedFlow se deslindan completamente de cualquier tipo de responsabilidad legal, civil o penal frente a disputas comerciales con inquilinos, consorcios o terceros a la hora de certificar consumos, cobros, o reclamos derivados de la información del sistema.<br><br>
                                    <strong class="text-secondary">2. Fallas Técnicas y Bugs:</strong> Los creadores quedan eximidos de toda responsabilidad ante daños económicos, lucro cesante, alteración de facturaciones o pérdida de datos causados por anomalías del sistema (bugs), fallas en el motor de prorrateo, caídas del servidor (downtime), o discontinuidad técnica del servicio.<br><br>
                                    <strong class="text-secondary">3. Acciones de Operarios:</strong> Usted asume total responsabilidad legal por las actividades de los "Inspectores" / operarios de calle alojados bajo su licencia, eximiendo a MedFlow de cualquier práctica fraudulenta ejecutada con el aplicativo.
                                </div>';

$content = str_replace($oldTermReg, $newTermBox, $content);

// Replace footer terms block
$oldFooterTerm = '<div class="bg-light p-4 rounded-3 border text-start"
                        style="font-size: 0.85rem; color: #4a5568 !important;">
                        <h5 class="mb-3 text-dark"><i class="bi bi-shield-check text-primary"></i> Privacidad y
                            Condiciones Legales Estatales</h5>
                        <p class="mb-3" style="line-height: 1.6;">
                            <strong>Exención de Responsabilidad y Privacidad (SaaS):</strong> El software "MedFlow" y
                            sus desarrolladores proporcionan la plataforma exclusivamente como una herramienta
                            informática (SaaS) destinada a facilitar la recolección y logística de medidores. Los
                            administradores, usuarios y empresas registradas son los únicos dueños, creadores y
                            responsables absolutos de los datos cargados en el sistema.
                        </p>
                        <p class="mb-0" style="line-height: 1.6;">
                            <strong>Protección Criptográfica de la Identidad:</strong> MedFlow <b>no recopila, no
                                alquila, ni comercia</b> con direcciones de correo electrónico, fotos subidas, métricas
                            ni metadatos de los clientes finales bajo ninguna circunstancia. El software no efectúa
                            ningún uso no autorizado ni venta de información a terceros. Al utilizar esta herramienta o
                            registrarse, el usuario exime formalmente a los desarrolladores del software por los daños
                            directos e indirectos causados por el mal uso de la plataforma por parte de los operadores
                            inspectores corporativos.
                        </p>
                    </div>';

$newFooterTerm = '<div class="bg-white p-3 rounded-3 border text-start shadow-sm mb-4">
                        <h5 class="mb-3 text-dark" style="font-size: 1.1rem;"><i class="bi bi-shield-check text-primary"></i> Legal, Privacidad y Términos de Servicio</h5>
                        
                        <div class="p-3 bg-light rounded" style="max-height: 250px; overflow-y: auto; font-size: 0.75rem; color: #64748b; text-align: justify; border: 1px solid #e2e8f0;">
                            <strong class="text-dark d-block mb-1">Acuerdo de Exención de Responsabilidad Técnica y Legal (SaaS)</strong>
                            Al utilizar la plataforma "MedFlow", usted reconoce y acepta explícitamente que el software se proporciona bajo la modalidad "Tal cual" (AS IS) y "Según disponibilidad" para funcionar exclusivamente como una herramienta informática en la nube.<br><br>
                            
                            <strong class="text-secondary">1. Datos y Disputas con Terceros:</strong> 
                            Los administradores y empresas registradas son los únicos dueños y responsables absolutos de los datos ingresados y la veracidad de las fotografías documentadas. Los desarrolladores formales e integrantes del equipo técnico de MedFlow se deslindan irrevocablemente de cualquier tipo de responsabilidad legal, civil o penal frente a disputas judiciales con inquilinos, consorcios, entes de control, o terceros vinculadas a la certificación de consumos, montos de facturación extraídos, cortes de suministro o reclamos derivados de reportes PDF descargados del sistema.<br><br>
                            
                            <strong class="text-secondary">2. Prevención contra Fallas Informáticas (Bugs):</strong> 
                            Siendo un software sujeto a conectividad de red y procesamiento lógico continuo, los creadores de MedFlow quedan eximidos de forma absoluta de toda obligación de resarcimiento económico ante lucro cesante, pérdidas monetarias por liquidaciones mal calculadas, alteración de la base de datos (corrupción), brechas de seguridad imprevistas de infraestructura (hosting), alteraciones producidas por bugs lógicos en los algoritmos matemáticos, o caídas temporales del servidor (downtime). El usuario es responsable de someter a revisión manual periódica sus liquidaciones.<br><br>
                            
                            <strong class="text-secondary">3. Mala Praxis Corporativa:</strong> 
                            Usted asume la total responsabilidad legal, civil y tributaria por las actividades de todos los colaboradores, huéspedes técnicos y operarios que usted designe dentro de su entorno virtual, liberando de toda implicación a la marca de los desarrolladores frente al uso ilícito, extorsivo o negligente de la herramienta.<br><br>
                            
                            <strong class="text-secondary">4. Privacidad Sagrada de la Identidad:</strong> 
                            La Plataforma se diseñó sin mecanismos comerciales de extracción. MedFlow no alquila, no subasta, y no rastrea información cruzada, métricas fotográficas, parámetros contables ni direcciones de las entidades gestionadas. Los metadatos de sus clientes le pertenecen estrictamente a su entidad operativa.
                        </div>
                    </div>';

$content = str_replace($oldFooterTerm, $newFooterTerm, $content);
file_put_contents($file, $content);

echo "Landing page terms updated!\n";
