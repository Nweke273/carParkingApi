<?php

namespace Tests\Feature;

use App\Models\Parking;
use Tests\TestCase;
use App\Models\User;
use App\Models\Zone;
use App\Models\Vehicle;
use Carbon\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParkingTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function testUserCanStartParking()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $zone = Zone::first();

        $response = $this->actingAs($user)->postJson('/api/v1/parkings/start', [
            'vehicle_id' => $vehicle->id,
            'zone_id'    => $zone->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data'])
            ->assertJson([
                'data' => [
                    'stop_time'   => null,
                    'total_price' => 0,
                ],
            ]);

        $this->assertDatabaseCount('parkings', '1');
    }

    public function testUserCanGetActiveParkingWithCorrectPrice()
    {
        $dr_alert = User::factory()->create();

        $vehicle = Vehicle::factory()->create([
            'user_id' => $dr_alert->id
        ]);

        $zone = Zone::first();

        $parkingDetails = [
            'vehicle_id' => $vehicle->id,
            'zone_id' => $zone->id
        ];

        $this->actingAs($dr_alert)->postJson('/api/v1/parkings/start', $parkingDetails);

        $this->travel(2)->hours();

        $activeParking = Parking::first();

        $reponse = $this->actingAs($dr_alert)->getJson('/api/v1/parkings/' . $activeParking->id);

        $reponse->assertStatus(200)->assertJsonStructure(['data'])
            ->assertJson([
                'data' => [
                    'start_time' => $activeParking->start_time->format('Y-m-d\TH:i:s.u\Z'),
                    'stop_time' => null,
                    'total_price' => $zone->price_per_hour * 2
                ]
            ]);
    }

    public function testUserCanStopParking()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);
        $zone = Zone::first();

        $this->actingAs($user)->postJson('/api/v1/parkings/start', [
            'vehicle_id' => $vehicle->id,
            'zone_id'    => $zone->id,
        ]);

        $this->travel(2)->hours();

        $parking = Parking::first();
        $response = $this->actingAs($user)->putJson('/api/v1/parkings/' . $parking->id);

        $updatedParking = Parking::find($parking->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJson([
                'data' => [
                    'start_time'  => $updatedParking->start_time->format('Y-m-d\TH:i:s.u\Z'),
                    'stop_time'   => $updatedParking->stop_time->format('Y-m-d\TH:i:s.u\Z'),
                    'total_price' => $updatedParking->total_price,
                ],
            ]);

        $this->assertDatabaseCount('parkings', '1');
    }
}
