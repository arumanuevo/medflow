<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SuperAdminController extends Controller
{
    public function index()
    {
        $users = User::withCount('sensors', 'measurements')->get();
        return view('superadmin.users', compact('users'));
    }

    public function updatePlan(Request $request, User $user)
    {
        $request->validate([
            'subscription_plan' => 'required|in:free,basico,premium,enterprise'
        ]);

        $user->subscription_plan = $request->subscription_plan;

        // Asignar 30 dias si lo pasan a un plan pago (como fallback manual admin)
        if ($request->subscription_plan !== 'free') {
            $user->subscription_expires_at = Carbon::now()->addDays(30);
        } else {
            $user->subscription_expires_at = null;
        }

        $user->save();

        return redirect()->back()->with('success', 'Plan actualizado a ' . strtoupper($request->subscription_plan));
    }

    public function deleteUser(User $user)
    {
        if ($user->email === 'scastellanoadmin@gmail.com') {
            return redirect()->back()->with('error', 'No puedes eliminar al SuperAdmin');
        }
        $user->delete();
        return redirect()->back()->with('success', 'Usuario eliminado para siempre');
    }
}
