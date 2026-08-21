<?php

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
});

require __DIR__.'/auth.php';
