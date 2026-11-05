<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_login_page_is_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertViewIs('login');
    }
}
