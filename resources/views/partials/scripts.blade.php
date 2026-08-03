<script>
    // Forzar la inyección del token en localStorage para peticiones AJAX
    @auth
        @if(session()->has('sanctum_token'))
            localStorage.setItem('token', '{{ session('sanctum_token') }}');
        @else
            @if(auth()->user()->currentAccessToken())
                localStorage.setItem('token', '{{ auth()->user()->currentAccessToken()->plainTextToken }}');
            @endif
        @endif
    @endauth

    // Función para mostrar alertas
    function showAlert(message, type = 'success') {
        const alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        $('.card-body, #content').first().prepend(alert);
    }
</script>
