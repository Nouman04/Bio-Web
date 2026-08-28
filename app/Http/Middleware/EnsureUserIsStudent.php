<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the student portal to students. Staff who wander in are sent back to
 * the admin dashboard rather than shown a dead end.
 */
class EnsureUserIsStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isStudent()) {
            return $next($request);
        }

        if ($user?->isStaff()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'The student portal is not available to admin accounts.'], 403)
                : redirect()->route('dashboard')
                    ->with('error', 'The student portal is not available to admin accounts.');
        }

        // No role at all — nowhere sensible to send them, so say so plainly.
        abort(403, 'This account has no access to the student portal.');
    }
}
