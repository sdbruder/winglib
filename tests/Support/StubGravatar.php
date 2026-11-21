<?php

namespace Tests\Support;

/**
 * A gravatar service that answers without talking to www.gravatar.com.
 *
 * The suite binds this on the "gravatar" container key. A test that has to pin what a user
 * gets can still mock the service through the facade: the facade builds its mock from this
 * class, and Mockery cannot build one from an anonymous class.
 */
class StubGravatar
{
    /**
     * @param  string  $email
     * @return bool
     */
    public function exists($email)
    {
        return false;
    }

    /**
     * @param  string  $email
     * @param  string  $configGroup
     * @return string
     */
    public function get($email, $configGroup = 'default')
    {
        return '';
    }
}
