<?php
namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use App\Services\Subscription\SubscriptionGate;

class TemplateViewController extends Controller
{
    /**
     * Mostrar el listado de plantillas.
     */
    public function index()
    {
        $user = auth()->user();
        
        // ✅ OBTENER PERMISOS
        $gate = new SubscriptionGate($user);
        $permissions = $gate->getAllPermissions();
        
        return view('templates.index', compact('permissions'));
    }

    /**
     * Mostrar el formulario para crear una nueva plantilla.
     */
    public function create()
    {
        $measurementTypes = [
            'electricidad' => 'Electricidad',
            'agua' => 'Agua',
            'gas' => 'Gas',
            'temperatura' => 'Temperatura',
            'presion' => 'Presión',
            'caudal' => 'Caudal',
            'personalizado' => 'Personalizado'
        ];

        return view('templates.create', compact('measurementTypes'));
    }
    
    /**
     * Mostrar el formulario para editar una plantilla.
     */
    public function edit(Template $template)
{
    $measurementTypes = [
        'electricidad' => 'Electricidad',
        'agua' => 'Agua',
        'gas' => 'Gas',
        'temperatura' => 'Temperatura',
        'presion' => 'Presión',
        'caudal' => 'Caudal',
        'personalizado' => 'Personalizado'
    ];

    return view('templates.edit', compact('template', 'measurementTypes'));
}
}