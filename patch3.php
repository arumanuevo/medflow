<?php
$c = file_get_contents('app/Http/Controllers/Api/SensorController.php');
$target = 'class SensorController extends Controller';
$replacement = <<<PHP
use App\Mail\IndividualReportMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SensorController extends Controller
PHP;
if (strpos($c, 'IndividualReportMail') === false) {
    $c = str_replace($target, $replacement, $c);
}

// Ensure PHP allows prepending at the end
$newFn = <<<PHP

    public function shareReport(Request \$request, Sensor \$sensor)
    {
        \$request->validate([
            'email' => 'required|email'
        ]);

        if (empty(\$sensor->public_token)) {
            \$sensor->public_token = Str::random(32);
            \$sensor->save();
        }

        \$url = route('public.visor', ['token' => \$sensor->public_token]);
        
        try {
            Mail::to(\$request->email)->send(new IndividualReportMail(\$sensor, \$url));
            return response()->json(['success' => true]);
        } catch (\Exception \$e) {
            Log::error('Error enviando reporte avanzado: ' . \$e->getMessage());
            return response()->json(['success' => false, 'message' => 'Fallo al enviar correo: ' . \$e->getMessage()], 500);
        }
    }
}
PHP;
$c = preg_replace('/}\s*$/', $newFn, $c);
file_put_contents('app/Http/Controllers/Api/SensorController.php', $c);
echo 'Added shareReport to SensorController';
