<?php

use App\Http\Controllers\Api\V1\Mahasiswa\AcademicController;
use App\Http\Controllers\Api\V1\Mahasiswa\AttendanceController;
use App\Http\Controllers\Api\V1\Mahasiswa\AuthController;
use App\Http\Controllers\Api\V1\Mahasiswa\DashboardController;
use App\Http\Controllers\Api\V1\Mahasiswa\EdomController;
use App\Http\Controllers\Api\V1\Mahasiswa\LmsController;
use App\Http\Controllers\Api\V1\Mahasiswa\PedomanAkademikController;
use App\Http\Controllers\Api\V1\Mahasiswa\SkpiController;
use App\Http\Controllers\Api\V1\Mahasiswa\StudentServiceController;
use App\Http\Controllers\Api\V1\Mahasiswa\StudyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'service' => 'PAS API',
        'version' => 'v1',
    ]));

    Route::prefix('mahasiswa')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/dashboard', DashboardController::class);
            Route::get('/krs/pilihan', [AcademicController::class, 'krsOptions']);
            Route::get('/krs/diskusi', [AcademicController::class, 'krsDiscussion']);
            Route::post('/krs/diskusi', [AcademicController::class, 'sendKrsDiscussion'])
                ->middleware('throttle:20,1');
            Route::put('/krs', [AcademicController::class, 'saveKrs']);
            Route::get('/krs', [AcademicController::class, 'krs']);
            Route::get('/khs', [AcademicController::class, 'khs']);
            Route::get('/khs/riwayat', [AcademicController::class, 'khsHistory']);
            Route::get('/edom', [EdomController::class, 'index']);
            Route::get('/edom/{krs}/{dosenId}', [EdomController::class, 'form']);
            Route::post('/edom/{krs}/{dosenId}', [EdomController::class, 'submit'])
                ->middleware('throttle:10,1');
            Route::post('/edom/konfirmasi', [EdomController::class, 'confirm'])
                ->middleware('throttle:5,1');
            Route::get('/skpi', [SkpiController::class, 'index']);
            Route::get('/skpi/{category}', [SkpiController::class, 'records']);
            Route::post('/skpi/{category}', [SkpiController::class, 'store'])
                ->middleware('throttle:10,1');
            Route::put('/skpi/{category}/{record}', [SkpiController::class, 'update'])
                ->middleware('throttle:10,1');
            Route::delete('/skpi/{category}/{record}', [SkpiController::class, 'destroy'])
                ->middleware('throttle:10,1');
            Route::get('/pedoman-akademik', [PedomanAkademikController::class, 'index']);
            Route::get('/pedoman-akademik/{pedoman}/file', [PedomanAkademikController::class, 'file'])
                ->middleware('throttle:30,1');
            Route::get('/pelayanan/helpdesk', [StudentServiceController::class, 'helpdesk']);
            Route::post('/pelayanan/helpdesk', [StudentServiceController::class, 'storeHelpdesk'])
                ->middleware('throttle:10,1');
            Route::delete('/pelayanan/helpdesk/{permintaan}', [StudentServiceController::class, 'destroyHelpdesk'])
                ->middleware('throttle:10,1');
            Route::get('/pelayanan/helpdesk/{permintaan}/lampiran', [StudentServiceController::class, 'helpdeskAttachment']);
            Route::get('/pelayanan/administrasi', [StudentServiceController::class, 'finance']);
            Route::get('/pelayanan/cuti', [StudentServiceController::class, 'leave']);
            Route::post('/pelayanan/cuti', [StudentServiceController::class, 'storeLeave'])
                ->middleware('throttle:5,1');
            Route::patch('/pelayanan/cuti/{cuti}/batalkan', [StudentServiceController::class, 'cancelLeave'])
                ->middleware('throttle:5,1');
            Route::get('/pelayanan/cuti/{cuti}/lampiran', [StudentServiceController::class, 'leaveAttachment']);
            Route::get('/pelayanan/profil', [StudentServiceController::class, 'profile']);
            Route::post('/pelayanan/profil', [StudentServiceController::class, 'updateProfile'])
                ->middleware('throttle:10,1');
            Route::get('/nilai', [AcademicController::class, 'grades']);
            Route::post('/nilai/pengajuan-transkrip', [AcademicController::class, 'submitTranscriptRequest'])
                ->middleware('throttle:5,1');
            Route::get('/jadwal', [StudyController::class, 'schedules']);
            Route::get('/jadwal-ujian', [StudyController::class, 'examSchedules']);
            Route::get('/absensi', AttendanceController::class);
            Route::get('/rps', [StudyController::class, 'rps']);
            Route::get('/rps/{rps}/file', [StudyController::class, 'rpsFile']);
            Route::get('/lms', [LmsController::class, 'index']);
            Route::get('/lms/materi/{materi}/file', [LmsController::class, 'materialFile']);
            Route::get('/lms/{jadwal}', [LmsController::class, 'show']);
        });
    });
});
