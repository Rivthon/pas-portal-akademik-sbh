<?php

use App\Http\Controllers\Api\V1\Mahasiswa\AcademicController;
use App\Http\Controllers\Api\V1\Mahasiswa\AttendanceController;
use App\Http\Controllers\Api\V1\Mahasiswa\AuthController;
use App\Http\Controllers\Api\V1\Mahasiswa\DashboardController;
use App\Http\Controllers\Api\V1\Mahasiswa\LmsController;
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
            Route::get('/nilai', [AcademicController::class, 'grades']);
            Route::post('/nilai/pengajuan-transkrip', [AcademicController::class, 'submitTranscriptRequest'])
                ->middleware('throttle:5,1');
            Route::get('/jadwal', [StudyController::class, 'schedules']);
            Route::get('/absensi', AttendanceController::class);
            Route::get('/rps', [StudyController::class, 'rps']);
            Route::get('/rps/{rps}/file', [StudyController::class, 'rpsFile']);
            Route::get('/lms', [LmsController::class, 'index']);
            Route::get('/lms/materi/{materi}/file', [LmsController::class, 'materialFile']);
            Route::get('/lms/{jadwal}', [LmsController::class, 'show']);
        });
    });
});
