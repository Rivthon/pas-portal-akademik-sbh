<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SessionExpirationRedirectTest extends TestCase
{
    #[DataProvider('protectedRouteProvider')]
    public function test_expired_session_redirects_to_the_correct_login(
        string $protectedRoute,
        string $loginRoute
    ): void {
        $this->get(route($protectedRoute))
            ->assertRedirect(route($loginRoute))
            ->assertSessionHas(
                'auth_notice',
                'Sesi Anda telah berakhir atau Anda telah logout. Silakan login kembali.'
            );
    }

    public static function protectedRouteProvider(): array
    {
        return [
            'admin' => ['admin.home', 'admin.login'],
            'dosen' => ['dosen.dashboard', 'dosen.login'],
            'mahasiswa' => ['mahasiswa.dashboard', 'mahasiswa.login'],
        ];
    }
}
