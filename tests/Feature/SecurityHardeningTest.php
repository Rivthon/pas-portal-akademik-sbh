<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BeritaController;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\LmsPengumpulanTugas;
use App\Models\Mahasiswa;
use App\Models\Sertifikasi;
use App\Models\User;
use App\Services\LoginAttemptService;
use App\Support\StoredUpload;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use DatabaseTransactions;

    public function test_web_responses_include_security_headers(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_sensitive_upload_helper_prefers_private_storage(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        Storage::disk('private')->put('lms/pengumpulan/private-test.txt', 'secret');

        $this->assertTrue(StoredUpload::exists('lms/pengumpulan/private-test.txt'));
        $this->assertSame(
            Storage::disk('private')->path('lms/pengumpulan/private-test.txt'),
            StoredUpload::disk('lms/pengumpulan/private-test.txt')->path('lms/pengumpulan/private-test.txt')
        );
        Storage::disk('public')->assertMissing('lms/pengumpulan/private-test.txt');
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
                ->post(route('admin.login.submit'), [
                    'email' => 'missing-security-test@example.test',
                    'password' => 'invalid-password',
                ])->assertStatus(302);
        }

        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->from(route('admin.login'))->post(route('admin.login.submit'), [
                'email' => 'missing-security-test@example.test',
                'password' => 'invalid-password',
            ]);

        $response->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors([
                'email' => 'Percobaan login gagal berkali-kali. Silakan tunggu beberapa menit sebelum mencoba kembali.',
            ])
            ->assertSessionHas('login_blocked_until');
        $this->assertNotNull($response->headers->get('Retry-After'));

        $this->travel(61)->seconds();

        $sixthFailure = $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->from(route('admin.login'))->post(route('admin.login.submit'), [
                'email' => 'missing-security-test@example.test',
                'password' => 'invalid-password',
            ]);

        $sixthFailure->assertRedirect(route('admin.login'))
            ->assertSessionHas('login_blocked_until')
            ->assertHeader('Retry-After', '120');
    }

    public function test_successful_login_clears_progressive_failure_count(): void
    {
        $user = User::create([
            'name' => 'Security Login Reset',
            'email' => 'security-login-reset@example.test',
            'password' => Hash::make('valid-password-123'),
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.11'])
                ->post(route('admin.login.submit'), [
                    'email' => $user->email,
                    'password' => 'invalid-password',
                ]);
        }

        $this->travel(61)->seconds();

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.11'])
            ->post(route('admin.login.submit'), [
                'email' => $user->email,
                'password' => 'valid-password-123',
            ])
            ->assertRedirect(route('admin.home'));

        $request = Request::create('/admin/login', 'POST', ['email' => $user->email], [], [], [
            'REMOTE_ADDR' => '192.0.2.11',
        ]);
        $this->assertSame(0, app(LoginAttemptService::class)->attempts($request, 'web'));
    }

    public function test_student_cannot_delete_another_students_skpi_record(): void
    {
        [$attacker, $owner] = Mahasiswa::query()->limit(2)->get()->values()->all();
        $record = Sertifikasi::create([
            'mahasiswa_id' => $owner->mahasiswa_id,
            'nama_kegiatan' => 'Security ownership test',
            'penyelenggara' => 'Test',
            'tingkat_kegiatan' => 'Lokal',
            'tanggal' => now()->toDateString(),
            'jenis_sertifikat' => 'Test',
            'file_sertifikat' => 'https://example.test/certificate.pdf',
            'dokumen_pendukung' => 'https://example.test/support.pdf',
            'status_validasi' => 'Menunggu',
            'bobot' => 0,
        ]);

        $this->actingAs($attacker, 'mahasiswa')
            ->delete(route('mahasiswa.delete.sertifikasi', $record->getKey()))
            ->assertNotFound();

        $this->assertDatabaseHas($record->getTable(), [
            $record->getKeyName() => $record->getKey(),
            'mahasiswa_id' => $owner->mahasiswa_id,
        ]);
    }

    public function test_student_cannot_view_or_update_another_students_skpi_record(): void
    {
        [$attacker, $owner] = Mahasiswa::query()->limit(2)->get()->values()->all();
        $record = Sertifikasi::create([
            'mahasiswa_id' => $owner->mahasiswa_id,
            'nama_kegiatan' => 'Foreign security record',
            'penyelenggara' => 'Test',
            'tingkat_kegiatan' => 'Lokal',
            'tanggal' => now()->toDateString(),
            'jenis_sertifikat' => 'Test',
            'file_sertifikat' => 'https://example.test/certificate.pdf',
            'dokumen_pendukung' => 'https://example.test/support.pdf',
            'status_validasi' => 'Menunggu',
            'bobot' => 0,
        ]);

        $this->actingAs($attacker, 'mahasiswa')
            ->get(route('mahasiswa.edit.sertifikasi', $record->getKey()))
            ->assertNotFound();

        $this->actingAs($attacker, 'mahasiswa')
            ->put(route('mahasiswa.update.sertifikasi', $record->getKey()), [
                'nama_kegiatan' => 'Diambil alih penyerang',
                'penyelenggara' => 'Test',
                'tingkat_kegiatan' => 'Lokal',
                'tanggal' => now()->toDateString(),
                'jenis_sertifikat' => 'Test',
                'file_sertifikat' => 'https://example.test/certificate.pdf',
                'dokumen_pendukung' => 'https://example.test/support.pdf',
            ])->assertNotFound();

        $this->assertDatabaseHas($record->getTable(), [
            $record->getKeyName() => $record->getKey(),
            'mahasiswa_id' => $owner->mahasiswa_id,
            'nama_kegiatan' => 'Foreign security record',
        ]);
    }

    public function test_student_cannot_download_another_students_submission(): void
    {
        $submission = LmsPengumpulanTugas::query()->whereHas('mahasiswa')->firstOrFail();
        $attacker = Mahasiswa::whereKeyNot($submission->mahasiswa->getKey())->firstOrFail();

        $this->actingAs($attacker, 'mahasiswa')
            ->get(route('mahasiswa.lms.pengumpulan.download', $submission))
            ->assertForbidden();
    }

    public function test_external_news_html_is_sanitized_before_raw_rendering(): void
    {
        $method = new \ReflectionMethod(BeritaController::class, 'sanitizeContent');
        $html = $method->invoke(new BeritaController,
            '<script>alert(1)</script><img src="javascript:alert(2)" onerror="alert(3)"><a href="javascript:alert(4)">x</a>'
        );

        $this->assertStringNotContainsString('<script', strtolower($html));
        $this->assertStringNotContainsString('javascript:', strtolower($html));
        $this->assertStringNotContainsString('onerror', strtolower($html));
    }

    public function test_unused_admin_attendance_mutation_routes_are_not_exposed(): void
    {
        $routes = app('router')->getRoutes();

        $this->assertNull($routes->getByName('admin.absensi.store'));
        $this->assertNull($routes->getByName('admin.absensi.update'));
        $this->assertNull($routes->getByName('admin.absensi.destroy'));
    }

    public function test_attendance_uses_authenticated_student_not_submitted_student_id(): void
    {
        $jadwal = Jadwal::with('pertemuan')->whereHas('pertemuan')->get()
            ->first(function (Jadwal $candidate) {
                return Krs::where('kurikulum_id', $candidate->kurikulum_id)
                    ->where('ta_id', $candidate->ta_id)
                    ->whereHas('mahasiswa', function ($query) use ($candidate) {
                        $kelas = strtolower((string) $candidate->jenis_kelas) === 'karyawan'
                            ? 'karyawan'
                            : 'reguler';
                        $kelas === 'karyawan'
                            ? $query->whereRaw('LOWER(kelas) = ?', ['karyawan'])
                            : $query->whereRaw('LOWER(kelas) != ?', ['karyawan']);
                    })
                    ->exists();
            });
        $this->assertNotNull($jadwal);

        $enrollment = Krs::with('mahasiswa')
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->where('ta_id', $jadwal->ta_id)
            ->whereHas('mahasiswa', function ($query) use ($jadwal) {
                strtolower((string) $jadwal->jenis_kelas) === 'karyawan'
                    ? $query->whereRaw('LOWER(kelas) = ?', ['karyawan'])
                    : $query->whereRaw('LOWER(kelas) != ?', ['karyawan']);
            })
            ->firstOrFail();
        $student = $enrollment->mahasiswa;
        $victim = Mahasiswa::whereKeyNot($student->getKey())->firstOrFail();

        $pertemuan = $jadwal->pertemuan->first()->replicate();
        $pertemuan->tanggal_pertemuan = now('Asia/Jakarta')->toDateString();
        $pertemuan->topik = 'Security attendance test';
        $pertemuan->status = 1;
        $pertemuan->save();

        $this->actingAs($student, 'mahasiswa')
            ->post(route('mahasiswa.absensi.store'), [
                'pertemuan_id' => $pertemuan->getKey(),
                'jadwal_id' => $jadwal->getKey(),
                'mahasiswa_id' => $victim->mahasiswa_id,
                'status' => 'hadir',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas((new Absensi)->getTable(), [
            'pertemuan_id' => $pertemuan->getKey(),
            'mahasiswa_id' => $student->mahasiswa_id,
        ]);
        $this->assertDatabaseMissing((new Absensi)->getTable(), [
            'pertemuan_id' => $pertemuan->getKey(),
            'mahasiswa_id' => $victim->mahasiswa_id,
        ]);
    }

    public function test_student_cannot_submit_edom_for_another_students_krs(): void
    {
        $foreignKrs = Krs::with('mahasiswa')->whereHas('mahasiswa')->firstOrFail();
        $attacker = Mahasiswa::whereKeyNot($foreignKrs->mahasiswa->getKey())->firstOrFail();

        $this->actingAs($attacker, 'mahasiswa')
            ->post(route('mahasiswa.edom.submit', [
                'krs_id' => $foreignKrs->getKey(),
                'dosen_id' => $foreignKrs->mahasiswa->dosen_id,
            ]), [
                'responses' => [1 => 5],
                'suggestion' => 'Unauthorized evaluation',
            ])
            ->assertRedirect(route('mahasiswa.edom.index'));

        $this->assertDatabaseMissing('penilaian', [
            'mahasiswa_id' => $attacker->mahasiswa_id,
            'krs_id' => $foreignKrs->getKey(),
        ]);
    }
}
