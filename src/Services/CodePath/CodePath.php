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
use DevLnk\LaravelCodeBuilder\Services\CodeStructure\CodeStructure;

final class CodePath
{
    /**
     * @var array<string, CodePathContract>
     */
    private array $paths = [];

    public function initPaths(CodeStructure $codeStructure, string $path, bool $isGenerationDir): void
    {
        $genPath = $isGenerationDir ? app_path($path) : '';

        $this
            ->setPath(
                new ModelPath(
                    $codeStructure->entity()->ucFirstSingular() . '.php',
                    $genPath ? $genPath . "/Models" : app_path('Models'),
                    $genPath ? 'App\\' . str_replace('/', '\\', $path) . '\\Models' : 'App\\Models'
                )
            )
            ->setPath(
                new AddActionPath(
                    'Add' . $codeStructure->entity()->ucFirstSingular() . 'Action.php',
                    $genPath ? $genPath . "/Actions" : app_path('Actions'),
                    $genPath ? 'App\\' . str_replace('/', '\\', $path) . '\\Actions' : 'App\\Actions'
                )
            )
            ->setPath(
                new EditActionPath(
                    'Edit' . $codeStructure->entity()->ucFirstSingular() . 'Action.php',
                    $genPath ? $genPath . "/Actions" : app_path('Actions'),
                    $genPath ? 'App\\' . str_replace('/', '\\', $path) . '\\Actions' : 'App\\Actions'
                )
            )
            ->setPath(
                new RequestPath(
                    $codeStructure->entity()->ucFirstSingular() . 'Request.php',
                    $genPath ? $genPath . "/Http/Requests" : app_path('Http/Requests'),
                    $genPath ? 'App\\' . str_replace('/', '\\', $path) . '\\Http\\Requests' : 'App\\Http\\Requests'
                )
            )
            ->setPath(
                new ControllerPath(
                    $codeStructure->entity()->ucFirstSingular() . 'Controller.php',
                    $genPath ? $genPath . "/Http/Controllers" : app_path('Http/Controllers'),
                    $genPath ? 'App\\' . str_replace('/', '\\', $path) . '\\Http\\Controllers' : 'App\\Http\\Controllers'
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
                    $codeStructure->entity()->lower() . '.blade.php',
                    $genPath ? $genPath . "/resources/views" : base_path('resources/views'),
                    ''
                )
            )
            ->setPath(
                new DTOPath(
                    $codeStructure->entity()->ucFirstSingular() . 'DTO.php',
                    $genPath ? $genPath . "/DTO" : app_path('DTO'),
                    $genPath ? 'App\\' . str_replace('/', '\\', $path) . '\\DTOs' : 'App\\DTOs'
                )
            )
        ;
    }

    public function setPath(AbstractPath $path): self
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
    public function path(string $alias): CodePathContract
    {
        return $this->paths[$alias] ?? throw new NotFoundCodePathException("CodePath alias '$alias' not found");
    }
}
