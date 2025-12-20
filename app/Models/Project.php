<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model untuk project management.
 * 
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Project extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'description',
    ];

    /**
     * Boot method untuk model events.
     * 
     * Cleanup tasks dan members saat project dihapus.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function (Project $project) {
            $project->tasks()->delete();
            $project->members()->detach();
        });
    }

    /**
     * Relasi ke user yang memiliki project.
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relasi many-to-many ke users sebagai members.
     *
     * @return BelongsToMany
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members');
    }

    /**
     * Relasi ke tasks dalam project.
     *
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}