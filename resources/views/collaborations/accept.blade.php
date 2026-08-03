@extends('layouts.modern')

@section('title', 'Aceptar Invitación - MedFlow')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h4><i class="bi bi-envelope"></i> Invitación a colaborar</h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-people" style="font-size: 3rem; color: #0d6efd;"></i>
                    </div>
                    
                    <p>Has recibido una invitación para colaborar en un espacio de trabajo.</p>
                    
                    @guest
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i>
                            <strong>⚠️ Debes iniciar sesión para aceptar la invitación.</strong>
                            <p class="mb-0 mt-2">
                                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
                                </a>
                                <a href="{{ route('password.request') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-key"></i> ¿Olvidaste tu contraseña?
                                </a>
                            </p>
                        </div>
                    @else
                        <p class="text-muted small">
                            Esta invitación expirará en <strong>7 días</strong>.
                        </p>
                        
                        <div class="d-grid gap-2 mt-4">
                            <form method="POST" action="{{ route('collaborations.accept', $token) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="bi bi-check-circle"></i> Aceptar Invitación
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('collaborations.reject', $token) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-x-circle"></i> Rechazar
                                </button>
                            </form>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</div>
@endsection