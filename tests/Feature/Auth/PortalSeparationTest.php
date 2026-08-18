<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortalSeparationTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $user = User::factory()->create(['password' => bcrypt('test@123')]);
        $user->assignRole(Role::firstOrCreate(['name' => 'student']));

        return $user;
    }

    private function admin(): User
    {
        $user = User::factory()->create(['password' => bcrypt('test@123')]);
        $user->assignRole(Role::firstOrCreate(['name' => 'admin']));

        return $user;
    }

    public function test_student_cannot_sign_in_from_the_admin_login(): void
    {
        $student = $this->student();

        $response = $this->from('/login')->post('/login', [
            'email' => $student->email,
            'password' => 'test@123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_student_signs_in_from_the_student_login_and_lands_on_the_student_dashboard(): void
    {
        $student = $this->student();

        $response = $this->post('/student/login', [
            'email' => $student->email,
            'password' => 'test@123',
        ]);

        $response->assertRedirect(route('student.dashboard', absolute: false));
        $this->assertAuthenticatedAs($student);
    }

    public function test_admin_cannot_sign_in_from_the_student_login(): void
    {
        $admin = $this->admin();

        $response = $this->from('/student/login')->post('/student/login', [
            'email' => $admin->email,
            'password' => 'test@123',
        ]);

        $response->assertRedirect('/student/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_signs_in_from_the_admin_login_and_lands_on_the_admin_dashboard(): void
    {
        $admin = $this->admin();

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'test@123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_cannot_open_the_student_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get('/student/dashboard')
            ->assertRedirect(route('dashboard'));
    }

    public function test_student_cannot_open_the_admin_dashboard(): void
    {
        $this->actingAs($this->student())
            ->get('/dashboard')
            ->assertRedirect(route('student.dashboard'));
    }

    public function test_student_cannot_open_an_admin_page(): void
    {
        $this->actingAs($this->student())
            ->get('/questions')
            ->assertRedirect(route('student.dashboard'));
    }

    public function test_guests_are_sent_to_the_login_matching_the_area(): void
    {
        $this->get('/student/dashboard')->assertRedirect(route('student.login'));
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_signed_in_users_are_sent_home_from_the_login_pages(): void
    {
        $this->actingAs($this->student())->get('/login')->assertRedirect(route('student.dashboard'));
        $this->actingAs($this->admin())->get('/student/login')->assertRedirect(route('dashboard'));
    }
}
