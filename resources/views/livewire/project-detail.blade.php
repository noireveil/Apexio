<div class="d-flex flex-column h-100">
    
    {{-- Header --}}
    <div class="page-header pb-0 pt-4 px-4 border-bottom flex-shrink-0">
        
        <div class="d-flex justify-content-between align-items-start mb-4">
            
            {{-- Title --}}
            <div>
                <div class="d-flex align-items-center gap-2 text-muted small mb-2">
                    <span class="text-muted">Apexio Workspace</span>
                    <span class="text-muted">›</span>
                    <div class="d-flex align-items-center gap-2 text-dark fw-medium">
                        <span class="text-success d-flex align-items-center justify-content-center">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        </span>
                        {{ $project->name }}
                    </div>
                </div>

                <h1 class="h3 fw-bold text-dark mb-1">{{ $project->name }}</h1>
                <p class="text-muted small mb-0">
                    {{ $project->description ?? 'No description provided.' }}
                </p>
            </div>

            {{-- Actions --}}
            <div class="d-flex align-items-center gap-2 mt-2">
                
                {{-- Members (UPDATED LOGIC) --}}
                <div class="d-flex align-items-center">
                    <div class="d-flex avatar-stack">
                        @foreach($project->members->take(3) as $member)
                            @if($member->avatar_path)
                                <img src="{{ $member->avatar_url }}" 
                                     class="rounded-circle border-2 border-white shadow-sm" 
                                     width="32" height="32" 
                                     title="{{ $member->name }}"
                                     style="margin-right: -8px; object-fit: cover; background-color: white; position: relative; z-index: 1;">
                            @else
                                <div class="rounded-circle border-2 border-white bg-primary d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                                     title="{{ $member->name }}"
                                     style="width: 32px; height: 32px; margin-right: -8px; font-size: 10px; position: relative; z-index: 1;">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                            @endif
                        @endforeach
                        
                        @if($project->members->count() > 3)
                            <div class="rounded-circle border-2 border-white bg-light text-muted d-flex align-items-center justify-content-center small fw-bold shadow-sm" 
                                 style="width: 32px; height: 32px; margin-right: -8px; font-size: 10px; position: relative; z-index: 0;">
                                +{{ $project->members->count() - 3 }}
                            </div>
                        @endif
                    </div>
                    
                    {{-- Add Member --}}
                    @if($canManageMembers ?? true)
                    <button class="btn btn-outline-secondary d-flex align-items-center gap-1 fw-medium px-3 py-2 ms-3" 
                            style="border-style: dashed; border-width: 2px; color: #9ca3af; background-color: transparent;"
                            data-bs-toggle="modal" 
                            data-bs-target="#addMemberModal"> 
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Member
                    </button>
                    @endif
                </div>
                
                <div class="vr mx-3" style="height: 24px; color: #e5e7eb;"></div>

                {{-- Add Task --}}
                @can('update', $project)
                    <button class="btn btn-primary d-flex align-items-center gap-2 fw-medium px-3 py-2" 
                            data-bs-toggle="modal" 
                            data-bs-target="#createTaskModal">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Task
                    </button>
                @endcan
            </div>
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-tabs custom-tabs border-0">
            {{-- Board --}}
            <li class="nav-item">
                <a href="#" class="nav-link active text-primary border-bottom border-primary border-2 bg-transparent">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-1 mb-1">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 00-2-2h-2"></path>
                    </svg>
                    Board
                </a>
            </li>
        </ul>
    </div>

    {{-- Content --}}
    <div class="page-content bg-light p-4 flex-grow-1" style="background-color: #f8fafc !important;">
        @livewire('task-list', ['project_id' => $project->id], key($project->id))
    </div>
    
    {{-- Member Modal --}}
    <div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Manage Members</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-4">
                    @livewire('project-members', ['project' => $project], key('members-'.$project->id))
                </div>
            </div>
        </div>
    </div>
</div>