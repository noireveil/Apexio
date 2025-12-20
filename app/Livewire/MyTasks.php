<?php

namespace App\Livewire;

use App\Models\Task;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class MyTasks extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

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