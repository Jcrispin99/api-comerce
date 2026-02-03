<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Tenant\TenantAuthController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
});

// API routes for tenant
Route::prefix('api/v1')->middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Public routes with auth rate limiter (5/min - brute force protection)
    Route::middleware('throttle:auth')->group(function (): void {
        Route::post('register', [TenantAuthController::class, 'register'])->name('tenant.api.v1.register');
        Route::post('login', [TenantAuthController::class, 'login'])->name('tenant.api.v1.login');
    });

    // Protected routes with authenticated rate limiter (120/min)
    Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function (): void {
        Route::post('logout', [TenantAuthController::class, 'logout'])->name('tenant.api.v1.logout');
        Route::get('me', [TenantAuthController::class, 'me'])->name('tenant.api.v1.me');

        // Categories
        Route::apiResource('categories', \App\Http\Controllers\Api\V1\Tenant\CategoryController::class)
            ->names('tenant.api.v1.categories');

        // Companies
        Route::apiResource('companies', \App\Http\Controllers\Api\V1\Tenant\CompanyController::class)
            ->names('tenant.api.v1.companies');

        // Users
        Route::apiResource('users', \App\Http\Controllers\Api\V1\Tenant\UserController::class)
            ->names('tenant.api.v1.users');

        // Attributes
        Route::apiResource('attributes', \App\Http\Controllers\Api\V1\Tenant\AttributeController::class)
            ->names('tenant.api.v1.attributes');

        // Products
        Route::apiResource('products', \App\Http\Controllers\Api\V1\Tenant\ProductController::class)
            ->names('tenant.api.v1.products');

        // Warehouses
        Route::apiResource('warehouses', \App\Http\Controllers\Api\V1\Tenant\WarehouseController::class)
            ->names('tenant.api.v1.warehouses');

        // Taxes
        Route::apiResource('taxes', \App\Http\Controllers\Api\V1\Tenant\TaxController::class)
            ->names('tenant.api.v1.taxes');

        // Customers
        Route::apiResource('customers', \App\Http\Controllers\Api\V1\Tenant\CustomerController::class)
            ->names('tenant.api.v1.customers');

        // Suppliers
        Route::apiResource('suppliers', \App\Http\Controllers\Api\V1\Tenant\SupplierController::class)
            ->names('tenant.api.v1.suppliers');

        // Journals
        Route::apiResource('journals', \App\Http\Controllers\Api\V1\Tenant\JournalController::class)
            ->names('tenant.api.v1.journals');

        // Purchases
        Route::post('purchases/{purchase}/post', [\App\Http\Controllers\Api\V1\Tenant\PurchaseController::class, 'post'])
            ->name('tenant.api.v1.purchases.post');
        Route::apiResource('purchases', \App\Http\Controllers\Api\V1\Tenant\PurchaseController::class)
            ->names('tenant.api.v1.purchases');
    });
});
