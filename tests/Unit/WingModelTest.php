<?php

namespace Tests\Unit;

use App\User;
use App\Wing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WingModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_slug_comes_from_the_name()
    {
        $wing = $this->createWing(['name' => 'Test Wing Alpha']);

        $this->assertSame('test-wing-alpha', $wing->slug);
        $this->assertSame('test-wing-alpha', $wing->slug_str);
    }

    public function test_a_second_wing_with_the_same_name_gets_an_incremented_slug()
    {
        $this->createWing(['name' => 'Test Wing Alpha']);

        $second = $this->createWing(['name' => 'Test Wing Alpha']);

        $this->assertSame('test-wing-alpha-1', $second->slug);
        $this->assertSame('test-wing-alpha', $second->slug_str);
        $this->assertSame(1, (int) $second->slug_id);
    }

    public function test_the_panels_are_built_from_the_panel_fields()
    {
        $wing = Wing::find($this->createWing()->id);

        $this->assertCount(6, $wing->panels);

        // json_encode() writes a whole float as an integer, so the stored copy comes back as
        // one. The values are what the views and the client side read, so compare numerically.
        for ($panel = 1; $panel <= 6; $panel++) {
            $this->assertEquals($panel, $wing->panels[$panel]['span']);
            $this->assertEquals(10, $wing->panels[$panel]['chord']);
            $this->assertEquals(0, $wing->panels[$panel]['sweep']);
            $this->assertEquals(0, $wing->panels[$panel]['angle']);
        }
    }

    public function test_the_panel_accessors_read_the_stored_panels()
    {
        $wing = Wing::find($this->createWing()->id);

        // The wing form binds every field to one of these accessors through the model, so they
        // must agree with the stored panels. They were written as getPanel1SpanAttribute style
        // mutators and became Attribute casts at the Laravel 9 hop.
        $this->assertEquals(1, $wing->panel1span);
        $this->assertEquals(2, $wing->panel2span);
        $this->assertEquals(10, $wing->panel3chord);
        $this->assertEquals(0, $wing->panel4sweep);
        $this->assertEquals(0, $wing->panel5angle);
        $this->assertEquals(6, $wing->panel6span);
    }

    public function test_the_total_span_is_twice_the_sum_of_the_panel_spans()
    {
        $this->assertEquals(42, $this->createWing()->totalspan);
    }

    public function test_the_distance_unit_follows_the_unit_system()
    {
        $this->assertSame('mm', $this->createWing(['unitsystem' => 'metric'])->unit('distance'));
        $this->assertSame('in', $this->createWing(['unitsystem' => 'imperial'])->unit('distance'));
    }

    public function test_a_cg_position_loses_the_dot_zeroes()
    {
        $this->assertSame('12', $this->createWing(['cgpos' => '12.00'])->cgpos);
    }

    public function test_create_image_writes_the_decoded_bytes()
    {
        $url = Wing::createImage($this->base64Png('abcdefgh'));

        $this->assertSame(1, preg_match('#^/wingimg/\d\d/[0-9a-f]+\.png$#', $url));
        $this->assertSame('abcdefgh', file_get_contents(storage_path('app'.$url)));

        $this->deleteWingImage($url);
    }

    public function test_save_image_overwrites_the_file_behind_the_url()
    {
        $url = Wing::createImage($this->base64Png('first image'));
        $wing = new Wing(['image_file' => $url]);

        $wing->saveImage($this->base64Png('second image'));

        $this->assertSame('second image', file_get_contents(storage_path('app'.$url)));

        $this->deleteWingImage($url);
    }

    public function test_duplicate_image_copies_the_bytes_to_a_new_file()
    {
        $original = Wing::createImage($this->base64Png('copied bytes'));

        $copy = Wing::duplicateImage($original);

        $this->assertNotSame($original, $copy);
        $this->assertSame('copied bytes', file_get_contents(storage_path('app'.$copy)));

        $this->deleteWingImage($original);
        $this->deleteWingImage($copy);
    }

    private function createWing(array $attributes = [])
    {
        $user = User::factory()->create();

        return Wing::factory()->create($attributes + ['user_id' => $user->id]);
    }
}
