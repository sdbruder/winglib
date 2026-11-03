<?php

namespace Tests\Feature;

use App\User;
use App\Wing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WingLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_liking_a_wing_adds_one_row_and_returns_the_count()
    {
        $user = factory(User::class)->create();
        $wing = factory(Wing::class)->create(['user_id' => factory(User::class)->create()->id]);

        $response = $this->actingAs($user)->post('/wings/likeit', ['id' => $wing->id]);

        $response->assertStatus(200);
        $response->assertExactJson([1]);
        $this->assertSame(1, DB::table('users_likes_wings')->count());

        $again = $this->actingAs($user)->post('/wings/likeit', ['id' => $wing->id]);

        $again->assertExactJson([1]);
        $this->assertSame(1, DB::table('users_likes_wings')->count());
    }

    public function test_liking_an_unknown_wing_is_not_found()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)->post('/wings/likeit', ['id' => 999999])->assertStatus(404);
    }
}
