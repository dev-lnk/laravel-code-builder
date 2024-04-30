## Generating laravel code from SQL table schema

### Description
Hello Laravel users!
This package allows you to generate code from the schema of your SQL table. The following entities are generated:
- Model
- FormRequest
- Controller (with store and edit methods)
- Actions
- Route
- Form
### Installation
```shell
composer require dev-lnk/laravel-code-builder
```
### Configuration:
Publish the package configuration file:
```shell
php artisan vendor:publish --tag=laravel-code-builder
```
### Examples
Model
```php
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
}
```
Controller
```php
class ProductController extends Controller
{
    public function store(ProductRequest $request, AddProductAction $action): RedirectResponse
    {
        $data = $request->validated();
        
        $model = $action->handle($data);

        return back();
    }

    public function edit(string $id, ProductRequest $request, EditProductAction $action): RedirectResponse
    {
        $data = $request->validated();

        $model = $action->handle((int) $id, $data);

        return back();
    }
}
```
FormRequest
```php
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
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
```
Form
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
        <input id="user_id" name="user_id" value="{{ old('user_id') }}" type="number"/>
    </div>
    <div>
        <label for="category_id">category_id</label>
        <input id="category_id" name="category_id" value="{{ old('category_id') }}" type="number"/>
    </div>
    <div>
        <label for="is_active">is_active</label>
        <input id="is_active" name="is_active" value="{{ old('is_active') }}" type="number"/>
    </div>
    <button type="submit">Submit</button>
</form>
```
### Usage
```shell
php artisan code:build user
```
You will be offered a list of your tables, choose which table to generate the code based on:
```shell
 ┌ Table ───────────────────────────────────────────────────────┐
 │   ○ migrations                                             │ │
 │   ○ password_reset_tokens                                  │ │
 │   ○ products                                               │ │
 │   ○ sessions                                               │ │
 │ › ● users                                                  ┃ │
 └──────────────────────────────────────────────────────────────┘
```
You can also specify part of the table name to shorten the list
```shell
php artisan code:build user us
 ┌ Table ───────────────────────────────────────────────────────┐
 │ › ● users                                                    │
 └──────────────────────────────────────────────────────────────┘
```
If you did not specify a `generation_path` in the configuration file, you will be offered 2 options:
```shell
 ┌ Where to generate the result? ───────────────────────────────┐
 │ › ● In the project directories                               │
 │   ○ To the generation folder: `app/Generation`               │
 └──────────────────────────────────────────────────────────────┘
```
In the first option, all files will be generated according to the folders of your app_path directory. If a file with the same name is found, you will be prompted to replace it:
```shell
app/Models/User.php was created successfully!
...
 ┌ Controller already exists, are you sure you want to replace it? ┐
 │ Yes                                                             │
 └─────────────────────────────────────────────────────────────────┘

app/Http/Controllers/UserController.php was created successfully!
...
```
In the second option, all files will be generated in the `app/Generation` folder
```shell
app/Generation/Models/User.php was created successfully!
...
```
You can generate one thing using the only flag:
```shell
php artisan code:build user --only=model
```
Available options for the only flag:
- model
- addAction
- editAction
- request
- controller
- route
- form

In the `builders` configuration you can comment out those builders that you do not want to be executed
```php
use DevLnk\LaravelCodeBuilder\Enums\BuildType;

return [
    'builders' => [
        BuildType::MODEL,
//        BuildType::ADD_ACTION,
//        BuildType::EDIT_ACTION,
//        BuildType::REQUEST,
//        BuildType::CONTROLLER,
//        BuildType::ROUTE,
        BuildType::FORM,
    ],
    //...
];
```
If you want to change the code generation option, you can publish the stubs and change them yourself:
```shell
php artisan vendor:publish --tag=laravel-code-builder-stubs
```
Stubs will be copied to the directory `<your-base-bath>/code_stubs`.Don't forget to specify your directory in the `stub_dir` setting.

 ### Beta
This is a beta version of the package! Stubs and commands may change during development. Work tested on sqlite and MySQL. It is planned to add to the release version:
- Generate belongsTo relation
- Generating the Livewire component
- Inertia support
- And other