<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\DataMaster\PerusahaanController;
use App\Http\Controllers\DataMaster\DepartemenController;
use App\Http\Controllers\DataMaster\JabatanController;
use App\Http\Controllers\DataMaster\JenisDokumenController;
use App\Http\Controllers\DataMaster\TteController;
use App\Http\Controllers\DataMaster\WilayahKerjaController;
use App\Http\Controllers\Data\SubmissionController;
use App\Http\Controllers\Data\ApprovalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {

    // ── Manajemen Pengguna ──────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {

        Route::get('/', [UsersController::class, 'index'])
            ->middleware('akses:users,index_access')
            ->name('index');

        Route::get('/create', [UsersController::class, 'create'])
            ->middleware('akses:users,create_access')
            ->name('create');

        Route::post('/', [UsersController::class, 'store'])
            ->middleware('akses:users,create_access')
            ->name('store');

        // !! Harus SEBELUM /{user} agar tidak tertangkap sebagai wildcard
        Route::get('/akses', [UsersController::class, 'aksesIndex'])
            ->middleware('akses:users.akses,index_access')
            ->name('akses.index');

        Route::get('/{user}', [UsersController::class, 'show'])
            ->middleware('akses:users,show_access')
            ->name('show');

        Route::get('/{user}/edit', [UsersController::class, 'edit'])
            ->middleware('akses:users,update_access')
            ->name('edit');

        Route::put('/{user}', [UsersController::class, 'update'])
            ->middleware('akses:users,update_access')
            ->name('update');

        Route::delete('/{user}', [UsersController::class, 'destroy'])
            ->middleware('akses:users,delete_access')
            ->name('destroy');

        Route::get('/{user}/akses', [UsersController::class, 'editAkses'])
            ->middleware('akses:users.akses,update_access')
            ->name('akses.edit');

        Route::put('/{user}/akses', [UsersController::class, 'updateAkses'])
            ->middleware('akses:users.akses,update_access')
            ->name('akses.update');
    });

    // ── Data Master ─────────────────────────────────────────────
    Route::prefix('master')->name('master.')->group(function () {

        // Perusahaan
        Route::get('perusahaan',                   [PerusahaanController::class, 'index'])
            ->middleware('akses:master.perusahaan,index_access')->name('perusahaan.index');
        Route::get('perusahaan/create',            [PerusahaanController::class, 'create'])
            ->middleware('akses:master.perusahaan,create_access')->name('perusahaan.create');
        Route::post('perusahaan',                  [PerusahaanController::class, 'store'])
            ->middleware('akses:master.perusahaan,create_access')->name('perusahaan.store');
        Route::get('perusahaan/{perusahaan}',      [PerusahaanController::class, 'show'])
            ->middleware('akses:master.perusahaan,show_access')->name('perusahaan.show');
        Route::get('perusahaan/{perusahaan}/edit', [PerusahaanController::class, 'edit'])
            ->middleware('akses:master.perusahaan,update_access')->name('perusahaan.edit');
        Route::put('perusahaan/{perusahaan}',      [PerusahaanController::class, 'update'])
            ->middleware('akses:master.perusahaan,update_access')->name('perusahaan.update');
        Route::delete('perusahaan/{perusahaan}',   [PerusahaanController::class, 'destroy'])
            ->middleware('akses:master.perusahaan,delete_access')->name('perusahaan.destroy');

        // Departemen
        Route::get('departemen',                   [DepartemenController::class, 'index'])
            ->middleware('akses:master.departemen,index_access')->name('departemen.index');
        Route::get('departemen/create',            [DepartemenController::class, 'create'])
            ->middleware('akses:master.departemen,create_access')->name('departemen.create');
        Route::post('departemen',                  [DepartemenController::class, 'store'])
            ->middleware('akses:master.departemen,create_access')->name('departemen.store');
        Route::get('departemen/{departemen}',      [DepartemenController::class, 'show'])
            ->middleware('akses:master.departemen,show_access')->name('departemen.show');
        Route::get('departemen/{departemen}/edit', [DepartemenController::class, 'edit'])
            ->middleware('akses:master.departemen,update_access')->name('departemen.edit');
        Route::put('departemen/{departemen}',      [DepartemenController::class, 'update'])
            ->middleware('akses:master.departemen,update_access')->name('departemen.update');
        Route::delete('departemen/{departemen}',   [DepartemenController::class, 'destroy'])
            ->middleware('akses:master.departemen,delete_access')->name('departemen.destroy');

        // Jabatan
        Route::get('jabatan',                [JabatanController::class, 'index'])
            ->middleware('akses:master.jabatan,index_access')->name('jabatan.index');
        Route::get('jabatan/create',         [JabatanController::class, 'create'])
            ->middleware('akses:master.jabatan,create_access')->name('jabatan.create');
        Route::post('jabatan',               [JabatanController::class, 'store'])
            ->middleware('akses:master.jabatan,create_access')->name('jabatan.store');
        Route::get('jabatan/{jabatan}',      [JabatanController::class, 'show'])
            ->middleware('akses:master.jabatan,show_access')->name('jabatan.show');
        Route::get('jabatan/{jabatan}/edit', [JabatanController::class, 'edit'])
            ->middleware('akses:master.jabatan,update_access')->name('jabatan.edit');
        Route::put('jabatan/{jabatan}',      [JabatanController::class, 'update'])
            ->middleware('akses:master.jabatan,update_access')->name('jabatan.update');
        Route::delete('jabatan/{jabatan}',   [JabatanController::class, 'destroy'])
            ->middleware('akses:master.jabatan,delete_access')->name('jabatan.destroy');

        // Wilayah Kerja
        Route::get('wilayah-kerja',              [WilayahKerjaController::class, 'index'])
            ->middleware('akses:master.wilker,index_access')->name('wilker.index');
        Route::get('wilayah-kerja/create',       [WilayahKerjaController::class, 'create'])
            ->middleware('akses:master.wilker,create_access')->name('wilker.create');
        Route::post('wilayah-kerja',             [WilayahKerjaController::class, 'store'])
            ->middleware('akses:master.wilker,create_access')->name('wilker.store');
        Route::get('wilayah-kerja/{wilker}',     [WilayahKerjaController::class, 'show'])
            ->middleware('akses:master.wilker,show_access')->name('wilker.show');
        Route::get('wilayah-kerja/{wilker}/edit', [WilayahKerjaController::class, 'edit'])
            ->middleware('akses:master.wilker,update_access')->name('wilker.edit');
        Route::put('wilayah-kerja/{wilker}',     [WilayahKerjaController::class, 'update'])
            ->middleware('akses:master.wilker,update_access')->name('wilker.update');
        Route::delete('wilayah-kerja/{wilker}',  [WilayahKerjaController::class, 'destroy'])
            ->middleware('akses:master.wilker,delete_access')->name('wilker.destroy');

        // TTE
        Route::get('tte',                   [TteController::class, 'index'])
            ->middleware('akses:master.tte,index_access')->name('tte.index');
        Route::get('tte/create',            [TteController::class, 'create'])
            ->middleware('akses:master.tte,create_access')->name('tte.create');
        Route::post('tte',                  [TteController::class, 'store'])
            ->middleware('akses:master.tte,create_access')->name('tte.store');
        Route::get('tte/{tte}',             [TteController::class, 'show'])
            ->middleware('akses:master.tte,index_access')->name('tte.show');
        Route::get('tte/{tte}/edit',        [TteController::class, 'edit'])
            ->middleware('akses:master.tte,update_access')->name('tte.edit');
        Route::put('tte/{tte}',             [TteController::class, 'update'])
            ->middleware('akses:master.tte,update_access')->name('tte.update');
        Route::delete('tte/{tte}',          [TteController::class, 'destroy'])
            ->middleware('akses:master.tte,delete_access')->name('tte.destroy');
        Route::post('tte/{tte}/regenerate', [TteController::class, 'regenerate'])
            ->middleware('akses:master.tte,update_access')->name('tte.regenerate');
        Route::post('tte/{tte}/toggle',     [TteController::class, 'toggleActive'])
            ->middleware('akses:master.tte,update_access')->name('tte.toggle');

        // Jenis Dokumen
        Route::get('jenis-dokumen',                     [JenisDokumenController::class, 'index'])
            ->middleware('akses:master.jenis-dokumen,index_access')->name('jenis-dokumen.index');
        Route::get('jenis-dokumen/create',              [JenisDokumenController::class, 'create'])
            ->middleware('akses:master.jenis-dokumen,create_access')->name('jenis-dokumen.create');
        Route::post('jenis-dokumen',                    [JenisDokumenController::class, 'store'])
            ->middleware('akses:master.jenis-dokumen,create_access')->name('jenis-dokumen.store');
        Route::get('jenis-dokumen/{jenisDokumen}/edit', [JenisDokumenController::class, 'edit'])
            ->middleware('akses:master.jenis-dokumen,update_access')->name('jenis-dokumen.edit');
        Route::put('jenis-dokumen/{jenisDokumen}',      [JenisDokumenController::class, 'update'])
            ->middleware('akses:master.jenis-dokumen,update_access')->name('jenis-dokumen.update');
        Route::delete('jenis-dokumen/{jenisDokumen}',   [JenisDokumenController::class, 'destroy'])
            ->middleware('akses:master.jenis-dokumen,delete_access')->name('jenis-dokumen.destroy');
    });

    // ── Data Transaksi ──────────────────────────────────────────
    Route::prefix('data')->name('data.')->group(function () {

        // Submission (Pengajuan Surat)
        Route::get('submission',                    [SubmissionController::class, 'index'])
            ->middleware('akses:data.submission,index_access')->name('submission.index');
        Route::get('submission/create',             [SubmissionController::class, 'create'])
            ->middleware('akses:data.submission,create_access')->name('submission.create');
        Route::post('submission',                   [SubmissionController::class, 'store'])
            ->middleware('akses:data.submission,create_access')->name('submission.store');
        Route::get('submission/{submission}',       [SubmissionController::class, 'show'])
            ->middleware('akses:data.submission,index_access')->name('submission.show');
        Route::get('submission/{submission}/edit',  [SubmissionController::class, 'edit'])
            ->middleware('akses:data.submission,update_access')->name('submission.edit');
        Route::put('submission/{submission}',       [SubmissionController::class, 'update'])
            ->middleware('akses:data.submission,update_access')->name('submission.update');
        Route::delete('submission/{submission}',    [SubmissionController::class, 'destroy'])
            ->middleware('akses:data.submission,delete_access')->name('submission.destroy');

        // Serve PDF file
// Serve PDF file — hapus prefix 'data.' dari name
Route::get('submission/{submission}/file', function (App\Models\Data\PengajuanSurat $submission) {
    abort_unless(auth()->check(), 403);

    $relativePath = $submission->file_signed ?? $submission->file_original;
    $path = storage_path('app/' . $relativePath);

    abort_unless(file_exists($path), 404);

    return response()->file($path, ['Content-Type' => 'application/pdf']);
})->name('submission.file'); // ← was 'data.submission.file', sekarang cukup 'submission.file'

        // Approval
        Route::get('approval',                          [ApprovalController::class, 'index'])
            ->name('approval.index');
        Route::get('approval/{submission}/review',      [ApprovalController::class, 'review'])
            ->name('approval.review');
        Route::post('approval/{submission}/approve',    [ApprovalController::class, 'approve'])
            ->name('approval.approve');
        Route::post('approval/{submission}/reject',     [ApprovalController::class, 'reject'])
            ->name('approval.reject');
    });
});
