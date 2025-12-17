<div>
    {{-- CSS for Scrollbar & Hover Effect --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .member-card {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        .member-card:hover {
            background-color: #f8fafc;
            border-left-color: #6366f1;
            transform: translateX(2px);
        }
    </style>

    <div class="d-flex flex-column h-100 bg-white rounded-3 overflow-hidden shadow-sm border" style="max-height: 500px;">
        
        <div class="p-4 bg-white border-bottom z-1 position-relative">
            @if($canManageMembers)
                <label class="small fw-bold text-uppercase text-secondary mb-2" style="font-size: 0.7rem; letter-spacing: 0.8px;">
                    Invite Team Member
                </label>
                <form class="position-relative" wire:submit.prevent="addMember">
                    <div class="input-group shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <svg width="18" height="18" fill="none" stroke="#94a3b8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </span>
                        <input type="email" 
                               class="form-control border-0 shadow-none ps-2" 
                               placeholder="Enter email address..." 
                               wire:model="email"
                               style="font-size: 0.95rem;">
                        <button type="submit" class="btn btn-dark px-4 fw-medium">Invite</button>
                    </div>
                </form>
                @error('email') 
                    <div class="text-danger small mt-2 d-flex align-items-center gap-1 animate__animated animate__fadeIn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        {{ $message }}
                    </div> 
                @enderror
            @else
                <div class="alert alert-light border d-flex align-items-center gap-2 text-muted small mb-0">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>You view this project as a member.</span>
                </div>
            @endif
        </div>

        <div class="px-4 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
            <span class="text-secondary fw-bold small text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Active Members</span>
            <span class="badge bg-white text-dark border shadow-sm rounded-pill px-3">{{ $members->count() }} Users</span>
        </div>

        <div class="custom-scrollbar flex-grow-1 p-2" style="overflow-y: auto; overflow-x: hidden; min-height: 200px;">
            <div class="d-flex flex-column gap-1">
                @forelse ($members as $member)
                    <div wire:key="member-{{ $member->id }}" 
                         class="member-card d-flex align-items-center justify-content-between p-3 rounded-3 bg-white">
                        
                        <div class="d-flex align-items-center gap-3 overflow-hidden">
                            @if($member->avatar_path)
                                <img src="{{ $member->avatar_url }}" class="rounded-circle border shadow-sm flex-shrink-0" width="40" height="40" style="object-fit: cover;">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm flex-shrink-0" 
                                     style="width: 40px; height: 40px; background: linear-gradient(135deg, #4f46e5, #ec4899); font-size: 0.9rem;">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                            @endif
                                 
                            <div class="d-flex flex-column" style="min-width: 0;">
                                <div class="fw-bold text-dark text-truncate">{{ $member->name }}</div>
                                <div class="text-muted small text-truncate">{{ $member->email }}</div>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center gap-3 flex-shrink-0">
                            
                            @if($member->id === $project->owner_id)
                                <span class="badge rounded-pill px-3 py-2 d-flex align-items-center gap-1 shadow-sm" 
                                      style="background-color: #fffbeb; color: #b45309; border: 1px solid #fcd34d;">
                                    <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    OWNER
                                </span>
                            @elseif($member->pivot->role === 'Admin')
                                <span class="badge rounded-pill px-3 py-2 shadow-sm" 
                                      style="background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe;">
                                    ADMIN
                                </span>
                            @else
                                <span class="badge rounded-pill px-3 py-2" 
                                      style="background-color: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">
                                    MEMBER
                                </span>
                            @endif

                            @if($canManageMembers && $member->id !== $project->owner_id && $member->id !== Auth::id())
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center border" 
                                            style="width: 32px; height: 32px; transition: 0.2s;" 
                                            type="button" data-bs-toggle="dropdown">
                                        <svg width="18" height="18" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-1" style="min-width: 180px;">
                                        
                                        @if($member->pivot->role !== 'Admin')
                                            <li>
                                                <button class="dropdown-item small rounded-2 py-2 d-flex align-items-center gap-2 fw-medium" 
                                                        wire:click="updateRole({{ $member->id }}, 'Admin')">
                                                    <svg class="text-primary" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Promote to Admin
                                                </button>
                                            </li>
                                        @else
                                            <li>
                                                <button class="dropdown-item small rounded-2 py-2 d-flex align-items-center gap-2 fw-medium" 
                                                        wire:click="updateRole({{ $member->id }}, 'Member')">
                                                    <svg class="text-secondary" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                    Demote to Member
                                                </button>
                                            </li>
                                        @endif
                                        
                                        <li><hr class="dropdown-divider my-1 opacity-25"></li>
                                        
                                        <li>
                                            <button class="dropdown-item text-danger small rounded-2 py-2 d-flex align-items-center gap-2 fw-medium hover-bg-danger-subtle" 
                                                    wire:click="removeMember({{ $member->id }})"
                                                    wire:confirm="Are you sure you want to remove {{ $member->name }} from this project?">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Kick from Project
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle p-3 mb-3 d-inline-block">
                            <svg class="text-muted" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h6 class="text-secondary fw-bold">No Members Found</h6>
                        <p class="text-muted small mb-0">Start by inviting someone to this project.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>