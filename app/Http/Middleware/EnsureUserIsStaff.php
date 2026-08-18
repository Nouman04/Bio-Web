<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the admin panel to staff. A student who lands here is sent back to
 * their own dashboard rather than shown a dead end.
 */
class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isStaff()) {
            return $next($request);
        }

        if ($user?->isStudent()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'The admin panel is not available to students.'], 403)
                : redirect()->route('student.dashboard')
                    ->with('error', 'The admin panel is not available to students.');
        }

        // No role at all — nowhere sensible to send them, so say so plainly.
        abort(403, 'This account has no access to the admin panel.');
    }
}
