<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(); // Disable all middleware
    }

    public function test_example(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}
