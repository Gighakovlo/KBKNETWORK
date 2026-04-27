<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SwitchController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ViewerController;

// Jika user buka alamat depan, langsung tendang ke HUB (bukan lagi dashboard)
Route::get('/', function () {
    return redirect('/hub');
});

// --- JALUR PUBLIK / RUANG PAMER (TIDAK DIGEMBOK) ---
Route::get('/viewer', [ViewerController::class, 'index']);
Route::get('/viewer/mapping/{floor_id}', [ViewerController::class, 'mapping']);

// --- JALUR PINTU MASUK (LOGIN) ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- RUANGAN VIP ADMIN (DIGEMBOK) ---
Route::middleware('auth')->group(function () {
    
    // ==========================================
    // 1. AREA COMMAND CENTER
    // ==========================================
    Route::get('/hub', [LocationController::class, 'hub'])->name('hub');
    Route::get('/live-monitor', [LocationController::class, 'liveMonitor'])->name('live.monitor');
    Route::get('/macro-editor', [LocationController::class, 'macroEditor'])->name('macro.editor');
    
    // Masuk ke Split-Screen Micro Studio untuk gedung spesifik (BUAT BARU)
    Route::get('/micro-studio/{building_id}', [LocationController::class, 'microStudio'])->name('micro.studio');

    // Masuk ke Split-Screen Micro Studio untuk EDIT LANTAI (UPDATE VISUAL)
    Route::get('/micro-studio/floor/{floor_id}', [LocationController::class, 'editMicroStudio']);
    Route::post('/floor/update-visual', [LocationController::class, 'updateFloorVisual']);

    // ==========================================
    // 2. LOGIKA SIMPAN DATA KAWASAN (LOBBY)
    // ==========================================
    Route::post('/building/position', [LocationController::class, 'updatePosition']);
    Route::post('/building', [LocationController::class, 'storeBuilding']);
    Route::post('/floor', [LocationController::class, 'storeFloor']);

    // ==========================================
    // 3. AREA MAPPING LOKAL / MICRO STUDIO (LAMA - TETAP UTUH)
    // ==========================================
    Route::get('/mapping/{floor_id}', [SwitchController::class, 'index']);
    Route::post('/switch', [SwitchController::class, 'store']);
    Route::post('/connection', [SwitchController::class, 'storeConnection']);
    Route::delete('/connection/{id}', [SwitchController::class, 'destroyConnection']);
    Route::post('/switch/update-position', [SwitchController::class, 'updatePosition']);
    Route::delete('/switch/{id}', [SwitchController::class, 'destroy']);

    // Rute khusus untuk PC
    Route::post('/pc', [SwitchController::class, 'storePc']);
    Route::post('/pc/update-position', [SwitchController::class, 'updatePcPosition']);
    Route::delete('/pc/{id}', [SwitchController::class, 'destroyPc']);

    // Cetak Laporan
    Route::get('/mapping/{floor_id}/print', [SwitchController::class, 'printReport']);

    // ==========================================
    // 4. DATA MANAGEMENT (BARU)
    // ==========================================
    Route::get('/management', [LocationController::class, 'management'])->name('management');
    
    // API untuk Update & Delete Gedung
    Route::put('/building/{id}', [LocationController::class, 'updateBuilding']);
    Route::delete('/building/{id}', [LocationController::class, 'destroyBuilding']);

    // API untuk Update & Delete Lantai
    Route::put('/floor/{id}', [LocationController::class, 'updateFloor']);
    Route::delete('/floor/{id}', [LocationController::class, 'destroyFloor']);

    // API untuk Batch Delete
    Route::post('/building/batch-delete', [LocationController::class, 'destroyBuildingBatch']);
    Route::post('/floor/batch-delete', [LocationController::class, 'destroyFloorBatch']);
    
    // Rute untuk Cetak Laporan Master Data
    Route::get('/print-report', [LocationController::class, 'printReport'])->name('print.report');
    // Rute untuk Export Excel/CSV Master Data
    Route::get('/export-inventory', [LocationController::class, 'exportInventory'])->name('export.inventory');
});