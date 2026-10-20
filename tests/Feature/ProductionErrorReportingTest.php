<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\ProductionError;
use App\Models\User;
use App\Services\ProductionErrorReporter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class ProductionErrorReportingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('system.monitoring.capture_production_errors', true);
    }

    public function test_it_records_actor_academic_context_and_masks_secrets(): void
    {
        $admin = User::query()->firstOrFail();
        $jadwal = Jadwal::query()->with(['kurikulum.mataKuliah', 'programStudi'])->firstOrFail();
        $this->actingAs($admin);

        $request = Request::create('/dosen/absensi/pertemuan', 'POST', [
            'jadwal_id' => $jadwal->id,
            'tanggal_pertemuan' => '2026-10-20',
            'topik' => 'Pengujian laporan error',
        ]);
        $route = new Route('POST', '/dosen/absensi/pertemuan', fn () => null);
        $route->name('dosen.absensi.store');
        $request->setRouteResolver(fn () => $route);

        app(ProductionErrorReporter::class)->captureException(
            new RuntimeException('Gagal menyimpan password=rahasia token=abc123'),
            $request
        );

        $error = ProductionError::query()->latest('id')->firstOrFail();

        $this->assertSame((string) $admin->getAuthIdentifier(), $error->actor_id);
        $this->assertSame('web', $error->actor_guard);
        $this->assertSame($jadwal->id, data_get($error->context, 'akademik.jadwal_id'));
        $this->assertStringContainsString('[disamarkan]', $error->message);
        $this->assertStringNotContainsString('rahasia', $error->message);
        $this->assertStringNotContainsString('abc123', $error->message);
        $this->assertCount(1, $error->events);
        $this->assertSame((string) $admin->getAuthIdentifier(), $error->events->first()->actor_id);
    }

    public function test_it_does_not_record_expected_422_errors(): void
    {
        $before = ProductionError::count();

        app(ProductionErrorReporter::class)->captureException(
            new UnprocessableEntityHttpException('Validasi biasa.'),
            Request::create('/test', 'POST')
        );

        $this->assertSame($before, ProductionError::count());
    }

    public function test_only_super_admin_can_open_error_reports(): void
    {
        $superAdmin = User::query()->firstOrFail();
        $regularAdmin = User::query()->where('id', '!=', $superAdmin->getKey())->firstOrFail();
        $role = Role::findOrCreate('Super Admin', 'web');
        $superAdmin->syncRoles([$role]);
        $regularAdmin->syncRoles([]);

        $this->actingAs($superAdmin)
            ->get(route('admin.system.errors.index'))
            ->assertOk()
            ->assertSee('Laporan Error Production');

        $this->get(route('admin.home'))
            ->assertOk()
            ->assertSee('Error Production Terbaru');

        $this->actingAs($regularAdmin)
            ->get(route('admin.system.errors.index'))
            ->assertForbidden();
    }
}
