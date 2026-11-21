<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Every test file in both directories is a Pest test that runs on the application's TestCase.
uses(TestCase::class)->in('Feature', 'Unit');

// Both suites touch the database: the unit tests cover models that read and write rows, so this
// binding is wider than the plan's, which gave the trait to Feature alone.
uses(RefreshDatabase::class)->in('Feature', 'Unit');
