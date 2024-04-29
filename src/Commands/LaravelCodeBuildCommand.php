<?php

namespace DevLnk\LaravelCodeBuilder\Commands;

use DevLnk\LaravelCodeBuilder\Services\Builders\BuildFactory;
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
                '_default' => 'In the project directories',
                'Generation' => 'To the generation folder: `app/Generation`'
            ],
            default: '_default'
        );

        $this->prepareGeneration($path);

        $onlyFlag = $this->option('only');

        $buildFactory = new BuildFactory(
            $this->codeStructure,
            $this->codePath,
            $onlyFlag
        );

        $buildFactory->call(BuildType::MODEL, $this->stubDir.'Model');
        $this->info('The model was created successfully!');

        $buildFactory->call(BuildType::ADD_ACTION, $this->stubDir.'AddAction');
        $this->info('The AddAction was created successfully!');
    }

    private function prepareGeneration(string $path): void
    {
        $isDir = $path !== '_default';

        $fileSystem = new Filesystem();

        $genPath = app_path($path);

        if($isDir) {
            $generateDirs = [
                'Models',
                'Actions'
            ];

            if(! $fileSystem->isDirectory($genPath)) {
                $fileSystem->makeDirectory($genPath, recursive: true);
                $fileSystem->put($genPath . '/.gitignore', "*\n!.gitignore");
            }

            foreach ($generateDirs as $dir) {
                if(! $fileSystem->isDirectory($genPath . '/' . $dir)) {
                    $fileSystem->makeDirectory($genPath . '/' . $dir);
                }
            }
        } else {
            $generateProjectDirs = [
                'Actions'
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
            )
        ;
    }
}
