<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unknown_driver_is_not_found()
    {
        $this->get('/auth/nope')->assertStatus(404);
    }

    public function test_the_social_drivers_redirect_to_their_provider()
    {
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
    }

    public function test_the_configured_redirects_are_absolute_callbacks()
    {
        foreach (['facebook', 'google'] as $driver) {
            $redirect = config('services.'.$driver.'.redirect');

            $this->assertStringStartsWith('http', $redirect);
            $this->assertStringEndsWith('/auth/'.$driver.'/callback', $redirect);
        }
    }

    private function queryValue($url, $key)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        return isset($query[$key]) ? $query[$key] : null;
    }
}
