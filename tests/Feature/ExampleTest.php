<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_is_redirected_to_the_student_login_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('mahasiswa.login'));
    }
}
