<?php

namespace App\Utils;
// matches a URL with a set of routes (RouteCollection)
class UrlMatcher
{
    private RouteCollection $routes;
    private RequestContext $context;

    public function __construct(RouteCollection $routes, RequestContext $context)
    {
        $this->routes = $routes;
        $this->context = $context;
    }
    public function match(string $pathInfo): array
    {
        foreach ($this->routes->all() as $name => $route) {
            $pattern = $this->convertPathToRegex($route->getPath());
            if(preg_match($pattern,$pathInfo, $matches)){
                if(!in_array($this->context->getMethod(), $route->getMethods())){
                    continue;
                }
                return array_merge(['_controller'=>$route->getAction()], $this->extractParameters($matches, $route->getPath()));
            }
        }
        throw new ResourceNotFoundException('No route found for '.$pathInfo);
    }

    private function convertPathToRegex(string $path): string
    {
        $regex = preg_replace('/\{([a-zA-Z0-9]+)\}/', '(?P<\1>[^/]+)', $path);
        return '#^' . $regex . '$#';
    }

    private function extractParameters(array $matches, string $path): array
    {
        $parameters = [];
        preg_match_all('/\{([a-zA-Z0-9]+)\}/', $path, $paramNames);

        if(isset($paramNames[1])){
            foreach ($paramNames[1] as $index => $paramName) {
                if(isset($matches[$paramName])){
                    $parameters[$paramName] = $matches[$paramName];
                }
            }
        }

        return $parameters;
    }

}