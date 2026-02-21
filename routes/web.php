<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ModalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnNoteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== SUPERADMIN ONLY =====
    Route::middleware('role:superadmin')->group(function () {
        // Master Data
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
            Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
            Route::get('/client', [ReportController::class, 'clientReport'])->name('client');
        });
    });

    // ===== SUPERADMIN + STAFF =====
    // Master Data (view by all, manage by superadmin)
    Route::resource('items', ItemController::class);
    Route::post('api/items/quick-store', [ItemController::class, 'quickStore'])->name('api.items.quick-store');
    Route::resource('clients', ClientController::class);

    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve')->middleware('role:superadmin');
    Route::post('purchase-orders/{purchase_order}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject')->middleware('role:superadmin');
    Route::post('purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    Route::post('purchase-orders/{purchase_order}/update-prices', [PurchaseOrderController::class, 'updatePrices'])->name('purchase-orders.update-prices');
    Route::post('purchase-orders/{purchase_order}/set-modal', [PurchaseOrderController::class, 'setModal'])->name('purchase-orders.set-modal');
    Route::post('purchase-orders/{purchase_order}/add-expense', [PurchaseOrderController::class, 'addExpense'])->name('purchase-orders.add-expense');
    Route::get('api/item-price', [PurchaseOrderController::class, 'getItemPrice'])->name('api.item-price');

    // Delivery Notes (Surat Jalan)
    Route::resource('delivery-notes', DeliveryNoteController::class);
    Route::get('delivery-notes/{delivery_note}/print', [DeliveryNoteController::class, 'print'])->name('delivery-notes.print');
    Route::patch('delivery-notes/{delivery_note}/status', [DeliveryNoteController::class, 'updateStatus'])->name('delivery-notes.update-status');
    Route::post('delivery-notes/{delivery_note}/regenerate', [DeliveryNoteController::class, 'regenerate'])->name('delivery-notes.regenerate');

    // Return Notes (Retur)
    Route::resource('return-notes', ReturnNoteController::class)->except(['edit', 'update']);
    Route::patch('return-notes/{return_note}/status', [ReturnNoteController::class, 'updateStatus'])->name('return-notes.update-status');

    // Invoices
    Route::get('invoices/batch', [InvoiceController::class, 'batch'])->name('invoices.batch');
    Route::get('invoices/batch/invoices', [InvoiceController::class, 'getClientInvoices'])->name('invoices.batch-invoices');
    Route::get('invoices/batch/print', [InvoiceController::class, 'batchPrint'])->name('invoices.batch-print');
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('invoices/{invoice}/regenerate', [InvoiceController::class, 'regenerate'])->name('invoices.regenerate');

    // Payments
    Route::resource('payments', PaymentController::class)->except(['edit', 'update', 'show']);

    // Modals (Capital)
    Route::resource('modals', ModalController::class);

    // Expenses
    Route::resource('expenses', ExpenseController::class)->except(['edit', 'update']);
});

require __DIR__.'/auth.php';
