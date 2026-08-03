<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso - MedFlow</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #d9e2f0 50%, #c5d3e8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-container {
            max-width: 550px;
            width: 100%;
            padding: 1.5rem;
        }
        
        .success-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: scaleIn 0.5s ease 0.3s forwards;
            transform: scale(0);
        }
        
        .success-icon i {
            font-size: 3.5rem;
            color: #fff;
        }
        
        .success-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .success-subtitle {
            color: #4a5568;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        .user-info {
            background: #f7fafc;
            border-radius: 12px;
            padding: 1rem;
            margin: 1.5rem 0;
            text-align: left;
        }
        
        .user-info .label {
            font-size: 0.8rem;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .user-info .value {
            font-weight: 600;
            color: #2d3748;
            font-size: 1rem;
        }
        
        .verification-message {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            text-align: left;
        }
        
        .verification-message i {
            color: #f59e0b;
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }
        
        .verification-message p {
            margin: 0;
            color: #78350f;
            font-size: 0.95rem;
        }
        
        .btn-outline-primary-custom {
            background: transparent;
            border: 2px solid #0d6efd;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            color: #0d6efd;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .btn-outline-primary-custom:hover {
            background: #0d6efd;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.35);
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #0d6efd, #0a5fd9);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            color: #fff;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.35);
            color: #fff;
        }
        
        .btn-success-custom {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            color: #fff;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.35);
            color: #fff;
        }
        
        .actions-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        
        .next-steps {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            text-align: left;
        }
        
        .next-steps h6 {
            color: #1a202c;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .next-steps ul {
            margin: 0;
            padding-left: 1.2rem;
            color: #4a5568;
            font-size: 0.9rem;
        }
        
        .next-steps ul li {
            margin-bottom: 0.3rem;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        @media (max-width: 576px) {
            .success-card {
                padding: 2rem 1.5rem;
            }
            
            .success-icon {
                width: 80px;
                height: 80px;
            }
            
            .success-icon i {
                font-size: 2.8rem;
            }
            
            .success-title {
                font-size: 1.6rem;
            }
            
            .actions-container {
                flex-direction: column;
                align-items: center;
            }
            
            .actions-container a {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <!-- Ícono de éxito -->
            <div class="success-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            
            <!-- Título -->
            <h1 class="success-title">¡Registro Exitoso! 🎉</h1>
            <p class="success-subtitle">Tu cuenta ha sido creada correctamente.</p>
            
            <!-- Información del usuario -->
            <div class="user-info">
                <div class="row mb-2">
                    <div class="col-5">
                        <div class="label">👤 Usuario</div>
                    </div>
                    <div class="col-7">
                        <div class="value">{{ $user->name ?? 'Usuario' }}</div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-5">
                        <div class="label">📧 Email</div>
                    </div>
                    <div class="col-7">
                        <div class="value">{{ $user->email ?? 'email@ejemplo.com' }}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-5">
                        <div class="label">🏷️ Tipo de cuenta</div>
                    </div>
                    <div class="col-7">
                        <div class="value">
                            @if(isset($user) && $user->subscription_type === 'corporativo')
                                🏢 Corporativo
                            @else
                                🏠 Domiciliario
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mensaje de verificación de email -->
            <div class="verification-message">
                <i class="bi bi-envelope"></i>
                <p>
                    <strong>Verifica tu correo electrónico</strong><br>
                    Te hemos enviado un email de verificación a <strong>{{ $user->email ?? 'tu email' }}</strong>.
                    Por favor, revisa tu bandeja de entrada y haz clic en el enlace de verificación para activar tu cuenta.
                </p>
                <p class="mt-2 small">
                    <i class="bi bi-info-circle"></i>
                    Si no recibes el email en unos minutos, revisa tu carpeta de spam.
                </p>
            </div>
            
            <!-- Próximos pasos -->
            <div class="next-steps">
                <h6><i class="bi bi-list-check"></i> Próximos pasos:</h6>
                <ul>
                    <li>📧 Revisa tu correo electrónico para verificar tu cuenta</li>
                    <li>🔐 Una vez verificado, podrás iniciar sesión</li>
                    <li>📊 Comienza a configurar tus sensores y grupos</li>
                </ul>
            </div>
            
            <!-- Botones de acción -->
            <div class="actions-container">
                <a href="{{ route('login') }}" class="btn-primary-custom">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Ir a Iniciar Sesión
                </a>
                <a href="{{ route('landing') }}" class="btn-outline-primary-custom">
                    <i class="bi bi-house me-2"></i> Volver al Inicio
                </a>
            </div>
            
            <!-- Mensaje de ayuda -->
            <div class="mt-3">
                <small class="text-muted">
                    <i class="bi bi-question-circle"></i>
                    ¿Problemas con la verificación? 
                    <a href="#" class="text-primary">Reenviar email</a>
                </small>
            </div>
        </div>
    </div>
</body>
</html>