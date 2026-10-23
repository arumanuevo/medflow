@extends('layouts.app')

@section('title', 'Registrarse - MedFlow')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-2 text-center">
                        <h4 class="fw-bold mb-1"><i class="bi bi-activity text-primary me-2"></i>Crear Cuenta en MedFlow</h4>
                        <p class="text-muted small">Plataforma SaaS de Medición y Gestión de Sensores</p>
                    </div>

                    <div class="card-body p-4 pt-2">
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

                            <!-- Términos Legales y Delimitación de Responsabilidad (SaaS Argentina) -->
                            <div class="row mb-4">
                                <div class="col-md-10 offset-md-1">
                                    <div class="p-3 border rounded-3 mb-2 bg-light"
                                        style="max-height: 180px; overflow-y: auto; font-size: 0.8rem; color: #475569; line-height: 1.6;">
                                        <strong class="text-dark"><i class="bi bi-shield-check text-primary me-1"></i> Acuerdo de Exención de Responsabilidad, Indemnidad y Privacidad (SaaS Argentina)</strong><br><br>
                                        <strong>1. Naturaleza Instrumental y Metrología:</strong> El software "MedFlow" se provee bajo modalidad SaaS exclusivamente como una herramienta tecnológica informática para digitalización de fotos y cálculo aritmético de diferencias entre lecturas. MedFlow <strong>no es empresa distribuidora de servicios públicos ni certifica metrología legal (Ley 19.511)</strong> de medidores o caudalímetros físicos.<br><br>
                                        <strong>2. Responsabilidad de la Entidad:</strong> La persona humana o jurídica administradora (Barrio Privado / Club de Campo bajo CCCN art. 2073, Consorcio de Propiedad Horizontal art. 2037 CCCN, Cooperativa Ley 20.337, Municipio o Empresa) es la <strong>única, exclusiva e indelegable responsable</strong> de los datos que ingresa, de la veracidad de las fotos, de la calibración de los sensores y de las liquidaciones de cobro o expensas que emita a sus residentes o abonados.<br><br>
                                        <strong>3. Indemnidad Total (Hold Harmless):</strong> El Administrador se compromete formalmente a mantener plenamente indemne a MedFlow y a sus desarrolladores ante cualquier reclamo judicial o administrativo incoado por vecinos, copropietarios o entes de Defensa del Consumidor (Ley 24.240) derivado de facturaciones, lecturas o averías de red.<br><br>
                                        <strong>4. Datos Personales (Ley 25.326):</strong> El Administrador actúa como Responsable de la Base de Datos y MedFlow como Encargado del Tratamiento. MedFlow no vende ni cede datos a terceros.<br><br>
                                        <a href="{{ route('legal.terms') }}" target="_blank" class="fw-bold text-primary text-decoration-none">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Leer el Marco Legal y Artículos Completos &raquo;
                                        </a>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="terms" id="termsAccepted"
                                            required>
                                        <label class="form-check-label text-muted" for="termsAccepted"
                                            style="font-size: 0.85rem;">
                                            He leído, comprendido y <strong>acepto los términos de uso, condiciones legales, régimen de protección de datos (Ley 25.326) y exención de responsabilidad</strong>.
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-semibold" id="registerBtn">
                                        <i class="bi bi-person-plus me-1"></i> Crear Cuenta
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
