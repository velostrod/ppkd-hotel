<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CheckinCheckoutController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\FoodAndBeverageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// Redirect welcome page to login since it is an internal system
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard Hub (Redirects to role dashboard)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ========================================================
    // FRONT OFFICE & ADMIN ACCESS
    // ========================================================
    Route::middleware('role:front_office,admin,manager')->group(function () {
        // Guests CRUD
        Route::resource('guests', GuestController::class);

        // Reservations
        Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::post('/reservations/{reservation}/extend', [ReservationController::class, 'extend'])->name('reservations.extend');

        // Check-in & Checkout Flow
        Route::get('/checkin/{reservation}', [CheckinCheckoutController::class, 'showCheckin'])->name('checkins.create');
        Route::post('/checkin/{reservation}', [CheckinCheckoutController::class, 'processCheckin'])->name('checkins.store');
        
        Route::post('/reservations/{reservation}/request-inspection', [CheckinCheckoutController::class, 'requestInspection'])->name('checkouts.request-inspection');
        Route::get('/checkout/{reservation}', [CheckinCheckoutController::class, 'showCheckout'])->name('checkouts.invoice');
        Route::post('/checkout/{reservation}/payment', [CheckinCheckoutController::class, 'processPayment'])->name('checkouts.payment');
        Route::post('/checkout/{reservation}', [CheckinCheckoutController::class, 'processCheckout'])->name('checkouts.store');
        Route::get('/checkout/{reservation}/print', [CheckinCheckoutController::class, 'printInvoice'])->name('checkouts.print');
        Route::post('/reservations/{reservation}/return-deposit', [CheckinCheckoutController::class, 'returnDeposit'])->name('reservations.return-deposit');

        // Service Requests
        Route::get('/services/laundry', [ServiceRequestController::class, 'indexLaundry'])->name('services.laundry');
        Route::post('/services/laundry', [ServiceRequestController::class, 'storeLaundry'])->name('services.laundry.store');
        
        Route::get('/services/fnb', [ServiceRequestController::class, 'indexFnb'])->name('services.fnb');
        Route::post('/services/fnb', [ServiceRequestController::class, 'storeFnb'])->name('services.fnb.store');
        
        Route::get('/services/cleaning', [ServiceRequestController::class, 'indexCleaning'])->name('services.cleaning');
        Route::post('/services/cleaning', [ServiceRequestController::class, 'storeCleaning'])->name('services.cleaning.store');
    });

    // ========================================================
    // HOUSEKEEPING & ADMIN ACCESS
    // ========================================================
    Route::middleware('role:housekeeping,admin,manager')->group(function () {
        Route::get('/housekeeping', [HousekeepingController::class, 'dashboard'])->name('housekeeping.dashboard');
        Route::post('/housekeeping/assign/{hkRequest}', [HousekeepingController::class, 'assignRequest'])->name('housekeeping.assign');
        Route::post('/housekeeping/start/{hkRequest}', [HousekeepingController::class, 'startRequest'])->name('housekeeping.start');
        Route::post('/housekeeping/complete/{hkRequest}', [HousekeepingController::class, 'completeRequest'])->name('housekeeping.complete');
        
        Route::get('/housekeeping/inspect/{inspection}', [HousekeepingController::class, 'showInspectionForm'])->name('housekeeping.inspect-form');
        Route::post('/housekeeping/inspect/{inspection}', [HousekeepingController::class, 'submitInspection'])->name('housekeeping.inspect');
        
        Route::post('/housekeeping/laundry/{laundry}', [HousekeepingController::class, 'updateLaundryStatus'])->name('housekeeping.laundry-update');
        Route::post('/housekeeping/room/{room}/status', [HousekeepingController::class, 'updateRoomStatus'])->name('housekeeping.room-status');
        
        Route::get('/housekeeping/cleaning-history', [HousekeepingController::class, 'cleaningHistory'])->name('housekeeping.cleaning-history');
        Route::get('/housekeeping/inspection-history', [HousekeepingController::class, 'inspectionHistory'])->name('housekeeping.inspection-history');
    });

    // ========================================================
    // F&B KITCHEN & ADMIN ACCESS
    // ========================================================
    Route::middleware('role:fnb,admin,manager')->group(function () {
        Route::get('/fnb', [FoodAndBeverageController::class, 'index'])->name('fnb.index');
        Route::post('/fnb/process/{order}', [FoodAndBeverageController::class, 'processOrder'])->name('fnb.process');
        Route::get('/fnb/history', [FoodAndBeverageController::class, 'orderHistory'])->name('fnb.history');
    });

    // ========================================================
    // ADMIN & MANAGER READ ONLY / OPERATIONAL VIEW
    // ========================================================
    Route::middleware('role:admin,manager,front_office')->group(function () {
        // Master Room Types CRUD (Read only for Manager)
        Route::get('/room-types', [AdminController::class, 'roomTypes'])->name('admin.room-types');

        // Master Rooms CRUD (Read only for Manager)
        Route::get('/admin/rooms', [AdminController::class, 'rooms'])->name('admin.rooms');
    });

    // ========================================================
    // ADMIN ONLY ACCESS (WRITE ACTIONS & SETTINGS)
    // ========================================================
    Route::middleware('role:admin')->group(function () {
        // Staff Accounts CRUD
        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::post('/admin/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle');

        // Master Room Types CRUD (Write)
        Route::post('/admin/room-types', [AdminController::class, 'storeRoomType'])->name('admin.room-types.store');
        Route::put('/admin/room-types/{roomType}', [AdminController::class, 'updateRoomType'])->name('admin.room-types.update');

        // Master Rooms CRUD (Write)
        Route::post('/admin/rooms', [AdminController::class, 'storeRoom'])->name('admin.rooms.store');
        Route::put('/admin/rooms/{room}', [AdminController::class, 'updateRoom'])->name('admin.rooms.update');

        // Hotel Settings
        Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::put('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
    });

    // ========================================================
    // REPORTS & CHARTS (FO, Admin, and Management)
    // ========================================================
    Route::middleware('role:front_office,admin,manager')->group(function () {
        Route::get('/reports/reservations', [ReportController::class, 'reservations'])->name('reports.reservations');
        Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
        Route::get('/reports/fnb', [ReportController::class, 'fnb'])->name('reports.fnb');
        Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/summary', [ReportController::class, 'summary'])->name('reports.summary');
    });
});
