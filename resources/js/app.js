import './bootstrap';

// =============================================
// LOADING INDICATOR GLOBAL
// =============================================

// Inicializar el indicador de carga global
window.loadingIndicator = new LoadingIndicator({
    text: 'Procesando...',
    color: 'text-primary',
    overlay: true
});

// Función global para mostrar loading
window.showLoading = function(text = 'Procesando...') {
    window.loadingIndicator.updateText(text);
    window.loadingIndicator.show(text);
};

// Función global para ocultar loading
window.hideLoading = function() {
    window.loadingIndicator.hide();
};

// Interceptar todas las peticiones AJAX
$(document).ajaxStart(function() {
    // No mostrar loading automáticamente, dejamos que cada función decida
});

$(document).ajaxStop(function() {
    // No ocultar loading automáticamente
});
