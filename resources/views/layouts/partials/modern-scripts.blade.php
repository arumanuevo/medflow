<script>
$(document).ready(function() {
    // =============================================
    // TOGGLE SIDEBAR (SOLO MÓVIL)
    // =============================================
    $('#sidebarToggle').click(function(e) {
        e.preventDefault();
        const sidebar = $('#modernSidebar');
        const overlay = $('#sidebarOverlay');
        const icon = $(this).find('i');

        // ✅ Solo funciona en móvil
        if (window.innerWidth <= 768) {
            sidebar.toggleClass('mobile-open');
            overlay.toggleClass('active');
            $('body').toggleClass('sidebar-open');

            if (sidebar.hasClass('mobile-open')) {
                icon.removeClass('bi-list').addClass('bi-x-lg');
            } else {
                icon.removeClass('bi-x-lg').addClass('bi-list');
            }
        }
    });

    // =============================================
    // CERRAR SIDEBAR EN MÓVIL
    // =============================================
    function closeMobileSidebar() {
        const sidebar = $('#modernSidebar');
        const overlay = $('#sidebarOverlay');

        sidebar.removeClass('mobile-open');
        overlay.removeClass('active');
        $('body').removeClass('sidebar-open');
        $('#sidebarToggle i').removeClass('bi-x-lg').addClass('bi-list');
    }

    $('#sidebarOverlay').click(closeMobileSidebar);

    $('.sidebar-menu-link').click(function() {
        if (window.innerWidth <= 768) {
            closeMobileSidebar();
        }
    });

    $(window).resize(function() {
        if (window.innerWidth > 768) {
            closeMobileSidebar();
        }
    });

    // =============================================
    // SELECTOR DE ESPACIO
    // =============================================
    $('#workspaceSelector').change(function() {
        const workspaceId = $(this).val();
        localStorage.setItem('active_workspace', workspaceId);
        window.location.href = '/dashboard?workspace=' + workspaceId;
    });

    // =============================================
    // TOKEN INTERCEPTOR
    // =============================================
    $(document).ajaxSend(function(event, jqxhr, settings) {
        if (settings.url && settings.url.includes('/api/')) {
            const token = localStorage.getItem('token');
            if (token) {
                jqxhr.setRequestHeader('Authorization', 'Bearer ' + token);
            }
        }
    });

    window.getToken = function() {
        return localStorage.getItem('token') || '';
    };

    console.log('✅ Modern sidebar cargado');
});
</script>