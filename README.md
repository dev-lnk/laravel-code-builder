## Generating laravel code from SQL table schema

### Description
Hello Laravel users!
This package allows you to generate code from the schema of your SQL table. The following entities are generated:
- Model
- FormRequest
- DTO
- Controller (with store and edit methods)
- Actions
- Route
- Form
### Installation
```shell
composer require dev-lnk/laravel-code-builder --dev
```
### Configuration:
Publish the package configuration file:
```shell
php artisan vendor:publish --tag=laravel-code-builder
```
### Examples
Model:
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
FormRequest:
```php
class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['int', 'nullable'],
            'content' => ['string', 'nullable'],
            'title' => ['string', 'nullable'],
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
DTO:
```php
readonly class ProductDTO
{
    public function __construct(
        private int $id,
        private string $content,
        private string $title = 'Default',
        private int $sortNumber = 0,
        private bool $isActive = false,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
        private ?string $deletedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
       return new self(
            id: $data['id'],
            content: $data['content'],
            title: $data['title'] ?? 'Default',
            sortNumber: $data['sort_number'] ?? 0,
            isActive: $data['is_active'] ?? false,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            deletedAt: $data['deleted_at'] ?? null,
       );
    }

    public static function fromRequest(ProductRequest $request): self
    {
        return new self(
            id: (int) $request->input('id'),
            content: $request->input('content'),
            title: $request->input('title'),
            sortNumber: (int) $request->input('sort_number'),
            isActive: $request->has('is_active'),
        );
    }

    public static function fromModel(Product $model): self
    {
        return new self(
            id: (int) $model->id,
            content: $model->content,
            title: $model->title,
            sortNumber: (int) $model->sort_number,
            isActive: (bool) $model->is_active,
            createdAt: $model->created_at?->format('Y-m-d H:i:s'),
            updatedAt: $model->updated_at?->format('Y-m-d H:i:s'),
            deletedAt: $model->deleted_at?->format('Y-m-d H:i:s'),
        );
    }
    // Getters...
    // toArray...
}
```
Form:
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
        <label for="is_active">is_active</label>
        <input type="checkbox" id="is_active" name="is_active" value="1" @if(old('is_active')) checked @endif/>
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
In the `builders` configuration you can comment out those builders that you do not want to be executed
```php
use DevLnk\LaravelCodeBuilder\Enums\BuildType;

return [
    'builders' => [
        BuildType::MODEL,
//        BuildType::DTO,
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
You can generate certain entities using flags:
```shell
php artisan code:build user --model --request
```
Available options for the only flag:
- `--model`
- `--request`
- `--DTO`
- `--addAction`
- `--editAction`
- `--controller`
- `--route`
- `--form`
- `--builder` - Generates all builders specified in the `builders` configuration + your specified flag, for example:
```shell
php artisan code:build user --builders --request
```
If you want to change the code generation option, you can publish the stubs and change them yourself:
```shell
php artisan vendor:publish --tag=laravel-code-builder-stubs
```
Stubs will be copied to the directory `<your-base-bath>/code_stubs`.Don't forget to specify your directory in the `stub_dir` setting.

### Relationship
Relationship **[documentation](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/relationship.md)**.

 ### Beta
This is a beta version of the package! Stubs and commands may change during development. Work tested on sqlite and MySQL. Plans for adding new functionality and information for contributors **[here](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/for_contributors.md)**.
