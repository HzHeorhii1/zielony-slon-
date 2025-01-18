<?php

namespace App\Utils;

class Route
{
    private string $path;
    private array $action;
    private array $methods;

    public function __construct(string $path, array $action, array $methods = ['GET'])
    {
        $this->path = $path;
        $this->action = $action;
        $this->methods = $methods;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getAction(): array
    {
        return $this->action;
    }
    public function getMethods(): array
    {
        return $this->methods;
    }
}