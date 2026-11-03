<?php

it('an unknown driver is not found', function () {
    $this->get('/auth/nope')->assertStatus(404);
});

it('the social drivers redirect to their provider', function () {
    $hosts = ['facebook' => 'facebook.com', 'google' => 'accounts.google.com'];

    foreach ($hosts as $driver => $host) {
        $response = $this->get('/auth/'.$driver);

        $response->assertStatus(302);

        $location = $response->baseResponse->headers->get('Location');

        $this->assertStringEndsWith($host, parse_url($location, PHP_URL_HOST));
        $this->assertSame(
            config('services.'.$driver.'.redirect'),
            $this->queryValue($location, 'redirect_uri')
        );
    }
});

it('the configured redirects are absolute callbacks', function () {
    foreach (['facebook', 'google'] as $driver) {
        $redirect = config('services.'.$driver.'.redirect');

        $this->assertStringStartsWith('http', $redirect);
        $this->assertStringEndsWith('/auth/'.$driver.'/callback', $redirect);
    }
});
