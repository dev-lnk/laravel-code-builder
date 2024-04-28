<?php

namespace DevLnk\LaravelCodeBuilder\Commands;

use DevLnk\LaravelCodeBuilder\CodeBuilder\CodeStructure;
use DevLnk\LaravelCodeBuilder\CodeBuilder\CodeStructureFactory;
use DevLnk\LaravelCodeBuilder\Enums\StubValue;
use DevLnk\LaravelCodeBuilder\Services\CodePath;
use DevLnk\LaravelCodeBuilder\Services\StubBuilder;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Throwable;

use function Laravel\Prompts\select;

class LaravelCodeBuildCommand extends Command
{
    protected $signature = 'code:build {entity} {table?} {--only=}';

    private ?string $only = null;

    private string $stubDir = __DIR__ . '/../../stubs/';

    private CodePath $codePath;

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

        $codeStructure = CodeStructureFactory::makeFromTable($table, $this->argument('entity'));

        $path = config('code_builder.generation_path') ?? select(
            label: 'Where to generate the result?',
            options: [
                '_project' => 'In the project directories',
                'Generation' => 'To the generation folder: `app/Generation`'
            ],
            default: '_project'
        );

        $this->prepareGeneration($path, $codeStructure);

        $this->only = $this->option('only');

        $this->makeModel($codeStructure);
    }

    private function prepareGeneration(string $path, CodeStructure $codeStructure): void
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

        $modelName = $codeStructure->entity()->ucFirstSingular() . '.php';

        $this->codePath
            ->setModel(
                $isDir ? $genPath . "/Models/$modelName"
                    : app_path('Models') . "/$modelName",
                $isDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Models'
                    : 'App\\Models'
            )
        ;
    }

    /**
     * @throws FileNotFoundException|Throwable
     */
    private function makeModel(CodeStructure $codeStructure): void
    {
        if($this->only && $this->only !== 'model') {
            return;
        }

        StubBuilder::make($this->stubDir.'Model')
            ->setKey(
                StubValue::USE_SOFT_DELETES->key(),
                StubValue::USE_SOFT_DELETES->value(),
                $codeStructure->isSoftDeletes()
            )
            ->setKey(
                StubValue::SOFT_DELETES->key(),
                StubValue::SOFT_DELETES->value(),
                $codeStructure->isSoftDeletes()
            )->setKey(
                StubValue::TIMESTAMPS->key(),
                StubValue::TIMESTAMPS->value(),
                ! $codeStructure->isTimestamps()
            )->setKey(
                StubValue::TABLE->key(),
                StubValue::TABLE->value() . " '{$codeStructure->table()}';",
                $codeStructure->table() !== $codeStructure->entity()->str()->plural()->snake()->value()
            )->makeFromStub($this->codePath->modelDir(), [
                '{namespace}' => $this->codePath->modelNamespace(),
                '{class}' => $codeStructure->entity()->ucFirstSingular(),
                '{fillable}' => $codeStructure->columnsToModel(),
            ])
        ;
    }
}
