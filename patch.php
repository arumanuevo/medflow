<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');

// 1. Fix double div
$c = preg_replace('/<\/div>\s*<\/div>\s*<!-- Modal para detalles del consumo -->/', "</div>\n    <!-- Modal para detalles del consumo -->", $c);

// 2. Reduce button sizes
$c = str_replace("btn-sm btn-info viewConsumptionBtn", "btn-sm py-0 px-2 viewConsumptionBtn", $c);
$c = str_replace("btn-sm btn-primary ms-1 analyzeSensorBtn", "btn-sm py-0 px-2 ms-1 analyzeSensorBtn", $c);

// 3. Fix days rounding in Advanced Analysis JS
$c = str_replace("$('#resDaysBetween').text(d.days_between);", "$('#resDaysBetween').text(parseFloat(d.days_between).toFixed(0));", $c);
$c = str_replace("parseFloat(d.daily_average).toFixed(4)", "parseFloat(d.daily_average).toFixed(2)", $c);

// 4. Add Share button in modal footer
$footerOld = '<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>';
$footerNew = '<button type="button" class="btn btn-outline-success btn-sm me-auto" id="btnShareAnalysis"><i class="bi bi-share"></i> Compartir Informe</button>' . "\n" . '                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>';
$c = str_replace($footerOld, $footerNew, $c);

// Add the script binding for share button if not present
if (strpos($c, 'btnShareAnalysis') !== false && strpos($c, "$('#btnShareAnalysis').click") === false) {
    $script = <<<JS

        $('#btnShareAnalysis').click(function() {
            const sensorId = $('#analyzeSensorId').val();
            const sensorName = $('#analyzeSensorName').text();
            if (!sensorId) return;

            const email = prompt('Ingrese el correo electrónico para enviar el reporte de ' + sensorName + ':');
            if (email) {
                $.ajax({
                    url: '/api/sensors/' + sensorId + '/share',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    data: { email: email },
                    success: function (res) {
                        if(res.success) {
                            alert('Informe enviado correctamente a ' + email);
                        } else {
                            alert('Error: ' + res.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error de conexión al enviar informe.');
                    }
                });
            }
        });
JS;
    $c = str_replace('});', '});' . $script, $c);
}

file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo 'Patched consumptions view';
