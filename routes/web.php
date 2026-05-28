<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\StoreInventoryController;
use App\Http\Controllers\OversightInventoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\OversightDispatchController;
use App\Http\Controllers\OversightReportsController;
use Illuminate\Support\Facades\Route;

/* ── Authentication ── */
Route::get('/', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/* ── Password Reset (OTP) ── */
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.email');
Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerify'])->name('password.verify');
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify.post');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

/* ── Protected ── */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* ── Notifications ── */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    /* ── Products (Super Admin CRUD) ── */
    Route::get('/admin/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::post('/admin/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::put('/admin/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/admin/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    Route::patch('/admin/products/{id}/restore', [ProductController::class, 'restore'])->name('admin.products.restore');

    /* ── Distributor Pricing & Discounts ── */
    Route::get('/admin/pricing', [ProductController::class, 'pricingIndex'])->name('admin.pricing.index');
    Route::post('/admin/pricing/save', [ProductController::class, 'savePricing'])->name('admin.pricing.save');
    Route::post('/admin/pricing/discounts', [ProductController::class, 'storeDiscount'])->name('admin.pricing.storeDiscount');
    Route::delete('/admin/pricing/discounts/{discount}', [ProductController::class, 'destroyDiscount'])->name('admin.pricing.destroyDiscount');

    /* ── User Management (Super Admin only) ── */
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserManagementController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('admin.users.toggleActive');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/admin/users/{user}/impersonate', [UserManagementController::class, 'impersonate'])->name('admin.users.impersonate');
    Route::post('/admin/users/stop-impersonate', [UserManagementController::class, 'stopImpersonate'])->name('admin.users.stop-impersonate');

    /* ── Inventory & Batches ── */
    Route::get('/admin/inventory', [OversightInventoryController::class, 'index'])->name('admin.inventory.index');
    Route::get('/admin/inventory/batches/{batch}/download', [OversightInventoryController::class, 'downloadReport'])->name('admin.inventory.download');
    Route::delete('/admin/inventory/batches/{batch}', [OversightInventoryController::class, 'destroy'])->name('admin.inventory.destroy');
    Route::get('/admin/dispatches', [OversightDispatchController::class, 'index'])->name('admin.dispatches.index');
    Route::get('/admin/distributors', [OversightDispatchController::class, 'distributorsIndex'])->name('admin.distributors.index');
    Route::get('/admin/payments', [OversightDispatchController::class, 'paymentsIndex'])->name('admin.payments.index');
    Route::post('/admin/invoices/{invoice}/payment', [OversightDispatchController::class, 'recordPayment'])->name('admin.invoices.payment');
    Route::post('/admin/payments/{id}/approve', [OversightDispatchController::class, 'approvePayment'])->name('admin.payments.approve');
    Route::post('/admin/payments/{id}/reject', [OversightDispatchController::class, 'rejectPayment'])->name('admin.payments.reject');
    Route::get('/admin/reports', [OversightReportsController::class, 'index'])->name('admin.reports.index');

    /* ── Production Manager routes ── */
    Route::get('/production/batches', [ProductionBatchController::class, 'index'])->name('production.batches.index');
    Route::post('/production/batches', [ProductionBatchController::class, 'store'])->name('production.batches.store');
    Route::put('/production/batches/{batch}', [ProductionBatchController::class, 'update'])->name('production.batches.update');
    Route::get('/production/products', [ProductionBatchController::class, 'productsIndex'])->name('production.products.index');

    /* ── Store Manager routes ── */
    Route::get('/store/inventory', [StoreInventoryController::class, 'index'])->name('store.inventory.index');
    Route::post('/store/inventory/{batch}/verify', [StoreInventoryController::class, 'verify'])->name('store.inventory.verify');
    Route::get('/store/dispatches', [DispatchController::class, 'index'])->name('store.dispatches.index');
    Route::get('/store/dispatches/create', [DispatchController::class, 'create'])->name('store.dispatches.create');
    Route::post('/store/dispatches', [DispatchController::class, 'store'])->name('store.dispatches.store');

    /* ── Distributor routes ── */
    Route::get('/distributor/received', [DistributorController::class, 'receivedIndex'])->name('distributor.received.index');
    Route::post('/distributor/received/{dispatch}/receive', [DistributorController::class, 'markAsReceived'])->name('distributor.received.receive');
    Route::get('/distributor/invoices', [DistributorController::class, 'invoicesIndex'])->name('distributor.invoices.index');
    Route::get('/distributor/payments', [DistributorController::class, 'paymentsIndex'])->name('distributor.payments.index');
    Route::post('/distributor/payments/upload', [DistributorController::class, 'uploadPayment'])->name('distributor.payments.upload');
});
