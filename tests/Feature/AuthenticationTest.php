<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUserCanLoginWithTheRightCredentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(201);
    }

    public function testUserCannotLoginWithTheWrongCredentials()
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'adminsecret'
        ]);

        $response->assertStatus(422);
    }

    public function testUserCanRegisterWithCorrectCredentials()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Godswill',
            'email' => 'chisom5711@gmail.com',
            'password' => '123456789a',
            'password_confirmation' => '123456789a'
        ]);

        $response->assertStatus(201)->assertJsonStructure([
            'access_token',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Godswill',
            'email' => 'chisom5711@gmail.com',
        ]);
    }

    public function testUserCannotRegisterWithWrongCredentials()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Godswill',
            'email' => 'alert@gmail.com',
            'password' => '1234',
            'password_confirmation' => '1234'
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('users', [
            'name' => 'Godswill',
            'email' => 'chisom5711@gmail.com',
        ]);
    }
}
