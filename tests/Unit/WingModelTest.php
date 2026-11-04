<?php

use App\User;
use App\Wing;

it('a user likes the wings the pivot table points at', function () {
    // User::likes() had no caller at all: only the wing side of the relation was ever exercised,
    // through the likeit route.
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);

    expect($user->likes)->toHaveCount(0);

    $user->likes()->attach($wing->id);

    expect($user->fresh()->likes)->toHaveCount(1);
    expect($user->fresh()->likes->first()->id)->toBe($wing->id);

    $this->deleteWingImage($wing->image_file);
});

it('accepts every field the forms post, and refuses the ones they do not', function () {
    // The Fillable attribute is what lets the create, edit and fork forms persist their fields.
    // Mutation testing reported every one of its entries as unkillable, because no test mass
    // assigns them; this list is the forms' own field set, not a copy of the attribute.
    $fields = [
        'slug' => 'a-wing', 'user_id' => 7, 'name' => 'A Wing', 'description' => 'text',
        'image_file' => '/wingimg/aa/f.png', 'unitsystem' => 'metric', 'cgpos' => 12.5,
        'weight' => 900, 'drawvalues' => 1, 'rootchord' => 340, 'totalspan' => 1200,
        'panels' => [], 'wingarea' => 55.5, 'macdist' => 26.1, 'maclength' => 30.2,
        'cg' => 12, 'wingload' => 40.4,
    ];

    foreach (range(1, 6) as $panel) {
        $fields["panel{$panel}span"] = (float) $panel;
        $fields["panel{$panel}chord"] = 10.0;
        $fields["panel{$panel}sweep"] = 0.0;
        $fields["panel{$panel}angle"] = 0.0;
    }

    $wing = new Wing;
    $wing->fill($fields);

    expect(array_keys($wing->getAttributes()))->toBe(array_keys($fields));

    $wing->fill(['id' => 99, 'created_at' => '2026-01-01 00:00:00', 'slug_str' => 'kept']);

    expect($wing->getAttributes())->not->toHaveKey('id');
    expect($wing->getAttributes())->not->toHaveKey('created_at');
    expect($wing->getAttributes())->not->toHaveKey('slug_str');
});

it('the slug comes from the name', function () {
    $wing = $this->createWing(['name' => 'Test Wing Alpha']);

    $this->assertSame('test-wing-alpha', $wing->slug);
    $this->assertSame('test-wing-alpha', $wing->slug_str);
});

it('a second wing with the same name gets an incremented slug', function () {
    $this->createWing(['name' => 'Test Wing Alpha']);

    $second = $this->createWing(['name' => 'Test Wing Alpha']);

    $this->assertSame('test-wing-alpha-1', $second->slug);
    $this->assertSame('test-wing-alpha', $second->slug_str);
    $this->assertSame(1, (int) $second->slug_id);
});

it('the panels are built from the panel fields', function () {
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
});

it('the panel accessors read the stored panels', function () {
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
});

it('the total span is twice the sum of the panel spans', function () {
    $this->assertEquals(42, $this->createWing()->totalspan);
});

it('the distance unit follows the unit system', function () {
    $this->assertSame('mm', $this->createWing(['unitsystem' => 'metric'])->unit('distance'));
    $this->assertSame('in', $this->createWing(['unitsystem' => 'imperial'])->unit('distance'));
});

it('a cg position loses the dot zeroes', function () {
    $this->assertSame('12', $this->createWing(['cgpos' => '12.00'])->cgpos);
});

it('create image writes the decoded bytes', function () {
    $url = Wing::createImage($this->base64Png('abcdefgh'));

    $this->assertSame(1, preg_match('#^/wingimg/\d\d/[0-9a-f]+\.png$#', $url));
    $this->assertSame('abcdefgh', file_get_contents(storage_path('app'.$url)));

    $this->deleteWingImage($url);
});

it('save image overwrites the file behind the url', function () {
    $url = Wing::createImage($this->base64Png('first image'));
    $wing = new Wing(['image_file' => $url]);

    $wing->saveImage($this->base64Png('second image'));

    $this->assertSame('second image', file_get_contents(storage_path('app'.$url)));

    $this->deleteWingImage($url);
});

it('duplicate image copies the bytes to a new file', function () {
    $original = Wing::createImage($this->base64Png('copied bytes'));

    $copy = Wing::duplicateImage($original);

    $this->assertNotSame($original, $copy);
    $this->assertSame('copied bytes', file_get_contents(storage_path('app'.$copy)));

    $this->deleteWingImage($original);
    $this->deleteWingImage($copy);
});
