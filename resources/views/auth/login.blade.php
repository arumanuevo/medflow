@extends('layouts.public')

@section('title', 'Iniciar Sesión - MedFlow')

@section('content')
<div class="login-content">
    <!-- Header -->
    <div class="text-center mb-4">
        <div class="login-icon-wrapper mx-auto">
            <i class="bi bi-box-arrow-in-right"></i>
        </div>
        <h2 class="fw-bold mt-3 mb-1">¡Bienvenido de vuelta!</h2>
        <p class="text-muted small">Inicia sesión para continuar gestionando tus sensores</p>
    </div>

    <!-- Formulario -->
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small">
                <i class="bi bi-envelope me-1"></i> Correo electrónico
            </label>
            <input id="email" type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" 
                   placeholder="ejemplo@correo.com"
                   required autocomplete="email" autofocus>
            @error('email')
                <div class="invalid-feedback">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold small">
                <i class="bi bi-lock me-1"></i> Contraseña
            </label>
            <div class="password-wrapper">
                <input id="password" type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       name="password" placeholder="Ingresa tu contraseña"
                       required autocomplete="current-password">
                <button type="button" class="password-toggle" id="togglePassword">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Options -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                       {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label small" for="remember">
                    <i class="bi bi-check-circle me-1"></i> Recordarme
                </label>
            </div>
            @if (Route::has('password.request'))
                <a class="text-decoration-none small" href="{{ route('password.request') }}">
                    <i class="bi bi-key me-1"></i> ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <!-- Submit -->
        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
            </button>
        </div>

        <!-- Divider -->
        <div class="divider">
            <span class="text-muted small">o</span>
        </div>

        <!-- Google -->
        <div class="d-grid gap-2">
            <a href="{{ route('auth.google') }}" class="btn btn-outline-danger">
                <i class="bi bi-google me-2"></i> Continuar con Google
            </a>
        </div>
    </form>

    <!-- Register link -->
    <div class="text-center mt-4">
        <p class="text-muted small mb-0">
            ¿No tienes cuenta? 
            <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">
                <i class="bi bi-person-plus me-1"></i> Regístrate gratis
            </a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
</script>
@endpush