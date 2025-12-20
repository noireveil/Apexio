<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

/**
 * Model untuk user/pengguna aplikasi.
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property bool $is_admin
 * @property string|null $avatar_path
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'avatar_path',
    ];

    /**
     * Atribut yang disembunyikan saat serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut ke tipe data tertentu.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Cek apakah user sedang online.
     * 
     * Berdasarkan cache yang di-set oleh TrackUserActivity middleware.
     *
     * @return bool True jika user online dalam 2 menit terakhir
     */
    public function isOnline()
    {
        return Cache::has('user-is-online-' . $this->id);
    }

    /**
     * Relasi many-to-many ke projects sebagai member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_members')->withPivot('role');
    }

    /**
     * Relasi ke projects yang dimiliki user (sebagai owner).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    /**
     * Relasi ke tasks yang di-assign ke user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    /**
     * Accessor untuk mendapatkan URL avatar user.
     *
     * @return string|null URL avatar atau null jika tidak ada
     */
    public function getAvatarUrlAttribute()
    {
        return $this->avatar_path 
            ? asset('storage/' . $this->avatar_path) 
            : null;
    }
}