@extends('layouts.public')

@section('title', 'Establecer Contraseña')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header ">
                <h4><i class="bi bi-key"></i> Establecer Contraseña</h4>
            </div>
            <div class="card-body">
                <p>Establece tu contraseña para acceder al sistema.</p>
                
                <form method="POST" action="{{ route('password.set') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle"></i> Establecer Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection