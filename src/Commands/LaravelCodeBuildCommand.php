<?php

namespace DevLnk\LaravelCodeBuilder\Commands;

use DevLnk\LaravelCodeBuilder\Enums\BuildType;
use DevLnk\LaravelCodeBuilder\Exceptions\CodeGenerateCommandException;
use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\Builders\BuildFactory;
use DevLnk\LaravelCodeBuilder\Services\CodePath\CodePath;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructureFactory;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\confirm;

use function Laravel\Prompts\select;

use Throwable;

class LaravelCodeBuildCommand extends Command
{
    protected $signature = 'code:build {entity} {table?} {--model} {--request} {--addAction} {--editAction} {--request} {--controller} {--route} {--form} {--DTO} {--builders} {--has-many=*} {--has-one=*} {--belongs-to-many=*}';

    private CodePath $codePath;

    private CodeStructure $codeStructure;

    /**
     * @var array<string, string>
     */
    private array $replaceCautions = [];

    /**
     * @var array<int, BuildType>
     */
    private array $builders = [];

    /**
     * @throws FileNotFoundException|Throwable
     */
    public function handle(): void
    {
        $stubDir = config('code_builder.stub_dir', __DIR__ . '/../../code_stubs') . '/';

        $this->prepareBuilders();

        $this->codePath = new CodePath();

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

        $entity = $this->argument('entity');
        if(is_array($entity)) {
            throw new CodeGenerateCommandException('The entity argument must not be an array');
        }

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

        $this->codeStructure = CodeStructureFactory::makeFromTable(
            (string) $table,
            (string) $entity,
            $confirmBelongsTo,
            $hasMany,
            $hasOne,
            $belongsToMany
        );

        $this->codeStructure->setStubDir($stubDir);

        $path = config('code_builder.generation_path') ?? select(
            label: 'Where to generate the result?',
            options: [
                '_default' => 'In the project directories',
                'Generation' => 'To the generation folder: `app/Generation`',
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

            if($confirmed) {
                $buildFactory->call($builder->value, $stubDir . $builder->stub());

                $codePath = $this->codePath->path($builder->value);
                $filePath = substr($codePath->file(), strpos($codePath->file(), '/app') + 1);
                $this->info($filePath . ' was created successfully!');
            }
        }
    }

    private function prepareBuilders(): void
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
    private function prepareGeneration(string $path): void
    {
        $isGenerationDir = $path !== '_default';

        $fileSystem = new Filesystem();

        $genPath = app_path($path);

        $generateDirs = [];
        $generateProjectDirs = [];

        if(in_array(BuildType::MODEL, $this->builders)) {
            $generateDirs[] = 'Models';
        }

        if(
            in_array(BuildType::ADD_ACTION, $this->builders)
            || in_array(BuildType::EDIT_ACTION, $this->builders)
        ) {
            $generateDirs[] = 'Actions';
            $generateProjectDirs[] = 'Actions';
        }

        if(in_array(BuildType::DTO, $this->builders)) {
            $generateDirs[] = 'DTO';
            $generateProjectDirs[] = 'DTO';
        }

        if(in_array(BuildType::REQUEST, $this->builders)) {
            $generateDirs[] = 'Http/Requests';
            $generateProjectDirs[] = 'Http/Requests';
        }

        if(in_array(BuildType::CONTROLLER, $this->builders)) {
            $generateDirs[] = 'Http/Controllers';
            $generateProjectDirs[] = 'Http/Controllers';
        }

        if(in_array(BuildType::ROUTE, $this->builders)) {
            $generateDirs[] = 'routes';
        }

        if(in_array(BuildType::FORM, $this->builders)) {
            $generateDirs[] = 'resources/views';
        }

        if($isGenerationDir) {
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
                'Add' . $this->codeStructure->entity()->ucFirstSingular() . 'Action.php',
                $isGenerationDir ? $genPath . "/Actions" : app_path('Actions'),
                $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Actions' : 'App\\Actions'
            )
            ->editAction(
                'Edit' . $this->codeStructure->entity()->ucFirstSingular() . 'Action.php',
                $isGenerationDir ? $genPath . "/Actions" : app_path('Actions'),
                $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Actions' : 'App\\Actions'
            )
            ->request(
                $this->codeStructure->entity()->ucFirstSingular() . 'Request.php',
                $isGenerationDir ? $genPath . "/Http/Requests" : app_path('Http/Requests'),
                $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Http\\Requests' : 'App\\Http\\Requests'
            )
            ->controller(
                $this->codeStructure->entity()->ucFirstSingular() . 'Controller.php',
                $isGenerationDir ? $genPath . "/Http/Controllers" : app_path('Http/Controllers'),
                $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\Http\\Controllers' : 'App\\Http\\Controllers'
            )
            ->route(
                $this->codeStructure->entity()->lower() . '.php',
                $isGenerationDir ? $genPath . "/routes" : base_path('routes'),
                ''
            )
            ->form(
                $this->codeStructure->entity()->lower() . '.blade.php',
                $isGenerationDir ? $genPath . "/resources/views" : base_path('resources/views'),
                ''
            )
            ->dto(
                $this->codeStructure->entity()->ucFirstSingular() . 'DTO.php',
                $isGenerationDir ? $genPath . "/DTO" : app_path('DTO'),
                $isGenerationDir ? 'App\\' . str_replace('/', '\\', $path) . '\\DTOs' : 'App\\DTOs'
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
