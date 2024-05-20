## For contributors

### Release plans:
- [x] BelongsTo
- [x] HasMany
- [x] HasOne
- [x] BelongsToMany
- [x] DTO
- [x] Improved console experience
- [ ] Testing

### Roadmap after release:
- [ ] TS interface support
- [ ] Livewire support
- [ ] Inertia support
- [ ] SpatieLaravelData support


### How to add support for new code generation
1. Add a new type to Enums/BuildType.php, `JOB` for example.
2. Add new CodePath type in Services/CodePath/Advanced, for example: 
```php
<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath;

readonly class JobPath extends AbstractPathItem
{
    public function getBuildAlias(): string
    {
        return BuildType::JOB->value;
    }
}
```
3. Add a new code to Services/CodePath/CodePath.php for your `job`
```php
->setPath(
    new JobPath(
        'filename.php',
        'filedir',
        'namespace'
    )
)
```
4. Create a new contract in the `Services/Builders/Advanced/Contracts`:
```php
interface JobBuilderContract extends BuilderContract
{
}
```
Create a new builder in the `Services/Builders/Advanced` and implement your contract in the builder:
```php
final class JobBuilder extends AbstractBuilder implements JobBuilderContract
{
    /**
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        //your code
    }
}
```
Bind your contract in the LaravelCodeBuilderProvider:
```php
public function register(): void
{
    //...
    $this->app->bind(JobBuilderContract::class, JobBuilder::class);
}
```

Add code to the factory `Services/Builders/BuildFactory.php`
```php
public function call(string $buildType, string $stub): void
{
    match($buildType) {
        //...
        BuildType::JOB->value => app(
            JobBuilderContract::class,
            $classParameters
        ),
```
5. Write your new builder in the configuration file
```php
<?php

use DevLnk\LaravelCodeBuilder\Enums\BuildType;

return [
    'builders' => [
        // Core
        //...
        // Additionally
        BuildType::JOB
    ],
```
6. Create `Job.stub` in `code_stubs`
7. Write the logic in your `build()` method of the `Services/Builders/Advanced/JobBuilder.php` file
8. Write tests
9. During PR the code will be analyzed on phpstan with level 4
### Documentation
- **[Relationship](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/relationship.md)**
- **[Customization](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/customization.md)**
- **[For contributors](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/for_contributors.md)**