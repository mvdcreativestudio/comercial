<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;

use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\EcommerceController;
use App\Http\Controllers\OmnichannelController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;

Route::get('lang/{locale}', [LanguageController::class, 'swap']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        return view('content.dashboard.dashboard-mvd');
    })->name('dashboard');
    Route::get('/clients/datatable', [ClientController::class, 'datatable'])->name('clients.datatable');
    Route::get('/products/datatable', [ProductController::class, 'datatable'])->name('products.datatable');
    Route::get('/product-categories/datatable', [ProductCategoryController::class, 'datatable'])->name('product-categories.datatable');


    // Tiendas / Franquicias
    Route::resource('stores', StoreController::class);
    Route::get('stores/{store}/manage-users', [StoreController::class, 'manageUsers'])->name('stores.manageUsers');
    Route::post('stores/{store}/associate-user', [StoreController::class, 'associateUser'])->name('stores.associateUser');
    Route::post('stores/{store}/disassociate-user', [StoreController::class, 'disassociateUser'])->name('stores.disassociateUser');

    Route::resource('raw-materials', RawMaterialController::class);
});

// Clients
Route::resource('clients', ClientController::class);


// Omnicanalidad
Route::get('omnichannel', [OmnichannelController::class, 'index'])->name('omnichannel');

// E-Commerce
Route::get('shop', [EcommerceController::class, 'index'])->name('shop');
Route::get('store', [EcommerceController::class, 'store'])->name('store');

// Cart
Route::post('/cart/add/{productId}', [CartController::class, 'addToCart'])->name('cart.add');

Route::get('/session/clear', [CartController::class, 'clearSession'])->name('session.clear');

// Checkout
Route::resource('checkout', CheckoutController::class);


// E-Commerce - Backoffice

// E-Commerce - Products
Route::resource('products', ProductController::class);

// E-Commerce - Categories
Route::resource('product-categories', ProductCategoryController::class);

// E-Commerce - Orders
Route::resource('orders', OrderController::class);

Route::get('/ecommerce/marketing', [EcommerceController::class, 'marketing'])->name('marketing');
Route::get('/ecommerce/settings', [EcommerceController::class, 'settings'])->name('settings');

// CRM
Route::get('crm', [CrmController::class, 'index'])->name('crm');

// Contabilidad
Route::get('receipts', [AccountingController::class, 'receipts'])->name('receipts');
Route::get('entries', [AccountingController::class, 'entries'])->name('entries');
Route::get('entrie', [AccountingController::class, 'entrie'])->name('entrie');


 // Invoices
 Route::resource('invoices', InvoiceController::class);


