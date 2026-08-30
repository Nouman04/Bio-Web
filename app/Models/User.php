<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasUuid;
use App\Notifications\ResetPassword;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuid, Searchable , Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Whether this account belongs to the student portal. Role names are
     * compared case-insensitively because the seeders disagree on casing
     * ("student" vs "Student").
     */
    public function isStudent(): bool
    {
        return $this->hasRoleNamed('student');
    }

    /**
     * Whether this account is an admin, as opposed to an instructor. Admins
     * answer for every course; an instructor only for what they created.
     */
    public function isAdmin(): bool
    {
        return $this->hasRoleNamed('admin');
    }

    /**
     * Every quiz this user has sat.
     */
    public function quizAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuizUserAttempt::class);
    }

    /**
     * Whether this account may use the admin panel: anyone holding a role that
     * is not the student role — admin or instructor.
     */
    public function isStaff(): bool
    {
        return $this->roles->contains(fn ($role) => strtolower($role->name) !== 'student');
    }

    /**
     * Case-insensitive role check.
     */
    private function hasRoleNamed(string $name): bool
    {
        return $this->roles->contains(fn ($role) => strtolower($role->name) === strtolower($name));
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    /**
     * Sends the branded reset email rather than the framework's default one.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPassword($token));
    }
}