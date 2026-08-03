<?php
// app/Http/Controllers/Auth/RegisterController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Asignar roles por defecto: consumidor e inspector
        $consumidorRole = Role::firstOrCreate(['name' => 'consumidor', 'guard_name' => 'web']);
        $inspectorRole = Role::firstOrCreate(['name' => 'inspector', 'guard_name' => 'web']);

        $user->assignRole([$consumidorRole, $inspectorRole]);

        return $user;
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
    
        $user = $this->create($request->all());
    
        auth()->login($user);
    
        // Crear token de Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;
        $request->session()->put('sanctum_token', $token);
        
        // ✅ Guardar en cookie para JavaScript
        cookie()->queue('sanctum_token', $token, 60 * 24 * 7);
    
        return redirect($this->redirectPath());
    }
}