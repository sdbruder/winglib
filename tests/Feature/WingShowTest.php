<?php

namespace Tests\Feature;

use App\User;
use App\Wing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WingShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_wing_is_reachable_by_its_slug()
    {
        $wing = $this->createWing(['name' => 'Test Wing Alpha']);

        $this->assertSame('test-wing-alpha', $wing->slug);

        $response = $this->get('/wing/test-wing-alpha');

        $response->assertStatus(200);
        $response->assertViewHas('wing');
        $this->assertEquals($wing->id, $this->viewData($response, 'wing')->id);
    }

    public function test_a_wing_is_reachable_by_its_id()
    {
        $wing = $this->createWing();

        $response = $this->get('/wings/'.$wing->id);

        $response->assertStatus(200);
        $response->assertViewHas('wing');
        $this->assertEquals($wing->id, $this->viewData($response, 'wing')->id);
    }

    public function test_an_unknown_slug_is_not_found()
    {
        $this->get('/wing/nope')->assertStatus(404);
    }

    public function test_an_unknown_id_is_not_found()
    {
        $this->get('/wings/999999')->assertStatus(404);
    }

    private function createWing(array $attributes = [])
    {
        $user = User::factory()->create();

        return Wing::factory()->create($attributes + ['user_id' => $user->id]);
    }
}
