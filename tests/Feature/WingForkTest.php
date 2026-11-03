<?php

namespace Tests\Feature;

use App\User;
use App\Wing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WingForkTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_logged_in_user_forks_a_wing_that_is_not_theirs()
    {
        $owner = factory(User::class)->create();
        $forker = factory(User::class)->create();
        $original = $this->wingOwnedBy($owner);

        $response = $this->actingAs($forker)->get('/wings/'.$original->id.'/forkit');

        $response->assertStatus(302);

        $fork = Wing::where('id', '!=', $original->id)->first();

        $this->assertNotNull($fork);
        $response->assertRedirect('/wings/'.$fork->id.'/edit');
        $this->assertSame('Test Wing Alpha a forked version', $fork->name);
        $this->assertEquals($forker->id, $fork->user_id);
        $this->assertSame('test-wing-alpha-a-forked-version', $fork->slug);
        $this->assertSame('original image', file_get_contents(storage_path('app'.$fork->image_file)));

        $this->deleteWingImage($original->image_file);
        $this->deleteWingImage($fork->image_file);
    }

    public function test_the_owner_cannot_fork_a_wing_of_their_own()
    {
        $owner = factory(User::class)->create();
        $original = $this->wingOwnedBy($owner);

        $response = $this->actingAs($owner)->get('/wings/'.$original->id.'/forkit');

        $response->assertStatus(302);
        $response->assertSessionHas('flash_notification');
        $this->assertSame(1, Wing::count());
        $this->get('/')->assertSee('You need to be logged in and');

        $this->deleteWingImage($original->image_file);
    }

    public function test_a_guest_cannot_fork_a_wing()
    {
        $original = $this->wingOwnedBy(factory(User::class)->create());

        $response = $this->get('/wings/'.$original->id.'/forkit');

        $response->assertStatus(302);
        $response->assertSessionHas('flash_notification');
        $this->assertSame(1, Wing::count());
        $this->get('/')->assertSee('You need to be logged in and');

        $this->deleteWingImage($original->image_file);
    }

    private function wingOwnedBy(User $user)
    {
        return factory(Wing::class)->create([
            'user_id' => $user->id,
            'name' => 'Test Wing Alpha',
            'image_file' => Wing::createImage($this->base64Png('original image')),
        ]);
    }
}
