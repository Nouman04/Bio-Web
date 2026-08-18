<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Exception;

class GoogleController extends Controller
{
    // Redirect user to Google sign-in page
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle callback from Google
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Find existing user by google_id or email
            $user = User::where('google_id', $googleUser->getId())
                        ->orWhere('email', $googleUser->getEmail())
                        ->first();

            if (! $user) {
                // Create a new user if one doesn't exist — Google sign-in is only
                // offered on the student pages, so new accounts are students.
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)), // Assign random hashed password
                ]);

                $user->assignRole(Role::firstOrCreate(['name' => 'student']));
            } else if (empty($user->google_id)) {
                // Link Google ID if existing account logged in with email
                $user->update(['google_id' => $googleUser->getId()]);
            }

            Auth::login($user);

            $home = $user->isStudent() ? route('student.dashboard') : route('dashboard');

            return redirect()->intended($home);

        } catch (Exception $e) {
            return redirect()->route('student.login')->with('error', 'Google authentication failed.');
        }
    }
}