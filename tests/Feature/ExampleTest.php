<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_home_redirects_to_rsvp(): void
    {
        $this->get('/')->assertRedirect('/rsvp');
    }

    public function test_rsvp_page_is_successful(): void
    {
        $this->get('/rsvp')->assertOk();
    }
}
