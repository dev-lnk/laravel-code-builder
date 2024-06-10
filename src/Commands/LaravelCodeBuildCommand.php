<?php

namespace DevLnk\LaravelCodeBuilder\Commands;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Enums\BuildTypeContract;
use DevLnk\LaravelCodeBuilder\Exceptions\CodeGenerateCommandException;
use DevLnk\LaravelCodeBuilder\Services\Builders\Factory\AbstractBuildFactory;
use DevLnk\LaravelCodeBuilder\Services\Builders\Factory\BuildFactory;
use DevLnk\LaravelCodeBuilder\Services\CodePath\CodePath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\CodePathContract;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\Factories\CodeStructureFromMysql;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

class LaravelCodeBuildCommand extends Command
{
    protected $signature = 'code:build {entity} {table?} {--model} {--request} {--addAction} {--editAction} {--request} {--controller} {--route} {--form} {--DTO} {--table} {--builders} {--has-many=*} {--has-one=*} {--belongs-to-many=*}';

    protected ?string $entity = '';

    protected ?string $stubDir = '';

    /**
     * @var array<string, string>
     */
    protected array $replaceCautions = [];

    /**
     * @var array<int, BuildTypeContract>
     */
    protected array $builders = [];

    /**
     * @throws CodeGenerateCommandException
     */
    public function handle(): int
    {
        $this->setStubDir();

        $this->setEntity();

        $this->prepareBuilders();

        $codeStructures = $this->codeStructures();

        $generationPath = $this->generationPath();

        foreach ($codeStructures as $codeStructure) {
            $this->make($codeStructure, $generationPath);
        }

        return self::SUCCESS;
    }

    /**
     * @throws CodeGenerateCommandException
     *
     * @return array<int, CodeStructure>
     */
    protected function codeStructures(): array
    {
        $tableStr = $this->argument('table') ?? '';
        if(is_array($tableStr)) {
            throw new CodeGenerateCommandException('The table argument must not be an array');
        }

        $table = select(
            'Table',
            collect(Schema::getTables())
                ->filter(fn ($v) => str_contains((string) $v['name'], (string) $tableStr))
                ->mapWithKeys(fn ($v) => [$v['name'] => $v['name']]),
            default: 'jobs'
        );

        $confirmBelongsTo = config('code_builder.belongs_to');
        if(is_null($confirmBelongsTo)) {
            $confirmBelongsTo = confirm("Generate BelongsTo relations from foreign keys?");
        }

        $hasMany = $this->option('has-many');
        if(! is_array($hasMany)) {
            throw new CodeGenerateCommandException('The has-many option must be an array');
        }

        $hasOne = $this->option('has-one');
        if(! is_array($hasOne)) {
            throw new CodeGenerateCommandException('The has-one option must be an array');
        }

        $belongsToMany = $this->option('belongs-to-many');
        if(! is_array($belongsToMany)) {
            throw new CodeGenerateCommandException('The belongs-to-many option must be an array');
        }

        return [
            CodeStructureFromMysql::make(
                (string) $table,
                $this->entity,
                $confirmBelongsTo,
                $hasMany,
                $hasOne,
                $belongsToMany
            ),
        ];
    }

    public function generationPath(): string
    {
        return config('code_builder.generation_path') ?? select(
            label: 'Where to generate the result?',
            options: [
                '_default' => 'In the project directories',
                'app/Generation' => 'To the generation folder: `app/Generation`',
            ],
            default: '_default'
        );
    }

    /**
     * @throws CodeGenerateCommandException
     */
    protected function make(CodeStructure $codeStructure, string $generationPath): void
    {
        $codeStructure->setStubDir($this->stubDir);

        $codePath = $this->codePath();

        $this->prepareGeneration($generationPath, $codeStructure, $codePath);

        $this->buildCode($codeStructure, $codePath);
    }

