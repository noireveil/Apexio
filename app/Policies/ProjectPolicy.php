<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/**
 * Policy untuk authorization project.
 * 
 * Menentukan siapa yang bisa view, update, dan delete project.
 */
class ProjectPolicy
{
    /**
     * Cek apakah user bisa view project.
     * 
     * User bisa view jika dia owner atau member project.
     *
     * @param User $user User yang akan dicek
     * @param Project $project Project yang akan di-view
     * @return bool True jika user berhak view
     */
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id || 
               $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Cek apakah user bisa update project.
     * 
     * User bisa update jika dia owner atau member dengan role Admin.
     *
     * @param User $user User yang akan dicek
     * @param Project $project Project yang akan di-update
     * @return bool True jika user berhak update
     */
    public function update(User $user, Project $project): bool
    {
        if ($user->id === $project->owner_id) {
            return true;
        }

        return $project->members()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'Admin')
            ->exists();
    }

    /**
     * Cek apakah user bisa delete project.
     * 
     * Hanya owner yang bisa delete project.
     *
     * @param User $user User yang akan dicek
     * @param Project $project Project yang akan dihapus
     * @return bool True jika user berhak delete
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->owner_id;
    }
}