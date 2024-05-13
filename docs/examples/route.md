## Routes
```php
<?php

declare(strict_types=1);

use App\Generation\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->controller(ProductController::class)->group(function (): void {
    Route::get('/', 'index')->name('products.index');
    Route::get('/create', 'create')->name('products.create');
    Route::post('/', 'store')->name('products.store');
    Route::get('/{id}/edit', 'edit')->name('products.edit');
    Route::put('/{id}', 'update')->name('products.update');
    Route::delete('/{id}', 'destroy')->name('products.destroy');
});
```