    /**
     * @throws CodeGenerateCommandException
     */
    protected function buildCode(CodeStructure $codeStructure, CodePathContract $codePath): void
    {
        $buildFactory = $this->buildFactory(
            $codeStructure,
            $codePath,
        );

        foreach ($this->builders as $builder) {
            if(! $builder instanceof BuildTypeContract) {
                throw new CodeGenerateCommandException('builder is not DevLnk\LaravelCodeBuilder\Enums\BuildTypeContract');
            }

            $confirmed = true;
            if(isset($this->replaceCautions[$builder->value()])) {
                $confirmed = confirm($this->replaceCautions[$builder->value()]);
            }

            if(! $confirmed) {
                continue;
            }

            $buildFactory->call($builder->value(), $this->stubDir . $builder->stub());
            $filePath = $codePath->path($builder->value())->file();
            $this->info($this->projectFileName($filePath) . ' was created successfully!');
        }
    }

    protected function setStubDir(): void
    {
        $this->stubDir = config('code_builder.stub_dir', __DIR__ . '/../../code_stubs') . '/';
    }

    /**
     * @throws CodeGenerateCommandException
     */
    protected function setEntity(): void
    {
        $entity = $this->argument('entity');
        if(is_array($entity)) {
            throw new CodeGenerateCommandException('The entity argument must not be an array');
        }
        $this->entity = $entity;
    }

    protected function prepareBuilders(): void
    {
        $builders = $this->builders();

        foreach ($builders as $builder) {
            if($this->option($builder->value())) {
                $this->builders[] = $builder;
            }
        }

        if($this->option('builders')) {
            foreach ($this->configBuilders() as $builder) {
                if(! in_array($builder, $this->builders)) {
                    $this->builders[] = $builder;
                }
            }
        }

        if(empty($this->builders)) {
            $this->builders = $this->configBuilders();
        }
    }

    protected function prepareGeneration(string $generationPath, CodeStructure $codeStructure, CodePathContract $codePath): void
    {
        $isGenerationDir = $generationPath !== '_default';

        $fileSystem = new Filesystem();

        if($isGenerationDir) {
            $genPath = base_path($generationPath);
            if(! $fileSystem->isDirectory($genPath)) {
                $fileSystem->makeDirectory($genPath, recursive: true);
                $fileSystem->put($genPath . '/.gitignore', "*\n!.gitignore");
            }
        }

        $codePath->initPaths($codeStructure, $generationPath, $isGenerationDir);

        if(! $isGenerationDir) {
            foreach ($this->builders as $buildType) {
                if($fileSystem->isFile($codePath->path($buildType->value())->file())) {
                    $this->replaceCautions[$buildType->value()] =
                        $this->projectFileName($codePath->path($buildType->value())->file()) . " already exists, are you sure you want to replace it?";
                }
            }
        }
    }

    protected function codePath(): CodePathContract
    {
        return new CodePath();
    }

    protected function buildFactory(
        CodeStructure $codeStructure,
        CodePathContract $codePath
    ): AbstractBuildFactory {
        return new BuildFactory(
            $codeStructure,
            $codePath
        );
    }

    /**
     * @return array<int, BuildTypeContract>
     */
    protected function builders(): array
    {
        return [
            BuildType::MODEL,
            BuildType::ADD_ACTION,
            BuildType::EDIT_ACTION,
            BuildType::REQUEST,
            BuildType::CONTROLLER,
            BuildType::ROUTE,
            BuildType::FORM,
            BuildType::DTO,
            BuildType::TABLE,
        ];
    }

    /**
     * @return array<int, BuildTypeContract>
     */
    protected function configBuilders(): array
    {
        $builders = (array) config('code_builder.builders', []);
        return array_map(function ($builder) {
            return ($builder instanceof BuildType) ? $builder : BuildType::from($builder);
        }, $builders);
    }

    protected function projectFileName(string $filePath): string
    {
        if(str_contains($filePath, '/resources/views')) {
            return substr($filePath, strpos($filePath, '/resources/views') + 1);
        }

        if(str_contains($filePath, '/routes')) {
            return substr($filePath, strpos($filePath, '/routes') + 1);
        }

        return substr($filePath, strpos($filePath, '/app') + 1);
    }
}
