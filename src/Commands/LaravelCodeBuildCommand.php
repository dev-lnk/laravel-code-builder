<?php

namespace DevLnk\LaravelCodeBuilder\Commands;

use DevLnk\LaravelCodeBuilder\Services\Builders\BuildFactory;
use DevLnk\LaravelCodeBuilder\Services\CodePath\CodePath;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructureFactory;
use DevLnk\LaravelCodeBuilder\Types\BuildType;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Throwable;

use function Laravel\Prompts\select;

class LaravelCodeBuildCommand extends Command
{
    protected $signature = 'code:build {entity} {table?} {--only=}';

    private string $stubDir = __DIR__ . '/../../stubs/';

    private CodePath $codePath;

    private CodeStructure $codeStructure;

    /**
     * @throws FileNotFoundException|Throwable
     */
    public function handle(): void
    {
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

        if(! $onlyFlag || $onlyFlag === BuildType::MODEL) {
            $buildFactory->call(BuildType::MODEL, $this->stubDir.'Model');
            $this->info('Model was created successfully!');
        }

        if(! $onlyFlag || $onlyFlag === BuildType::ADD_ACTION) {
            $buildFactory->call(BuildType::ADD_ACTION, $this->stubDir.'AddAction');
            $this->info('AddAction was created successfully!');
        }

        if(! $onlyFlag || $onlyFlag === BuildType::EDIT_ACTION) {
            $buildFactory->call(BuildType::EDIT_ACTION, $this->stubDir.'EditAction');
            $this->info('EditAction was created successfully!');
        }

        if(! $onlyFlag || $onlyFlag === BuildType::REQUEST) {
            $buildFactory->call(BuildType::REQUEST, $this->stubDir.'Request');
            $this->info('FormRequest was created successfully!');
        }

        if(! $onlyFlag || $onlyFlag === BuildType::CONTROLLER) {
            $buildFactory->call(BuildType::CONTROLLER, $this->stubDir.'Controller');
            $this->info('Controller was created successfully!');
        }

        if(! $onlyFlag || $onlyFlag === BuildType::ROUTE) {
            $buildFactory->call(BuildType::ROUTE, $this->stubDir.'Route');
            $this->info('Route was created successfully!');
        }

        if(! $onlyFlag || $onlyFlag === BuildType::FORM) {
            $buildFactory->call(BuildType::FORM, $this->stubDir.'Form');
            $this->info('Form was created successfully!');
        }
    }

    private function prepareGeneration(string $path): void
    {
        $isDir = $path !== '_default';

        $fileSystem = new Filesystem();

        $genPath = app_path($path);

        if($isDir) {
            $generateDirs = [
                'Models',
                'Actions',
                'Http/Requests',
                'Http/Controllers',
                'routes',
                'resource/views'
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
                'routes',
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
                $isDir ? $genPath . "/Models" : app_path('Models'),
                $isDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Models' : 'App\\Models'
            )
            ->addAction(
                'Add' .$this->codeStructure->entity()->ucFirstSingular() . 'Action.php',
                $isDir ? $genPath . "/Actions" : app_path('Actions'),
                $isDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Actions' : 'App\\Actions'
            )->editAction(
                'Edit' .$this->codeStructure->entity()->ucFirstSingular() . 'Action.php',
                $isDir ? $genPath . "/Actions" : app_path('Actions'),
                $isDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Actions' : 'App\\Actions'
            )->request(
                $this->codeStructure->entity()->ucFirstSingular() . 'Request.php',
                $isDir ? $genPath . "/Http/Requests" : app_path('Http/Requests'),
                $isDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Http\\Requests' : 'App\\Http\\Requests'
            )->controller(
                $this->codeStructure->entity()->ucFirstSingular() . 'Controller.php',
                $isDir ? $genPath . "/Http/Controllers" : app_path('Http/Controllers'),
                $isDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Http\\Controllers' : 'App\\Http\\Controllers'
            )->route(
                $this->codeStructure->entity()->lower(). '.php',
                $isDir ? $genPath . "/routes" : base_path('routes'),
                ''
            )->form(
                $this->codeStructure->entity()->lower(). '.blade.php',
                $isDir ? $genPath . "/resources/views" : base_path('resources/views'),
                ''
            )
        ;
    }
}
