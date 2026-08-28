<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\User;
use App\Notifications\NewQuizPublished;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Reading and clearing a person's notifications, and announcing a new quiz to
 * the students who can sit it.
 *
 * Notifications are written by the notification classes themselves; this only
 * reads them back and decides who a new quiz should reach.
 */
class NotificationService
{
    /**
     * How many the bell shows before "view all" is the only way to see more.
     */
    public const BELL_LIMIT = 10;

    /**
     * An icon per notification type, so a glance tells you what it is. Falls
     * back to a bell for anything added later.
     */
    private const ICONS = [
        'quiz.awaiting_review' => 'fa-solid fa-pen-to-square',
        'quiz.graded' => 'fa-solid fa-square-check',
        'quiz.published' => 'fa-solid fa-clipboard-question',
    ];

    /**
     * What the bell drops down: the newest few, and how many are unread.
     *
     * @return array{items: array<int, array<string, mixed>>, unread: int}
     */
    public function feed(User $user, int $limit = self::BELL_LIMIT): array
    {
        return [
            'items' => $user->notifications()
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn (DatabaseNotification $row) => $this->present($row))
                ->all(),
            'unread' => $user->unreadNotifications()->count(),
        ];
    }

    /**
     * The full list behind the bell, searchable and filterable by whether it
     * has been read.
     *
     * @param  array{search?:string|null, state?:string|null}  $filters
     */
    public function listing(User $user, array $filters = []): LengthAwarePaginator
    {
        $notifications = $user->notifications()->getQuery();

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            // The payload is JSON in a text column, so the search runs against
            // it directly rather than against columns that do not exist.
            $notifications->where('data', 'like', "%{$search}%");
        }

        match ($filters['state'] ?? null) {
            'unread' => $notifications->whereNull('read_at'),
            'read' => $notifications->whereNotNull('read_at'),
            default => null,
        };

        return $notifications->latest()->paginate(15)->withQueryString();
    }

    /**
     * One notification of this user's, or nothing.
     */
    public function find(User $user, string $id): ?DatabaseNotification
    {
        return $user->notifications()->whereKey($id)->first();
    }

    public function markRead(User $user, string $id): ?DatabaseNotification
    {
        $notification = $this->find($user, $id);

        $notification?->markAsRead();

        return $notification;
    }

    public function markUnread(User $user, string $id): ?DatabaseNotification
    {
        $notification = $this->find($user, $id);

        $notification?->forceFill(['read_at' => null])->save();

        return $notification;
    }

    public function markAllRead(User $user): int
    {
        $count = $user->unreadNotifications()->count();

        $user->unreadNotifications->markAsRead();

        return $count;
    }

    public function delete(User $user, string $id): bool
    {
        return (bool) $user->notifications()->whereKey($id)->delete();
    }

    /**
     * One notification in the shape the bell and the list both use.
     *
     * @return array<string, mixed>
     */
    public function present(DatabaseNotification $row): array
    {
        $data = $row->data ?? [];

        return [
            'id' => $row->id,
            'type' => $data['type'] ?? null,
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'url' => $data['url'] ?? null,
            'icon' => self::ICONS[$data['type'] ?? ''] ?? 'fa-regular fa-bell',
            'read' => $row->read_at !== null,
            'created_at' => $row->created_at?->toIso8601String(),
            'ago' => $row->created_at?->diffForHumans(),
        ];
    }

    /* ── Announcing a new quiz ──────────────────────────────────────────── */

    /**
     * Tells everyone subscribed to the course a quiz sits under that it is
     * there.
     *
     * A draft is not announced, and neither is a quiz that has not been put on
     * a chapter yet — there would be nowhere to send anyone.
     *
     * @return int how many students were told
     */
    public function announceQuiz(Quiz $quiz): int
    {
        if ($quiz->status !== 'published') {
            return 0;
        }

        $chapter = $quiz->chapters()->with('course')->first();
        $course = $chapter?->course;

        if (! $course) {
            return 0;
        }

        $students = $this->subscribersOf($course->uuid);

        foreach ($students as $student) {
            $student->notify(new NewQuizPublished($quiz, $chapter));
        }

        return $students->count();
    }

    /**
     * Everyone holding a live subscription to one course.
     *
     * @return Collection<int, User>
     */
    public function subscribersOf(string $courseUuid): Collection
    {
        return User::query()
            ->whereHas('subscriptions', fn (Builder $q) => $q
                ->where('type', 'course_' . $courseUuid)
                ->where(fn ($valid) => $valid
                    ->whereIn('stripe_status', ['active', 'trialing'])
                    ->orWhere(fn ($grace) => $grace
                        ->whereNotNull('ends_at')
                        ->where('ends_at', '>', now()))))
            ->get();
    }

    /**
     * The channel the browser listens on for this user.
     */
    public function channelFor(User $user): string
    {
        return 'private-App.Models.User.' . $user->id;
    }

    /**
     * Laravel broadcasts notifications under this event name.
     */
    public function eventName(): string
    {
        return Str::start('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', '');
    }
}
