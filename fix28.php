<?php
$c = file_get_contents('resources/views/landing.blade.php');

$contactHtml = <<<EOF
        <!-- ========================================= -->
        <!-- CONTACT SECTION -->
        <!-- ========================================= -->
        <div id="contacto" class="row mt-5 mb-5 align-items-center animate-fade-in animate-delay-3">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="badge bg-primary-subtle text-primary mb-2 px-3 py-2 rounded-pill">Atención Personalizada</span>
                <h3 class="fw-bold mb-3">¿Necesitas un Desarrollo a Medida?</h3>
                <p class="text-muted mb-4">
                    Comprendemos que cada industria tiene flujos de trabajo únicos. Si necesitas 
                    <strong>asesoramiento especializado</strong> o un <strong>desarrollo de software y hardware AdHoc</strong> 
                    para integrar tus propios sensores a MedFlow, estamos para ayudarte.
                </p>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Correo Electrónico</h6>
                        <a href="mailto:scastellano10@gmail.com" class="text-muted text-decoration-none">scastellano10@gmail.com</a>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">WhatsApp (Ventas)</h6>
                        <a href="https://wa.me/5491122334455" class="text-muted text-decoration-none">+54 9 11 2233-4455</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 overlay-box">
                    <div class="card-body p-4 p-md-5">
                        <h5 class="fw-bold mb-4">Envíanos tu consulta</h5>
                        <form id="contactForm" onsubmit="event.preventDefault(); alert('Gracias por contactarnos. Nuestro equipo se comunicará contigo mediante el correo scastellano10@gmail.com a la brevedad.'); this.reset();">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre Completo</label>
                                <input type="text" class="form-control" placeholder="Ej. Juan Pérez" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Corporativo</label>
                                <input type="email" class="form-control" placeholder="Ej. juan@empresa.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tu Consulta</label>
                                <textarea class="form-control" rows="4" placeholder="Desarrollo a medida, cotización de sensores..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                                <i class="bi bi-send me-2"></i> Enviar Mensaje
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <footer>
EOF;

$c = str_replace('<footer>', $contactHtml, $c);

// Also add a Contact link in the top Navbar
$navLink = <<<EOF
                <div class="d-flex gap-3 align-items-center">
                    <a href="#contacto" class="text-muted text-decoration-none d-none d-md-block fw-semibold">
                        <i class="bi bi-headset me-1"></i> Asesoramiento
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill px-4">
EOF;

// Replace existing button in navbar to inject the link
$c = preg_replace('/<div class="d-flex gap-2">\s*<a href="\{\{ route\(\'login\'\) \}\}" class="btn btn-outline-primary rounded-pill px-4">/s', $navLink, $c);

file_put_contents('resources/views/landing.blade.php', $c);
echo "Injected Contact Form\n";
