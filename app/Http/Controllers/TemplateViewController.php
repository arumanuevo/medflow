<?php
namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateViewController extends Controller
{
    /**
     * Mostrar el listado de plantillas.
     */
    public function index()
    {
        return view('templates.index');
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