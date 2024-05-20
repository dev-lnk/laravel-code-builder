<?php

namespace DevLnk\LaravelCodeBuilder\Commands;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\CodeGenerateCommandException;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundBuilderException;
use DevLnk\LaravelCodeBuilder\Services\Builders\BuildFactory;
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

    private ?string $entity = '';

    private ?string $stubDir = '';

    /**
     * @var array<string, string>
     */
    private array $replaceCautions = [];

    /**
     * @var array<int, BuildType>
     */
    private array $builders = [];

    /**
     * @throws CodeGenerateCommandException
     * @throws NotFoundBuilderException
     */
    public function handle(): int
    {
        $this->setStubDir();

        $this->setEntity();

        $this->prepareBuilders();

        $codeStructures = $this->codeStructures();

        $generationPath = $this->generationPath();

        foreach ($codeStructures as $codeStructure) {
            if(! $codeStructure instanceof CodeStructure) {
                throw new CodeGenerateCommandException('codeStructure is not DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure');
            }
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
            )
        ];
    }

    /**
     * @throws NotFoundBuilderException
     * @throws CodeGenerateCommandException
     */
    protected function make(CodeStructure $codeStructure, string $generationPath): void
    {
        $codeStructure->setStubDir($this->stubDir);

        $codePath = $this->getCodePath();

        $this->prepareGeneration($generationPath, $codeStructure, $codePath);

        $this->buildCode($codeStructure, $codePath);
    }

    /**
     * @throws NotFoundBuilderException
     * @throws CodeGenerateCommandException
     */
    protected function buildCode(CodeStructure $codeStructure, CodePathContract $codePath): void
    {
        $buildFactory = new BuildFactory(
            $codeStructure,
            $codePath,
        );

        foreach ($this->builders as $builder) {
            if(! $builder instanceof BuildType) {
                throw new CodeGenerateCommandException('builder is not DevLnk\LaravelCodeBuilder\Enums\BuildType');
            }

            $confirmed = true;
            if(isset($this->replaceCautions[$builder->value])) {
                $confirmed = confirm($this->replaceCautions[$builder->value]);
            }

            if(! $confirmed) {
                continue;
            }

            $buildFactory->call($builder->value, $this->stubDir . $builder->stub());

            $codePathItem = $codePath->path($builder->value);
            $filePath = substr($codePathItem->file(), strpos($codePathItem->file(), '/app') + 1);
            $this->info($filePath . ' was created successfully!');
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

    public function getCodePath(): CodePathContract
    {
        return new CodePath();
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

    protected function prepareBuilders(): void
    {
        $builders = $this->builders();

        foreach ($builders as $builder) {
            if($this->option($builder->value)) {
                $this->builders[] = $builder;
            }
        }

        if($this->option('builders')) {
            foreach (config('code_builder.builders', []) as $builder) {
                if(! in_array($builder, $this->builders)) {
                    $this->builders[] = $builder;
                }
            }
        }

        if(empty($this->builders)) {
            $this->builders = config('code_builder.builders');
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
                if($fileSystem->isFile($codePath->path($buildType->value)->file())) {
                    $this->replaceCautions[$buildType->value] = $buildType->stub() . " already exists, are you sure you want to replace it?";
                }
            }
        }
    }

    /**
     * @return array<int, BuildType>
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
}
