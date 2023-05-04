<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/profile');

        $response->assertStatus(200)->assertJsonStructure([
            'name', 'email'
        ])->assertJsonFragment([
            'name' => $user->name
        ])->assertJsonCount(2);
    }

    public function testUserCanUpdateNameAndEmail()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->putJson('/api/v1/profile', [
            'name' => "Dr Alert",
            'email' => "dr@mail.com"
        ]);

        $response->assertStatus(202)->assertJsonStructure(['name', 'email'])->assertJsonCount(2)->assertJsonFragment([
            'name' => "Dr Alert",
            'email' => "dr@mail.com"
        ]);

        $this->assertDatabaseHas('users', [
            'name' => "Dr Alert",
            'email' => "dr@mail.com"
        ]);
    }

    public function testUserCanChangePassword()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->putJson('/api/v1/password', [
            'current_password' => 'password',
            'password' => 'another_password',
            'password_confirmation' => 'another_password'
        ]);

        $response->assertStatus(202);
    }

    // public function testUnauthenticatedUserCannotAccessProfile()
    // {
    //     $user = User::factory()->create();
    //     $anotherUser = "Goddy";
            
    
    //     $response = $this->actingAs($anotherUser)->getJson('/api/v1/profile');

    //     $response->assertStatus(402);
    // }
}
