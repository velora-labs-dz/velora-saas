<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('/organizations/{organization:slug}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::post('/organizations/{organization:slug}/switch', [OrganizationController::class, 'switch'])->name('organizations.switch');
    Route::post('/organizations/{organization:slug}/leave', [OrganizationMemberController::class, 'leave'])->name('organizations.leave');

    Route::get('/organizations/{organization:slug}/members', [OrganizationMemberController::class, 'index'])->name('organizations.members.index');
    Route::post('/organizations/{organization:slug}/members', [OrganizationMemberController::class, 'store'])->name('organizations.members.store');
    Route::patch('/organizations/{organization:slug}/members/{member}', [OrganizationMemberController::class, 'update'])->name('organizations.members.update');
    Route::delete('/organizations/{organization:slug}/members/{member}', [OrganizationMemberController::class, 'destroy'])->name('organizations.members.destroy');

    // Clients (and every org-owned entity from here on) operate on the
    // *current* organization resolved from the session — no slug in the
    // URL — rather than repeating {organization:slug} per resource.
    // 'current-org' (EnsureCurrentOrganization) blocks the request with a
    // redirect to organizations.index if none is selected.
    Route::middleware('current-org')->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::patch('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::post('/clients/{client}/restore', [ClientController::class, 'restore'])->name('clients.restore');
    });
});

require __DIR__.'/auth.php';
