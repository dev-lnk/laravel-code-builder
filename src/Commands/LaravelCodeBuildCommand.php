<?php

namespace DevLnk\LaravelCodeBuilder\Commands;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\Builders\BuildFactory;
use DevLnk\LaravelCodeBuilder\Services\CodePath\CodePath;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructureFactory;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

class LaravelCodeBuildCommand extends Command
{
    protected $signature = 'code:build {entity} {table?} {--only=}';

    private CodePath $codePath;

    private CodeStructure $codeStructure;

    /**
     * @var array<string, string>
     */
    private array $replaceCautions = [];

    /**
     * @var array<int, BuildType> $builders
     */
    private array $builders = [];

    /**
     * @throws FileNotFoundException|Throwable
     */
    public function handle(): void
    {
        $stubDir = config('code_builder.stub_dir', __DIR__ . '/../../code_stubs') . '/';

        $this->builders = config('code_builder.builders');

        $this->codePath = new CodePath();

        $tableStr = $this->argument('table') ?? '';
        if(is_array($tableStr)) {
            throw new Exception('table must be a string');
        }

        $table = select(
            'Table',
            collect(Schema::getTables())
                ->filter(fn ($v) => str_contains((string) $v['name'], (string) $tableStr))
                ->mapWithKeys(fn ($v) => [$v['name'] => $v['name']]),
            default: 'jobs'
        );

        $entity = $this->argument('entity');
        if(is_array($entity)) {
            throw new Exception('entity must be a string');
        }

        $this->codeStructure = CodeStructureFactory::makeFromTable((string) $table, (string) $entity);

        $this->codeStructure->setStubDir($stubDir);

        $path = config('code_builder.generation_path') ?? select(
            label: 'Where to generate the result?',
            options: [
                '_default' => 'In the project directories',
                'Generation' => 'To the generation folder: `app/Generation`'
            ],
            default: '_default'
        );

        $this->prepareGeneration($path);

        $onlyFlag = $this->option('only');
        if(is_array($onlyFlag)) {
            throw new Exception('only flag must be a string');
        }

        $buildFactory = new BuildFactory(
            $this->codeStructure,
            $this->codePath,
        );

        if($onlyFlag) {
            $onlyBuilder = BuildType::tryFrom((string) $onlyFlag);
            if(! is_null($onlyBuilder) && ! in_array($onlyBuilder, $this->builders)) {
                $this->builders[] = $onlyBuilder;
            }
        }

        foreach ($this->builders as $builder) {
            if(! $onlyFlag || $onlyFlag === $builder->value) {
                $confirmed = true;
                if(isset($this->replaceCautions[$builder->value])) {
                    $confirmed = confirm($this->replaceCautions[$builder->value]);
                }

                if($confirmed) {
                    $buildFactory->call($builder->value, $stubDir . $builder->stub());

                    $codePath = $this->codePath->path($builder->value);
                    $filePath = substr($codePath->file(), strpos($codePath->file(), '/app') + 1 );
                    $this->info($filePath . ' was created successfully!');
                }
            }
        }
    }

    /**
     * @throws NotFoundCodePathException
     */
    private function prepareGeneration(string $path): void
    {
        $isGenerationDir = $path !== '_default';

        $fileSystem = new Filesystem();

        $genPath = app_path($path);

        if($isGenerationDir) {
            $generateDirs = [
                'Models',
                'Actions',
                'Http/Requests',
                'Http/Controllers',
                'routes',
                'resources/views'
            ];

            if(! $fileSystem->isDirectory($genPath)) {
                $fileSystem->makeDirectory($genPath, recursive: true);
                $fileSystem->put($genPath . '/.gitignore', "*\n!.gitignore");
            }

            foreach ($generateDirs as $dir) {
                if(! $fileSystem->isDirectory($genPath . '/' . $dir)) {
                    $fileSystem->makeDirectory($genPath . '/' . $dir, recursive: true);
                }
            }
        } else {
            $generateProjectDirs = [
                'Actions',
                'Http/Requests',
                'Http/Controllers',
            ];

            foreach ($generateProjectDirs as $dir) {
                if(! $fileSystem->isDirectory(app_path($dir))) {
                    $fileSystem->makeDirectory(app_path($dir));
                }
            }
        }

        $this->codePath
            ->model(
                $this->codeStructure->entity()->ucFirstSingular() . '.php',
                $isGenerationDir ? $genPath . "/Models" : app_path('Models'),
                $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Models' : 'App\\Models'
            )
            ->addAction(
                'Add' .$this->codeStructure->entity()->ucFirstSingular() . 'Action.php',
                $isGenerationDir ? $genPath . "/Actions" : app_path('Actions'),
                $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Actions' : 'App\\Actions'
            )->editAction(
                'Edit' .$this->codeStructure->entity()->ucFirstSingular() . 'Action.php',
                $isGenerationDir ? $genPath . "/Actions" : app_path('Actions'),
                $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Actions' : 'App\\Actions'
            )->request(
                $this->codeStructure->entity()->ucFirstSingular() . 'Request.php',
                $isGenerationDir ? $genPath . "/Http/Requests" : app_path('Http/Requests'),
                $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Http\\Requests' : 'App\\Http\\Requests'
            )->controller(
                $this->codeStructure->entity()->ucFirstSingular() . 'Controller.php',
                $isGenerationDir ? $genPath . "/Http/Controllers" : app_path('Http/Controllers'),
                $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Http\\Controllers' : 'App\\Http\\Controllers'
            )->route(
                $this->codeStructure->entity()->lower(). '.php',
                $isGenerationDir ? $genPath . "/routes" : base_path('routes'),
                ''
            )->form(
                $this->codeStructure->entity()->lower(). '.blade.php',
                $isGenerationDir ? $genPath . "/resources/views" : base_path('resources/views'),
                ''
            )
        ;

        if(! $isGenerationDir) {
            foreach ($this->builders as $buildType) {
                if($fileSystem->isFile($this->codePath->path($buildType->value)->file())) {
                    $this->replaceCautions[$buildType->value] = $buildType->stub() . " already exists, are you sure you want to replace it?";
                }
            }
        }
    }
}
