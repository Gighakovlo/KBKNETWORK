<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MappingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ViewerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\IpAddressController;
use App\Http\Controllers\AssetMovementController;
use App\Http\Controllers\AssetRequestController;

// ==========================================
// 1. JALUR REDIRECT & PUBLIK (TANPA LOGIN)
// ==========================================
Route::get('/', function () {
    return redirect('/hub');
});

Route::get('/viewer', [ViewerController::class, 'index']);
Route::get('/viewer/mapping/{floor_id}', [ViewerController::class, 'mapping']);

// ==========================================
// 2. JALUR AUTENTIKASI (PINTU MASUK)
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// =========================================================================
// 🔒 RUANGAN VIP ADMIN (SEMUA RUTE DI BAWAH INI WAJIB LOGIN!) 🔒
// =========================================================================
Route::middleware('auth')->group(function () {
    
    // --- A. COMMAND CENTER & DASHBOARD ---
    Route::get('/hub', [LocationController::class, 'hub'])->name('hub');
    Route::get('/live-monitor', [LocationController::class, 'liveMonitor'])->name('live.monitor');
    
    // --- B. MACRO EDITOR (MANAJEMEN KAWASAN VISUAL) ---
    Route::get('/macro-editor', [LocationController::class, 'macroEditor'])->name('macro.editor');
    Route::post('/building/position', [LocationController::class, 'updatePosition']);
    Route::post('/building', [LocationController::class, 'storeBuilding']);
    Route::post('/floor', [LocationController::class, 'storeFloor']);

    // --- C. MICRO STUDIO / MAPPING (NEW ENTERPRISE EAM) ---
    Route::get('/micro-studio/{building_id}', [LocationController::class, 'microStudio'])->name('micro.studio');
    Route::get('/micro-studio/floor/{floor_id}', [LocationController::class, 'editMicroStudio']);
    Route::post('/floor/update-visual', [LocationController::class, 'updateFloorVisual']);
    
    Route::get('/mapping/{floor_id}', [MappingController::class, 'index'])->name('mapping.index');
    Route::post('/mapping/asset/update-position', [MappingController::class, 'updatePosition']);
    Route::post('/connection', [MappingController::class, 'storeConnection']);
    Route::delete('/connection/{id}', [MappingController::class, 'destroyConnection']);
    Route::get('/mapping/{floor_id}/print', [MappingController::class, 'printReport']);
    Route::post('/mapping/spawn', [\App\Http\Controllers\MappingController::class, 'spawnAsset']);

    // --- D. DATA MANAGEMENT (LOKASI & BATCH) ---
    Route::get('/management', [LocationController::class, 'management'])->name('management');
    Route::put('/building/{id}', [LocationController::class, 'updateBuilding']);
    Route::delete('/building/{id}', [LocationController::class, 'destroyBuilding']);
    Route::put('/floor/{id}', [LocationController::class, 'updateFloor']);
    Route::delete('/floor/{id}', [LocationController::class, 'destroyFloor']);
    Route::post('/building/batch-delete', [LocationController::class, 'destroyBuildingBatch']);
    Route::post('/floor/batch-delete', [LocationController::class, 'destroyFloorBatch']);
    Route::get('/print-report', [LocationController::class, 'printReport'])->name('print.report');
    Route::get('/export-inventory', [LocationController::class, 'exportInventory'])->name('export.inventory');

    // --- E. ITAM (SISTEM INVENTARIS ASSET DATA) ---
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/category/{id}', [InventoryController::class, 'showCategory'])->name('inventory.category');
    Route::get('/inventory/category/{id}/fields', [InventoryController::class, 'getFields']);
    Route::get('/inventory/{id}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/bulk-delete', [InventoryController::class, 'bulkDelete'])->name('inventory.bulkDelete');
    
    // --- MASTER AUDIT (CETAK & EXPORT) ---
    Route::get('/inventory/audit/print', [InventoryController::class, 'printReport'])->name('inventory.print');
    Route::get('/inventory/audit/export', [InventoryController::class, 'exportInventory'])->name('inventory.export');

    // Import/Export Asset Excel
    Route::get('/inventory/category/{id}/export', [InventoryController::class, 'exportExcel'])->name('inventory.export');
    Route::get('/inventory/category/{id}/template', [InventoryController::class, 'downloadTemplate'])->name('inventory.template');
    Route::post('/inventory/category/{id}/import', [InventoryController::class, 'importExcel'])->name('inventory.import');

    // --- F. IPAM (IP ADDRESS MANAGEMENT) ---
    Route::get('/inventory/ipam', [IpAddressController::class, 'index'])->name('ipam.index');
    Route::post('/inventory/ipam', [IpAddressController::class, 'store'])->name('ipam.store');
    Route::put('/inventory/ipam/{id}', [IpAddressController::class, 'update'])->name('ipam.update');
    Route::delete('/inventory/ipam/{id}', [IpAddressController::class, 'destroy'])->name('ipam.destroy');
    Route::post('/inventory/ipam/sync', [IpAddressController::class, 'syncOldData'])->name('ipam.sync');

    // --- G. LOG MUTASI BARANG (MOVEMENT TRACKER) ---
    Route::get('/inventory/movements', [AssetMovementController::class, 'index'])->name('movements.index');

    // --- H. TICKETING (ASSET REQUEST) ---
    Route::get('/inventory/requests', [AssetRequestController::class, 'index'])->name('requests.index');
    Route::post('/inventory/requests', [AssetRequestController::class, 'store'])->name('requests.store');
    Route::put('/inventory/requests/{id}/complete', [AssetRequestController::class, 'markAsCompleted'])->name('requests.complete');
    Route::delete('/inventory/requests/{id}', [AssetRequestController::class, 'destroy'])->name('requests.destroy');

    // --- I. MASTER SETTINGS (KONTROL KATEGORI & LOKASI) ---
    Route::get('/inventory/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/inventory/settings/category', [SettingsController::class, 'storeCategory'])->name('settings.category.store');
    Route::delete('/inventory/settings/category/{id}', [SettingsController::class, 'destroyCategory'])->name('settings.category.destroy');
    Route::post('/inventory/settings/category/{categoryId}/field', [SettingsController::class, 'storeField'])->name('settings.field.store');
    Route::delete('/inventory/settings/field/{id}', [SettingsController::class, 'destroyField'])->name('settings.field.destroy');
    Route::put('/settings/field/{id}', [App\Http\Controllers\SettingsController::class, 'updateField'])->name('settings.field.update');

    // Update Lokasi (Arahkan ke LocationController)
    Route::put('/settings/building/{id}', [App\Http\Controllers\LocationController::class, 'updateBuilding'])->name('building.update');
    Route::put('/settings/floor/{id}', [App\Http\Controllers\LocationController::class, 'updateFloor'])->name('floor.update');

    // Update Kategori (Arahkan ke Controller tempat Kategori diatur, misal SettingsController)
    Route::put('/settings/category/{id}', [App\Http\Controllers\SettingsController::class, 'updateCategory'])->name('category.update');
    
    // Hapus Lokasi dari Settings
    Route::post('/inventory/settings/location', [SettingsController::class, 'storeLocation'])->name('settings.location.store');
    Route::delete('/inventory/settings/building/{id}', [SettingsController::class, 'destroyBuilding'])->name('settings.building.destroy');
    Route::delete('/inventory/settings/floor/{id}', [SettingsController::class, 'destroyFloor'])->name('settings.floor.destroy');

    // --- J. RUTE MIGRASI DARURAT (SAPU JAGAT) ---
    Route::get('/mapping-migrate-legacy', [MappingController::class, 'migrateLegacyData']);

});