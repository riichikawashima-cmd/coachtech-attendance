<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_path_returns_redirect_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }
}
