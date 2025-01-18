<?php

namespace App\Utils;

class RouteCollection
{
    private array $routes = [];

    public function add(string $name, Route $route): void
    {
        $this->routes[$name] = $route;
    }

    public function get(string $name): ?Route
    {
        return $this->routes[$name] ?? null;
    }

    public function all(): array
    {
        return $this->routes;
    }
}