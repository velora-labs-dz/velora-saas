<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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

        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::patch('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::patch('/services/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('services.toggle-status');

        Route::get('/membership-plans', [MembershipPlanController::class, 'index'])->name('membership-plans.index');
        Route::get('/membership-plans/create', [MembershipPlanController::class, 'create'])->name('membership-plans.create');
        Route::post('/membership-plans', [MembershipPlanController::class, 'store'])->name('membership-plans.store');
        Route::get('/membership-plans/{membershipPlan}/edit', [MembershipPlanController::class, 'edit'])->name('membership-plans.edit');
        Route::patch('/membership-plans/{membershipPlan}', [MembershipPlanController::class, 'update'])->name('membership-plans.update');
        Route::patch('/membership-plans/{membershipPlan}/toggle-status', [MembershipPlanController::class, 'toggleStatus'])->name('membership-plans.toggle-status');

        Route::get('/memberships', [MembershipController::class, 'index'])->name('memberships.index');
        Route::get('/memberships/create', [MembershipController::class, 'create'])->name('memberships.create');
        Route::post('/memberships', [MembershipController::class, 'store'])->name('memberships.store');
        Route::get('/memberships/{membership}', [MembershipController::class, 'show'])->name('memberships.show');
        Route::get('/memberships/{membership}/edit', [MembershipController::class, 'edit'])->name('memberships.edit');
        Route::patch('/memberships/{membership}', [MembershipController::class, 'update'])->name('memberships.update');
        Route::patch('/memberships/{membership}/activate', [MembershipController::class, 'activate'])->name('memberships.activate');
        Route::patch('/memberships/{membership}/freeze', [MembershipController::class, 'freeze'])->name('memberships.freeze');
        Route::patch('/memberships/{membership}/unfreeze', [MembershipController::class, 'unfreeze'])->name('memberships.unfreeze');
        Route::patch('/memberships/{membership}/cancel', [MembershipController::class, 'cancel'])->name('memberships.cancel');
        Route::patch('/memberships/{membership}/expire', [MembershipController::class, 'expire'])->name('memberships.expire');

        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
        Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
        Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::patch('/attendance/{attendance}/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::patch('/payments/{payment}/void', [PaymentController::class, 'void'])->name('payments.void');
        Route::patch('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    });
});

require __DIR__.'/auth.php';
