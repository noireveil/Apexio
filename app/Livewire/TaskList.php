<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Komponen Livewire untuk mengelola task dalam project.
 * 
 * Menampilkan kanban board, CRUD task, dan update status task dengan drag & drop.
 */
class TaskList extends Component
{
    /**
     * Opsi status task yang tersedia.
     *
     * @var array
     */
    public const STATUS_OPTIONS = ['To-Do', 'In-Progress', 'Done'];

    /**
     * Instance project yang tasknya ditampilkan.
     *
     * @var Project
     */
    public Project $project;

    /**
     * Collection members project.
     *
     * @var Collection
     */
    public Collection $members;

    /**
     * Judul task baru.
     *
     * @var string
     */
    public string $title = '';

    /**
     * Deskripsi task baru.
     *
     * @var string|null
     */
    public ?string $description = null;

    /**
     * ID user yang di-assign untuk task.
     *
     * @var int|null
     */
    public ?int $assignee_id = null;

    /**
     * Prioritas task.
     *
     * @var string
     */
    public string $priority = 'medium';

    /**
     * Status task.
     *
     * @var string
     */
    public string $status = 'To-Do';

    /**
     * Due date task.
     *
     * @var string|null
     */
    public ?string $due_date = null;

    /**
     * Flag apakah user bisa create tasks.
     *
     * @var bool
     */
    public bool $canCreateTasks = false;

    /**
     * Flag apakah user bisa delete tasks.
     *
     * @var bool
     */
    public bool $canDeleteTasks = false; 

    /**
     * Task yang sedang ditampilkan komentarnya.
     *
     * @var Task|null
     */
    public ?Task $taskWithComments = null;

    /**
     * Event listeners untuk refresh komponen.
     *
     * @var array
     */
    protected $listeners = [
        'task-updated' => '$refresh'
    ];

    /**
     * Aturan validasi untuk task.
     *
     * @return array<string, mixed> Rules validasi
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:To-Do,In-Progress,Done',
            'due_date' => 'nullable|date',
        ];
    }

    /**
     * Inisialisasi komponen dan cek permissions.
     *
     * @param int $project_id ID project yang tasknya akan ditampilkan
     * @return void
     */
    public function mount(int $project_id): void
    {
        $this->project = Project::findOrFail($project_id);
        $this->members = $this->project->members()->get();

        $user = Auth::user();
        $this->canCreateTasks = $user->can('create', [Task::class, $this->project]);
        $this->canDeleteTasks = $user->can('update', $this->project);
    }

    /**
     * Render komponen dengan tasks yang dikelompokkan berdasarkan status.
     *
     * @return View View task list
     */
    public function render(): View
    {
        $allTasks = $this->project->tasks()
            ->with(['assignee'])
            ->withCount('comments')
            ->latest()
            ->get();

        return view('livewire.task-list', [
            'todoTasks' => $allTasks->where('status', 'To-Do'),
            'inProgressTasks' => $allTasks->where('status', 'In-Progress'),
            'doneTasks' => $allTasks->where('status', 'Done'),
        ]);
    }

    /**
     * Load dan tampilkan komentar untuk task tertentu.
     *
     * @param int $taskId ID task yang komentarnya akan ditampilkan
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika tidak berhak view
     */
    public function loadComments(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $this->authorize('view', $task);
        $this->taskWithComments = $task;
        $this->dispatch('open-modal', 'commentsModal');
    }

    /**
     * Tutup modal komentar.
     *
     * @return void
     */
    public function closeCommentModal(): void
    {
        $this->dispatch('close-modal', 'commentsModal');
        $this->taskWithComments = null;
    }

    /**
     * Simpan task baru ke database.
     * 
     * Validasi input, cek authorization, buat task, dan reset form.
     *
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika tidak berhak create
     */
    public function saveTask(): void
    {
        $this->authorize('create', [Task::class, $this->project]);

        $validatedData = $this->validate();

        $this->project->tasks()->create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'priority' => $validatedData['priority'],
            'status' => $validatedData['status'],
            'assignee_id' => $validatedData['assignee_id'],
            'due_date' => $validatedData['due_date'],
        ]);

        $this->reset('title', 'description', 'assignee_id', 'priority', 'status', 'due_date');
        $this->priority = 'medium';
        $this->status = 'To-Do';

        $this->dispatch('close-create-modal');
        $this->dispatch('task-notification', message: 'Task created successfully!', type: 'success');
    }

    /**
     * Update status task (untuk drag & drop).
     * 
     * Cek authorization dan pastikan user hanya bisa move task sendiri.
     *
     * @param int $task_id ID task yang statusnya akan diupdate
     * @param string $status Status baru
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika tidak berhak update
     */
    public function updateTaskStatus(int $task_id, string $status): void
    {
        if (!in_array($status, self::STATUS_OPTIONS)) {
            return;
        }

        $task = Task::find($task_id);
        
        $this->authorize('update', $task);

        if ($task->assignee_id && $task->assignee_id !== Auth::id() && !$this->canDeleteTasks) {
            $this->dispatch('task-notification', message: 'You can only move your own tasks!', type: 'error');
            return;
        }

        $oldStatus = $task->status;
        $task->status = $status;
        $task->save();

        if ($oldStatus !== $status) {
            $this->dispatch('task-notification', message: "Task moved to {$status}", type: 'info');
        }
    }

    /**
     * Hapus task dari database.
     *
     * @param int $task_id ID task yang akan dihapus
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika tidak berhak delete
     */
    public function deleteTask(int $task_id): void
    {
        $task = Task::find($task_id);
        $this->authorize('delete', $task);
        $task->delete();
        $this->dispatch('task-notification', message: "Task deleted", type: 'error');
    }
}