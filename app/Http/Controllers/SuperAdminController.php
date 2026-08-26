<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuperAdminMessage;
use App\Mail\SuperAdminReceipt;
use Barryvdh\DomPDF\Facade\Pdf;

class SuperAdminController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('superadmin.users', compact('users'));
    }

    public function updatePlan(Request $request, User $user)
    {
        $request->validate([
            'subscription_plan' => 'required|in:free,basico,premium,enterprise'
        ]);

        $user->subscription_plan = $request->subscription_plan;

        if ($request->subscription_plan !== 'free') {
            $user->subscription_expires_at = Carbon::now()->addDays(30);
        } else {
            $user->subscription_expires_at = null;
        }

        $user->save();

        return redirect()->back()->with('success', 'Plan actualizado a ' . strtoupper($request->subscription_plan));
    }

    public function sendCustomMessage(Request $request, User $user)
    {
        $request->validate([
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string'
        ]);

        try {
            Mail::to($user->email)->send(new SuperAdminMessage($request->message, $request->subject, $user->name));
            return redirect()->back()->with('success', 'Mensaje institucional enviado exitosamente a ' . $user->email);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al enviar email: ' . $e->getMessage());
        }
    }

    public function sendReceipt(Request $request, User $user)
    {
        try {
            // Reutilizar la vista de recibo existente del sistema
            $pdf = Pdf::loadView('profile.receipt_pdf', [
                'user' => $user,
                'plan' => $user->subscription_plan,
                'expires_at' => $user->subscription_expires_at
            ]);

            $pdfContent = $pdf->output();
            $filename = 'Comprobante_MedFlow_' . date('dmY') . '.pdf';

            // Usar el email de facturación si existe, si no usar el email principal
            $emailTo = !empty($user->email_facturacion) ? $user->email_facturacion : $user->email;

            Mail::to($emailTo)->send(new SuperAdminReceipt($user->name, $pdfContent, $filename));

            return redirect()->back()->with('success', 'Comprobante de pago generado y adjuntado exitosamente a ' . $emailTo);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar o enviar comprobante: ' . $e->getMessage());
        }
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
