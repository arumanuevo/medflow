<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');

$badPos = strpos($c, "$('#btnShareAnalysis').click");
if ($badPos !== false) {
    // Slice off everything from the first bad injection onwards
    $c = substr($c, 0, $badPos);

    // Sometimes there might be a hanging "});" because the injection was right after it.
    // Let's strip any trailing whitespace
    $c = rtrim($c);

    // Ensure the last characters make sense for JavaScript
    // The previous injected line was probably right after `});` or similar. Let's just append the new script block securely.
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
    </script>
@endpush
JS;

    $c .= "\n" . $script;
}

file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo "Nuclear patch done\n";
