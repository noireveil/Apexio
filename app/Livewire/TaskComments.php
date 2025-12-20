<?php

namespace App\Livewire;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Komponen Livewire untuk mengelola komentar task.
 * 
 * Menampilkan list komentar dan menangani penambahan komentar baru.
 */
class TaskComments extends Component
{
    /**
     * Instance task yang komentarnya ditampilkan.
     *
     * @var Task
     */
    public Task $task;

    /**
     * Collection komentar task.
     *
     * @var Collection
     */
    public $comments; 

    /**
     * Isi komentar baru yang akan ditambahkan.
     *
     * @var string
     */
    public string $newComment = '';

    /**
     * Aturan validasi untuk komentar baru.
     *
     * @return array<string, mixed> Rules validasi
     */
    protected function rules(): array
    {
        return [
            'newComment' => 'required|string|max:1000',
        ];
    }

    /**
     * Inisialisasi komponen dan load komentar.
     *
     * @param Task $task Task yang komentarnya akan ditampilkan
     * @return void
     */
    public function mount(Task $task): void
    {
        $this->task = $task;
        $this->loadComments();
    }

    /**
     * Simpan komentar baru ke database.
     * 
     * Validasi input, buat komentar, reset form, dan reload komentar.
     *
     * @return void
     */
    public function saveComment(): void
    {
        $this->validate();
        $this->task->comments()->create([
            'user_id' => Auth::id(),
            'body' => $this->newComment,
        ]);
        $this->newComment = ''; 
        $this->reset('newComment');
        $this->loadComments();
        $this->dispatch('task-updated'); 
    }

    /**
     * Load komentar dari database.
     * 
     * Mengambil komentar terbaru dengan relasi user.
     *
     * @return void
     */
    public function loadComments(): void
    {
        $this->comments = $this->task->comments()
                                  ->with('user')
                                  ->latest()
                                  ->get();
    }

    /**
     * Render komponen komentar task.
     *
     * @return View View task comments
     */
    public function render(): View
    {
        return view('livewire.task-comments');
    }
}
