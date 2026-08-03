/**
 * LoadingIndicator - Clase reutilizable para indicadores de carga
 */
 class LoadingIndicator {
    constructor(options = {}) {
        this.defaults = {
            text: 'Cargando...',
            spinnerClass: 'spinner-border',
            color: 'text-primary',
            containerSelector: '#loadingContainer',
            overlay: true,
            overlayClass: 'loading-overlay'
        };
        
        this.options = { ...this.defaults, ...options };
        this.isActive = false;
    }

    /**
     * Mostrar el indicador de carga
     */
    show(text = null) {
        const message = text || this.options.text;
        this.isActive = true;
        
        // Buscar o crear contenedor
        let container = document.querySelector(this.options.containerSelector);
        if (!container) {
            container = document.createElement('div');
            container.id = this.options.containerSelector.replace('#', '');
            document.body.appendChild(container);
        }

        // Si hay overlay, agregarlo
        if (this.options.overlay) {
            const overlay = document.createElement('div');
            overlay.className = this.options.overlayClass;
            overlay.id = 'loadingOverlay';
            container.appendChild(overlay);
        }

        // Crear el spinner
        const spinnerWrapper = document.createElement('div');
        spinnerWrapper.className = 'loading-content';
        spinnerWrapper.id = 'loadingContent';
        spinnerWrapper.innerHTML = `
            <div class="d-flex flex-column align-items-center gap-3">
                <div class="spinner-border ${this.options.spinnerClass} ${this.options.color}" role="status">
                    <span class="visually-hidden">${message}</span>
                </div>
                <span class="loading-text text-muted">${message}</span>
            </div>
        `;

        container.appendChild(spinnerWrapper);
        document.body.style.overflow = 'hidden';
    }

    /**
     * Ocultar el indicador de carga
     */
    hide() {
        this.isActive = false;
        const container = document.querySelector(this.options.containerSelector);
        if (container) {
            container.innerHTML = '';
        }
        document.body.style.overflow = '';
    }

    /**
     * Actualizar el texto del indicador
     */
    updateText(text) {
        const textElement = document.querySelector('.loading-text');
        if (textElement) {
            textElement.textContent = text;
        }
    }

    /**
     * Verificar si el indicador está activo
     */
    isShowing() {
        return this.isActive;
    }
}

// ✅ Exportar para uso global
window.LoadingIndicator = LoadingIndicator;