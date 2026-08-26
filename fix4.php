<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');

// Remove every block matching $('#btnShareAnalysis').click(function() { ... });
$c = preg_replace("/\\$\\('#btnShareAnalysis'\\)\\.click\\(function\\s*\\(\\)\\s*\\{.*?\\}\\);/s", '', $c);

// Also remove it if it has extra spaces
$c = preg_replace("/\\$\\('#btnShareAnalysis'\\)\\.click\\(\\s*function\\s*\\([\\s\\S]*?\\)\\s*\\{[\\s\\S]*?\\}\\s*\\);/s", '', $c);

// Add the button to the footer securely.
// Ensure we don't have multiple buttons.
$c = preg_replace('/<button type="button" class="btn btn-outline-success btn-sm me-auto" id="btnShareAnalysis">.*?<\/button>/s', '', $c);

// Find the precise footer for analyzeSensorModal
$target = '<div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>';
$replacement = '<div class="modal-footer p-2">
                <button type="button" class="btn btn-outline-success btn-sm me-auto" id="btnShareAnalysis"><i class="bi bi-share"></i> Compartir Informe</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>';
$c = str_replace($target, $replacement, $c);

// Now reinject exactly ONCE before </script>
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

$c = str_replace('</script>', $script . "\n    </script>", $c);

file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo 'Fix completed';
