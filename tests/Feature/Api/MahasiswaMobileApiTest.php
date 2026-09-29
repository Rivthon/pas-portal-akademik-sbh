<?php

namespace Tests\Feature\Api;

use App\Models\Mahasiswa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class MahasiswaMobileApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_health_endpoint_is_available(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'service' => 'PAS API',
                'version' => 'v1',
            ]);
    }

    public function test_student_dashboard_requires_a_token(): void
    {
        $this->getJson('/api/v1/mahasiswa/dashboard')
            ->assertUnauthorized();
    }

    public function test_login_requires_all_fields(): void
    {
        $this->postJson('/api/v1/mahasiswa/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['login', 'password', 'device_name']);
    }

    public function test_student_can_login_read_profile_and_logout_with_device_token(): void
    {
        $mahasiswa = Mahasiswa::query()->firstOrFail();
        $mahasiswa->password = 'password-uji-android';
        $mahasiswa->save();

        $login = $this->postJson('/api/v1/mahasiswa/login', [
            'login' => $mahasiswa->nim,
            'password' => 'password-uji-android',
            'device_name' => 'phpunit-android',
        ])->assertOk()
            ->assertJsonPath('mahasiswa.id', $mahasiswa->mahasiswa_id)
            ->assertJsonStructure(['access_token', 'token_type', 'mahasiswa']);

        $token = $login->json('access_token');
        $tokenId = PersonalAccessToken::findToken($token)?->getKey();

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/me')
            ->assertOk()
            ->assertJsonPath('mahasiswa.nim', $mahasiswa->nim);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'tahun_akademik',
                'ringkasan' => [
                    'semester_mahasiswa',
                    'jumlah_krs',
                    'jumlah_kelas_lms',
                    'status_krs',
                    'status_mahasiswa',
                ],
                'pengumuman_lms',
            ]);

        $this->withToken($token)
            ->postJson('/api/v1/mahasiswa/logout')
            ->assertOk();

        $this->assertNotNull($tokenId);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }
}
