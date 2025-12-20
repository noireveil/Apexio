<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen Livewire untuk dashboard admin.
 * 
 * Menampilkan statistik aplikasi dan mengelola pengguna (CRUD, reset password, toggle admin).
 */
class AdminDashboard extends Component
{
    use WithPagination;

    /**
     * Query pencarian untuk filter user.
     *
     * @var string
     */
    public $search = '';

    /**
     * Theme pagination yang digunakan.
     *
     * @var string
     */
    protected $paginationTheme = 'bootstrap';

    /**
     * Inisialisasi komponen dan cek akses admin.
     * 
     * Hanya admin yang bisa mengakses dashboard ini.
     *
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Jika bukan admin
     */
    #[Layout('layouts.app-with-sidebar')] 
    public function mount()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Unauthorized access.');
        }
    }

    /**
     * Reset pagination saat search berubah.
     *
     * @return void
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Reset password user menjadi 'password123'.
     *
     * @param int $userId ID user yang akan direset passwordnya
     * @return void
     */
    public function resetPassword($userId)
    {
        $user = User::findOrFail($userId);
        
        $user->password = Hash::make('password123');
        $user->save();

        $this->dispatch('alert', type: 'success', message: "Password reset for {$user->name}");
    }

    /**
     * Toggle status admin user.
     * 
     * Tidak bisa toggle admin untuk diri sendiri.
     *
     * @param int $userId ID user yang akan ditoggle status adminnya
     * @return void
     */
    public function toggleAdmin($userId)
    {
        if ($userId === Auth::id()) return;

        $user = User::findOrFail($userId);
        $user->is_admin = !$user->is_admin;
        $user->save();
        
        $role = $user->is_admin ? 'Admin' : 'User';
        $this->dispatch('alert', type: 'info', message: "User promoted to {$role}");
    }

    /**
     * Hapus user dari sistem.
     * 
     * Tidak bisa menghapus diri sendiri.
     *
     * @param int $userId ID user yang akan dihapus
     * @return void
     */
    public function deleteUser($userId)
    {
        if ($userId === Auth::id()) return;
        
        User::findOrFail($userId)->delete();
        $this->dispatch('alert', type: 'error', message: "User deleted successfully");
    }

    /**
     * Render komponen dengan data user dan statistik.
     *
     * @return \Illuminate\View\View View dashboard admin
     */
    public function render()
    {
        $users = User::query()
            ->where('name', 'like', '%'.$this->search.'%')
            ->orWhere('email', 'like', '%'.$this->search.'%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin-dashboard', [
            'stats' => [
                'users' => User::count(),
                'projects' => Project::count(),
                'tasks' => Task::count(),
                'active_tasks' => Task::where('status', '!=', 'Done')->count(),
            ],
            'users' => $users
        ]);
    }
}