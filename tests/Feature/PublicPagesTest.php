<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_auth_pages_are_available(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }
}
