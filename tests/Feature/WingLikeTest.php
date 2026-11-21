<?php

use App\User;
use App\Wing;
use Illuminate\Support\Facades\DB;

it('liking a wing adds one row and returns the count', function () {
    $user = User::factory()->create();
    $wing = Wing::factory()->create(['user_id' => User::factory()->create()->id]);

    $response = $this->actingAs($user)->post('/wings/likeit', ['id' => $wing->id]);

    $response->assertStatus(200);
    $response->assertExactJson([1]);
    $this->assertSame(1, DB::table('users_likes_wings')->count());

    $again = $this->actingAs($user)->post('/wings/likeit', ['id' => $wing->id]);

    $again->assertExactJson([1]);
    $this->assertSame(1, DB::table('users_likes_wings')->count());
});

it('liking an unknown wing is not found', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/wings/likeit', ['id' => 999999])->assertStatus(404);
});
