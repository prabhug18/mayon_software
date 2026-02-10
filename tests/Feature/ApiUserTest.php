<?php
namespace Tests\Feature;


use Tests\TestCase;
use App\Models\User;

class ApiUserTest extends TestCase
{
   

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(); // Disable all middleware
        \Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
            '--provider' => 'users',
        ]);
    }

    public function test_api_can_fetch_user_list()
    {
        User::factory()->count(3)->create();
        $user = User::first();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         '*' => ['id', 'name', 'email', 'status', 'created_at', 'updated_at']
                     ]
                 ]);
    }
}
