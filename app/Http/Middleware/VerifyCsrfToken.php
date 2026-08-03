<?php
// app/Http/Middleware/VerifyCsrfToken.php
namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*', // Excluir todas las rutas de la API
    ];

    public function handle($request, Closure $next)
{
    // No verificar CSRF para rutas GET
    if ($request->isMethod('GET')) {
        return $next($request);
    }
    return parent::handle($request, $next);
}
}