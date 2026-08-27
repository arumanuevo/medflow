<?php
$c = file_get_contents('resources/views/landing.blade.php');
$find = "showAlert('Gracias por contactarnos. Nos comunicaremos a la brevedad.', 'success');";
$replace = "\n                    $('#contactForm').find('.alert').remove(); // Clear previous alerts\n                    $('#contactForm').prepend('<div class=\"alert alert-success alert-dismissible fade show mb-4\" role=\"alert\"><i class=\"bi bi-check-circle-fill me-2\"></i>Gracias por contactarnos. Nos comunicaremos a la brevedad.<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>');";
$c = str_replace($find, $replace, $c);

$findErr = "showAlert('Hubo un error al enviar el mensaje. Inténtalo más tarde.', 'danger');";
$replaceErr = "\n                    $('#contactForm').find('.alert').remove(); // Clear previous alerts\n                    $('#contactForm').prepend('<div class=\"alert alert-danger alert-dismissible fade show mb-4\" role=\"alert\"><i class=\"bi bi-exclamation-triangle-fill me-2\"></i>Hubo un error al enviar el mensaje. Inténtalo más tarde.<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button></div>');";
$c = str_replace($findErr, $replaceErr, $c);

file_put_contents('resources/views/landing.blade.php', $c);
echo "Adjusted target alerts to be inside the card.\n";
