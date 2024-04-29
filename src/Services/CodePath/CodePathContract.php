<?php

namespace DevLnk\LaravelCodeBuilder\Services\CodePath;

interface CodePathContract
{
    public function name(): string;

    public function rawName(): string;

    public function file(): string;

    public function namespace(): string;
}