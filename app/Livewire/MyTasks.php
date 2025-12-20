<?php

namespace App\Livewire;

use App\Models\Task;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

/**
 * Komponen Livewire untuk menampilkan task yang assigned ke user.
 * 
 * Menampilkan semua task yang belum selesai (status != Done) untuk user yang login.
 */
class MyTasks extends Component
{
    use WithPagination;

    /**
     * Theme pagination yang digunakan.
     *
     * @var string
     */
    protected $paginationTheme = 'bootstrap';

    /**
     * Render komponen dengan list task user.
     * 
     * Task diurutkan berdasarkan due date dan priority.
     *
     * @return \Illuminate\View\View View my tasks
     */
    #[Layout('layouts.app-with-sidebar')] 
    public function render()
    {
        $userId = Auth::id();

        $tasks = Task::with(['project', 'project.owner'])
            ->where('assignee_id', $userId)
            ->whereHas('project', function ($query) use ($userId) {
                $query->where('owner_id', $userId)
                      ->orWhereHas('members', function ($m) use ($userId) {
                          $m->where('user_id', $userId);
                      });
            })
            ->where('status', '!=', 'Done')
            ->orderByRaw('ISNULL(due_date), due_date ASC')
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->paginate(10);

        return view('livewire.my-tasks', [
            'tasks' => $tasks
        ]);
    }
}