## Controller
```php
<?php

declare(strict_types=1);

namespace App\Generation\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Generation\Actions\AddProductAction;
use App\Generation\Actions\EditProductAction;
use App\Generation\Http\Requests\ProductRequest;
use App\Generation\Models\Product;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->get();

        return view('product.table', compact('products'));
    }

    public function create(): View
    {
        return view('product.form');
    }

    public function store(ProductRequest $request, AddProductAction $action): RedirectResponse
    {
        $data = $request->validated();
        
        $product = $action->handle($data);

        return back();
    }

    public function edit(string $id)
    {
        $product = Product::query()->where('id', $id)->firstOrFail();

        return view('product.form', compact('product'));
    }

    public function update(string $id, ProductRequest $request, EditProductAction $action): RedirectResponse
    {
        $data = $request->validated();

        $product = $action->handle((int) $id, $data);

        return back();
    }

    public function destroy(string $id)
    {
        Product::query()->where('id', $id)->delete();

        return back();
    }
}
```