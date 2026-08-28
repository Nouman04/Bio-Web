<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The bell, and the page behind it.
 *
 * Shared by staff and students: a notification belongs to a user, not to an
 * area of the site, so both read them the same way and the list view picks its
 * layout from who is signed in.
 */
class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    /**
     * What the bell drops down, and the unread count beside it. Polled as a
     * fallback when the websocket is not connected.
     */
    public function feed(Request $request): JsonResponse
    {
        return response()->json(
            $this->notifications->feed($request->user())
        );
    }

    /**
     * The full list, searchable and filterable by read state.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $filters = [
            'search' => trim((string) $request->input('search')),
            'state' => $request->input('state'),
        ];

        $notifications = $this->notifications->listing($user, $filters);

        return view('notifications.index', [
            'notifications' => $notifications,
            // The presenter shapes each row; the paginator keeps its links.
            'rows' => $notifications->getCollection()
                ->map(fn ($row) => $this->notifications->present($row)),
            'filters' => $filters,
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Opens one notification: marks it read, then goes where it points.
     *
     * This is the "view in detail" path — the row's own link — so following it
     * clears the badge without a separate step.
     */
    public function show(Request $request, string $notification)
    {
        $record = $this->notifications->markRead($request->user(), $notification);

        abort_if($record === null, 404);

        $url = $record->data['url'] ?? null;

        return redirect($url ?: route('notifications'));
    }

    /**
     * Marks one as read — the ✕ on a row in the bell.
     */
    public function read(Request $request, string $notification): JsonResponse
    {
        $record = $this->notifications->markRead($request->user(), $notification);

        abort_if($record === null, 404);

        return response()->json([
            'message' => 'Marked as read.',
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Puts one back to unread, so a row read by mistake can be restored.
     */
    public function unread(Request $request, string $notification): JsonResponse
    {
        $record = $this->notifications->markUnread($request->user(), $notification);

        abort_if($record === null, 404);

        return response()->json([
            'message' => 'Marked as unread.',
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Clears the badge in one go.
     */
    public function readAll(Request $request): JsonResponse
    {
        $cleared = $this->notifications->markAllRead($request->user());

        return response()->json([
            'message' => $cleared === 1 ? '1 notification marked as read.' : "{$cleared} notifications marked as read.",
            'unread' => 0,
        ]);
    }

    /**
     * Removes one for good.
     */
    public function destroy(Request $request, string $notification): JsonResponse
    {
        abort_unless($this->notifications->delete($request->user(), $notification), 404);

        return response()->json([
            'message' => 'Notification removed.',
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
