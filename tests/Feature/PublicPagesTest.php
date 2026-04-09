<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_welcome_page_is_available(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_auth_pages_are_available(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }
}
