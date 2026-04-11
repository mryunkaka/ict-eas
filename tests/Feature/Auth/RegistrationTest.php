<?php

namespace Tests\Feature\Auth;

use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $unit = Unit::create(['code' => 'UNIT-01', 'name' => 'Unit 01', 'type' => 'unit', 'is_active' => true]);

        $response = $this->post('/register', [
            'unit_id' => $unit->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
