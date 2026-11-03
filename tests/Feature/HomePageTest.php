<?php

namespace Tests\Feature;

use App\User;
use App\Wing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_shows_the_last_random_and_most_voted_wings()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Wing Library');
        $response->assertViewHas('last');
        $response->assertViewHas('rand');
        $response->assertViewHas('voted');
    }

    public function test_the_last_panel_holds_the_five_most_recently_updated_wings()
    {
        $user = User::factory()->create();
        $newest = Wing::factory()->create(['user_id' => $user->id]);

        for ($i = 0; $i < 6; $i++) {
            Wing::factory()->create(['user_id' => $user->id]);
        }

        Wing::where('id', '!=', $newest->id)->update(['updated_at' => '2000-01-01 00:00:00']);

        $last = $this->viewData($this->get('/'), 'last');

        $this->assertCount(5, $last);
        $this->assertEquals($newest->id, $last->first()->id);
    }

    public function test_the_most_voted_panel_ranks_the_wings_by_their_likes()
    {
        $user = User::factory()->create();
        $liked = Wing::factory()->create(['user_id' => $user->id]);
        $lessLiked = Wing::factory()->create(['user_id' => $user->id]);
        $likers = User::factory()->count(2)->create();

        $liked->likedBy()->attach($likers->pluck('id')->all());
        $lessLiked->likedBy()->attach($likers->first()->id);

        $voted = $this->viewData($this->get('/'), 'voted');

        $this->assertEquals($liked->id, $voted->first()->id);
        $this->assertEquals(2, $voted->first()->likes);
    }
}
