<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Display the student login view.
     */
    public function createStudent(): View
    {
        return view('auth.student-login');
    }

    /**
     * Handle a sign-in from the admin login page. Students belong to the
     * student portal, so their credentials are refused here even when correct.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (Auth::user()->isStudent()) {
            return $this->rejectPortal(
                $request,
                'Students sign in from the student portal.'
            );
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Handle a sign-in from the student login page. Only students get through;
     * admin and instructor accounts are sent back to their own login.
     */
    public function storeStudent(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (! Auth::user()->isStudent()) {
            return $this->rejectPortal(
                $request,
                'Admin accounts sign in from the administrator portal.'
            );
        }

        $request->session()->regenerate();

        return redirect()->intended(route('student.dashboard', absolute: false));
    }

    /**
     * Undo a sign-in made through the wrong portal and explain why.
     */
    private function rejectPortal(LoginRequest $request, string $message): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $message]);
    }

    /**
     * Destroy an authenticated session, returning to the portal the user came
     * from rather than a shared landing page.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $wasStudent = $request->user()?->isStudent();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route($wasStudent ? 'student.login' : 'admin.login');
    }
}
