<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_landing_page_is_public(): void
    {
        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('f', 32)),
            'session.driver' => 'array',
        ]);

        $this->get('/')->assertOk()->assertSee('Create account');
        $this->get('/fielddesk-landing.css')->assertOk()->assertHeader('Content-Type', 'text/css; charset=UTF-8');
    }
}
