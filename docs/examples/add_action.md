## Add action
```php
<?php

declare(strict_types=1);

namespace App\Generation\Actions;

use App\Generation\Models\Product;

final class AddProductAction
{
    public function handle(array $data): Product
    {
        $product = new Product();

        $product->fill($data);

        $product->save();

        $product->refresh();

        return $product;
    }
}
```