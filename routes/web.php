<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\DriverPortalController;
use App\Http\Controllers\CitizenDashboardController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\AdminController;

// Language Toggle Switcher
Route::get('/locale/{lang}', [LocaleController::class, 'switchLocale'])->name('locale.switch');

// Authentication Route Pathways
Route::get('/', [LoginController::class, 'showLogin'])->name('login');
Route::get('/login', [LoginController::class, 'showLogin']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Public Citizen Dashboards & APIs (accessible to guests and logged-in users)
Route::get('/citizen/dashboard', [CitizenDashboardController::class, 'dashboard'])->name('citizen.dashboard');
Route::post('/citizen/report', [CitizenDashboardController::class, 'submitReport'])->name('citizen.report.submit');
Route::get('/api/active-trucks', [CitizenDashboardController::class, 'activeTrucks'])->name('api.active-trucks');

// Protected Routes
Route::middleware(['auth'])->group(function () {

    // Citizen: Submit recycling claims (requires login, enforces daily cooldown)
    Route::post('/citizen/recycle', [CitizenDashboardController::class, 'submitRecyclingClaim'])->name('citizen.recycle.claim');

    // Admin Group Protection
    Route::middleware([\App\Http\Middleware\CheckRole::class.':Admin'])->group(function () {
        // Route Management
        Route::get('/admin/dashboard', [RouteController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/admin/routes', [RouteController::class, 'store'])->name('admin.routes.store');
        Route::delete('/admin/routes/{id}', [RouteController::class, 'destroy'])->name('admin.routes.destroy');
        Route::post('/admin/routes/assign', [RouteController::class, 'assignDriver'])->name('admin.routes.assign');

        // Announcement Management
        Route::post('/admin/announcements', [AnnouncementController::class, 'store'])->name('admin.announcements.store');

        // Driver Account Management
        Route::post('/admin/drivers', [AdminController::class, 'storeDriver'])->name('admin.drivers.store');
        Route::delete('/admin/drivers/{id}', [AdminController::class, 'destroyDriver'])->name('admin.drivers.destroy');

        // Recycling Submission Moderation
        Route::post('/admin/recycling/{id}/approve', [AdminController::class, 'approveRecycling'])->name('admin.recycling.approve');
        Route::post('/admin/recycling/{id}/reject', [AdminController::class, 'rejectRecycling'])->name('admin.recycling.reject');

        // Incident Report Status Management
        Route::post('/admin/reports/{id}/status', [AdminController::class, 'updateReportStatus'])->name('admin.reports.status');
    });

    // Driver Group Protection
    Route::middleware([\App\Http\Middleware\CheckRole::class.':Driver'])->group(function () {
        Route::get('/driver/portal', [DriverPortalController::class, 'portal'])->name('driver.portal');
        Route::post('/driver/trip/start', [DriverPortalController::class, 'startTrip'])->name('driver.trip.start');
        Route::post('/driver/trip/toggle-node', [DriverPortalController::class, 'toggleNode'])->name('driver.trip.toggle-node');
        Route::post('/driver/trip/complete', [DriverPortalController::class, 'completeTrip'])->name('driver.trip.complete');
    });

});