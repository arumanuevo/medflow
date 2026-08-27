<?php
// app/Http/Controllers/LandingController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing');
    }
    public function contact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'message' => 'required|string|max:1000'
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $text = $request->input('message');

        $body = "Nuevo mensaje de asesoramiento desde la Landing:

"
              . "Nombre: $name
"
              . "Email: $email

"
              . "Consulta:
$text";

        \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($email, $name) {
            $message->to('scastellano10@gmail.com')
                    ->subject('Nuevo Contacto Comercial - MedFlow App')
                    ->replyTo($email, $name);
        });

        return response()->json(['success' => true]);
    }
}