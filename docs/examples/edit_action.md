## Edit action
```php
<?php

declare(strict_types=1);

namespace App\Generation\Actions;

use App\Generation\Models\Product;

final class EditProductAction
{
    public function handle(int $id, array $data): ?Product
    {
        $product = Product::query()->where('id', $id)->first();

        if(is_null($product)) {
            return null;
        }

        $product->fill($data);

        $product->save();

        $product->refresh();

        return $product;
    }
}
```