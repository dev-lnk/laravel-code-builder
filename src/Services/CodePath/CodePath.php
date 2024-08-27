<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath;

use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Advanced\TypeScriptPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\AddActionPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\ControllerPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\DTOPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\EditActionPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\FormPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\ModelPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\RequestPath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\RoutePath;
use DevLnk\LaravelCodeBuilder\Services\CodePath\Core\TablePath;
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;

class CodePath implements CodePathContract
{
    /**
     * @var array<string, CodePathItemContract>
     */
    private array $paths = [];

    public function initPaths(CodeStructure $codeStructure, string $generationPath, bool $isGenerationDir): void
    {
        $generationPath = str_replace(base_path('/'), '', $generationPath);

        $resGenPath = $isGenerationDir ? base_path($generationPath) : '';

        $namespace = implode('/', array_map(
            fn ($dir) => str($dir)->camel()->ucfirst()->value(),
            explode("/", $generationPath)
        ));

        $this
            ->setPath(
                new ModelPath(
                    $codeStructure->entity()->ucFirstSingular() . '.php',
                    $resGenPath ? $resGenPath . "/Models" : app_path('Models'),
                    $resGenPath ? str_replace('/', '\\', $namespace) . '\\Models' : 'App\\Models'
                )
            )
            ->setPath(
                new AddActionPath(
                    'Add' . $codeStructure->entity()->ucFirstSingular() . 'Action.php',
                    $resGenPath ? $resGenPath . "/Actions" : app_path('Actions'),
                    $resGenPath ? str_replace('/', '\\', $namespace) . '\\Actions' : 'App\\Actions'
                )
            )
            ->setPath(
                new EditActionPath(
                    'Edit' . $codeStructure->entity()->ucFirstSingular() . 'Action.php',
                    $resGenPath ? $resGenPath . "/Actions" : app_path('Actions'),
                    $resGenPath ? str_replace('/', '\\', $namespace) . '\\Actions' : 'App\\Actions'
                )
            )
            ->setPath(
                new RequestPath(
                    $codeStructure->entity()->ucFirstSingular() . 'Request.php',
                    $resGenPath ? $resGenPath . "/Http/Requests" : app_path('Http/Requests'),
                    $resGenPath ? str_replace('/', '\\', $namespace) . '\\Http\\Requests' : 'App\\Http\\Requests'
                )
            )
            ->setPath(
                new ControllerPath(
                    $codeStructure->entity()->ucFirstSingular() . 'Controller.php',
                    $resGenPath ? $resGenPath . "/Http/Controllers" : app_path('Http/Controllers'),
                    $resGenPath ? str_replace('/', '\\', $namespace) . '\\Http\\Controllers' : 'App\\Http\\Controllers'
                )
            )
            ->setPath(
                new RoutePath(
                    $codeStructure->entity()->lower() . '.php',
                    $resGenPath ? $resGenPath . "/routes" : base_path('routes'),
                    ''
                )
            )
            ->setPath(
                new FormPath(
                    'form.blade.php',
                    $resGenPath ? $resGenPath . "/resources/views/" . $codeStructure->entity()->lower() : base_path('resources/views/' . $codeStructure->entity()->lower()),
                    ''
                )
            )
            ->setPath(
                new DTOPath(
                    $codeStructure->entity()->ucFirstSingular() . 'DTO.php',
                    $resGenPath ? $resGenPath . "/DTO" : app_path('DTO'),
                    $resGenPath ? str_replace('/', '\\', $namespace) . '\\DTO' : 'App\\DTO'
                )
            )
            ->setPath(
                new TablePath(
                    'table.blade.php',
                    $resGenPath ? $resGenPath . "/resources/views/" . $codeStructure->entity()->lower() : base_path('resources/views/' . $codeStructure->entity()->lower()),
                    ''
                )
            )
            ->setPath(
                new TypeScriptPath(
                    $codeStructure->entity()->lower() . '.ts',
                    $resGenPath ? $resGenPath . "/resources/ts/" . $codeStructure->entity()->lower() : base_path('resources/ts/' . $codeStructure->entity()->lower()),
                    ''
                )
            )
        ;
    }

    public function setPath(AbstractPathItem $path): self
    {
        if(isset($this->paths[$path->getBuildAlias()])) {
            return $this;
        }
        $this->paths[$path->getBuildAlias()] = $path;

        return $this;
    }

    /**
     * @throws NotFoundCodePathException
     */
    public function path(string $alias): CodePathItemContract
    {
        return $this->paths[$alias] ?? throw new NotFoundCodePathException("CodePath alias '$alias' not found");
    }
}
