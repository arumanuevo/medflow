@extends('layouts.modern')

@section('title', 'Activar Suscripción - MedFlow')

@push('styles')
<style>
    .payment-card {
        max-width: 500px;
        margin: 2rem auto;
    }
    .plan-badge {
        font-size: 0.8rem;
        padding: 0.3rem 1rem;
        border-radius: 20px;
    }
    .plan-badge.basico {
        background: #e9ecef;
        color: #495057;
    }
    .plan-badge.premium {
        background: #ffd700;
        color: #000;
    }
    #wallet_container {
        min-height: 150px;
        margin-top: 1.5rem;
    }
    .price-display {
        font-size: 2.5rem;
        font-weight: 700;
        color: #0d6efd;
    }
    .price-display small {
        font-size: 1rem;
        color: #6c757d;
    }
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    .loading-overlay.active {
        display: flex;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card payment-card shadow-sm">
                <div class="card-header  text-center">
                    <h4 class="mb-0"><i class="bi bi-credit-card"></i> Activar Suscripción</h4>
                </div>
                <div class="card-body text-center">
                    <h5 class="mb-3">{{ $planData['name'] }}</h5>
                    <p class="text-muted">{{ $planData['description'] }}</p>
                    
                    <div class="price-display">
                        ${{ number_format($planData['price'] / 100, 2) }}
                        <small>ARS</small>
                    </div>
                    
                    <span class="plan-badge {{ $plan }}">
                        {{ ucfirst($plan) }}
                    </span>
                    
                    <hr>
                    
                    {{-- ✅ Botón para crear preferencia --}}
                    <button class="btn btn-primary btn-lg w-100" id="createPreferenceBtn">
                        <i class="bi bi-credit-card"></i> Pagar con Mercado Pago
                    </button>
                    
                    <div id="wallet_container" class="mt-3 d-none"></div>
                    
                    <p class="text-muted small mt-3">
                        <i class="bi bi-shield-lock"></i>
                        Pago seguro a través de Mercado Pago
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div class="loading-overlay" id="loadingOverlay">
    <div class="text-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <p class="mt-2">Creando preferencia de pago...</p>
    </div>
</div>

<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const plan = "{{ $plan }}";
    const createBtn = document.getElementById('createPreferenceBtn');
    const walletContainer = document.getElementById('wallet_container');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const publicKey = "{{ config('mercadopago.public_key') }}";

    // ✅ Función para crear preferencia y renderizar botón
    async function createPreferenceAndPay() {
        // Mostrar loading
        loadingOverlay.classList.add('active');
        createBtn.disabled = true;
        createBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Preparando pago...';

        try {
            const response = await fetch('/api/subscription/create-preference', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                body: JSON.stringify({ plan: plan })
            });

            const data = await response.json();
            console.log('📤 Respuesta del servidor:', data);

            if (!response.ok) {
                throw new Error(data.message || 'Error al crear la preferencia');
            }

            if (data.success && data.data.preference_id) {
                // ✅ Renderizar el botón de Mercado Pago
                renderWalletBrick(data.data.preference_id, publicKey);
            } else {
                throw new Error(data.message || 'No se pudo crear la preferencia');
            }

        } catch (error) {
            console.error('❌ Error:', error);
            alert('Error: ' + error.message);
            
            // Restaurar botón
            createBtn.disabled = false;
            createBtn.innerHTML = '<i class="bi bi-credit-card"></i> Pagar con Mercado Pago';
            loadingOverlay.classList.remove('active');
        }
    }

    // ✅ Función para renderizar el botón de Mercado Pago
    function renderWalletBrick(preferenceId, publicKey) {
        try {
            const mp = new MercadoPago(publicKey);
            const bricksBuilder = mp.bricks();
            
            bricksBuilder.create("wallet", "wallet_container", {
                initialization: {
                    preferenceId: preferenceId,
                },
            }).then(function() {
                // ✅ Ocultar botón y mostrar wallet
                createBtn.classList.add('d-none');
                walletContainer.classList.remove('d-none');
                loadingOverlay.classList.remove('active');
                console.log('✅ Botón de pago renderizado');
            }).catch(function(error) {
                console.error('❌ Error al renderizar el botón:', error);
                alert('Error al cargar el botón de pago: ' + error.message);
                loadingOverlay.classList.remove('active');
                createBtn.disabled = false;
                createBtn.innerHTML = '<i class="bi bi-credit-card"></i> Pagar con Mercado Pago';
            });
        } catch (error) {
            console.error('❌ Error al inicializar Mercado Pago:', error);
            alert('Error al inicializar el sistema de pagos: ' + error.message);
            loadingOverlay.classList.remove('active');
            createBtn.disabled = false;
            createBtn.innerHTML = '<i class="bi bi-credit-card"></i> Pagar con Mercado Pago';
        }
    }

    // ✅ Evento del botón
    createBtn.addEventListener('click', createPreferenceAndPay);
});
</script>
@endsection