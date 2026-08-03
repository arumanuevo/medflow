@php
    $menuItems = app(\App\Services\SidebarService::class)->getMenuItems();
    $activeWorkspace = session('active_workspace', auth()->id());
    $isOwner = $activeWorkspace == auth()->id();
    $isPaused = false;
    
    if (!$isOwner) {
        $collaboration = \App\Models\WorkspaceCollaborator::where('workspace_id', $activeWorkspace)
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();
        if ($collaboration && $collaboration->is_paused) {
            $isPaused = true;
        }
    }
    
    $userInitials = strtoupper(substr(auth()->user()->name ?? 'U', 0, 2));
    $userRole = auth()->user()->roles->pluck('name')->join(', ');
    
    // Obtener colaboraciones activas para el selector
    $collaborations = \App\Models\WorkspaceCollaborator::where('user_id', auth()->id())
        ->where('status', 'active')
        ->where('is_paused', false)
        ->with('workspace')
        ->get();
@endphp

<aside class="modern-sidebar {{ session('sidebar_collapsed', false) ? 'collapsed' : '' }}" id="modernSidebar">
    
    {{-- Brand --}}
    <a href="/dashboard" class="sidebar-brand">
        <span class="sidebar-brand-icon"><i class="bi bi-droplet"></i></span>
        <span class="sidebar-brand-text">Med<span class="light">Flow</span></span>
        
    </a>

    {{-- User Panel --}}
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">{{ $userInitials }}</div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name" title="{{ auth()->user()->name }}">
                {{ auth()->user()->name }}
            </div>
            <div class="sidebar-user-role">
                {{-- ✅ MOSTRAR SOLO EL BADGE PRINCIPAL --}}
                @if($isOwner)
                    <span class="sidebar-user-badge owner" title="Propietario del espacio">
                        <i class="bi bi-house-fill"></i> Propietario
                    </span>
                @else
                    <span class="sidebar-user-badge collaborator" title="Colaborador en este espacio">
                        <i class="bi bi-people-fill"></i> Colaborador
                    </span>
                @endif
                @if($isPaused)
                    <span class="sidebar-user-badge paused" title="Acceso pausado por el propietario">
                        <i class="bi bi-pause-circle-fill"></i> Pausado
                    </span>
                @endif
                
                {{-- ✅ TOOLTIP CON TODOS LOS ROLES --}}
                <span class="sidebar-user-roles-tooltip" 
                    title="Roles: {{ $userRole }}"
                    style="cursor: help; font-size: 0.6rem; color: #475569; display: inline-block; margin-left: 4px;">
                    <i class="bi bi-info-circle"></i>
                    <span style="font-size: 0.55rem;">({{ $userRole }})</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Selector de Espacio --}}
    <div style="padding: 0.5rem 0.75rem; border-bottom: 1px solid var(--sidebar-border);">
        <label style="font-size: 0.6rem; text-transform: uppercase; color: #475569; font-weight: 600; letter-spacing: 0.08em; display: block; margin-bottom: 0.25rem;">
            <i class="bi bi-briefcase"></i> Espacio activo
        </label>
        <select id="workspaceSelector" style="width: 100%; background: var(--sidebar-bg-hover); color: #fff; border: 1px solid var(--sidebar-border); border-radius: 6px; padding: 0.4rem 0.6rem; font-size: 0.8rem; outline: none;">
            <option value="{{ auth()->id() }}" {{ $activeWorkspace == auth()->id() ? 'selected' : '' }}>
                <i class="bi bi-house"></i> Mi espacio
            </option>
            @foreach($collaborations as $collab)
                <option value="{{ $collab->workspace_id }}" {{ $activeWorkspace == $collab->workspace_id ? 'selected' : '' }}>
                    <i class="bi bi-people"></i> 
                    {{ $collab->workspace->name ?? 'Espacio de ' . ($collab->workspace->email ?? 'Usuario') }}
                    <span style="font-size: 0.55rem; color: #94a3b8;">({{ $collab->role }})</span>
                </option>
            @endforeach
        </select>
    </div>

    {{-- Menu --}}
    <nav class="sidebar-menu">
        <div class="sidebar-menu-section">Navegación</div>
        
        @foreach($menuItems as $item)
            @if(isset($item['admin_only']) && $item['admin_only'] && !auth()->user()->hasRole('admin'))
                @continue
            @endif
            @if($isPaused && $item['route'] !== 'dashboard' && $item['route'] !== 'profile.index')
                @continue
            @endif
            
            <a href="{{ $item['url'] }}" 
               class="sidebar-menu-link {{ $item['active'] ? 'active' : '' }} {{ isset($item['highlight']) && $item['highlight'] ? 'highlight' : '' }}"
               title="{{ $item['label'] }}">
                <i class="{{ $item['icon'] }}"></i>
                <span class="sidebar-menu-text">{{ $item['label'] }}</span>
                @if(isset($item['badge']) && $item['badge'] > 0)
                    <span class="sidebar-badge">{{ $item['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Footer --}}
    <div class="sidebar-footer">
        <a href="{{ route('logout') }}" 
           class="sidebar-menu-link" 
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           style="color: #ef4444;">
            <i class="bi bi-box-arrow-right"></i>
            <span class="sidebar-menu-text">Cerrar sesión</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</aside>