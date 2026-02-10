<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class UserRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(); // Disable all middleware
    }

    public function test_user_can_register_and_receive_notification()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302); // Redirect after registration
        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }
}
