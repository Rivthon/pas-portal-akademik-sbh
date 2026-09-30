<?php

namespace Tests\Feature\Api;

use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
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
            ->getJson('/api/v1/mahasiswa/lms')
            ->assertOk()
            ->assertJsonStructure(['tahun_akademik', 'kelas']);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/krs')
            ->assertOk()
            ->assertJsonStructure(['tahun_akademik', 'status', 'total_sks', 'mata_kuliah']);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/krs/diskusi')
            ->assertOk()
            ->assertJsonStructure([
                'tahun_akademik',
                'dosen_pembimbing',
                'tersedia',
                'terkunci',
                'pesan_status',
                'pesan',
            ]);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/krs/pilihan')
            ->assertOk()
            ->assertJsonStructure([
                'tahun_akademik',
                'pengisian_diaktifkan',
                'dapat_diubah',
                'mode',
                'mata_kuliah',
            ]);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/khs')
            ->assertOk()
            ->assertJsonStructure(['tahun_akademik', 'terkunci', 'total_sks', 'mata_kuliah']);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/khs/riwayat')
            ->assertOk()
            ->assertJsonStructure(['riwayat']);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/jadwal')
            ->assertOk()
            ->assertJsonStructure(['tahun_akademik', 'teori', 'praktik']);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/absensi')
            ->assertOk()
            ->assertJsonStructure([
                'tahun_akademik_tersedia',
                'tahun_akademik',
                'semester_tersedia',
                'semester',
                'teori',
                'praktik',
            ]);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/rps')
            ->assertOk()
            ->assertJsonStructure(['tahun_akademik', 'mata_kuliah']);

        $this->withToken($token)
            ->postJson('/api/v1/mahasiswa/logout')
            ->assertOk();

        $this->assertNotNull($tokenId);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    public function test_student_only_receives_lms_class_from_approved_matching_krs(): void
    {
        $tahunAkademik = TahunAkademik::query()->where('status_ta', 1)->firstOrFail();
        $mahasiswa = Mahasiswa::query()
            ->whereHas('krs', fn ($query) => $query
                ->where('ta_id', $tahunAkademik->ta_id)
                ->whereNotNull('disetujui_pada'))
            ->get()
            ->first(fn (Mahasiswa $candidate) => KrsClassResolver::jadwalIdsForMahasiswa(
                $candidate,
                (int) $tahunAkademik->ta_id
            )->isNotEmpty());

        if (! $mahasiswa) {
            $this->markTestSkipped('Tidak ada KRS aktif yang cocok dengan jadwal pada database uji.');
        }

        $jadwalId = KrsClassResolver::jadwalIdsForMahasiswa(
            $mahasiswa,
            (int) $tahunAkademik->ta_id
        )->first();

        Sanctum::actingAs($mahasiswa, ['mahasiswa']);

        $this->getJson('/api/v1/mahasiswa/lms')
            ->assertOk()
            ->assertJsonFragment(['id' => $jadwalId]);

        $this->getJson('/api/v1/mahasiswa/lms/'.$jadwalId)
            ->assertOk()
            ->assertJsonPath('kelas.id', $jadwalId)
            ->assertJsonStructure(['kelas', 'pertemuan']);

        $jadwal = Jadwal::with('kurikulum.mataKuliah')->findOrFail($jadwalId);
        $this->getJson('/api/v1/mahasiswa/absensi?ta_id='.$tahunAkademik->ta_id.'&semester='.$jadwal->kurikulum->mataKuliah->smt)
            ->assertOk()
            ->assertJsonFragment(['kurikulum_id' => (int) $jadwal->kurikulum_id]);

        $this->getJson('/api/v1/mahasiswa/jadwal')
            ->assertOk()
            ->assertJsonFragment(['id' => $jadwalId]);

        $this->getJson('/api/v1/mahasiswa/rps')
            ->assertOk()
            ->assertJsonCount($mahasiswa->krs()
                ->where('ta_id', $tahunAkademik->ta_id)
                ->whereNotNull('disetujui_pada')
                ->count(), 'mata_kuliah');
    }
}
