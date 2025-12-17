<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Apexio') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased overflow-hidden">
    <div class="app-wrapper">
        
        {{-- Sidebar --}}
        <aside class="app-sidebar">
            <div class="sidebar-header">
                <span class="fs-5 fw-bold text-white tracking-tight" style="font-size: 1.1rem;">Apexio</span>
            </div>

            <div class="sidebar-content">
                @if(Auth::user()->is_admin)
                    {{-- Admin Navigation --}}
                    <div class="nav-section">
                        <div class="section-title">ADMINISTRATION</div>
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            System Dashboard
                        </a>
                    </div>
                    
                    <div class="px-4 py-3 mt-4 text-center">
                        <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10">
                            <div class="text-white small fw-medium mb-1">Super Admin Mode</div>
                            <div class="text-white text-opacity-50" style="font-size: 0.75rem;">Full system access enabled</div>
                        </div>
                    </div>
                @else
                    {{-- User Navigation --}}
                    <div class="nav-section">
                        <div class="section-title">MAIN MENU</div>
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            Dashboard
                        </a>
                        <a href="{{ route('my-tasks') }}" class="nav-link {{ request()->routeIs('my-tasks') ? 'active' : '' }}">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            My Tasks
                        </a>
                    </div>

                    <div class="nav-section">
                        <div class="d-flex align-items-center justify-content-between pe-2">
                            <div class="section-title mb-0">PROJECTS</div>
                            <button class="btn btn-link p-0 text-secondary" onclick="Livewire.dispatch('open-create-modal')">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <div class="mt-2"></div>

                        @php 
                            $userProjects = Auth::user()->projects()->latest()->get();
                            $colors = ['#3b82f6', '#f97316', '#22c55e', '#a855f7', '#ec4899', '#eab308', '#06b6d4'];
                        @endphp

                        <div style="max-height: 300px; overflow-y: auto; overflow-x: hidden;" class="pe-1">
                            @forelse($userProjects as $index => $proj)
                                @php
                                    $isActive = request()->routeIs('project.detail') && request()->route('project')->id == $proj->id;
                                    $dotColor = $colors[$index % count($colors)];
                                @endphp
                                
                                <a href="{{ route('project.detail', $proj) }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                                    <span class="project-dot" style="background-color: {{ $dotColor }};"></span>
                                    <span class="text-truncate" title="{{ $proj->name }}">{{ $proj->name }}</span>
                                </a>
                            @empty
                                <div class="px-3 py-2 text-muted small fst-italic" style="font-size: 0.8rem;">No projects yet</div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            {{-- User Profile Footer --}}
            <div class="sidebar-footer">
                <div class="dropdown dropup w-100">
                    <a href="#" class="user-dropdown-btn dropdown-toggle d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
                        @if(Auth::user()->avatar_path)
                            <img src="{{ Auth::user()->avatar_url }}" class="avatar rounded-circle border border-secondary" width="36" height="36" style="object-fit: cover;">
                        @else
                            <div class="avatar rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 36px; height: 36px; font-size: 0.9rem; border: 2px solid rgba(255,255,255,0.1);">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif

                        <div class="info text-truncate" style="max-width: 140px;">
                            <div class="name fw-bold text-white" style="font-size: 0.9rem;">{{ Auth::user()->name }}</div>
                            <div class="email text-white-50 small text-truncate" style="font-size: 0.75rem;">{{ Auth::user()->email }}</div>
                        </div>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-dark shadow-lg w-100 mb-2 border-0 rounded-3 overflow-hidden">
                        <li><div class="px-3 py-2 text-white-50 small text-uppercase fw-bold" style="font-size: 0.7rem;">Account</div></li>
                        <li><a class="dropdown-item py-2 small d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Profile Settings
                        </a></li>
                        <li><hr class="dropdown-divider border-secondary opacity-25 my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger py-2 small d-flex align-items-center gap-2 hover-bg-danger-subtle" type="submit">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

        <main class="app-content">
            {{ $slot }}
        </main>
    </div>

    @livewire('manage-projects')

    <script>
        document.addEventListener('livewire:initialized', () => {
            const bs = window.bootstrap;
            
            Livewire.on('open-create-modal', () => bs.Modal.getOrCreateInstance('#createProjectModal').show());
            Livewire.on('close-create-modal', () => bs.Modal.getInstance('#createProjectModal')?.hide());
            Livewire.on('open-modal', (id) => bs.Modal.getOrCreateInstance('#' + (Array.isArray(id) ? id[0] : (id || 'commentsModal'))).show());
            Livewire.on('close-modal', () => bs.Modal.getInstance('#commentsModal')?.hide());
        });
    </script>
</body>
</html>