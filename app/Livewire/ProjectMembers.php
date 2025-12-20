<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Komponen Livewire untuk mengelola member project.
 * 
 * Menampilkan list member, add member, update role, dan remove member.
 */
class ProjectMembers extends Component
{
    /**
     * Instance project yang dikelola.
     *
     * @var Project
     */
    public Project $project;

    /**
     * Email user yang akan ditambahkan.
     *
     * @var string
     */
    public string $email = '';

    /**
     * Flag apakah user bisa manage members.
     *
     * @var bool
     */
    public bool $canManageMembers = false;

    /**
     * Event listeners untuk refresh komponen.
     *
     * @var array
     */
    protected $listeners = ['member-updated' => '$refresh'];

    /**
     * Aturan validasi untuk add member.
     *
     * @return array<string, mixed> Rules validasi
     */
    protected function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }

    /**
     * Inisialisasi komponen dan cek permission.
     *
     * @param Project $project Project yang akan dikelola membernya
     * @return void
     */
    public function mount(Project $project): void
    {
        $this->project = $project;
        $this->canManageMembers = Auth::user()->can('update', $this->project);
    }

    /**
     * Render komponen dengan list members.
     *
     * @return View View project members
     */
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

    /**
     * Tambahkan member baru ke project.
     * 
     * Cek authorization, validasi email, dan pastikan user belum menjadi member.
     *
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika tidak berhak update
     */
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

    /**
     * Update role member di project.
     * 
     * Owner tidak bisa diubah rolenya.
     *
     * @param int $userId ID user yang rolenya akan diupdate
     * @param string $role Role baru (Admin/Member)
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika tidak berhak update
     */
    public function updateRole($userId, $role)
    {
        $this->authorize('update', $this->project);

        if (!in_array($role, ['Admin', 'Member'])) return;
        
        if ($userId === $this->project->owner_id) return;

        $this->project->members()->updateExistingPivot($userId, ['role' => $role]);
        $this->dispatch('member-updated');
    }

    /**
     * Remove member dari project.
     * 
     * Tidak bisa remove owner atau diri sendiri.
     *
     * @param int $userId ID user yang akan diremove
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika tidak berhak update
     */
    public function removeMember($userId)
    {
        $this->authorize('update', $this->project);

        if ($userId === $this->project->owner_id) return;
        
        if ($userId === Auth::id()) return;

        $this->project->members()->detach($userId);
        $this->dispatch('member-updated');
    }
}