@extends('layouts.app')

@section('title', 'Registrarse - MeasureFlow')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Register') }}</div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="row mb-3">
                                <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="email"
                                    class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                        name="email" value="{{ old('email') }}" required autocomplete="email">

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="password"
                                    class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        required autocomplete="new-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="password-confirm"
                                    class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control"
                                        name="password_confirmation" required autocomplete="new-password">
                                </div>
                            </div>

                            <!-- Términos Legales -->
                            <div class="row mb-4">
                                <div class="col-md-8 offset-md-2">
                                    <div class="p-3 border rounded mb-3 bg-light"
                                        style="max-height: 150px; overflow-y: auto; font-size: 0.8rem; color: #6c757d;">
                                        <strong>Acuerdo de Exención de Responsabilidad y Privacidad (SaaS)</strong><br><br>
                                        El software "MedFlow" se proporciona exclusivamente como una herramienta tecnológica
                                        (SaaS) facilitadora de recolección y logística. Usted, como administrador o usuario,
                                        es el único propietario y responsable de los datos que ingresa al sistema y de la
                                        actividad de los inspectores que opera.
                                        <br><br>
                                        MedFlow no recopila, no comercializa y no utiliza su información personal (ni fotos,
                                        ni correos, ni métricas) para beneficio de terceros bajo ninguna circunstancia. Al
                                        registrarse, usted acepta formalmente eximir de cualquier daño legal, directo o
                                        indirecto, derivado del mal uso o vulnerabilidad del servicio, a los desarrolladores
                                        del sistema. Usted asume la responsabilidad total sobre la administración de sus
                                        espacios de trabajo y rutas creadas.
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="terms" id="termsAccepted"
                                            required>
                                        <label class="form-check-label text-muted" for="termsAccepted"
                                            style="font-size: 0.9rem;">
                                            He leído, comprendido y <strong>acepto los términos de uso, políticas de
                                                privacidad y exención de responsabilidad</strong>.
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary" id="registerBtn">
                                        <i class="bi bi-person-plus"></i> Crear Cuenta
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const termsCheckbox = document.getElementById('termsAccepted');
            const registerBtn = document.getElementById('registerBtn');

            function updateBtnState() {
                registerBtn.disabled = !termsCheckbox.checked;
            }

            termsCheckbox.addEventListener('change', updateBtnState);
            updateBtnState(); // Inicia bloqueado
        });
    </script>
@endsection