@php
    $menuItems = app(\App\Services\SidebarService::class)->getMenuItems();
    $isOwner = session('active_workspace', auth()->id()) == auth()->id();
    
    // ✅ Verificar si el usuario está pausado en el workspace activo
    $isPaused = false;
    $activeWorkspace = session('active_workspace', auth()->id());
    if ($activeWorkspace != auth()->id()) {
        $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();
        
        if ($collaboration && $collaboration->is_paused) {
            $isPaused = true;
        }
    }
@endphp

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/dashboard" class="brand-link">
        <i class="fas fa-tachometer-alt brand-image"></i>
        <span class="brand-text font-weight-light">MedFlow</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle fa-2x text-white"></i>
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->name ?? 'Invitado' }}</a>
                <small class="text-muted">
                    {{ auth()->check() ? auth()->user()->roles->pluck('name')->join(', ') : '' }}
                    
                    @if(!$isOwner)
                        <span class="badge bg-info" style="font-size: 0.6rem;">
                            <i class="bi bi-people"></i> Colaborador
                        </span>
                    @else
                        <span class="badge bg-success" style="font-size: 0.6rem;">
                            <i class="bi bi-house"></i> Propietario
                        </span>
                    @endif

                    {{-- ✅ Badge de acceso pausado --}}
                    @if($isPaused)
                        <span class="badge bg-danger" style="font-size: 0.6rem; display: block; margin-top: 4px;">
                            <i class="bi bi-pause-circle"></i> Acceso pausado
                        </span>
                    @endif
                </small>
            </div>
        </div>

        {{-- ✅ Selector de espacio de trabajo --}}
        @auth
        <div class="mt-3 mb-3 px-2">
            <label class="text-muted small text-uppercase" style="font-size: 0.65rem; padding-left: 5px;">
                <i class="bi bi-briefcase"></i> Espacio de trabajo
            </label>
            <select class="form-select form-select-sm" id="workspaceSelector" style="background: #3a3f46; color: #fff; border-color: #4b545c; width: 100%;">
                <option value="{{ auth()->id() }}" {{ session('active_workspace') == auth()->id() ? 'selected' : '' }}>
                    <i class="bi bi-house"></i> Mi espacio
                </option>
                @php
                    $collaborations = \App\Models\WorkspaceCollaborator::where('user_id', auth()->id())
                        ->where('status', 'active')
                        ->with('workspace')
                        ->get();
                @endphp
                @foreach($collaborations as $collab)
                    <option value="{{ $collab->workspace_id }}" {{ session('active_workspace') == $collab->workspace_id ? 'selected' : '' }}>
                        <i class="bi bi-people"></i> {{ $collab->workspace->name ?? 'Espacio de ' . ($collab->workspace->email ?? 'Usuario') }}
                        <span class="badge bg-info" style="font-size: 0.6rem;">({{ $collab->role }})</span>
                        @if($collab->is_paused)
                            <span class="badge bg-danger" style="font-size: 0.5rem;">⏸️ Pausado</span>
                        @endif
                    </option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-1" style="font-size: 0.6rem;">
                <i class="bi bi-info-circle"></i> Cambia entre tu espacio y los colaborativos
            </small>
            @if($isPaused)
                <div class="alert alert-danger alert-sm mt-2 p-1 text-center" style="font-size: 0.65rem; border-radius: 4px; background: #dc3545; color: white; border: none;">
                    <i class="bi bi-exclamation-triangle"></i> Acceso pausado por el propietario
                </div>
            @endif
        </div>
        @endauth

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column">
            @foreach($menuItems as $item)
                @if(isset($item['admin_only']) && $item['admin_only'] && !auth()->user()->hasRole('admin'))
                    @continue
                @endif
                <li class="nav-item">
                    <a href="{{ $item['url'] }}" 
                    class="nav-link {{ $item['active'] ? 'active' : '' }}"
                    @if(isset($item['highlight']) && $item['highlight'])
                        style="background: linear-gradient(90deg, #28a745, #20c997); border-radius: 4px; margin: 2px 5px;"
                    @endif
                    >
                        <i class="nav-icon {{ $item['icon'] }}"></i>
                        <p @if(isset($item['highlight']) && $item['highlight']) style="color: white; font-weight: 600;" @endif>
                            {{ $item['label'] }}
                            @if(isset($item['badge']) && $item['badge'] > 0)
                                <span class="badge badge-warning ml-2" style="background: #ffc107; color: #000; font-size: 0.6rem;">
                                    <i class="fas fa-bell"></i> {{ $item['badge'] }}
                                </span>
                            @endif
                        </p>
                    </a>
                </li>
            @endforeach

                <!-- Separador visual antes del cierre -->
                <li class="nav-item" style="border-top: 1px solid #4b545c; margin-top: 10px; padding-top: 10px;">
                    <small class="text-muted" style="padding-left: 15px; font-size: 0.7rem; text-transform: uppercase;">
                        <i class="fas fa-circle" style="font-size: 0.4rem; color: #28a745;"></i> Sistema
                    </small>
                </li>
            </ul>
        </nav>
    </div>
</aside>

{{-- ✅ Script para el selector de espacio y colapso del sidebar --}}
@push('scripts')
<script>
$(document).ready(function() {
    // =============================================
    // SELECTOR DE ESPACIO DE TRABAJO
    // =============================================
    const activeWorkspace = localStorage.getItem('active_workspace');
    if (activeWorkspace) {
        $('#workspaceSelector').val(activeWorkspace);
    }

    $('#workspaceSelector').change(function() {
        const workspaceId = $(this).val();
        localStorage.setItem('active_workspace', workspaceId);
        window.location.href = '/dashboard?workspace=' + workspaceId;
    });

    // =============================================
    // COLABSO DEL SIDEBAR - AdminLTE
    // =============================================
    if (typeof $.AdminLTE !== 'undefined') {
        $.AdminLTE.init();
    }

    $('[data-widget="pushmenu"]').on('click', function(e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-collapse');
        const isCollapsed = $('body').hasClass('sidebar-collapse');
        localStorage.setItem('sidebar_collapsed', isCollapsed);
    });

    const sidebarCollapsed = localStorage.getItem('sidebar_collapsed');
    if (sidebarCollapsed === 'true') {
        $('body').addClass('sidebar-collapse');
    }

    if ($(window).width() <= 768) {
        $('body').addClass('sidebar-collapse');
    }

    $('.nav-link').on('click', function() {
        if ($(window).width() <= 768) {
            $('body').addClass('sidebar-collapse');
        }
    });
});
</script>
@endpush