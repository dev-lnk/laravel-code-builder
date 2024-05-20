<?php

declare(strict_types=1);

namespace DevLnk\LaravelCodeBuilder\Services\CodePath;

use DevLnk\LaravelCodeBuilder\Exceptions\NotFoundCodePathException;
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

    public function initPaths(CodeStructure $codeStructure, string $path, bool $isGenerationDir): void
    {
        $path = str_replace(base_path('/'), '', $path);

        $genPath = $isGenerationDir ? base_path($path) : '';

        $namespace = implode('/', array_map(
            fn ($dir) => str($dir)->camel()->ucfirst()->value(),
            explode("/", $path)
        ));

        $this
            ->setPath(
                new ModelPath(
                    $codeStructure->entity()->ucFirstSingular() . '.php',
                    $genPath ? $genPath . "/Models" : app_path('Models'),
                    $genPath ? str_replace('/', '\\', $namespace) . '\\Models' : 'App\\Models'
                )
            )
            ->setPath(
                new AddActionPath(
                    'Add' . $codeStructure->entity()->ucFirstSingular() . 'Action.php',
                    $genPath ? $genPath . "/Actions" : app_path('Actions'),
                    $genPath ? str_replace('/', '\\', $namespace) . '\\Actions' : 'App\\Actions'
                )
            )
            ->setPath(
                new EditActionPath(
                    'Edit' . $codeStructure->entity()->ucFirstSingular() . 'Action.php',
                    $genPath ? $genPath . "/Actions" : app_path('Actions'),
                    $genPath ? str_replace('/', '\\', $namespace) . '\\Actions' : 'App\\Actions'
                )
            )
            ->setPath(
                new RequestPath(
                    $codeStructure->entity()->ucFirstSingular() . 'Request.php',
                    $genPath ? $genPath . "/Http/Requests" : app_path('Http/Requests'),
                    $genPath ? str_replace('/', '\\', $namespace) . '\\Http\\Requests' : 'App\\Http\\Requests'
                )
            )
            ->setPath(
                new ControllerPath(
                    $codeStructure->entity()->ucFirstSingular() . 'Controller.php',
                    $genPath ? $genPath . "/Http/Controllers" : app_path('Http/Controllers'),
                    $genPath ? str_replace('/', '\\', $namespace) . '\\Http\\Controllers' : 'App\\Http\\Controllers'
                )
            )
            ->setPath(
                new RoutePath(
                    $codeStructure->entity()->lower() . '.php',
                    $genPath ? $genPath . "/routes" : base_path('routes'),
                    ''
                )
            )
            ->setPath(
                new FormPath(
                    'form.blade.php',
                    $genPath ? $genPath . "/resources/views/" . $codeStructure->entity()->lower() : base_path('resources/views/' . $codeStructure->entity()->lower()),
                    ''
                )
            )
            ->setPath(
                new DTOPath(
                    $codeStructure->entity()->ucFirstSingular() . 'DTO.php',
                    $genPath ? $genPath . "/DTO" : app_path('DTO'),
                    $genPath ? str_replace('/', '\\', $namespace) . '\\DTO' : 'App\\DTO'
                )
            )
            ->setPath(
                new TablePath(
                    'table.blade.php',
                    $genPath ? $genPath . "/resources/views/" . $codeStructure->entity()->lower() : base_path('resources/views/' . $codeStructure->entity()->lower()),
                    ''
                )
            )
        ;
    }

    public function setPath(AbstractPathItem $path): self
    {
        if(isset($this->paths[$path->getBuildType()->value])) {
            return $this;
        }
        $this->paths[$path->getBuildType()->value] = $path;

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
