<?php

namespace App\Livewire;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class MyTasks extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Layout('layouts.app-with-sidebar')] 
    public function render()
    {
        // Fetch tasks assigned to the current user, excluding completed ones
        $tasks = Task::with('project')
            ->where('assignee_id', Auth::id())
            ->where('status', '!=', 'Done')
            ->orderBy('due_date', 'asc')
            ->paginate(10);

        return view('livewire.my-tasks', [
            'tasks' => $tasks
        ]);
    }
}