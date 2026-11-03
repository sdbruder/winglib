<?php

it('a wing is reachable by its slug', function () {
    $wing = $this->createWing(['name' => 'Test Wing Alpha']);

    $this->assertSame('test-wing-alpha', $wing->slug);

    $response = $this->get('/wing/test-wing-alpha');

    $response->assertStatus(200);
    $response->assertViewHas('wing');
    $this->assertEquals($wing->id, $this->viewData($response, 'wing')->id);
});

it('a wing is reachable by its id', function () {
    $wing = $this->createWing();

    $response = $this->get('/wings/'.$wing->id);

    $response->assertStatus(200);
    $response->assertViewHas('wing');
    $this->assertEquals($wing->id, $this->viewData($response, 'wing')->id);
});

it('an unknown slug is not found', function () {
    $this->get('/wing/nope')->assertStatus(404);
});

it('an unknown id is not found', function () {
    $this->get('/wings/999999')->assertStatus(404);
});
