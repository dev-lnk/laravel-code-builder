<?php

namespace DevLnk\LaravelCodeBuilder\Commands;

use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\Builders\AddActionBuilder;
use DevLnk\LaravelCodeBuilder\Services\Builders\BuildFactory;
use DevLnk\LaravelCodeBuilder\Services\Builders\ModelBuilder;
use DevLnk\LaravelCodeBuilder\Services\CodePath\CodePath;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructureFactory;
use DevLnk\LaravelCodeBuilder\Types\BuildType;
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

        $table = select(
            'Table',
            collect(Schema::getTables())
                ->filter(fn ($v) => str_contains($v['name'], $tableStr))
                ->mapWithKeys(fn ($v) => [$v['name'] => $v['name']]),
            default: 'jobs'
        );

        $this->codeStructure = CodeStructureFactory::makeFromTable($table, $this->argument('entity'));

        $path = config('code_builder.generation_path') ?? select(
            label: 'Where to generate the result?',
            options: [
                '_project' => 'In the project directories',
                'Generation' => 'To the generation folder: `app/Generation`'
            ],
            default: '_project'
        );

        $this->prepareGeneration($path);

        $onlyFlag = $this->option('only');

        $buildFactory = new BuildFactory(
            $this->codeStructure,
            $this->codePath,
            $onlyFlag
        );

        $buildFactory->call(BuildType::MODEL, $this->stubDir.'Model');
    }

    private function prepareGeneration(string $path): void
    {
        $isDir = $path !== '_project';

        $fileSystem = new Filesystem();

        $genPath = app_path($path);

        if($isDir) {
            if(! $fileSystem->isDirectory($genPath)) {
                $fileSystem->makeDirectory($genPath, recursive: true);
                $fileSystem->put($genPath . '/.gitignore', "*\n!.gitignore");
            }

            if(! $fileSystem->isDirectory($genPath . '/Models')) {
                $fileSystem->makeDirectory($genPath . '/Models');
            }
        }

        $modelName = $this->codeStructure->entity()->ucFirstSingular() . '.php';

        $this->codePath
            ->model(
                $modelName,
                $isDir ? $genPath . "/Models"
                    : app_path('Models'),
                $isDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Models'
                    : 'App\\Models'
            )
        ;
    }
}
