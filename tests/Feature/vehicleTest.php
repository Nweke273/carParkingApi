<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class vehicleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUserCanGetTheirOwnVehicles()
    {
        $som = User::factory()->create();

        $somVehicle = Vehicle::factory()->create([
            'user_id' => $som->id,

        ]);

        $sycamor = User::factory()->create();
        $sycamorVehicle = Vehicle::factory()->create([
            'user_id' => $sycamor->id
        ]);

        $response = $this->actingAs($som)->getJson('/api/v1/vehicles');
        $response->assertStatus(200)->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.plate_number', $somVehicle->plate_number)
            ->assertJsonMissing($sycamorVehicle->toArray());
    }

    public function testUserCanCreateVehicle()
    {
        $user = User::factory()->create();

        $vehicle = [
            'user_id' => $user->id,
            'plate_number' => 'DFAFFSFSFS2343'
        ];
        $response = $this->actingAs($user)->postJson('/api/v1/vehicles', $vehicle);

        $response->assertStatus(201)
            ->assertJsonStructure(['data'])
            ->assertJsonStructure([
                'data' => ['plate_number']
            ])
            ->assertJsonFragment([
                'plate_number' => 'DFAFFSFSFS2343'
            ])->assertJsonCount(2, 'data')
            ->assertJsonPath('data.plate_number', 'DFAFFSFSFS2343');

        $this->assertDatabaseHas('vehicles', [
            'plate_number' => 'DFAFFSFSFS2343'
        ]);
    }

    public function testUserCanUpdateTheirVehicle()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
        ]);

        $newVehicle = [
            'user_id' => $user->id,
            'plate_number' => 'FFSFSFS2343'
        ];
        $response = $this->actingAs($user)->putJson('/api/v1/vehicles/' . $vehicle->id, $newVehicle);
        $response->assertStatus(202)->assertJsonStructure(['plate_number'])->assertJsonPath('plate_number', 'FFSFSFS2343');

        $this->assertDatabaseHas('vehicles', [
            'plate_number' => 'FFSFSFS2343'
        ]);
    }

    public function testUserCanDeleteTheirVehicle()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->deleteJson('/api/v1/vehicles/' . $vehicle->id);

        $response->assertNoContent();

        $this->assertDatabaseMissing('vehicles', [
            'id' => $vehicle->id,
            'deleted_at' => NULL
        ])->assertDatabaseCount('vehicles', 1); //soft delete
    }
}
