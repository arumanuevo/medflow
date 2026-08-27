<?php

$c = file_get_contents('app/Http/Controllers/LandingController.php');
$method = <<<EOF
    public function contact(Request \$request)
    {
        \$request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'message' => 'required|string|max:1000'
        ]);

        \$name = \$request->input('name');
        \$email = \$request->input('email');
        \$text = \$request->input('message');

        \$body = "Nuevo mensaje de asesoramiento desde la Landing:\n\n"
              . "Nombre: \$name\n"
              . "Email: \$email\n\n"
              . "Consulta:\n\$text";

        \Illuminate\Support\Facades\Mail::raw(\$body, function (\$message) use (\$email, \$name) {
            \$message->to('scastellano10@gmail.com')
                    ->subject('Nuevo Contacto Comercial - MedFlow App')
                    ->replyTo(\$email, \$name);
        });

        return response()->json(['success' => true]);
    }
}
EOF;
$c = preg_replace('/\}\s*$/', $method, $c);
file_put_contents('app/Http/Controllers/LandingController.php', $c);

$r = file_get_contents('routes/web.php');
if (strpos($r, "Route::post('/contacto'") === false) {
    $r = str_replace("Route::post('/registro', [LandingController::class, 'register'])->name('landing.register');", "Route::post('/registro', [LandingController::class, 'register'])->name('landing.register');\nRoute::post('/contacto', [LandingController::class, 'contact'])->name('landing.contact');", $r);
    file_put_contents('routes/web.php', $r);
}

// Modify frontend form
$blade = file_get_contents('resources/views/landing.blade.php');
$formFind = '<form id="contactForm" onsubmit="event.preventDefault(); alert(\'Gracias por contactarnos. Nuestro equipo se comunicará contigo mediante el correo scastellano10@gmail.com a la brevedad.\'); this.reset();">';
$formReplace = '<form id="contactForm">';
$blade = str_replace($formFind, $formReplace, $blade);

// Replace inputs with name=""
$blade = str_replace('placeholder="Ej. Juan P&eacute;rez" required>', 'name="name" id="contactName" placeholder="Ej. Juan P&eacute;rez" required>', $blade);
$blade = str_replace('placeholder="Ej. juan@empresa.com" required>', 'name="email" id="contactEmail" placeholder="Ej. juan@empresa.com" required>', $blade);
$blade = str_replace('placeholder="Desarrollo a medida, cotizaci&oacute;n de sensores..." required></textarea>', 'name="message" id="contactMessage" placeholder="Desarrollo a medida, cotizaci&oacute;n de sensores..." required></textarea>', $blade);

$script = <<<EOF
        // Contact Form AJAX
        $('#contactForm').on('submit', function(e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            var originalText = btn.html();
            btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i> Enviando...');
            
            $.ajax({
                url: '/contacto',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: $('#contactName').val(),
                    email: $('#contactEmail').val(),
                    message: $('#contactMessage').val()
                },
                success: function(res) {
                    btn.prop('disabled', false).html(originalText);
                    showAlert('Gracias por contactarnos. Nos comunicaremos a la brevedad.', 'success');
                    $('#contactForm')[0].reset();
                },
                error: function(err) {
                    btn.prop('disabled', false).html(originalText);
                    showAlert('Hubo un error al enviar el mensaje. Inténtalo más tarde.', 'danger');
                }
            });
        });
    });
</script>
EOF;

$blade = str_replace("});\n    </script>", $script, $blade);
file_put_contents('resources/views/landing.blade.php', $blade);

echo "Fully Linked SMTP Mail and Removed Boton!\n";
