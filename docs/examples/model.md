## Model
```php
<?php

declare(strict_types=1);

namespace App\Generation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
			'id' => ['int', 'nullable'],
			'title' => ['string', 'nullable'],
			'content' => ['string', 'nullable'],
			'user_id' => ['int', 'nullable'],
			'sort_number' => ['int', 'nullable'],
			'is_active' => ['accepted', 'sometimes'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
```