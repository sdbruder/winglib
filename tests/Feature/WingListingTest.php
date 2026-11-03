<?php

use App\User;
use App\Wing;

it('the library lists ten wings per page', function () {
    $user = User::factory()->create();
    Wing::factory()->count(12)->create(['user_id' => $user->id]);

    $response = $this->get('/wings');

    $response->assertStatus(200);
    $response->assertViewHas('wings', function ($wings) {
        return $wings->count() === 10 && $wings->total() === 12;
    });

    $this->assertCount(2, $this->viewData($this->get('/wings?page=2'), 'wings'));
});
