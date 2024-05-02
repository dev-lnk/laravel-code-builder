## Relationship
### BelongsTo
If the `belongs_to` setting is not specified in the `code_builder.php` configuration file, a choice will be prompted when the command runs:
```shell
Generate BelongsTo relations from foreign keys?
```
The BelongsTo relation will be formed based on your foreign keys, for example:
```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}

public function category(): BelongsTo
{
    return $this->belongsTo(Category::class, 'category_id');
}
```
### Other relationships
The relations HasOne, HasMany, BelongsToMany cannot be defined using a table schema, so special flags must be specified for them

### HasOne
The HasOne relation has a `{--has-one=*}` flag, example:
```shell
php artisan code:build product --has-one=images
```
Result:
```php
public function image(): HasOne
{
    return $this->hasOne(Image::class, 'product_id');
}
```
### HasMany
The HasMany relation has a `{--has-many=*}` flag, example:
```shell
php artisan code:build product --has-many=comments
```
Result:
```php
public function comments(): HasMany
{
    return $this->hasMany(Comment::class, 'product_id');
}
```
### BelongsToMany
The BelongsToMany relation has a `{--belongs-to-many=*}` flag, example:
```shell
php artisan code:build product --belongs-to-many=properties
```
Result:
```php
public function properties(): BelongsToMany
{
    return $this->belongsToMany(Property::class);
}
```
You must configure the pivot table yourself
### Summary
An example of building all types of relationships
```shell
php artisan code:build product --has-one=images --has-many=comments --belongs-to-many=properties
```
Model:
```php
<?php

declare(strict_types=1);

namespace App\Generation\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'sort_number',
        'user_id',
        'category_id',
        'is_active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'product_id');
    }
    
    public function image(): HasOne
    {
        return $this->hasOne(Image::class, 'product_id');
    }
    
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }
}
```
FormRequest:
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
            'sort_number' => ['int', 'nullable'],
            'user_id' => ['int', 'nullable'],
            'category_id' => ['int', 'nullable'],
            'is_active' => ['int', 'nullable'],
            'properties' => ['array', 'nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
```
View:
```html
<form action="{{ route('product.store') }}" method="POST">
    @csrf
    <div>
        <label for="title">title</label>
        <input id="title" name="title" value="{{ old('title') }}"/>
    </div>
    <div>
        <label for="content">content</label>
        <input id="content" name="content" value="{{ old('content') }}"/>
    </div>
    <div>
        <label for="sort_number">sort_number</label>
        <input id="sort_number" name="sort_number" value="{{ old('sort_number') }}" type="number"/>
    </div>
    <div>
        <label for="user_id">user_id</label>
        <select id="user_id" name="user_id">
            <option value="">Not selected</option>
        </select>
    </div>
    <div>
        <label for="category_id">category_id</label>
        <select id="category_id" name="category_id">
            <option value="">Not selected</option>
        </select>
    </div>
    <div>
        <label for="is_active">is_active</label>
        <input id="is_active" name="is_active" value="{{ old('is_active') }}" type="number"/>
    </div>
    <div>
        <label for="properties">properties</label>
        <select id="properties" name="properties" multiple>
            <option value="">Not selected</option>
        </select>
    </div>
    <button type="submit">Submit</button>
</form>
```