<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProjectMembers extends Component
{
    public Project $project;
    public string $email = '';
    public bool $canManageMembers = false;

    protected $listeners = ['member-updated' => '$refresh'];

    protected function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->canManageMembers = Auth::user()->can('update', $this->project);
    }

    public function render(): View
    {
        $members = $this->project->members()
            ->withPivot('role')
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.project-members', [
            'members' => $members
        ]);
    }

    public function addMember(): void
    {
        $this->authorize('update', $this->project);
        $this->validate();

        $user = User::where('email', $this->email)->first();

        if ($this->project->members()->where('user_id', $user->id)->exists() || $user->id === $this->project->owner_id) {
            $this->addError('email', 'User is already a member or is the owner.');
            return;
        }

        $this->project->members()->attach($user->id, ['role' => 'Member']);

        $this->reset('email');
        $this->dispatch('member-updated');
    }

    public function updateRole($userId, $role)
    {
        $this->authorize('update', $this->project);

        if (!in_array($role, ['Admin', 'Member'])) return;
        
        if ($userId === $this->project->owner_id) return;

        $this->project->members()->updateExistingPivot($userId, ['role' => $role]);
        $this->dispatch('member-updated');
    }

    public function removeMember($userId)
    {
        $this->authorize('update', $this->project);

        if ($userId === $this->project->owner_id) return;
        
        if ($userId === Auth::id()) return;

        $this->project->members()->detach($userId);
        $this->dispatch('member-updated');
    }
}