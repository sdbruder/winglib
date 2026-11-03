<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_route_table_is_unchanged()
    {
        $expected = $this->applicationRoutes();
        $actual = [];

        foreach (app('router')->getRoutes() as $route) {
            $uri = $route->uri();

            // Package routes (debugbar) and internal "_"-prefixed routes differ between majors.
            if (strpos($uri, '_') === 0 || strpos($uri, 'debugbar') !== false) {
                continue;
            }

            $actual[] = implode('|', $route->methods()).' '.$uri;
        }

        sort($expected);
        sort($actual);

        $this->assertSame($expected, $actual);
    }

    /**
     * Every route that routes/web.php and routes/api.php declare.
     *
     * @return array
     */
    private function applicationRoutes()
    {
        return [
            'GET|HEAD /',
            'GET|HEAD api/user',
            'GET|HEAD auth/login',
            'POST auth/login',
            'GET|HEAD auth/logout',
            'POST auth/logout',
            'GET|HEAD auth/register',
            'POST auth/register',
            'GET|HEAD auth/{driver}',
            'GET|HEAD auth/{driver}/callback',
            'GET|HEAD password/reset',
            'POST password/email',
            'GET|HEAD password/reset/{token}',
            'POST password/reset',
            'GET|HEAD user/settings',
            'GET|HEAD wing/{slug}',
            'GET|HEAD wings',
            'GET|HEAD wings/create',
            'POST wings',
            'GET|HEAD wings/{id}/forkit',
            'GET|HEAD wings/{wing}',
            'GET|HEAD wings/{wing}/edit',
            'PUT|PATCH wings/{wing}',
            'DELETE wings/{wing}',
            'POST wings/likeit',
        ];
    }
}
