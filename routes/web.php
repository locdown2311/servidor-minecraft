<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Servers
    Route::get('/servidores/criar', [ServerController::class, 'create'])->name('servers.create');
    Route::post('/servidores', [ServerController::class, 'store'])->name('servers.store');
    Route::get('/servidores/{server}', [ServerController::class, 'show'])->name('servers.show');
    Route::post('/servidores/{server}/iniciar', [ServerController::class, 'start'])->name('servers.start');
    Route::post('/servidores/{server}/parar', [ServerController::class, 'stop'])->name('servers.stop');
    Route::post('/servidores/{server}/reiniciar', [ServerController::class, 'restart'])->name('servers.restart');
    Route::delete('/servidores/{server}', [ServerController::class, 'destroy'])->name('servers.destroy');

    // Server Panel — Console
    Route::get('/servidores/{server}/console', [ServerController::class, 'console'])->name('servers.console');
    Route::get('/servidores/{server}/logs', [ServerController::class, 'logs'])->name('servers.logs');
    Route::post('/servidores/{server}/comando', [ServerController::class, 'sendCommand'])->name('servers.command');

    // Server Panel — Players
    Route::get('/servidores/{server}/jogadores', [ServerController::class, 'players'])->name('servers.players');
    Route::post('/servidores/{server}/jogadores/acao', [ServerController::class, 'playerAction'])->name('servers.player-action');

    // Server Panel — Settings
    Route::get('/servidores/{server}/configuracoes', [ServerController::class, 'settings'])->name('servers.settings');
    Route::post('/servidores/{server}/configuracoes', [ServerController::class, 'saveSettings'])->name('servers.settings.save');

    // Server Panel — Resources & Status (AJAX)
    Route::get('/servidores/{server}/recursos', [ServerController::class, 'resources'])->name('servers.resources');
    Route::get('/servidores/{server}/status', [ServerController::class, 'status'])->name('servers.status');

    // Server Panel — File Manager
    Route::get('/servidores/{server}/arquivos', [ServerController::class, 'files'])->name('servers.files');
    Route::get('/servidores/{server}/arquivos/listar', [ServerController::class, 'filesList'])->name('servers.files.list');
    Route::get('/servidores/{server}/arquivos/ler', [ServerController::class, 'fileRead'])->name('servers.files.read');
    Route::post('/servidores/{server}/arquivos/salvar', [ServerController::class, 'fileSave'])->name('servers.files.save');
    Route::post('/servidores/{server}/arquivos/excluir', [ServerController::class, 'fileDelete'])->name('servers.files.delete');
    Route::post('/servidores/{server}/arquivos/criar', [ServerController::class, 'fileCreate'])->name('servers.files.create');
    Route::post('/servidores/{server}/arquivos/renomear', [ServerController::class, 'fileRename'])->name('servers.files.rename');
    Route::post('/servidores/{server}/arquivos/upload', [ServerController::class, 'fileUpload'])->name('servers.files.upload');
    Route::get('/servidores/{server}/arquivos/download', [ServerController::class, 'fileDownload'])->name('servers.files.download');

    // Server Panel — Backups
    Route::get('/servidores/{server}/backups', [ServerController::class, 'backups'])->name('servers.backups');
    Route::post('/servidores/{server}/backups/criar', [ServerController::class, 'backupCreate'])->name('servers.backups.create');
    Route::post('/servidores/{server}/backups/excluir', [ServerController::class, 'backupDelete'])->name('servers.backups.delete');
    Route::post('/servidores/{server}/backups/restaurar', [ServerController::class, 'backupRestore'])->name('servers.backups.restore');
    Route::get('/servidores/{server}/backups/download', [ServerController::class, 'backupDownload'])->name('servers.backups.download');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/servidores', [AdminController::class, 'servers'])->name('servers');
    Route::get('/usuarios', [AdminController::class, 'users'])->name('users');
    Route::get('/planos', [AdminController::class, 'plans'])->name('plans');
});

require __DIR__.'/auth.php';
