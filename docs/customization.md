## Customization of templates and methods
#### Stubs
If you want to change stubs, you can publish them in your base_dir
```shell
php artisan vendor:publish --tag=laravel-code-builder-stubs
```
Stubs will be copied to the directory `<your-base-bath>/code_stubs`.Don't forget to specify your directory in the `stub_dir` setting.
#### Builders
If you want to change the way the code is generated inside the stub, you can create your own builder and register it with the provider, your builder:
```php
class CustomAddActionBuilder extends AbstractBuilder implements AddActionBuilderContract
{
    /**
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        // Your code
    }
}
```
Provider:
```php
public function register(): void
{
    $this->app->bind(
        AddActionBuilderContract::class,
        CustomAddActionBuilder::class
    );
}
```
#### Command
You can create your own generation command and create your own CodeStructure array:
```php
class MyCodeBuildCommand extends LaravelCodeBuildCommand
{
    protected $signature = 'my-code:build {entity} {table?} {--model} {--request} {--addAction} {--editAction} {--request} {--controller} {--route} {--form} {--table} {--DTO} {--builders} {--has-many=*} {--has-one=*} {--belongs-to-many=*}';

     /**
     * @return array<int, CodeStructure>
     */
    protected function codeStructures(): array
    {
        //Your CodeStructure content, maybe from json or xml?
    }
}
```