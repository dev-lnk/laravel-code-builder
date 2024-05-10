<?php

namespace DevLnk\LaravelCodeBuilder\Commands;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\CodeGenerateCommandException;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundBuilderException;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\Builders\BuildFactory;
use DevLnk\LaravelCodeBuilder\Services\CodePath\CodePath;
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

    private CodePath $codePath;

    private CodeStructure $codeStructure;

    private ?string $entity = '';

    /**
     * @var array<string, string>
     */
    private array $replaceCautions = [];

    /**
     * @var array<int, BuildType>
     */
    private array $builders = [];

    /**
     * @throws NotFoundCodePathException
     * @throws CodeGenerateCommandException
     * @throws NotFoundBuilderException
     */
    public function handle(): void
    {
        $stubDir = config('code_builder.stub_dir', __DIR__ . '/../../code_stubs') . '/';

        $this->prepareBuilders();

        $this->setEntity();

        $this->codePath = new CodePath();

        $this->codeStructure = $this->getCodeStructure();

        $this->codeStructure->setStubDir($stubDir);

        $path = config('code_builder.generation_path') ?? select(
            label: 'Where to generate the result?',
            options: [
                '_default' => 'In the project directories',
                'app/Generation' => 'To the generation folder: `app/Generation`',
            ],
            default: '_default'
        );

        $this->prepareGeneration($path);

        $buildFactory = new BuildFactory(
            $this->codeStructure,
            $this->codePath,
        );

        foreach ($this->builders as $builder) {
            $confirmed = true;
            if(isset($this->replaceCautions[$builder->value])) {
                $confirmed = confirm($this->replaceCautions[$builder->value]);
            }

            if(! $confirmed) {
                continue;
            }

            $buildFactory->call($builder->value, $stubDir . $builder->stub());

            $codePath = $this->codePath->path($builder->value);
            $filePath = substr($codePath->file(), strpos($codePath->file(), '/app') + 1);
            $this->info($filePath . ' was created successfully!');
        }
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

    /**
     * @throws CodeGenerateCommandException
     */
    protected function getCodeStructure(): CodeStructure
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

        return CodeStructureFromMysql::make(
            (string) $table,
            $this->entity,
            $confirmBelongsTo,
            $hasMany,
            $hasOne,
            $belongsToMany
        );
    }

    protected function prepareBuilders(): void
    {
        $builders = [
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

    /**
     * @throws NotFoundCodePathException
     */
    protected function prepareGeneration(string $path): void
    {
        $isGenerationDir = $path !== '_default';

        $fileSystem = new Filesystem();

        if($isGenerationDir) {
            $genPath = base_path($path);
            if(! $fileSystem->isDirectory($genPath)) {
                $fileSystem->makeDirectory($genPath, recursive: true);
                $fileSystem->put($genPath . '/.gitignore', "*\n!.gitignore");
            }
        }

        $this->codePath->initPaths($this->codeStructure, $path, $isGenerationDir);

        if(! $isGenerationDir) {
            foreach ($this->builders as $buildType) {
                if($fileSystem->isFile($this->codePath->path($buildType->value)->file())) {
                    $this->replaceCautions[$buildType->value] = $buildType->stub() . " already exists, are you sure you want to replace it?";
                }
            }
        }
    }
}
