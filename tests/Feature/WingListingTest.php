<?php

namespace Tests\Feature;

use App\User;
use App\Wing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WingListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_library_lists_ten_wings_per_page()
    {
        $user = factory(User::class)->create();
        factory(Wing::class, 12)->create(['user_id' => $user->id]);

        $response = $this->get('/wings');

        $response->assertStatus(200);
        $response->assertViewHas('wings', function ($wings) {
            return $wings->count() === 10 && $wings->total() === 12;
        });

        $this->assertCount(2, $this->viewData($this->get('/wings?page=2'), 'wings'));
    }
}
