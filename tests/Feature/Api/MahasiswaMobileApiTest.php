<?php

namespace Tests\Feature\Api;

use App\Models\Jadwal;
use App\Models\KhsPublication;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\PedomanAkademik;
use App\Models\PengajuanCuti;
use App\Models\PengajuanTranskrip;
use App\Models\Setting;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            ->getJson('/api/v1/mahasiswa/edom')
            ->assertOk()
            ->assertJsonStructure([
                'tahun_akademik',
                'diaktifkan',
                'dikonfirmasi',
                'progres' => ['required', 'filled', 'remaining', 'complete'],
                'mata_kuliah',
            ]);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/nilai')
            ->assertOk()
            ->assertJsonStructure([
                'tahun_akademik',
                'uts' => ['aktif', 'pesan', 'mata_kuliah'],
                'uas' => ['aktif', 'pesan', 'mata_kuliah'],
                'riwayat',
                'transkrip' => ['total_sks', 'ipk', 'predikat', 'mata_kuliah'],
                'pengajuan_transkrip' => ['dapat_mengajukan', 'pengajuan_terakhir'],
            ]);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/jadwal')
            ->assertOk()
            ->assertJsonStructure(['tahun_akademik', 'teori', 'praktik']);

        $this->withToken($token)
            ->getJson('/api/v1/mahasiswa/jadwal-ujian')
            ->assertOk()
            ->assertJsonStructure([
                'tahun_akademik',
                'program_studi',
                'semester',
                'uts' => ['tersedia', 'aktif', 'pesan', 'jadwal'],
                'uas' => ['tersedia', 'aktif', 'pesan', 'jadwal'],
                'uap' => ['tersedia', 'aktif', 'pesan', 'jadwal'],
            ]);

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

    public function test_student_can_submit_edom_once_through_mobile_api(): void
    {
        Setting::query()->firstOrFail()->update(['edom_enabled' => true]);
        $activeTa = TahunAkademik::query()->where('status_ta', 1)->firstOrFail();
        $students = Mahasiswa::query()
            ->whereHas('krs', fn ($query) => $query
                ->where('ta_id', $activeTa->ta_id)
                ->whereNotNull('disetujui_pada'))
            ->get();

        $selected = null;
        $assignment = null;
        foreach ($students as $student) {
            Sanctum::actingAs($student);
            $payload = $this->getJson('/api/v1/mahasiswa/edom')->assertOk()->json();
            foreach ($payload['mata_kuliah'] ?? [] as $course) {
                if (! empty($course['dosen'])) {
                    $selected = $student;
                    $assignment = [$course, $course['dosen'][0]];
                    break 2;
                }
            }
        }

        if (! $selected || ! $assignment) {
            $this->markTestSkipped('Tidak ada penugasan dosen pada KRS aktif untuk pengujian EDOM.');
        }

        [$course, $lecturer] = $assignment;
        DB::table('penilaian')
            ->where('mahasiswa_id', $selected->mahasiswa_id)
            ->where('krs_id', $course['krs_id'])
            ->where('dosen_id', $lecturer['id'])
            ->where('jenis_dosen', $lecturer['jenis_dosen'])
            ->where('jenis_kelas', $lecturer['jenis_kelas'])
            ->delete();
        DB::table('saran')
            ->where('mahasiswa_id', $selected->mahasiswa_id)
            ->where('krs_id', $course['krs_id'])
            ->where('dosen_id', $lecturer['id'])
            ->where('jenis_dosen', $lecturer['jenis_dosen'])
            ->where('jenis_kelas', $lecturer['jenis_kelas'])
            ->delete();

        Sanctum::actingAs($selected);
        $form = $this->getJson(
            '/api/v1/mahasiswa/edom/'.$course['krs_id'].'/'.$lecturer['id']
            .'?jenis_dosen='.$lecturer['jenis_dosen']
        )->assertOk()->assertJsonStructure(['pertanyaan' => [['id', 'pertanyaan']]])->json();
        $responses = collect($form['pertanyaan'])->mapWithKeys(
            fn (array $question) => [(string) $question['id'] => 5]
        )->all();

        $endpoint = '/api/v1/mahasiswa/edom/'.$course['krs_id'].'/'.$lecturer['id'];
        $body = [
            'jenis_dosen' => $lecturer['jenis_dosen'],
            'responses' => $responses,
            'suggestion' => 'Pengajaran sudah baik dan semoga terus dipertahankan.',
        ];
        $this->postJson($endpoint, $body)
            ->assertOk()
            ->assertJsonPath('message', 'EDOM berhasil disimpan. Jawaban tidak dapat diubah setelah dikirim.');

        $this->postJson($endpoint, $body)
            ->assertUnprocessable();
    }

    public function test_student_can_open_edom_for_the_selected_khs_history_period(): void
    {
        $activeTaId = TahunAkademik::query()->where('status_ta', 1)->value('ta_id');
        $historicalKrs = Krs::query()
            ->with('mahasiswa')
            ->where('ta_id', '!=', $activeTaId)
            ->whereNotNull('disetujui_pada')
            ->whereHas('mahasiswa', fn ($query) => $query->whereNotNull('jurusan_id'))
            ->first();

        if (! $historicalKrs || ! $historicalKrs->mahasiswa) {
            $this->markTestSkipped('Tidak ada KRS riwayat yang disetujui untuk pengujian EDOM.');
        }

        KhsPublication::query()->updateOrCreate([
            'ta_id' => $historicalKrs->ta_id,
            'program_studi_id' => $historicalKrs->mahasiswa->jurusan_id,
            'scope_key' => 'all',
        ], [
            'scope_type' => 'all',
            'semester' => null,
            'jadwal_id' => null,
            'published_by_user_id' => null,
            'published_at' => now(),
        ]);

        Sanctum::actingAs($historicalKrs->mahasiswa);

        $this->getJson('/api/v1/mahasiswa/edom?ta_id='.$historicalKrs->ta_id)
            ->assertOk()
            ->assertJsonPath('tahun_akademik.id', (int) $historicalKrs->ta_id)
            ->assertJsonPath('riwayat', true)
            ->assertJsonPath('diaktifkan', true);
    }

    public function test_student_can_manage_only_their_own_skpi_records_through_mobile_api(): void
    {
        $students = Mahasiswa::query()->limit(2)->get();
        if ($students->count() < 2) {
            $this->markTestSkipped('Dibutuhkan dua mahasiswa untuk pengujian kepemilikan SKPI.');
        }

        $owner = $students->first();
        $otherStudent = $students->last();
        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/mahasiswa/skpi')
            ->assertOk()
            ->assertJsonStructure([
                'mahasiswa',
                'tahun_akademik',
                'total_pengajuan',
                'total_disetujui',
                'total_bobot',
                'kategori' => [['key', 'title', 'total', 'menunggu', 'disetujui', 'bobot']],
            ]);

        $payload = [
            'nama_kegiatan' => 'Uji Kompetensi Mobile',
            'penyelenggara' => 'STIKes Bakti Husada',
            'tingkat_kegiatan' => 'Nasional',
            'prestasi' => 'Peserta',
            'tanggal' => '2026-09-30',
            'jenis_sertifikat' => 'Kompetensi',
            'file_sertifikat' => 'https://example.com/sertifikat.pdf',
            'dokumen_pendukung' => 'https://example.com/pendukung.pdf',
        ];

        $created = $this->postJson('/api/v1/mahasiswa/skpi/sertifikasi', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'Menunggu')
            ->json('data');

        $recordId = $created['id'];
        $payload['nama_kegiatan'] = 'Uji Kompetensi Mobile Diperbarui';
        $this->putJson('/api/v1/mahasiswa/skpi/sertifikasi/'.$recordId, $payload)
            ->assertOk()
            ->assertJsonPath('data.data.nama_kegiatan', $payload['nama_kegiatan']);

        Sanctum::actingAs($otherStudent);
        $this->deleteJson('/api/v1/mahasiswa/skpi/sertifikasi/'.$recordId)
            ->assertNotFound();

        Sanctum::actingAs($owner);
        $this->deleteJson('/api/v1/mahasiswa/skpi/sertifikasi/'.$recordId)
            ->assertOk();
    }

    public function test_student_mobile_api_only_exposes_active_academic_guides(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('pedoman-akademik/aktif.pdf', '%PDF-1.4 test');
        Storage::disk('private')->put('pedoman-akademik/nonaktif.pdf', '%PDF-1.4 test');
        $active = PedomanAkademik::query()->create([
            'judul' => 'Pedoman Akademik Aktif',
            'tahun_berlaku' => '2026/2027',
            'nama_file' => 'Pedoman Akademik Aktif.pdf',
            'path' => 'pedoman-akademik/aktif.pdf',
            'status' => true,
        ]);
        $inactive = PedomanAkademik::query()->create([
            'judul' => 'Pedoman Akademik Nonaktif',
            'tahun_berlaku' => '2025/2026',
            'nama_file' => 'Pedoman Akademik Nonaktif.pdf',
            'path' => 'pedoman-akademik/nonaktif.pdf',
            'status' => false,
        ]);
        Sanctum::actingAs(Mahasiswa::query()->firstOrFail());

        $response = $this->getJson('/api/v1/mahasiswa/pedoman-akademik')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $active->id,
                'judul' => 'Pedoman Akademik Aktif',
                'tersedia' => true,
            ]);
        $this->assertFalse(
            collect($response->json('pedoman'))->contains('id', $inactive->id)
        );

        $this->get('/api/v1/mahasiswa/pedoman-akademik/'.$active->id.'/file')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->get('/api/v1/mahasiswa/pedoman-akademik/'.$inactive->id.'/file')
            ->assertNotFound();
    }

    public function test_student_can_use_helpdesk_finance_and_safe_profile_mobile_services(): void
    {
        $students = Mahasiswa::query()->limit(2)->get();
        if ($students->count() < 2) {
            $this->markTestSkipped('Dibutuhkan dua mahasiswa untuk pengujian Pelayanan Mahasiswa.');
        }
        $owner = $students->first();
        $other = $students->last();
        $originalNim = $owner->nim;
        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/mahasiswa/pelayanan/administrasi')
            ->assertOk()
            ->assertJsonStructure([
                'ringkasan' => ['total_tagihan', 'total_dibayar', 'sisa'],
                'tagihan',
            ]);
        $this->getJson('/api/v1/mahasiswa/pelayanan/profil')
            ->assertOk()
            ->assertJsonPath('profil.akademik.nim', $originalNim);

        $created = $this->postJson('/api/v1/mahasiswa/pelayanan/helpdesk', [
            'jenis_permintaan' => 'fitur',
            'judul' => 'Pengujian Helpdesk Mobile',
            'deskripsi' => 'Permintaan ini dibuat otomatis melalui pengujian API mobile.',
            'prioritas' => 'sedang',
        ])->assertCreated()->json();

        $this->getJson('/api/v1/mahasiswa/pelayanan/helpdesk')
            ->assertOk()
            ->assertJsonFragment(['id' => $created['id'], 'status' => 'menunggu']);

        Sanctum::actingAs($other);
        $this->deleteJson('/api/v1/mahasiswa/pelayanan/helpdesk/'.$created['id'])
            ->assertNotFound();

        Sanctum::actingAs($owner);
        $this->postJson('/api/v1/mahasiswa/pelayanan/profil', [
            'nama' => $owner->nama,
            'email' => $owner->email,
            'no_telp' => '081234567890',
        ])->assertOk()
            ->assertJsonPath('profil.no_telp', '081234567890')
            ->assertJsonPath('profil.akademik.nim', $originalNim);
        $this->assertSame($originalNim, $owner->fresh()->nim);

        $this->deleteJson('/api/v1/mahasiswa/pelayanan/helpdesk/'.$created['id'])
            ->assertOk();
    }

    public function test_student_can_submit_and_cancel_their_own_leave_request_through_mobile_api(): void
    {
        $activeTa = TahunAkademik::query()->where('status_ta', 1)->firstOrFail();
        $student = Mahasiswa::query()
            ->whereRaw('LOWER(status_mhs) = ?', ['aktif'])
            ->whereNotNull('dosen_id')
            ->whereHas('programStudi', fn ($query) => $query->whereNotNull('kaprodi_dosen_id'))
            ->first();
        if (! $student) {
            $this->markTestSkipped('Tidak ada mahasiswa aktif dengan Dospem dan Kaprodi untuk pengujian cuti.');
        }
        PengajuanCuti::query()
            ->where('mahasiswa_id', $student->mahasiswa_id)
            ->where('ta_id', $activeTa->ta_id)
            ->delete();
        Sanctum::actingAs($student);

        $this->getJson('/api/v1/mahasiswa/pelayanan/cuti')
            ->assertOk()
            ->assertJsonPath('dapat_mengajukan', true);
        $created = $this->postJson('/api/v1/mahasiswa/pelayanan/cuti', [
            'alasan' => 'Pengajuan cuti akademik untuk kebutuhan pengujian aplikasi mobile.',
        ])->assertCreated()->json();

        $other = Mahasiswa::query()->where('mahasiswa_id', '!=', $student->mahasiswa_id)->first();
        if ($other) {
            Sanctum::actingAs($other);
            $this->patchJson('/api/v1/mahasiswa/pelayanan/cuti/'.$created['id'].'/batalkan')
                ->assertNotFound();
        }

        Sanctum::actingAs($student);
        $this->patchJson('/api/v1/mahasiswa/pelayanan/cuti/'.$created['id'].'/batalkan')
            ->assertOk();
        $this->assertDatabaseHas('pengajuan_cuti', [
            'id' => $created['id'],
            'status' => PengajuanCuti::DIBATALKAN,
        ]);
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

    public function test_inactive_exam_grade_is_not_exposed_by_mobile_api(): void
    {
        $tahunAkademik = TahunAkademik::query()->where('status_ta', 1)->firstOrFail();
        $krs = Krs::query()
            ->where('ta_id', $tahunAkademik->ta_id)
            ->whereHas('kurikulum.mataKuliah')
            ->first();

        if (! $krs) {
            $this->markTestSkipped('Tidak ada KRS aktif pada database uji.');
        }

        $mahasiswa = Mahasiswa::findOrFail($krs->mahasiswa_id);
        $mahasiswa->update(['status_nilai_uts' => 0]);
        $krs->update(['uts' => 88]);
        Sanctum::actingAs($mahasiswa, ['mahasiswa']);

        $this->getJson('/api/v1/mahasiswa/nilai')
            ->assertOk()
            ->assertJsonPath('uts.aktif', false)
            ->assertJsonCount(0, 'uts.mata_kuliah');
    }

    public function test_exam_schedule_activation_and_uap_program_scope_are_enforced(): void
    {
        $mahasiswa = Mahasiswa::query()
            ->where('jurusan_id', '!=', 15401)
            ->firstOrFail();
        $mahasiswa->update([
            'status_uts' => 0,
            'status_uas' => 0,
            'status_uap' => 1,
            'semester' => 6,
        ]);
        Sanctum::actingAs($mahasiswa, ['mahasiswa']);

        $this->getJson('/api/v1/mahasiswa/jadwal-ujian')
            ->assertOk()
            ->assertJsonPath('uts.aktif', false)
            ->assertJsonCount(0, 'uts.jadwal')
            ->assertJsonPath('uas.aktif', false)
            ->assertJsonCount(0, 'uas.jadwal')
            ->assertJsonPath('uap.tersedia', false)
            ->assertJsonPath('uap.aktif', false)
            ->assertJsonCount(0, 'uap.jadwal');
    }

    public function test_student_can_submit_only_one_active_transcript_request(): void
    {
        $mahasiswa = Mahasiswa::query()->firstOrFail();
        PengajuanTranskrip::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->delete();
        Sanctum::actingAs($mahasiswa, ['mahasiswa']);

        $payload = [
            'jenis' => 'sementara',
            'keperluan' => 'Seminar Usulan Penelitian',
        ];

        $this->postJson('/api/v1/mahasiswa/nilai/pengajuan-transkrip', $payload)
            ->assertCreated()
            ->assertJsonPath('pengajuan.status', 'pending');

        $this->assertDatabaseHas('pengajuan_transkrip', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'jenis' => 'sementara',
            'keperluan' => 'Seminar Usulan Penelitian',
            'status' => 'pending',
        ]);

        $this->postJson('/api/v1/mahasiswa/nilai/pengajuan-transkrip', $payload)
            ->assertUnprocessable();
    }
}
