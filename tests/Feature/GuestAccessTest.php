<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    public function test_guest_is_redirected_from_internal_pages(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/forms/ict-requests')->assertRedirect('/login');
        $this->get('/inventory')->assertRedirect('/login');
    }
}
