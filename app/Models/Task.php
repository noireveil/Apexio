<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model untuk task dalam project.
 * 
 * @property int $id
 * @property int $project_id
 * @property int|null $assignee_id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string $priority
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Task extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'assignee_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    /**
     * Casting atribut ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
    ];

    /**
     * Boot method untuk model events.
     * 
     * Cleanup comments saat task dihapus.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::deleting(function (Task $task) {
            $task->comments()->delete();
        });
    }

    /**
     * Relasi ke project yang memiliki task.
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relasi ke user yang di-assign ke task.
     *
     * @return BelongsTo
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Relasi ke comments pada task.
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}