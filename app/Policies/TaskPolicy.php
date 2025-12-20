<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

/**
 * Policy untuk authorization task.
 * 
 * Menentukan siapa yang bisa view, create, update, dan delete task.
 */
class TaskPolicy
{
    /**
     * Cek apakah user bisa view any tasks.
     *
     * @param User $user User yang akan dicek
     * @return bool Selalu true untuk semua user
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Cek apakah user bisa view task tertentu.
     * 
     * User bisa view jika dia member dari project task.
     *
     * @param User $user User yang akan dicek
     * @param Task $task Task yang akan di-view
     * @return bool True jika user berhak view
     */
    public function view(User $user, Task $task): bool
    {
        return $this->isProjectMember($user, $task);
    }

    /**
     * Cek apakah user bisa create task.
     * 
     * Note: Parameter kedua adalah Project karena task belum ada.
     * Semua member project bisa create task.
     *
     * @param User $user User yang akan dicek
     * @param Project $project Project tempat task akan dibuat
     * @return bool True jika user berhak create
     */
    public function create(User $user, Project $project): bool
    {
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Cek apakah user bisa update task.
     * 
     * Semua member project bisa update task (ubah status, dll).
     *
     * @param User $user User yang akan dicek
     * @param Task $task Task yang akan di-update
     * @return bool True jika user berhak update
     */
    public function update(User $user, Task $task): bool
    {
        return $this->isProjectMember($user, $task);
    }

    /**
     * Cek apakah user bisa delete task.
     * 
     * Hanya admin project yang bisa delete task.
     *
     * @param User $user User yang akan dicek
     * @param Task $task Task yang akan dihapus
     * @return bool True jika user berhak delete
     */
    public function delete(User $user, Task $task): bool
    {
        return $this->isProjectAdmin($user, $task);
    }

    /**
     * Cek apakah user bisa restore task yang dihapus.
     *
     * @param User $user User yang akan dicek
     * @param Task $task Task yang akan direstore
     * @return bool Selalu false karena tidak ada soft delete
     */
    public function restore(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Cek apakah user bisa force delete task.
     *
     * @param User $user User yang akan dicek
     * @param Task $task Task yang akan di-force delete
     * @return bool Selalu false karena tidak ada soft delete
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return false;
    }

    /**
     * Helper method: Cek apakah user adalah admin project.
     *
     * @param User $user User yang akan dicek
     * @param Task $task Task untuk mengakses project
     * @return bool True jika user adalah admin project
     */
    private function isProjectAdmin(User $user, Task $task): bool
    {
        return $task->project->members()
                       ->where('user_id', $user->id)
                       ->where('role', 'Admin')
                       ->exists();
    }

    /**
     * Helper method: Cek apakah user adalah member project.
     *
     * @param User $user User yang akan dicek
     * @param Task $task Task untuk mengakses project
     * @return bool True jika user adalah member project
     */
    private function isProjectMember(User $user, Task $task): bool
    {
        return $task->project->members()->where('user_id', $user->id)->exists();
    }
}