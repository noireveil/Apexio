<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

/**
 * Komponen Livewire untuk mengelola project.
 * 
 * Menampilkan list project dan menangani create/delete project.
 */
class ManageProjects extends Component
{
    /**
     * Nama project.
     *
     * @var string|null
     */
    public $name;

    /**
     * Deskripsi project.
     *
     * @var string|null
     */
    public $description;

    /**
     * Status aktif project.
     *
     * @var bool
     */
    public $is_active = true;

    /**
     * Event listeners untuk refresh komponen.
     *
     * @var array
     */
    protected $listeners = ['project-created' => '$refresh', 'project-deleted' => '$refresh'];

    /**
     * Render komponen dengan list project user.
     *
     * @return \Illuminate\View\View View manage projects
     */
    public function render()
    {
        return view('livewire.manage-projects', [
            'projects' => Auth::user()->projects()->latest()->get()
        ]);
    }

    /**
     * Buka modal create project dan reset form.
     *
     * @return void
     */
    public function openCreateModal()
    {
        $this->reset(['name', 'description']);
        $this->dispatch('open-create-modal'); 
    }

    /**
     * Simpan project baru ke database.
     * 
     * Validasi input, buat project, dan tambahkan owner sebagai admin member.
     *
     * @return void
     */
    public function saveProject()
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
            'description' => 'nullable|string'
        ]);

        $project = Project::create([
            'owner_id' => Auth::id(),
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $project->members()->attach(Auth::id(), ['role' => 'Admin']);

        $this->reset(['name', 'description']);
        $this->dispatch('close-create-modal');
        
        $this->dispatch('project-created');
    }

    /**
     * Hapus project dari database.
     * 
     * Cek authorization sebelum delete.
     *
     * @param int $projectId ID project yang akan dihapus
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika tidak berhak delete
     */
    public function deleteProject($projectId)
    {
        $project = Project::findOrFail($projectId);

        $this->authorize('delete', $project);

        $project->delete();
        
        $this->dispatch('project-deleted'); 
    }
}