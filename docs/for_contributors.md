## For contributors

### Release plans:
- [x] BelongsTo
- [x] Select (if BelongsTo)
- [ ] HasMany
- [ ] HasOne
- [ ] DTOs
- [ ] TS interface support

### Roadmap after release:
- [ ] SpatieLaravelData support
- [ ] Livewire support
- [ ] Inertia support
- [ ] MoonShine support


### How to add support for new code generation
1. Add a new type to Enums/BuildType.php, `job` for example.
2. Add new CodePath type in Services/CodePath, for example:
```php
<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath;

readonly class JobPath extends AbstractPath
{

}
```
3. Add a new method to Services/CodePath/CodePath.php for your `job`
```php
public function job(string $name, string $dir, string $namespace): self
{
    if(isset($this->paths[BuildType::JOB->value])) {
        return $this;
    }
    $this->paths[BuildType::JOB->value] = new JobPath($name, $dir, $namespace);
    return $this;
}
```
4. In the `prepareGeneration` method of the `Commands/LaravelCodeBuildCommand.php` file, find the place where all the directories for your builder are written:
```php
$this->codePath
    ->job(
        $this->codeStructure->entity()->ucFirstSingular() . '.php',
        $isGenerationDir ? $genPath . "/Jobs" : app_path('Jobs'),
        $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Jobs' : 'App\\Models'
    )
```
5. Create a new builder in `Services/Builders`
```php
final class JobBuilder extends AbstractBuilder
{
    /**
     * @throws NotFoundCodePathException
     * @throws FileNotFoundException
     */
    public function build(): void
    {
        //your code
    }
}
```
and add its call to the factory `Services/Builders/BuildFactory.php`
```php
public function call(string $buildType, string $stub): void
{
    match($buildType) {
        //...
        BuildType::JOB->value => JobBuilder::make(
            $this->codeStructure,
            $this->codePath,
            $stub,
        )->build()
```
6. Write your new builder in the configuration file
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
7. Create `Job.stub` in `code_stubs`
8. Write the logic in your `build()` method of the `Services/Builders/JobBuilder.php` file
9. Write tests
10. During PR the code will be analyzed on phpstan with level 4