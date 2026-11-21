<?php

use App\User;
use App\Wing;

it('the home page shows the last random and most voted wings', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Wing Library');
    $response->assertViewHas('last');
    $response->assertViewHas('rand');
    $response->assertViewHas('voted');
});

it('the last panel holds the five most recently updated wings', function () {
    $user = User::factory()->create();
    $newest = Wing::factory()->create(['user_id' => $user->id]);

    for ($i = 0; $i < 6; $i++) {
        Wing::factory()->create(['user_id' => $user->id]);
    }

    Wing::where('id', '!=', $newest->id)->update(['updated_at' => '2000-01-01 00:00:00']);

    $last = $this->viewData($this->get('/'), 'last');

    $this->assertCount(5, $last);
    $this->assertEquals($newest->id, $last->first()->id);
});

it('the most voted panel ranks the wings by their likes', function () {
    $user = User::factory()->create();
    $liked = Wing::factory()->create(['user_id' => $user->id]);
    $lessLiked = Wing::factory()->create(['user_id' => $user->id]);
    $likers = User::factory()->count(2)->create();

    $liked->likedBy()->attach($likers->pluck('id')->all());
    $lessLiked->likedBy()->attach($likers->first()->id);

    $voted = $this->viewData($this->get('/'), 'voted');

    $this->assertEquals($liked->id, $voted->first()->id);
    $this->assertEquals(2, $voted->first()->likes);
});
