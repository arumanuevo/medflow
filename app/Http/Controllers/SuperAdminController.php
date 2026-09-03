<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuperAdminMessage;
use App\Mail\SuperAdminReceipt;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;

class SuperAdminController extends Controller
{
    public function invoicesIndex()
    {
        $invoices = Invoice::with('user')->orderBy('created_at', 'desc')->get();
        return view('superadmin.invoices', compact('invoices'));
    }

    public function generateInvoice(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pendiente,pagada',
            'description' => 'required|string|max:255',
            'invoice_file' => 'nullable|mimes:pdf|max:5120' // max 5MB
        ]);

        $filePath = null;
        $dbPath = null;
        if ($request->hasFile('invoice_file')) {
            $file = $request->file('invoice_file');
            $filename = 'factura_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('invoices', $filename, 'public');
            $filePath = $path; // Guardamos el path real en storage (ej: invoices/factura.pdf)
            $dbPath = 'storage/' . $path; // Para la DB guardamos con storage/ prefijo para la web
        }

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'status' => $request->status,
            'description' => $request->description,
            'file_path' => $dbPath // Guardamos con prefijo storage
        ]);

        if ($request->send_email) {
            $emailTo = !empty($user->email_facturacion) ? $user->email_facturacion : $user->email;

            // Si el admin no adjuntó PDF, mandamos el genérico del sistema con el mock
            if (!$filePath) {
                // Generar mock
                $subscription = new \stdClass();
                $subscription->id = $invoice->id;
                $subscription->created_at = \Carbon\Carbon::now();
                $subscription->plan = $user->subscription_plan ?: 'premium';
                $subscription->payment_id = 'MANUAL-INV-' . $invoice->id;
                $subscription->amount = $invoice->amount;
                $subscription->currency = 'ARS';

                $pdf = Pdf::loadView('profile.receipt_pdf', [
                    'user' => $user,
                    'subscription' => $subscription
                ]);
                $pdfContent = $pdf->output();
                $generatedFilename = 'Factura_MedFlow_' . $invoice->id . '.pdf';
            } else {
                // Leer el archivo subido real saltando el symlink fallido
                $pdfContent = Storage::disk('public')->get($filePath);
                $generatedFilename = 'Factura_MedFlow_' . $invoice->id . '.pdf';
            }

            Mail::to($emailTo)->send(new SuperAdminReceipt($user->name, $pdfContent, $generatedFilename));
        }

        return redirect()->back()->with('success', 'Factura manual generada ' . ($request->send_email ? 'y enviada' : 'exitosamente') . '.');
    }

    public function changeInvoiceStatus(Request $request, Invoice $invoice)
    {
        $request->validate(['status' => 'required|in:pendiente,pagada,anulada']);
        $invoice->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'Estado de la factura actualizado.');
    }

    public function resendInvoice(Request $request, Invoice $invoice)
    {
        $user = $invoice->user;
        if (!$user)
            return redirect()->back()->with('error', 'Usuario eliminado no puede recibir emails.');

        $emailTo = !empty($user->email_facturacion) ? $user->email_facturacion : $user->email;

        if ($invoice->file_path) {
            // Eliminar el prefijo "storage/" para que get() localice internamente
            $realPath = str_replace('storage/', '', $invoice->file_path);
            $pdfContent = Storage::disk('public')->get($realPath);
        } else {
            $subscription = new \stdClass();
            $subscription->id = $invoice->id;
            $subscription->created_at = $invoice->created_at;
            $subscription->plan = $user->subscription_plan ?: 'premium';
            $subscription->payment_id = 'MANUAL-INV-' . $invoice->id;
            $subscription->amount = $invoice->amount;
            $subscription->currency = 'ARS';

            $pdf = Pdf::loadView('profile.receipt_pdf', [
                'user' => $user,
                'subscription' => $subscription
            ]);
            $pdfContent = $pdf->output();
        }

        Mail::to($emailTo)->send(new SuperAdminReceipt($user->name, $pdfContent, 'Factura_Reenviada_' . $invoice->id . '.pdf'));

        return redirect()->back()->with('success', 'Factura re-enviada a ' . $emailTo);
    }

    public function deleteInvoice(Invoice $invoice)
    {
        // $realPath = str_replace('storage/', '', $invoice->file_path);
        // Storage::disk('public')->delete($realPath);
        $invoice->delete();
        return redirect()->back()->with('success', 'Factura eliminada del historial.');
    }

    public function downloadInvoice(Invoice $invoice)
    {
        if ($invoice->file_path) {
            $realPath = str_replace('storage/', '', $invoice->file_path);
            if (Storage::disk('public')->exists($realPath)) {
                return response()->download(
                    Storage::disk('public')->path($realPath),
                    'Factura_MedFlow_' . $invoice->id . '.pdf'
                );
            }
        }
        return redirect()->back()->with('error', 'El archivo no existe físicamente en el servidor.');
    }

    public function index()
    {
        $users = User::all();
        $prices = @json_decode(file_get_contents(storage_path('app/pricing.json')), true) ?: ['basico' => 10000.00, 'premium' => 25000.00];
        return view('superadmin.users', compact('users', 'prices'));
    }

    public function savePrices(Request $request)
    {
        $request->validate([
            'price_basico' => 'required|numeric|min:0',
            'price_premium' => 'required|numeric|min:0',
        ]);
        $prices = [
            'basico' => $request->price_basico,
            'premium' => $request->price_premium
        ];
        file_put_contents(storage_path('app/pricing.json'), json_encode($prices));
        return redirect()->back()->with('success', 'Precios de los planes actualizados correctamente en ARS.');
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
            // Generar un mock de suscripción para el recibo manual
            $subscription = new \stdClass();
            $subscription->id = rand(1000, 9999);
            $subscription->created_at = Carbon::now();
            $subscription->plan = $user->subscription_plan ?: 'premium';
            $subscription->payment_id = 'MANUAL-' . strtoupper(uniqid());
            // Asignar monto realista según el plan (1000 básico / 2000 premium)
            $subscription->amount = $subscription->plan == 'premium' ? 2000 : 1000;
            $subscription->currency = 'ARS';

            // Reutilizar la vista de recibo existente del sistema
            $pdf = Pdf::loadView('profile.receipt_pdf', [
                'user' => $user,
                'subscription' => $subscription
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
