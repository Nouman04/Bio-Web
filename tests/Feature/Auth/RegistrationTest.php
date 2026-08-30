<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'new-passw0rd!',
            'password_confirmation' => 'new-passw0rd!',
        ]);

        $this->assertAuthenticated();
        // Registration is the student portal's sign-up, so new accounts are
        // students and land on the student dashboard.
        $this->assertTrue(auth()->user()->isStudent());
        $response->assertRedirect(route('student.dashboard', absolute: false));
    }
}
